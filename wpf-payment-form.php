<?php
/**
 * Plugin Name:  WPF Payment Form
 * Plugin URI:   #
 * Description:  Modern multi-step PayPal & Credit Card payment form with glassmorphism UI, transaction dashboard, and shortcode support.
 * Version:      1.3.0
 * Author:       Your Name
 * License:      GPL-2.0+
 * Text Domain:  wpf-payment-form
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Constants ────────────────────────────────────────────────────────────────
define( 'WPF_VERSION',         '1.3.1' );
define( 'WPF_PLUGIN_DIR',      plugin_dir_path( __FILE__ ) );
define( 'WPF_PLUGIN_URL',      plugin_dir_url( __FILE__ ) );
define( 'WPF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// ── Includes ─────────────────────────────────────────────────────────────────
require_once WPF_PLUGIN_DIR . 'includes/class-wpf-activator.php';
require_once WPF_PLUGIN_DIR . 'includes/class-wpf-paypal.php';
require_once WPF_PLUGIN_DIR . 'includes/class-wpf-transactions.php';
require_once WPF_PLUGIN_DIR . 'includes/class-wpf-emails.php';
require_once WPF_PLUGIN_DIR . 'includes/class-wpf-shortcode.php';
require_once WPF_PLUGIN_DIR . 'admin/class-wpf-admin.php';

// ── Activation / Deactivation ─────────────────────────────────────────────────
register_activation_hook( __FILE__, array( 'WPF_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPF_Activator', 'deactivate' ) );

// ── Bootstrap ─────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'wpf_init' );
function wpf_init() {
    // Auto-migrate DB on every load if version changed (handles upgrades without reactivation)
    WPF_Activator::maybe_upgrade_db();

    $shortcode = new WPF_Shortcode();
    $shortcode->init();

    if ( is_admin() ) {
        $admin = new WPF_Admin();
        $admin->init();
    }
}

// ── AJAX: Create PayPal Order ─────────────────────────────────────────────────
add_action( 'wp_ajax_wpf_create_order',        'wpf_create_order_handler' );
add_action( 'wp_ajax_nopriv_wpf_create_order', 'wpf_create_order_handler' );

function wpf_create_order_handler() {
    check_ajax_referer( 'wpf_nonce', 'nonce' );

    $amount  = isset( $_POST['amount'] )  ? round( floatval( $_POST['amount'] ), 2 )     : 0;
    $name    = isset( $_POST['name'] )    ? sanitize_text_field( $_POST['name'] )         : '';
    $email   = isset( $_POST['email'] )   ? sanitize_email( $_POST['email'] )             : '';
    $phone   = isset( $_POST['phone'] )   ? sanitize_text_field( $_POST['phone'] )        : '';
    $company = isset( $_POST['company'] ) ? sanitize_text_field( $_POST['company'] )      : '';

    $min = floatval( get_option( 'wpf_min_amount', 1 ) );
    $max = floatval( get_option( 'wpf_max_amount', 99999 ) );

    if ( $amount < $min || $amount > $max ) {
        wp_send_json_error( array( 'message' => "Amount must be between \${$min} and \${$max}." ) );
    }

    $paypal = new WPF_PayPal();
    $order  = $paypal->create_order( $amount, 'USD' );

    if ( $order && isset( $order['id'] ) ) {
        $tx = new WPF_Transactions();
        $tx_id = $tx->create( array(
            'wpf_name'           => $name,
            'wpf_email'          => $email,
            'wpf_phone'          => $phone,
            'wpf_company'        => $company,
            'wpf_amount'         => $amount,
            'wpf_currency'       => 'USD',
            'wpf_payment_method' => 'paypal',
            'wpf_paypal_order_id'=> $order['id'],
            'wpf_status'         => 'pending',
            'wpf_billing_address'=> '',
            'wpf_created_at'     => current_time( 'mysql' ),
            'wpf_updated_at'     => current_time( 'mysql' ),
        ) );

        if ( ! $tx_id ) {
            // DB insert failed — log it and report error so the JS doesn't silently proceed
            global $wpdb;
            error_log( '[WPF] DB insert failed: ' . $wpdb->last_error );
            wp_send_json_error( array( 'message' => 'Database error — could not save transaction. Please contact the site admin.' ) );
        }

        wp_send_json_success( array(
            'order_id'       => $order['id'],
            'transaction_id' => $tx_id,
        ) );
    } else {
        wp_send_json_error( array( 'message' => 'Could not create PayPal order. Please check your API credentials.' ) );
    }
}

// ── AJAX: Capture PayPal Order ────────────────────────────────────────────────
add_action( 'wp_ajax_wpf_capture_order',        'wpf_capture_order_handler' );
add_action( 'wp_ajax_nopriv_wpf_capture_order', 'wpf_capture_order_handler' );

function wpf_capture_order_handler() {
    check_ajax_referer( 'wpf_nonce', 'nonce' );

    $order_id      = isset( $_POST['order_id'] )      ? sanitize_text_field( $_POST['order_id'] )   : '';
    $tx_id         = isset( $_POST['transaction_id'] ) ? intval( $_POST['transaction_id'] )          : 0;
    $pay_method    = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : 'paypal';
    $zip           = isset( $_POST['zip'] )            ? sanitize_text_field( $_POST['zip'] )        : '';
    $country       = isset( $_POST['country'] )        ? sanitize_text_field( $_POST['country'] )    : '';

    if ( empty( $order_id ) ) {
        wp_send_json_error( array( 'message' => 'Invalid order ID.' ) );
    }

    $paypal  = new WPF_PayPal();
    $capture = $paypal->capture_order( $order_id );

    $tx = new WPF_Transactions();

    if ( $capture && isset( $capture['status'] ) && $capture['status'] === 'COMPLETED' ) {
        $tx->update( $tx_id, array(
            'wpf_status'         => 'completed',
            'wpf_payment_method' => $pay_method,
            'wpf_billing_address'=> wp_json_encode( array( 'zip' => $zip, 'country' => $country ) ),
            'wpf_updated_at'     => current_time( 'mysql' ),
        ) );

        // Fetch the full, updated transaction row for emails & JS response
        $full_tx = $tx->get( $tx_id );

        // ── Send emails ────────────────────────────────────────────────────────
        if ( $full_tx ) {
            if ( get_option( 'wpf_email_notify_customer', '1' ) === '1' ) {
                WPF_Emails::send_customer( $full_tx );
            }
            if ( get_option( 'wpf_email_notify_admin', '1' ) === '1' ) {
                WPF_Emails::send_admin( $full_tx );
            }
        }

        // Build billing display string
        $billing_str = '';
        if ( $zip || $country ) {
            $billing_str = trim( $zip . ( $zip && $country ? ' · ' : '' ) . $country );
        }

        wp_send_json_success( array(
            'message'        => 'Payment completed successfully.',
            'transaction_id' => '#' . $tx_id,
            'name'           => $full_tx['wpf_name']           ?? '',
            'email'          => $full_tx['wpf_email']          ?? '',
            'phone'          => $full_tx['wpf_phone']          ?? '',
            'company'        => $full_tx['wpf_company']        ?? '',
            'amount'         => '$' . number_format( floatval( $full_tx['wpf_amount'] ?? 0 ), 2 ),
            'currency'       => $full_tx['wpf_currency']       ?? 'USD',
            'method'         => $pay_method === 'paypal' ? 'PayPal' : 'Credit / Debit Card',
            'paypal_order'   => $full_tx['wpf_paypal_order_id']?? '',
            'billing'        => $billing_str,
            'date'           => date_i18n( 'F j, Y \a\t g:i A', strtotime( $full_tx['wpf_created_at'] ) ),
            'status'         => 'Completed',
        ) );
    } else {
        $tx->update( $tx_id, array(
            'wpf_status'     => 'failed',
            'wpf_updated_at' => current_time( 'mysql' ),
        ) );
        wp_send_json_error( array( 'message' => 'Payment capture failed. Please try again.' ) );
    }
}

// ── AJAX: Save Settings ───────────────────────────────────────────────────────
add_action( 'wp_ajax_wpf_save_settings', 'wpf_save_settings_handler' );

function wpf_save_settings_handler() {
    check_ajax_referer( 'wpf_admin_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

    $fields = array(
        'wpf_paypal_mode'            => 'sanitize_text_field',
        'wpf_paypal_client_id'       => 'sanitize_text_field',
        'wpf_paypal_secret'          => 'sanitize_text_field',
        'wpf_min_amount'             => 'floatval',
        'wpf_max_amount'             => 'floatval',
        'wpf_currency'               => 'sanitize_text_field',
        'wpf_form_heading'           => 'sanitize_text_field',
        'wpf_form_subheading'        => 'sanitize_textarea_field',
        // Email settings
        'wpf_email_from_name'        => 'sanitize_text_field',
        'wpf_email_from_email'       => 'sanitize_email',
        'wpf_email_admin_address'    => 'sanitize_email',
        'wpf_email_customer_subject' => 'sanitize_text_field',
        'wpf_email_admin_subject'    => 'sanitize_text_field',
    );

    foreach ( $fields as $key => $sanitizer ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_option( $key, $sanitizer( $_POST[ $key ] ) );
        }
    }

    // Checkboxes — unchecked fields are absent from POST, so explicitly save 0/1
    update_option( 'wpf_email_notify_customer', isset( $_POST['wpf_email_notify_customer'] ) ? '1' : '0' );
    update_option( 'wpf_email_notify_admin',    isset( $_POST['wpf_email_notify_admin'] )    ? '1' : '0' );

    wp_send_json_success( array( 'message' => 'Settings saved successfully.' ) );
}
