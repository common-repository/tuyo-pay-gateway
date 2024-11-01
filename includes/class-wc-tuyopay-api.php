<?php
defined('ABSPATH') || exit;

/**
 * Communicates with TuyoPay API
 */
class WC_TuyoPay_API
{

    /**
     * Define API constants
     */
    const ENDPOINT = 'https://tuyoanalytics.com/api';
    const ENDPOINT_TEST = 'https://test.tuyoanalytics.com/api';
    const STATUS_APPROVED = 'COMPLETED';
    const STATUS_DECLINED = 'ERRORED';
    const STATUS_PENDING = 'PENDING';
    const PAYMENT_TYPE_CARD = 'CARD';
    const PAYMENT_TYPE_POINTS = 'MILLAS';

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * API endpoint
     */
    private $endpoint = '';

    /**
     * API Key
     */
    private $api_key = '';

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
     * Constructor
     */
    public function __construct()
    {

        $options = WC_TuyoPay::$settings;

        if ('yes' === $options['testmode']) {
            $this->endpoint = self::ENDPOINT_TEST;
            $this->api_key = $options['test_api_key'];
        } else {
            $this->endpoint = self::ENDPOINT;
            $this->api_key = $options['api_key'];
        }
    }

    /**
     * Getter
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    /**
     * Generates the headers to pass to API request
     */
    private function get_headers($use_secret)
    {
        $headers = array();

        if ($use_secret) {
            $headers['x-api-token'] = $this->api_key;
        }

        return $headers;
    }

    /**
     * Send the request to TuyoPay's API
     */
    public function request($method, $request, $data = null, $use_secret = false)
    {
        WC_TuyoPay_Logger::log("==== REQUEST ============================== Start Log ==== \n REQUEST URL: " . $method . ' ' . $this->endpoint . $request . "\n", false);
        if (!is_null($data)) {
            WC_TuyoPay_Logger::log('REQUEST DATA: ' . print_r($data, true), false);
        }

        $headers = $this->get_headers($use_secret);

        $params = array(
            'method'  => $method,
            'headers' => $headers,
            'body'    => $data,
        );

        // Exclude api key from logs
        if ('yes' === WC_TuyoPay::$settings['logging'] && !empty($headers)) {
            $strlen = strlen($this->api_key);
            $headers['x-api-token'] = (!empty($strlen) ? str_repeat('X', $strlen) : '');
            WC_TuyoPay_Logger::log('REQUEST HEADERS: ' . print_r($headers, true), false);
        }

        $response = wp_safe_remote_post($this->endpoint . $request, $params);
        WC_TuyoPay_Logger::log('REQUEST RESPONSE: ' . print_r($response, true), false);

        if (is_wp_error($response)) {
            return false;
        }

        return json_decode($response['body']);
    }

    /**
     * Transaction status
     */
    public function transaction_status($transaction_id)
    {
        $response = $this->request('POST', '/find_transactions/' . $transaction_id, null, true);
        return $response->data->status == self::STATUS_APPROVED ? true : false;
    }
}

WC_TuyoPay_API::instance();
