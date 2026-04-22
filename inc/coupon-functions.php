<?php
/**
 * KWL Coupon WP — Coupon Functions
 *
 * Core helper functions used by templates and AJAX handlers.
 * Everything coupon-related that isn't post type registration lives here.
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   EXPIRY HELPERS
   ============================================================================= */

/**
 * Check if a coupon is expired.
 *
 * @param  int  $coupon_id  Coupon post ID.
 * @return bool             True if expired, false if still valid or no expiry set.
 */
function kwl_is_expired( int $coupon_id ): bool {

    $expiry = get_post_meta( $coupon_id, '_kwl_coupon_expiry', true );

    if ( empty( $expiry ) ) {
        return false; // No expiry = never expires
    }

    // Compare dates in Y-m-d format
    $today      = current_time( 'Y-m-d' );
    $expiry_date = sanitize_text_field( $expiry );

    return $expiry_date < $today;

}


/**
 * Check if a coupon is expiring soon (within N days).
 *
 * @param  int $coupon_id  Coupon post ID.
 * @param  int $days       Number of days to consider "soon". Default 3.
 * @return bool
 */
function kwl_is_expiring_soon( int $coupon_id, int $days = 3 ): bool {

    $expiry = get_post_meta( $coupon_id, '_kwl_coupon_expiry', true );

    if ( empty( $expiry ) ) {
        return false;
    }

    $today      = strtotime( current_time( 'Y-m-d' ) );
    $expiry_ts  = strtotime( $expiry );
    $diff_days  = (int) floor( ( $expiry_ts - $today ) / DAY_IN_SECONDS );

    return $diff_days >= 0 && $diff_days <= $days;

}


/**
 * Get a human-readable expiry string for display.
 *
 * Examples:
 *   "Expires today"
 *   "Expires in 2 days"
 *   "Expired 5 days ago"
 *   "No expiry"
 *
 * @param  int $coupon_id
 * @return string
 */
function kwl_get_expiry_label( int $coupon_id ): string {

    $expiry = get_post_meta( $coupon_id, '_kwl_coupon_expiry', true );

    if ( empty( $expiry ) ) {
        return __( 'No expiry', 'kwl-coupon-wp' );
    }

    $today     = strtotime( current_time( 'Y-m-d' ) );
    $expiry_ts = strtotime( $expiry );
    $diff_days = (int) floor( ( $expiry_ts - $today ) / DAY_IN_SECONDS );

    if ( $diff_days < 0 ) {
        $days_ago = abs( $diff_days );
        return sprintf(
            /* translators: %d = number of days */
            _n( 'Expired %d day ago', 'Expired %d days ago', $days_ago, 'kwl-coupon-wp' ),
            $days_ago
        );
    }

    if ( $diff_days === 0 ) {
        return __( 'Expires today', 'kwl-coupon-wp' );
    }

    if ( $diff_days === 1 ) {
        return __( 'Expires tomorrow', 'kwl-coupon-wp' );
    }

    return sprintf(
        /* translators: %d = number of days */
        __( 'Expires in %d days', 'kwl-coupon-wp' ),
        $diff_days
    );

}


/**
 * Get the CSS class for the expiry status on a coupon card.
 *
 * @param  int $coupon_id
 * @return string  CSS modifier class.
 */
function kwl_get_expiry_class( int $coupon_id ): string {

    if ( kwl_is_expired( $coupon_id ) ) {
        return 'cwp-coupon-card__expiry--expired';
    }

    if ( kwl_is_expiring_soon( $coupon_id ) ) {
        return 'cwp-coupon-card__expiry--soon';
    }

    return '';

}


/* =============================================================================
   AFFILIATE URL HELPERS
   ============================================================================= */

/**
 * Get the effective affiliate URL for a coupon.
 *
 * Priority:
 * 1. Coupon-specific affiliate URL (if set)
 * 2. Store's default affiliate URL
 * 3. Store's regular website URL
 *
 * @param  int $coupon_id  Coupon post ID.
 * @return string          Affiliate URL.
 */
