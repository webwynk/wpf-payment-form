<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="wpf-payment-form-wrap" class="wpf-wrap" role="main">
<div class="wpf-layout">

    <!-- ── LEFT PANEL ─────────────────────────────────────────────────────── -->
    <aside class="wpf-panel-left" aria-hidden="true">
        <div class="wpf-panel-left__inner">

            <div class="wpf-brand">
                <span class="wpf-brand__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <span class="wpf-brand__name"><?php echo esc_html( $atts['brand'] ); ?></span>
            </div>

            <div class="wpf-panel-left__copy">
                <h2 class="wpf-panel-left__heading"><?php echo esc_html( $atts['heading'] ); ?></h2>
                <p class="wpf-panel-left__sub"><?php echo esc_html( $atts['subheading'] ); ?></p>
            </div>

            <ul class="wpf-trust-list" role="list">
                <li class="wpf-trust-item">
                    <span class="wpf-trust-item__icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </span>
                    <span><strong>SSL Encrypted</strong>256-bit bank-level security</span>
                </li>
                <li class="wpf-trust-item">
                    <span class="wpf-trust-item__icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </span>
                    <span><strong>Instant Access</strong>Activates immediately after payment</span>
                </li>
                <li class="wpf-trust-item">
                    <span class="wpf-trust-item__icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.067 8.478c.492.88.556 2.014.3 3.327-.74 3.806-3.276 5.12-6.514 5.12h-.5a.805.805 0 0 0-.794.68l-.04.22-.63 3.993-.032.17a.804.804 0 0 1-.794.679H8.969a.483.483 0 0 1-.477-.558L9.948 12h2.56c4.106 0 7.39-1.998 8.315-6.287.373-1.768.232-3.22-.442-4.286C22.024 2.836 23 4.867 23 7.5c0 .337-.02.668-.053.993.056-.015.12-.015 0 0zM5.107 21.5h2.394a.805.805 0 0 0 .794-.68l.032-.17.63-3.993.04-.22a.805.805 0 0 1 .794-.68h.5c3.238 0 5.774-1.314 6.514-5.12.256-1.313.192-2.447-.3-3.327C15.37 5.866 13.745 5 11.5 5H6.5a.805.805 0 0 0-.794.68L3.5 19.5a.483.483 0 0 0 .477.558l1.13-.558z"/></svg>
                    </span>
                    <span><strong>Powered by PayPal</strong>Trusted by 430M+ users</span>
                </li>
                <li class="wpf-trust-item">
                    <span class="wpf-trust-item__icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    <span><strong>Email Receipt</strong>Confirmation sent instantly</span>
                </li>
            </ul>

            <div class="wpf-panel-deco" aria-hidden="true">
                <div class="wpf-deco-ring wpf-deco-ring--1"></div>
                <div class="wpf-deco-ring wpf-deco-ring--2"></div>
            </div>

        </div>
    </aside>

    <!-- ── RIGHT PANEL ────────────────────────────────────────────────────── -->
    <main class="wpf-panel-right">
        <div class="wpf-card">

            <!-- Step breadcrumb -->
            <nav class="wpf-breadcrumb" id="wpf-breadcrumb" aria-label="Checkout progress">
                <button class="wpf-breadcrumb__item wpf-breadcrumb__item--active" id="wpf-bc-1" type="button" tabindex="-1">
                    <span class="wpf-bc-num">1</span>
                    Your Details
                </button>
                <svg class="wpf-bc-sep" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                <button class="wpf-breadcrumb__item" id="wpf-bc-2" type="button" tabindex="-1">
                    <span class="wpf-bc-num">2</span>
                    Payment
                </button>
            </nav>

            <!-- ── STEP 1 ──────────────────────────────────────────────────── -->
            <div class="wpf-step" id="wpf-step-1">

                <div class="wpf-step-head">
                    <h3 class="wpf-step-title">Your information</h3>
                    <p class="wpf-step-sub">Fill in your details and select your payment amount.</p>
                </div>

                <div class="wpf-fields">

                    <div class="wpf-row-2">
                        <!-- Full Name -->
                        <div class="wpf-field" id="wpf-field-name">
                            <label class="wpf-label" for="wpf-name">Full Name <abbr class="wpf-req" title="required">*</abbr></label>
                            <div class="wpf-input-wrap">
                                <svg class="wpf-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <input type="text" id="wpf-name" class="wpf-input" placeholder="John Doe" autocomplete="name">
                            </div>
                            <span class="wpf-err" id="wpf-error-name" role="alert"></span>
                        </div>

                        <!-- Email -->
                        <div class="wpf-field" id="wpf-field-email">
                            <label class="wpf-label" for="wpf-email">Email <abbr class="wpf-req" title="required">*</abbr></label>
                            <div class="wpf-input-wrap">
                                <svg class="wpf-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                <input type="email" id="wpf-email" class="wpf-input" placeholder="you@example.com" autocomplete="email">
                            </div>
                            <span class="wpf-err" id="wpf-error-email" role="alert"></span>
                        </div>
                    </div>

                    <!-- Phone + Company row -->
                    <div class="wpf-row-2">

                        <!-- Phone -->
                        <div class="wpf-field">
                            <label class="wpf-label" for="wpf-phone">Phone <span class="wpf-opt">optional</span></label>
                            <div class="wpf-input-wrap">
                                <svg class="wpf-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.39 2 2 0 0 1 3.58 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.16 6.16l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <input type="tel" id="wpf-phone" class="wpf-input" placeholder="+1 (555) 000-0000" autocomplete="tel">
                            </div>
                        </div>

                        <!-- Company Name -->
                        <div class="wpf-field">
                            <label class="wpf-label" for="wpf-company">Company <span class="wpf-opt">optional</span></label>
                            <div class="wpf-input-wrap">
                                <svg class="wpf-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="12"/></svg>
                                <input type="text" id="wpf-company" class="wpf-input" placeholder="Acme Inc." autocomplete="organization">
                            </div>
                        </div>

                    </div>

                    <!-- Amount -->
                    <div class="wpf-field" id="wpf-field-amount">
                        <label class="wpf-label">Amount <abbr class="wpf-req" title="required">*</abbr></label>

                        <div class="wpf-amount-grid" id="wpf-preset-amounts" role="group" aria-label="Select amount">
                            <button type="button" class="wpf-preset-btn" data-amount="500">$500</button>
                            <button type="button" class="wpf-preset-btn" data-amount="800">$800</button>
                            <button type="button" class="wpf-preset-btn" data-amount="1200">$1,200</button>
                            <button type="button" class="wpf-preset-btn" data-amount="1500">$1,500</button>
                            <button type="button" class="wpf-preset-btn wpf-preset-btn--custom" data-amount="custom">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Custom
                            </button>
                        </div>

                        <div class="wpf-custom-amount-wrap" id="wpf-custom-amount-wrap">
                            <div class="wpf-amount-input-wrap">
                                <span class="wpf-currency-sign">$</span>
                                <input type="number" id="wpf-amount" class="wpf-input wpf-amount-input"
                                    placeholder="0.00"
                                    min="<?php echo esc_attr( get_option('wpf_min_amount', 1) ); ?>"
                                    max="<?php echo esc_attr( get_option('wpf_max_amount', 99999) ); ?>"
                                    step="0.01">
                                <span class="wpf-currency-tag">USD</span>
                            </div>
                            <p class="wpf-hint">
                                Min $<?php echo esc_html( number_format( floatval(get_option('wpf_min_amount',1)), 0 ) ); ?> · Max $<?php echo esc_html( number_format( floatval(get_option('wpf_max_amount',99999)), 0 ) ); ?>
                            </p>
                        </div>

                        <input type="hidden" id="wpf-amount-hidden">
                        <span class="wpf-err" id="wpf-error-amount" role="alert"></span>
                    </div>

                </div><!-- .wpf-fields -->

                <button type="button" class="wpf-btn-cta" id="wpf-btn-next-1">
                    Continue to Payment
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>

            </div>

            <!-- ── STEP 2 ──────────────────────────────────────────────────── -->
            <div class="wpf-step wpf-step--hidden" id="wpf-step-2">

                <!-- Summary strip -->
                <div class="wpf-summary-strip" id="wpf-order-summary">
                    <div>
                        <span class="wpf-summary-strip__label">Paying as</span>
                        <strong id="wpf-summary-name" class="wpf-summary-strip__val">—</strong>
                    </div>
                    <div class="wpf-summary-strip__amount-block">
                        <span class="wpf-summary-strip__label">Total</span>
                        <strong id="wpf-summary-amount" class="wpf-summary-strip__amount">$0.00</strong>
                    </div>
                </div>

                <div class="wpf-step-head">
                    <h3 class="wpf-step-title">Secure checkout</h3>
                    <p class="wpf-step-sub">Choose your payment method inside PayPal's secure environment.</p>
                </div>

                <!-- Accepted methods row -->
                <div class="wpf-methods-row">
                    <span class="wpf-method-tag">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Debit / Credit Card
                    </span>
                    <span class="wpf-method-tag">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M20.067 8.478c.492.88.556 2.014.3 3.327-.74 3.806-3.276 5.12-6.514 5.12h-.5a.805.805 0 0 0-.794.68l-.04.22-.63 3.993-.032.17a.804.804 0 0 1-.794.679H8.969a.483.483 0 0 1-.477-.558L9.948 12h2.56c4.106 0 7.39-1.998 8.315-6.287.373-1.768.232-3.22-.442-4.286C22.024 2.836 23 4.867 23 7.5c0 .337-.02.668-.053.993.056-.015.12-.015 0 0zM5.107 21.5h2.394a.805.805 0 0 0 .794-.68l.032-.17.63-3.993.04-.22a.805.805 0 0 1 .794-.68h.5c3.238 0 5.774-1.314 6.514-5.12.256-1.313.192-2.447-.3-3.327C15.37 5.866 13.745 5 11.5 5H6.5a.805.805 0 0 0-.794.68L3.5 19.5a.483.483 0 0 0 .477.558l1.13-.558z"/></svg>
                        PayPal Balance
                    </span>
                    <span class="wpf-method-tag">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        Bank Account
                    </span>
                </div>

                <!-- PayPal button area -->
                <div class="wpf-paypal-box">
                    <div id="wpf-paypal-btn-skeleton" class="wpf-paypal-skeleton">
                        <span class="wpf-spin-ring"></span>
                        <span>Loading secure payment…</span>
                    </div>
                    <div id="wpf-paypal-button-container"></div>
                    <div class="wpf-paypal-error" id="wpf-no-paypal-msg" style="display:none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        PayPal not configured. Add credentials in plugin settings.
                    </div>
                </div>

                <div class="wpf-payment-notice" id="wpf-payment-notice" style="display:none;"></div>

                <button type="button" class="wpf-btn-back" id="wpf-btn-back">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back
                </button>

            </div>

            <!-- ── STEP 3: Success ────────────────────────────────────────── -->
            <div class="wpf-step wpf-step--hidden" id="wpf-step-success">
                <div class="wpf-success-wrap">

                    <div class="wpf-success-check-wrap">
                        <div class="wpf-success-ring-anim"></div>
                        <div class="wpf-success-check">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                    </div>

                    <h2 class="wpf-success-heading">Payment confirmed</h2>
                    <p class="wpf-success-meta">
                        Receipt sent to <strong id="wpf-success-email"></strong>
                    </p>

                    <div class="wpf-success-amount-block">
                        <span id="wpf-success-amount"></span>
                        <span class="wpf-paid-tag">Paid</span>
                    </div>

                    <div class="wpf-receipt-table">
                        <div class="wpf-receipt-row">
                            <span>Transaction ID</span>
                            <span id="wpf-success-txid" class="wpf-mono"></span>
                        </div>
                        <div class="wpf-receipt-row">
                            <span>Name</span>
                            <span id="wpf-success-name"></span>
                        </div>
                        <div class="wpf-receipt-row">
                            <span>Method</span>
                            <span id="wpf-success-method"></span>
                        </div>
                        <div class="wpf-receipt-row">
                            <span>PayPal Order</span>
                            <span id="wpf-success-order" class="wpf-mono"></span>
                        </div>
                        <div class="wpf-receipt-row">
                            <span>Date</span>
                            <span id="wpf-success-date"></span>
                        </div>
                        <div class="wpf-receipt-row" id="wpf-sd-billing" style="display:none;">
                            <span>Billing</span>
                            <span id="wpf-success-billing"></span>
                        </div>
                    </div>

                    <div class="wpf-success-email-note">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Check your inbox for a full receipt
                    </div>

                </div>
            </div>

            <div class="wpf-card-footer">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                256-bit SSL encrypted &nbsp;·&nbsp; Powered by PayPal
            </div>

        </div>
    </main>

</div>
</div>
