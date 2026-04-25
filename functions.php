<?php
/**
 * KWL Coupon WP — functions.php
 *
 * Main theme bootstrap file. Loads all includes, registers
 * theme support, and enqueues assets.
 *
 * @package   KWL_Coupon_WP
 * @author    Ken Weill <https://github.com/kenweill>
 * @link      https://github.com/kenweill/kwl-coupon-wp
 * @license   GNU General Public License v2
 * @version   1.0.0
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   CONSTANTS
   ============================================================================= */

define( 'KWL_VERSION',   '1.0.0' );
define( 'KWL_DIR',       get_template_directory() );
define( 'KWL_URI',       get_template_directory_uri() );
define( 'KWL_INC',       KWL_DIR . '/inc/' );
define( 'KWL_ASSETS',    KWL_URI . '/assets/' );


/* =============================================================================
   INCLUDES
   ============================================================================= */

// Core functionality
require_once KWL_INC . 'post-types.php';          // Custom post types: Store, Coupon
require_once KWL_INC . 'taxonomies.php';           // Categories, Tags
require_once KWL_INC . 'slug-handler.php';         // Smart slug generation & duplicate check
require_once KWL_INC . 'coupon-functions.php';     // Coupon helpers, reveal logic, expiry
require_once KWL_INC . 'template-functions.php';   // Pagination helper, supplemental CSS
require_once KWL_INC . 'seo.php';                  // Meta tags, Open Graph, canonical
require_once KWL_INC . 'schema.php';               // JSON-LD structured data
require_once KWL_INC . 'sitemap.php';              // XML sitemap (no plugin needed)
require_once KWL_INC . 'admin-settings.php';       // Theme options page
require_once KWL_INC . 'customizer.php';           // WordPress Customizer controls
require_once KWL_INC . 'preset-loader.php';        // Style preset switcher
require_once KWL_INC . 'csv-import.php';           // Bulk coupon/store importer
require_once KWL_INC . 'admin-columns.php';        // Custom admin list table columns
require_once KWL_INC . 'widgets.php';              // Sidebar widgets


/* =============================================================================
   THEME SETUP
   ============================================================================= */

/**
 * Register theme support features and set content width.
 */
/**
 * Load theme textdomain early and cleanly.
 * Separated from kwl_theme_setup to avoid WP 6.7 timing notice.
 */
function kwl_load_textdomain(): void {
    load_theme_textdomain( 'kwl-coupon-wp', KWL_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'kwl_load_textdomain', 1 );


function kwl_theme_setup(): void {

    // Feed links in <head>
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the <title> tag
    add_theme_support( 'title-tag' );

    // Featured image support (used for store logos, coupon images)
    add_theme_support( 'post-thumbnails' );
    set_post_thumbnail_size( 300, 300, true );
    add_image_size( 'kwl-store-logo',   120, 120, true  );
    add_image_size( 'kwl-store-logo-sm', 64,  64, true  );
    add_image_size( 'kwl-coupon-thumb', 400, 300, true  );

    // HTML5 markup for core elements
    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ] );

    // Custom logo support
    add_theme_support( 'custom-logo', [
        'height'               => 60,
        'width'                => 200,
        'flex-height'          => true,
        'flex-width'           => true,
        'unlink-homepage-logo' => false,
    ] );

    // Custom background
    add_theme_support( 'custom-background', [
        'default-color' => 'f8fafc',
    ] );

    // WordPress block styles (for editor parity)
    add_theme_support( 'wp-block-styles' );

    // Responsive embeds
    add_theme_support( 'responsive-embeds' );

    // Register navigation menus
    register_nav_menus( [
        'kwl-primary'  => __( 'Primary Navigation (Categories)', 'kwl-coupon-wp' ),
        'kwl-footer-1' => __( 'Footer Column 1',                 'kwl-coupon-wp' ),
        'kwl-footer-2' => __( 'Footer Column 2',                 'kwl-coupon-wp' ),
        'kwl-footer-3' => __( 'Footer Column 3',                 'kwl-coupon-wp' ),
    ] );

    // Content width — used by WordPress core for media embeds
    $GLOBALS['content_width'] = 900;

}
add_action( 'after_setup_theme', 'kwl_theme_setup' );


