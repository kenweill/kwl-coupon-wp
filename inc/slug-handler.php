<?php
/**
 * KWL Coupon WP — Slug Handler
 *
 * Handles smart slug generation for stores and coupons.
 *
 * Rules:
 * - Dots are PRESERVED (shopee.ph stays shopee.ph)
 * - Allows TLD distinction: shopee.ph vs shopee.my vs shopee.sg
 * - Uppercase → lowercase
 * - Spaces → hyphens
 * - Special characters stripped
 * - Consecutive dots/hyphens collapsed
 * - Leading/trailing dots and hyphens removed
 * - Duplicate slugs get numeric suffix: shopee → shopee-2 → shopee-3
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/**
 * Sanitize a raw slug input.
 *
 * Preserves dots for TLD-style slugs (shopee.ph, namecheap.com).
 * Converts everything else to lowercase-with-hyphens.
 *
 * @param  string $raw  Raw input from user.
 * @return string       Clean, sanitized slug.
 */
function kwl_sanitize_slug( string $raw ): string {

    if ( empty( $raw ) ) {
        return '';
    }

    $slug = $raw;

    // Lowercase everything
    $slug = mb_strtolower( $slug, 'UTF-8' );

    // Replace spaces and underscores with hyphens
    $slug = preg_replace( '/[\s_]+/', '-', $slug );

    // Remove characters that are NOT: a-z, 0-9, hyphens, dots
    $slug = preg_replace( '/[^a-z0-9\-\.]/', '', $slug );

    // Collapse consecutive hyphens: shop--ee → shop-ee
    $slug = preg_replace( '/-{2,}/', '-', $slug );

    // Collapse consecutive dots: shop..ee → shop.ee
    $slug = preg_replace( '/\.{2,}/', '.', $slug );

    // Remove leading dots and hyphens
    $slug = ltrim( $slug, '-.' );

    // Remove trailing dots and hyphens
    $slug = rtrim( $slug, '-.' );

    return $slug;

}


/**
 * Auto-generate a slug from a store/coupon name.
 *
 * Used when the slug field is left blank.
 * Delegates to kwl_sanitize_slug() after basic cleanup.
 *
 * @param  string $name  Store or coupon title.
 * @return string        Generated slug.
 */
function kwl_generate_slug_from_name( string $name ): string {

    if ( empty( $name ) ) {
        return '';
    }

    // If name looks like a domain (contains a dot), preserve as-is after sanitize
    // e.g. "Shopee.PH" → "shopee.ph"
    // e.g. "Namecheap.com" → "namecheap.com"
    return kwl_sanitize_slug( $name );

}


/**
 * Check if a slug is already in use by another post of the same type.
 *
 * @param  string $slug       The slug to check.
 * @param  string $post_type  Post type to check against (kwl_store or kwl_coupon).
 * @param  int    $exclude_id Post ID to exclude (the post being edited). 0 for new posts.
 * @return bool               True if slug is available, false if taken.
 */
function kwl_is_slug_available( string $slug, string $post_type, int $exclude_id = 0 ): bool {

    if ( empty( $slug ) ) {
        return false;
    }

    global $wpdb;

    // Check post_name (WordPress native slug) in posts table
    $query = $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_name = %s
         AND post_type = %s
         AND post_status NOT IN ('trash', 'auto-draft')
         AND ID != %d
         LIMIT 1",
        $slug,
        $post_type,
        $exclude_id
    );

    $result = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    return empty( $result );

}


/**
 * Generate a unique slug by appending a numeric suffix if needed.
 *
 * Examples:
 *   shopee        → shopee        (if available)
 *   shopee        → shopee-2      (if shopee is taken)
 *   shopee-2      → shopee-3      (if shopee-2 is taken)
 *   namecheap.com → namecheap.com (if available)
 *   namecheap.com → namecheap.com-2 (if taken)
 *
 * @param  string $slug       Desired slug.
 * @param  string $post_type  Post type context.
 * @param  int    $exclude_id Post ID to exclude (0 for new posts).
 * @return string             A guaranteed-unique slug.
 */
function kwl_unique_slug( string $slug, string $post_type, int $exclude_id = 0 ): string {

    if ( empty( $slug ) ) {
        return '';
    }

    // If the slug is already available, return it unchanged
    if ( kwl_is_slug_available( $slug, $post_type, $exclude_id ) ) {
        return $slug;
    }

    // Find a unique slug by incrementing suffix
    $counter    = 2;
    $base_slug  = $slug;

    while ( true ) {
        $candidate = $base_slug . '-' . $counter;

        if ( kwl_is_slug_available( $candidate, $post_type, $exclude_id ) ) {
            return $candidate;
        }

        $counter++;

        // Safety valve — prevent infinite loop (shouldn't ever reach this)
        if ( $counter > 999 ) {
            return $base_slug . '-' . time();
        }
    }

}


