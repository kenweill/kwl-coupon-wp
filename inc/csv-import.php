<?php
/**
 * KWL Coupon WP — CSV Import
 *
 * Provides bulk import of Stores and Coupons from CSV files.
 *
 * Store CSV columns (required*):
 *   name*, slug, website, affiliate_url, network, description, featured
 *
 * Coupon CSV columns (required*):
 *   title*, store_name*, type, code, discount, expiry, verified, exclusive,
 *   affiliate_url, categories, tags, description
 *
 * Behaviour:
 * - Duplicate detection: stores matched by slug or name, coupons by title+store
 * - Duplicates are skipped (not overwritten) by default
 * - Empty required fields → row skipped with log entry
 * - Batch processing: 10 rows per AJAX call to avoid timeout
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   ADMIN MENU PAGE
   ============================================================================= */

/**
 * Register CSV import admin page.
 */
function kwl_register_import_page(): void {

    add_submenu_page(
        'tools.php',
        __( 'KWL Coupon WP — CSV Import', 'kwl-coupon-wp' ),
        __( 'Import Coupons',             'kwl-coupon-wp' ),
        'manage_options',
        'kwl-csv-import',
        'kwl_render_import_page'
    );

}
add_action( 'admin_menu', 'kwl_register_import_page' );


/**
 * Render the CSV import admin page.
 */