/* =============================================================================
   REGISTER SIDEBARS / WIDGET AREAS
   ============================================================================= */

/**
 * Register sidebar widget areas.
 */
function kwl_register_sidebars(): void {

    $defaults = [
        'before_widget' => '<div id="%1$s" class="cwp-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="cwp-widget__title">',
        'after_title'   => '</h3><div class="cwp-widget__body">',
    ];

    register_sidebar( array_merge( $defaults, [
        'name'        => __( 'Main Sidebar', 'kwl-coupon-wp' ),
        'id'          => 'kwl-sidebar-main',
        'description' => __( 'Appears on store, coupon, and category pages.', 'kwl-coupon-wp' ),
    ] ) );

    register_sidebar( array_merge( $defaults, [
        'name'        => __( 'Homepage Sidebar', 'kwl-coupon-wp' ),
        'id'          => 'kwl-sidebar-home',
        'description' => __( 'Appears on the homepage only.', 'kwl-coupon-wp' ),
    ] ) );

    register_sidebar( array_merge( $defaults, [
        'name'        => __( 'Ad — Header Banner', 'kwl-coupon-wp' ),
        'id'          => 'kwl-ad-header',
        'description' => __( 'Ad slot below the header. Recommended: 728×90 leaderboard.', 'kwl-coupon-wp' ),
        'before_widget' => '<div class="cwp-ad-slot cwp-ad-slot--header">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="cwp-sr-only">',
        'after_title'   => '</span>',
    ] ) );

    register_sidebar( array_merge( $defaults, [
        'name'        => __( 'Ad — Sidebar', 'kwl-coupon-wp' ),
        'id'          => 'kwl-ad-sidebar',
        'description' => __( 'Ad slot in sidebar. Recommended: 300×250 rectangle.', 'kwl-coupon-wp' ),
        'before_widget' => '<div class="cwp-ad-slot cwp-ad-slot--sidebar">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="cwp-sr-only">',
        'after_title'   => '</span>',
    ] ) );

    register_sidebar( array_merge( $defaults, [
        'name'        => __( 'Ad — In Feed', 'kwl-coupon-wp' ),
        'id'          => 'kwl-ad-infeed',
        'description' => __( 'Ad inserted within coupon listings. Recommended: 728×90.', 'kwl-coupon-wp' ),
        'before_widget' => '<div class="cwp-ad-slot cwp-ad-slot--infeed">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="cwp-sr-only">',
        'after_title'   => '</span>',
    ] ) );

}
add_action( 'widgets_init', 'kwl_register_sidebars' );


/* =============================================================================
   ENQUEUE STYLES & SCRIPTS
   ============================================================================= */

/**
 * Enqueue frontend assets.
 * Zero jQuery dependency — pure vanilla JS.
 */
