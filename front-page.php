<?php
/**
 * KWL Coupon WP — Homepage (front-page.php)
 *
 * Sections:
 * 1. Hero banner with search
 * 2. Featured Stores grid
 * 3. Top Categories grid
 * 4. Latest Coupons list
 * 5. Sidebar (on sidebar-right layout)
 *
 * @package KWL_Coupon_WP
 */

get_header();

$hide_expired = kwl_get_option( 'hide_expired_coupons', false );
$layout       = kwl_get_option( 'layout', 'sidebar-right' );
$is_full      = $layout === 'full-width';
?>

<main id="kwl-main" role="main">

    <!-- =========================================================
         HERO SECTION
         ========================================================= -->
    <?php if ( kwl_get_option( 'show_hero', true ) ) : ?>
    <section class="cwp-hero" aria-label="<?php esc_attr_e( 'Search Coupons', 'kwl-coupon-wp' ); ?>">
        <div class="cwp-container">

            <h1 class="cwp-hero__title">
                <?php echo esc_html( kwl_get_option( 'hero_title', __( 'Find the Best Coupon Codes', 'kwl-coupon-wp' ) ) ); ?>
            </h1>

            <p class="cwp-hero__subtitle">
                <?php echo esc_html( kwl_get_option( 'hero_subtitle', __( 'Verified promo codes. Updated daily.', 'kwl-coupon-wp' ) ) ); ?>
            </p>

            <!-- Hero Search -->
            <div class="cwp-hero__search">
                <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <div class="cwp-search">
                        <svg class="cwp-search__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                        <input
                            type="search"
                            class="cwp-search__input"
                            id="kwl-hero-search"
                            name="s"
                            placeholder="<?php esc_attr_e( 'Search for a store or coupon…', 'kwl-coupon-wp' ); ?>"
                            autocomplete="off"
                            aria-label="<?php esc_attr_e( 'Search for coupons', 'kwl-coupon-wp' ); ?>"
                        >
                        <button type="submit" class="cwp-search__btn">
                            <?php esc_html_e( 'Search', 'kwl-coupon-wp' ); ?>
                        </button>
                        <div id="kwl-hero-results" class="cwp-search-dropdown" aria-live="polite" hidden></div>
                    </div>
                </form>
            </div>

            <!-- Quick stats -->
            <?php
            $store_count  = wp_count_posts( 'kwl_store' )->publish;
            $coupon_count = wp_count_posts( 'kwl_coupon' )->publish;
            if ( $store_count > 0 || $coupon_count > 0 ) :
            ?>
            <p class="cwp-hero__stats">
                <?php
                printf(
                    /* translators: 1: coupon count, 2: store count */
                    esc_html__( '%1$s coupons from %2$s stores', 'kwl-coupon-wp' ),
                    '<strong>' . number_format( $coupon_count ) . '</strong>',
                    '<strong>' . number_format( $store_count ) . '</strong>'
                );
                ?>
            </p>
            <?php endif; ?>

        </div>
    </section>
    <?php endif; ?>


    <div class="cwp-container">
        <div class="cwp-layout <?php echo $is_full ? 'cwp-layout--full' : ''; ?>">

            <!-- Main Content -->
            <div class="cwp-main-content">

                <!-- =======================================================
                     FEATURED STORES
                     ======================================================= -->
                <?php
                $show_stores = kwl_get_option( 'show_featured_stores', true );
                $stores_count = (int) kwl_get_option( 'featured_stores_count', 12 );
                $featured_stores = $show_stores ? kwl_get_featured_stores( $stores_count ) : [];

                // Fall back to all stores if no featured ones set
                if ( $show_stores && empty( $featured_stores ) ) {
                    $featured_stores = get_posts( [
                        'post_type'      => 'kwl_store',
                        'post_status'    => 'publish',
                        'posts_per_page' => $stores_count,
                        'orderby'        => 'title',
                        'order'          => 'ASC',
                    ] );
                }
                ?>

                <?php if ( $show_stores && ! empty( $featured_stores ) ) : ?>
                <section class="cwp-section cwp-section--sm">

                    <div class="cwp-section-header">
                        <h2 class="cwp-section-title"><?php esc_html_e( 'Featured Stores', 'kwl-coupon-wp' ); ?></h2>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'kwl_store' ) ); ?>" class="cwp-section-link">
                            <?php esc_html_e( 'View All Stores', 'kwl-coupon-wp' ); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>

                    <div class="cwp-grid cwp-grid--stores">
                        <?php foreach ( $featured_stores as $store ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $store->ID ) ); ?>" class="cwp-store-card">

                            <?php kwl_store_logo( $store->ID, 'kwl-store-logo', 'cwp-store-card__logo' ); ?>

                            <span class="cwp-store-card__name"><?php echo esc_html( $store->post_title ); ?></span>

                            <?php
                            $count = kwl_get_store_coupon_count( $store->ID );
                            if ( $count > 0 ) :
                            ?>
                            <span class="cwp-store-card__count">
                                <?php
                                printf(
                                    _n( '%d coupon', '%d coupons', $count, 'kwl-coupon-wp' ),
                                    $count
                                );
                                ?>
                            </span>
                            <?php endif; ?>

                        </a>
                        <?php endforeach; ?>
                    </div>

                </section>
                <?php endif; ?>


                <!-- =======================================================
                     TOP CATEGORIES
                     ======================================================= -->
                <?php
                $show_cats  = kwl_get_option( 'show_categories_home', true );
                $cats_count = (int) kwl_get_option( 'home_categories_count', 8 );
                $home_cats  = $show_cats ? kwl_get_coupon_categories( $cats_count ) : [];
                ?>

                <?php if ( $show_cats && ! empty( $home_cats ) ) : ?>
                <section class="cwp-section cwp-section--sm">

                    <div class="cwp-section-header">
                        <h2 class="cwp-section-title"><?php esc_html_e( 'Browse by Category', 'kwl-coupon-wp' ); ?></h2>
                    </div>

                    <div class="cwp-grid cwp-grid--<?php echo $is_full ? '4' : '2'; ?>">
                        <?php foreach ( $home_cats as $cat ) : ?>
                        <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="cwp-category-card">

                            <div class="cwp-category-card__icon">
                                <?php echo esc_html( kwl_get_category_icon( $cat ) ); ?>
                            </div>

                            <div>
                                <div class="cwp-category-card__name"><?php echo esc_html( $cat->name ); ?></div>
                                <div class="cwp-category-card__count">
                                    <?php
                                    printf(
                                        _n( '%d coupon', '%d coupons', $cat->count, 'kwl-coupon-wp' ),
                                        $cat->count
                                    );
                                    ?>
                                </div>
                            </div>

                        </a>
                        <?php endforeach; ?>
                    </div>

                </section>
                <?php endif; ?>


                <!-- =======================================================
                     LATEST COUPONS
                     ======================================================= -->
                <?php
                $coupons_count = (int) kwl_get_option( 'home_coupons_count', 10 );
                $latest_query  = kwl_get_latest_coupons( $coupons_count, $hide_expired );
                ?>

                <?php if ( $latest_query->have_posts() ) : ?>
                <section class="cwp-section cwp-section--sm">

                    <div class="cwp-section-header">
                        <h2 class="cwp-section-title"><?php esc_html_e( 'Latest Coupons', 'kwl-coupon-wp' ); ?></h2>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'kwl_coupon' ) ); ?>" class="cwp-section-link">
                            <?php esc_html_e( 'View All Coupons', 'kwl-coupon-wp' ); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>

                    <div class="cwp-grid cwp-grid--coupons">
                        <?php
                        $infeed_interval = (int) kwl_get_option( 'ad_infeed_interval', 5 );
                        $coupon_index    = 0;

                        while ( $latest_query->have_posts() ) :
                            $latest_query->the_post();
                            $coupon_index++;

                            // In-feed ad slot
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
                        <?php wp_reset_postdata(); ?>
                    </div>

                </section>
                <?php endif; ?>


            </div><!-- /.cwp-main-content -->

            <!-- Sidebar -->
            <?php if ( ! $is_full ) : ?>
                <?php get_sidebar(); ?>
            <?php endif; ?>

        </div><!-- /.cwp-layout -->
    </div><!-- /.cwp-container -->

</main>

<?php get_footer(); ?>
