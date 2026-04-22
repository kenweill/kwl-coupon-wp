<?php
/**
 * KWL Coupon WP — SEO
 *
 * Handles all on-page SEO without requiring Yoast, RankMath, or any other plugin.
 *
 * Outputs:
 * - <title> tag (via WordPress title-tag support)
 * - Meta description
 * - Canonical URL
 * - Open Graph tags (og:title, og:description, og:image, og:url, og:type)
 * - Twitter Card tags
 * - Robots meta (noindex for expired coupon pages, search results, etc.)
 * - Breadcrumb HTML (with schema via inc/schema.php)
 * - Pagination rel prev/next
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   HEAD OUTPUT
   ============================================================================= */

/**
 * Main SEO hook — outputs all meta tags into <head>.
 *
 * Hooked at priority 1 so it runs before other wp_head content.
 */
function kwl_seo_head(): void {

    // Don't run in admin
    if ( is_admin() ) {
        return;
    }

    $meta = kwl_get_current_page_meta();

    // Meta description
    if ( ! empty( $meta['description'] ) ) {
        echo '<meta name="description" content="' . esc_attr( $meta['description'] ) . '">' . "\n";
    }

    // Canonical URL
    if ( ! empty( $meta['canonical'] ) ) {
        echo '<link rel="canonical" href="' . esc_url( $meta['canonical'] ) . '">' . "\n";
    }

    // Robots
    if ( ! empty( $meta['robots'] ) ) {
        echo '<meta name="robots" content="' . esc_attr( $meta['robots'] ) . '">' . "\n";
    }

    // Open Graph
    kwl_output_open_graph( $meta );

    // Twitter Card
    kwl_output_twitter_card( $meta );

    // Pagination rel prev/next
    kwl_output_pagination_rel();

}
add_action( 'wp_head', 'kwl_seo_head', 1 );


/* =============================================================================
   PAGE META RESOLVER
   ============================================================================= */

/**
 * Resolve all meta information for the current page.
 *
 * Returns a normalized array used by all output functions.
 *
 * @return array {
 *   title:       string,
 *   description: string,
 *   canonical:   string,
 *   image:       string,
 *   type:        string,  (website|article)
 *   robots:      string,
 * }
 */
