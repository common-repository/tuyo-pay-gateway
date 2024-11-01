<?php
defined('ABSPATH') || exit;

/**
 * Webhook Handler Class
 */
class WC_TuyoPay_Webhook_Handler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('woocommerce_api_wc_tuyopay', array($this, 'check_for_webhook'));
    }

    /**
     * Check incoming requests for TuyoPay Webhook data and process them
     */
    public function check_for_webhook()
    {

        if (!WC_TuyoPay_Helper::is_webhook(true)) {
            return false;
        }

        $response = json_decode(file_get_contents('php://input'));
        if (is_object($response)) {
            WC_TuyoPay_Logger::log('check_for_webhook response: ' . print_r($response, true));
            $this->process_webhook($response);
        } else {
            WC_TuyoPay_Logger::log('check_for_webhook ERROR');
            status_header(400);
        }
    }

    /**
     * Processes the incoming webhook
     */
    public function process_webhook($response)
    {
        switch ($response->status) {
            case WC_TuyoPay_API::STATUS_APPROVED:
                $this->process_webhook_payment($response);
                break;
            default:
                WC_TuyoPay_Logger::log('process_webhook transaction not found');
                status_header(404);
        }
    }

    /**
     * Process the payment
     */
    public function process_webhook_payment($response)
    {
        if (isset($response)) {
            $order = new WC_Order($response->ref);
            if ($this->is_payment_valid($order, $response)) {
                $this->update_order_data($order, $response);
                $this->apply_status($order, $response);
                status_header(200);
            } else {
                $this->update_transaction_status($order, __('El pago no fue procesado. Referencia: ', 'tuyo-pay-gateway') . ' (' . $response->ref . ')', 'failed');
                status_header(400);
            }
        } else {
            WC_TuyoPay_Logger::log('process_webhook_payment response not found');
            status_header(404);
        }
    }

    /**
     * Validate transaction response
     */
    protected function is_payment_valid($order, $voucher)
    {
        if ($order === false) {
            WC_TuyoPay_Logger::log('El pedido no se encuentra' . ' Referencia: ' . $voucher->ref);
            return false;
        }

        $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;

        if ($order->get_payment_method() != 'tuyopay') {
            WC_TuyoPay_Logger::log('Método de pago incorrecto' . ' Referencia: ' . $voucher->code . ' Pedido: ' . $order_id . ' Método de pago: ' . $order->get_payment_method());
            return false;
        }

        $amount = WC_TuyoPay_Helper::get_amount_in_cents($order->get_total());
        $voucher_amount = WC_TuyoPay_Helper::get_amount_in_cents($voucher->amount);
        if ($voucher_amount != $amount) {
            WC_TuyoPay_Logger::log('Monto incorrecto' . ' Referencia: ' . $voucher->code . ' Pedido: ' . $order_id . ' Monto: ' . $amount);
            return false;
        }

        return true;
    }

    /**
     * Apply transaction status
     */
    public function apply_status($order, $voucher)
    {
        switch ($voucher->status) {
            case WC_TuyoPay_API::STATUS_APPROVED:
                $order->payment_complete($voucher->ref);
                $this->update_transaction_status($order, __('Pago exitoso. Referencia: ', 'tuyo-pay-gateway') . ' (' . $voucher->code . ')', 'processing');
                break;
            default: // ERROR
                $this->update_transaction_status($order, __('El pago no fue procesado. Referencia: ', 'tuyo-pay-gateway') . ' (' . $voucher->code . ')', 'failed');
        }
    }

    /**
     * Update order data
     */
    public function update_order_data($order, $voucher)
    {

        $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;

        // Check if order data was set
        if (!$order->get_transaction_id()) {
            // Set transaction id
            update_post_meta($order_id, '_transaction_id', $voucher->code);
            // Set payment method type
            update_post_meta($order_id, WC_TuyoPay::FIELD_PAYMENT_METHOD_TYPE, 'tuyopay');
            // Set customer email
            if (!$order->get_billing_email()) {
                update_post_meta($order_id, '_billing_email', $voucher->email);
                update_post_meta($order_id, '_billing_address_index', $voucher->email);
            }
            // Set first name
            if (!$order->get_billing_first_name() && property_exists($voucher, 'firstname')) {
                update_post_meta($order_id, '_billing_first_name', $voucher->firstname);
            }
            // Set last name
            if (!$order->get_billing_last_name() && property_exists($voucher, 'firstname')) {
                update_post_meta($order_id, '_billing_last_name', $voucher->lastname);
            }
            // Set phone number
            if (!$order->get_billing_phone() && property_exists($voucher, 'phone')) {
                update_post_meta($order_id, '_billing_phone', $voucher->phone);
            }
        }
    }

    /**
     * Update transaction status
     */
    public function update_transaction_status($order, $note, $status)
    {
        $order->add_order_note($note);
        $status = apply_filters('wc_tuyopay_order_status', $status, $order);
        if ($status) {
            $order->update_status($status);
        }
    }
}

new WC_TuyoPay_Webhook_Handler();
