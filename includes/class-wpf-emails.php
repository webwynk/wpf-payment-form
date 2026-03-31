<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPF_Emails {

    /**
     * Send confirmation email to the customer.
     */
    public static function send_customer( array $tx ) {
        $to      = $tx['wpf_email'];
        $subject = self::parse_tags( get_option( 'wpf_email_customer_subject', 'Payment Confirmed – #{transaction_id}' ), $tx );
        $body    = self::customer_html( $tx );
        self::send( $to, $subject, $body );
    }

    /**
     * Send notification email to the site admin.
     */
    public static function send_admin( array $tx ) {
        $admin_email = get_option( 'wpf_email_admin_address', get_option( 'admin_email' ) );
        $subject     = self::parse_tags( get_option( 'wpf_email_admin_subject', 'New Payment Received – #{transaction_id}' ), $tx );
        $body        = self::admin_html( $tx );
        self::send( $admin_email, $subject, $body );
    }

    /**
     * Core send wrapper using wp_mail with HTML headers.
     */
    private static function send( $to, $subject, $body ) {
        $from_name  = get_option( 'wpf_email_from_name',  get_bloginfo( 'name' ) );
        $from_email = get_option( 'wpf_email_from_email', get_option( 'admin_email' ) );

        // Ensure from_email is valid — fall back to admin_email
        if ( ! is_email( $from_email ) ) {
            $from_email = get_option( 'admin_email' );
        }

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            "From: {$from_name} <{$from_email}>",
            "Reply-To: {$from_name} <{$from_email}>",
            'MIME-Version: 1.0',
        );

        $sent = wp_mail( $to, $subject, $body, $headers );

        // Log failures so you can see them in debug.log
        if ( ! $sent ) {
            error_log( "[WPF Email] Failed to send to: {$to} | Subject: {$subject}" );
        }

        return $sent;
    }

    /**
     * Simple tag replacer: {name}, {email}, {amount}, etc.
     */
    private static function parse_tags( $text, array $tx ) {
        $billing = json_decode( $tx['wpf_billing_address'] ?? '', true );
        $tags = array(
            '{transaction_id}' => '#' . intval( $tx['wpf_id'] ),
            '{name}'           => esc_html( $tx['wpf_name'] ),
            '{email}'          => esc_html( $tx['wpf_email'] ),
            '{phone}'          => esc_html( $tx['wpf_phone'] ),
            '{company}'        => ! empty( $tx['wpf_company'] ) ? esc_html( $tx['wpf_company'] ) : '—',
            '{amount}'         => '$' . number_format( floatval( $tx['wpf_amount'] ), 2 ),
            '{currency}'       => esc_html( $tx['wpf_currency'] ),
            '{method}'         => ucfirst( esc_html( $tx['wpf_payment_method'] ) ),
            '{paypal_order}'   => esc_html( $tx['wpf_paypal_order_id'] ),
            '{zip}'            => $billing['zip']     ?? '—',
            '{country}'        => $billing['country'] ?? '—',
            '{date}'           => date_i18n( 'F j, Y \a\t g:i A', strtotime( $tx['wpf_created_at'] ) ),
            '{site_name}'      => get_bloginfo( 'name' ),
            '{site_url}'       => home_url(),
        );
        return str_replace( array_keys( $tags ), array_values( $tags ), $text );
    }

    // ── Customer Email HTML ───────────────────────────────────────────────────

    private static function customer_html( array $tx ) {
        $billing    = json_decode( $tx['wpf_billing_address'] ?? '', true );
        $zip        = $billing['zip']     ?? '—';
        $country    = $billing['country'] ?? '—';
        $method_label = $tx['wpf_payment_method'] === 'paypal' ? '🅿️ PayPal' : '💳 Credit / Debit Card';
        $site_name  = get_bloginfo( 'name' );
        $site_url   = home_url();
        $tx_id      = '#' . intval( $tx['wpf_id'] );
        $amount     = '$' . number_format( floatval( $tx['wpf_amount'] ), 2 );
        $date       = date_i18n( 'F j, Y \a\t g:i A', strtotime( $tx['wpf_created_at'] ) );
        $primary    = '#8169f1';
        $accent     = '#ff512a';

        ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Confirmed</title>
</head>
<body style="margin:0;padding:0;background:#f0f2f7;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f7;padding:40px 16px;">
  <tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">

      <!-- Header — matches website green success screen -->
      <tr><td style="background:#ffffff;border-radius:16px 16px 0 0;padding:48px 40px 32px;text-align:center;border-bottom:1px solid #e5e7eb;">

        <!-- Outer glow ring -->
        <div style="display:inline-block;width:90px;height:90px;border-radius:50%;background:rgba(22,163,74,0.12);padding:12px;margin-bottom:20px;">
          <!-- Inner green circle -->
          <div style="width:66px;height:66px;border-radius:50%;background:#16a34a;text-align:center;line-height:66px;">
            <span style="color:#ffffff;font-size:30px;font-weight:900;line-height:66px;display:inline-block;">✓</span>
          </div>
        </div>

        <h1 style="margin:0 0 8px;color:#111827;font-size:26px;font-weight:800;letter-spacing:-0.4px;">Payment confirmed</h1>
        <p style="margin:0 0 20px;color:#6b7280;font-size:14px;">Receipt sent to <strong style="color:#111827;"><?php echo esc_html( $tx['wpf_email'] ); ?></strong></p>

        <!-- Green amount pill -->
        <div style="display:inline-flex;align-items:center;background:#f0fdf4;border:1.5px solid rgba(22,163,74,0.25);border-radius:40px;padding:10px 24px;">
          <span style="font-size:26px;font-weight:900;color:#16a34a;letter-spacing:-0.5px;"><?php echo esc_html( $amount ); ?></span>
          <span style="display:inline-block;background:#16a34a;color:#ffffff;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1px;padding:3px 9px;border-radius:20px;margin-left:12px;">PAID</span>
        </div>

      </td></tr>

      <!-- Body -->
      <tr><td style="background:#ffffff;padding:36px 40px;">

        <p style="margin:0 0 24px;color:#374151;font-size:15px;line-height:1.6;">
          Hi <strong style="color:#111827;"><?php echo esc_html( $tx['wpf_name'] ); ?></strong>, thank you for your payment. Here's your full transaction summary for your records.
        </p>

        <!-- Transaction ID badge -->
        <div style="background:linear-gradient(135deg,rgba(129,105,241,0.08),rgba(255,81,42,0.05));border:1.5px solid rgba(129,105,241,0.20);border-radius:10px;padding:14px 20px;margin-bottom:28px;text-align:center;">
          <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#6b7280;">Transaction ID</span><br>
          <span style="font-size:22px;font-weight:800;color:<?php echo $primary; ?>;"><?php echo esc_html( $tx_id ); ?></span>
        </div>

        <!-- Details table -->
        <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:28px;">
          <?php
          $rows = array(
            array( '📅', 'Date & Time',     esc_html( $date ) ),
            array( '💳', 'Payment Method',  esc_html( $method_label ) ),
            array( '🔖', 'PayPal Order ID', '<code style="font-size:12px;background:#f3f4f6;padding:2px 6px;border-radius:4px;">' . esc_html( $tx['wpf_paypal_order_id'] ) . '</code>' ),
            array( '👤', 'Name',            esc_html( $tx['wpf_name'] ) ),
            array( '📧', 'Email',           esc_html( $tx['wpf_email'] ) ),
            array( '📞', 'Phone',           ! empty( $tx['wpf_phone'] ) ? esc_html( $tx['wpf_phone'] ) : '—' ),
            array( '🏢', 'Company',         ! empty( $tx['wpf_company'] ) ? esc_html( $tx['wpf_company'] ) : '—' ),
          );
          foreach ( $rows as $i => $row ) :
            $bg = $i % 2 === 0 ? '#ffffff' : '#f9fafb';
          ?>
          <tr style="background:<?php echo $bg; ?>;">
            <td style="padding:12px 18px;border-bottom:1px solid #e5e7eb;width:36px;font-size:16px;"><?php echo $row[0]; ?></td>
            <td style="padding:12px 4px 12px 0;border-bottom:1px solid #e5e7eb;font-size:13px;font-weight:600;color:#6b7280;width:140px;"><?php echo $row[1]; ?></td>
            <td style="padding:12px 18px 12px 8px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;"><?php echo $row[2]; ?></td>
          </tr>
          <?php endforeach; ?>
        </table>

        <!-- Status badge -->
        <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;padding:12px 18px;margin-bottom:28px;display:flex;align-items:center;">
          <span style="color:#065f46;font-size:14px;font-weight:600;">✅ &nbsp;Status: <strong>Payment Completed</strong> — Your access has been activated.</span>
        </div>

        <p style="margin:0;color:#6b7280;font-size:13px;line-height:1.7;">
          If you have any questions about this transaction, please contact us and quote your Transaction ID <strong><?php echo esc_html( $tx_id ); ?></strong>.
        </p>

      </td></tr>

      <!-- Footer -->
      <tr><td style="background:#f9fafb;border-top:1px solid #e5e7eb;border-radius:0 0 16px 16px;padding:20px 40px;text-align:center;">
        <p style="margin:0;font-size:12px;color:#9ca3af;">
          This email was sent by <a href="<?php echo esc_url( $site_url ); ?>" style="color:<?php echo $primary; ?>;text-decoration:none;"><?php echo esc_html( $site_name ); ?></a><br>
          256-bit SSL Encrypted &nbsp;·&nbsp; Powered by PayPal
        </p>
      </td></tr>

    </table>
  </td></tr>
</table>
</body>
</html>
        <?php return ob_get_clean();
    }

    // ── Admin Email HTML ──────────────────────────────────────────────────────

    private static function admin_html( array $tx ) {
        $billing      = json_decode( $tx['wpf_billing_address'] ?? '', true );
        $zip          = $billing['zip']     ?? '—';
        $country      = $billing['country'] ?? '—';
        $method_label = $tx['wpf_payment_method'] === 'paypal' ? '🅿️ PayPal' : '💳 Card';
        $site_name    = get_bloginfo( 'name' );
        $admin_url    = admin_url( 'admin.php?page=wpf-transactions' );
        $tx_id        = '#' . intval( $tx['wpf_id'] );
        $amount       = '$' . number_format( floatval( $tx['wpf_amount'] ), 2 );
        $date         = date_i18n( 'F j, Y \a\t g:i A', strtotime( $tx['wpf_created_at'] ) );
        $primary      = '#8169f1';
        $accent       = '#ff512a';

        ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Payment Received</title>
</head>
<body style="margin:0;padding:0;background:#f0f2f7;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f7;padding:40px 16px;">
  <tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">

      <!-- Header -->
      <tr><td style="background:linear-gradient(135deg,<?php echo $primary; ?> 0%,<?php echo $accent; ?> 100%);border-radius:16px 16px 0 0;padding:28px 40px;text-align:center;">
        <p style="margin:0 0 6px;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,0.7);"><?php echo esc_html( $site_name ); ?></p>
        <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;">💸 New Payment Received</h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;"><?php echo esc_html( $date ); ?></p>
      </td></tr>

      <!-- Amount highlight -->
      <tr><td style="background:#ffffff;padding:28px 40px 0;text-align:center;">
        <div style="background:linear-gradient(135deg,rgba(5,150,105,0.08),rgba(5,150,105,0.04));border:1.5px solid #6ee7b7;border-radius:12px;padding:20px;margin-bottom:24px;">
          <p style="margin:0 0 4px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#6b7280;">Amount Received</p>
          <p style="margin:0;font-size:36px;font-weight:900;color:#059669;"><?php echo esc_html( $amount ); ?></p>
          <p style="margin:4px 0 0;font-size:13px;color:#6b7280;">Transaction <?php echo esc_html( $tx_id ); ?> &nbsp;·&nbsp; <?php echo esc_html( $method_label ); ?></p>
        </div>
      </td></tr>

      <!-- Customer details -->
      <tr><td style="background:#ffffff;padding:0 40px 28px;">
        <h3 style="margin:0 0 14px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#6b7280;">Customer Details</h3>
        <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:20px;">
          <?php
          $rows = array(
            array( '👤', 'Name',           esc_html( $tx['wpf_name'] ) ),
            array( '📧', 'Email',          esc_html( $tx['wpf_email'] ) ),
            array( '📞', 'Phone',          ! empty( $tx['wpf_phone'] ) ? esc_html( $tx['wpf_phone'] ) : '—' ),
            array( '🏢', 'Company',        ! empty( $tx['wpf_company'] ) ? esc_html( $tx['wpf_company'] ) : '—' ),
            array( '📍', 'Billing ZIP',    esc_html( $zip ) ),
            array( '🌍', 'Country',        esc_html( $country ) ),
            array( '💳', 'Method',         esc_html( $method_label ) ),
            array( '🔖', 'PayPal Order',   esc_html( $tx['wpf_paypal_order_id'] ) ),
          );
          foreach ( $rows as $i => $row ) :
            $bg = $i % 2 === 0 ? '#ffffff' : '#f9fafb';
          ?>
          <tr style="background:<?php echo $bg; ?>;">
            <td style="padding:10px 16px;border-bottom:1px solid #e5e7eb;width:30px;"><?php echo $row[0]; ?></td>
            <td style="padding:10px 4px 10px 0;border-bottom:1px solid #e5e7eb;font-size:12px;font-weight:600;color:#6b7280;width:130px;"><?php echo $row[1]; ?></td>
            <td style="padding:10px 16px 10px 8px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;"><?php echo $row[2]; ?></td>
          </tr>
          <?php endforeach; ?>
        </table>

        <!-- View in dashboard button -->
        <div style="text-align:center;margin-bottom:8px;">
          <a href="<?php echo esc_url( $admin_url ); ?>"
             style="display:inline-block;background:linear-gradient(135deg,<?php echo $primary; ?>,<?php echo $accent; ?>);color:#ffffff;text-decoration:none;padding:13px 32px;border-radius:8px;font-size:14px;font-weight:700;letter-spacing:0.3px;">
            View in Dashboard →
          </a>
        </div>
      </td></tr>

      <!-- Footer -->
      <tr><td style="background:#f9fafb;border-top:1px solid #e5e7eb;border-radius:0 0 16px 16px;padding:18px 40px;text-align:center;">
        <p style="margin:0;font-size:12px;color:#9ca3af;">
          Automated notification from WPF Payment Form on <strong><?php echo esc_html( $site_name ); ?></strong>
        </p>
      </td></tr>

    </table>
  </td></tr>
</table>
</body>
</html>
        <?php return ob_get_clean();
    }
}