function kwl_get_current_page_meta(): array {

    $meta = [
        'title'       => '',
        'description' => '',
        'canonical'   => '',
        'image'       => kwl_get_default_og_image(),
        'type'        => 'website',
        'robots'      => '',
    ];

    // --- Single Store ---
    if ( is_singular( 'kwl_store' ) ) {
        $post_id          = get_the_ID();
        $store_name       = get_the_title();
        $coupon_count     = kwl_get_store_coupon_count( $post_id );
        $site_name        = get_bloginfo( 'name' );

        $meta['title']    = kwl_get_post_meta_title( $post_id )
            ?: sprintf(
                /* translators: 1: store name, 2: site name */
                __( '%1$s Coupons & Promo Codes — %2$s', 'kwl-coupon-wp' ),
                $store_name,
                $site_name
            );

        $meta['description'] = kwl_get_post_meta_description( $post_id )
            ?: sprintf(
                /* translators: 1: coupon count, 2: store name, 3: site name */
                _n(
                    'Find %1$d working coupon code for %2$s. Verified and updated daily at %3$s.',
                    'Find %1$d working coupon codes for %2$s. Verified and updated daily at %3$s.',
                    $coupon_count,
                    'kwl-coupon-wp'
                ),
                $coupon_count,
                $store_name,
                $site_name
            );

        $meta['canonical'] = get_permalink( $post_id );
        $meta['type']      = 'article';

        // Store logo as OG image
        $logo = kwl_get_store_logo_url( $post_id, 'kwl-coupon-thumb' );
        if ( $logo ) {
            $meta['image'] = $logo;
        }

        return $meta;
    }

    // --- Single Coupon ---
    if ( is_singular( 'kwl_coupon' ) ) {
        $post_id      = get_the_ID();
        $coupon_title = get_the_title();
        $store_id     = kwl_get_coupon_store_id( $post_id );
        $store_name   = $store_id ? get_the_title( $store_id ) : '';
        $discount     = kwl_get_coupon_discount( $post_id );
        $site_name    = get_bloginfo( 'name' );

        $meta['title'] = kwl_get_post_meta_title( $post_id )
            ?: sprintf(
                /* translators: 1: discount label, 2: store name */
                __( '%1$s at %2$s — Verified Coupon Code', 'kwl-coupon-wp' ),
                $discount ?: $coupon_title,
                $store_name ?: $site_name
            );

        $meta['description'] = kwl_get_post_meta_description( $post_id )
            ?: sprintf(
                /* translators: 1: coupon title, 2: store name, 3: site name */
                __( 'Get %1$s at %2$s. Find this coupon and more verified promo codes at %3$s.', 'kwl-coupon-wp' ),
                $coupon_title,
                $store_name,
                $site_name
            );

        $meta['canonical'] = get_permalink( $post_id );
        $meta['type']      = 'article';

        // Noindex expired coupons
        if ( kwl_is_expired( $post_id ) ) {
            $meta['robots'] = 'noindex, follow';
        }

        return $meta;
    }

    // --- Store Archive (/stores/) ---
    if ( is_post_type_archive( 'kwl_store' ) ) {
        $site_name = get_bloginfo( 'name' );

        $meta['title'] = sprintf(
            /* translators: %s = site name */
            __( 'All Stores — %s', 'kwl-coupon-wp' ),
            $site_name
        );

        $meta['description'] = sprintf(
            /* translators: %s = site name */
            __( 'Browse all stores with verified coupons and promo codes on %s. Updated daily.', 'kwl-coupon-wp' ),
            $site_name
        );

        $meta['canonical'] = get_post_type_archive_link( 'kwl_store' );
        return $meta;
    }

    // --- Coupon Archive (/coupons/) ---
    if ( is_post_type_archive( 'kwl_coupon' ) ) {
        $site_name = get_bloginfo( 'name' );

        $meta['title'] = sprintf(
            /* translators: %s = site name */
            __( 'Latest Coupons & Promo Codes — %s', 'kwl-coupon-wp' ),
            $site_name
        );

        $meta['description'] = sprintf(
            /* translators: %s = site name */
            __( 'Find the latest verified coupon codes and deals at %s. Handpicked and updated daily.', 'kwl-coupon-wp' ),
            $site_name
        );

        $meta['canonical'] = get_post_type_archive_link( 'kwl_coupon' );
        return $meta;
    }

    // --- Category Archive ---
    if ( is_tax( 'kwl_coupon_cat' ) ) {
        $term      = get_queried_object();
        $site_name = get_bloginfo( 'name' );

        $meta['title'] = sprintf(
            /* translators: 1: category name, 2: site name */
            __( '%1$s Coupons & Promo Codes — %2$s', 'kwl-coupon-wp' ),
            $term->name,
            $site_name
        );

        $meta['description'] = kwl_get_category_meta_description( $term );
        $meta['canonical']   = get_term_link( $term );

        // Noindex paginated pages (page 2+)
        if ( get_query_var( 'paged' ) > 1 ) {
            $meta['robots'] = 'noindex, follow';
        }

        return $meta;
    }

    // --- Tag Archive ---
    if ( is_tax( 'kwl_coupon_tag' ) ) {
        $term      = get_queried_object();
        $site_name = get_bloginfo( 'name' );

        $meta['title'] = sprintf(
            /* translators: 1: tag name, 2: site name */
            __( '%1$s Coupons — %2$s', 'kwl-coupon-wp' ),
            $term->name,
            $site_name
        );

        $meta['description'] = sprintf(
            /* translators: 1: tag name, 2: site name */
            __( 'Find verified "%1$s" coupon codes and deals at %2$s.', 'kwl-coupon-wp' ),
            $term->name,
            $site_name
        );

        $meta['canonical'] = get_term_link( $term );
        return $meta;
    }

    // --- Search Results ---
    if ( is_search() ) {
        $meta['robots']    = 'noindex, follow';
        $meta['canonical'] = '';
        return $meta;
    }

    // --- Homepage ---
    if ( is_front_page() ) {
        $site_name = get_bloginfo( 'name' );
        $tagline   = get_bloginfo( 'description' );

        $meta['title']       = $site_name . ( $tagline ? ' — ' . $tagline : '' );
        $meta['description'] = kwl_get_option( 'homepage_meta_description', '' )
            ?: sprintf(
                /* translators: %s = site name */
                __( 'Find verified coupon codes, promo codes, and deals at %s. Updated daily.', 'kwl-coupon-wp' ),
                $site_name
            );
        $meta['canonical']   = home_url( '/' );
        return $meta;
    }

    // --- Regular WordPress page/post ---
    if ( is_singular() ) {
        $post_id = get_the_ID();

        $meta['title']       = kwl_get_post_meta_title( $post_id ) ?: get_the_title();
        $meta['description'] = kwl_get_post_meta_description( $post_id ) ?: kwl_get_excerpt( $post_id );
        $meta['canonical']   = get_permalink( $post_id );
        $meta['type']        = 'article';

        $thumbnail_id = get_post_thumbnail_id( $post_id );
        if ( $thumbnail_id ) {
            $image = wp_get_attachment_image_src( $thumbnail_id, 'large' );
            if ( $image ) {
                $meta['image'] = $image[0];
            }
        }

        return $meta;
    }

    return $meta;

}


