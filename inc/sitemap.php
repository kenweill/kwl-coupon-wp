<?php
/**
 * KWL Coupon WP — XML Sitemap
 *
 * Generates XML sitemaps for all coupon site content
 * without requiring any plugin (Yoast, RankMath, etc.)
 *
 * Sitemap index: /sitemap.xml
 * Sub-sitemaps:
 *   /sitemap-stores.xml    — all stores
 *   /sitemap-coupons.xml   — all active (non-expired) coupons
 *   /sitemap-categories.xml — all coupon categories
 *   /sitemap-pages.xml     — regular WordPress pages
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   REWRITE RULES
   ============================================================================= */

/**
 * Register sitemap rewrite rules.
 */
function kwl_sitemap_rewrite_rules(): void {

    add_rewrite_rule( '^sitemap\.xml$',             'index.php?kwl_sitemap=index',      'top' );
    add_rewrite_rule( '^sitemap-stores\.xml$',      'index.php?kwl_sitemap=stores',     'top' );
    add_rewrite_rule( '^sitemap-coupons\.xml$',     'index.php?kwl_sitemap=coupons',    'top' );
    add_rewrite_rule( '^sitemap-categories\.xml$',  'index.php?kwl_sitemap=categories', 'top' );
    add_rewrite_rule( '^sitemap-pages\.xml$',       'index.php?kwl_sitemap=pages',      'top' );

}
add_action( 'init', 'kwl_sitemap_rewrite_rules' );


/**
 * Register query var for sitemap routing.
 *
 * @param  array $vars
 * @return array
 */
function kwl_sitemap_query_vars( array $vars ): array {
    $vars[] = 'kwl_sitemap';
    return $vars;
}
add_filter( 'query_vars', 'kwl_sitemap_query_vars' );


/* =============================================================================
   SITEMAP ROUTER
   ============================================================================= */

/**
 * Intercept requests and serve the correct sitemap.
 */
function kwl_serve_sitemap(): void {

    $sitemap = get_query_var( 'kwl_sitemap' );

    if ( empty( $sitemap ) ) {
        return;
    }

    // Check if WordPress's own sitemap is disabled — if user enabled it, skip ours
    // (Our sitemap is only active when WP core sitemap is disabled or not conflicting)

    // Set XML headers
    header( 'Content-Type: application/xml; charset=UTF-8' );
    header( 'X-Robots-Tag: noindex, follow' );

    switch ( $sitemap ) {
        case 'index':
            kwl_render_sitemap_index();
            break;
        case 'stores':
            kwl_render_sitemap_stores();
            break;
        case 'coupons':
            kwl_render_sitemap_coupons();
            break;
        case 'categories':
            kwl_render_sitemap_categories();
            break;
        case 'pages':
            kwl_render_sitemap_pages();
            break;
        default:
            wp_die( esc_html__( 'Sitemap not found.', 'kwl-coupon-wp' ), 404 );
    }

    exit;

}
add_action( 'template_redirect', 'kwl_serve_sitemap', 5 );


/* =============================================================================
   SITEMAP INDEX
   ============================================================================= */

/**
 * Output the sitemap index XML.
 *
 * Lists all sub-sitemaps with their last modification dates.
 */
function kwl_render_sitemap_index(): void {

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?xml-stylesheet type="text/xsl" href="' . esc_url( KWL_URI . '/assets/xml/sitemap.xsl' ) . '"?>' . "\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    $sub_sitemaps = [
        [
            'loc'     => home_url( '/sitemap-stores.xml' ),
            'lastmod' => kwl_get_last_modified( 'kwl_store' ),
        ],
        [
            'loc'     => home_url( '/sitemap-coupons.xml' ),
            'lastmod' => kwl_get_last_modified( 'kwl_coupon' ),
        ],
        [
            'loc'     => home_url( '/sitemap-categories.xml' ),
            'lastmod' => kwl_get_last_modified_term( 'kwl_coupon_cat' ),
        ],
        [
            'loc'     => home_url( '/sitemap-pages.xml' ),
            'lastmod' => kwl_get_last_modified( 'page' ),
        ],
    ];

    foreach ( $sub_sitemaps as $sitemap ) {
        echo "\t<sitemap>\n";
        echo "\t\t<loc>" . esc_url( $sitemap['loc'] ) . "</loc>\n";
        if ( ! empty( $sitemap['lastmod'] ) ) {
            echo "\t\t<lastmod>" . esc_html( $sitemap['lastmod'] ) . "</lastmod>\n";
        }
        echo "\t</sitemap>\n";
    }

    echo '</sitemapindex>';

}


