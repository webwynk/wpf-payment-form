<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPF_Shortcode {

    public function init() {
        add_shortcode( 'wpf_payment_form', array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue CSS, JS and PayPal SDK on pages that use the shortcode.
     */
    public function enqueue_assets() {
        global $post;

        // Only load on posts/pages containing our shortcode
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wpf_payment_form' ) ) {
            $this->do_enqueue();
        }
    }

    private function do_enqueue() {
        // Google Fonts
        wp_enqueue_style(
            'wpf-google-fonts',
            'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap',
            array(),
            null
        );

        // Plugin CSS
        wp_enqueue_style(
            'wpf-form-style',
            WPF_PLUGIN_URL . 'public/assets/wpf-form.css',
            array(),
            WPF_VERSION
        );

        // PayPal JS SDK
        $paypal = new WPF_PayPal();
        $client_id = $paypal->get_client_id();
        $currency  = get_option( 'wpf_currency', 'USD' );

        if ( ! empty( $client_id ) ) {
            $sdk_url = add_query_arg( array(
                'client-id'       => $client_id,
                'currency'        => $currency,
                'components'      => 'buttons',
                'disable-funding' => 'venmo,credit',
                'intent'          => 'capture',
            ), 'https://www.paypal.com/sdk/js' );

            wp_enqueue_script(
                'wpf-paypal-sdk',
                $sdk_url,
                array(),
                null,
                true
            );
        }

        // Plugin JS
        wp_enqueue_script(
            'wpf-form-script',
            WPF_PLUGIN_URL . 'public/assets/wpf-form.js',
            array( 'jquery' ),
            WPF_VERSION,
            true
        );

        wp_localize_script( 'wpf-form-script', 'wpfConfig', array(
            'ajax_url'   => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'wpf_nonce' ),
            'currency'   => $currency,
            'min_amount' => get_option( 'wpf_min_amount', 1 ),
            'max_amount' => get_option( 'wpf_max_amount', 99999 ),
            'has_paypal' => ! empty( $client_id ) ? '1' : '0',
        ) );
    }

    /**
     * Render the shortcode HTML.
     */
    public function render( $atts ) {
        $atts = shortcode_atts( array(
            'heading'    => get_option( 'wpf_form_heading',    'Complete Your Payment' ),
            'subheading' => get_option( 'wpf_form_subheading', 'Securely complete your transaction using PayPal or Credit Card.' ),
            'currency'   => get_option( 'wpf_currency', 'USD' ),
            'brand'      => get_option( 'wpf_brand_name', 'WebWynk' ),
        ), $atts, 'wpf_payment_form' );

        // Ensure assets are loaded even if called from PHP directly
        $this->do_enqueue();

        ob_start();
        include WPF_PLUGIN_DIR . 'public/views/wpf-form-template.php';
        return ob_get_clean();
    }
}
