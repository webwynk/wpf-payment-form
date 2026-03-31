/* global wpfConfig, paypal */
(function () {
    'use strict';

    // ── Guard ─────────────────────────────────────────────────────────────────
    var wrap = document.getElementById('wpf-payment-form-wrap');
    if (!wrap) return;

    // ── State ─────────────────────────────────────────────────────────────────
    var state = {
        currentStep:   1,
        name:          '',
        email:         '',
        phone:         '',
        company:       '',
        amount:        0,
        transactionId: null,
    };

    var selectedAmount = 0;

    // ── DOM Refs ──────────────────────────────────────────────────────────────
    var step1        = document.getElementById('wpf-step-1');
    var step2        = document.getElementById('wpf-step-2');
    var stepSuccess  = document.getElementById('wpf-step-success');
    var btnNext1     = document.getElementById('wpf-btn-next-1');
    var btnBack      = document.getElementById('wpf-btn-back');
    var paymentNotice= document.getElementById('wpf-payment-notice');
    var summaryName  = document.getElementById('wpf-summary-name');
    var summaryAmount= document.getElementById('wpf-summary-amount');
    var noPayPalMsg  = document.getElementById('wpf-no-paypal-msg');
    var ppSkeleton   = document.getElementById('wpf-paypal-btn-skeleton');
    var bc1          = document.getElementById('wpf-bc-1');
    var bc2          = document.getElementById('wpf-bc-2');
    var inputName    = document.getElementById('wpf-name');
    var inputEmail   = document.getElementById('wpf-email');
    var inputAmount  = document.getElementById('wpf-amount');
    var amountHidden = document.getElementById('wpf-amount-hidden');

    // ── Helpers ───────────────────────────────────────────────────────────────
    function fmtAmount(n) {
        return '$' + parseFloat(n).toFixed(2);
    }

    function ajaxPost(action, data, callback) {
        var body = new URLSearchParams(
            Object.assign({ action: action, nonce: wpfConfig.nonce }, data)
        );
        fetch(wpfConfig.ajax_url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body.toString(),
        })
        .then(function (r) { return r.json(); })
        .then(callback)
        .catch(function () {
            showNotice('error', '⚠️ A network error occurred. Please try again.');
        });
    }

    function showNotice(type, msg) {
        if (!paymentNotice) return;
        paymentNotice.className = 'wpf-payment-notice wpf-payment-notice--' + type;
        paymentNotice.innerHTML = msg;
        paymentNotice.style.display = 'flex';
        paymentNotice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideNotice() {
        if (paymentNotice) paymentNotice.style.display = 'none';
    }

    function setFieldError(fieldId, errorId, msg) {
        var field = document.getElementById(fieldId);
        var error = document.getElementById(errorId);
        if (field) {
            var input = field.querySelector('.wpf-input');
            if (input) input.classList.toggle('wpf-field--error', !!msg);
        }
        if (error) error.textContent = msg || '';
    }
    // ── Step Navigation ───────────────────────────────────────────────────────
    function goToStep(n) {
        state.currentStep = n;

        step1.classList.toggle('wpf-step--hidden',       n !== 1);
        step2.classList.toggle('wpf-step--hidden',       n !== 2);
        stepSuccess.classList.toggle('wpf-step--hidden', n !== 3);

        // Update breadcrumb
        if (bc1 && bc2) {
            bc1.className = 'wpf-breadcrumb__item';
            bc2.className = 'wpf-breadcrumb__item';
            if (n === 1) {
                bc1.classList.add('wpf-breadcrumb__item--active');
            } else if (n === 2) {
                bc1.classList.add('wpf-breadcrumb__item--done');
                bc1.querySelector('.wpf-bc-num').textContent = '✓';
                bc2.classList.add('wpf-breadcrumb__item--active');
            } else {
                bc1.classList.add('wpf-breadcrumb__item--done');
                bc2.classList.add('wpf-breadcrumb__item--done');
                bc1.querySelector('.wpf-bc-num').textContent = '✓';
                bc2.querySelector('.wpf-bc-num').textContent = '✓';
            }
        }

        wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ── Success Screen ────────────────────────────────────────────────────────
    function showSuccess(data) {
        function set(id, val) {
            var el = document.getElementById(id);
            if (el && val) el.textContent = val;
        }
        set('wpf-success-email',  data.email  || state.email);
        set('wpf-success-amount', data.amount || fmtAmount(state.amount));
        set('wpf-success-txid',   data.transaction_id || '');
        set('wpf-success-name',   data.name   || state.name);
        set('wpf-success-method', data.method || 'PayPal');
        set('wpf-success-order',  data.paypal_order || '');
        set('wpf-success-date',   data.date   || '');

        if (data.billing) {
            set('wpf-success-billing', data.billing);
            var row = document.getElementById('wpf-sd-billing');
            if (row) row.style.display = 'flex';
        }
        goToStep(3);
    }

    // ── Validation ────────────────────────────────────────────────────────────
    function validateStep1() {
        var ok = true;

        var name = inputName ? inputName.value.trim() : '';
        if (name.length < 2) {
            setFieldError('wpf-field-name', 'wpf-error-name', 'Please enter your full name.');
            ok = false;
        } else { setFieldError('wpf-field-name', 'wpf-error-name', ''); }

        var email = inputEmail ? inputEmail.value.trim() : '';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            setFieldError('wpf-field-email', 'wpf-error-email', 'Please enter a valid email address.');
            ok = false;
        } else { setFieldError('wpf-field-email', 'wpf-error-email', ''); }

        var minAmt   = parseFloat(wpfConfig.min_amount);
        var maxAmt   = parseFloat(wpfConfig.max_amount);
        var hasPreset = document.querySelector('.wpf-preset-btn--active');

        if (!hasPreset) {
            setFieldError('wpf-field-amount', 'wpf-error-amount', 'Please select or enter a payment amount.');
            ok = false;
        } else if (isNaN(selectedAmount) || selectedAmount < minAmt || selectedAmount > maxAmt) {
            setFieldError('wpf-field-amount', 'wpf-error-amount',
                'Amount must be between ' + fmtAmount(minAmt) + ' and ' + fmtAmount(maxAmt) + '.');
            ok = false;
        } else {
            setFieldError('wpf-field-amount', 'wpf-error-amount', '');
        }

        return ok;
    }

    // ── Preset Amount Buttons ─────────────────────────────────────────────────
    var presetBtns       = document.querySelectorAll('.wpf-preset-btn');
    var customAmountWrap = document.getElementById('wpf-custom-amount-wrap');

    if (customAmountWrap) customAmountWrap.style.display = 'none';

    presetBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            presetBtns.forEach(function (b) { b.classList.remove('wpf-preset-btn--active'); });
            btn.classList.add('wpf-preset-btn--active');

            var val = btn.getAttribute('data-amount');
            if (val === 'custom') {
                if (customAmountWrap) {
                    customAmountWrap.style.display   = 'block';
                    customAmountWrap.style.animation = 'wpf-custom-amount-in 0.3s cubic-bezier(0.22,1,0.36,1) both';
                }
                if (inputAmount) inputAmount.focus();
                selectedAmount = 0;
                if (amountHidden) amountHidden.value = '';
            } else {
                if (customAmountWrap) customAmountWrap.style.display = 'none';
                selectedAmount = parseFloat(val);
                if (amountHidden) amountHidden.value = selectedAmount;
                if (inputAmount)  inputAmount.value  = '';
                setFieldError('wpf-field-amount', 'wpf-error-amount', '');
            }
        });
    });

    if (inputAmount) {
        inputAmount.addEventListener('input', function () {
            selectedAmount = parseFloat(inputAmount.value) || 0;
            if (amountHidden) amountHidden.value = selectedAmount;
        });
    }

    // ── Step 1 → 2 ───────────────────────────────────────────────────────────
    if (btnNext1) {
        btnNext1.addEventListener('click', function () {
            if (!validateStep1()) return;

            state.name   = inputName  ? inputName.value.trim()  : '';
            state.email  = inputEmail ? inputEmail.value.trim() : '';
            state.phone  = (document.getElementById('wpf-phone')   || {}).value || '';
            state.company= (document.getElementById('wpf-company') || {}).value || '';
            state.amount = selectedAmount;

            if (summaryName)   summaryName.textContent   = state.name;
            if (summaryAmount) summaryAmount.textContent = fmtAmount(state.amount);

            hideNotice();
            goToStep(2);
        });
    }

    // ── Back ──────────────────────────────────────────────────────────────────
    if (btnBack) {
        btnBack.addEventListener('click', function () {
            hideNotice();
            goToStep(1);
        });
    }

    // ── Live Validation ───────────────────────────────────────────────────────
    function liveValidate(input, validate) {
        if (!input) return;
        input.addEventListener('blur',  validate);
        input.addEventListener('input', function () {
            if (input.classList.contains('wpf-field--error')) validate();
        });
    }

    liveValidate(inputName, function () {
        var v = inputName.value.trim();
        setFieldError('wpf-field-name', 'wpf-error-name', v.length < 2 ? 'Please enter your full name.' : '');
        if (v.length >= 2) inputName.classList.add('wpf-field--valid');
    });

    liveValidate(inputEmail, function () {
        var v  = inputEmail.value.trim();
        var ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
        setFieldError('wpf-field-email', 'wpf-error-email', !ok ? 'Please enter a valid email.' : '');
        if (ok) inputEmail.classList.add('wpf-field--valid');
    });

    liveValidate(inputAmount, function () {
        var v   = parseFloat(inputAmount.value);
        var min = parseFloat(wpfConfig.min_amount);
        var max = parseFloat(wpfConfig.max_amount);
        if (!isNaN(v) && v >= min && v <= max) {
            setFieldError('wpf-field-amount', 'wpf-error-amount', '');
            inputAmount.classList.add('wpf-field--valid');
        }
    });

    // ── Create PayPal Order (PHP) ─────────────────────────────────────────────
    function createPayPalOrder() {
        return new Promise(function (resolve, reject) {
            ajaxPost('wpf_create_order', {
                name:    state.name,
                email:   state.email,
                phone:   state.phone,
                company: state.company,
                amount:  state.amount,
            }, function (res) {
                if (res.success) {
                    state.transactionId = res.data.transaction_id;
                    resolve(res.data.order_id);
                } else {
                    showNotice('error', '⚠️ ' + (res.data.message || 'Could not create order.'));
                    reject(new Error(res.data.message));
                }
            });
        });
    }

    // ── Capture PayPal Order (PHP) ────────────────────────────────────────────
    function capturePayPalOrder(orderID) {
        return new Promise(function (resolve, reject) {
            ajaxPost('wpf_capture_order', {
                order_id:       orderID,
                transaction_id: state.transactionId,
                payment_method: 'paypal',
                zip:            '',
                country:        '',
            }, function (res) {
                if (res.success) {
                    resolve(res.data);
                } else {
                    reject(new Error(res.data.message || 'Capture failed.'));
                }
            });
        });
    }

    // ── Init PayPal Button ────────────────────────────────────────────────────
    function initPayPal() {
        if (wpfConfig.has_paypal !== '1' || typeof paypal === 'undefined') {
            if (ppSkeleton)  ppSkeleton.style.display  = 'none';
            if (noPayPalMsg) noPayPalMsg.style.display = 'flex';
            return;
        }

        // SDK ready — hide skeleton
        if (ppSkeleton) ppSkeleton.style.display = 'none';

        paypal.Buttons({
            // No fundingSource = shows ALL eligible methods PayPal supports
            // in the popup (PayPal wallet, card, bank, Pay Later, etc.)
            style: {
                layout: 'vertical',
                color:  'gold',
                shape:  'rect',
                label:  'paypal',
                height: 52,
            },

            createOrder: function () {
                hideNotice();
                return createPayPalOrder();
            },

            onApprove: function (data) {
                showNotice('success', '<span class="wpf-spinner"></span>&nbsp; Confirming your payment, please wait…');
                return capturePayPalOrder(data.orderID)
                    .then(function (txData) {
                        hideNotice();
                        showSuccess(txData);
                    })
                    .catch(function (err) {
                        showNotice('error', '⚠️ ' + (err.message || 'Payment capture failed. Please contact support.'));
                    });
            },

            onError: function (err) {
                console.error('PayPal error:', err);
                showNotice('error', '⚠️ Something went wrong with PayPal. Please try again.');
            },

            onCancel: function () {
                showNotice('error', 'Payment cancelled. You can click the button above to try again.');
            },

        }).render('#wpf-paypal-button-container')
          .catch(function (err) {
              console.error('PayPal render error:', err);
              if (ppSkeleton)  ppSkeleton.style.display  = 'none';
              if (noPayPalMsg) noPayPalMsg.style.display = 'flex';
          });
    }

    // ── Lazy-init PayPal on Step 2 ────────────────────────────────────────────
    var paypalInited = false;
    var _origGoTo    = goToStep;

    goToStep = function (n) { // eslint-disable-line no-func-assign
        _origGoTo(n);
        if (n === 2 && !paypalInited) {
            paypalInited = true;
            var waited = 0;
            var poll   = setInterval(function () {
                if (typeof paypal !== 'undefined') {
                    clearInterval(poll);
                    initPayPal();
                } else {
                    waited += 250;
                    if (waited >= 10000) {
                        clearInterval(poll);
                        if (ppSkeleton)  ppSkeleton.style.display  = 'none';
                        if (noPayPalMsg) noPayPalMsg.style.display = 'flex';
                    }
                }
            }, 250);
        }
    };

    // ── Boot ──────────────────────────────────────────────────────────────────
    goToStep(1);

}());
