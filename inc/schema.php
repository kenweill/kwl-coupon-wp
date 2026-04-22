<?php
/**
 * KWL Coupon WP — Schema Markup
 *
 * Outputs JSON-LD structured data for Google rich results.
 *
 * Schema types used:
 * - WebSite          : Sitelinks search box eligibility
 * - Organization     : Site identity on homepage
 * - Store            : Single store pages
 * - ItemList         : Store archive and category archive pages
 * - Offer / Coupon   : Single coupon pages
 * - BreadcrumbList   : All pages with breadcrumbs
 *
 * @see https://schema.org/
 * @see https://developers.google.com/search/docs/appearance/structured-data
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   MAIN HOOK
   ============================================================================= */

/**
 * Output all JSON-LD schema for the current page.
 *
 * Runs in wp_head at priority 5.
 */
function kwl_output_schema(): void {

    if ( is_admin() ) {
        return;
    }

    $schemas = [];

    // WebSite schema — on every page (enables sitelinks search box)
    $schemas[] = kwl_schema_website();

    // Page-specific schemas
    if ( is_front_page() ) {
        $schemas[] = kwl_schema_organization();
    }

    if ( is_singular( 'kwl_store' ) ) {
        $schemas[] = kwl_schema_store( get_the_ID() );
    }

    if ( is_singular( 'kwl_coupon' ) ) {
        $schemas[] = kwl_schema_coupon( get_the_ID() );
    }

    if ( is_post_type_archive( 'kwl_store' ) || is_post_type_archive( 'kwl_coupon' ) ) {
        $schemas[] = kwl_schema_item_list_from_query();
    }

    if ( is_tax( 'kwl_coupon_cat' ) || is_tax( 'kwl_coupon_tag' ) ) {
        $schemas[] = kwl_schema_item_list_from_query();
    }

    // BreadcrumbList — all pages that have breadcrumbs
    $breadcrumbs = kwl_schema_breadcrumb_list();
    if ( $breadcrumbs ) {
        $schemas[] = $breadcrumbs;
    }

    // Output each schema block
    foreach ( array_filter( $schemas ) as $schema ) {
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        echo "\n" . '</script>' . "\n";
    }

}
add_action( 'wp_head', 'kwl_output_schema', 5 );


/* =============================================================================
   SCHEMA: WEBSITE
   ============================================================================= */

/**
 * WebSite schema — enables Google Sitelinks Search Box.
 *
 * @return array
 */
function kwl_schema_website(): array {

    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        '@id'             => home_url( '/#website' ),
        'url'             => home_url( '/' ),
        'name'            => get_bloginfo( 'name' ),
        'description'     => get_bloginfo( 'description' ),
        'inLanguage'      => get_bloginfo( 'language' ),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => home_url( '/?s={search_term_string}' ),
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

}


/* =============================================================================
   SCHEMA: ORGANIZATION
   ============================================================================= */

/**
 * Organization schema for homepage.
 *
 * @return array
 */
function kwl_schema_organization(): array {

    $schema = [
        '@context'  => 'https://schema.org',
        '@type'     => 'Organization',
        '@id'       => home_url( '/#organization' ),
        'url'       => home_url( '/' ),
        'name'      => get_bloginfo( 'name' ),
        'sameAs'    => [],
    ];

    // Logo
    $logo_id = get_theme_mod( 'custom_logo' );
    if ( $logo_id ) {
        $logo_src = wp_get_attachment_image_src( $logo_id, 'full' );
        if ( $logo_src ) {
            $schema['logo'] = [
                '@type'  => 'ImageObject',
                'url'    => $logo_src[0],
                'width'  => $logo_src[1],
                'height' => $logo_src[2],
            ];
        }
    }

    // Social profiles
    $social_fields = [
        'social_twitter'   => kwl_get_option( 'social_twitter',   '' ),
        'social_facebook'  => kwl_get_option( 'social_facebook',  '' ),
        'social_instagram' => kwl_get_option( 'social_instagram', '' ),
        'social_pinterest' => kwl_get_option( 'social_pinterest', '' ),
        'social_youtube'   => kwl_get_option( 'social_youtube',   '' ),
    ];

    foreach ( $social_fields as $profile ) {
        if ( ! empty( $profile ) ) {
            $schema['sameAs'][] = esc_url( $profile );
        }
    }

    if ( empty( $schema['sameAs'] ) ) {
        unset( $schema['sameAs'] );
    }

    return $schema;

}


/* =============================================================================
   SCHEMA: STORE
   ============================================================================= */

