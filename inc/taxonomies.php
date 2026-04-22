<?php
/**
 * KWL Coupon WP — Taxonomies
 *
 * Registers:
 *   - kwl_coupon_cat : Coupon categories (e.g. Web Hosting, VPN, Fashion)
 *   - kwl_coupon_tag : Coupon tags (e.g. Free Shipping, Student Discount)
 *
 * Both taxonomies are attached to kwl_coupon post type.
 * Stores are NOT categorized via taxonomy — they link to coupons
 * which carry the category, keeping the data model clean.
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/* =============================================================================
   REGISTER: COUPON CATEGORY
   ============================================================================= */

/**
 * Register the Coupon Category taxonomy.
 *
 * URL structure: /category/{slug}/
 * Example:       /category/web-hosting
 *                /category/vpn
 *                /category/fashion
 */
function kwl_register_taxonomy_coupon_cat(): void {

    $labels = [
        'name'                       => __( 'Coupon Categories',                  'kwl-coupon-wp' ),
        'singular_name'              => __( 'Coupon Category',                    'kwl-coupon-wp' ),
        'search_items'               => __( 'Search Categories',                  'kwl-coupon-wp' ),
        'popular_items'              => __( 'Popular Categories',                 'kwl-coupon-wp' ),
        'all_items'                  => __( 'All Categories',                     'kwl-coupon-wp' ),
        'parent_item'                => __( 'Parent Category',                    'kwl-coupon-wp' ),
        'parent_item_colon'          => __( 'Parent Category:',                  'kwl-coupon-wp' ),
        'edit_item'                  => __( 'Edit Category',                      'kwl-coupon-wp' ),
        'view_item'                  => __( 'View Category',                      'kwl-coupon-wp' ),
        'update_item'                => __( 'Update Category',                    'kwl-coupon-wp' ),
        'add_new_item'               => __( 'Add New Category',                   'kwl-coupon-wp' ),
        'new_item_name'              => __( 'New Category Name',                  'kwl-coupon-wp' ),
        'separate_items_with_commas' => __( 'Separate categories with commas',   'kwl-coupon-wp' ),
        'add_or_remove_items'        => __( 'Add or remove categories',           'kwl-coupon-wp' ),
        'choose_from_most_used'      => __( 'Choose from most used categories',  'kwl-coupon-wp' ),
        'not_found'                  => __( 'No categories found.',               'kwl-coupon-wp' ),
        'no_terms'                   => __( 'No categories',                      'kwl-coupon-wp' ),
        'filter_by_item'             => __( 'Filter by category',                 'kwl-coupon-wp' ),
        'items_list_navigation'      => __( 'Categories list navigation',         'kwl-coupon-wp' ),
        'items_list'                 => __( 'Categories list',                    'kwl-coupon-wp' ),
        'most_used'                  => __( 'Most Used',                          'kwl-coupon-wp' ),
        'back_to_items'              => __( '&larr; Go to Categories',            'kwl-coupon-wp' ),
        'menu_name'                  => __( 'Categories',                         'kwl-coupon-wp' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => true,    // Like WordPress categories (parent/child)
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'show_in_quick_edit'=> true,
        'show_admin_column' => true,    // Shows category column in coupons list
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => [
            'slug'         => 'category',   // /category/{slug}/
            'with_front'   => false,
            'hierarchical' => true,
        ],
        'capabilities'      => [
            'manage_terms' => 'manage_categories',
            'edit_terms'   => 'manage_categories',
            'delete_terms' => 'manage_categories',
            'assign_terms' => 'edit_posts',
        ],
    ];

    register_taxonomy( 'kwl_coupon_cat', [ 'kwl_coupon' ], $args );

}
add_action( 'init', 'kwl_register_taxonomy_coupon_cat' );


/* =============================================================================
   REGISTER: COUPON TAG
   ============================================================================= */

/**
 * Register the Coupon Tag taxonomy.
 *
 * URL structure: /coupon-tag/{slug}/
 * Example:       /coupon-tag/free-shipping
 *                /coupon-tag/student-discount
 *                /coupon-tag/first-order
 */
function kwl_register_taxonomy_coupon_tag(): void {

    $labels = [
        'name'                       => __( 'Coupon Tags',                        'kwl-coupon-wp' ),
        'singular_name'              => __( 'Coupon Tag',                         'kwl-coupon-wp' ),
        'search_items'               => __( 'Search Tags',                        'kwl-coupon-wp' ),
        'popular_items'              => __( 'Popular Tags',                       'kwl-coupon-wp' ),
        'all_items'                  => __( 'All Tags',                           'kwl-coupon-wp' ),
        'edit_item'                  => __( 'Edit Tag',                           'kwl-coupon-wp' ),
        'view_item'                  => __( 'View Tag',                           'kwl-coupon-wp' ),
        'update_item'                => __( 'Update Tag',                         'kwl-coupon-wp' ),
        'add_new_item'               => __( 'Add New Tag',                        'kwl-coupon-wp' ),
        'new_item_name'              => __( 'New Tag Name',                       'kwl-coupon-wp' ),
        'separate_items_with_commas' => __( 'Separate tags with commas',         'kwl-coupon-wp' ),
        'add_or_remove_items'        => __( 'Add or remove tags',                 'kwl-coupon-wp' ),
        'choose_from_most_used'      => __( 'Choose from most used tags',        'kwl-coupon-wp' ),
        'not_found'                  => __( 'No tags found.',                     'kwl-coupon-wp' ),
        'no_terms'                   => __( 'No tags',                            'kwl-coupon-wp' ),
        'items_list_navigation'      => __( 'Tags list navigation',               'kwl-coupon-wp' ),
        'items_list'                 => __( 'Tags list',                          'kwl-coupon-wp' ),
        'most_used'                  => __( 'Most Used',                          'kwl-coupon-wp' ),
        'back_to_items'              => __( '&larr; Go to Tags',                  'kwl-coupon-wp' ),
        'menu_name'                  => __( 'Tags',                               'kwl-coupon-wp' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => false,   // Flat tags, like WordPress post tags
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'show_in_quick_edit'=> true,
        'show_admin_column' => false,   // Tags column would be too noisy
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => [
            'slug'       => 'coupon-tag',   // /coupon-tag/{slug}/
            'with_front' => false,
        ],
    ];

    register_taxonomy( 'kwl_coupon_tag', [ 'kwl_coupon' ], $args );

}
add_action( 'init', 'kwl_register_taxonomy_coupon_tag' );


/* =============================================================================
   TAXONOMY META — CATEGORY EXTRAS
   ============================================================================= */

/**
 * Add extra fields to coupon category edit screen.
 * - Icon (emoji or dashicon class) for category cards on homepage
 * - Custom meta description for category archive SEO
 */
function kwl_coupon_cat_add_fields(): void {
    ?>
    <div class="form-field">
        <label for="kwl_cat_icon"><?php esc_html_e( 'Category Icon (Emoji)', 'kwl-coupon-wp' ); ?></label>
        <input type="text" name="kwl_cat_icon" id="kwl_cat_icon" value="" maxlength="4" placeholder="🛍️" />
        <p><?php esc_html_e( 'An emoji displayed on the category card. E.g. 🛍️ for shopping, ✈️ for travel.', 'kwl-coupon-wp' ); ?></p>
    </div>
    <div class="form-field">
        <label for="kwl_cat_meta_desc"><?php esc_html_e( 'SEO Meta Description', 'kwl-coupon-wp' ); ?></label>
        <textarea name="kwl_cat_meta_desc" id="kwl_cat_meta_desc" rows="3" maxlength="165" placeholder="<?php esc_attr_e( 'Auto-generated if blank', 'kwl-coupon-wp' ); ?>"></textarea>
    </div>
    <?php
}
add_action( 'kwl_coupon_cat_add_form_fields', 'kwl_coupon_cat_add_fields' );


/**
 * Edit screen fields for existing category.
 *
 * @param WP_Term $term
 */
function kwl_coupon_cat_edit_fields( WP_Term $term ): void {

    $icon      = get_term_meta( $term->term_id, 'kwl_cat_icon',      true );
    $meta_desc = get_term_meta( $term->term_id, 'kwl_cat_meta_desc', true );

    ?>
    <tr class="form-field">
        <th><label for="kwl_cat_icon"><?php esc_html_e( 'Category Icon (Emoji)', 'kwl-coupon-wp' ); ?></label></th>
        <td>
            <input type="text" name="kwl_cat_icon" id="kwl_cat_icon"
                value="<?php echo esc_attr( $icon ); ?>" maxlength="4" placeholder="🛍️" />
            <p class="description"><?php esc_html_e( 'Emoji shown on category cards.', 'kwl-coupon-wp' ); ?></p>
        </td>
    </tr>
    <tr class="form-field">
        <th><label for="kwl_cat_meta_desc"><?php esc_html_e( 'SEO Meta Description', 'kwl-coupon-wp' ); ?></label></th>
        <td>
            <textarea name="kwl_cat_meta_desc" id="kwl_cat_meta_desc"
                rows="3" maxlength="165"
            ><?php echo esc_textarea( $meta_desc ); ?></textarea>
            <p class="description"><?php esc_html_e( '120–160 characters. Auto-generated if blank.', 'kwl-coupon-wp' ); ?></p>
        </td>
    </tr>
    <?php

}
add_action( 'kwl_coupon_cat_edit_form_fields', 'kwl_coupon_cat_edit_fields' );


/**
 * Save taxonomy extra fields.
 *
 * @param int $term_id
 */
function kwl_save_coupon_cat_fields( int $term_id ): void {

    if ( isset( $_POST['kwl_cat_icon'] ) ) {
        update_term_meta( $term_id, 'kwl_cat_icon', sanitize_text_field( $_POST['kwl_cat_icon'] ) );
    }

    if ( isset( $_POST['kwl_cat_meta_desc'] ) ) {
        update_term_meta( $term_id, 'kwl_cat_meta_desc', sanitize_textarea_field( $_POST['kwl_cat_meta_desc'] ) );
    }

}
add_action( 'created_kwl_coupon_cat', 'kwl_save_coupon_cat_fields' );
add_action( 'edited_kwl_coupon_cat',  'kwl_save_coupon_cat_fields' );


/* =============================================================================
   HELPER FUNCTIONS
   ============================================================================= */

/**
 * Get all coupon categories with coupon counts.
 *
 * @param int $number   Max number of categories to return. 0 = all.
 * @param bool $hide_empty  Hide categories with no coupons.
 * @return WP_Term[]
 */
function kwl_get_coupon_categories( int $number = 0, bool $hide_empty = true ): array {

    return get_terms( [
        'taxonomy'   => 'kwl_coupon_cat',
        'hide_empty' => $hide_empty,
        'number'     => $number ?: 0,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ] ) ?: [];

}

/**
 * Get all coupon tags.
 *
 * @param int $number
 * @return WP_Term[]
 */
function kwl_get_coupon_tags( int $number = 20 ): array {

    return get_terms( [
        'taxonomy'   => 'kwl_coupon_tag',
        'hide_empty' => true,
        'number'     => $number,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ] ) ?: [];

}

/**
 * Get the icon for a coupon category.
 *
 * @param int|WP_Term $term  Term ID or WP_Term object.
 * @return string  Emoji or default icon.
 */
function kwl_get_category_icon( int|WP_Term $term ): string {

    $term_id = is_object( $term ) ? $term->term_id : (int) $term;
    $icon    = get_term_meta( $term_id, 'kwl_cat_icon', true );
    return ! empty( $icon ) ? esc_html( $icon ) : '🏷️';

}

/**
 * Get the SEO meta description for a category.
 * Falls back to auto-generated text.
 *
 * @param WP_Term $term
 * @return string
 */
function kwl_get_category_meta_description( WP_Term $term ): string {

    $custom = get_term_meta( $term->term_id, 'kwl_cat_meta_desc', true );

    if ( ! empty( $custom ) ) {
        return esc_attr( $custom );
    }

    // Auto-generate
    return esc_attr( sprintf(
        /* translators: 1: category name, 2: site name */
        __( 'Find the best %1$s coupons, promo codes, and deals at %2$s. Updated daily.', 'kwl-coupon-wp' ),
        $term->name,
        get_bloginfo( 'name' )
    ) );

}