function kwl_enqueue_assets(): void {

    // Google Fonts — Figtree (clean, modern, free)
    // Loaded only if user hasn't disabled it in theme settings
    if ( kwl_get_option( 'load_google_fonts', true ) ) {
        wp_enqueue_style(
            'kwl-google-fonts',
            'https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap',
            [],
            null
        );
    }

    // Main theme stylesheet
    wp_enqueue_style(
        'kwl-style',
        get_stylesheet_uri(),
        [ 'kwl-google-fonts' ],
        KWL_VERSION
    );

    // Active preset CSS (overrides CSS variables)
    $active_preset = kwl_get_option( 'active_preset', 'clean' );
    $preset_file   = KWL_DIR . '/presets/' . $active_preset . '/preset.css';

    if ( file_exists( $preset_file ) ) {
        wp_enqueue_style(
            'kwl-preset',
            KWL_URI . '/presets/' . $active_preset . '/preset.css',
            [ 'kwl-style' ],
            KWL_VERSION
        );
    }

    // Customizer inline CSS (user color overrides)
    $custom_css = kwl_generate_customizer_css();
    if ( ! empty( $custom_css ) ) {
        wp_add_inline_style( 'kwl-style', $custom_css );
    }

    // User custom CSS from theme settings
    $user_css = kwl_get_option( 'custom_css', '' );
    if ( ! empty( trim( $user_css ) ) ) {
        wp_add_inline_style( 'kwl-style', wp_strip_all_tags( $user_css ) );
    }

    // Main JS — coupon reveal, vote buttons, search, mobile menu
    wp_enqueue_script(
        'kwl-main',
        KWL_ASSETS . 'js/main.js',
        [],             // No jQuery dependency
        KWL_VERSION,
        true            // Footer
    );

    // Pass data to JS
    wp_localize_script( 'kwl-main', 'kwlData', [
        'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
        'nonce'        => wp_create_nonce( 'kwl_nonce' ),
        'revealText'   => __( 'Show Code',  'kwl-coupon-wp' ),
        'copiedText'   => __( 'Copied!',    'kwl-coupon-wp' ),
        'copyText'     => __( 'Copy Code',  'kwl-coupon-wp' ),
        'getDealText'  => __( 'Get Deal',   'kwl-coupon-wp' ),
        'expiredText'  => __( 'Expired',    'kwl-coupon-wp' ),
        'strings' => [
            'voteSuccess'  => __( 'Thanks for your feedback!', 'kwl-coupon-wp' ),
            'voteDuplicate'=> __( 'You already voted on this.', 'kwl-coupon-wp' ),
            'voteError'    => __( 'Something went wrong. Try again.', 'kwl-coupon-wp' ),
        ],
    ] );

    // Search autocomplete (only if enabled in settings)
    if ( kwl_get_option( 'enable_search_autocomplete', true ) ) {
        wp_enqueue_script(
            'kwl-search',
            KWL_ASSETS . 'js/search.js',
            [ 'kwl-main' ],
            KWL_VERSION,
            true
        );
    }

    // Comment reply script (only on singular with comments open)
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }

}
add_action( 'wp_enqueue_scripts', 'kwl_enqueue_assets' );


/**
 * Enqueue admin assets.
 */
function kwl_enqueue_admin_assets( string $hook ): void {

    wp_enqueue_style(
        'kwl-admin',
        KWL_ASSETS . 'css/admin.css',
        [],
        KWL_VERSION
    );

    // Admin JS — slug generator, duplicate checker, CSV import UI
    wp_enqueue_script(
        'kwl-admin',
        KWL_ASSETS . 'js/admin.js',
        [ 'jquery' ],   // Admin uses jQuery (it's already loaded in WP admin)
        KWL_VERSION,
        true
    );

    wp_localize_script( 'kwl-admin', 'kwlAdmin', [
        'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
        'nonce'            => wp_create_nonce( 'kwl_admin_nonce' ),
        'slugCheckUrl'     => admin_url( 'admin-ajax.php?action=kwl_check_slug' ),
        'strings' => [
            'slugAvailable'  => __( 'Slug is available.', 'kwl-coupon-wp' ),
            'slugTaken'      => __( 'Slug already in use.', 'kwl-coupon-wp' ),
            'slugSuggestion' => __( 'Suggested:', 'kwl-coupon-wp' ),
            'importing'      => __( 'Importing...', 'kwl-coupon-wp' ),
            'importDone'     => __( 'Import complete.', 'kwl-coupon-wp' ),
        ],
    ] );

}
add_action( 'admin_enqueue_scripts', 'kwl_enqueue_admin_assets' );


/* =============================================================================
   PRECONNECT HINTS
   ============================================================================= */

/**
 * Add preconnect hints for external domains.
 * Improves loading speed for Google Fonts and affiliate redirects.
 */
