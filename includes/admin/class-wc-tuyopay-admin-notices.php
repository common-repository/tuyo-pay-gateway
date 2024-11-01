<?php
defined('ABSPATH') || exit;

/**
 * Class that represents admin notices.
 */
class WC_TuyoPay_Admin_Notices
{
    /**
     * Notices (array)
     */
    public $notices = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Allow this class and other classes to add slug keyed notices (to avoid duplication).
     */
    public function add_admin_notice($slug, $class, $message, $dismissible = false)
    {
        $this->notices[$slug] = array(
            'class'       => $class,
            'message'     => $message,
            'dismissible' => $dismissible,
        );
    }

    /**
     * Display any notices we've collected thus far.
     */
    public function admin_notices()
    {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $this->tuyopay_check_environment();

        foreach ((array) $this->notices as $notice_key => $notice) {
            echo '<div class="' . esc_attr($notice['class']) . '"><p>' . wp_kses($notice['message'], array('a' => array('href' => array(), 'target' => array()))) . '</p></div>';
        }
    }

    /**
     * The backup sanity check, in case the plugin is activated in a weird way,
     * or the environment changes after activation. Also handles upgrade routines.
     */
    public function tuyopay_check_environment()
    {
        $options = WC_TuyoPay::$settings;
        $testmode = (isset($options['testmode']) && 'yes' === $options['testmode']) ? true : false;
        $test_api_key = isset($options['test_api_key']) ? $options['test_api_key'] : '';
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';

        if (isset($options['enabled']) && 'yes' === $options['enabled']) {
            $setting_link = $this->get_setting_link();

            // Check if keys are entered properly per live/test mode.
            if ($testmode) {
                if (empty($test_api_key)) {
                    $this->add_admin_notice('wc_tuyopay', 'notice notice-error', sprintf(__('TuyoPay is in test mode however your test keys may not be valid. Please go to your settings and, <a href="%s">set your TuyoPay account keys</a>.', 'tuyo-pay-gateway'), $setting_link));
                }
            } else {
                if (empty($api_key)) {
                    $this->add_admin_notice('wc_tuyopay', 'notice notice-error', sprintf(__('TuyoPay is in live mode however your live keys may not be valid. Please go to your settings and, <a href="%s">set your TuyoPay account keys</a>.', 'tuyo-pay-gateway'), $setting_link));
                }
            }
        }
    }

    /**
     * Get setting link.
     */
    public function get_setting_link()
    {
        $use_id_as_section = function_exists('WC') ? version_compare(WC()->version, '2.6', '>=') : false;

        $section_slug = $use_id_as_section ? 'tuyopay' : strtolower('WC_Gateway_TuyoPay');

        return admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $section_slug);
    }
}

new WC_TuyoPay_Admin_Notices();
