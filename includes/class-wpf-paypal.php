<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPF_PayPal {

    private $client_id;
    private $secret;
    private $mode;
    private $base_url;

    public function __construct() {
        $this->mode      = get_option( 'wpf_paypal_mode', 'sandbox' );
        $this->client_id = get_option( 'wpf_paypal_client_id', '' );
        $this->secret    = get_option( 'wpf_paypal_secret', '' );
        $this->base_url  = ( $this->mode === 'live' )
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get OAuth 2.0 access token from PayPal.
     */
    public function get_access_token() {
        $cache_key = 'wpf_paypal_token_' . $this->mode;
        $cached    = get_transient( $cache_key );
        if ( $cached ) return $cached;

        $response = wp_remote_post( $this->base_url . '/v1/oauth2/token', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->secret ),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body'    => 'grant_type=client_credentials',
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) return false;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['access_token'] ) ) {
            $expires_in = isset( $body['expires_in'] ) ? intval( $body['expires_in'] ) - 60 : 3540;
            set_transient( $cache_key, $body['access_token'], $expires_in );
            return $body['access_token'];
        }

        return false;
    }

    /**
     * Create a PayPal Order (Orders API v2).
     */
    public function create_order( $amount, $currency = 'USD' ) {
        $token = $this->get_access_token();
        if ( ! $token ) return false;

        $payload = wp_json_encode( array(
            'intent'         => 'CAPTURE',
            'purchase_units' => array( array(
                'amount' => array(
                    'currency_code' => strtoupper( $currency ),
                    'value'         => number_format( $amount, 2, '.', '' ),
                ),
            ) ),
            'payment_source' => array(
                'paypal' => array(
                    'experience_context' => array(
                        'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        'brand_name'                => get_option( 'wpf_brand_name', get_bloginfo( 'name' ) ),
                        'locale'                    => 'en-US',
                        'landing_page'              => 'LOGIN',
                        'user_action'               => 'PAY_NOW',
                        'return_url'                => home_url( '/' ),
                        'cancel_url'                => home_url( '/' ),
                    ),
                ),
            ),
        ) );

        $response = wp_remote_post( $this->base_url . '/v2/checkout/orders', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => $payload,
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) return false;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return isset( $body['id'] ) ? $body : false;
    }

    /**
     * Capture a PayPal Order.
     */
    public function capture_order( $order_id ) {
        $token = $this->get_access_token();
        if ( ! $token ) return false;

        $response = wp_remote_post( $this->base_url . "/v2/checkout/orders/{$order_id}/capture", array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => '{}',
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) return false;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return $body;
    }

    /**
     * Get the PayPal JS SDK Client ID (for frontend).
     */
    public function get_client_id() {
        return $this->client_id;
    }

    /**
     * Get current mode (sandbox / live).
     */
    public function get_mode() {
        return $this->mode;
    }
}