function kwl_render_import_page(): void {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    ?>
    <div class="wrap kwl-import-wrap">

        <h1><?php esc_html_e( 'CSV Import', 'kwl-coupon-wp' ); ?></h1>
        <p class="kwl-import-description">
            <?php esc_html_e( 'Import stores and coupons in bulk from a CSV file. Download a template to see the required column format.', 'kwl-coupon-wp' ); ?>
        </p>

        <?php settings_errors( 'kwl_import' ); ?>

        <div class="kwl-import-card">
            <h2><?php esc_html_e( 'Import Settings', 'kwl-coupon-wp' ); ?></h2>

            <form id="kwl-csv-import-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'kwl_admin_nonce', 'nonce' ); ?>

                <!-- Import Type -->
                <p><strong><?php esc_html_e( 'What are you importing?', 'kwl-coupon-wp' ); ?></strong></p>
                <div class="kwl-import-type-options">
                    <label class="kwl-import-type-option">
                        <input type="radio" name="kwl_import_type" value="stores" checked>
                        🏪 <?php esc_html_e( 'Stores', 'kwl-coupon-wp' ); ?>
                    </label>
                    <label class="kwl-import-type-option">
                        <input type="radio" name="kwl_import_type" value="coupons">
                        🏷️ <?php esc_html_e( 'Coupons', 'kwl-coupon-wp' ); ?>
                    </label>
                </div>

                <!-- File Upload -->
                <div class="kwl-file-drop-zone" id="kwl-drop-zone">
                    <div class="kwl-file-drop-zone__icon">📄</div>
                    <div class="kwl-file-drop-zone__text">
                        <?php esc_html_e( 'Drop your CSV file here or click to browse', 'kwl-coupon-wp' ); ?>
                    </div>
                    <div class="kwl-file-drop-zone__file-name" id="kwl-file-name"></div>
                    <input type="file" id="kwl-csv-file" name="kwl_csv_file" accept=".csv">
                </div>

                <!-- Progress -->
                <div id="kwl-import-progress">
                    <div class="kwl-progress-track">
                        <div class="kwl-progress-bar"></div>
                    </div>
                    <div class="kwl-progress-text">0 / 0 rows</div>
                </div>

                <!-- Status -->
                <div id="kwl-import-status"></div>

                <!-- Log -->
                <div id="kwl-import-log"></div>

                <!-- Actions -->
                <p style="margin-top:16px;">
                    <button type="submit" id="kwl-import-btn" class="button button-primary button-large">
                        <?php esc_html_e( 'Start Import', 'kwl-coupon-wp' ); ?>
                    </button>
                </p>

            </form>

            <!-- CSV Templates -->
            <div class="kwl-csv-templates">
                <p><?php esc_html_e( 'Download a CSV template to see the required column format:', 'kwl-coupon-wp' ); ?></p>
                <a href="<?php echo esc_url( add_query_arg( [ 'kwl_csv_template' => 'stores' ], admin_url( 'admin.php' ) ) ); ?>" class="button">
                    📥 <?php esc_html_e( 'Stores Template', 'kwl-coupon-wp' ); ?>
                </a>
                <a href="<?php echo esc_url( add_query_arg( [ 'kwl_csv_template' => 'coupons' ], admin_url( 'admin.php' ) ) ); ?>" class="button">
                    📥 <?php esc_html_e( 'Coupons Template', 'kwl-coupon-wp' ); ?>
                </a>
            </div>

        </div>

        <!-- Column Reference -->
        <div class="kwl-import-card">
            <h2><?php esc_html_e( 'Column Reference', 'kwl-coupon-wp' ); ?></h2>

            <h3><?php esc_html_e( 'Stores CSV', 'kwl-coupon-wp' ); ?></h3>
            <table class="widefat striped" style="margin-bottom:20px;">
                <thead><tr><th><?php esc_html_e( 'Column', 'kwl-coupon-wp' ); ?></th><th><?php esc_html_e( 'Required', 'kwl-coupon-wp' ); ?></th><th><?php esc_html_e( 'Notes', 'kwl-coupon-wp' ); ?></th></tr></thead>
                <tbody>
                    <?php
                    $store_cols = [
                        [ 'name',          '✓', 'Store display name' ],
                        [ 'slug',          '',  'URL slug. Auto-generated from name if blank. Dots allowed (shopee.ph)' ],
                        [ 'website',       '',  'Store homepage URL' ],
                        [ 'affiliate_url', '',  'Your affiliate link for this store' ],
                        [ 'network',       '',  'shareasale, cj, impact, awin, rakuten, amazon, direct, other' ],
                        [ 'description',   '',  'Store description (used in post content)' ],
                        [ 'featured',      '',  '1 = show in Featured Stores section, 0 = no' ],
                    ];
                    foreach ( $store_cols as $col ) {
                        printf(
                            '<tr><td><code>%s</code></td><td>%s</td><td>%s</td></tr>',
                            esc_html( $col[0] ),
                            esc_html( $col[1] ),
                            esc_html( $col[2] )
                        );
                    }
                    ?>
                </tbody>
            </table>

            <h3><?php esc_html_e( 'Coupons CSV', 'kwl-coupon-wp' ); ?></h3>
            <table class="widefat striped">
                <thead><tr><th><?php esc_html_e( 'Column', 'kwl-coupon-wp' ); ?></th><th><?php esc_html_e( 'Required', 'kwl-coupon-wp' ); ?></th><th><?php esc_html_e( 'Notes', 'kwl-coupon-wp' ); ?></th></tr></thead>
                <tbody>
                    <?php
                    $coupon_cols = [
                        [ 'title',         '✓', 'Coupon title / description' ],
                        [ 'store_name',    '✓', 'Must match an existing store name exactly' ],
                        [ 'type',          '',  'code, deal, freeshipping. Default: code' ],
                        [ 'code',          '',  'Coupon code (for type=code). Auto-uppercased' ],
                        [ 'discount',      '',  'Discount label. E.g. 50% Off, $10 Off' ],
                        [ 'expiry',        '',  'Expiry date in YYYY-MM-DD format' ],
                        [ 'verified',      '',  '1 = verified, 0 = not. Default: 0' ],
                        [ 'exclusive',     '',  '1 = exclusive, 0 = not. Default: 0' ],
                        [ 'affiliate_url', '',  'Coupon-specific affiliate URL (overrides store URL)' ],
                        [ 'categories',    '',  'Category names, pipe-separated. E.g. Web Hosting|VPN' ],
                        [ 'tags',          '',  'Tag names, pipe-separated. E.g. Free Shipping|Student' ],
                        [ 'description',   '',  'Long description (used in post content)' ],
                    ];
                    foreach ( $coupon_cols as $col ) {
                        printf(
                            '<tr><td><code>%s</code></td><td>%s</td><td>%s</td></tr>',
                            esc_html( $col[0] ),
                            esc_html( $col[1] ),
                            esc_html( $col[2] )
                        );
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- File drop zone JS (inline — small enough) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const zone    = document.getElementById('kwl-drop-zone');
        const input   = document.getElementById('kwl-csv-file');
        const label   = document.getElementById('kwl-file-name');
        const logEl   = document.getElementById('kwl-import-log');

        if (!zone || !input) return;

        zone.addEventListener('click', () => input.click());

        input.addEventListener('change', function() {
            if (this.files[0]) {
                label.textContent = this.files[0].name;
                if (logEl) { logEl.classList.add('kwl-log-active'); logEl.innerHTML = ''; }
            }
        });

        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('kwl-drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('kwl-drag-over'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('kwl-drag-over');
            const file = e.dataTransfer?.files[0];
            if (file && file.name.endsWith('.csv')) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                label.textContent = file.name;
                if (logEl) { logEl.classList.add('kwl-log-active'); logEl.innerHTML = ''; }
            }
        });
    });
    </script>
    <?php

}