/* =============================================================================
   OPEN GRAPH OUTPUT
   ============================================================================= */

/**
 * Output Open Graph meta tags.
 *
 * @param array $meta  Meta array from kwl_get_current_page_meta().
 */
function kwl_output_open_graph( array $meta ): void {

    $site_name = get_bloginfo( 'name' );

    $og = [
        'og:site_name'   => $site_name,
        'og:type'        => $meta['type'] ?? 'website',
        'og:title'       => $meta['title'] ?? get_bloginfo( 'name' ),
        'og:description' => $meta['description'] ?? '',
        'og:url'         => $meta['canonical'] ?? home_url( '/' ),
        'og:image'       => $meta['image'] ?? '',
        'og:locale'      => str_replace( '-', '_', get_bloginfo( 'language' ) ),
    ];

    foreach ( $og as $property => $content ) {
        if ( ! empty( $content ) ) {
            printf(
                '<meta property="%s" content="%s">' . "\n",
                esc_attr( $property ),
                esc_attr( $content )
            );
        }
    }

    // OG image dimensions (if we have an image)
    if ( ! empty( $meta['image'] ) ) {
        $attachment_id = attachment_url_to_postid( $meta['image'] );
        if ( $attachment_id ) {
            $img_meta = wp_get_attachment_metadata( $attachment_id );
            if ( $img_meta ) {
                echo '<meta property="og:image:width" content="' . esc_attr( $img_meta['width'] ) . '">' . "\n";
                echo '<meta property="og:image:height" content="' . esc_attr( $img_meta['height'] ) . '">' . "\n";
            }
        }
    }

}


/* =============================================================================
   TWITTER CARD OUTPUT
   ============================================================================= */

/**
 * Output Twitter Card meta tags.
 *
 * @param array $meta
 */
function kwl_output_twitter_card( array $meta ): void {

    $twitter_handle = kwl_get_option( 'twitter_handle', '' );

    $card_type = ! empty( $meta['image'] ) ? 'summary_large_image' : 'summary';

    $twitter = [
        'twitter:card'        => $card_type,
        'twitter:title'       => $meta['title'] ?? '',
        'twitter:description' => $meta['description'] ?? '',
        'twitter:image'       => $meta['image'] ?? '',
    ];

    if ( ! empty( $twitter_handle ) ) {
        $twitter['twitter:site'] = '@' . ltrim( $twitter_handle, '@' );
    }

    foreach ( $twitter as $name => $content ) {
        if ( ! empty( $content ) ) {
            printf(
                '<meta name="%s" content="%s">' . "\n",
                esc_attr( $name ),
                esc_attr( $content )
            );
        }
    }

}


/* =============================================================================
   PAGINATION REL PREV/NEXT
   ============================================================================= */

/**
 * Output rel="prev" and rel="next" link tags for paginated archives.
 * Helps Google understand paginated content.
 */
