<?php
/**
 * KWL Coupon WP — Store Archive (/stores/)
 *
 * Lists all published stores with logo, name, coupon count.
 * Includes alphabetical filter and search.
 *
 * @package KWL_Coupon_WP
 */

get_header();

$layout  = kwl_get_option( 'layout', 'sidebar-right' );
$is_full = $layout === 'full-width';
$paged   = max( 1, get_query_var( 'paged' ) );

// Get stores — use the main query (WordPress handles pagination)
global $wp_query;
$total_stores = $wp_query->found_posts;

// Current letter filter
$letter = isset( $_GET['letter'] ) ? strtoupper( sanitize_text_field( $_GET['letter'] ) ) : '';
?>

<main id="kwl-main" role="main">
    <div class="cwp-container">

        <!-- Breadcrumbs -->
        <?php kwl_breadcrumbs(); ?>

        <!-- Archive Header -->
        <header class="cwp-archive-header">
            <h1 class="cwp-archive-title">
                <?php esc_html_e( 'All Stores', 'kwl-coupon-wp' ); ?>
                <?php if ( $total_stores > 0 ) : ?>
                <span class="cwp-archive-count">(<?php echo number_format( $total_stores ); ?>)</span>
                <?php endif; ?>
            </h1>
            <p class="cwp-archive-description">
                <?php esc_html_e( 'Browse all stores with verified coupon codes and deals.', 'kwl-coupon-wp' ); ?>
            </p>
        </header>

        <!-- A–Z Filter -->
        <div class="cwp-az-filter">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'kwl_store' ) ); ?>"
               class="cwp-az-filter__item <?php echo empty( $letter ) ? 'active' : ''; ?>">
                <?php esc_html_e( 'All', 'kwl-coupon-wp' ); ?>
            </a>
            <?php
            foreach ( range( 'A', 'Z' ) as $l ) {
                $url = add_query_arg( 'letter', $l, get_post_type_archive_link( 'kwl_store' ) );
                printf(
                    '<a href="%s" class="cwp-az-filter__item %s">%s</a>',
                    esc_url( $url ),
                    $letter === $l ? 'active' : '',
                    esc_html( $l )
                );
            }
            // Numeric
            $url = add_query_arg( 'letter', '0-9', get_post_type_archive_link( 'kwl_store' ) );
            printf(
                '<a href="%s" class="cwp-az-filter__item %s">0–9</a>',
                esc_url( $url ),
                $letter === '0-9' ? 'active' : ''
            );
            ?>
        </div>

        <div class="cwp-layout <?php echo $is_full ? 'cwp-layout--full' : ''; ?>">

            <div class="cwp-main-content">

                <?php if ( have_posts() ) : ?>

                    <div class="cwp-grid cwp-grid--stores">
                        <?php while ( have_posts() ) : the_post(); ?>

                        <?php
                        $store_id    = get_the_ID();
                        $coupon_count = kwl_get_store_coupon_count( $store_id );
                        $store_url   = get_permalink();
                        ?>

                        <a href="<?php echo esc_url( $store_url ); ?>" class="cwp-store-card">

                            <?php kwl_store_logo( $store_id, 'kwl-store-logo', 'cwp-store-card__logo' ); ?>

                            <span class="cwp-store-card__name"><?php the_title(); ?></span>

                            <span class="cwp-store-card__count">
                                <?php
                                if ( $coupon_count > 0 ) {
                                    printf(
                                        _n( '%d coupon', '%d coupons', $coupon_count, 'kwl-coupon-wp' ),
                                        $coupon_count
                                    );
                                } else {
                                    esc_html_e( 'No coupons', 'kwl-coupon-wp' );
                                }
                                ?>
                            </span>

                        </a>

                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php kwl_render_pagination(); ?>

                <?php else : ?>

                    <div class="cwp-empty-state">
                        <?php if ( $letter ) : ?>
                            <p><?php printf(
                                esc_html__( 'No stores found starting with "%s".', 'kwl-coupon-wp' ),
                                esc_html( $letter )
                            ); ?></p>
                            <a href="<?php echo esc_url( get_post_type_archive_link( 'kwl_store' ) ); ?>" class="cwp-btn cwp-btn--primary">
                                <?php esc_html_e( 'View All Stores', 'kwl-coupon-wp' ); ?>
                            </a>
                        <?php else : ?>
                            <p><?php esc_html_e( 'No stores found.', 'kwl-coupon-wp' ); ?></p>
                        <?php endif; ?>
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
