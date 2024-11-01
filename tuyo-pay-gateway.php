<?php
/*
Plugin Name: Tuyo Pay Gateway
Plugin URI: https://docs.tuyopay.info/
Description: Plugin WooCommerce para la pasarela de pagos Tuyo Pay.
Version: 1.1.0
Author: Tuyo Dev_
Author URI: https://tuyo.dev
Text Domain: tuyo-pay-gateway
License: GNU Lesser General Public License v2.1
Requires at least: 5.4
Tested up to: 6.3
Requires PHP: 7.1
WC requires at least: 3.5.0
WC tested up to: 8.0.1
*/

defined('ABSPATH') || exit;

/**
 * Constants
 */
define('WC_TUYOPAY_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('WC_TUYOPAY_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('WC_TUYOPAY_MIN_WC_VER', '3.5.0');

/**
 * Notice if WooCommerce not activated
 */
function woocommerce_gateway_tuyopay_not_installed_notice()
{
    echo '<div class="error"><p><strong>' . sprintf(esc_html__('Tuyo Pay Gateway requiere que WooCommerce esté instalado y activo. Puede descargar %s aquí.', 'tuyo-pay-gateway'), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
}

/**
 * Notice if WooCommerce not supported
 */
function woocommerce_gateway_tuyopay_wc_not_supported_notice()
{
    echo '<div class="error"><p><strong>' . sprintf(esc_html__('Tuyo Pay Gateway requiere WooCommerce %1$s o superior.', 'tuyo-pay-gateway'), WC_TUYOPAY_MIN_WC_VER, WC_VERSION) . '</strong></p></div>';
}

/**
 * Hook on plugins loaded
 */
add_action('plugins_loaded', 'woocommerce_gateway_tuyopay_init', 0);
function woocommerce_gateway_tuyopay_init()
{
    /**
     * Check if WooCommerce is activated
     */
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'woocommerce_gateway_tuyopay_not_installed_notice');
        return;
    }

    /**
     * Check if WooCommerce is supported
     */
    if (version_compare(WC_VERSION, WC_TUYOPAY_MIN_WC_VER, '<')) {
        add_action('admin_notices', 'woocommerce_gateway_tuyopay_wc_not_supported_notice');
        return;
    }

    /**
     * Returns the main instance of WC_TuyoPay
     */
    require_once WC_TUYOPAY_PLUGIN_PATH . '/includes/class-wc-tuyopay.php';
    WC_TuyoPay::instance();

    /**
     * Add plugin action links
     */
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('WC_TuyoPay', 'plugin_action_links'));
}