function kwl_get_coupon_affiliate_url( int $coupon_id ): string {

    // 1. Coupon-specific URL takes priority
    $coupon_affiliate = get_post_meta( $coupon_id, '_kwl_coupon_affiliate', true );
    if ( ! empty( $coupon_affiliate ) ) {
        return esc_url( $coupon_affiliate );
    }

    // 2. Fall back to store affiliate URL
    $store_id = (int) get_post_meta( $coupon_id, '_kwl_coupon_store_id', true );
    if ( $store_id ) {
        return kwl_get_store_affiliate_url( $store_id );
    }

    return '';

}


/**
 * Get the affiliate URL for a store.
 *
 * Priority:
 * 1. Store's affiliate URL
 * 2. Store's regular website URL
 *
 * @param  int $store_id  Store post ID.
 * @return string         URL.
 */
function kwl_get_store_affiliate_url( int $store_id ): string {

    $affiliate = get_post_meta( $store_id, '_kwl_store_affiliate_url', true );
    if ( ! empty( $affiliate ) ) {
        return esc_url( $affiliate );
    }

    $website = get_post_meta( $store_id, '_kwl_store_website', true );
    if ( ! empty( $website ) ) {
        return esc_url( $website );
    }

    return '';

}


/**
 * Get the cloaked affiliate link URL for a store.
 *
 * Uses /go/{store-slug}/ for clean, trackable links.
 * Falls back to direct affiliate URL if cloaking is disabled.
 *
 * @param  int $store_id
 * @return string
 */
function kwl_get_store_go_url( int $store_id ): string {

    if ( kwl_get_option( 'cloak_affiliate_links', true ) ) {
        $slug = get_post_field( 'post_name', $store_id );
        return home_url( '/go/' . $slug . '/' );
    }

    return kwl_get_store_affiliate_url( $store_id );

}


/* =============================================================================
   COUPON DATA HELPERS
   ============================================================================= */

/**
 * Get a coupon's code.
 *
 * @param  int $coupon_id
 * @return string  The coupon code or empty string.
 */
function kwl_get_coupon_code( int $coupon_id ): string {
    return esc_html( get_post_meta( $coupon_id, '_kwl_coupon_code', true ) );
}


/**
 * Get the coupon type.
 *
 * @param  int $coupon_id
 * @return string  'code', 'deal', or 'freeshipping'
 */
function kwl_get_coupon_type( int $coupon_id ): string {
    $type = get_post_meta( $coupon_id, '_kwl_coupon_type', true );
    return in_array( $type, [ 'code', 'deal', 'freeshipping' ], true ) ? $type : 'code';
}


/**
 * Get the discount label for a coupon.
 *
 * @param  int $coupon_id
 * @return string
 */
function kwl_get_coupon_discount( int $coupon_id ): string {
    return esc_html( get_post_meta( $coupon_id, '_kwl_coupon_discount', true ) );
}


/**
 * Get the store ID linked to a coupon.
 *
 * @param  int $coupon_id
 * @return int  Store post ID or 0 if not set.
 */
function kwl_get_coupon_store_id( int $coupon_id ): int {
    return (int) get_post_meta( $coupon_id, '_kwl_coupon_store_id', true );
}


/**
 * Check if a coupon is verified.
 *
 * @param  int $coupon_id
 * @return bool
 */
function kwl_is_coupon_verified( int $coupon_id ): bool {
    return get_post_meta( $coupon_id, '_kwl_coupon_verified', true ) === '1';
}


/**
 * Check if a coupon is exclusive.
 *
 * @param  int $coupon_id
 * @return bool
 */
function kwl_is_coupon_exclusive( int $coupon_id ): bool {
    return get_post_meta( $coupon_id, '_kwl_coupon_exclusive', true ) === '1';
}


/**
 * Get the success rate percentage for a coupon.
 *
 * @param  int $coupon_id
 * @return int  0–100
 */
