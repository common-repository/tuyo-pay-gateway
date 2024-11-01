<?php
defined('ABSPATH') || exit;

/**
 * Extend Payment Gateway class
 */
class WC_Gateway_TuyoPay_Custom extends WC_Payment_Gateway
{

    /**
     * Vars
     */
    const MINIMUM_ORDER_AMOUNT = 1;
    public $testmode;
    public $api_key;

    /**
     * Init hooks
     */
    public function init_hooks()
    {
        add_action('woocommerce_receipt_tuyopay', array($this, 'generate_tuyopay_widget'));
    }

    /**
     * Generate TuyoPay widget on "Pay for order" page
     */
    public function generate_tuyopay_widget($order_id)
    {
        $order = new WC_Order($order_id);

        $out = '
            <div 
                id="tuyo-pay-widget" 
                data-render="link"
                ' . (WC_TuyoPay::$settings['testmode'] === 'yes' ? 'data-sandbox="true"' : '') . '
                data-client-id="' . (WC_TuyoPay::$settings['testmode'] === 'yes' ? WC_TuyoPay::$settings['test_api_key'] : WC_TuyoPay::$settings['api_key']) . '"
                data-reference="' . $order_id . '"
                data-concept="Orden ' . $order_id . ' - ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '"
                data-amount="' . $order->get_total() . '"
                data-amount-in-cents="' . WC_TuyoPay_Helper::get_amount_in_cents($order->get_total()) . '"
                data-redirect-url="' . $order->get_checkout_order_received_url() . '"
                data-webhook-url="' . add_query_arg('wc-api', 'wc_tuyopay', trailingslashit(get_home_url())) . '"
                ></div>
        ';
        $out .= '<script src="https://tuyomedia.sfo2.digitaloceanspaces.com/tuyo-pay-widget/TuyoPaySDK.js"></script>';

        echo $out;
    }

    /**
     * Billing details fields on the checkout page
     */
    public static function billing_fields()
    {
        return array(); // return empty, means hide
    }

    /**
     * Before checkout billing form
     */
    public static function before_checkout_billing_form()
    {
        echo '<p>' . __('Los detalles de facturación son necesarios para ingresarse en el widget de Tuyo Pay', 'tuyo-pay-gateway') . '</p>';
    }

    /**
     * Generate order key on thank you page
     */
    public static function thankyou_order_key($order_key)
    {
        if (empty($_GET['key'])) {
            global $wp;
            $order = wc_get_order($wp->query_vars['order-received']);
            $order_key = $order->get_order_key();
        }

        return $order_key;
    }

    /**
     * Inform user if status of received order is failed on the thank you page
     */
    public static function thankyou_order_received_text($text)
    {
        global $wp;
        $order = wc_get_order($wp->query_vars['order-received']);
        $status = $order->get_status();
        if (in_array($status, array('ERRORED', 'PENDING'))) {
            return '<div class="woocommerce-error">' . sprintf(__('La orden cambio al estado &ldquo;%s&rdquo;. Por favor contáctanos si necesitas asistencia.', 'tuyo-pay-gateway'), $status) . '</div>';
        } else {
            return $text;
        }
    }

    /**
     * Validation on checkout page
     */
    public static function checkout_validation($fields, $errors)
    {
        $amount = floatval(WC()->cart->total);
        if (!self::validate_minimum_order_amount($amount)) {
            $errors->add('validation', sprintf(__('Lo siento, el monto mínimo permitido para usar este método de pago es %1$s.', 'tuyo-pay-gateway'), wc_remove_number_precision(self::MINIMUM_ORDER_AMOUNT)));
        }
    }

    /**
     * Validates that the order meets the minimum order amount
     */
    public static function validate_minimum_order_amount($amount)
    {
        if (WC_TuyoPay_Helper::get_amount_in_cents($amount) < self::MINIMUM_ORDER_AMOUNT) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Output payment method type on order admin page
     */
    public static function admin_order_data_after_order_details($order)
    {
        $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
        echo '<p class="form-field form-field-wide tuyopay-payment-method-type"><strong>' . __('Método de Pago', 'tuyo-pay-gateway') . ':</strong> ' . get_post_meta($order_id, WC_TuyoPay::FIELD_PAYMENT_METHOD_TYPE, true) . '</p>';
    }
}
