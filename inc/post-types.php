<?php
/**
 * KWL Coupon WP — Custom Post Types
 *
 * Registers:
 *   - kwl_store  : Individual store/brand (e.g. Namecheap, Shopee)
 *   - kwl_coupon : Coupon/deal linked to a store
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   REGISTER: STORE
   ============================================================================= */

/**
 * Register the Store custom post type.
 *
 * URL structure: /store/{slug}/
 * Example:       /store/namecheap
 *                /store/shopee.ph
 */
function kwl_register_post_type_store(): void {

    $labels = [
        'name'                  => __( 'Stores',                        'kwl-coupon-wp' ),
        'singular_name'         => __( 'Store',                         'kwl-coupon-wp' ),
        'add_new'               => __( 'Add Store',                     'kwl-coupon-wp' ),
        'add_new_item'          => __( 'Add New Store',                 'kwl-coupon-wp' ),
        'edit_item'             => __( 'Edit Store',                    'kwl-coupon-wp' ),
        'new_item'              => __( 'New Store',                     'kwl-coupon-wp' ),
        'view_item'             => __( 'View Store',                    'kwl-coupon-wp' ),
        'view_items'            => __( 'View Stores',                   'kwl-coupon-wp' ),
        'search_items'          => __( 'Search Stores',                 'kwl-coupon-wp' ),
        'not_found'             => __( 'No stores found.',              'kwl-coupon-wp' ),
        'not_found_in_trash'    => __( 'No stores found in Trash.',     'kwl-coupon-wp' ),
        'all_items'             => __( 'All Stores',                    'kwl-coupon-wp' ),
        'menu_name'             => __( 'Stores',                        'kwl-coupon-wp' ),
        'archives'              => __( 'Store Archives',                'kwl-coupon-wp' ),
        'attributes'            => __( 'Store Attributes',              'kwl-coupon-wp' ),
        'insert_into_item'      => __( 'Insert into store',             'kwl-coupon-wp' ),
        'uploaded_to_this_item' => __( 'Uploaded to this store',        'kwl-coupon-wp' ),
        'featured_image'        => __( 'Store Logo',                    'kwl-coupon-wp' ),
        'set_featured_image'    => __( 'Set store logo',                'kwl-coupon-wp' ),
        'remove_featured_image' => __( 'Remove store logo',             'kwl-coupon-wp' ),
        'use_featured_image'    => __( 'Use as store logo',             'kwl-coupon-wp' ),
        'filter_items_list'     => __( 'Filter stores list',            'kwl-coupon-wp' ),
        'items_list_navigation' => __( 'Stores list navigation',        'kwl-coupon-wp' ),
        'items_list'            => __( 'Stores list',                   'kwl-coupon-wp' ),
        'item_published'        => __( 'Store published.',              'kwl-coupon-wp' ),
        'item_updated'          => __( 'Store updated.',                'kwl-coupon-wp' ),
    ];

    $args = [
        'labels'              => $labels,
        'description'         => __( 'Stores and brands that have coupons and deals.', 'kwl-coupon-wp' ),
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => [
            'slug'       => 'store',    // /store/{slug}
            'with_front' => false,
            'feeds'      => false,
            'pages'      => true,
        ],
        'capability_type'     => 'post',
        'has_archive'         => 'stores',  // /stores/ — all stores listing
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-store',
        'supports'            => [
            'title',            // Store name
            'editor',           // Store description
            'thumbnail',        // Store logo (featured image)
            'custom-fields',    // For meta fields
            'revisions',
        ],
        'show_in_rest'        => true,  // Gutenberg / REST API support
        'taxonomies'          => [],    // Stores use their own meta for categories
    ];

    register_post_type( 'kwl_store', $args );

}
add_action( 'init', 'kwl_register_post_type_store' );


/* =============================================================================
   REGISTER: COUPON
   ============================================================================= */

/**
 * Register the Coupon custom post type.
 *
 * URL structure: /coupon/{slug}/
 * Example:       /coupon/namecheap-50-off-hosting
 *
 * Note: Coupon slugs are auto-generated from title but fully editable.
 */
