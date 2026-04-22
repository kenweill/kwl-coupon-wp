<?php
/**
 * KWL Coupon WP — Single Coupon Page
 *
 * Displays:
 * - Coupon hero card (large reveal button, code, store info)
 * - Coupon details (discount, type, expiry, categories)
 * - Voting (works / doesn't work)
 * - Store info box
 * - More coupons from same store
 * - Sidebar
 *
 * @package KWL_Coupon_WP
 */

get_header();

$coupon_id    = get_the_ID();
$layout       = kwl_get_option( 'layout', 'sidebar-right' );
$is_full      = $layout === 'full-width';

// Coupon data
$type         = kwl_get_coupon_type( $coupon_id );
$code         = kwl_get_coupon_code( $coupon_id );
$discount     = kwl_get_coupon_discount( $coupon_id );
$expiry       = get_post_meta( $coupon_id, '_kwl_coupon_expiry', true );
$is_expired   = kwl_is_expired( $coupon_id );
$is_verified  = kwl_is_coupon_verified( $coupon_id );
$is_exclusive = kwl_is_coupon_exclusive( $coupon_id );
$affiliate    = kwl_get_coupon_affiliate_url( $coupon_id );
$votes        = kwl_get_coupon_votes( $coupon_id );
$expiry_label = kwl_get_expiry_label( $coupon_id );
$expiry_class = kwl_get_expiry_class( $coupon_id );

// Store data
$store_id     = kwl_get_coupon_store_id( $coupon_id );
$store_name   = $store_id ? get_the_title( $store_id ) : '';
$store_url    = $store_id ? get_permalink( $store_id ) : '';
$store_go_url = $store_id ? kwl_get_store_go_url( $store_id ) : '';
$store_logo   = $store_id ? kwl_get_store_logo_url( $store_id, 'kwl-store-logo' ) : '';

// Categories
$categories   = get_the_terms( $coupon_id, 'kwl_coupon_cat' );
$tags         = get_the_terms( $coupon_id, 'kwl_coupon_tag' );

// Increment click count on page load (tracks views, not just reveals)
// Actual click count incremented by AJAX on reveal
?>

