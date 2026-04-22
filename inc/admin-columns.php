<?php
/**
 * KWL Coupon WP — Admin Columns
 *
 * Adds useful custom columns to the WordPress admin list tables
 * for Stores and Coupons, replacing the default columns with
 * contextually relevant data.
 *
 * Store columns:  Logo | Name | Slug | Coupons | Network | Featured | Date
 * Coupon columns: Type | Title | Store | Code | Discount | Expiry | Clicks | Date
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   STORE COLUMNS
   ============================================================================= */

/**
 * Define columns for the Store list table.
 *
 * @param  array $columns
 * @return array
 */
function kwl_store_columns( array $columns ): array {

    return [
        'cb'            => $columns['cb'],
        'kwl_logo'      => __( 'Logo',     'kwl-coupon-wp' ),
        'title'         => __( 'Store',    'kwl-coupon-wp' ),
        'kwl_slug'      => __( 'Slug',     'kwl-coupon-wp' ),
        'kwl_coupons'   => __( 'Coupons',  'kwl-coupon-wp' ),
        'kwl_network'   => __( 'Network',  'kwl-coupon-wp' ),
        'kwl_featured'  => __( 'Featured', 'kwl-coupon-wp' ),
        'kwl_clicks'    => __( 'Clicks',   'kwl-coupon-wp' ),
        'date'          => __( 'Date',     'kwl-coupon-wp' ),
    ];

}
add_filter( 'manage_kwl_store_posts_columns', 'kwl_store_columns' );


/**
 * Render store column content.
 *
 * @param string $column   Column ID.
 * @param int    $post_id  Store post ID.
 */
function kwl_store_column_content( string $column, int $post_id ): void {

    switch ( $column ) {

        case 'kwl_logo':
            $logo_url = kwl_get_store_logo_url( $post_id, 'kwl-store-logo-sm' );
            if ( $logo_url ) {
                echo '<img src="' . esc_url( $logo_url ) . '" alt="" width="40" height="40" '
                    . 'style="border-radius:6px; object-fit:contain; border:1px solid #eee;">';
            } else {
                $initial = mb_strtoupper( mb_substr( get_the_title( $post_id ), 0, 1 ) );
                echo '<div style="width:40px;height:40px;background:#eff6ff;border-radius:6px;display:flex;align-items:center;justify-content:center;font-weight:700;color:#2563eb;font-size:16px;">'
                    . esc_html( $initial ) . '</div>';
            }
            break;

        case 'kwl_slug':
            $slug = get_post_field( 'post_name', $post_id );
            if ( $slug ) {
                echo '<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">'
                    . esc_html( $slug ) . '</code>';
            } else {
                echo '<span style="color:#94a3b8;">—</span>';
            }
            break;

        case 'kwl_coupons':
            $count = kwl_get_store_coupon_count( $post_id );
            if ( $count > 0 ) {
                $url = add_query_arg( [
                    'post_type'           => 'kwl_coupon',
                    'kwl_filter_store'    => $post_id,
                ], admin_url( 'edit.php' ) );
                printf(
                    '<a href="%s"><strong>%d</strong></a>',
                    esc_url( $url ),
                    $count
                );
            } else {
                echo '<span style="color:#94a3b8;">0</span>';
            }
            break;

        case 'kwl_network':
            $network = get_post_meta( $post_id, '_kwl_store_network', true );
            $labels  = [
                'shareasale' => 'ShareASale',
                'cj'         => 'CJ',
                'impact'     => 'Impact',
                'awin'       => 'Awin',
                'rakuten'    => 'Rakuten',
                'amazon'     => 'Amazon',
                'direct'     => 'Direct',
                'other'      => 'Other',
            ];
            echo esc_html( $labels[ $network ] ?? '—' );
            break;

        case 'kwl_featured':
            $featured = get_post_meta( $post_id, '_kwl_store_featured', true );
            if ( $featured === '1' ) {
                echo '<span style="color:#16a34a;" title="' . esc_attr__( 'Featured', 'kwl-coupon-wp' ) . '">★</span>';
            } else {
                echo '<span style="color:#e2e8f0;">★</span>';
            }
            break;

        case 'kwl_clicks':
            $clicks = (int) get_post_meta( $post_id, '_kwl_store_click_count', true );
            echo number_format( $clicks );
            break;

    }

}
add_action( 'manage_kwl_store_posts_custom_column', 'kwl_store_column_content', 10, 2 );


