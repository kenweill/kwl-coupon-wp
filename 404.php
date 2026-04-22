<?php
/**
 * KWL Coupon WP — 404 Not Found
 *
 * @package KWL_Coupon_WP
 */

get_header();
?>

<main id="kwl-main" role="main">
    <div class="cwp-container">
        <div class="cwp-404">

            <div class="cwp-404__content">
                <div class="cwp-404__emoji">🏷️</div>
                <h1 class="cwp-404__title"><?php esc_html_e( '404 — Page Not Found', 'kwl-coupon-wp' ); ?></h1>
                <p class="cwp-404__message">
                    <?php esc_html_e( "That page doesn't exist — but there are plenty of great deals waiting for you.", 'kwl-coupon-wp' ); ?>
                </p>

                <!-- Search -->
                <div class="cwp-404__search">
                    <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <div class="cwp-search">
                            <svg class="cwp-search__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                            </svg>
                            <input type="search" class="cwp-search__input" name="s"
                                placeholder="<?php esc_attr_e( 'Search for coupons or stores…', 'kwl-coupon-wp' ); ?>">
                            <button type="submit" class="cwp-search__btn"><?php esc_html_e( 'Search', 'kwl-coupon-wp' ); ?></button>
                        </div>
                    </form>
                </div>

                <!-- Quick links -->
                <div class="cwp-404__links">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cwp-btn cwp-btn--primary">
                        <?php esc_html_e( 'Homepage', 'kwl-coupon-wp' ); ?>
                    </a>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'kwl_store' ) ); ?>" class="cwp-btn cwp-btn--outline">
                        <?php esc_html_e( 'All Stores', 'kwl-coupon-wp' ); ?>
                    </a>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'kwl_coupon' ) ); ?>" class="cwp-btn cwp-btn--ghost">
                        <?php esc_html_e( 'All Coupons', 'kwl-coupon-wp' ); ?>
                    </a>
                </div>

            </div>

            <!-- Recent coupons -->
            <?php
            $recent = kwl_get_latest_coupons( 4, true );
            if ( $recent->have_posts() ) :
            ?>
            <div class="cwp-404__recent">
                <h2><?php esc_html_e( 'Recent Coupons', 'kwl-coupon-wp' ); ?></h2>
                <div class="cwp-grid cwp-grid--coupons">
                    <?php while ( $recent->have_posts() ) : $recent->the_post(); ?>
                        <?php kwl_render_coupon_card( get_the_ID(), true ); ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php get_footer(); ?>
