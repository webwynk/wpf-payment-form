<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPF_Activator {

    public static function activate() {
        self::create_tables();
        self::set_default_options();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Run on every plugins_loaded — applies DB migrations automatically
     * so upgrades never require manual deactivate/reactivate.
     */
    public static function maybe_upgrade_db() {
        $installed = get_option( 'wpf_db_version', '0' );
        if ( version_compare( $installed, WPF_VERSION, '<' ) ) {
            self::create_tables();   // dbDelta is safe to run repeatedly
            self::set_default_options();
        }
    }

    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . 'wpf_transactions';

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            wpf_id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            wpf_name            VARCHAR(100)        NOT NULL DEFAULT '',
            wpf_email           VARCHAR(150)        NOT NULL DEFAULT '',
            wpf_phone           VARCHAR(30)         NOT NULL DEFAULT '',
            wpf_company         VARCHAR(150)        NOT NULL DEFAULT '',
            wpf_amount          DECIMAL(10,2)       NOT NULL DEFAULT '0.00',
            wpf_currency        VARCHAR(10)         NOT NULL DEFAULT 'USD',
            wpf_payment_method  ENUM('paypal','card','unknown') NOT NULL DEFAULT 'unknown',
            wpf_paypal_order_id VARCHAR(100)        NOT NULL DEFAULT '',
            wpf_status          ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
            wpf_billing_address TEXT,
            wpf_created_at      DATETIME            NOT NULL,
            wpf_updated_at      DATETIME            NOT NULL,
            PRIMARY KEY (wpf_id),
            KEY wpf_email   (wpf_email),
            KEY wpf_status  (wpf_status),
            KEY wpf_created (wpf_created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Also ensure wpf_company column exists on existing installs
        $col = $wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE 'wpf_company'" );
        if ( empty( $col ) ) {
            $wpdb->query( "ALTER TABLE {$table} ADD COLUMN wpf_company VARCHAR(150) NOT NULL DEFAULT '' AFTER wpf_phone" );
        }

        update_option( 'wpf_db_version', WPF_VERSION );
    }

    private static function set_default_options() {
        $defaults = array(
            'wpf_brand_name'              => 'WebWynk',
            'wpf_paypal_mode'             => 'sandbox',
            'wpf_paypal_client_id'        => '',
            'wpf_paypal_secret'           => '',
            'wpf_min_amount'              => '1',
            'wpf_max_amount'              => '99999',
            'wpf_currency'                => 'USD',
            'wpf_form_heading'            => 'Complete Your Payment',
            'wpf_form_subheading'         => 'Securely complete your transaction using PayPal or Credit Card.',
            'wpf_email_from_name'         => get_bloginfo( 'name' ),
            'wpf_email_from_email'        => get_option( 'admin_email' ),
            'wpf_email_admin_address'     => get_option( 'admin_email' ),
            'wpf_email_customer_subject'  => 'Payment Confirmed – #{transaction_id}',
            'wpf_email_admin_subject'     => 'New Payment Received – #{transaction_id} from {name}',
            'wpf_email_notify_customer'   => '1',
            'wpf_email_notify_admin'      => '1',
        );

        foreach ( $defaults as $key => $value ) {
            if ( get_option( $key ) === false ) {
                add_option( $key, $value );
            }
        }
    }
}