function kwl_preconnect_hints(): void {
    if ( kwl_get_option( 'load_google_fonts', true ) ) {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    }
}
add_action( 'wp_head', 'kwl_preconnect_hints', 1 );


/* =============================================================================
   BODY CLASSES
   ============================================================================= */

/**
 * Add useful body classes for CSS targeting.
 *
 * @param array $classes
 * @return array
 */
function kwl_body_classes( array $classes ): array {

    // Add preset class
    $preset    = kwl_get_option( 'active_preset', 'clean' );
    $classes[] = 'kwl-preset-' . sanitize_html_class( $preset );

    // Add layout class
    $layout    = kwl_get_option( 'layout', 'sidebar-right' );
    $classes[] = 'kwl-layout-' . sanitize_html_class( $layout );

    // CPT-specific classes
    if ( is_singular( 'kwl_store' ) ) {
        $classes[] = 'kwl-single-store';
    }

    if ( is_singular( 'kwl_coupon' ) ) {
        $classes[] = 'kwl-single-coupon';
    }

    if ( is_post_type_archive( 'kwl_store' ) ) {
        $classes[] = 'kwl-archive-stores';
    }

    return $classes;

}
add_action( 'body_class', 'kwl_body_classes' );


/* =============================================================================
   TITLE TAG
   ============================================================================= */

/**
 * Filter document title for custom post type archives and singles.
 *
 * @param array $title
 * @return array
 */
function kwl_document_title( array $title ): array {

    if ( is_post_type_archive( 'kwl_store' ) ) {
        $title['title'] = __( 'All Stores', 'kwl-coupon-wp' );
    }

    if ( is_singular( 'kwl_store' ) ) {
        $store_name      = get_the_title();
        $title['title']  = sprintf(
            /* translators: %s = store name */
            __( '%s Coupons & Promo Codes', 'kwl-coupon-wp' ),
            $store_name
        );
    }

    return $title;

}
add_filter( 'document_title_parts', 'kwl_document_title' );


/* =============================================================================
   EXCERPT
   ============================================================================= */

/**
 * Set excerpt length.
 */
function kwl_excerpt_length(): int {
    return 20;
}
add_filter( 'excerpt_length', 'kwl_excerpt_length' );

/**
 * Set excerpt more string.
 */
function kwl_excerpt_more(): string {
    return '&hellip;';
}
add_filter( 'excerpt_more', 'kwl_excerpt_more' );


/* =============================================================================
   AJAX HANDLERS
   ============================================================================= */

/**
 * AJAX: Coupon reveal — returns the actual coupon code.
 * Increments click count. Validates nonce.
 */
function kwl_ajax_reveal_coupon(): void {

    check_ajax_referer( 'kwl_nonce', 'nonce' );

    $coupon_id = absint( $_POST['coupon_id'] ?? 0 );

    if ( ! $coupon_id || get_post_type( $coupon_id ) !== 'kwl_coupon' ) {
        wp_send_json_error( [ 'message' => __( 'Invalid coupon.', 'kwl-coupon-wp' ) ] );
    }

    $code        = get_post_meta( $coupon_id, '_kwl_coupon_code', true );
    $type        = get_post_meta( $coupon_id, '_kwl_coupon_type', true );
    $affiliate   = kwl_get_store_affiliate_url( $coupon_id );

    // Increment click counter
    $clicks = (int) get_post_meta( $coupon_id, '_kwl_click_count', true );
    update_post_meta( $coupon_id, '_kwl_click_count', $clicks + 1 );

    wp_send_json_success( [
        'code'          => esc_html( $code ),
        'type'          => esc_attr( $type ),
        'affiliate_url' => esc_url( $affiliate ),
    ] );

}
add_action( 'wp_ajax_kwl_reveal_coupon',        'kwl_ajax_reveal_coupon' );
add_action( 'wp_ajax_nopriv_kwl_reveal_coupon', 'kwl_ajax_reveal_coupon' );


