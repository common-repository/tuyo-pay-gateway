<?php
defined('ABSPATH') || exit;

/**
 * Payment Gateway class
 */
class WC_Gateway_TuyoPay extends WC_Gateway_TuyoPay_Custom
{

    /**
     * Constructor
     */
    public function __construct()
    {

        $options = WC_TuyoPay::$settings;

        $this->id = 'tuyopay';
        $this->method_title = 'Tuyo Pay';
        $this->method_description = sprintf(__('Tuyo Pay funciona via Widget en el Checkout. <a href="%1$s" target="_blank">Regístrese</a> a Tuyo Pay si aún no tiene una cuenta, y solicite sus credenciales de acceso a la API de Tuyo Pay.', 'tuyo-pay-gateway'), 'https://tuyopay.info/');
        $this->has_fields = false;
        $this->init_form_fields();
        $this->init_settings();
        $this->enabled = $options['enabled'];
        $this->icon = WC_TUYOPAY_PLUGIN_URL . '/assets/img/' . $options['icon_style'] . '.png';
        $this->title = $options['title'];
        $this->description = $options['description'];
        $this->testmode = $options['testmode'];
        $this->supports = array(
            'products'
        );
        $this->api_key  = $this->testmode ? $options['test_api_key'] : $options['api_key'];

        // Hooks
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        if ('yes' === $this->enabled) {
            $this->init_hooks();
        }
    }

    /**
     * Checks to see if all criteria is met before showing payment method
     */
    public function is_available()
    {
        if (
            !parent::is_available() ||
            !$this->api_key
        ) {
            return false;
        }

        return true;
    }

    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields()
    {
        $this->form_fields = require(dirname(__FILE__) . '/admin/tuyopay-settings.php');
    }

    // TODO: Delete this
    /**
     * Gets the transaction URL linked to TuyoPay dashboard
     */
    public function get_transaction_url($order)
    {
        $this->view_transaction_url = 'https://dash.tuyoapp.com/';

        return parent::get_transaction_url($order);
    }

    /**
     * Process the payment (after place order)
     */
    public function process_payment($order_id)
    {
        $order = new WC_Order($order_id);
        if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>=')) {
            /* >= 2.1.0 */
            $checkout_payment_url = $order->get_checkout_payment_url(true);
        } else {
            /* < 2.1.0 */
            $checkout_payment_url = get_permalink(get_option('woocommerce_pay_page_id'));
        }

        // Clear cart
        WC()->cart->empty_cart();

        return array(
            'result' => 'success',
            'redirect' => add_query_arg('order_pay', $order_id, $checkout_payment_url)
        );
    }

    // TODO: Delete this
    /**
     * Process the payment to void
     */
    public static function process_void($order)
    {

        // Restore stock
        wc_maybe_increase_stock_levels($order);
    }
}