/* =============================================================================
   CSV TEMPLATE DOWNLOAD
   ============================================================================= */

/**
 * Handle template CSV download requests.
 */
function kwl_handle_csv_template_download(): void {

    if ( ! isset( $_GET['kwl_csv_template'] ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Permission denied.', 'kwl-coupon-wp' ) );
    }

    $type = sanitize_key( $_GET['kwl_csv_template'] );

    if ( $type === 'stores' ) {
        $filename = 'kwl-stores-template.csv';
        $content  = "name,slug,website,affiliate_url,network,description,featured\n";
        $content .= "\"Example Store\",example-store,https://example.com,https://affiliate.example.com/ref=123,direct,\"A great online store.\",1\n";
        $content .= "\"Shopee PH\",shopee.ph,https://shopee.ph,,direct,,0\n";
    } elseif ( $type === 'coupons' ) {
        $filename = 'kwl-coupons-template.csv';
        $content  = "title,store_name,type,code,discount,expiry,verified,exclusive,affiliate_url,categories,tags,description\n";
        $content  .= "\"50% Off Hosting\",\"Example Store\",code,SAVE50,\"50% Off\",2025-12-31,1,0,,Web Hosting,Free Trial,\"Get 50% off any hosting plan.\"\n";
        $content  .= "\"Free Shipping on Orders\",\"Example Store\",freeshipping,,\"Free Shipping\",,,0,,,Free Shipping,\n";
        $content  .= "\"Flash Sale Deal\",\"Shopee PH\",deal,,\"Up to 80% Off\",2025-06-30,1,0,,,Flash Sale,\n";
    } else {
        wp_die( esc_html__( 'Invalid template type.', 'kwl-coupon-wp' ) );
    }

    header( 'Content-Type: text/csv; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    // BOM for Excel UTF-8 compatibility
    echo "\xEF\xBB\xBF" . $content; // phpcs:ignore
    exit;

}
add_action( 'admin_init', 'kwl_handle_csv_template_download' );


/* =============================================================================
   AJAX: BATCH IMPORT
   ============================================================================= */

/**
 * AJAX handler: process a batch of CSV rows.
 */
function kwl_ajax_csv_import_batch(): void {

    check_ajax_referer( 'kwl_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied.' ] );
    }

    $import_type = sanitize_key( $_POST['import_type'] ?? 'stores' );
    $rows_json   = wp_unslash( $_POST['rows'] ?? '[]' );
    $rows        = json_decode( $rows_json, true );

    if ( ! is_array( $rows ) ) {
        wp_send_json_error( [ 'message' => 'Invalid row data.' ] );
    }

    $log = [];

    foreach ( $rows as $row ) {
        if ( $import_type === 'stores' ) {
            $result = kwl_import_store_row( $row );
        } else {
            $result = kwl_import_coupon_row( $row );
        }
        $log[] = $result;
    }

    wp_send_json_success( [ 'log' => $log ] );

}
add_action( 'wp_ajax_kwl_csv_import_batch', 'kwl_ajax_csv_import_batch' );