<main id="kwl-main" role="main">
    <div class="cwp-container">

        <?php kwl_breadcrumbs(); ?>

        <div class="cwp-layout <?php echo $is_full ? 'cwp-layout--full' : ''; ?>">

            <div class="cwp-main-content">

                <!-- =========================================================
                     COUPON HERO CARD
                     ========================================================= -->
                <div class="cwp-coupon-single <?php echo $is_expired ? 'cwp-coupon-single--expired' : ''; ?>">

                    <!-- Store Logo + Name -->
                    <?php if ( $store_id ) : ?>
                    <div class="cwp-coupon-single__store">
                        <?php if ( $store_logo ) : ?>
                        <a href="<?php echo esc_url( $store_url ); ?>">
                            <img
                                src="<?php echo esc_url( $store_logo ); ?>"
                                alt="<?php echo esc_attr( $store_name ); ?>"
                                class="cwp-coupon-single__store-logo"
                                width="64"
                                height="64"
                                loading="eager"
                            >
                        </a>
                        <?php endif; ?>
                        <div class="cwp-coupon-single__store-info">
                            <a href="<?php echo esc_url( $store_url ); ?>" class="cwp-coupon-single__store-name">
                                <?php echo esc_html( $store_name ); ?>
                            </a>
                            <span class="cwp-coupon-single__store-label">
                                <?php esc_html_e( 'Coupon Code', 'kwl-coupon-wp' ); ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Badges -->
                    <div class="cwp-coupon-single__badges">
                        <?php kwl_render_coupon_badges( $coupon_id ); ?>
                    </div>

                    <!-- Discount + Title -->
                    <?php if ( $discount ) : ?>
                    <div class="cwp-coupon-single__discount"><?php echo esc_html( $discount ); ?></div>
                    <?php endif; ?>

                    <h1 class="cwp-coupon-single__title"><?php the_title(); ?></h1>

                    <?php if ( has_excerpt() ) : ?>
                    <p class="cwp-coupon-single__description"><?php the_excerpt(); ?></p>
                    <?php endif; ?>

                    <!-- =====================================================
                         REVEAL / ACTION SECTION
                         ===================================================== -->
                    <?php if ( ! $is_expired ) : ?>

                        <?php if ( $type === 'code' ) : ?>
                        <!-- Code reveal -->
                        <div class="cwp-coupon-single__reveal" id="kwl-coupon-reveal">

                            <!-- Blurred placeholder (shown before reveal) -->
                            <div class="cwp-code-preview" id="kwl-code-preview" data-reveal-behavior="<?php echo esc_attr( kwl_get_option( 'reveal_behavior', 'blur' ) ); ?>">
                                <span class="cwp-code-preview__blur"><?php
                                    // Show blurred/masked code text
                                    $mask = str_repeat( '•', max( 6, strlen( $code ) ) );
                                    echo esc_html( $mask );
                                ?></span>
                            </div>

                            <!-- Reveal button -->
                            <button
                                class="cwp-btn-reveal cwp-btn-reveal--large"
                                id="kwl-reveal-btn"
                                data-coupon-id="<?php echo esc_attr( $coupon_id ); ?>"
                                data-affiliate="<?php echo esc_attr( $affiliate ); ?>"
                                data-action="reveal"
                                aria-label="<?php esc_attr_e( 'Show coupon code', 'kwl-coupon-wp' ); ?>"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <?php esc_html_e( 'Show Coupon Code', 'kwl-coupon-wp' ); ?>
                            </button>

                            <!-- Revealed state (shown after click, via JS) -->
                            <div class="cwp-code-revealed" id="kwl-code-revealed" hidden>
                                <div class="cwp-code-box">
                                    <span class="cwp-code-box__code" id="kwl-code-text"></span>
                                    <button class="cwp-code-box__copy" id="kwl-copy-btn" aria-label="<?php esc_attr_e( 'Copy code', 'kwl-coupon-wp' ); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M7 3.5A1.5 1.5 0 018.5 2h3.879a1.5 1.5 0 011.06.44l3.122 3.12A1.5 1.5 0 0117 6.622V12.5a1.5 1.5 0 01-1.5 1.5h-1v-3.379a3 3 0 00-.879-2.121L10.5 5.379A3 3 0 008.379 4.5H7v-1z"/>
                                            <path d="M4.5 6A1.5 1.5 0 003 7.5v9A1.5 1.5 0 004.5 18h7a1.5 1.5 0 001.5-1.5v-5.879a1.5 1.5 0 00-.44-1.06L9.44 6.439A1.5 1.5 0 008.378 6H4.5z"/>
                                        </svg>
                                        <?php esc_html_e( 'Copy', 'kwl-coupon-wp' ); ?>
                                    </button>
                                </div>
                                <p class="cwp-code-revealed__hint">
                                    <?php printf(
                                        /* translators: %s = store name */
                                        esc_html__( 'Code copied! Now %s', 'kwl-coupon-wp' ),
                                        $store_go_url
                                            ? '<a href="' . esc_url( $store_go_url ) . '" target="_blank" rel="nofollow noopener sponsored">' . sprintf( esc_html__( 'visit %s →', 'kwl-coupon-wp' ), esc_html( $store_name ) ) . '</a>'
                                            : esc_html( $store_name )
                                    ); ?>
                                </p>
                            </div>

                        </div><!-- /.cwp-coupon-single__reveal -->

                        <?php else : ?>
                        <!-- Deal / Free Shipping — direct link -->
                        <div class="cwp-coupon-single__reveal">
                            <a
                                href="<?php echo esc_url( $affiliate ); ?>"
                                class="cwp-btn cwp-btn--primary cwp-btn--full cwp-btn-deal--large"
                                target="_blank"
                                rel="nofollow noopener sponsored"
                                data-coupon-id="<?php echo esc_attr( $coupon_id ); ?>"
                                data-action="deal"
                            >
                                <?php echo esc_html(
                                    $type === 'freeshipping'
                                        ? __( 'Get Free Shipping', 'kwl-coupon-wp' )
                                        : __( 'Get This Deal', 'kwl-coupon-wp' )
                                ); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5z" clip-rule="evenodd"/>
                                    <path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 001.06.053L16.5 4.44v2.81a.75.75 0 001.5 0v-4.5a.75.75 0 00-.75-.75h-4.5a.75.75 0 000 1.5h2.553l-9.056 8.194a.75.75 0 00-.053 1.06z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                            <p style="text-align:center; color:var(--cwp-text-muted); font-size:0.875rem; margin-top:var(--cwp-space-2);">
                                <?php esc_html_e( 'No code required — discount applied automatically.', 'kwl-coupon-wp' ); ?>
                            </p>
                        </div>
                        <?php endif; ?>

                    <?php else : ?>
                    <!-- Expired state -->
                    <div class="cwp-coupon-single__expired">
                        <p><?php esc_html_e( 'This coupon has expired.', 'kwl-coupon-wp' ); ?></p>
                        <?php if ( $store_url ) : ?>
                        <a href="<?php echo esc_url( $store_url ); ?>" class="cwp-btn cwp-btn--outline">
                            <?php printf( esc_html__( 'Find active %s coupons →', 'kwl-coupon-wp' ), esc_html( $store_name ) ); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>


                    <!-- =====================================================
                         COUPON META DETAILS
                         ===================================================== -->
                    <div class="cwp-coupon-single__meta">

                        <div class="cwp-coupon-meta-grid">

                            <!-- Expiry -->
                            <div class="cwp-coupon-meta-item">
                                <span class="cwp-coupon-meta-item__label"><?php esc_html_e( 'Expiry', 'kwl-coupon-wp' ); ?></span>
                                <span class="cwp-coupon-meta-item__value <?php echo esc_attr( $expiry_class ); ?>">
                                    <?php echo esc_html( $expiry_label ); ?>
                                </span>
                            </div>

                            <!-- Type -->
                            <div class="cwp-coupon-meta-item">
                                <span class="cwp-coupon-meta-item__label"><?php esc_html_e( 'Type', 'kwl-coupon-wp' ); ?></span>
                                <span class="cwp-coupon-meta-item__value">
                                    <?php
                                    $type_labels = [
                                        'code'         => __( 'Coupon Code',   'kwl-coupon-wp' ),
                                        'deal'         => __( 'Deal',          'kwl-coupon-wp' ),
                                        'freeshipping' => __( 'Free Shipping', 'kwl-coupon-wp' ),
                                    ];
                                    echo esc_html( $type_labels[ $type ] ?? $type );
                                    ?>
                                </span>
                            </div>

                            <!-- Success Rate -->
                            <?php if ( $votes['total'] >= 3 ) : ?>
                            <div class="cwp-coupon-meta-item">
                                <span class="cwp-coupon-meta-item__label"><?php esc_html_e( 'Success Rate', 'kwl-coupon-wp' ); ?></span>
                                <span class="cwp-coupon-meta-item__value"><?php echo esc_html( $votes['rate'] ); ?>%</span>
                            </div>
                            <?php endif; ?>

                            <!-- Store -->
                            <?php if ( $store_name ) : ?>
                            <div class="cwp-coupon-meta-item">
                                <span class="cwp-coupon-meta-item__label"><?php esc_html_e( 'Store', 'kwl-coupon-wp' ); ?></span>
                                <span class="cwp-coupon-meta-item__value">
                                    <a href="<?php echo esc_url( $store_url ); ?>"><?php echo esc_html( $store_name ); ?></a>
                                </span>
                            </div>
                            <?php endif; ?>

                        </div><!-- /.cwp-coupon-meta-grid -->

                        <!-- Categories -->
                        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                        <div class="cwp-coupon-single__cats">
                            <span class="cwp-coupon-meta-item__label"><?php esc_html_e( 'Categories:', 'kwl-coupon-wp' ); ?></span>
                            <?php foreach ( $categories as $cat ) : ?>
                            <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="cwp-badge cwp-badge--new">
                                <?php echo esc_html( $cat->name ); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Tags -->
                        <?php if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) : ?>
                        <div class="cwp-coupon-single__tags" style="margin-top:var(--cwp-space-3);">
                            <?php foreach ( $tags as $tag ) : ?>
                            <a href="<?php echo esc_url( get_term_link( $tag ) ); ?>" class="cwp-badge">
                                #<?php echo esc_html( $tag->name ); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div><!-- /.cwp-coupon-single__meta -->


                    <!-- =====================================================
                         VOTING
                         ===================================================== -->
                    <?php if ( kwl_get_option( 'enable_voting', true ) && ! $is_expired ) : ?>
                    <div class="cwp-coupon-single__votes">
                        <span class="cwp-coupon-single__votes-label">
                            <?php esc_html_e( 'Did this coupon work for you?', 'kwl-coupon-wp' ); ?>
                        </span>
                        <div class="cwp-votes" id="kwl-vote-buttons">
                            <button
                                class="cwp-vote-btn"
                                data-coupon-id="<?php echo esc_attr( $coupon_id ); ?>"
                                data-vote="up"
                                aria-label="<?php esc_attr_e( 'This coupon worked', 'kwl-coupon-wp' ); ?>"
                            >
                                👍 <?php esc_html_e( 'Worked', 'kwl-coupon-wp' ); ?>
                                <span class="cwp-vote-count"><?php echo $votes['up'] > 0 ? '(' . $votes['up'] . ')' : ''; ?></span>
                            </button>
                            <button
                                class="cwp-vote-btn cwp-vote-btn--down"
                                data-coupon-id="<?php echo esc_attr( $coupon_id ); ?>"
                                data-vote="down"
                                aria-label="<?php esc_attr_e( "This coupon didn't work", 'kwl-coupon-wp' ); ?>"
                            >
                                👎 <?php esc_html_e( "Didn't Work", 'kwl-coupon-wp' ); ?>
                                <span class="cwp-vote-count"><?php echo $votes['down'] > 0 ? '(' . $votes['down'] . ')' : ''; ?></span>
                            </button>
                        </div>
                        <p class="cwp-vote-feedback" id="kwl-vote-feedback" hidden></p>
                    </div>
                    <?php endif; ?>

                </div><!-- /.cwp-coupon-single -->


                <!-- =========================================================
                     FULL COUPON DESCRIPTION
                     ========================================================= -->
                <?php
                $content = get_post_field( 'post_content', $coupon_id );
                if ( ! empty( trim( $content ) ) ) :
                ?>
                <div class="cwp-coupon-content cwp-prose">
                    <h2><?php esc_html_e( 'Coupon Details', 'kwl-coupon-wp' ); ?></h2>
                    <?php echo wp_kses_post( apply_filters( 'the_content', $content ) ); ?>
                </div>
                <?php endif; ?>


                <!-- =========================================================
                     MORE COUPONS FROM THIS STORE
                     ========================================================= -->
                <?php if ( $store_id ) :
                    $related_query = kwl_get_coupons_by_store( $store_id, true, 5 );
                    // Exclude current coupon
                    $related_posts = array_filter(
                        $related_query->posts,
                        fn( $p ) => $p->ID !== $coupon_id
                    );
                ?>
                <?php if ( ! empty( $related_posts ) ) : ?>
                <div class="cwp-related-coupons">
                    <div class="cwp-section-header">
                        <h2 class="cwp-section-title">
                            <?php printf(
                                esc_html__( 'More %s Coupons', 'kwl-coupon-wp' ),
                                esc_html( $store_name )
                            ); ?>
                        </h2>
                        <a href="<?php echo esc_url( $store_url ); ?>" class="cwp-section-link">
                            <?php esc_html_e( 'View All', 'kwl-coupon-wp' ); ?>
                        </a>
                    </div>
                    <div class="cwp-grid cwp-grid--coupons">
                        <?php foreach ( array_slice( $related_posts, 0, 4 ) as $related ) : ?>
                            <?php kwl_render_coupon_card( $related->ID, false ); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
                <?php endif; ?>

            </div><!-- /.cwp-main-content -->

            <?php if ( ! $is_full ) : ?>
                <?php get_sidebar(); ?>
            <?php endif; ?>

        </div><!-- /.cwp-layout -->

    </div><!-- /.cwp-container -->
</main>

<?php get_footer(); ?>