/**
 * Store schema for single store pages.
 *
 * Uses schema.org/Store type.
 *
 * @param  int $store_id
 * @return array
 */
function kwl_schema_store( int $store_id ): array {

    $store_name    = get_the_title( $store_id );
    $store_url     = get_permalink( $store_id );
    $website_url   = get_post_meta( $store_id, '_kwl_store_website', true );
    $description   = get_the_excerpt( $store_id ) ?: get_post_field( 'post_content', $store_id );
    $description   = wp_strip_all_tags( $description );
    $coupon_count  = kwl_get_store_coupon_count( $store_id );
    $logo_url      = kwl_get_store_logo_url( $store_id, 'kwl-store-logo' );

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Store',
        '@id'         => $store_url . '#store',
        'name'        => $store_name,
        'url'         => $store_url,
        'numberOfEmployees' => null, // Not relevant but schema valid without it
    ];

    if ( ! empty( $description ) ) {
        $schema['description'] = $description;
    }

    if ( ! empty( $website_url ) ) {
        $schema['sameAs'] = [ esc_url( $website_url ) ];
    }

    if ( ! empty( $logo_url ) ) {
        $schema['image'] = [
            '@type' => 'ImageObject',
            'url'   => $logo_url,
        ];
    }

    // Aggregate offer data — how many coupons this store has
    if ( $coupon_count > 0 ) {
        $schema['potentialAction'] = [
            '@type'  => 'ViewAction',
            'target' => $store_url,
            'name'   => sprintf(
                /* translators: %d = coupon count */
                _n( 'View %d Coupon', 'View %d Coupons', $coupon_count, 'kwl-coupon-wp' ),
                $coupon_count
            ),
        ];
    }

    // Remove null values
    $schema = array_filter( $schema, fn( $v ) => $v !== null );

    return $schema;

}


/* =============================================================================
   SCHEMA: COUPON / OFFER
   ============================================================================= */

/**
 * Coupon schema for single coupon pages.
 *
 * Uses schema.org/Offer with priceSpecification.
 *
 * @param  int $coupon_id
 * @return array
 */
function kwl_schema_coupon( int $coupon_id ): array {

    $title         = get_the_title( $coupon_id );
    $coupon_url    = get_permalink( $coupon_id );
    $store_id      = kwl_get_coupon_store_id( $coupon_id );
    $store_name    = $store_id ? get_the_title( $store_id ) : '';
    $store_url     = $store_id ? get_permalink( $store_id ) : '';
    $code          = kwl_get_coupon_code( $coupon_id );
    $type          = kwl_get_coupon_type( $coupon_id );
    $discount      = kwl_get_coupon_discount( $coupon_id );
    $expiry        = get_post_meta( $coupon_id, '_kwl_coupon_expiry', true );
    $verified      = kwl_is_coupon_verified( $coupon_id );
    $is_expired    = kwl_is_expired( $coupon_id );
    $affiliate_url = kwl_get_coupon_affiliate_url( $coupon_id );
    $description   = get_the_excerpt( $coupon_id ) ?: $title;
    $votes         = kwl_get_coupon_votes( $coupon_id );

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Offer',
        '@id'         => $coupon_url . '#offer',
        'name'        => $title,
        'description' => wp_strip_all_tags( $description ),
        'url'         => $coupon_url,
        'availability'=> $is_expired
            ? 'https://schema.org/Discontinued'
            : 'https://schema.org/InStock',
    ];

    // Coupon code
    if ( ! empty( $code ) && $type === 'code' ) {
        // schema.org doesn't have a native coupon code field in Offer,
        // but we can signal it via name + identifier
        $schema['identifier'] = $code;
    }

    // Seller (the store)
    if ( ! empty( $store_name ) ) {
        $schema['seller'] = [
            '@type' => 'Organization',
            'name'  => $store_name,
            'url'   => $store_url,
        ];
    }

    // Price — for offers with no specific price, we use price 0
    $schema['price']         = '0';
    $schema['priceCurrency'] = kwl_get_option( 'currency_code', 'USD' );

    // Expiry date
    if ( ! empty( $expiry ) ) {
        $schema['priceValidUntil'] = $expiry; // Y-m-d format
    }

    // Valid from (publish date)
    $schema['validFrom'] = get_post_time( 'c', true, $coupon_id );

    // Aggregate rating from votes
    if ( $votes['total'] >= 3 ) {
        $schema['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => round( $votes['rate'] / 20, 1 ), // Convert % to 1-5 scale
            'bestRating'  => '5',
            'worstRating' => '1',
            'ratingCount' => $votes['total'],
        ];
    }

    // Affiliate link as offerURL
    if ( ! empty( $affiliate_url ) ) {
        $schema['url'] = $affiliate_url;
    }

    return $schema;

}