function kwl_get_coupon_success_rate( int $coupon_id ): int {

    $up   = (int) get_post_meta( $coupon_id, '_kwl_votes_up',   true );
    $down = (int) get_post_meta( $coupon_id, '_kwl_votes_down', true );
    $total = $up + $down;

    return $total > 0 ? (int) round( ( $up / $total ) * 100 ) : 0;

}


/**
 * Get vote counts for a coupon.
 *
 * @param  int $coupon_id
 * @return array { up: int, down: int, total: int, rate: int }
 */
function kwl_get_coupon_votes( int $coupon_id ): array {

    $up    = (int) get_post_meta( $coupon_id, '_kwl_votes_up',   true );
    $down  = (int) get_post_meta( $coupon_id, '_kwl_votes_down', true );
    $total = $up + $down;
    $rate  = $total > 0 ? (int) round( ( $up / $total ) * 100 ) : 0;

    return compact( 'up', 'down', 'total', 'rate' );

}


/* =============================================================================
   QUERY HELPERS
   ============================================================================= */

/**
 * Get coupons for a specific store.
 *
 * @param  int   $store_id         Store post ID.
 * @param  bool  $hide_expired     Whether to exclude expired coupons.
 * @param  int   $posts_per_page   Number of coupons to return. -1 for all.
 * @param  int   $paged            Current page number.
 * @return WP_Query
 */
function kwl_get_coupons_by_store( int $store_id, bool $hide_expired = false, int $posts_per_page = -1, int $paged = 1 ): WP_Query {

    $args = [
        'post_type'      => 'kwl_coupon',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'meta_query'     => [
            [
                'key'   => '_kwl_coupon_store_id',
                'value' => $store_id,
                'type'  => 'NUMERIC',
            ],
        ],
        'orderby'        => [
            'meta_value' => 'DESC', // Verified first
            'date'       => 'DESC', // Then newest
        ],
        'meta_key'       => '_kwl_coupon_verified',
    ];

    // Filter out expired coupons if requested
    if ( $hide_expired ) {
        $today = current_time( 'Y-m-d' );
        $args['meta_query'][] = [
            'relation' => 'OR',
            [
                'key'     => '_kwl_coupon_expiry',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key'     => '_kwl_coupon_expiry',
                'value'   => '',
                'compare' => '=',
            ],
            [
                'key'     => '_kwl_coupon_expiry',
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATE',
            ],
        ];
    }

    return new WP_Query( $args );

}


/**
 * Get the latest coupons sitewide.
 *
 * @param  int  $count         Number of coupons to return.
 * @param  bool $hide_expired  Exclude expired coupons.
 * @param  int  $paged         Page number for pagination.
 * @return WP_Query
 */
function kwl_get_latest_coupons( int $count = 20, bool $hide_expired = true, int $paged = 1 ): WP_Query {

    $args = [
        'post_type'      => 'kwl_coupon',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    if ( $hide_expired ) {
        $today = current_time( 'Y-m-d' );
        $args['meta_query'] = [
            'relation' => 'OR',
            [
                'key'     => '_kwl_coupon_expiry',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key'     => '_kwl_coupon_expiry',
                'value'   => '',
                'compare' => '=',
            ],
            [
                'key'     => '_kwl_coupon_expiry',
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATE',
            ],
        ];
    }

    return new WP_Query( $args );

}


/**
 * Get featured stores for homepage display.
 *
 * @param  int $count  Number of stores to return.
 * @return WP_Post[]
 */
function kwl_get_featured_stores( int $count = 12 ): array {

    return get_posts( [
        'post_type'      => 'kwl_store',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'   => '_kwl_store_featured',
                'value' => '1',
            ],
        ],
    ] );

}


/**
 * Get all stores (for archive page).
 *
 * @param  int  $posts_per_page
 * @param  int  $paged
 * @param  string $orderby  'title', 'date', 'count' (by coupon count)
 * @return WP_Query
 */