function kwl_register_post_type_coupon(): void {

    $labels = [
        'name'                  => __( 'Coupons',                       'kwl-coupon-wp' ),
        'singular_name'         => __( 'Coupon',                        'kwl-coupon-wp' ),
        'add_new'               => __( 'Add Coupon',                    'kwl-coupon-wp' ),
        'add_new_item'          => __( 'Add New Coupon',                'kwl-coupon-wp' ),
        'edit_item'             => __( 'Edit Coupon',                   'kwl-coupon-wp' ),
        'new_item'              => __( 'New Coupon',                    'kwl-coupon-wp' ),
        'view_item'             => __( 'View Coupon',                   'kwl-coupon-wp' ),
        'view_items'            => __( 'View Coupons',                  'kwl-coupon-wp' ),
        'search_items'          => __( 'Search Coupons',                'kwl-coupon-wp' ),
        'not_found'             => __( 'No coupons found.',             'kwl-coupon-wp' ),
        'not_found_in_trash'    => __( 'No coupons found in Trash.',    'kwl-coupon-wp' ),
        'all_items'             => __( 'All Coupons',                   'kwl-coupon-wp' ),
        'menu_name'             => __( 'Coupons',                       'kwl-coupon-wp' ),
        'archives'              => __( 'Coupon Archives',               'kwl-coupon-wp' ),
        'featured_image'        => __( 'Coupon Image',                  'kwl-coupon-wp' ),
        'set_featured_image'    => __( 'Set coupon image',              'kwl-coupon-wp' ),
        'remove_featured_image' => __( 'Remove coupon image',           'kwl-coupon-wp' ),
        'filter_items_list'     => __( 'Filter coupons list',           'kwl-coupon-wp' ),
        'items_list_navigation' => __( 'Coupons list navigation',       'kwl-coupon-wp' ),
        'items_list'            => __( 'Coupons list',                  'kwl-coupon-wp' ),
        'item_published'        => __( 'Coupon published.',             'kwl-coupon-wp' ),
        'item_updated'          => __( 'Coupon updated.',               'kwl-coupon-wp' ),
    ];

    $args = [
        'labels'              => $labels,
        'description'         => __( 'Coupon codes, deals, and offers from stores.', 'kwl-coupon-wp' ),
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => [
            'slug'       => 'coupon',   // /coupon/{slug}
            'with_front' => false,
            'feeds'      => false,
            'pages'      => false,
        ],
        'capability_type'     => 'post',
        'has_archive'         => 'coupons', // /coupons/ — all coupons listing
        'hierarchical'        => false,
        'menu_position'       => 6,
        'menu_icon'           => 'dashicons-tickets-alt',
        'supports'            => [
            'title',            // Coupon title / description
            'editor',           // Long description (optional)
            'thumbnail',        // Coupon image
            'custom-fields',
            'revisions',
        ],
        'show_in_rest'        => true,
        'taxonomies'          => [ 'kwl_coupon_cat', 'kwl_coupon_tag' ],
    ];

    register_post_type( 'kwl_coupon', $args );

}
add_action( 'init', 'kwl_register_post_type_coupon' );


/* =============================================================================
   CUSTOM META BOXES — STORE
   ============================================================================= */

/**
 * Register meta boxes for the Store post type.
 */
function kwl_store_meta_boxes(): void {

    add_meta_box(
        'kwl_store_details',
        __( 'Store Details', 'kwl-coupon-wp' ),
        'kwl_store_details_cb',
        'kwl_store',
        'normal',
        'high'
    );

    add_meta_box(
        'kwl_store_seo',
        __( 'SEO Settings', 'kwl-coupon-wp' ),
        'kwl_store_seo_cb',
        'kwl_store',
        'normal',
        'default'
    );

}
add_action( 'add_meta_boxes', 'kwl_store_meta_boxes' );


/**
 * Store Details meta box output.
 *
 * @param WP_Post $post
 */
