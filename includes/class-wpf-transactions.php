<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPF_Transactions {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'wpf_transactions';
    }

    /**
     * Insert a new transaction. Returns the new row ID.
     */
    public function create( array $data ) {
        global $wpdb;

        $defaults = array(
            'wpf_name'           => '',
            'wpf_email'          => '',
            'wpf_phone'          => '',
            'wpf_company'        => '',
            'wpf_amount'         => 0.00,
            'wpf_currency'       => 'USD',
            'wpf_payment_method' => 'unknown',
            'wpf_paypal_order_id'=> '',
            'wpf_status'         => 'pending',
            'wpf_billing_address'=> '',
            'wpf_created_at'     => current_time( 'mysql' ),
            'wpf_updated_at'     => current_time( 'mysql' ),
        );

        $row = wp_parse_args( $data, $defaults );

        $formats = array( '%s','%s','%s','%s','%f','%s','%s','%s','%s','%s','%s','%s' );

        $wpdb->insert( $this->table, $row, $formats );
        return $wpdb->insert_id;
    }

    /**
     * Update an existing transaction by ID.
     */
    public function update( $id, array $data ) {
        global $wpdb;
        $data['wpf_updated_at'] = current_time( 'mysql' );
        $wpdb->update( $this->table, $data, array( 'wpf_id' => $id ) );
    }

    /**
     * Get a single transaction by ID.
     */
    public function get( $id ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->table} WHERE wpf_id = %d", $id ),
            ARRAY_A
        );
    }

    /**
     * Get paginated list of transactions with optional filters.
     */
    public function get_list( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'per_page' => 20,
            'page'     => 1,
            'status'   => '',
            'method'   => '',
            'search'   => '',
            'orderby'  => 'wpf_created_at',
            'order'    => 'DESC',
        );
        $args = wp_parse_args( $args, $defaults );

        $where   = array( '1=1' );
        $prepare = array();

        if ( ! empty( $args['status'] ) ) {
            $where[]   = 'wpf_status = %s';
            $prepare[] = $args['status'];
        }
        if ( ! empty( $args['method'] ) ) {
            $where[]   = 'wpf_payment_method = %s';
            $prepare[] = $args['method'];
        }
        if ( ! empty( $args['search'] ) ) {
            $where[]   = '( wpf_name LIKE %s OR wpf_email LIKE %s )';
            $like      = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $prepare[] = $like;
            $prepare[] = $like;
        }

        $where_sql = implode( ' AND ', $where );

        $allowed_orderby = array( 'wpf_id','wpf_name','wpf_email','wpf_amount','wpf_status','wpf_created_at' );
        $orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'wpf_created_at';
        $order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        $offset = ( intval( $args['page'] ) - 1 ) * intval( $args['per_page'] );

        $sql = "SELECT * FROM {$this->table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $prepare[] = intval( $args['per_page'] );
        $prepare[] = $offset;

        if ( ! empty( $prepare ) ) {
            $sql = $wpdb->prepare( $sql, $prepare );
        }

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Count transactions matching filters.
     */
    public function count( $args = array() ) {
        global $wpdb;

        $where   = array( '1=1' );
        $prepare = array();

        if ( ! empty( $args['status'] ) ) {
            $where[]   = 'wpf_status = %s';
            $prepare[] = $args['status'];
        }
        if ( ! empty( $args['method'] ) ) {
            $where[]   = 'wpf_payment_method = %s';
            $prepare[] = $args['method'];
        }
        if ( ! empty( $args['search'] ) ) {
            $where[]   = '( wpf_name LIKE %s OR wpf_email LIKE %s )';
            $like      = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $prepare[] = $like;
            $prepare[] = $like;
        }

        $where_sql = implode( ' AND ', $where );
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where_sql}";

        if ( ! empty( $prepare ) ) {
            $sql = $wpdb->prepare( $sql, $prepare );
        }

        return intval( $wpdb->get_var( $sql ) );
    }

    /**
     * Get total revenue (completed only).
     */
    public function get_total_revenue() {
        global $wpdb;
        return floatval( $wpdb->get_var(
            "SELECT SUM(wpf_amount) FROM {$this->table} WHERE wpf_status = 'completed'"
        ) );
    }

    /**
     * Delete a transaction.
     */
    public function delete( $id ) {
        global $wpdb;
        $wpdb->delete( $this->table, array( 'wpf_id' => $id ), array( '%d' ) );
    }
}