/* =============================================================================
   SITEMAP: STORES
   ============================================================================= */

/**
 * Output the stores sitemap XML.
 */
function kwl_render_sitemap_stores(): void {

    $stores = get_posts( [
        'post_type'      => 'kwl_store',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'modified',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ] );

    kwl_render_sitemap_xml( $stores, function( int $post_id ) {

        $logo_url = kwl_get_store_logo_url( $post_id, 'kwl-coupon-thumb' );

        $entry = [
            'loc'     => get_permalink( $post_id ),
            'lastmod' => get_post_modified_time( 'c', true, $post_id ),
            'changefreq' => 'daily',
            'priority'   => '0.8',
        ];

        // Image tag for store logo
        if ( ! empty( $logo_url ) ) {
            $entry['image'] = [
                'loc'     => $logo_url,
                'title'   => get_the_title( $post_id ),
                'caption' => sprintf(
                    /* translators: %s = store name */
                    __( '%s logo', 'kwl-coupon-wp' ),
                    get_the_title( $post_id )
                ),
            ];
        }

        return $entry;

    } );

}


/* =============================================================================
   SITEMAP: COUPONS
   ============================================================================= */

/**
 * Output the coupons sitemap XML.
 *
 * Only includes active (non-expired) coupons.
 * Expired coupons are excluded — they're noindexed anyway.
 */
function kwl_render_sitemap_coupons(): void {

    $today = current_time( 'Y-m-d' );

    $coupons = get_posts( [
        'post_type'      => 'kwl_coupon',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'modified',
        'order'          => 'DESC',
        'fields'         => 'ids',
        'meta_query'     => [
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
        ],
    ] );

    kwl_render_sitemap_xml( $coupons, function( int $post_id ) {

        return [
            'loc'        => get_permalink( $post_id ),
            'lastmod'    => get_post_modified_time( 'c', true, $post_id ),
            'changefreq' => 'weekly',
            'priority'   => '0.6',
        ];

    } );

}


/* =============================================================================
   SITEMAP: CATEGORIES
   ============================================================================= */

/**
 * Output the categories sitemap XML.
 */