/**
 * Make store columns sortable.
 *
 * @param  array $columns
 * @return array
 */
function kwl_store_sortable_columns( array $columns ): array {
    $columns['kwl_coupons'] = 'kwl_coupons';
    $columns['kwl_clicks']  = 'kwl_clicks';
    return $columns;
}
add_filter( 'manage_edit-kwl_store_sortable_columns', 'kwl_store_sortable_columns' );


/* =============================================================================
   COUPON COLUMNS
   ============================================================================= */

/**
 * Define columns for the Coupon list table.
 *
 * @param  array $columns
 * @return array
 */
function kwl_coupon_columns( array $columns ): array {

    return [
        'cb'            => $columns['cb'],
        'kwl_type'      => __( 'Type',     'kwl-coupon-wp' ),
        'title'         => __( 'Coupon',   'kwl-coupon-wp' ),
        'kwl_store'     => __( 'Store',    'kwl-coupon-wp' ),
        'kwl_code'      => __( 'Code',     'kwl-coupon-wp' ),
        'kwl_discount'  => __( 'Discount', 'kwl-coupon-wp' ),
        'kwl_expiry'    => __( 'Expiry',   'kwl-coupon-wp' ),
        'kwl_status'    => __( 'Status',   'kwl-coupon-wp' ),
        'kwl_clicks'    => __( 'Clicks',   'kwl-coupon-wp' ),
        'date'          => __( 'Date',     'kwl-coupon-wp' ),
    ];

}
add_filter( 'manage_kwl_coupon_posts_columns', 'kwl_coupon_columns' );


/**
 * Render coupon column content.
 *
 * @param string $column   Column ID.
 * @param int    $post_id  Coupon post ID.
 */
function kwl_coupon_column_content( string $column, int $post_id ): void {

    switch ( $column ) {

        case 'kwl_type':
            $type = kwl_get_coupon_type( $post_id );
            $icons = [
                'code'         => '🏷️',
                'deal'         => '🎁',
                'freeshipping' => '🚚',
            ];
            $labels = [
                'code'         => __( 'Code',          'kwl-coupon-wp' ),
                'deal'         => __( 'Deal',          'kwl-coupon-wp' ),
                'freeshipping' => __( 'Free Shipping', 'kwl-coupon-wp' ),
            ];
            echo '<span title="' . esc_attr( $labels[ $type ] ?? '' ) . '" style="font-size:18px;">'
                . esc_html( $icons[ $type ] ?? '🏷️' ) . '</span>';
            break;

        case 'kwl_store':
            $store_id = kwl_get_coupon_store_id( $post_id );
            if ( $store_id ) {
                $edit_url = get_edit_post_link( $store_id );
                echo '<a href="' . esc_url( $edit_url ) . '">' . esc_html( get_the_title( $store_id ) ) . '</a>';
            } else {
                echo '<span style="color:#dc2626;">' . esc_html__( 'No store', 'kwl-coupon-wp' ) . '</span>';
            }
            break;

        case 'kwl_code':
            $code = kwl_get_coupon_code( $post_id );
            $type = kwl_get_coupon_type( $post_id );
            if ( $type === 'code' && ! empty( $code ) ) {
                echo '<code style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:12px;letter-spacing:.05em;">'
                    . esc_html( $code ) . '</code>';
            } elseif ( $type === 'deal' ) {
                echo '<span style="color:#94a3b8;font-size:12px;">' . esc_html__( 'No code', 'kwl-coupon-wp' ) . '</span>';
            } elseif ( $type === 'freeshipping' ) {
                echo '<span style="color:#16a34a;font-size:12px;">🚚</span>';
            }
            break;

        case 'kwl_discount':
            $discount = kwl_get_coupon_discount( $post_id );
            echo ! empty( $discount )
                ? '<strong>' . esc_html( $discount ) . '</strong>'
                : '<span style="color:#94a3b8;">—</span>';
            break;

        case 'kwl_expiry':
            $expiry  = get_post_meta( $post_id, '_kwl_coupon_expiry', true );
            $expired = kwl_is_expired( $post_id );
            $soon    = kwl_is_expiring_soon( $post_id );

            if ( empty( $expiry ) ) {
                echo '<span style="color:#16a34a;font-size:12px;">' . esc_html__( 'No expiry', 'kwl-coupon-wp' ) . '</span>';
            } elseif ( $expired ) {
                echo '<span style="color:#dc2626;font-size:12px;">⚠ ' . esc_html( $expiry ) . '</span>';
            } elseif ( $soon ) {
                echo '<span style="color:#d97706;font-size:12px;">⏰ ' . esc_html( $expiry ) . '</span>';
            } else {
                echo '<span style="font-size:12px;">' . esc_html( $expiry ) . '</span>';
            }
            break;

        case 'kwl_status':
            $verified  = kwl_is_coupon_verified( $post_id );
            $exclusive = kwl_is_coupon_exclusive( $post_id );
            $expired   = kwl_is_expired( $post_id );

            $badges = [];

            if ( $expired ) {
                $badges[] = '<span style="background:#fee2e2;color:#b91c1c;padding:1px 6px;border-radius:9999px;font-size:11px;font-weight:600;">'
                    . esc_html__( 'Expired', 'kwl-coupon-wp' ) . '</span>';
            } else {
                if ( $verified ) {
                    $badges[] = '<span style="background:#dcfce7;color:#15803d;padding:1px 6px;border-radius:9999px;font-size:11px;font-weight:600;">'
                        . esc_html__( 'Verified', 'kwl-coupon-wp' ) . '</span>';
                }
                if ( $exclusive ) {
                    $badges[] = '<span style="background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:9999px;font-size:11px;font-weight:600;">'
                        . esc_html__( 'Exclusive', 'kwl-coupon-wp' ) . '</span>';
                }
            }

            echo ! empty( $badges )
                ? implode( ' ', $badges ) // phpcs:ignore
                : '<span style="color:#94a3b8;">—</span>';
            break;

        case 'kwl_clicks':
            $clicks = (int) get_post_meta( $post_id, '_kwl_click_count', true );
            echo number_format( $clicks );
            break;

    }

}
add_action( 'manage_kwl_coupon_posts_custom_column', 'kwl_coupon_column_content', 10, 2 );