/**
 * Get suggested alternative slugs when a slug is taken.
 *
 * Used in the admin slug checker to show helpful alternatives.
 *
 * @param  string $slug       The taken slug.
 * @param  string $post_type  Post type context.
 * @param  int    $exclude_id Post ID to exclude.
 * @return string[]           Array of available alternatives (up to 3).
 */
function kwl_slug_suggestions( string $slug, string $post_type, int $exclude_id = 0 ): array {

    $suggestions = [];

    // Option 1: numeric suffix (shopee-2)
    $with_number = kwl_unique_slug( $slug, $post_type, $exclude_id );
    if ( $with_number !== $slug ) {
        $suggestions[] = $with_number;
    }

    // Option 2: if slug doesn't contain a dot, try with common suffixes
    if ( strpos( $slug, '.' ) === false ) {

        // Try with -store suffix
        $with_store = kwl_sanitize_slug( $slug . '-store' );
        if ( kwl_is_slug_available( $with_store, $post_type, $exclude_id ) ) {
            $suggestions[] = $with_store;
        }

        // Try with -official suffix
        $with_official = kwl_sanitize_slug( $slug . '-official' );
        if ( kwl_is_slug_available( $with_official, $post_type, $exclude_id ) ) {
            $suggestions[] = $with_official;
        }
    }

    // Return up to 3 unique suggestions
    return array_unique( array_slice( $suggestions, 0, 3 ) );

}


/**
 * Auto-generate a slug for a store post if none is set.
 *
 * Hooked to wp_insert_post_data to ensure every store
 * gets a proper slug before saving, even if admin left it blank.
 *
 * @param  array $data     Post data being saved.
 * @param  array $postarr  Raw POST data.
 * @return array           Modified post data.
 */
function kwl_auto_slug_on_save( array $data, array $postarr ): array {

    // Only for our CPTs
    if ( ! in_array( $data['post_type'], [ 'kwl_store', 'kwl_coupon' ], true ) ) {
        return $data;
    }

    // Only when post is being published or updated (not autosave/draft without title)
    if ( empty( $data['post_title'] ) ) {
        return $data;
    }

    // If post_name is empty or looks like a draft placeholder, generate one
    if ( empty( $data['post_name'] ) || $data['post_name'] === sanitize_title( __( 'Auto Draft', 'kwl-coupon-wp' ) ) ) {

        $post_id    = $postarr['ID'] ?? 0;
        $raw_slug   = kwl_generate_slug_from_name( $data['post_title'] );
        $clean_slug = kwl_sanitize_slug( $raw_slug );

        if ( ! empty( $clean_slug ) ) {
            $data['post_name'] = kwl_unique_slug( $clean_slug, $data['post_type'], (int) $post_id );
        }
    }

    return $data;

}
add_filter( 'wp_insert_post_data', 'kwl_auto_slug_on_save', 10, 2 );


/**
 * Validate slug format before saving.
 *
 * Returns true if slug is valid, WP_Error if not.
 *
 * @param  string $slug
 * @return true|WP_Error
 */
function kwl_validate_slug( string $slug ): true|WP_Error {

    if ( empty( $slug ) ) {
        return new WP_Error( 'empty_slug', __( 'Slug cannot be empty.', 'kwl-coupon-wp' ) );
    }

    if ( strlen( $slug ) < 2 ) {
        return new WP_Error( 'slug_too_short', __( 'Slug must be at least 2 characters.', 'kwl-coupon-wp' ) );
    }

    if ( strlen( $slug ) > 200 ) {
        return new WP_Error( 'slug_too_long', __( 'Slug must be 200 characters or less.', 'kwl-coupon-wp' ) );
    }

    // Must match our allowed pattern: a-z, 0-9, hyphens, dots
    if ( ! preg_match( '/^[a-z0-9][a-z0-9\-\.]*[a-z0-9]$/', $slug ) && ! preg_match( '/^[a-z0-9]$/', $slug ) ) {
        return new WP_Error(
            'invalid_slug',
            __( 'Slug may only contain lowercase letters, numbers, hyphens, and dots. Must start and end with a letter or number.', 'kwl-coupon-wp' )
        );
    }

    return true;

}
