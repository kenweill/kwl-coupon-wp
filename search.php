<?php
/**
 * KWL Coupon WP — Search Results
 *
 * Shows stores and coupons matching the search query.
 * Groups results: Stores first, then Coupons.
 *
 * @package KWL_Coupon_WP
 */

get_header();

$query        = get_search_query();
$layout       = kwl_get_option( 'layout', 'sidebar-right' );
$is_full      = $layout === 'full-width';

global $wp_query;
$total = $wp_query->found_posts;

// Separate stores from coupons in results
$store_results  = [];
$coupon_results = [];

if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        $type = get_post_type();
        if ( $type === 'kwl_store' ) {
            $store_results[] = get_the_ID();
        } elseif ( $type === 'kwl_coupon' ) {
            $coupon_results[] = get_the_ID();
        }
    }
    rewind_posts();
}
?>

<main id="kwl-main" role="main">
    <div class="cwp-container">

        <!-- Search Header -->
        <header class="cwp-archive-header">
            <?php if ( $query ) : ?>
            <h1 class="cwp-archive-title">
                <?php printf(
                    /* translators: %s = search query */
                    esc_html__( 'Search results for "%s"', 'kwl-coupon-wp' ),
                    '<em>' . esc_html( $query ) . '</em>'
                ); ?>
                <?php if ( $total > 0 ) : ?>
                <span class="cwp-archive-count">(<?php echo number_format( $total ); ?>)</span>
                <?php endif; ?>
            </h1>
            <?php else : ?>
            <h1 class="cwp-archive-title"><?php esc_html_e( 'Search', 'kwl-coupon-wp' ); ?></h1>
            <?php endif; ?>
        </header>

        <!-- Search Form -->
        <div style="max-width:560px; margin-bottom:var(--cwp-space-8);">
            <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <div class="cwp-search">
                    <svg class="cwp-search__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                    </svg>
                    <input type="search" class="cwp-search__input" name="s"
                        value="<?php echo esc_attr( $query ); ?>"
                        placeholder="<?php esc_attr_e( 'Search stores, coupons…', 'kwl-coupon-wp' ); ?>">
                    <button type="submit" class="cwp-search__btn"><?php esc_html_e( 'Search', 'kwl-coupon-wp' ); ?></button>
                </div>
            </form>
        </div>

        <div class="cwp-layout <?php echo $is_full ? 'cwp-layout--full' : ''; ?>">

            <div class="cwp-main-content">

                <?php if ( $total > 0 ) : ?>

                    <!-- Store Results -->
                    <?php if ( ! empty( $store_results ) ) : ?>
                    <section class="cwp-search-group">
                        <h2 class="cwp-section-title" style="margin-bottom:var(--cwp-space-5);">
                            <?php printf(
                                _n( '%d Store', '%d Stores', count( $store_results ), 'kwl-coupon-wp' ),
                                count( $store_results )
                            ); ?>
                        </h2>
                        <div class="cwp-grid cwp-grid--stores">
                            <?php foreach ( $store_results as $store_id ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $store_id ) ); ?>" class="cwp-store-card">
                                <?php kwl_store_logo( $store_id, 'kwl-store-logo', 'cwp-store-card__logo' ); ?>
                                <span class="cwp-store-card__name"><?php echo esc_html( get_the_title( $store_id ) ); ?></span>
                                <?php
                                $count = kwl_get_store_coupon_count( $store_id );
                                if ( $count > 0 ) :
                                ?>
                                <span class="cwp-store-card__count">
                                    <?php printf( _n( '%d coupon', '%d coupons', $count, 'kwl-coupon-wp' ), $count ); ?>
                                </span>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Coupon Results -->
                    <?php if ( ! empty( $coupon_results ) ) : ?>
                    <section class="cwp-search-group" style="margin-top:var(--cwp-space-8);">
                        <h2 class="cwp-section-title" style="margin-bottom:var(--cwp-space-5);">
                            <?php printf(
                                _n( '%d Coupon', '%d Coupons', count( $coupon_results ), 'kwl-coupon-wp' ),
                                count( $coupon_results )
                            ); ?>
                        </h2>
                        <div class="cwp-grid cwp-grid--coupons">
                            <?php foreach ( $coupon_results as $coupon_id ) : ?>
                                <?php kwl_render_coupon_card( $coupon_id, true ); ?>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php kwl_render_pagination(); ?>

                <?php else : ?>

                    <div class="cwp-empty-state">
                        <?php if ( $query ) : ?>
                        <p><?php printf(
                            esc_html__( 'No results found for "%s". Try a different search term.', 'kwl-coupon-wp' ),
                            esc_html( $query )
                        ); ?></p>
                        <?php else : ?>
                        <p><?php esc_html_e( 'Enter a search term above to find coupons and stores.', 'kwl-coupon-wp' ); ?></p>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cwp-btn cwp-btn--primary">
                            <?php esc_html_e( 'Back to Homepage', 'kwl-coupon-wp' ); ?>
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
