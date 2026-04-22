<?php
/**
 * KWL Coupon WP — Coupon Tag Archive
 *
 * @package KWL_Coupon_WP
 */

get_header();

$term    = get_queried_object();
$layout  = kwl_get_option( 'layout', 'sidebar-right' );
$is_full = $layout === 'full-width';

global $wp_query;
$total = $wp_query->found_posts;
?>

<main id="kwl-main" role="main">
    <div class="cwp-container">

        <?php kwl_breadcrumbs(); ?>

        <header class="cwp-archive-header">
            <h1 class="cwp-archive-title">
                #<?php echo esc_html( $term->name ); ?>
                <?php if ( $total > 0 ) : ?>
                <span class="cwp-archive-count">(<?php echo number_format( $total ); ?>)</span>
                <?php endif; ?>
            </h1>
            <p class="cwp-archive-description">
                <?php printf(
                    esc_html__( 'Coupons tagged with "%s".', 'kwl-coupon-wp' ),
                    esc_html( $term->name )
                ); ?>
            </p>
        </header>

        <div class="cwp-layout <?php echo $is_full ? 'cwp-layout--full' : ''; ?>">

            <div class="cwp-main-content">

                <?php if ( have_posts() ) : ?>

                    <div class="cwp-grid cwp-grid--coupons">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <?php kwl_render_coupon_card( get_the_ID(), true ); ?>
                        <?php endwhile; ?>
                    </div>

                    <?php kwl_render_pagination(); ?>

                <?php else : ?>

                    <div class="cwp-empty-state">
                        <p><?php printf(
                            esc_html__( 'No coupons found tagged "%s".', 'kwl-coupon-wp' ),
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
