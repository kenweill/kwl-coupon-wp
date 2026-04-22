<?php
/**
 * KWL Coupon WP — Coupon Archive (/coupons/)
 *
 * Lists all active coupons with:
 * - Category filter sidebar/tabs
 * - Type filter (All / Codes / Deals / Free Shipping)
 * - Pagination
 *
 * @package KWL_Coupon_WP
 */

get_header();

$layout       = kwl_get_option( 'layout', 'sidebar-right' );
$is_full      = $layout === 'full-width';
$hide_expired = kwl_get_option( 'hide_expired_coupons', false );
$paged        = max( 1, get_query_var( 'paged' ) );
$active_type  = sanitize_key( $_GET['type'] ?? '' );
$active_cat   = absint( $_GET['cat'] ?? 0 );

// Total coupons in archive
global $wp_query;
$total = $wp_query->found_posts;
?>

<main id="kwl-main" role="main">
    <div class="cwp-container">

        <?php kwl_breadcrumbs(); ?>

        <!-- Archive Header -->
        <header class="cwp-archive-header">
            <h1 class="cwp-archive-title">
                <?php esc_html_e( 'Latest Coupon Codes & Deals', 'kwl-coupon-wp' ); ?>
                <?php if ( $total > 0 ) : ?>
                <span class="cwp-archive-count">(<?php echo number_format( $total ); ?>)</span>
                <?php endif; ?>
            </h1>
            <p class="cwp-archive-description">
                <?php esc_html_e( 'Verified coupon codes and deals, updated daily.', 'kwl-coupon-wp' ); ?>
            </p>
        </header>

        <!-- Type Filter Tabs -->
        <div class="cwp-filter-tabs" role="tablist">
            <?php
            $type_filters = [
                ''             => __( 'All Coupons',   'kwl-coupon-wp' ),
                'code'         => __( 'Coupon Codes',  'kwl-coupon-wp' ),
                'deal'         => __( 'Deals',         'kwl-coupon-wp' ),
                'freeshipping' => __( 'Free Shipping', 'kwl-coupon-wp' ),
            ];
            foreach ( $type_filters as $type_val => $type_label ) {
                $url = $type_val
                    ? add_query_arg( 'type', $type_val, get_post_type_archive_link( 'kwl_coupon' ) )
                    : get_post_type_archive_link( 'kwl_coupon' );
                printf(
                    '<a href="%s" class="cwp-filter-tab %s" role="tab" aria-selected="%s">%s</a>',
                    esc_url( $url ),
                    $active_type === $type_val ? 'active' : '',
                    $active_type === $type_val ? 'true' : 'false',
                    esc_html( $type_label )
                );
            }
            ?>
        </div>

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

                            // In-feed ad
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
                        <p><?php esc_html_e( 'No coupons found.', 'kwl-coupon-wp' ); ?></p>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'kwl_coupon' ) ); ?>" class="cwp-btn cwp-btn--primary">
                            <?php esc_html_e( 'View All Coupons', 'kwl-coupon-wp' ); ?>
                        </a>
                    </div>

                <?php endif; ?>

            </div><!-- /.cwp-main-content -->

            <?php if ( ! $is_full ) : ?>
                <?php get_sidebar(); ?>
            <?php endif; ?>

        </div><!-- /.cwp-layout -->

    </div><!-- /.cwp-container -->
</main>

<?php get_footer(); ?>