function kwl_store_details_cb( WP_Post $post ): void {

    wp_nonce_field( 'kwl_store_save', 'kwl_store_nonce' );

    $website      = get_post_meta( $post->ID, '_kwl_store_website',       true );
    $affiliate    = get_post_meta( $post->ID, '_kwl_store_affiliate_url', true );
    $custom_slug  = get_post_meta( $post->ID, '_kwl_store_slug',          true );
    $featured     = get_post_meta( $post->ID, '_kwl_store_featured',      true );
    $network      = get_post_meta( $post->ID, '_kwl_store_network',       true );

    ?>
    <table class="form-table kwl-meta-table">

        <tr>
            <th><label for="kwl_store_slug"><?php esc_html_e( 'Store Slug', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <input
                    type="text"
                    id="kwl_store_slug"
                    name="kwl_store_slug"
                    value="<?php echo esc_attr( $custom_slug ); ?>"
                    class="regular-text kwl-slug-field"
                    placeholder="<?php esc_attr_e( 'e.g. namecheap or shopee.ph', 'kwl-coupon-wp' ); ?>"
                    data-post-type="kwl_store"
                    data-post-id="<?php echo esc_attr( $post->ID ); ?>"
                />
                <span class="kwl-slug-status"></span>
                <p class="description">
                    <?php esc_html_e( 'Used in the URL: /store/{slug}/. Dots are allowed (e.g. shopee.ph). Leave blank to auto-generate from store name.', 'kwl-coupon-wp' ); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th><label for="kwl_store_website"><?php esc_html_e( 'Store Website', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <input
                    type="url"
                    id="kwl_store_website"
                    name="kwl_store_website"
                    value="<?php echo esc_url( $website ); ?>"
                    class="regular-text"
                    placeholder="https://www.example.com"
                />
            </td>
        </tr>

        <tr>
            <th><label for="kwl_store_affiliate_url"><?php esc_html_e( 'Affiliate URL', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <input
                    type="url"
                    id="kwl_store_affiliate_url"
                    name="kwl_store_affiliate_url"
                    value="<?php echo esc_url( $affiliate ); ?>"
                    class="large-text"
                    placeholder="https://www.shareasale.com/r.cfm?..."
                />
                <p class="description">
                    <?php esc_html_e( 'Your affiliate link for this store. Used when visitors click "Visit Store" or reveal a coupon.', 'kwl-coupon-wp' ); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th><label for="kwl_store_network"><?php esc_html_e( 'Affiliate Network', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <select id="kwl_store_network" name="kwl_store_network">
                    <option value="" <?php selected( $network, '' ); ?>><?php esc_html_e( '— Select Network —', 'kwl-coupon-wp' ); ?></option>
                    <option value="shareasale"    <?php selected( $network, 'shareasale' ); ?>>ShareASale</option>
                    <option value="cj"            <?php selected( $network, 'cj' ); ?>>Commission Junction (CJ)</option>
                    <option value="impact"        <?php selected( $network, 'impact' ); ?>>Impact</option>
                    <option value="awin"          <?php selected( $network, 'awin' ); ?>>Awin</option>
                    <option value="rakuten"       <?php selected( $network, 'rakuten' ); ?>>Rakuten</option>
                    <option value="amazon"        <?php selected( $network, 'amazon' ); ?>>Amazon Associates</option>
                    <option value="direct"        <?php selected( $network, 'direct' ); ?>>Direct / In-House</option>
                    <option value="other"         <?php selected( $network, 'other' ); ?>><?php esc_html_e( 'Other', 'kwl-coupon-wp' ); ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Featured Store', 'kwl-coupon-wp' ); ?></th>
            <td>
                <label>
                    <input
                        type="checkbox"
                        name="kwl_store_featured"
                        value="1"
                        <?php checked( $featured, '1' ); ?>
                    />
                    <?php esc_html_e( 'Show this store in the Featured Stores section on the homepage.', 'kwl-coupon-wp' ); ?>
                </label>
            </td>
        </tr>

    </table>
    <?php

}


/**
 * Store SEO meta box output.
 *
 * @param WP_Post $post
 */
function kwl_store_seo_cb( WP_Post $post ): void {

    $meta_title = get_post_meta( $post->ID, '_kwl_meta_title',       true );
    $meta_desc  = get_post_meta( $post->ID, '_kwl_meta_description', true );

    ?>
    <table class="form-table">

        <tr>
            <th><label for="kwl_meta_title"><?php esc_html_e( 'Meta Title', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <input
                    type="text"
                    id="kwl_meta_title"
                    name="kwl_meta_title"
                    value="<?php echo esc_attr( $meta_title ); ?>"
                    class="large-text"
                    maxlength="70"
                    placeholder="<?php esc_attr_e( 'Leave blank to auto-generate', 'kwl-coupon-wp' ); ?>"
                />
                <p class="description"><?php esc_html_e( 'Recommended: 50–70 characters. Auto-generated if left blank.', 'kwl-coupon-wp' ); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="kwl_meta_description"><?php esc_html_e( 'Meta Description', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <textarea
                    id="kwl_meta_description"
                    name="kwl_meta_description"
                    class="large-text"
                    rows="3"
                    maxlength="165"
                    placeholder="<?php esc_attr_e( 'Leave blank to auto-generate', 'kwl-coupon-wp' ); ?>"
                ><?php echo esc_textarea( $meta_desc ); ?></textarea>
                <p class="description"><?php esc_html_e( 'Recommended: 120–160 characters. Auto-generated if left blank.', 'kwl-coupon-wp' ); ?></p>
            </td>
        </tr>

    </table>
    <?php

}


/* =============================================================================
   CUSTOM META BOXES — COUPON
   ============================================================================= */

/**
 * Register meta boxes for the Coupon post type.
 */
function kwl_coupon_meta_boxes(): void {

    add_meta_box(
        'kwl_coupon_details',
        __( 'Coupon Details', 'kwl-coupon-wp' ),
        'kwl_coupon_details_cb',
        'kwl_coupon',
        'normal',
        'high'
    );

    add_meta_box(
        'kwl_coupon_seo',
        __( 'SEO Settings', 'kwl-coupon-wp' ),
        'kwl_coupon_seo_cb',
        'kwl_coupon',
        'normal',
        'default'
    );

    add_meta_box(
        'kwl_coupon_stats',
        __( 'Coupon Stats', 'kwl-coupon-wp' ),
        'kwl_coupon_stats_cb',
        'kwl_coupon',
        'side',
        'default'
    );

}
add_action( 'add_meta_boxes', 'kwl_coupon_meta_boxes' );


/**
 * Coupon Details meta box output.
 *
 * @param WP_Post $post
 */
function kwl_coupon_details_cb( WP_Post $post ): void {

    wp_nonce_field( 'kwl_coupon_save', 'kwl_coupon_nonce' );

    // Get saved values
    $store_id       = get_post_meta( $post->ID, '_kwl_coupon_store_id',   true );
    $type           = get_post_meta( $post->ID, '_kwl_coupon_type',       true ) ?: 'code';
    $code           = get_post_meta( $post->ID, '_kwl_coupon_code',       true );
    $discount       = get_post_meta( $post->ID, '_kwl_coupon_discount',   true );
    $expiry         = get_post_meta( $post->ID, '_kwl_coupon_expiry',     true );
    $verified       = get_post_meta( $post->ID, '_kwl_coupon_verified',   true );
    $exclusive      = get_post_meta( $post->ID, '_kwl_coupon_exclusive',  true );
    $affiliate_url  = get_post_meta( $post->ID, '_kwl_coupon_affiliate',  true );

    // Get all stores for dropdown
    $stores = get_posts( [
        'post_type'      => 'kwl_store',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ] );

    ?>
    <table class="form-table kwl-meta-table">

        <tr>
            <th><label for="kwl_coupon_store_id"><?php esc_html_e( 'Store', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <select id="kwl_coupon_store_id" name="kwl_coupon_store_id" required>
                    <option value=""><?php esc_html_e( '— Select a Store —', 'kwl-coupon-wp' ); ?></option>
                    <?php foreach ( $stores as $store ) : ?>
                        <option value="<?php echo esc_attr( $store->ID ); ?>" <?php selected( $store_id, $store->ID ); ?>>
                            <?php echo esc_html( $store->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Coupon Type', 'kwl-coupon-wp' ); ?></th>
            <td>
                <label class="kwl-radio">
                    <input type="radio" name="kwl_coupon_type" value="code" <?php checked( $type, 'code' ); ?>>
                    <?php esc_html_e( 'Coupon Code', 'kwl-coupon-wp' ); ?>
                </label>
                &nbsp;&nbsp;
                <label class="kwl-radio">
                    <input type="radio" name="kwl_coupon_type" value="deal" <?php checked( $type, 'deal' ); ?>>
                    <?php esc_html_e( 'Deal (No Code)', 'kwl-coupon-wp' ); ?>
                </label>
                &nbsp;&nbsp;
                <label class="kwl-radio">
                    <input type="radio" name="kwl_coupon_type" value="freeshipping" <?php checked( $type, 'freeshipping' ); ?>>
                    <?php esc_html_e( 'Free Shipping', 'kwl-coupon-wp' ); ?>
                </label>
            </td>
        </tr>

        <tr class="kwl-row-code" <?php echo $type !== 'code' ? 'style="display:none"' : ''; ?>>
            <th><label for="kwl_coupon_code"><?php esc_html_e( 'Coupon Code', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <input
                    type="text"
                    id="kwl_coupon_code"
                    name="kwl_coupon_code"
                    value="<?php echo esc_attr( $code ); ?>"
                    class="regular-text kwl-font-mono"
                    placeholder="e.g. SAVE50"
                    style="font-family: monospace; font-size: 1rem; letter-spacing: 0.05em;"
                />
            </td>
        </tr>

        <tr>
            <th><label for="kwl_coupon_discount"><?php esc_html_e( 'Discount Label', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <input
                    type="text"
                    id="kwl_coupon_discount"
                    name="kwl_coupon_discount"
                    value="<?php echo esc_attr( $discount ); ?>"
                    class="regular-text"
                    placeholder="<?php esc_attr_e( 'e.g. 50% Off, $10 Off, Free Shipping', 'kwl-coupon-wp' ); ?>"
                />
                <p class="description"><?php esc_html_e( 'Displayed prominently on the coupon card.', 'kwl-coupon-wp' ); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="kwl_coupon_expiry"><?php esc_html_e( 'Expiry Date', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <input
                    type="date"
                    id="kwl_coupon_expiry"
                    name="kwl_coupon_expiry"
                    value="<?php echo esc_attr( $expiry ); ?>"
                />
                <p class="description"><?php esc_html_e( 'Leave blank if coupon has no expiry. Expired coupons are hidden automatically.', 'kwl-coupon-wp' ); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="kwl_coupon_affiliate"><?php esc_html_e( 'Coupon-Specific Affiliate URL', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <input
                    type="url"
                    id="kwl_coupon_affiliate"
                    name="kwl_coupon_affiliate"
                    value="<?php echo esc_url( $affiliate_url ); ?>"
                    class="large-text"
                    placeholder="<?php esc_attr_e( 'Leave blank to use the store\'s default affiliate URL', 'kwl-coupon-wp' ); ?>"
                />
            </td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Badges', 'kwl-coupon-wp' ); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="kwl_coupon_verified" value="1" <?php checked( $verified, '1' ); ?>>
                    <?php esc_html_e( 'Verified — This coupon has been tested and works.', 'kwl-coupon-wp' ); ?>
                </label>
                <br><br>
                <label>
                    <input type="checkbox" name="kwl_coupon_exclusive" value="1" <?php checked( $exclusive, '1' ); ?>>
                    <?php esc_html_e( 'Exclusive — This coupon is exclusive to this site.', 'kwl-coupon-wp' ); ?>
                </label>
            </td>
        </tr>

    </table>
    <?php

}


/**
 * Coupon SEO meta box.
 *
 * @param WP_Post $post
 */
function kwl_coupon_seo_cb( WP_Post $post ): void {

    $meta_title = get_post_meta( $post->ID, '_kwl_meta_title',       true );
    $meta_desc  = get_post_meta( $post->ID, '_kwl_meta_description', true );

    ?>
    <table class="form-table">
        <tr>
            <th><label for="kwl_coupon_meta_title"><?php esc_html_e( 'Meta Title', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <input type="text" id="kwl_coupon_meta_title" name="kwl_meta_title"
                    value="<?php echo esc_attr( $meta_title ); ?>"
                    class="large-text" maxlength="70"
                    placeholder="<?php esc_attr_e( 'Auto-generated if blank', 'kwl-coupon-wp' ); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="kwl_coupon_meta_desc"><?php esc_html_e( 'Meta Description', 'kwl-coupon-wp' ); ?></label></th>
            <td>
                <textarea id="kwl_coupon_meta_desc" name="kwl_meta_description"
                    class="large-text" rows="3" maxlength="165"
                    placeholder="<?php esc_attr_e( 'Auto-generated if blank', 'kwl-coupon-wp' ); ?>"
                ><?php echo esc_textarea( $meta_desc ); ?></textarea>
            </td>
        </tr>
    </table>
    <?php

}


/**
 * Coupon Stats meta box (read-only).
 *
 * @param WP_Post $post
 */
function kwl_coupon_stats_cb( WP_Post $post ): void {

    $clicks     = (int) get_post_meta( $post->ID, '_kwl_click_count',  true );
    $votes_up   = (int) get_post_meta( $post->ID, '_kwl_votes_up',     true );
    $votes_down = (int) get_post_meta( $post->ID, '_kwl_votes_down',   true );
    $total      = $votes_up + $votes_down;
    $rate       = $total > 0 ? round( ( $votes_up / $total ) * 100 ) : 0;

    ?>
    <ul style="margin: 0; padding: 0;">
        <li style="padding: 6px 0; border-bottom: 1px solid #eee;">
            <strong><?php esc_html_e( 'Total Clicks:', 'kwl-coupon-wp' ); ?></strong>
            <?php echo number_format( $clicks ); ?>
        </li>
        <li style="padding: 6px 0; border-bottom: 1px solid #eee;">
            <strong><?php esc_html_e( 'Worked:', 'kwl-coupon-wp' ); ?></strong>
            <?php echo number_format( $votes_up ); ?>
        </li>
        <li style="padding: 6px 0; border-bottom: 1px solid #eee;">
            <strong><?php esc_html_e( "Didn't Work:", 'kwl-coupon-wp' ); ?></strong>
            <?php echo number_format( $votes_down ); ?>
        </li>
        <li style="padding: 6px 0;">
            <strong><?php esc_html_e( 'Success Rate:', 'kwl-coupon-wp' ); ?></strong>
            <?php echo $rate; ?>%
        </li>
    </ul>
    <p>
        <button type="button" class="button button-small kwl-reset-stats"
            data-post-id="<?php echo esc_attr( $post->ID ); ?>"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'kwl_admin_nonce' ) ); ?>">
            <?php esc_html_e( 'Reset Stats', 'kwl-coupon-wp' ); ?>
        </button>
    </p>
    <?php

}


/* =============================================================================
   SAVE META — STORE
   ============================================================================= */

/**
 * Save store meta fields.
 *
 * @param int $post_id
 */
function kwl_save_store_meta( int $post_id ): void {

    // Security checks
    if (
        ! isset( $_POST['kwl_store_nonce'] ) ||
        ! wp_verify_nonce( $_POST['kwl_store_nonce'], 'kwl_store_save' ) ||
        defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
        ! current_user_can( 'edit_post', $post_id )
    ) {
        return;
    }

    // Slug — sanitized with dot support
    if ( isset( $_POST['kwl_store_slug'] ) ) {
        $raw_slug    = sanitize_text_field( $_POST['kwl_store_slug'] );
        $clean_slug  = kwl_sanitize_slug( $raw_slug );
        $final_slug  = kwl_unique_slug( $clean_slug, 'kwl_store', $post_id );
        update_post_meta( $post_id, '_kwl_store_slug', $final_slug );

        // Also update the post's actual slug
        if ( ! empty( $final_slug ) ) {
            remove_action( 'save_post', 'kwl_save_store_meta' );
            wp_update_post( [
                'ID'        => $post_id,
                'post_name' => $final_slug,
            ] );
            add_action( 'save_post_kwl_store', 'kwl_save_store_meta' );
        }
    }

    // Website URL
    if ( isset( $_POST['kwl_store_website'] ) ) {
        update_post_meta( $post_id, '_kwl_store_website', esc_url_raw( $_POST['kwl_store_website'] ) );
    }

    // Affiliate URL
    if ( isset( $_POST['kwl_store_affiliate_url'] ) ) {
        update_post_meta( $post_id, '_kwl_store_affiliate_url', esc_url_raw( $_POST['kwl_store_affiliate_url'] ) );
    }

    // Network
    if ( isset( $_POST['kwl_store_network'] ) ) {
        update_post_meta( $post_id, '_kwl_store_network', sanitize_key( $_POST['kwl_store_network'] ) );
    }

    // Featured
    $featured = isset( $_POST['kwl_store_featured'] ) ? '1' : '0';
    update_post_meta( $post_id, '_kwl_store_featured', $featured );

    // SEO fields
    if ( isset( $_POST['kwl_meta_title'] ) ) {
        update_post_meta( $post_id, '_kwl_meta_title', sanitize_text_field( $_POST['kwl_meta_title'] ) );
    }

    if ( isset( $_POST['kwl_meta_description'] ) ) {
        update_post_meta( $post_id, '_kwl_meta_description', sanitize_textarea_field( $_POST['kwl_meta_description'] ) );
    }

}
add_action( 'save_post_kwl_store', 'kwl_save_store_meta' );


/* =============================================================================
   SAVE META — COUPON
   ============================================================================= */

/**
 * Save coupon meta fields.
 *
 * @param int $post_id
 */
function kwl_save_coupon_meta( int $post_id ): void {

    if (
        ! isset( $_POST['kwl_coupon_nonce'] ) ||
        ! wp_verify_nonce( $_POST['kwl_coupon_nonce'], 'kwl_coupon_save' ) ||
        defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
        ! current_user_can( 'edit_post', $post_id )
    ) {
        return;
    }

    // Store ID
    if ( isset( $_POST['kwl_coupon_store_id'] ) ) {
        update_post_meta( $post_id, '_kwl_coupon_store_id', absint( $_POST['kwl_coupon_store_id'] ) );
    }

    // Type
    $type = sanitize_key( $_POST['kwl_coupon_type'] ?? 'code' );
    if ( in_array( $type, [ 'code', 'deal', 'freeshipping' ], true ) ) {
        update_post_meta( $post_id, '_kwl_coupon_type', $type );
    }

    // Code (only for type=code)
    if ( isset( $_POST['kwl_coupon_code'] ) ) {
        update_post_meta( $post_id, '_kwl_coupon_code', strtoupper( sanitize_text_field( $_POST['kwl_coupon_code'] ) ) );
    }

    // Discount label
    if ( isset( $_POST['kwl_coupon_discount'] ) ) {
        update_post_meta( $post_id, '_kwl_coupon_discount', sanitize_text_field( $_POST['kwl_coupon_discount'] ) );
    }

    // Expiry date
    if ( isset( $_POST['kwl_coupon_expiry'] ) ) {
        $expiry = sanitize_text_field( $_POST['kwl_coupon_expiry'] );
        // Validate date format
        if ( empty( $expiry ) || preg_match( '/^\d{4}-\d{2}-\d{2}$/', $expiry ) ) {
            update_post_meta( $post_id, '_kwl_coupon_expiry', $expiry );
        }
    }

    // Affiliate URL override
    if ( isset( $_POST['kwl_coupon_affiliate'] ) ) {
        update_post_meta( $post_id, '_kwl_coupon_affiliate', esc_url_raw( $_POST['kwl_coupon_affiliate'] ) );
    }

    // Badges
    update_post_meta( $post_id, '_kwl_coupon_verified',  isset( $_POST['kwl_coupon_verified']  ) ? '1' : '0' );
    update_post_meta( $post_id, '_kwl_coupon_exclusive', isset( $_POST['kwl_coupon_exclusive'] ) ? '1' : '0' );

    // SEO fields
    if ( isset( $_POST['kwl_meta_title'] ) ) {
        update_post_meta( $post_id, '_kwl_meta_title', sanitize_text_field( $_POST['kwl_meta_title'] ) );
    }

    if ( isset( $_POST['kwl_meta_description'] ) ) {
        update_post_meta( $post_id, '_kwl_meta_description', sanitize_textarea_field( $_POST['kwl_meta_description'] ) );
    }

}
add_action( 'save_post_kwl_coupon', 'kwl_save_coupon_meta' );


/* =============================================================================
   FLUSH REWRITE RULES
   ============================================================================= */

/**
 * Flush rewrite rules on theme activation.
 * Ensures /store/ and /coupon/ URLs work immediately.
 */
function kwl_flush_rewrite_rules(): void {
    kwl_register_post_type_store();
    kwl_register_post_type_coupon();
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'kwl_flush_rewrite_rules' );
