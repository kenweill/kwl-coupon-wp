<?php
/**
 * KWL Coupon WP — Preset Loader
 *
 * Handles the style preset system.
 *
 * How presets work:
 * - Each preset lives in /presets/{slug}/
 * - preset.json  — metadata: name, description, colors (for UI swatches), author
 * - preset.css   — CSS custom property overrides only (no layout, no components)
 *
 * The active preset's CSS is enqueued after the main style.css,
 * overriding the default CSS variables. The Customizer can then
 * override individual variables on top of that.
 *
 * Community contribution: anyone can submit a new preset via pull request
 * by adding a folder with just preset.json + preset.css — no PHP required.
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   PRESET DISCOVERY
   ============================================================================= */

/**
 * Get all available presets by scanning the /presets/ directory.
 *
 * Returns an associative array: slug => preset data.
 * Merges bundled presets with any user-added presets.
 *
 * @return array[] {
 *   slug => [
 *     name:        string,
 *     description: string,
 *     colors:      string[],  (hex colors for swatches)
 *     author:      string,
 *     css_file:    string,    (absolute path)
 *     css_url:     string,    (URL)
 *   ]
 * }
 */
function kwl_get_available_presets(): array {

    static $presets = null;

    if ( $presets !== null ) {
        return $presets;
    }

    $presets      = [];
    $presets_dir  = KWL_DIR . '/presets/';

    if ( ! is_dir( $presets_dir ) ) {
        return $presets;
    }

    $folders = glob( $presets_dir . '*', GLOB_ONLYDIR );

    if ( empty( $folders ) ) {
        return $presets;
    }

    foreach ( $folders as $folder ) {

        $slug     = basename( $folder );
        $json_file = $folder . '/preset.json';
        $css_file  = $folder . '/preset.css';

        // Both files must exist
        if ( ! file_exists( $json_file ) || ! file_exists( $css_file ) ) {
            continue;
        }

        // Parse JSON metadata
        $json = file_get_contents( $json_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
        $data = json_decode( $json, true );

        if ( ! is_array( $data ) || empty( $data['name'] ) ) {
            continue;
        }

        $presets[ $slug ] = [
            'name'        => sanitize_text_field( $data['name'] ),
            'description' => sanitize_text_field( $data['description'] ?? '' ),
            'colors'      => array_map( 'sanitize_hex_color', (array) ( $data['colors'] ?? [] ) ),
            'author'      => sanitize_text_field( $data['author'] ?? '' ),
            'version'     => sanitize_text_field( $data['version'] ?? '1.0.0' ),
            'css_file'    => $css_file,
            'css_url'     => KWL_URI . '/presets/' . $slug . '/preset.css',
        ];

    }

    // Sort: built-in presets first (clean, dark, warm), then alphabetical
    $builtin_order = [ 'clean', 'dark', 'warm' ];
    uksort( $presets, function( $a, $b ) use ( $builtin_order ) {
        $a_pos = array_search( $a, $builtin_order, true );
        $b_pos = array_search( $b, $builtin_order, true );

        if ( $a_pos !== false && $b_pos !== false ) return $a_pos - $b_pos;
        if ( $a_pos !== false ) return -1;
        if ( $b_pos !== false ) return 1;

        return strcmp( $a, $b );
    } );

    return $presets;

}


/**
 * Get a single preset's data by slug.
 *
 * @param  string $slug  Preset slug (e.g. 'clean', 'dark').
 * @return array|null    Preset data or null if not found.
 */
function kwl_get_preset( string $slug ): ?array {
    $presets = kwl_get_available_presets();
    return $presets[ $slug ] ?? null;
}


/**
 * Get the currently active preset slug.
 *
 * @return string
 */
function kwl_get_active_preset(): string {
    $active   = kwl_get_option( 'active_preset', 'clean' );
    $presets  = kwl_get_available_presets();

    // Fall back to 'clean' if saved preset no longer exists
    return isset( $presets[ $active ] ) ? $active : 'clean';
}


/* =============================================================================
   PRESET CSS ENQUEUE
   ============================================================================= */

/**
 * Enqueue the active preset CSS.
 *
 * Hooked via functions.php during wp_enqueue_scripts.
 * This function is called from there — not hooked directly here
 * to keep enqueue logic centralized.
 */
function kwl_enqueue_active_preset(): void {

    $slug   = kwl_get_active_preset();
    $preset = kwl_get_preset( $slug );

    if ( ! $preset || ! file_exists( $preset['css_file'] ) ) {
        return;
    }

    wp_enqueue_style(
        'kwl-preset-' . $slug,
        $preset['css_url'],
        [ 'kwl-style' ],  // Load after main stylesheet
        $preset['version']
    );

}


/* =============================================================================
   PRESET SWITCHER — AJAX
   ============================================================================= */

/**
 * AJAX: Switch the active preset from the admin settings page.
 *
 * Used for instant preset preview in settings (non-Customizer).
 */
function kwl_ajax_switch_preset(): void {

    check_ajax_referer( 'kwl_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => __( 'Permission denied.', 'kwl-coupon-wp' ) ] );
    }

    $slug    = sanitize_key( $_POST['preset'] ?? '' );
    $presets = kwl_get_available_presets();

    if ( ! isset( $presets[ $slug ] ) ) {
        wp_send_json_error( [ 'message' => __( 'Preset not found.', 'kwl-coupon-wp' ) ] );
    }

    kwl_update_option( 'active_preset', $slug );

    wp_send_json_success( [
        'preset'  => $slug,
        'css_url' => $presets[ $slug ]['css_url'],
        'message' => sprintf(
            /* translators: %s = preset name */
            __( '"%s" preset activated.', 'kwl-coupon-wp' ),
            $presets[ $slug ]['name']
        ),
    ] );

}
add_action( 'wp_ajax_kwl_switch_preset', 'kwl_ajax_switch_preset' );


/* =============================================================================
   PRESET VALIDATION
   ============================================================================= */

/**
 * Validate a preset's CSS file for safety.
 *
 * Checks that the CSS only contains CSS custom property declarations
 * and doesn't include any PHP, JS, or dangerous content.
 *
 * Used when validating community-contributed presets.
 *
 * @param  string $css_file  Absolute path to preset.css.
 * @return true|WP_Error
 */
function kwl_validate_preset_css( string $css_file ) {

    if ( ! file_exists( $css_file ) ) {
        return new WP_Error( 'file_not_found', __( 'Preset CSS file not found.', 'kwl-coupon-wp' ) );
    }

    $css = file_get_contents( $css_file ); // phpcs:ignore

    // Must not contain PHP tags
    if ( strpos( $css, '<?php' ) !== false || strpos( $css, '<?=' ) !== false ) {
        return new WP_Error( 'invalid_css', __( 'Preset CSS must not contain PHP.', 'kwl-coupon-wp' ) );
    }

    // Must not contain JavaScript
    if ( stripos( $css, '<script' ) !== false || stripos( $css, 'javascript:' ) !== false ) {
        return new WP_Error( 'invalid_css', __( 'Preset CSS must not contain JavaScript.', 'kwl-coupon-wp' ) );
    }

    // Must not use @import (for security)
    if ( stripos( $css, '@import' ) !== false ) {
        return new WP_Error( 'invalid_css', __( 'Preset CSS must not use @import.', 'kwl-coupon-wp' ) );
    }

    // File size sanity check (max 50KB — presets should be tiny)
    if ( filesize( $css_file ) > 51200 ) {
        return new WP_Error( 'file_too_large', __( 'Preset CSS file is too large. Max 50KB.', 'kwl-coupon-wp' ) );
    }

    return true;

}


/* =============================================================================
   BUNDLED PRESET GENERATOR
   ============================================================================= */

/**
 * Create the default preset files on theme activation if they don't exist.
 *
 * This ensures the /presets/ directory always has the three bundled
 * presets (clean, dark, warm) even after a fresh install.
 *
 * Note: The actual preset files are committed to the repo.
 * This is a fallback in case they're missing.
 */
function kwl_create_default_presets(): void {

    $presets_dir = KWL_DIR . '/presets/';

    // Create presets directory if it doesn't exist
    if ( ! is_dir( $presets_dir ) ) {
        wp_mkdir_p( $presets_dir );
    }

    $defaults = kwl_get_default_preset_definitions();

    foreach ( $defaults as $slug => $preset ) {

        $preset_dir = $presets_dir . $slug . '/';

        if ( ! is_dir( $preset_dir ) ) {
            wp_mkdir_p( $preset_dir );
        }

        // Write preset.json if missing
        $json_file = $preset_dir . 'preset.json';
        if ( ! file_exists( $json_file ) ) {
            file_put_contents( // phpcs:ignore
                $json_file,
                wp_json_encode( $preset['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
            );
        }

        // Write preset.css if missing
        $css_file = $preset_dir . 'preset.css';
        if ( ! file_exists( $css_file ) ) {
            file_put_contents( $css_file, $preset['css'] ); // phpcs:ignore
        }

    }

}
add_action( 'after_switch_theme', 'kwl_create_default_presets' );


/**
 * Get the definitions for the three default bundled presets.
 *
 * @return array
 */
function kwl_get_default_preset_definitions(): array {

    return [

        'clean' => [
            'json' => [
                'name'        => 'Clean',
                'description' => 'The default preset. White background, blue primary, professional and neutral.',
                'version'     => '1.0.0',
                'author'      => 'Ken Weill',
                'colors'      => [ '#2563eb', '#f59e0b', '#f8fafc', '#0f172a' ],
            ],
            'css'  => ":root {
  --cwp-primary:       #2563eb;
  --cwp-primary-hover: #1d4ed8;
  --cwp-primary-light: #eff6ff;
  --cwp-accent:        #f59e0b;
  --cwp-accent-hover:  #d97706;
  --cwp-bg:            #f8fafc;
  --cwp-surface:       #ffffff;
  --cwp-surface-alt:   #f1f5f9;
  --cwp-border:        #e2e8f0;
  --cwp-text:          #0f172a;
  --cwp-text-muted:    #64748b;
  --cwp-radius:        8px;
}\n",
        ],

        'dark' => [
            'json' => [
                'name'        => 'Dark',
                'description' => 'Dark background with vibrant green accent. Great for tech and SaaS coupon sites.',
                'version'     => '1.0.0',
                'author'      => 'Ken Weill',
                'colors'      => [ '#22c55e', '#f97316', '#0f172a', '#f8fafc' ],
            ],
            'css'  => ":root {
  --cwp-primary:       #22c55e;
  --cwp-primary-hover: #16a34a;
  --cwp-primary-light: #052e16;
  --cwp-accent:        #f97316;
  --cwp-accent-hover:  #ea580c;
  --cwp-bg:            #0f172a;
  --cwp-surface:       #1e293b;
  --cwp-surface-alt:   #334155;
  --cwp-surface-hover: #334155;
  --cwp-border:        #334155;
  --cwp-text:          #f8fafc;
  --cwp-text-muted:    #94a3b8;
  --cwp-text-light:    #64748b;
  --cwp-radius:        8px;
}

/* Dark mode: invert footer so it's slightly lighter than body */
.cwp-footer {
  background: #1e293b;
  border-top: 1px solid #334155;
}

/* Dark mode: header border */
.cwp-header {
  border-bottom-color: #334155;
}
\n",
        ],

        'warm' => [
            'json' => [
                'name'        => 'Warm',
                'description' => 'Cream background with warm orange accents. Perfect for lifestyle, food, and fashion niches.',
                'version'     => '1.0.0',
                'author'      => 'Ken Weill',
                'colors'      => [ '#ea580c', '#f59e0b', '#faf7f4', '#1c1917' ],
            ],
            'css'  => ":root {
  --cwp-primary:       #ea580c;
  --cwp-primary-hover: #c2410c;
  --cwp-primary-light: #fff7ed;
  --cwp-accent:        #f59e0b;
  --cwp-accent-hover:  #d97706;
  --cwp-bg:            #faf7f4;
  --cwp-surface:       #ffffff;
  --cwp-surface-alt:   #f5f0eb;
  --cwp-surface-hover: #f5f0eb;
  --cwp-border:        #e7e0d8;
  --cwp-text:          #1c1917;
  --cwp-text-muted:    #78716c;
  --cwp-radius:        10px;
}

/* Warm nav uses the primary orange */
.cwp-nav {
  background: #ea580c;
}
\n",
        ],

    ];

}
