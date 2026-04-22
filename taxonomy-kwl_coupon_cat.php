<?php
/**
 * KWL Coupon WP — Coupon Category Archive
 *
 * Displays coupons filtered by category.
 * Includes subcategory listing and breadcrumbs.
 *
 * @package KWL_Coupon_WP
 */

get_header();

$term         = get_queried_object();
$layout       = kwl_get_option( 'layout', 'sidebar-right' );
$is_full      = $layout === 'full-width';
$icon         = kwl_get_category_icon( $term );

global $wp_query;
$total = $wp_query->found_posts;
?>

<main id="kwl-main" role="main">
    <div class="cwp-container">

        <?php kwl_breadcrumbs(); ?>

        <!-- Category Header -->
        <header class="cwp-archive-header">
            <div style="display:flex; align-items:center; gap:var(--cwp-space-4); margin-bottom:var(--cwp-space-3);">
                <span style="font-size:2.5rem;" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
                <div>
                    <h1 class="cwp-archive-title" style="margin-bottom:0;">
                        <?php
                        printf(
                            /* translators: %s = category name */
                            esc_html__( '%s Coupons', 'kwl-coupon-wp' ),
                            $term->name
                        );
                        ?>
                        <?php if ( $total > 0 ) : ?>
                        <span class="cwp-archive-count">(<?php echo number_format( $total ); ?>)</span>
                        <?php endif; ?>
                    </h1>
                    <?php if ( $term->description ) : ?>
                    <p class="cwp-archive-description"><?php echo esc_html( $term->description ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Subcategory chips (if this category has children) -->
        <?php
        $children = get_terms( [
            'taxonomy'   => 'kwl_coupon_cat',
            'parent'     => $term->term_id,
            'hide_empty' => true,
        ] );
        ?>
        <?php if ( ! empty( $children ) && ! is_wp_error( $children ) ) : ?>
        <div class="cwp-subcat-chips">
            <?php foreach ( $children as $child ) : ?>
            <a href="<?php echo esc_url( get_term_link( $child ) ); ?>" class="cwp-badge cwp-badge--new" style="padding:6px 14px; font-size:0.875rem;">
                <?php echo esc_html( kwl_get_category_icon( $child ) ); ?>
                <?php echo esc_html( $child->name ); ?>
                <span style="opacity:.6;">(<?php echo esc_html( $child->count ); ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="cwp-layout <?php echo $is_full ? 'cwp-layout--full' : ''; ?>">

            <div class="cwp-main-content">

                <?php if ( have_posts() ) : ?>

                    <div class="cwp-grid cwp-grid--coupons">
                        <?php
                        $infeed_interval = (int) kwl_get_option( 'ad_infeed_interval', 5 );
                        $coupon_index    = 0;

                        while ( have_posts() ) :
                            the_post();
                            $coupon_index++;

                            if (
                                kwl_get_option( 'ads_enabled' ) &&
                                kwl_get_option( 'ad_infeed_code', '' ) &&
                                $coupon_index > 1 &&
                                ( $coupon_index - 1 ) % $infeed_interval === 0
                            ) :
                            ?>
                            <div class="cwp-ad-slot cwp-ad-slot--infeed">
                                <?php echo kwl_get_option( 'ad_infeed_code', '' ); // phpcs:ignore ?>
                            </div>
                            <?php endif; ?>

                            <?php kwl_render_coupon_card( get_the_ID(), true ); ?>

                        <?php endwhile; ?>
                    </div>

                    <?php kwl_render_pagination(); ?>

                <?php else : ?>

                    <div class="cwp-empty-state">
                        <p><?php printf(
                            esc_html__( 'No coupons found in the "%s" category.', 'kwl-coupon-wp' ),
                            esc_html( $term->name )
                        ); ?></p>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'kwl_coupon' ) ); ?>" class="cwp-btn cwp-btn--primary">
                            <?php esc_html_e( 'View All Coupons', 'kwl-coupon-wp' ); ?>
                        </a>
                    </div>

                <?php endif; ?>

            </div>

            <?php if ( ! $is_full ) : ?>
                <?php get_sidebar(); ?>
            <?php endif; ?>

        </div>

    </div>
</main>

<?php get_footer(); ?>