function kwl_output_pagination_rel(): void {

    global $wp_query, $paged;

    if ( ! is_archive() && ! is_home() ) {
        return;
    }

    $paged     = max( 1, get_query_var( 'paged' ) );
    $max_page  = (int) $wp_query->max_num_pages;

    if ( $paged > 1 ) {
        $prev_url = get_pagenum_link( $paged - 1 );
        echo '<link rel="prev" href="' . esc_url( $prev_url ) . '">' . "\n";
    }

    if ( $paged < $max_page ) {
        $next_url = get_pagenum_link( $paged + 1 );
        echo '<link rel="next" href="' . esc_url( $next_url ) . '">' . "\n";
    }

}


/* =============================================================================
   BREADCRUMBS
   ============================================================================= */

/**
 * Output breadcrumb navigation HTML.
 *
 * Schema markup is handled in inc/schema.php via JSON-LD.
 * This function outputs the visible HTML only.
 *
 * @param bool $echo  Whether to echo or return. Default true.
 * @return string|void
 */
function kwl_breadcrumbs( bool $echo = true ): string|void {

    $crumbs    = kwl_get_breadcrumb_items();
    $site_name = get_bloginfo( 'name' );

    if ( empty( $crumbs ) ) {
        return;
    }

    $html  = '<nav class="cwp-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'kwl-coupon-wp' ) . '">';
    $html .= '<ol class="cwp-breadcrumbs__list">';

    // Home item always first
    $html .= '<li class="cwp-breadcrumbs__item">';
    $html .= '<a href="' . esc_url( home_url( '/' ) ) . '" class="cwp-breadcrumbs__link">' . esc_html( $site_name ) . '</a>';
    $html .= '</li>';

    $total = count( $crumbs );

    foreach ( $crumbs as $i => $crumb ) {
        $is_last = ( $i === $total - 1 );
        $html   .= '<li class="cwp-breadcrumbs__item">';

        if ( $is_last ) {
            $html .= '<span class="cwp-breadcrumbs__current" aria-current="page">' . esc_html( $crumb['label'] ) . '</span>';
        } else {
            $html .= '<a href="' . esc_url( $crumb['url'] ) . '" class="cwp-breadcrumbs__link">' . esc_html( $crumb['label'] ) . '</a>';
        }

        $html .= '</li>';
    }

    $html .= '</ol>';
    $html .= '</nav>';

    if ( $echo ) {
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        return;
    }

    return $html;

}


/**
 * Build the breadcrumb items array for the current page.
 *
 * @return array[] Array of ['label' => string, 'url' => string]
 */
function kwl_get_breadcrumb_items(): array {

    $crumbs = [];

    // Single store
    if ( is_singular( 'kwl_store' ) ) {
        $crumbs[] = [
            'label' => __( 'Stores', 'kwl-coupon-wp' ),
            'url'   => get_post_type_archive_link( 'kwl_store' ),
        ];
        $crumbs[] = [
            'label' => get_the_title(),
            'url'   => get_permalink(),
        ];
        return $crumbs;
    }

    // Single coupon
    if ( is_singular( 'kwl_coupon' ) ) {
        $store_id = kwl_get_coupon_store_id( get_the_ID() );

        $crumbs[] = [
            'label' => __( 'Coupons', 'kwl-coupon-wp' ),
            'url'   => get_post_type_archive_link( 'kwl_coupon' ),
        ];

        if ( $store_id ) {
            $crumbs[] = [
                'label' => get_the_title( $store_id ),
                'url'   => get_permalink( $store_id ),
            ];
        }

        $crumbs[] = [
            'label' => get_the_title(),
            'url'   => get_permalink(),
        ];

        return $crumbs;
    }

    // Store archive
    if ( is_post_type_archive( 'kwl_store' ) ) {
        $crumbs[] = [
            'label' => __( 'Stores', 'kwl-coupon-wp' ),
            'url'   => get_post_type_archive_link( 'kwl_store' ),
        ];
        return $crumbs;
    }

    // Coupon archive
    if ( is_post_type_archive( 'kwl_coupon' ) ) {
        $crumbs[] = [
            'label' => __( 'Coupons', 'kwl-coupon-wp' ),
            'url'   => get_post_type_archive_link( 'kwl_coupon' ),
        ];
        return $crumbs;
    }

    // Category archive
    if ( is_tax( 'kwl_coupon_cat' ) ) {
        $term = get_queried_object();

        $crumbs[] = [
            'label' => __( 'Categories', 'kwl-coupon-wp' ),
            'url'   => get_post_type_archive_link( 'kwl_coupon' ),
        ];

        // Parent category
        if ( $term->parent ) {
            $parent   = get_term( $term->parent, 'kwl_coupon_cat' );
            $crumbs[] = [
                'label' => $parent->name,
                'url'   => get_term_link( $parent ),
            ];
        }

        $crumbs[] = [
            'label' => $term->name,
            'url'   => get_term_link( $term ),
        ];

        return $crumbs;
    }

    // Tag archive
    if ( is_tax( 'kwl_coupon_tag' ) ) {
        $term     = get_queried_object();
        $crumbs[] = [
            'label' => $term->name,
            'url'   => get_term_link( $term ),
        ];
        return $crumbs;
    }

    // Search
    if ( is_search() ) {
        $crumbs[] = [
            'label' => sprintf(
                /* translators: %s = search query */
                __( 'Search results for "%s"', 'kwl-coupon-wp' ),
                get_search_query()
            ),
            'url' => '',
        ];
        return $crumbs;
    }

    // Regular page
    if ( is_page() ) {
        $crumbs[] = [
            'label' => get_the_title(),
            'url'   => get_permalink(),
        ];
        return $crumbs;
    }

    return $crumbs;

}


