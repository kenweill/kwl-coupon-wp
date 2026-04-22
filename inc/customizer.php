<?php
/**
 * KWL Coupon WP — Customizer
 *
 * Registers WordPress Customizer controls for live preview of:
 * - Colors (primary, accent, background, surface, text)
 * - Typography (font choice, base size)
 * - Header (sticky toggle, search visibility)
 * - Footer (columns, copyright text)
 *
 * All color controls sync directly to CSS custom properties
 * via kwl_generate_customizer_css() in functions.php.
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   REGISTER CUSTOMIZER PANELS, SECTIONS & CONTROLS
   ============================================================================= */

/**
 * Add all Customizer settings, sections, and controls.
 *
 * @param WP_Customize_Manager $wp_customize
 */
function kwl_customize_register( WP_Customize_Manager $wp_customize ): void {

    // ------------------------------------------------------------------
    // PANEL: KWL Coupon WP
    // ------------------------------------------------------------------
    $wp_customize->add_panel( 'kwl_panel', [
        'title'       => __( 'KWL Coupon WP', 'kwl-coupon-wp' ),
        'description' => __( 'Customize the look and feel of your coupon site.', 'kwl-coupon-wp' ),
        'priority'    => 30,
    ] );


    // ==================================================================
    // SECTION: Colors
    // ==================================================================
    $wp_customize->add_section( 'kwl_section_colors', [
        'panel'    => 'kwl_panel',
        'title'    => __( 'Colors', 'kwl-coupon-wp' ),
        'priority' => 10,
    ] );

    $color_controls = [
        'kwl_color_primary' => [
            'label'   => __( 'Primary Color',    'kwl-coupon-wp' ),
            'default' => '#2563eb',
            'var'     => '--cwp-primary',
        ],
        'kwl_color_accent' => [
            'label'   => __( 'Accent Color',     'kwl-coupon-wp' ),
            'default' => '#f59e0b',
            'var'     => '--cwp-accent',
        ],
        'kwl_color_bg' => [
            'label'   => __( 'Page Background',  'kwl-coupon-wp' ),
            'default' => '#f8fafc',
            'var'     => '--cwp-bg',
        ],
        'kwl_color_surface' => [
            'label'   => __( 'Card Background',  'kwl-coupon-wp' ),
            'default' => '#ffffff',
            'var'     => '--cwp-surface',
        ],
        'kwl_color_text' => [
            'label'   => __( 'Text Color',       'kwl-coupon-wp' ),
            'default' => '#0f172a',
            'var'     => '--cwp-text',
        ],
        'kwl_color_nav_bg' => [
            'label'   => __( 'Navigation Background', 'kwl-coupon-wp' ),
            'default' => '#2563eb',
            'var'     => '--cwp-nav-bg',
        ],
    ];

    foreach ( $color_controls as $setting_id => $config ) {

        $wp_customize->add_setting( $setting_id, [
            'default'           => $config['default'],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage', // Live preview without reload
        ] );

        $wp_customize->add_control( new WP_Customize_Color_Control(
            $wp_customize,
            $setting_id,
            [
                'label'   => $config['label'],
                'section' => 'kwl_section_colors',
            ]
        ) );

    }


    // ==================================================================
    // SECTION: Typography
    // ==================================================================
    $wp_customize->add_section( 'kwl_section_typography', [
        'panel'    => 'kwl_panel',
        'title'    => __( 'Typography', 'kwl-coupon-wp' ),
        'priority' => 20,
    ] );

    // Font family
    $wp_customize->add_setting( 'kwl_font_family', [
        'default'           => 'figtree',
        'sanitize_callback' => 'sanitize_key',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'kwl_font_family', [
        'label'   => __( 'Font Family', 'kwl-coupon-wp' ),
        'section' => 'kwl_section_typography',
        'type'    => 'select',
        'choices' => [
            'figtree'     => 'Figtree (Default)',
            'inter'       => 'Inter',
            'manrope'     => 'Manrope',
            'nunito'      => 'Nunito',
            'poppins'     => 'Poppins',
            'plus-jakarta' => 'Plus Jakarta Sans',
            'system'      => 'System UI (No Google Fonts)',
        ],
    ] );

    // Base font size
    $wp_customize->add_setting( 'kwl_font_size_base', [
        'default'           => '16',
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'kwl_font_size_base', [
        'label'       => __( 'Base Font Size (px)', 'kwl-coupon-wp' ),
        'section'     => 'kwl_section_typography',
        'type'        => 'range',
        'input_attrs' => [
            'min'  => 14,
            'max'  => 20,
            'step' => 1,
        ],
    ] );

    // Border radius
    $wp_customize->add_setting( 'kwl_border_radius', [
        'default'           => '8px',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'kwl_border_radius', [
        'label'   => __( 'Card Border Radius', 'kwl-coupon-wp' ),
        'section' => 'kwl_section_typography',
        'type'    => 'select',
        'choices' => [
            '0px'   => __( 'Sharp (0px)',     'kwl-coupon-wp' ),
            '4px'   => __( 'Subtle (4px)',    'kwl-coupon-wp' ),
            '8px'   => __( 'Default (8px)',   'kwl-coupon-wp' ),
            '12px'  => __( 'Rounded (12px)',  'kwl-coupon-wp' ),
            '16px'  => __( 'Very Round (16px)','kwl-coupon-wp' ),
        ],
    ] );


    // ==================================================================
    // SECTION: Header
    // ==================================================================
    $wp_customize->add_section( 'kwl_section_header', [
        'panel'    => 'kwl_panel',
        'title'    => __( 'Header', 'kwl-coupon-wp' ),
        'priority' => 30,
    ] );

    $wp_customize->add_setting( 'kwl_header_sticky', [
        'default'           => true,
        'sanitize_callback' => 'kwl_sanitize_checkbox',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'kwl_header_sticky', [
        'label'   => __( 'Sticky Header', 'kwl-coupon-wp' ),
        'section' => 'kwl_section_header',
        'type'    => 'checkbox',
    ] );

    $wp_customize->add_setting( 'kwl_header_search', [
        'default'           => true,
        'sanitize_callback' => 'kwl_sanitize_checkbox',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'kwl_header_search', [
        'label'   => __( 'Show Search in Header', 'kwl-coupon-wp' ),
        'section' => 'kwl_section_header',
        'type'    => 'checkbox',
    ] );

    $wp_customize->add_setting( 'kwl_show_nav', [
        'default'           => true,
        'sanitize_callback' => 'kwl_sanitize_checkbox',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'kwl_show_nav', [
        'label'   => __( 'Show Category Navigation Bar', 'kwl-coupon-wp' ),
        'section' => 'kwl_section_header',
        'type'    => 'checkbox',
    ] );


    // ==================================================================
    // SECTION: Footer
    // ==================================================================
    $wp_customize->add_section( 'kwl_section_footer', [
        'panel'    => 'kwl_panel',
        'title'    => __( 'Footer', 'kwl-coupon-wp' ),
        'priority' => 40,
    ] );

    $wp_customize->add_setting( 'kwl_footer_description', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'kwl_footer_description', [
        'label'   => __( 'Footer Brand Description', 'kwl-coupon-wp' ),
        'section' => 'kwl_section_footer',
        'type'    => 'textarea',
    ] );

    $wp_customize->add_setting( 'kwl_footer_copyright', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'kwl_footer_copyright', [
        'label'       => __( 'Copyright Text', 'kwl-coupon-wp' ),
        'description' => __( 'Supports {year} and {site} placeholders.', 'kwl-coupon-wp' ),
        'section'     => 'kwl_section_footer',
        'type'        => 'text',
    ] );

    $wp_customize->add_setting( 'kwl_footer_bg', [
        'default'           => '#0f172a',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( new WP_Customize_Color_Control(
        $wp_customize,
        'kwl_footer_bg',
        [
            'label'   => __( 'Footer Background Color', 'kwl-coupon-wp' ),
            'section' => 'kwl_section_footer',
        ]
    ) );


    // ==================================================================
    // Move existing WordPress sections into our panel context
    // ==================================================================

    // Site Identity stays in its own section — WordPress handles logo/title/tagline
    // We just bump its priority so it appears before our panel
    $wp_customize->get_section( 'title_tagline' )->priority = 25;

}
add_action( 'customize_register', 'kwl_customize_register' );


/* =============================================================================
   LIVE PREVIEW — postMessage JS
   ============================================================================= */

/**
 * Enqueue the Customizer preview script.
 *
 * Handles postMessage transport for instant live preview
 * of color and text changes without full page reload.
 */
function kwl_customize_preview_js(): void {

    wp_enqueue_script(
        'kwl-customizer-preview',
        KWL_ASSETS . 'js/customizer-preview.js',
        [ 'customize-preview' ],
        KWL_VERSION,
        true
    );

}
add_action( 'customize_preview_init', 'kwl_customize_preview_js' );


/* =============================================================================
   CUSTOMIZER CSS OUTPUT
   ============================================================================= */

/**
 * Generate CSS from Customizer settings and output as inline style.
 *
 * This expands kwl_generate_customizer_css() in functions.php
 * to include font and other non-color settings.
 *
 * Called from functions.php during wp_enqueue_scripts.
 *
 * @return string
 */
function kwl_get_full_customizer_css(): string {

    $vars = [];

    // Color mappings: setting_id => css variable
    $color_map = [
        'kwl_color_primary'  => '--cwp-primary',
        'kwl_color_accent'   => '--cwp-accent',
        'kwl_color_bg'       => '--cwp-bg',
        'kwl_color_surface'  => '--cwp-surface',
        'kwl_color_text'     => '--cwp-text',
        'kwl_color_nav_bg'   => '--cwp-nav-bg',
    ];

    foreach ( $color_map as $mod_key => $css_var ) {
        $value = get_theme_mod( $mod_key, '' );
        if ( ! empty( $value ) ) {
            $vars[] = $css_var . ': ' . sanitize_hex_color( $value ) . ';';
        }
    }

    // Border radius
    $radius = get_theme_mod( 'kwl_border_radius', '' );
    if ( ! empty( $radius ) ) {
        $vars[] = '--cwp-radius: ' . sanitize_text_field( $radius ) . ';';
    }

    // Font size
    $font_size = absint( get_theme_mod( 'kwl_font_size_base', 0 ) );
    if ( $font_size > 0 ) {
        $vars[] = '--cwp-font-size-base: ' . $font_size . 'px;';
    }

    // Font family
    $font_map = [
        'figtree'      => "'Figtree', system-ui, sans-serif",
        'inter'        => "'Inter', system-ui, sans-serif",
        'manrope'      => "'Manrope', system-ui, sans-serif",
        'nunito'       => "'Nunito', system-ui, sans-serif",
        'poppins'      => "'Poppins', system-ui, sans-serif",
        'plus-jakarta' => "'Plus Jakarta Sans', system-ui, sans-serif",
        'system'       => "system-ui, -apple-system, sans-serif",
    ];

    $font_choice = get_theme_mod( 'kwl_font_family', '' );
    if ( ! empty( $font_choice ) && isset( $font_map[ $font_choice ] ) ) {
        $vars[] = '--cwp-font: ' . $font_map[ $font_choice ] . ';';
    }

    // Footer background
    $footer_bg = get_theme_mod( 'kwl_footer_bg', '' );
    if ( ! empty( $footer_bg ) ) {
        $vars[] = '--cwp-footer-bg: ' . sanitize_hex_color( $footer_bg ) . ';';
    }

    if ( empty( $vars ) ) {
        return '';
    }

    $css = ':root { ' . implode( ' ', $vars ) . ' }' . "\n";

    // Sticky header toggle
    if ( ! get_theme_mod( 'kwl_header_sticky', true ) ) {
        $css .= '.cwp-header { position: relative; top: auto; }' . "\n";
    }

    // Footer background if set
    if ( ! empty( $footer_bg ) ) {
        $css .= '.cwp-footer { background: ' . sanitize_hex_color( $footer_bg ) . '; }' . "\n";
    }

    return $css;

}


/* =============================================================================
   FONT LOADER
   ============================================================================= */

/**
 * Load the selected Google Font based on Customizer setting.
 *
 * Runs during wp_enqueue_scripts, replacing or supplementing
 * the default Figtree loaded in functions.php.
 */
function kwl_load_customizer_font(): void {

    if ( ! kwl_get_option( 'load_google_fonts', true ) ) {
        return;
    }

    $font_choice = get_theme_mod( 'kwl_font_family', 'figtree' );

    $font_urls = [
        'figtree'      => 'https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap',
        'inter'        => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
        'manrope'      => 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap',
        'nunito'       => 'https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap',
        'poppins'      => 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap',
        'plus-jakarta' => 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
        'system'       => null,
    ];

    if ( $font_choice !== 'figtree' && isset( $font_urls[ $font_choice ] ) && $font_urls[ $font_choice ] ) {
        // Dequeue the default Figtree font
        wp_dequeue_style( 'kwl-google-fonts' );

        // Enqueue selected font
        wp_enqueue_style(
            'kwl-google-fonts',
            $font_urls[ $font_choice ],
            [],
            null
        );
    } elseif ( $font_choice === 'system' ) {
        wp_dequeue_style( 'kwl-google-fonts' );
    }

}
add_action( 'wp_enqueue_scripts', 'kwl_load_customizer_font', 15 );


/* =============================================================================
   SANITIZE HELPERS
   ============================================================================= */

/**
 * Sanitize a checkbox value for the Customizer.
 *
 * @param  mixed $value
 * @return bool
 */
function kwl_sanitize_checkbox( mixed $value ): bool {
    return (bool) $value;
}