/**
 * Make coupon columns sortable.
 *
 * @param  array $columns
 * @return array
 */
function kwl_coupon_sortable_columns( array $columns ): array {
    $columns['kwl_expiry'] = 'kwl_expiry';
    $columns['kwl_clicks'] = 'kwl_clicks';
    $columns['kwl_store']  = 'kwl_store';
    return $columns;
}
add_filter( 'manage_edit-kwl_coupon_sortable_columns', 'kwl_coupon_sortable_columns' );


/* =============================================================================
   ADMIN FILTERS — COUPON LIST
   ============================================================================= */

/**
 * Add "Filter by Store" and "Filter by Status" dropdowns to coupon list.
 *
 * @param string $post_type
 */
function kwl_coupon_list_filters( string $post_type ): void {

    if ( $post_type !== 'kwl_coupon' ) {
        return;
    }

    // Filter by Store
    $stores = get_posts( [
        'post_type'      => 'kwl_store',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ] );

    $selected_store = absint( $_GET['kwl_filter_store'] ?? 0 );

    echo '<select name="kwl_filter_store">';
    echo '<option value="">' . esc_html__( 'All Stores', 'kwl-coupon-wp' ) . '</option>';
    foreach ( $stores as $store ) {
        printf(
            '<option value="%d" %s>%s</option>',
            $store->ID,
            selected( $selected_store, $store->ID, false ),
            esc_html( $store->post_title )
        );
    }
    echo '</select>';

    // Filter by Type
    $selected_type = sanitize_key( $_GET['kwl_filter_type'] ?? '' );
    $types = [
        ''             => __( 'All Types',     'kwl-coupon-wp' ),
        'code'         => __( 'Coupon Code',   'kwl-coupon-wp' ),
        'deal'         => __( 'Deal',          'kwl-coupon-wp' ),
        'freeshipping' => __( 'Free Shipping', 'kwl-coupon-wp' ),
    ];

    echo '<select name="kwl_filter_type">';
    foreach ( $types as $type_value => $type_label ) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr( $type_value ),
            selected( $selected_type, $type_value, false ),
            esc_html( $type_label )
        );
    }
    echo '</select>';

    // Filter by Status
    $selected_status = sanitize_key( $_GET['kwl_filter_status'] ?? '' );
    $statuses = [
        ''         => __( 'All Statuses', 'kwl-coupon-wp' ),
        'active'   => __( 'Active',       'kwl-coupon-wp' ),
        'expired'  => __( 'Expired',      'kwl-coupon-wp' ),
        'verified' => __( 'Verified',     'kwl-coupon-wp' ),
        'exclusive'=> __( 'Exclusive',    'kwl-coupon-wp' ),
    ];

    echo '<select name="kwl_filter_status">';
    foreach ( $statuses as $status_value => $status_label ) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr( $status_value ),
            selected( $selected_status, $status_value, false ),
            esc_html( $status_label )
        );
    }
    echo '</select>';

}
add_action( 'restrict_manage_posts', 'kwl_coupon_list_filters' );


