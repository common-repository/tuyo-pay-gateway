<?php
defined('ABSPATH') || exit;

/**
 * Provides static methods as helpers
 */
class WC_TuyoPay_Helper
{

    /**
     * Check if current request is webhook
     */
    public static function is_webhook($log = false)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['wc-api']) && $_GET['wc-api'] === 'wc_tuyopay') {
            return true;
        } else {
            if ($log) {
                WC_TuyoPay_Logger::log('Webhook checking error');
            }
            return false;
        }
    }

    /**
     * Get amount in cents
     */
    public static function get_amount_in_cents($amount)
    {
        return (int) ($amount * 100);
    }
}