/* =============================================================================
   STORE ROW IMPORT
   ============================================================================= */

/**
 * Import a single store from a CSV row array.
 *
 * @param  array $row  Associative array of column => value.
 * @return array       { status: 'ok'|'skip'|'error', message: string }
 */
function kwl_import_store_row( array $row ): array {

    $name = trim( $row['name'] ?? '' );

    if ( empty( $name ) ) {
        return [ 'status' => 'skip', 'message' => 'Row skipped — missing required field: name.' ];
    }

    // Generate or use provided slug
    $raw_slug = trim( $row['slug'] ?? '' );
    $slug     = ! empty( $raw_slug )
        ? kwl_sanitize_slug( $raw_slug )
        : kwl_generate_slug_from_name( $name );

    // Duplicate check by slug
    $existing = get_posts( [
        'post_type'   => 'kwl_store',
        'post_status' => 'any',
        'name'        => $slug,
        'numberposts' => 1,
    ] );

    if ( ! empty( $existing ) ) {
        return [ 'status' => 'skip', 'message' => "Skipped \"{$name}\" — store with slug \"{$slug}\" already exists." ];
    }

    // Also check by name
    $existing_by_name = get_posts( [
        'post_type'   => 'kwl_store',
        'post_status' => 'any',
        'title'       => $name,
        'numberposts' => 1,
    ] );

    if ( ! empty( $existing_by_name ) ) {
        return [ 'status' => 'skip', 'message' => "Skipped \"{$name}\" — store with this name already exists." ];
    }

    // Get unique slug
    $final_slug = kwl_unique_slug( $slug, 'kwl_store' );

    // Create post
    $post_id = wp_insert_post( [
        'post_title'   => sanitize_text_field( $name ),
        'post_name'    => $final_slug,
        'post_type'    => 'kwl_store',
        'post_status'  => 'publish',
        'post_content' => wp_kses_post( $row['description'] ?? '' ),
    ] );

    if ( is_wp_error( $post_id ) ) {
        return [ 'status' => 'error', 'message' => "Error creating \"{$name}\": " . $post_id->get_error_message() ];
    }

    // Save meta
    $website  = esc_url_raw( $row['website'] ?? '' );
    $aff_url  = esc_url_raw( $row['affiliate_url'] ?? '' );
    $network  = sanitize_key( $row['network'] ?? '' );
    $featured = ( trim( $row['featured'] ?? '' ) === '1' ) ? '1' : '0';

    if ( $website )  update_post_meta( $post_id, '_kwl_store_website',       $website );
    if ( $aff_url )  update_post_meta( $post_id, '_kwl_store_affiliate_url', $aff_url );
    if ( $network )  update_post_meta( $post_id, '_kwl_store_network',       $network );
    update_post_meta( $post_id, '_kwl_store_slug',     $final_slug );
    update_post_meta( $post_id, '_kwl_store_featured', $featured );

    return [ 'status' => 'ok', 'message' => "Imported store \"{$name}\" (/{$final_slug}/)." ];

}


/* =============================================================================
   COUPON ROW IMPORT
   ============================================================================= */

/**
 * Import a single coupon from a CSV row array.
 *
 * @param  array $row
 * @return array
 */