function kwl_get_all_stores( int $posts_per_page = 40, int $paged = 1, string $orderby = 'title' ): WP_Query {

    $args = [
        'post_type'      => 'kwl_store',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'orderby'        => $orderby === 'title' ? 'title' : 'date',
        'order'          => $orderby === 'title' ? 'ASC' : 'DESC',
    ];

    return new WP_Query( $args );

}


/**
 * Get the total number of active (non-expired) coupons for a store.
 *
 * @param  int $store_id
 * @return int
 */
function kwl_get_store_coupon_count( int $store_id ): int {

    $query = kwl_get_coupons_by_store( $store_id, true, -1 );
    return (int) $query->found_posts;

}


/* =============================================================================
   STORE LOGO HELPERS
   ============================================================================= */

/**
 * Get the store logo URL.
 *
 * @param  int    $store_id   Store post ID.
 * @param  string $size       Image size. Default 'kwl-store-logo'.
 * @return string             Image URL or empty string.
 */
function kwl_get_store_logo_url( int $store_id, string $size = 'kwl-store-logo' ): string {

    $thumbnail_id = get_post_thumbnail_id( $store_id );

    if ( ! $thumbnail_id ) {
        return '';
    }

    $image = wp_get_attachment_image_src( $thumbnail_id, $size );
    return $image ? esc_url( $image[0] ) : '';

}


/**
 * Output store logo <img> tag or a text placeholder.
 *
 * @param  int    $store_id    Store post ID.
 * @param  string $size        Image size.
 * @param  string $css_class   CSS class for the img tag.
 */
function kwl_store_logo( int $store_id, string $size = 'kwl-store-logo', string $css_class = 'cwp-store-card__logo' ): void {

    $logo_url   = kwl_get_store_logo_url( $store_id, $size );
    $store_name = get_the_title( $store_id );

    if ( ! empty( $logo_url ) ) {
        printf(
            '<img src="%s" alt="%s" class="%s" loading="lazy" width="64" height="64">',
            esc_url( $logo_url ),
            esc_attr( $store_name ),
            esc_attr( $css_class )
        );
    } else {
        // Text placeholder using first letter of store name
        $initial = mb_strtoupper( mb_substr( $store_name, 0, 1, 'UTF-8' ), 'UTF-8' );
        printf(
            '<div class="cwp-store-card__logo-placeholder" aria-label="%s">%s</div>',
            esc_attr( $store_name ),
            esc_html( $initial )
        );
    }

}


/* =============================================================================
   RENDER HELPERS — BADGES
   ============================================================================= */

/**
 * Output all applicable badges for a coupon.
 *
 * @param  int $coupon_id
 */
function kwl_render_coupon_badges( int $coupon_id ): void {

    $type = kwl_get_coupon_type( $coupon_id );

    // Type badge
    if ( $type === 'freeshipping' ) {
        echo '<span class="cwp-badge cwp-badge--freeshipping">' . esc_html__( 'Free Shipping', 'kwl-coupon-wp' ) . '</span>';
    }

    // Verified badge
    if ( kwl_is_coupon_verified( $coupon_id ) ) {
        echo '<span class="cwp-badge cwp-badge--verified">' . esc_html__( 'Verified', 'kwl-coupon-wp' ) . '</span>';
    }

    // Exclusive badge
    if ( kwl_is_coupon_exclusive( $coupon_id ) ) {
        echo '<span class="cwp-badge cwp-badge--exclusive">' . esc_html__( 'Exclusive', 'kwl-coupon-wp' ) . '</span>';
    }

    // Expiry badge
    if ( kwl_is_expired( $coupon_id ) ) {
        echo '<span class="cwp-badge cwp-badge--expired">' . esc_html__( 'Expired', 'kwl-coupon-wp' ) . '</span>';
    } elseif ( kwl_is_expiring_soon( $coupon_id ) ) {
        echo '<span class="cwp-badge cwp-badge--hot">' . esc_html__( 'Ending Soon', 'kwl-coupon-wp' ) . '</span>';
    }

}