/* =============================================================================
   HELPER FUNCTIONS
   ============================================================================= */

/**
 * Get a post's custom meta title (from meta box field).
 *
 * @param  int $post_id
 * @return string
 */
function kwl_get_post_meta_title( int $post_id ): string {
    return sanitize_text_field( get_post_meta( $post_id, '_kwl_meta_title', true ) );
}


/**
 * Get a post's custom meta description (from meta box field).
 *
 * @param  int $post_id
 * @return string
 */
function kwl_get_post_meta_description( int $post_id ): string {
    return sanitize_textarea_field( get_post_meta( $post_id, '_kwl_meta_description', true ) );
}


/**
 * Get a plain text excerpt for a post.
 *
 * Strips HTML, shortcodes, and limits to ~160 chars.
 *
 * @param  int $post_id
 * @param  int $length  Character limit.
 * @return string
 */
function kwl_get_excerpt( int $post_id, int $length = 160 ): string {

    $post    = get_post( $post_id );
    $content = $post->post_excerpt ?: $post->post_content;
    $content = wp_strip_all_tags( strip_shortcodes( $content ) );
    $content = preg_replace( '/\s+/', ' ', trim( $content ) );

    if ( strlen( $content ) > $length ) {
        $content = substr( $content, 0, $length );
        $content = substr( $content, 0, strrpos( $content, ' ' ) ) . '…';
    }

    return $content;

}


/**
 * Get the default OG image URL.
 *
 * Uses theme setting > site icon > empty.
 *
 * @return string
 */
function kwl_get_default_og_image(): string {

    // From theme settings
    $custom = kwl_get_option( 'og_default_image', '' );
    if ( ! empty( $custom ) ) {
        return esc_url( $custom );
    }

    // Site icon (WordPress core)
    $site_icon_id = get_option( 'site_icon' );
    if ( $site_icon_id ) {
        $image = wp_get_attachment_image_src( $site_icon_id, 'large' );
        if ( $image ) {
            return esc_url( $image[0] );
        }
    }

    return '';

}


/**
 * Filter WordPress title tag for CPT archives.
 *
 * Supplements what's already in functions.php.
 *
 * @param  array $parts
 * @return array
 */
function kwl_filter_title_parts( array $parts ): array {

    // Remove "Page 2" suffix from title (keep it clean)
    if ( isset( $parts['page'] ) && get_query_var( 'paged' ) > 1 ) {
        // Keep the page suffix — it's useful for users
    }

    // Use custom meta title if set
    if ( is_singular( [ 'kwl_store', 'kwl_coupon' ] ) ) {
        $post_id = get_the_ID();
        $custom  = kwl_get_post_meta_title( $post_id );
        if ( ! empty( $custom ) ) {
            $parts['title'] = $custom;
        }
    }

    return $parts;

}
add_filter( 'document_title_parts', 'kwl_filter_title_parts', 20 );