function kwl_import_coupon_row( array $row ): array {

    $title      = trim( $row['title']      ?? '' );
    $store_name = trim( $row['store_name'] ?? '' );

    if ( empty( $title ) ) {
        return [ 'status' => 'skip', 'message' => 'Row skipped — missing required field: title.' ];
    }

    if ( empty( $store_name ) ) {
        return [ 'status' => 'skip', 'message' => "Row skipped \"{$title}\" — missing required field: store_name." ];
    }

    // Find store by name
    $stores = get_posts( [
        'post_type'   => 'kwl_store',
        'post_status' => 'publish',
        'title'       => $store_name,
        'numberposts' => 1,
    ] );

    if ( empty( $stores ) ) {
        return [ 'status' => 'skip', 'message' => "Skipped \"{$title}\" — store \"{$store_name}\" not found. Import stores first." ];
    }

    $store_id = $stores[0]->ID;

    // Duplicate check: same title + store
    $existing = get_posts( [
        'post_type'   => 'kwl_coupon',
        'post_status' => 'any',
        'title'       => $title,
        'numberposts' => 1,
        'meta_query'  => [
            [ 'key' => '_kwl_coupon_store_id', 'value' => $store_id, 'type' => 'NUMERIC' ],
        ],
    ] );

    if ( ! empty( $existing ) ) {
        return [ 'status' => 'skip', 'message' => "Skipped \"{$title}\" — already exists for {$store_name}." ];
    }

    // Validate type
    $type = sanitize_key( $row['type'] ?? 'code' );
    if ( ! in_array( $type, [ 'code', 'deal', 'freeshipping' ], true ) ) {
        $type = 'code';
    }

    // Validate expiry date
    $expiry = trim( $row['expiry'] ?? '' );
    if ( $expiry && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $expiry ) ) {
        $expiry = ''; // Invalid date — ignore
    }

    // Create post
    $post_id = wp_insert_post( [
        'post_title'   => sanitize_text_field( $title ),
        'post_type'    => 'kwl_coupon',
        'post_status'  => 'publish',
        'post_content' => wp_kses_post( $row['description'] ?? '' ),
    ] );

    if ( is_wp_error( $post_id ) ) {
        return [ 'status' => 'error', 'message' => "Error creating \"{$title}\": " . $post_id->get_error_message() ];
    }

    // Save meta
    update_post_meta( $post_id, '_kwl_coupon_store_id',  $store_id );
    update_post_meta( $post_id, '_kwl_coupon_type',      $type );
    update_post_meta( $post_id, '_kwl_coupon_code',      strtoupper( sanitize_text_field( $row['code'] ?? '' ) ) );
    update_post_meta( $post_id, '_kwl_coupon_discount',  sanitize_text_field( $row['discount'] ?? '' ) );
    update_post_meta( $post_id, '_kwl_coupon_expiry',    $expiry );
    update_post_meta( $post_id, '_kwl_coupon_verified',  ( trim( $row['verified']  ?? '' ) === '1' ) ? '1' : '0' );
    update_post_meta( $post_id, '_kwl_coupon_exclusive', ( trim( $row['exclusive'] ?? '' ) === '1' ) ? '1' : '0' );

    $aff_url = esc_url_raw( $row['affiliate_url'] ?? '' );
    if ( $aff_url ) {
        update_post_meta( $post_id, '_kwl_coupon_affiliate', $aff_url );
    }

    // Assign categories (pipe-separated names)
    $cat_names = array_filter( array_map( 'trim', explode( '|', $row['categories'] ?? '' ) ) );
    if ( ! empty( $cat_names ) ) {
        $cat_ids = [];
        foreach ( $cat_names as $cat_name ) {
            $term = get_term_by( 'name', $cat_name, 'kwl_coupon_cat' );
            if ( ! $term ) {
                // Create category if it doesn't exist
                $new_term = wp_insert_term( $cat_name, 'kwl_coupon_cat' );
                if ( ! is_wp_error( $new_term ) ) {
                    $cat_ids[] = $new_term['term_id'];
                }
            } else {
                $cat_ids[] = $term->term_id;
            }
        }
        if ( ! empty( $cat_ids ) ) {
            wp_set_post_terms( $post_id, $cat_ids, 'kwl_coupon_cat' );
        }
    }

    // Assign tags (pipe-separated names)
    $tag_names = array_filter( array_map( 'trim', explode( '|', $row['tags'] ?? '' ) ) );
    if ( ! empty( $tag_names ) ) {
        wp_set_post_terms( $post_id, $tag_names, 'kwl_coupon_tag' );
    }

    $code_display = ( $type === 'code' && ! empty( $row['code'] ) ) ? " [code: {$row['code']}]" : '';
    return [ 'status' => 'ok', 'message' => "Imported coupon \"{$title}\" for {$store_name}{$code_display}." ];

}