/* =============================================================================
   RENDER HELPERS — COUPON CARD ACTION BUTTON
   ============================================================================= */

/**
 * Output the coupon action button (reveal code or get deal).
 *
 * @param  int $coupon_id
 */
function kwl_render_coupon_action( int $coupon_id ): void {

    $type        = kwl_get_coupon_type( $coupon_id );
    $expired     = kwl_is_expired( $coupon_id );
    $affiliate   = kwl_get_coupon_affiliate_url( $coupon_id );

    if ( $expired ) {
        echo '<span class="cwp-btn-reveal cwp-btn-reveal--expired">'
            . esc_html__( 'Expired', 'kwl-coupon-wp' )
            . '</span>';
        return;
    }

    if ( $type === 'code' ) {
        // Click to reveal button — actual code revealed via JS/AJAX
        printf(
            '<button
                class="cwp-btn-reveal"
                data-coupon-id="%d"
                data-affiliate="%s"
                data-action="reveal"
                aria-label="%s"
            >%s</button>',
            esc_attr( $coupon_id ),
            esc_attr( $affiliate ),
            esc_attr__( 'Show coupon code', 'kwl-coupon-wp' ),
            esc_html__( 'Show Code', 'kwl-coupon-wp' )
        );
    } else {
        // Deal or free shipping — direct link, no code to reveal
        $label = $type === 'freeshipping'
            ? __( 'Get Free Shipping', 'kwl-coupon-wp' )
            : __( 'Get Deal', 'kwl-coupon-wp' );

        printf(
            '<a
                href="%s"
                class="cwp-btn-deal"
                target="_blank"
                rel="nofollow noopener sponsored"
                data-coupon-id="%d"
                data-action="deal"
            >%s</a>',
            esc_url( $affiliate ),
            esc_attr( $coupon_id ),
            esc_html( $label )
        );
    }

}


/* =============================================================================
   RENDER HELPERS — FULL COUPON CARD
   ============================================================================= */

/**
 * Output a complete coupon card HTML.
 *
 * Used by templates to avoid duplicating markup.
 *
 * @param  int  $coupon_id
 * @param  bool $show_store_logo  Whether to show the store logo column.
 */
