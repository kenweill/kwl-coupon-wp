<?php
/**
 * KWL Coupon WP — Single Store Page
 *
 * Displays:
 * - Store header (logo, name, description, stats, visit link)
 * - Filter tabs (All, Codes, Deals, Free Shipping)
 * - All coupons for this store
 * - FAQ schema (via schema.php)
 * - Sidebar
 *
 * @package KWL_Coupon_WP
 */

get_header();

$store_id     = get_the_ID();
$layout       = kwl_get_option( 'layout', 'sidebar-right' );
$is_full      = $layout === 'full-width';
$hide_expired = kwl_get_option( 'hide_expired_coupons', false );

// Store data
$store_name     = get_the_title();
$store_website  = get_post_meta( $store_id, '_kwl_store_website', true );
$affiliate_url  = kwl_get_store_affiliate_url( $store_id );
$go_url         = kwl_get_store_go_url( $store_id );
$logo_url       = kwl_get_store_logo_url( $store_id, 'kwl-store-logo' );
$coupon_count   = kwl_get_store_coupon_count( $store_id );
$click_count    = (int) get_post_meta( $store_id, '_kwl_store_click_count', true );

// Get coupons
$coupons_query  = kwl_get_coupons_by_store( $store_id, $hide_expired );

// Output FAQ schema for this store
if ( function_exists( 'kwl_schema_store_faq' ) ) {
    $faq_schema = kwl_schema_store_faq( $store_id );
    if ( $faq_schema ) {
        add_action( 'wp_head', function() use ( $faq_schema ) {
            echo '<script type="application/ld+json">' . wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }, 6 );
    }
}
?>

