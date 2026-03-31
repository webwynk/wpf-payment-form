<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPF_Admin {

    public function init() {
        add_action( 'admin_menu',             array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_wpf_export_csv', array( $this, 'export_csv' ) );
    }

    // ── Menu ─────────────────────────────────────────────────────────────────

    public function register_menus() {
        add_menu_page(
            'WPF Payment Form',
            'WPF Payments',
            'manage_options',
            'wpf-transactions',
            array( $this, 'page_transactions' ),
            'dashicons-money-alt',
            30
        );

        add_submenu_page(
            'wpf-transactions',
            'Transactions',
            'Transactions',
            'manage_options',
            'wpf-transactions',
            array( $this, 'page_transactions' )
        );

        add_submenu_page(
            'wpf-transactions',
            'PayPal Integration',
            'PayPal Integration',
            'manage_options',
            'wpf-settings',
            array( $this, 'page_settings' )
        );
    }

    // ── Assets ────────────────────────────────────────────────────────────────

    public function enqueue_assets( $hook ) {
        $pages = array( 'toplevel_page_wpf-transactions', 'wpf-payments_page_wpf-settings' );
        if ( ! in_array( $hook, $pages, true ) ) return;

        wp_enqueue_style(
            'wpf-admin-style',
            WPF_PLUGIN_URL . 'admin/assets/wpf-admin.css',
            array(),
            WPF_VERSION
        );

        wp_enqueue_script(
            'wpf-admin-script',
            WPF_PLUGIN_URL . 'admin/assets/wpf-admin.js',
            array( 'jquery' ),
            WPF_VERSION,
            true
        );

        wp_localize_script( 'wpf-admin-script', 'wpfAdmin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wpf_admin_nonce' ),
        ) );
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    public function page_transactions() {
        require_once WPF_PLUGIN_DIR . 'admin/views/wpf-transactions.php';
    }

    public function page_settings() {
        require_once WPF_PLUGIN_DIR . 'admin/views/wpf-settings.php';
    }

    // ── CSV Export ────────────────────────────────────────────────────────────

    public function export_csv() {
        check_ajax_referer( 'wpf_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

        $tx   = new WPF_Transactions();
        $rows = $tx->get_list( array( 'per_page' => 9999, 'page' => 1 ) );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=wpf-transactions-' . date( 'Y-m-d' ) . '.csv' );

        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, array( 'ID','Name','Email','Phone','Amount','Currency','Method','PayPal Order ID','Status','Billing (ZIP/Country)','Created','Updated' ) );

        foreach ( $rows as $row ) {
            $billing = json_decode( $row['wpf_billing_address'], true );
            $billing_str = $billing ? implode( ' / ', array_filter( $billing ) ) : '';
            fputcsv( $out, array(
                $row['wpf_id'],
                $row['wpf_name'],
                $row['wpf_email'],
                $row['wpf_phone'],
                $row['wpf_amount'],
                $row['wpf_currency'],
                $row['wpf_payment_method'],
                $row['wpf_paypal_order_id'],
                $row['wpf_status'],
                $billing_str,
                $row['wpf_created_at'],
                $row['wpf_updated_at'],
            ) );
        }

        fclose( $out );
        exit;
    }
}