/**
 * AJAX: Coupon vote (works / doesn't work).
 */
function kwl_ajax_vote_coupon(): void {

    check_ajax_referer( 'kwl_nonce', 'nonce' );

    $coupon_id = absint( $_POST['coupon_id'] ?? 0 );
    $vote      = sanitize_key( $_POST['vote'] ?? '' );

    if ( ! $coupon_id || ! in_array( $vote, [ 'up', 'down' ], true ) ) {
        wp_send_json_error( [ 'message' => __( 'Invalid request.', 'kwl-coupon-wp' ) ] );
    }

    // Simple session-based duplicate prevention
    $session_key = 'kwl_voted_' . $coupon_id;
    if ( ! empty( $_COOKIE[ $session_key ] ) ) {
        wp_send_json_error( [ 'message' => __( 'You already voted.', 'kwl-coupon-wp' ), 'duplicate' => true ] );
    }

    $meta_key = $vote === 'up' ? '_kwl_votes_up' : '_kwl_votes_down';
    $count    = (int) get_post_meta( $coupon_id, $meta_key, true );
    update_post_meta( $coupon_id, $meta_key, $count + 1 );

    $up   = (int) get_post_meta( $coupon_id, '_kwl_votes_up',   true );
    $down = (int) get_post_meta( $coupon_id, '_kwl_votes_down', true );

    wp_send_json_success( [
        'votes_up'   => $up,
        'votes_down' => $down,
    ] );

}
add_action( 'wp_ajax_kwl_vote_coupon',        'kwl_ajax_vote_coupon' );
add_action( 'wp_ajax_nopriv_kwl_vote_coupon', 'kwl_ajax_vote_coupon' );


/**
 * AJAX: Check if a slug is available (admin use).
 */
function kwl_ajax_check_slug(): void {

    check_ajax_referer( 'kwl_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error();
    }

    $slug      = kwl_sanitize_slug( sanitize_text_field( $_POST['slug'] ?? '' ) );
    $post_type = sanitize_key( $_POST['post_type'] ?? 'kwl_store' );
    $post_id   = absint( $_POST['post_id'] ?? 0 );

    $available  = kwl_is_slug_available( $slug, $post_type, $post_id );
    $suggestion = $available ? $slug : kwl_unique_slug( $slug, $post_type, $post_id );

    wp_send_json_success( [
        'slug'        => $slug,
        'available'   => $available,
        'suggestion'  => $suggestion,
    ] );

}
add_action( 'wp_ajax_kwl_check_slug', 'kwl_ajax_check_slug' );


/* =============================================================================
   SEARCH — INCLUDE CUSTOM POST TYPES
   ============================================================================= */

/**
 * Include stores and coupons in WordPress search results.
 *
 * @param WP_Query $query
 */
function kwl_include_cpt_in_search( WP_Query $query ): void {

    if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        $query->set( 'post_type', [ 'post', 'kwl_store', 'kwl_coupon' ] );
    }

}
add_action( 'pre_get_posts', 'kwl_include_cpt_in_search' );


/* =============================================================================
   OPTION HELPER
   ============================================================================= */

/**
 * Get a theme option with a fallback default.
 *
 * @param string $key     Option key.
 * @param mixed  $default Default value if option not set.
 * @return mixed
 */
function kwl_get_option( string $key, mixed $default = '' ): mixed {

    $options = get_option( 'kwl_theme_options', [] );
    return $options[ $key ] ?? $default;

}

/**
 * Save a theme option.
 *
 * @param string $key   Option key.
 * @param mixed  $value Option value.
 */
function kwl_update_option( string $key, mixed $value ): void {

    $options         = get_option( 'kwl_theme_options', [] );
    $options[ $key ] = $value;
    update_option( 'kwl_theme_options', $options );

}


/* =============================================================================
   CUSTOMIZER CSS GENERATOR
   ============================================================================= */

