<?php
defined('ABSPATH') || exit;

/**
 * Main class
 */
class WC_TuyoPay
{

    /**
     * Define WP constants
     */
    const FIELD_PAYMENT_METHOD_TYPE = '_tuyopay_payment_method_type';

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Settings
     */
    public static $settings = array();

    /**
     * Instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Cloning is forbidden
     */
    public function __clone()
    {
    }

    /**
     * Unserializing instances of this class is forbidden
     */
    public function __wakeup()
    {
    }

    /**
     * Constructor
     */
    public function __construct()
    {

        // Get settings
        self::$settings = get_option('woocommerce_tuyopay_settings');

        // Includes
        require_once WC_TUYOPAY_PLUGIN_PATH . '/includes/class-wc-tuyopay-helper.php';
        require_once WC_TUYOPAY_PLUGIN_PATH . '/includes/class-wc-tuyopay-logger.php';
        require_once WC_TUYOPAY_PLUGIN_PATH . '/includes/class-wc-tuyopay-api.php';
        require_once WC_TUYOPAY_PLUGIN_PATH . '/includes/class-wc-tuyopay-webhook-handler.php';
        require_once WC_TUYOPAY_PLUGIN_PATH . '/includes/class-wc-gateway-tuyopay-custom.php';
        require_once WC_TUYOPAY_PLUGIN_PATH . '/includes/class-wc-gateway-tuyopay.php';

        if (is_admin()) {
            require_once WC_TUYOPAY_PLUGIN_PATH . '/includes/admin/class-wc-tuyopay-admin-notices.php';
        }

        // Hooks
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));

        if ('yes' === self::$settings['enabled']) {
            add_action('woocommerce_after_checkout_validation', array('WC_Gateway_TuyoPay_Custom', 'checkout_validation'), 10, 2);
            add_action('woocommerce_thankyou_order_received_text', array('WC_Gateway_TuyoPay_Custom', 'thankyou_order_received_text'));
            add_action('woocommerce_admin_order_data_after_order_details', array('WC_Gateway_TuyoPay_Custom', 'admin_order_data_after_order_details'));
            add_filter('woocommerce_thankyou_order_key', array('WC_Gateway_TuyoPay_Custom', 'thankyou_order_key'));
        }
    }

    /**
     * Add plugin action links
     */
    public static function plugin_action_links($links)
    {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=tuyopay') . '">' . __('Ajustes', 'tuyo-pay-gateway') . '</a>',
            '<a href="https://tuyopay.info/">' . esc_html__('Soporte', 'tuyo-pay-gateway') . '</a>',
        );

        return array_merge($plugin_links, $links);
    }

    /**
     * Admin enqueue scripts
     */
    public function admin_enqueue_scripts()
    {
        wp_enqueue_style('wc_tuyopay_admin_styles', WC_TUYOPAY_PLUGIN_URL . '/assets/css/admin.css');
    }

    /**
     * Add the gateway to WooCommerce
     */
    public static function add_gateway($methods)
    {
        $methods[] = 'WC_Gateway_TuyoPay';

        return $methods;
    }
}
