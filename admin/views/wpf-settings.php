<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wpf-admin-wrap">

    <div class="wpf-admin-header">
        <div class="wpf-admin-header__logo">
            <span class="wpf-admin-header__icon dashicons dashicons-money-alt"></span>
            <div>
                <h1>PayPal Integration</h1>
                <p>Connect your PayPal Business account to start accepting payments.</p>
            </div>
        </div>
        <div class="wpf-admin-header__badge">
            <span class="wpf-mode-badge <?php echo esc_attr( get_option('wpf_paypal_mode','sandbox') ); ?>">
                <?php echo esc_html( ucfirst( get_option('wpf_paypal_mode','sandbox') ) ); ?> Mode
            </span>
        </div>
    </div>

    <div class="wpf-admin-notice" id="wpf-settings-notice" style="display:none;"></div>

    <form id="wpf-settings-form">
        <?php wp_nonce_field( 'wpf_admin_nonce', '_wpf_nonce' ); ?>

        <!-- PayPal Mode -->
        <div class="wpf-admin-card">
            <div class="wpf-admin-card__header">
                <span class="dashicons dashicons-admin-generic"></span>
                <h2>Environment Settings</h2>
            </div>
            <div class="wpf-admin-card__body">
                <div class="wpf-field-row">
                    <label class="wpf-label">PayPal Mode</label>
                    <div class="wpf-toggle-group">
                        <label class="wpf-toggle-option">
                            <input type="radio" name="wpf_paypal_mode" value="sandbox"
                                <?php checked( get_option('wpf_paypal_mode','sandbox'), 'sandbox' ); ?>>
                            <span class="wpf-toggle-btn wpf-toggle-sandbox">🧪 Sandbox (Testing)</span>
                        </label>
                        <label class="wpf-toggle-option">
                            <input type="radio" name="wpf_paypal_mode" value="live"
                                <?php checked( get_option('wpf_paypal_mode','sandbox'), 'live' ); ?>>
                            <span class="wpf-toggle-btn wpf-toggle-live">🚀 Live (Production)</span>
                        </label>
                    </div>
                    <p class="wpf-help">Use <strong>Sandbox</strong> for testing. Switch to <strong>Live</strong> when ready to accept real payments.</p>
                </div>

                <div class="wpf-field-row">
                    <label class="wpf-label">Default Currency</label>
                    <select name="wpf_currency" class="wpf-input">
                        <?php
                        $currencies = array( 'USD'=>'US Dollar','EUR'=>'Euro','GBP'=>'British Pound','CAD'=>'Canadian Dollar','AUD'=>'Australian Dollar','INR'=>'Indian Rupee' );
                        $current_currency = get_option('wpf_currency','USD');
                        foreach ( $currencies as $code => $label ) {
                            echo '<option value="' . esc_attr($code) . '" ' . selected($current_currency, $code, false) . '>' . esc_html("{$code} — {$label}") . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- API Credentials -->
        <div class="wpf-admin-card">
            <div class="wpf-admin-card__header">
                <span class="dashicons dashicons-lock"></span>
                <h2>API Credentials</h2>
            </div>
            <div class="wpf-admin-card__body">
                <div class="wpf-credential-hint">
                    <span class="dashicons dashicons-info-outline"></span>
                    Get your credentials from <a href="https://developer.paypal.com/dashboard/applications" target="_blank">PayPal Developer Dashboard</a> → My Apps &amp; Credentials → Create App.
                </div>

                <div class="wpf-field-row">
                    <label class="wpf-label" for="wpf_paypal_client_id">Client ID</label>
                    <div class="wpf-input-wrap">
                        <input type="text" id="wpf_paypal_client_id" name="wpf_paypal_client_id"
                            class="wpf-input"
                            value="<?php echo esc_attr( get_option('wpf_paypal_client_id','') ); ?>"
                            placeholder="AaBb...your client ID">
                    </div>
                </div>

                <div class="wpf-field-row">
                    <label class="wpf-label" for="wpf_paypal_secret">Secret Key</label>
                    <div class="wpf-input-wrap wpf-password-wrap">
                        <input type="password" id="wpf_paypal_secret" name="wpf_paypal_secret"
                            class="wpf-input"
                            value="<?php echo esc_attr( get_option('wpf_paypal_secret','') ); ?>"
                            placeholder="••••••••••••••••">
                        <button type="button" class="wpf-toggle-password" data-target="wpf_paypal_secret">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                    <p class="wpf-help">Your secret key is stored securely and never exposed to the browser.</p>
                </div>
            </div>
        </div>

        <!-- Amount Limits -->
        <div class="wpf-admin-card">
            <div class="wpf-admin-card__header">
                <span class="dashicons dashicons-chart-bar"></span>
                <h2>Payment Limits</h2>
            </div>
            <div class="wpf-admin-card__body wpf-two-col">
                <div class="wpf-field-row">
                    <label class="wpf-label" for="wpf_min_amount">Minimum Amount ($)</label>
                    <input type="number" id="wpf_min_amount" name="wpf_min_amount"
                        class="wpf-input"
                        value="<?php echo esc_attr( get_option('wpf_min_amount','1') ); ?>"
                        min="0.01" step="0.01">
                </div>
                <div class="wpf-field-row">
                    <label class="wpf-label" for="wpf_max_amount">Maximum Amount ($)</label>
                    <input type="number" id="wpf_max_amount" name="wpf_max_amount"
                        class="wpf-input"
                        value="<?php echo esc_attr( get_option('wpf_max_amount','99999') ); ?>"
                        min="1" step="0.01">
                </div>
            </div>
        </div>

        <!-- Form Text -->
        <div class="wpf-admin-card">
            <div class="wpf-admin-card__header">
                <span class="dashicons dashicons-edit"></span>
                <h2>Form Text</h2>
            </div>
            <div class="wpf-admin-card__body">
                <div class="wpf-field-row">
                    <label class="wpf-label" for="wpf_form_heading">Main Heading</label>
                    <input type="text" id="wpf_form_heading" name="wpf_form_heading"
                        class="wpf-input"
                        value="<?php echo esc_attr( get_option('wpf_form_heading','Complete Your Payment') ); ?>">
                </div>
                <div class="wpf-field-row">
                    <label class="wpf-label" for="wpf_form_subheading">Sub Heading</label>
                    <textarea id="wpf_form_subheading" name="wpf_form_subheading" class="wpf-input" rows="2"><?php echo esc_textarea( get_option('wpf_form_subheading','') ); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Email Notifications -->
        <div class="wpf-admin-card">
            <div class="wpf-admin-card__header">
                <span class="dashicons dashicons-email-alt"></span>
                <h2>Email Notifications</h2>
            </div>
            <div class="wpf-admin-card__body">
                <div class="wpf-credential-hint">
                    <span class="dashicons dashicons-info-outline"></span>
                    Use these tags in subject lines: <code>{transaction_id}</code> <code>{name}</code> <code>{email}</code> <code>{amount}</code> <code>{method}</code> <code>{date}</code>
                </div>
                <div class="wpf-notify-toggles">
                    <label class="wpf-check-label">
                        <input type="checkbox" name="wpf_email_notify_customer" value="1" <?php checked( get_option('wpf_email_notify_customer','1'), '1' ); ?>>
                        <span class="wpf-check-ui"></span>
                        Send confirmation email to customer after payment
                    </label>
                    <label class="wpf-check-label">
                        <input type="checkbox" name="wpf_email_notify_admin" value="1" <?php checked( get_option('wpf_email_notify_admin','1'), '1' ); ?>>
                        <span class="wpf-check-ui"></span>
                        Notify admin on every successful payment
                    </label>
                </div>
                <div class="wpf-admin-card__body wpf-two-col" style="padding:0;margin-top:20px;">
                    <div class="wpf-field-row">
                        <label class="wpf-label" for="wpf_email_from_name">From Name</label>
                        <input type="text" id="wpf_email_from_name" name="wpf_email_from_name" class="wpf-input"
                            value="<?php echo esc_attr( get_option('wpf_email_from_name', get_bloginfo('name')) ); ?>">
                    </div>
                    <div class="wpf-field-row">
                        <label class="wpf-label" for="wpf_email_from_email">From Email Address</label>
                        <input type="email" id="wpf_email_from_email" name="wpf_email_from_email" class="wpf-input"
                            value="<?php echo esc_attr( get_option('wpf_email_from_email', get_option('admin_email')) ); ?>">
                    </div>
                    <div class="wpf-field-row">
                        <label class="wpf-label" for="wpf_email_admin_address">Admin Notification Email</label>
                        <input type="email" id="wpf_email_admin_address" name="wpf_email_admin_address" class="wpf-input"
                            value="<?php echo esc_attr( get_option('wpf_email_admin_address', get_option('admin_email')) ); ?>">
                    </div>
                    <div class="wpf-field-row">
                        <label class="wpf-label" for="wpf_email_customer_subject">Customer Email Subject</label>
                        <input type="text" id="wpf_email_customer_subject" name="wpf_email_customer_subject" class="wpf-input"
                            value="<?php echo esc_attr( get_option('wpf_email_customer_subject','Payment Confirmed – #{transaction_id}') ); ?>">
                    </div>
                    <div class="wpf-field-row" style="grid-column:1/-1;">
                        <label class="wpf-label" for="wpf_email_admin_subject">Admin Email Subject</label>
                        <input type="text" id="wpf_email_admin_subject" name="wpf_email_admin_subject" class="wpf-input"
                            value="<?php echo esc_attr( get_option('wpf_email_admin_subject','New Payment Received – #{transaction_id} from {name}') ); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcode Info -->
        <div class="wpf-admin-card wpf-shortcode-card">
            <div class="wpf-admin-card__header">
                <span class="dashicons dashicons-shortcode"></span>
                <h2>Shortcode Usage</h2>
            </div>
            <div class="wpf-admin-card__body">
                <p>Copy and paste this shortcode into any page or post:</p>
                <div class="wpf-shortcode-box">
                    <code id="wpf-shortcode-text">[wpf_payment_form]</code>
                    <button type="button" class="wpf-copy-btn" data-clipboard="wpf-shortcode-text">
                        <span class="dashicons dashicons-admin-page"></span> Copy
                    </button>
                </div>
                <p class="wpf-help">Optional attributes: <code>[wpf_payment_form heading="Pay Now" currency="USD"]</code></p>
            </div>
        </div>

        <div class="wpf-admin-actions">
            <button type="submit" class="wpf-btn-primary" id="wpf-save-settings">
                <span class="dashicons dashicons-yes"></span> Save Settings
            </button>
            <span class="wpf-saving-indicator" id="wpf-saving-indicator" style="display:none;">Saving…</span>
        </div>
    </form>
</div>