function kwl_render_coupon_card( int $coupon_id, bool $show_store_logo = true ): void {

    $expired    = kwl_is_expired( $coupon_id );
    $store_id   = kwl_get_coupon_store_id( $coupon_id );
    $store_name = $store_id ? get_the_title( $store_id ) : '';
    $store_url  = $store_id ? get_permalink( $store_id ) : '';
    $discount   = kwl_get_coupon_discount( $coupon_id );
    $title      = get_the_title( $coupon_id );
    $votes      = kwl_get_coupon_votes( $coupon_id );
    $expiry_label = kwl_get_expiry_label( $coupon_id );
    $expiry_class = kwl_get_expiry_class( $coupon_id );

    $card_class = 'cwp-coupon-card';
    if ( $expired ) {
        $card_class .= ' cwp-coupon-card--expired';
    }

    ?>
    <div class="<?php echo esc_attr( $card_class ); ?>" data-coupon-id="<?php echo esc_attr( $coupon_id ); ?>">

        <?php if ( $show_store_logo && $store_id ) : ?>
        <a href="<?php echo esc_url( $store_url ); ?>" class="cwp-coupon-card__store" title="<?php echo esc_attr( $store_name ); ?>">
            <?php kwl_store_logo( $store_id, 'kwl-store-logo-sm', 'cwp-coupon-card__store-logo' ); ?>
        </a>
        <?php endif; ?>

        <div class="cwp-coupon-card__content">

            <div class="cwp-coupon-card__badges">
                <?php kwl_render_coupon_badges( $coupon_id ); ?>
            </div>

            <?php if ( ! empty( $discount ) ) : ?>
            <div class="cwp-coupon-card__discount"><?php echo esc_html( $discount ); ?></div>
            <?php endif; ?>

            <h3 class="cwp-coupon-card__title">
                <a href="<?php echo esc_url( get_permalink( $coupon_id ) ); ?>">
                    <?php echo esc_html( $title ); ?>
                </a>
            </h3>

            <?php if ( $store_name ) : ?>
            <div class="cwp-coupon-card__description">
                <?php
                printf(
                    /* translators: %s = store name */
                    esc_html__( 'At %s', 'kwl-coupon-wp' ),
                    '<a href="' . esc_url( $store_url ) . '">' . esc_html( $store_name ) . '</a>'
                );
                ?>
            </div>
            <?php endif; ?>

            <div class="cwp-coupon-card__meta">

                <span class="cwp-coupon-card__expiry <?php echo esc_attr( $expiry_class ); ?>">
                    <?php echo esc_html( $expiry_label ); ?>
                </span>

                <?php if ( $votes['total'] > 0 ) : ?>
                <span class="cwp-coupon-card__votes">
                    <?php
                    printf(
                        /* translators: %d = success rate percentage */
                        esc_html__( '%d%% success rate', 'kwl-coupon-wp' ),
                        $votes['rate']
                    );
                    ?>
                </span>
                <?php endif; ?>

            </div>

        </div>

        <div class="cwp-coupon-card__action">
            <?php kwl_render_coupon_action( $coupon_id ); ?>
            <span class="cwp-coupon-card__type">
                <?php
                $type_labels = [
                    'code'         => __( 'Coupon Code', 'kwl-coupon-wp' ),
                    'deal'         => __( 'Deal',        'kwl-coupon-wp' ),
                    'freeshipping' => __( 'Free Shipping','kwl-coupon-wp' ),
                ];
                $type = kwl_get_coupon_type( $coupon_id );
                echo esc_html( $type_labels[ $type ] ?? '' );
                ?>
            </span>
        </div>

    </div>
    <?php

}


/* =============================================================================
   AFFILIATE LINK REDIRECT
   ============================================================================= */

/**
 * Handle /go/{store-slug}/ redirects for cloaked affiliate links.
 *
 * Hooks into template_redirect to intercept the URL,
 * increment click count, then redirect to affiliate URL.
 */
function kwl_handle_affiliate_redirect(): void {

    if ( ! kwl_get_option( 'cloak_affiliate_links', true ) ) {
        return;
    }

    $request = trim( $_SERVER['REQUEST_URI'] ?? '', '/' );

    // Match /go/{slug}/ pattern
    if ( ! preg_match( '#^go/([a-z0-9\-\.]+)/?$#i', $request, $matches ) ) {
        return;
    }

    $slug = kwl_sanitize_slug( $matches[1] );

    // Find the store by slug
    $store = get_posts( [
        'post_type'   => 'kwl_store',
        'post_status' => 'publish',
        'name'        => $slug,
        'numberposts' => 1,
    ] );

    if ( empty( $store ) ) {
        return;
    }

    $store_id = $store[0]->ID;

    // Increment store click count
    $clicks = (int) get_post_meta( $store_id, '_kwl_store_click_count', true );
    update_post_meta( $store_id, '_kwl_store_click_count', $clicks + 1 );

    // Get destination URL
    $destination = kwl_get_store_affiliate_url( $store_id );

    if ( empty( $destination ) ) {
        return;
    }

    // Redirect with noindex header
    header( 'X-Robots-Tag: noindex, nofollow' );
    wp_redirect( $destination, 302 );
    exit;

}
add_action( 'template_redirect', 'kwl_handle_affiliate_redirect' );


/**
 * Register the /go/ rewrite rule so WordPress doesn't 404 it.
 */
function kwl_register_go_rewrite(): void {
    add_rewrite_rule( '^go/([a-z0-9\-\.]+)/?$', 'index.php?kwl_go=$matches[1]', 'top' );
    add_rewrite_tag( '%kwl_go%', '([a-z0-9\-\.]+)' );
}
add_action( 'init', 'kwl_register_go_rewrite' );
