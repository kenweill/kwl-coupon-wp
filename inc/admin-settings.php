<?php
/**
 * KWL Coupon WP — Admin Settings Page
 *
 * Registers a Theme Settings page under Appearance menu.
 * All options stored in a single `kwl_theme_options` array
 * via kwl_get_option() / kwl_update_option() helpers.
 *
 * Sections:
 * 1. General
 * 2. Appearance & Presets
 * 3. Homepage
 * 4. Coupons
 * 5. SEO
 * 6. Monetization & Ads
 * 7. Social Media
 * 8. Advanced
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   REGISTER MENU PAGE
   ============================================================================= */

/**
 * Add Theme Settings page under Appearance menu.
 */
function kwl_register_settings_page(): void {

    add_theme_page(
        __( 'KWL Coupon WP Settings', 'kwl-coupon-wp' ),
        __( 'Theme Settings',         'kwl-coupon-wp' ),
        'manage_options',
        'kwl-coupon-wp-settings',
        'kwl_render_settings_page'
    );

}
add_action( 'admin_menu', 'kwl_register_settings_page' );


/* =============================================================================
   REGISTER SETTINGS
   ============================================================================= */

/**
 * Register settings, sections, and fields via Settings API.
 */
function kwl_register_settings(): void {

    register_setting(
        'kwl_theme_options_group',
        'kwl_theme_options',
        [
            'sanitize_callback' => 'kwl_sanitize_settings',
            'default'           => kwl_default_options(),
        ]
    );

    // -------------------------------------------------------------------------
    // SECTION: General
    // -------------------------------------------------------------------------
    add_settings_section(
        'kwl_section_general',
        __( 'General', 'kwl-coupon-wp' ),
        '__return_null',
        'kwl-coupon-wp-settings'
    );

    kwl_add_field( 'load_google_fonts',    __( 'Load Google Fonts (Figtree)',   'kwl-coupon-wp' ), 'checkbox',  'kwl_section_general', true );
    kwl_add_field( 'cloak_affiliate_links',__( 'Cloak Affiliate Links (/go/)',  'kwl-coupon-wp' ), 'checkbox',  'kwl_section_general', true );
    kwl_add_field( 'currency_code',        __( 'Currency Code',                 'kwl-coupon-wp' ), 'text',      'kwl_section_general', 'USD',
        __( 'Used in schema markup (e.g. USD, EUR, PHP). Does not convert prices.', 'kwl-coupon-wp' )
    );

    // -------------------------------------------------------------------------
    // SECTION: Appearance & Presets
    // -------------------------------------------------------------------------
    add_settings_section(
        'kwl_section_appearance',
        __( 'Appearance & Presets', 'kwl-coupon-wp' ),
        '__return_null',
        'kwl-coupon-wp-settings'
    );

    kwl_add_field( 'active_preset', __( 'Active Style Preset', 'kwl-coupon-wp' ), 'preset_selector', 'kwl_section_appearance', 'clean' );
    kwl_add_field( 'layout',        __( 'Layout',              'kwl-coupon-wp' ), 'select',          'kwl_section_appearance', 'sidebar-right', '', [
        'sidebar-right' => __( 'Content + Right Sidebar', 'kwl-coupon-wp' ),
        'full-width'    => __( 'Full Width (No Sidebar)',  'kwl-coupon-wp' ),
    ] );
    kwl_add_field( 'custom_css', __( 'Custom CSS', 'kwl-coupon-wp' ), 'textarea', 'kwl_section_appearance', '',
        __( 'Additional CSS applied after the theme styles. Use CSS custom properties to override design tokens.', 'kwl-coupon-wp' )
    );

    // -------------------------------------------------------------------------
    // SECTION: Homepage
    // -------------------------------------------------------------------------
    add_settings_section(
        'kwl_section_homepage',
        __( 'Homepage', 'kwl-coupon-wp' ),
        '__return_null',
        'kwl-coupon-wp-settings'
    );

    kwl_add_field( 'show_hero',              __( 'Show Hero Banner',            'kwl-coupon-wp' ), 'checkbox', 'kwl_section_homepage', true );
    kwl_add_field( 'hero_title',             __( 'Hero Title',                  'kwl-coupon-wp' ), 'text',     'kwl_section_homepage', __( 'Find the Best Coupon Codes', 'kwl-coupon-wp' ) );
    kwl_add_field( 'hero_subtitle',          __( 'Hero Subtitle',               'kwl-coupon-wp' ), 'text',     'kwl_section_homepage', __( 'Verified promo codes. Updated daily.', 'kwl-coupon-wp' ) );
    kwl_add_field( 'show_featured_stores',   __( 'Show Featured Stores',        'kwl-coupon-wp' ), 'checkbox', 'kwl_section_homepage', true );
    kwl_add_field( 'featured_stores_count',  __( 'Featured Stores Count',       'kwl-coupon-wp' ), 'number',   'kwl_section_homepage', 12 );
    kwl_add_field( 'show_categories_home',   __( 'Show Categories Section',     'kwl-coupon-wp' ), 'checkbox', 'kwl_section_homepage', true );
    kwl_add_field( 'home_categories_count',  __( 'Categories to Show',          'kwl-coupon-wp' ), 'number',   'kwl_section_homepage', 8 );
    kwl_add_field( 'home_coupons_count',     __( 'Latest Coupons to Show',      'kwl-coupon-wp' ), 'number',   'kwl_section_homepage', 10 );
    kwl_add_field( 'homepage_meta_description', __( 'Homepage Meta Description','kwl-coupon-wp' ), 'textarea', 'kwl_section_homepage', '',
        __( 'Leave blank to auto-generate.', 'kwl-coupon-wp' )
    );

    // -------------------------------------------------------------------------
    // SECTION: Coupons
    // -------------------------------------------------------------------------
    add_settings_section(
        'kwl_section_coupons',
        __( 'Coupons', 'kwl-coupon-wp' ),
        '__return_null',
        'kwl-coupon-wp-settings'
    );

    kwl_add_field( 'coupons_per_page',      __( 'Coupons Per Page',          'kwl-coupon-wp' ), 'number',   'kwl_section_coupons', 20 );
    kwl_add_field( 'hide_expired_coupons',  __( 'Hide Expired Coupons',      'kwl-coupon-wp' ), 'checkbox', 'kwl_section_coupons', false,
        __( 'When enabled, expired coupons are not shown on store or archive pages.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'expiring_soon_days',    __( 'Expiring Soon Warning (days)','kwl-coupon-wp' ), 'number',  'kwl_section_coupons', 3,
        __( 'Show "Ending Soon" badge when coupon expires within this many days.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'enable_voting',         __( 'Enable Coupon Voting',      'kwl-coupon-wp' ), 'checkbox', 'kwl_section_coupons', true );
    kwl_add_field( 'enable_search_autocomplete', __( 'Enable Search Autocomplete', 'kwl-coupon-wp' ), 'checkbox', 'kwl_section_coupons', true );
    kwl_add_field( 'reveal_behavior',       __( 'Code Reveal Behavior',      'kwl-coupon-wp' ), 'select',   'kwl_section_coupons', 'blur', '', [
        'blur'   => __( 'Blur (show blurred code, click to reveal)', 'kwl-coupon-wp' ),
        'hidden' => __( 'Hidden (show "Show Code" button only)',      'kwl-coupon-wp' ),
    ] );

    // -------------------------------------------------------------------------
    // SECTION: SEO
    // -------------------------------------------------------------------------
    add_settings_section(
        'kwl_section_seo',
        __( 'SEO', 'kwl-coupon-wp' ),
        '__return_null',
        'kwl-coupon-wp-settings'
    );

    kwl_add_field( 'use_custom_sitemap',    __( 'Use Custom Sitemap',        'kwl-coupon-wp' ), 'checkbox', 'kwl_section_seo', true,
        __( 'Disables WordPress\'s built-in sitemap. Recommended: ON.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'ping_search_engines',   __( 'Ping Search Engines',       'kwl-coupon-wp' ), 'checkbox', 'kwl_section_seo', true,
        __( 'Notify Google and Bing when a new coupon or store is published.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'og_default_image',      __( 'Default OG Image URL',      'kwl-coupon-wp' ), 'url',      'kwl_section_seo', '',
        __( 'Used as the Open Graph image when no specific image is available.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'separator',             __( 'Title Separator',           'kwl-coupon-wp' ), 'text',     'kwl_section_seo', '—',
        __( 'Character used between page title and site name. E.g. — or |', 'kwl-coupon-wp' )
    );

    // -------------------------------------------------------------------------
    // SECTION: Ads & Monetization
    // -------------------------------------------------------------------------
    add_settings_section(
        'kwl_section_ads',
        __( 'Ads & Monetization', 'kwl-coupon-wp' ),
        '__return_null',
        'kwl-coupon-wp-settings'
    );

    kwl_add_field( 'ads_enabled',         __( 'Enable Ad Slots',              'kwl-coupon-wp' ), 'checkbox', 'kwl_section_ads', false );
    kwl_add_field( 'ad_header_code',      __( 'Header Ad Code (728×90)',      'kwl-coupon-wp' ), 'textarea', 'kwl_section_ads', '',
        __( 'Paste your ad code (AdSense, etc.) for the leaderboard slot below the header.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'ad_sidebar_code',     __( 'Sidebar Ad Code (300×250)',    'kwl-coupon-wp' ), 'textarea', 'kwl_section_ads', '',
        __( 'Paste your ad code for the sidebar rectangle slot.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'ad_infeed_code',      __( 'In-Feed Ad Code',             'kwl-coupon-wp' ), 'textarea', 'kwl_section_ads', '',
        __( 'Inserted within the coupon listings. Appears every N coupons.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'ad_infeed_interval',  __( 'In-Feed Ad Interval',         'kwl-coupon-wp' ), 'number',   'kwl_section_ads', 5,
        __( 'Insert the in-feed ad after every N coupons.', 'kwl-coupon-wp' )
    );

    // -------------------------------------------------------------------------
    // SECTION: Social Media
    // -------------------------------------------------------------------------
    add_settings_section(
        'kwl_section_social',
        __( 'Social Media', 'kwl-coupon-wp' ),
        '__return_null',
        'kwl-coupon-wp-settings'
    );

    kwl_add_field( 'social_twitter',   __( 'Twitter / X URL',   'kwl-coupon-wp' ), 'url', 'kwl_section_social', '' );
    kwl_add_field( 'social_facebook',  __( 'Facebook URL',       'kwl-coupon-wp' ), 'url', 'kwl_section_social', '' );
    kwl_add_field( 'social_instagram', __( 'Instagram URL',      'kwl-coupon-wp' ), 'url', 'kwl_section_social', '' );
    kwl_add_field( 'social_pinterest', __( 'Pinterest URL',      'kwl-coupon-wp' ), 'url', 'kwl_section_social', '' );
    kwl_add_field( 'social_youtube',   __( 'YouTube URL',        'kwl-coupon-wp' ), 'url', 'kwl_section_social', '' );
    kwl_add_field( 'twitter_handle',   __( 'Twitter/X Handle',   'kwl-coupon-wp' ), 'text','kwl_section_social', '',
        __( 'Without @. Used in Twitter Card meta tags. E.g. kenweill', 'kwl-coupon-wp' )
    );

    // -------------------------------------------------------------------------
    // SECTION: Advanced
    // -------------------------------------------------------------------------
    add_settings_section(
        'kwl_section_advanced',
        __( 'Advanced', 'kwl-coupon-wp' ),
        '__return_null',
        'kwl-coupon-wp-settings'
    );

    kwl_add_field( 'footer_text',      __( 'Footer Copyright Text', 'kwl-coupon-wp' ), 'text',     'kwl_section_advanced', '',
        __( 'Leave blank to auto-generate. Supports {year} placeholder.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'header_scripts',   __( 'Header Scripts',        'kwl-coupon-wp' ), 'textarea', 'kwl_section_advanced', '',
        __( 'Code added just before </head>. E.g. analytics scripts.', 'kwl-coupon-wp' )
    );
    kwl_add_field( 'footer_scripts',   __( 'Footer Scripts',        'kwl-coupon-wp' ), 'textarea', 'kwl_section_advanced', '',
        __( 'Code added just before </body>. E.g. chat widgets.', 'kwl-coupon-wp' )
    );

}
add_action( 'admin_init', 'kwl_register_settings' );


/* =============================================================================
   FIELD REGISTRATION HELPER
   ============================================================================= */

/**
 * Register a settings field with a unified callback.
 *
 * @param string $key         Option key.
 * @param string $label       Field label.
 * @param string $type        Field type: text|url|number|checkbox|select|textarea|preset_selector.
 * @param string $section     Section ID.
 * @param mixed  $default     Default value.
 * @param string $description Optional description.
 * @param array  $choices     Options for select fields.
 */
function kwl_add_field(
    string $key,
    string $label,
    string $type,
    string $section,
    mixed $default = '',
    string $description = '',
    array $choices = []
): void {

    add_settings_field(
        'kwl_' . $key,
        $label,
        'kwl_render_settings_field',
        'kwl-coupon-wp-settings',
        $section,
        [
            'key'         => $key,
            'type'        => $type,
            'default'     => $default,
            'description' => $description,
            'choices'     => $choices,
            'label_for'   => 'kwl_option_' . $key,
        ]
    );

}


/* =============================================================================
   FIELD RENDERER
   ============================================================================= */

/**
 * Render a settings field based on its type.
 *
 * @param array $args  Field arguments from kwl_add_field().
 */
function kwl_render_settings_field( array $args ): void {

    $key         = $args['key'];
    $type        = $args['type'];
    $default     = $args['default'];
    $description = $args['description'] ?? '';
    $choices     = $args['choices'] ?? [];
    $value       = kwl_get_option( $key, $default );
    $field_id    = 'kwl_option_' . $key;
    $field_name  = 'kwl_theme_options[' . $key . ']';

    switch ( $type ) {

        case 'text':
        case 'url':
            printf(
                '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">',
                esc_attr( $type ),
                esc_attr( $field_id ),
                esc_attr( $field_name ),
                esc_attr( $value )
            );
            break;

        case 'number':
            printf(
                '<input type="number" id="%s" name="%s" value="%s" class="small-text" min="1">',
                esc_attr( $field_id ),
                esc_attr( $field_name ),
                esc_attr( $value )
            );
            break;

        case 'checkbox':
            printf(
                '<input type="checkbox" id="%s" name="%s" value="1" %s>',
                esc_attr( $field_id ),
                esc_attr( $field_name ),
                checked( $value, true, false )
            );
            break;

        case 'select':
            echo '<select id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '">';
            foreach ( $choices as $choice_value => $choice_label ) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $choice_value ),
                    selected( $value, $choice_value, false ),
                    esc_html( $choice_label )
                );
            }
            echo '</select>';
            break;

        case 'textarea':
            printf(
                '<textarea id="%s" name="%s" class="large-text" rows="4">%s</textarea>',
                esc_attr( $field_id ),
                esc_attr( $field_name ),
                esc_textarea( $value )
            );
            break;

        case 'preset_selector':
            kwl_render_preset_selector( $field_name, $value );
            break;

    }

    if ( ! empty( $description ) ) {
        echo '<p class="description">' . esc_html( $description ) . '</p>';
    }

}


/* =============================================================================
   PRESET SELECTOR UI
   ============================================================================= */

/**
 * Render the visual preset selector with preview swatches.
 *
 * @param string $field_name  Input name attribute.
 * @param string $current     Currently active preset slug.
 */
function kwl_render_preset_selector( string $field_name, string $current ): void {

    $presets = kwl_get_available_presets();

    echo '<div class="kwl-preset-grid">';

    foreach ( $presets as $slug => $preset ) {

        $is_active   = $slug === $current;
        $card_class  = 'kwl-preset-card' . ( $is_active ? ' kwl-preset-card--active' : '' );
        $colors      = $preset['colors'] ?? [];

        echo '<label class="' . esc_attr( $card_class ) . '">';

        echo '<input type="radio" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $slug ) . '" '
            . checked( $is_active, true, false ) . ' style="display:none;">';

        // Color swatches
        echo '<div class="kwl-preset-swatches">';
        foreach ( $colors as $color ) {
            echo '<span class="kwl-preset-swatch" style="background:' . esc_attr( $color ) . '"></span>';
        }
        echo '</div>';

        echo '<div class="kwl-preset-name">' . esc_html( $preset['name'] ) . '</div>';

        if ( $is_active ) {
            echo '<div class="kwl-preset-active-badge">' . esc_html__( 'Active', 'kwl-coupon-wp' ) . '</div>';
        }

        echo '</label>';

    }

    echo '</div>';

    // Inline styles for the preset selector (admin only, minimal)
    echo '<style>
        .kwl-preset-grid { display:flex; gap:12px; flex-wrap:wrap; margin-top:6px; }
        .kwl-preset-card { border:2px solid #ddd; border-radius:8px; padding:12px; cursor:pointer; text-align:center; min-width:120px; transition:border-color .15s; }
        .kwl-preset-card:hover { border-color:#2563eb; }
        .kwl-preset-card--active { border-color:#2563eb; background:#eff6ff; }
        .kwl-preset-swatches { display:flex; gap:4px; justify-content:center; margin-bottom:8px; }
        .kwl-preset-swatch { width:20px; height:20px; border-radius:50%; border:1px solid rgba(0,0,0,.1); }
        .kwl-preset-name { font-weight:600; font-size:13px; }
        .kwl-preset-active-badge { font-size:11px; color:#2563eb; margin-top:4px; font-weight:600; }
    </style>';

}


/* =============================================================================
   SETTINGS PAGE RENDER
   ============================================================================= */

/**
 * Render the full settings page HTML.
 */
function kwl_render_settings_page(): void {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Handle reset to defaults
    if ( isset( $_POST['kwl_reset_defaults'] ) && check_admin_referer( 'kwl_reset_defaults_nonce' ) ) {
        update_option( 'kwl_theme_options', kwl_default_options() );
        add_settings_error( 'kwl_theme_options', 'kwl_reset', __( 'Settings reset to defaults.', 'kwl-coupon-wp' ), 'success' );
    }

    ?>
    <div class="wrap kwl-settings-wrap">

        <h1>
            <?php esc_html_e( 'KWL Coupon WP — Theme Settings', 'kwl-coupon-wp' ); ?>
            <span style="font-size:13px; font-weight:400; color:#666; margin-left:8px;">v<?php echo esc_html( KWL_VERSION ); ?></span>
        </h1>

        <?php settings_errors( 'kwl_theme_options' ); ?>

        <div class="kwl-settings-layout">

            <!-- Main Settings Form -->
            <div class="kwl-settings-main">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'kwl_theme_options_group' );
                    do_settings_sections( 'kwl-coupon-wp-settings' );
                    submit_button( __( 'Save Settings', 'kwl-coupon-wp' ) );
                    ?>
                </form>

                <!-- Reset to Defaults -->
                <form method="post" style="margin-top:16px;">
                    <?php wp_nonce_field( 'kwl_reset_defaults_nonce' ); ?>
                    <input type="hidden" name="kwl_reset_defaults" value="1">
                    <?php submit_button(
                        __( 'Reset to Defaults', 'kwl-coupon-wp' ),
                        'secondary',
                        'kwl_reset_btn',
                        false,
                        [ 'onclick' => 'return confirm("' . esc_js( __( 'Reset all settings to defaults? This cannot be undone.', 'kwl-coupon-wp' ) ) . '")' ]
                    ); ?>
                </form>
            </div>

            <!-- Sidebar Info Panel -->
            <div class="kwl-settings-sidebar">

                <div class="kwl-info-box">
                    <h3><?php esc_html_e( 'Quick Links', 'kwl-coupon-wp' ); ?></h3>
                    <ul>
                        <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=kwl_store' ) ); ?>"><?php esc_html_e( 'Manage Stores', 'kwl-coupon-wp' ); ?></a></li>
                        <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=kwl_coupon' ) ); ?>"><?php esc_html_e( 'Manage Coupons', 'kwl-coupon-wp' ); ?></a></li>
                        <li><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=kwl_coupon_cat&post_type=kwl_coupon' ) ); ?>"><?php esc_html_e( 'Manage Categories', 'kwl-coupon-wp' ); ?></a></li>
                        <li><a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customizer (Colors & Logo)', 'kwl-coupon-wp' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank"><?php esc_html_e( 'View Sitemap', 'kwl-coupon-wp' ); ?></a></li>
                        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=kwl-csv-import' ) ); ?>"><?php esc_html_e( 'CSV Import', 'kwl-coupon-wp' ); ?></a></li>
                    </ul>
                </div>

                <div class="kwl-info-box">
                    <h3><?php esc_html_e( 'Site Stats', 'kwl-coupon-wp' ); ?></h3>
                    <?php
                    $store_count  = wp_count_posts( 'kwl_store' )->publish;
                    $coupon_count = wp_count_posts( 'kwl_coupon' )->publish;
                    $cat_count    = wp_count_terms( [ 'taxonomy' => 'kwl_coupon_cat', 'hide_empty' => false ] );
                    ?>
                    <ul>
                        <li><?php printf( esc_html__( '%d Published Stores', 'kwl-coupon-wp' ), $store_count ); ?></li>
                        <li><?php printf( esc_html__( '%d Published Coupons', 'kwl-coupon-wp' ), $coupon_count ); ?></li>
                        <li><?php printf( esc_html__( '%d Categories', 'kwl-coupon-wp' ), $cat_count ); ?></li>
                    </ul>
                </div>

                <div class="kwl-info-box">
                    <h3><?php esc_html_e( 'About', 'kwl-coupon-wp' ); ?></h3>
                    <p><?php esc_html_e( 'KWL Coupon WP is a free, open source WordPress theme for coupon websites.', 'kwl-coupon-wp' ); ?></p>
                    <p>
                        <a href="https://github.com/kenweill/kwl-coupon-wp" target="_blank" rel="noopener">
                            <?php esc_html_e( 'GitHub Repository', 'kwl-coupon-wp' ); ?>
                        </a> &bull;
                        <a href="https://github.com/kenweill/kwl-coupon-wp/issues" target="_blank" rel="noopener">
                            <?php esc_html_e( 'Report an Issue', 'kwl-coupon-wp' ); ?>
                        </a>
                    </p>
                    <p style="font-size:12px; color:#666;">
                        <?php printf(
                            esc_html__( 'By %s. GPL v2 License.', 'kwl-coupon-wp' ),
                            '<a href="https://github.com/kenweill" target="_blank" rel="noopener">Ken Weill</a>'
                        ); ?>
                    </p>
                </div>

            </div>

        </div>

    </div>

    <style>
        .kwl-settings-layout { display:grid; grid-template-columns:1fr 260px; gap:24px; margin-top:20px; align-items:start; }
        .kwl-settings-main .form-table th { width:220px; }
        .kwl-info-box { background:#fff; border:1px solid #ddd; border-radius:6px; padding:16px; margin-bottom:16px; }
        .kwl-info-box h3 { margin:0 0 10px; font-size:13px; text-transform:uppercase; letter-spacing:.05em; color:#666; }
        .kwl-info-box ul { margin:0; padding:0 0 0 16px; }
        .kwl-info-box ul li { margin-bottom:6px; font-size:13px; }
        .kwl-info-box p { font-size:13px; margin:0 0 8px; }
        @media (max-width:960px) { .kwl-settings-layout { grid-template-columns:1fr; } }
    </style>
    <?php

}


/* =============================================================================
   SANITIZATION
   ============================================================================= */

/**
 * Sanitize all settings on save.
 *
 * @param  array $input  Raw input from form.
 * @return array         Sanitized options.
 */
function kwl_sanitize_settings( array $input ): array {

    $defaults  = kwl_default_options();
    $sanitized = [];

    // Checkboxes
    $checkboxes = [
        'load_google_fonts', 'cloak_affiliate_links', 'show_hero',
        'show_featured_stores', 'show_categories_home', 'hide_expired_coupons',
        'enable_voting', 'enable_search_autocomplete', 'use_custom_sitemap',
        'ping_search_engines', 'ads_enabled',
    ];

    foreach ( $checkboxes as $key ) {
        $sanitized[ $key ] = ! empty( $input[ $key ] );
    }

    // Text fields
    $text_fields = [ 'currency_code', 'hero_title', 'hero_subtitle', 'separator', 'footer_text', 'twitter_handle' ];
    foreach ( $text_fields as $key ) {
        $sanitized[ $key ] = sanitize_text_field( $input[ $key ] ?? $defaults[ $key ] );
    }

    // URL fields
    $url_fields = [ 'og_default_image', 'social_twitter', 'social_facebook', 'social_instagram', 'social_pinterest', 'social_youtube' ];
    foreach ( $url_fields as $key ) {
        $sanitized[ $key ] = esc_url_raw( $input[ $key ] ?? '' );
    }

    // Number fields
    $number_fields = [ 'featured_stores_count', 'home_categories_count', 'home_coupons_count', 'coupons_per_page', 'expiring_soon_days', 'ad_infeed_interval' ];
    foreach ( $number_fields as $key ) {
        $sanitized[ $key ] = max( 1, absint( $input[ $key ] ?? $defaults[ $key ] ) );
    }

    // Textareas (for descriptions and custom CSS — no script tags)
    $safe_textareas = [ 'homepage_meta_description', 'custom_css' ];
    foreach ( $safe_textareas as $key ) {
        $sanitized[ $key ] = sanitize_textarea_field( $input[ $key ] ?? '' );
    }

    // Script textareas — allow HTML/JS but strip PHP
    $script_textareas = [ 'ad_header_code', 'ad_sidebar_code', 'ad_infeed_code', 'header_scripts', 'footer_scripts' ];
    foreach ( $script_textareas as $key ) {
        // wp_kses with script tag allowed for ad code
        $sanitized[ $key ] = wp_kses( $input[ $key ] ?? '', [
            'script' => [ 'src' => true, 'type' => true, 'async' => true, 'defer' => true, 'data-*' => true ],
            'ins'    => [ 'class' => true, 'data-*' => true, 'style' => true ],
        ] );
    }

    // Select fields with whitelists
    $preset_slugs = array_keys( kwl_get_available_presets() );
    $sanitized['active_preset'] = in_array( $input['active_preset'] ?? 'clean', $preset_slugs, true )
        ? $input['active_preset']
        : 'clean';

    $sanitized['layout'] = in_array( $input['layout'] ?? 'sidebar-right', [ 'sidebar-right', 'full-width' ], true )
        ? $input['layout']
        : 'sidebar-right';

    $sanitized['reveal_behavior'] = in_array( $input['reveal_behavior'] ?? 'blur', [ 'blur', 'hidden' ], true )
        ? $input['reveal_behavior']
        : 'blur';

    return $sanitized;

}


/* =============================================================================
   DEFAULT OPTIONS
   ============================================================================= */

/**
 * Get all default option values.
 *
 * @return array
 */
function kwl_default_options(): array {

    return [
        // General
        'load_google_fonts'          => true,
        'cloak_affiliate_links'      => true,
        'currency_code'              => 'USD',

        // Appearance
        'active_preset'              => 'clean',
        'layout'                     => 'sidebar-right',
        'custom_css'                 => '',

        // Homepage
        'show_hero'                  => true,
        'hero_title'                 => __( 'Find the Best Coupon Codes', 'kwl-coupon-wp' ),
        'hero_subtitle'              => __( 'Verified promo codes. Updated daily.', 'kwl-coupon-wp' ),
        'show_featured_stores'       => true,
        'featured_stores_count'      => 12,
        'show_categories_home'       => true,
        'home_categories_count'      => 8,
        'home_coupons_count'         => 10,
        'homepage_meta_description'  => '',

        // Coupons
        'coupons_per_page'           => 20,
        'hide_expired_coupons'       => false,
        'expiring_soon_days'         => 3,
        'enable_voting'              => true,
        'enable_search_autocomplete' => true,
        'reveal_behavior'            => 'blur',

        // SEO
        'use_custom_sitemap'         => true,
        'ping_search_engines'        => true,
        'og_default_image'           => '',
        'separator'                  => '—',

        // Ads
        'ads_enabled'                => false,
        'ad_header_code'             => '',
        'ad_sidebar_code'            => '',
        'ad_infeed_code'             => '',
        'ad_infeed_interval'         => 5,

        // Social
        'social_twitter'             => '',
        'social_facebook'            => '',
        'social_instagram'           => '',
        'social_pinterest'           => '',
        'social_youtube'             => '',
        'twitter_handle'             => '',

        // Advanced
        'footer_text'                => '',
        'header_scripts'             => '',
        'footer_scripts'             => '',
    ];

}


/* =============================================================================
   HEADER / FOOTER SCRIPT OUTPUT
   ============================================================================= */

/**
 * Output header scripts (analytics, etc.) before </head>.
 */
function kwl_output_header_scripts(): void {
    $scripts = kwl_get_option( 'header_scripts', '' );
    if ( ! empty( $scripts ) && ! is_admin() ) {
        echo $scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
add_action( 'wp_head', 'kwl_output_header_scripts', 99 );


/**
 * Output footer scripts before </body>.
 */
function kwl_output_footer_scripts(): void {
    $scripts = kwl_get_option( 'footer_scripts', '' );
    if ( ! empty( $scripts ) && ! is_admin() ) {
        echo $scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
add_action( 'wp_footer', 'kwl_output_footer_scripts', 99 );