<main id="kwl-main" role="main">
    <div class="cwp-container">

        <!-- Breadcrumbs -->
        <?php kwl_breadcrumbs(); ?>

        <div class="cwp-layout <?php echo $is_full ? 'cwp-layout--full' : ''; ?>">

            <div class="cwp-main-content">

                <!-- =======================================================
                     STORE HEADER
                     ======================================================= -->
                <div class="cwp-store-header">

                    <!-- Store Logo -->
                    <?php if ( $logo_url ) : ?>
                    <img
                        src="<?php echo esc_url( $logo_url ); ?>"
                        alt="<?php echo esc_attr( $store_name ); ?>"
                        class="cwp-store-header__logo"
                        width="88"
                        height="88"
                        loading="eager"
                    >
                    <?php else : ?>
                    <div class="cwp-store-header__logo-placeholder" style="
                        width:88px; height:88px; border-radius:12px;
                        background:var(--cwp-primary-light);
                        display:flex; align-items:center; justify-content:center;
                        font-size:2rem; font-weight:800; color:var(--cwp-primary);
                        flex-shrink:0;
                    ">
                        <?php echo esc_html( mb_strtoupper( mb_substr( $store_name, 0, 1 ) ) ); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Store Info -->
                    <div class="cwp-store-header__info">

                        <h1 class="cwp-store-header__name">
                            <?php
                            printf(
                                /* translators: %s = store name */
                                esc_html__( '%s Coupons & Promo Codes', 'kwl-coupon-wp' ),
                                $store_name
                            );
                            ?>
                        </h1>

                        <?php
                        $description = get_the_excerpt() ?: get_post_field( 'post_content', $store_id );
                        $description = wp_trim_words( wp_strip_all_tags( $description ), 25, '…' );
                        if ( $description ) :
                        ?>
                        <p class="cwp-store-header__description"><?php echo esc_html( $description ); ?></p>
                        <?php endif; ?>

                        <!-- Store Stats -->
                        <div class="cwp-store-header__stats">
                            <div class="cwp-store-header__stat">
                                <strong><?php echo number_format( $coupon_count ); ?></strong>
                                <?php esc_html_e( 'Active Coupons', 'kwl-coupon-wp' ); ?>
                            </div>
                            <?php if ( $click_count > 0 ) : ?>
                            <div class="cwp-store-header__stat">
                                <strong><?php echo number_format( $click_count ); ?></strong>
                                <?php esc_html_e( 'Uses', 'kwl-coupon-wp' ); ?>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- Visit Store Button -->
                    <?php if ( $go_url ) : ?>
                    <div class="cwp-store-header__action">
                        <a
                            href="<?php echo esc_url( $go_url ); ?>"
                            class="cwp-btn cwp-btn--primary"
                            target="_blank"
                            rel="nofollow noopener sponsored"
                            data-store-id="<?php echo esc_attr( $store_id ); ?>"
                        >
                            <?php
                            printf(
                                /* translators: %s = store name */
                                esc_html__( 'Visit %s', 'kwl-coupon-wp' ),
                                $store_name
                            );
                            ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="margin-left:4px;">
                                <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5z" clip-rule="evenodd"/>
                                <path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 001.06.053L16.5 4.44v2.81a.75.75 0 001.5 0v-4.5a.75.75 0 00-.75-.75h-4.5a.75.75 0 000 1.5h2.553l-9.056 8.194a.75.75 0 00-.053 1.06z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>
                    <?php endif; ?>

                </div><!-- /.cwp-store-header -->


                <!-- =======================================================
                     COUPON TYPE FILTER TABS
                     ======================================================= -->
                <?php if ( $coupons_query->found_posts > 0 ) : ?>
                <div class="cwp-filter-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Filter coupons by type', 'kwl-coupon-wp' ); ?>">
                    <button class="cwp-filter-tab active" data-filter="all" role="tab" aria-selected="true">
                        <?php
                        printf(
                            esc_html__( 'All (%d)', 'kwl-coupon-wp' ),
                            $coupons_query->found_posts
                        );
                        ?>
                    </button>
                    <button class="cwp-filter-tab" data-filter="code" role="tab" aria-selected="false">
                        <?php esc_html_e( 'Codes', 'kwl-coupon-wp' ); ?>
                    </button>
                    <button class="cwp-filter-tab" data-filter="deal" role="tab" aria-selected="false">
                        <?php esc_html_e( 'Deals', 'kwl-coupon-wp' ); ?>
                    </button>
                    <button class="cwp-filter-tab" data-filter="freeshipping" role="tab" aria-selected="false">
                        <?php esc_html_e( 'Free Shipping', 'kwl-coupon-wp' ); ?>
                    </button>
                </div>
                <?php endif; ?>


                <!-- =======================================================
                     COUPONS LIST
                     ======================================================= -->
                <?php if ( $coupons_query->have_posts() ) : ?>

                <div class="cwp-grid cwp-grid--coupons" id="kwl-store-coupons">
                    <?php
                    $infeed_interval = (int) kwl_get_option( 'ad_infeed_interval', 5 );
                    $coupon_index    = 0;

                    while ( $coupons_query->have_posts() ) :
                        $coupons_query->the_post();
                        $coupon_id = get_the_ID();
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

                        <?php
                        // On store page, don't show store logo column (redundant)
                        kwl_render_coupon_card( $coupon_id, false );
                        ?>

                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>

                <?php else : ?>

                <div class="cwp-empty-state">
                    <p><?php
                        printf(
                            /* translators: %s = store name */
                            esc_html__( 'No active coupons found for %s right now. Check back soon!', 'kwl-coupon-wp' ),
                            '<strong>' . esc_html( $store_name ) . '</strong>'
                        );
                    ?></p>
                    <?php if ( $go_url ) : ?>
                    <a href="<?php echo esc_url( $go_url ); ?>" class="cwp-btn cwp-btn--primary" target="_blank" rel="nofollow noopener sponsored">
                        <?php printf( esc_html__( 'Visit %s Anyway', 'kwl-coupon-wp' ), esc_html( $store_name ) ); ?>
                    </a>
                    <?php endif; ?>
                </div>

                <?php endif; ?>


                <!-- =======================================================
                     STORE DESCRIPTION (full content)
                     ======================================================= -->
                <?php
                $full_content = get_post_field( 'post_content', $store_id );
                if ( ! empty( trim( $full_content ) ) ) :
                ?>
                <div class="cwp-store-description cwp-prose">
                    <?php echo wp_kses_post( apply_filters( 'the_content', $full_content ) ); ?>
                </div>
                <?php endif; ?>


                <!-- =======================================================
                     FAQ SECTION (from schema data)
                     ======================================================= -->
                <?php if ( $coupon_count > 0 ) : ?>
                <div class="cwp-store-faq">
                    <h2><?php printf( esc_html__( '%s Coupon FAQs', 'kwl-coupon-wp' ), esc_html( $store_name ) ); ?></h2>

                    <details class="cwp-faq-item">
                        <summary class="cwp-faq-question">
                            <?php printf( esc_html__( 'Does %s offer coupon codes?', 'kwl-coupon-wp' ), esc_html( $store_name ) ); ?>
                        </summary>
                        <p class="cwp-faq-answer">
                            <?php printf(
                                _n(
                                    'Yes. %1$s currently has %2$d verified coupon available.',
                                    'Yes. %1$s currently has %2$d verified coupons available.',
                                    $coupon_count,
                                    'kwl-coupon-wp'
                                ),
                                esc_html( $store_name ),
                                $coupon_count
                            ); ?>
                        </p>
                    </details>

                    <details class="cwp-faq-item">
                        <summary class="cwp-faq-question">
                            <?php printf( esc_html__( 'How do I use a %s coupon code?', 'kwl-coupon-wp' ), esc_html( $store_name ) ); ?>
                        </summary>
                        <p class="cwp-faq-answer">
                            <?php printf(
                                esc_html__( 'Click "Show Code" to reveal the coupon code. Copy it and paste it into the promo code field at checkout on %s\'s website.', 'kwl-coupon-wp' ),
                                esc_html( $store_name )
                            ); ?>
                        </p>
                    </details>
                </div>
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