/**
 * Generate inline CSS from Customizer settings.
 * Overrides CSS variables set by the active preset.
 *
 * @return string CSS string.
 */
function kwl_generate_customizer_css(): string {

    $mods = [
        'kwl_color_primary'   => '--cwp-primary',
        'kwl_color_accent'    => '--cwp-accent',
        'kwl_color_bg'        => '--cwp-bg',
        'kwl_color_surface'   => '--cwp-surface',
        'kwl_color_text'      => '--cwp-text',
        'kwl_border_radius'   => '--cwp-radius',
    ];

    $css_vars = [];

    foreach ( $mods as $mod_key => $css_var ) {
        $value = get_theme_mod( $mod_key, '' );
        if ( ! empty( $value ) ) {
            $css_vars[] = sprintf( '%s: %s;', $css_var, esc_attr( $value ) );
        }
    }

    if ( empty( $css_vars ) ) {
        return '';
    }

    return ':root { ' . implode( ' ', $css_vars ) . ' }';

}


/* =============================================================================
   ADMIN NOTICES
   ============================================================================= */

/**
 * Show a notice if permalink structure is not set to Post Name.
 * Coupon/store slugs won't work properly without this.
 */
function kwl_permalink_notice(): void {

    $structure = get_option( 'permalink_structure' );

    if ( empty( $structure ) || $structure === '/?p=%post_id%' ) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php esc_html_e( 'KWL Coupon WP:', 'kwl-coupon-wp' ); ?></strong>
                <?php
                printf(
                    /* translators: %s = link to permalink settings */
                    esc_html__( 'For best SEO results, please set your %s to "Post name".', 'kwl-coupon-wp' ),
                    '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' .
                    esc_html__( 'permalink structure', 'kwl-coupon-wp' ) .
                    '</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }

}
add_action( 'admin_notices', 'kwl_permalink_notice' );


/* =============================================================================
   CLEANUP
   ============================================================================= */

/**
 * Remove unnecessary WordPress head bloat.
 * Keeps the <head> clean for performance.
 */
function kwl_cleanup_head(): void {

    remove_action( 'wp_head', 'wp_generator' );                         // WordPress version
    remove_action( 'wp_head', 'wlwmanifest_link' );                     // Windows Live Writer
    remove_action( 'wp_head', 'rsd_link' );                             // Really Simple Discovery
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );                 // Shortlink
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );  // Prev/next rel links

}
add_action( 'init', 'kwl_cleanup_head' );


/* =============================================================================
   DISABLE COMMENTS ON CUSTOM POST TYPES
   ============================================================================= */

/**
 * Disable comments on stores and coupons — not relevant for CPTs.
 *
 * @param bool   $open
 * @param int    $post_id
 * @return bool
 */
function kwl_disable_cpt_comments( bool $open, int $post_id ): bool {

    $type = get_post_type( $post_id );

    if ( in_array( $type, [ 'kwl_store', 'kwl_coupon' ], true ) ) {
        return false;
    }

    return $open;

}
add_filter( 'comments_open', 'kwl_disable_cpt_comments', 10, 2 );



/* =============================================================================
   AJAX: RESET COUPON STATS
   ============================================================================= */


/* =============================================================================
   AJAX: RESET COUPON STATS
   ============================================================================= */

/**
 * Reset click count and vote data for a coupon.
 * Called from the coupon stats meta box in admin.
 */
function kwl_reset_coupon_stats_handler(): void {

    check_ajax_referer( 'kwl_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error();
    }

    $post_id = absint( $_POST['post_id'] ?? 0 );

    if ( ! $post_id ) {
        wp_send_json_error();
    }

    delete_post_meta( $post_id, '_kwl_click_count'  );
    delete_post_meta( $post_id, '_kwl_votes_up'     );
    delete_post_meta( $post_id, '_kwl_votes_down'   );

    wp_send_json_success();

}
add_action( 'wp_ajax_kwl_reset_coupon_stats', 'kwl_reset_coupon_stats_handler' );