function kwl_render_sitemap_categories(): void {

    $terms = get_terms( [
        'taxonomy'   => 'kwl_coupon_cat',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        kwl_render_empty_sitemap();
        return;
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ( $terms as $term ) {
        $url = get_term_link( $term );
        if ( is_wp_error( $url ) ) {
            continue;
        }

        echo "\t<url>\n";
        echo "\t\t<loc>" . esc_url( $url ) . "</loc>\n";
        echo "\t\t<changefreq>daily</changefreq>\n";

        // Higher priority for categories with more coupons
        $priority = $term->count >= 20 ? '0.8' : ( $term->count >= 5 ? '0.7' : '0.5' );
        echo "\t\t<priority>" . esc_html( $priority ) . "</priority>\n";
        echo "\t</url>\n";
    }

    echo '</urlset>';

}


/* =============================================================================
   SITEMAP: PAGES
   ============================================================================= */

/**
 * Output the WordPress pages sitemap XML.
 */
function kwl_render_sitemap_pages(): void {

    $pages = get_posts( [
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'modified',
        'order'          => 'DESC',
        'fields'         => 'ids',
        'exclude'        => [ (int) get_option( 'page_on_front' ) ], // Homepage handled separately
    ] );

    // Also include homepage
    $homepage = (int) get_option( 'page_on_front' );
    if ( $homepage ) {
        array_unshift( $pages, $homepage );
    }

    kwl_render_sitemap_xml( $pages, function( int $post_id ) {

        $is_homepage = (int) get_option( 'page_on_front' ) === $post_id;

        return [
            'loc'        => $is_homepage ? home_url( '/' ) : get_permalink( $post_id ),
            'lastmod'    => get_post_modified_time( 'c', true, $post_id ),
            'changefreq' => $is_homepage ? 'daily' : 'monthly',
            'priority'   => $is_homepage ? '1.0' : '0.5',
        ];

    } );

}


/* =============================================================================
   XML RENDER HELPERS
   ============================================================================= */

/**
 * Render a standard urlset XML from a list of post IDs and a callback.
 *
 * @param  int[]    $post_ids  Array of post IDs.
 * @param  callable $callback  Function that receives a post ID and returns entry array.
 */
function kwl_render_sitemap_xml( array $post_ids, callable $callback ): void {

    if ( empty( $post_ids ) ) {
        kwl_render_empty_sitemap();
        return;
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    echo '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

    foreach ( $post_ids as $post_id ) {

        $entry = $callback( (int) $post_id );

        if ( empty( $entry['loc'] ) ) {
            continue;
        }

        echo "\t<url>\n";
        echo "\t\t<loc>" . esc_url( $entry['loc'] ) . "</loc>\n";

        if ( ! empty( $entry['lastmod'] ) ) {
            echo "\t\t<lastmod>" . esc_html( $entry['lastmod'] ) . "</lastmod>\n";
        }

        if ( ! empty( $entry['changefreq'] ) ) {
            echo "\t\t<changefreq>" . esc_html( $entry['changefreq'] ) . "</changefreq>\n";
        }

        if ( ! empty( $entry['priority'] ) ) {
            echo "\t\t<priority>" . esc_html( $entry['priority'] ) . "</priority>\n";
        }

        // Image extension
        if ( ! empty( $entry['image'] ) ) {
            echo "\t\t<image:image>\n";
            echo "\t\t\t<image:loc>" . esc_url( $entry['image']['loc'] ) . "</image:loc>\n";
            if ( ! empty( $entry['image']['title'] ) ) {
                echo "\t\t\t<image:title>" . esc_html( $entry['image']['title'] ) . "</image:title>\n";
            }
            if ( ! empty( $entry['image']['caption'] ) ) {
                echo "\t\t\t<image:caption>" . esc_html( $entry['image']['caption'] ) . "</image:caption>\n";
            }
            echo "\t\t</image:image>\n";
        }

        echo "\t</url>\n";

    }

    echo '</urlset>';

}


/**
 * Output an empty but valid XML sitemap.
 */
function kwl_render_empty_sitemap(): void {
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    echo '</urlset>';
}


/* =============================================================================
   SITEMAP HELPERS
   ============================================================================= */

/**
 * Get the most recent modification date for a post type.
 *
 * @param  string $post_type
 * @return string  ISO 8601 date or empty string.
 */
function kwl_get_last_modified( string $post_type ): string {

    global $wpdb;

    $result = $wpdb->get_var( $wpdb->prepare(
        "SELECT MAX(post_modified_gmt)
         FROM {$wpdb->posts}
         WHERE post_type = %s
         AND post_status = 'publish'",
        $post_type
    ) );

    return $result ? gmdate( 'c', strtotime( $result ) ) : '';

}


/**
 * Get the most recent update date for a taxonomy.
 *
 * @param  string $taxonomy
 * @return string  ISO 8601 date or current date as fallback.
 */
function kwl_get_last_modified_term( string $taxonomy ): string {

    // WordPress doesn't store term modification dates natively
    // Use the most recent coupon modification as proxy
    return kwl_get_last_modified( 'kwl_coupon' ) ?: gmdate( 'c' );

}


/* =============================================================================
   DISABLE WORDPRESS CORE SITEMAP (OPTIONAL)
   ============================================================================= */

/**
 * Disable WordPress's built-in sitemap to avoid conflicts.
 *
 * Only disabled if our custom sitemap is enabled in settings.
 * Users can toggle this in Theme Settings.
 */
function kwl_maybe_disable_core_sitemap(): void {

    if ( kwl_get_option( 'use_custom_sitemap', true ) ) {
        add_filter( 'wp_sitemaps_enabled', '__return_false' );
    }

}
add_action( 'init', 'kwl_maybe_disable_core_sitemap' );


/* =============================================================================
   PING SEARCH ENGINES ON PUBLISH
   ============================================================================= */

/**
 * Ping Google (and Bing) when a store or coupon is published.
 *
 * Notifies search engines that the sitemap has been updated.
 *
 * @param string  $new_status
 * @param string  $old_status
 * @param WP_Post $post
 */
function kwl_ping_search_engines_on_publish( string $new_status, string $old_status, WP_Post $post ): void {

    if ( $new_status !== 'publish' || $old_status === 'publish' ) {
        return;
    }

    if ( ! in_array( $post->post_type, [ 'kwl_store', 'kwl_coupon' ], true ) ) {
        return;
    }

    if ( ! kwl_get_option( 'ping_search_engines', true ) ) {
        return;
    }

    $sitemap_url = urlencode( home_url( '/sitemap.xml' ) );

    $ping_urls = [
        'https://www.google.com/ping?sitemap=' . $sitemap_url,
        'https://www.bing.com/ping?sitemap='   . $sitemap_url,
    ];

    foreach ( $ping_urls as $url ) {
        wp_remote_get( $url, [ 'timeout' => 3, 'blocking' => false ] );
    }

}
add_action( 'transition_post_status', 'kwl_ping_search_engines_on_publish', 10, 3 );