/* =============================================================================
   SCHEMA: ITEM LIST
   ============================================================================= */

/**
 * ItemList schema for archive pages.
 *
 * Used on store archive, coupon archive, and category archives.
 *
 * @return array|null
 */
function kwl_schema_item_list_from_query(): ?array {

    global $wp_query;

    if ( empty( $wp_query->posts ) ) {
        return null;
    }

    $items = [];
    $position = 1;

    foreach ( $wp_query->posts as $post ) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'url'      => get_permalink( $post->ID ),
            'name'     => get_the_title( $post->ID ),
        ];
        $position++;
    }

    if ( empty( $items ) ) {
        return null;
    }

    $list_name = '';
    if ( is_post_type_archive( 'kwl_store' ) ) {
        $list_name = __( 'All Stores', 'kwl-coupon-wp' );
    } elseif ( is_post_type_archive( 'kwl_coupon' ) ) {
        $list_name = __( 'Latest Coupons', 'kwl-coupon-wp' );
    } elseif ( is_tax() ) {
        $term      = get_queried_object();
        $list_name = $term->name ?? '';
    }

    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => $list_name,
        'numberOfItems'   => count( $items ),
        'itemListElement' => $items,
    ];

}


/* =============================================================================
   SCHEMA: BREADCRUMB LIST
   ============================================================================= */

/**
 * BreadcrumbList schema.
 *
 * Uses breadcrumb items from kwl_get_breadcrumb_items() in seo.php.
 *
 * @return array|null
 */
function kwl_schema_breadcrumb_list(): ?array {

    $crumbs = kwl_get_breadcrumb_items();

    if ( empty( $crumbs ) ) {
        return null;
    }

    $list_elements = [];

    // Home item always first at position 1
    $list_elements[] = [
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => get_bloginfo( 'name' ),
        'item'     => home_url( '/' ),
    ];

    $position = 2;

    foreach ( $crumbs as $crumb ) {
        $element = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => $crumb['label'],
        ];

        // Only include 'item' if the crumb has a URL (last item may not)
        if ( ! empty( $crumb['url'] ) ) {
            $element['item'] = $crumb['url'];
        }

        $list_elements[] = $element;
        $position++;
    }

    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $list_elements,
    ];

}


/* =============================================================================
   SCHEMA: FAQ (Optional — for single store/coupon pages)
   ============================================================================= */

/**
 * Generate FAQ schema for a store page.
 *
 * Adds common "people also ask" style FAQ questions
 * that help with rich results and featured snippets.
 *
 * @param  int $store_id
 * @return array|null
 */
function kwl_schema_store_faq( int $store_id ): ?array {

    $store_name   = get_the_title( $store_id );
    $coupon_count = kwl_get_store_coupon_count( $store_id );
    $site_name    = get_bloginfo( 'name' );

    if ( $coupon_count === 0 ) {
        return null;
    }

    $questions = [
        [
            'question' => sprintf(
                /* translators: %s = store name */
                __( 'Does %s offer coupon codes?', 'kwl-coupon-wp' ),
                $store_name
            ),
            'answer' => sprintf(
                /* translators: 1: store name, 2: coupon count, 3: site name */
                _n(
                    'Yes. %1$s currently has %2$d verified coupon code available at %3$s.',
                    'Yes. %1$s currently has %2$d verified coupon codes available at %3$s.',
                    $coupon_count,
                    'kwl-coupon-wp'
                ),
                $store_name,
                $coupon_count,
                $site_name
            ),
        ],
        [
            'question' => sprintf(
                /* translators: %s = store name */
                __( 'How do I use a %s coupon code?', 'kwl-coupon-wp' ),
                $store_name
            ),
            'answer' => sprintf(
                /* translators: %s = store name */
                __( 'Click "Show Code" on any coupon to reveal the code. Copy it, then paste it into the promo code field at checkout on %s\'s website.', 'kwl-coupon-wp' ),
                $store_name
            ),
        ],
    ];

    $faq_items = [];

    foreach ( $questions as $q ) {
        $faq_items[] = [
            '@type'          => 'Question',
            'name'           => $q['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $q['answer'],
            ],
        ];
    }

    return [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faq_items,
    ];

}