/**
 * Apply coupon list filters to the query.
 *
 * @param WP_Query $query
 */
function kwl_coupon_list_filter_query( WP_Query $query ): void {

    global $pagenow;

    if ( ! is_admin() || $pagenow !== 'edit.php' || ( $_GET['post_type'] ?? '' ) !== 'kwl_coupon' ) {
        return;
    }

    $meta_query = [];

    // Filter by store
    $store_id = absint( $_GET['kwl_filter_store'] ?? 0 );
    if ( $store_id ) {
        $meta_query[] = [
            'key'   => '_kwl_coupon_store_id',
            'value' => $store_id,
            'type'  => 'NUMERIC',
        ];
    }

    // Filter by type
    $type = sanitize_key( $_GET['kwl_filter_type'] ?? '' );
    if ( $type && in_array( $type, [ 'code', 'deal', 'freeshipping' ], true ) ) {
        $meta_query[] = [
            'key'   => '_kwl_coupon_type',
            'value' => $type,
        ];
    }

    // Filter by status
    $status = sanitize_key( $_GET['kwl_filter_status'] ?? '' );
    $today  = current_time( 'Y-m-d' );

    if ( $status === 'active' ) {
        $meta_query[] = [
            'relation' => 'OR',
            [ 'key' => '_kwl_coupon_expiry', 'compare' => 'NOT EXISTS' ],
            [ 'key' => '_kwl_coupon_expiry', 'value' => '', 'compare' => '=' ],
            [ 'key' => '_kwl_coupon_expiry', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' ],
        ];
    } elseif ( $status === 'expired' ) {
        $meta_query[] = [
            'key'     => '_kwl_coupon_expiry',
            'value'   => $today,
            'compare' => '<',
            'type'    => 'DATE',
        ];
    } elseif ( $status === 'verified' ) {
        $meta_query[] = [ 'key' => '_kwl_coupon_verified', 'value' => '1' ];
    } elseif ( $status === 'exclusive' ) {
        $meta_query[] = [ 'key' => '_kwl_coupon_exclusive', 'value' => '1' ];
    }

    if ( ! empty( $meta_query ) ) {
        $query->set( 'meta_query', $meta_query );
    }

}
add_action( 'pre_get_posts', 'kwl_coupon_list_filter_query' );


/* =============================================================================
   COLUMN WIDTHS
   ============================================================================= */

/**
 * Output inline CSS for admin column widths.
 */
function kwl_admin_column_widths(): void {

    global $post_type;

    if ( ! in_array( $post_type, [ 'kwl_store', 'kwl_coupon' ], true ) ) {
        return;
    }

    echo '<style>
        .column-kwl_logo    { width: 56px; }
        .column-kwl_slug    { width: 140px; }
        .column-kwl_type    { width: 40px; text-align:center; }
        .column-kwl_code    { width: 130px; }
        .column-kwl_discount{ width: 100px; }
        .column-kwl_expiry  { width: 110px; }
        .column-kwl_status  { width: 120px; }
        .column-kwl_clicks  { width: 70px; }
        .column-kwl_coupons { width: 80px; }
        .column-kwl_network { width: 90px; }
        .column-kwl_featured{ width: 70px; text-align:center; }
    </style>';

}
add_action( 'admin_head', 'kwl_admin_column_widths' );
