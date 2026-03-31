<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$tx      = new WPF_Transactions();
$page    = isset( $_GET['paged'] )  ? max(1, intval($_GET['paged']))        : 1;
$status  = isset( $_GET['status'] ) ? sanitize_text_field($_GET['status'])  : '';
$method  = isset( $_GET['method'] ) ? sanitize_text_field($_GET['method'])  : '';
$search  = isset( $_GET['s'] )      ? sanitize_text_field($_GET['s'])       : '';

$per_page = 20;
$args     = array( 'page'=>$page, 'per_page'=>$per_page, 'status'=>$status, 'method'=>$method, 'search'=>$search );
$rows     = $tx->get_list( $args );
$total    = $tx->count( $args );
$pages    = ceil( $total / $per_page );
$revenue  = $tx->get_total_revenue();

$status_labels = array(
    'pending'   => array( 'label'=>'Pending',   'class'=>'wpf-badge-pending' ),
    'completed' => array( 'label'=>'Completed', 'class'=>'wpf-badge-completed' ),
    'failed'    => array( 'label'=>'Failed',    'class'=>'wpf-badge-failed' ),
    'refunded'  => array( 'label'=>'Refunded',  'class'=>'wpf-badge-refunded' ),
);
?>

<div class="wpf-admin-wrap">

    <div class="wpf-admin-header">
        <div class="wpf-admin-header__logo">
            <span class="wpf-admin-header__icon dashicons dashicons-list-view"></span>
            <div>
                <h1>Transactions</h1>
                <p>All payment records from your WPF Payment Form.</p>
            </div>
        </div>
        <div class="wpf-admin-header__actions">
            <a href="<?php echo esc_url( add_query_arg( array('action'=>'wpf_export_csv','nonce'=>wp_create_nonce('wpf_admin_nonce')), admin_url('admin-ajax.php')) ); ?>"
               class="wpf-btn-secondary">
                <span class="dashicons dashicons-download"></span> Export CSV
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="wpf-stats-row">
        <div class="wpf-stat-card">
            <span class="wpf-stat-icon dashicons dashicons-chart-area"></span>
            <div class="wpf-stat-body">
                <span class="wpf-stat-value"><?php echo number_format( $tx->count(), 0 ); ?></span>
                <span class="wpf-stat-label">Total Transactions</span>
            </div>
        </div>
        <div class="wpf-stat-card">
            <span class="wpf-stat-icon dashicons dashicons-yes-alt"></span>
            <div class="wpf-stat-body">
                <span class="wpf-stat-value"><?php echo number_format( $tx->count(array('status'=>'completed')), 0 ); ?></span>
                <span class="wpf-stat-label">Completed</span>
            </div>
        </div>
        <div class="wpf-stat-card">
            <span class="wpf-stat-icon dashicons dashicons-warning"></span>
            <div class="wpf-stat-body">
                <span class="wpf-stat-value"><?php echo number_format( $tx->count(array('status'=>'pending')), 0 ); ?></span>
                <span class="wpf-stat-label">Pending</span>
            </div>
        </div>
        <div class="wpf-stat-card wpf-stat-card--revenue">
            <span class="wpf-stat-icon dashicons dashicons-money-alt"></span>
            <div class="wpf-stat-body">
                <span class="wpf-stat-value">$<?php echo number_format( $revenue, 2 ); ?></span>
                <span class="wpf-stat-label">Total Revenue</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="wpf-filter-bar">
        <form method="GET" class="wpf-filter-form">
            <input type="hidden" name="page" value="wpf-transactions">

            <input type="text" name="s" class="wpf-input wpf-search-input"
                placeholder="🔍 Search name or email…"
                value="<?php echo esc_attr($search); ?>">

            <select name="status" class="wpf-input wpf-filter-select">
                <option value="">All Statuses</option>
                <?php foreach ( $status_labels as $val => $info ) : ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($status,$val); ?>>
                        <?php echo esc_html($info['label']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="method" class="wpf-input wpf-filter-select">
                <option value="">All Methods</option>
                <option value="paypal" <?php selected($method,'paypal'); ?>>PayPal</option>
                <option value="card"   <?php selected($method,'card');   ?>>Card</option>
            </select>

            <button type="submit" class="wpf-btn-primary">Filter</button>
            <a href="<?php echo esc_url( admin_url('admin.php?page=wpf-transactions') ); ?>" class="wpf-btn-ghost">Reset</a>
        </form>
    </div>

    <!-- Table -->
    <div class="wpf-table-wrap">
        <?php if ( empty($rows) ) : ?>
            <div class="wpf-empty-state">
                <span class="dashicons dashicons-database-view"></span>
                <p>No transactions found.</p>
            </div>
        <?php else : ?>
        <table class="wpf-table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>PayPal Order ID</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $rows as $row ) :
                    $info = isset($status_labels[$row['wpf_status']]) ? $status_labels[$row['wpf_status']] : array('label'=>$row['wpf_status'],'class'=>'');
                    $billing = json_decode($row['wpf_billing_address'], true);
                    $billing_str = $billing ? implode(' / ', array_filter($billing)) : '—';
                ?>
                <tr>
                    <td><strong>#<?php echo intval($row['wpf_id']); ?></strong></td>
                    <td>
                        <strong><?php echo esc_html($row['wpf_name']); ?></strong><br>
                        <small><?php echo esc_html($row['wpf_email']); ?></small><br>
                        <?php if ( ! empty($row['wpf_phone']) )   : ?><small>📞 <?php echo esc_html($row['wpf_phone']); ?></small><br><?php endif; ?>
                        <?php if ( ! empty($row['wpf_company']) ) : ?><small>🏢 <?php echo esc_html($row['wpf_company']); ?></small><?php endif; ?>
                    </td>
                    <td class="wpf-amount-cell">
                        <strong><?php echo esc_html($row['wpf_currency']); ?> <?php echo number_format(floatval($row['wpf_amount']),2); ?></strong>
                    </td>
                    <td>
                        <span class="wpf-method-badge wpf-method-<?php echo esc_attr($row['wpf_payment_method']); ?>">
                            <?php echo $row['wpf_payment_method'] === 'paypal' ? '🅿️ PayPal' : '💳 Card'; ?>
                        </span>
                    </td>
                    <td>
                        <code class="wpf-order-id"><?php echo esc_html($row['wpf_paypal_order_id'] ?: '—'); ?></code>
                        <?php if($row['wpf_billing_address']): ?>
                            <br><small class="wpf-billing">📍 <?php echo esc_html($billing_str); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="wpf-badge <?php echo esc_attr($info['class']); ?>"><?php echo esc_html($info['label']); ?></span></td>
                    <td>
                        <small><?php echo esc_html( date_i18n( get_option('date_format') . ' H:i', strtotime($row['wpf_created_at']) ) ); ?></small>
                    </td>
                    <td>
                        <a href="https://www.paypal.com/activity/payment/<?php echo esc_attr($row['wpf_paypal_order_id']); ?>"
                           target="_blank" class="wpf-action-link" title="View on PayPal">
                            <span class="dashicons dashicons-external"></span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ( $pages > 1 ) : ?>
        <div class="wpf-pagination">
            <?php for ( $i = 1; $i <= $pages; $i++ ) : ?>
                <a href="<?php echo esc_url( add_query_arg( array( 'paged'=>$i, 'status'=>$status, 'method'=>$method, 's'=>$search ) ) ); ?>"
                   class="wpf-page-btn <?php echo $i === $page ? 'wpf-page-btn--active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>

</div>
