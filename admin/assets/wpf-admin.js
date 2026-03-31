/* global wpfAdmin, jQuery */
(function ($) {
    'use strict';

    // ── Save Settings ─────────────────────────────────────────────────────────
    $('#wpf-settings-form').on('submit', function (e) {
        e.preventDefault();
        var $form  = $(this);
        var $btn   = $('#wpf-save-settings');
        var $ind   = $('#wpf-saving-indicator');
        var $notice= $('#wpf-settings-notice');

        $btn.prop('disabled', true);
        $ind.show();
        $notice.hide().removeClass('wpf-success wpf-error');

        var data = $form.serializeArray();
        data.push({ name: 'action', value: 'wpf_save_settings' });
        data.push({ name: 'nonce',  value: wpfAdmin.nonce });

        $.ajax({
            url:  wpfAdmin.ajax_url,
            type: 'POST',
            data: $.param(data),
            success: function (res) {
                $notice
                    .addClass(res.success ? 'wpf-success' : 'wpf-error')
                    .html(res.data.message)
                    .show();

                // Update mode badge
                if (res.success) {
                    var mode = $('input[name="wpf_paypal_mode"]:checked').val();
                    var $badge = $('.wpf-mode-badge');
                    $badge.text(mode.charAt(0).toUpperCase() + mode.slice(1) + ' Mode');
                    $badge.removeClass('sandbox live').addClass(mode);
                }
            },
            error: function () {
                $notice.addClass('wpf-error').html('A network error occurred. Please try again.').show();
            },
            complete: function () {
                $btn.prop('disabled', false);
                $ind.hide();
                $('html, body').animate({ scrollTop: $notice.offset().top - 60 }, 300);
            },
        });
    });

    // ── Password Toggle ───────────────────────────────────────────────────────
    $('.wpf-toggle-password').on('click', function () {
        var target = $(this).data('target');
        var $input = $('#' + target);
        var $icon  = $(this).find('.dashicons');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });

    // ── Copy Shortcode ────────────────────────────────────────────────────────
    $('.wpf-copy-btn').on('click', function () {
        var textId = $(this).data('clipboard');
        var text   = document.getElementById(textId).textContent;
        var $btn   = $(this);

        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                $btn.html('<span class="dashicons dashicons-yes"></span> Copied!');
                setTimeout(function () {
                    $btn.html('<span class="dashicons dashicons-admin-page"></span> Copy');
                }, 2000);
            });
        } else {
            // Fallback
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
            $btn.html('<span class="dashicons dashicons-yes"></span> Copied!');
            setTimeout(function () {
                $btn.html('<span class="dashicons dashicons-admin-page"></span> Copy');
            }, 2000);
        }
    });

    // ── CSV Export via direct link (no AJAX needed) ───────────────────────────
    // Already handled by href in the template

}(jQuery));
