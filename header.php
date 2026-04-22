<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="kwl-page" class="cwp-page-wrapper">

    <!-- =====================================================================
         HEADER AD SLOT
         ===================================================================== -->
    <?php if ( kwl_get_option( 'ads_enabled' ) && is_active_sidebar( 'kwl-ad-header' ) ) : ?>
    <div class="cwp-ad-banner cwp-ad-banner--top">
        <div class="cwp-container">
            <?php dynamic_sidebar( 'kwl-ad-header' ); ?>
        </div>
    </div>
    <?php endif; ?>


    <!-- =====================================================================
         MAIN HEADER
         ===================================================================== -->
    <header id="kwl-header" class="cwp-header" role="banner">
        <div class="cwp-container">
            <div class="cwp-header__inner">

                <!-- Logo -->
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cwp-logo" rel="home">
                    <?php if ( has_custom_logo() ) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <span class="cwp-logo__text">
                            <?php
                            $site_name = get_bloginfo( 'name' );
                            $parts     = explode( ' ', $site_name, 2 );
                            if ( count( $parts ) > 1 ) {
                                echo esc_html( $parts[0] ) . '<span>' . esc_html( $parts[1] ) . '</span>';
                            } else {
                                echo esc_html( $site_name );
                            }
                            ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Header Search (hidden on mobile, shown on tablet+) -->
                <?php if ( get_theme_mod( 'kwl_header_search', true ) ) : ?>
                <div class="cwp-header__search">
                    <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <div class="cwp-search">
                            <svg class="cwp-search__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" width="18" height="18">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                            </svg>
                            <input
                                type="search"
                                class="cwp-search__input"
                                id="kwl-header-search"
                                name="s"
                                value="<?php echo esc_attr( get_search_query() ); ?>"
                                placeholder="<?php esc_attr_e( 'Search stores, coupons…', 'kwl-coupon-wp' ); ?>"
                                autocomplete="off"
                                aria-label="<?php esc_attr_e( 'Search', 'kwl-coupon-wp' ); ?>"
                            >
                            <button type="submit" class="cwp-search__btn">
                                <?php esc_html_e( 'Search', 'kwl-coupon-wp' ); ?>
                            </button>
                            <!-- Autocomplete dropdown — populated by search.js -->
                            <div id="kwl-search-results" class="cwp-search-dropdown" aria-live="polite" hidden></div>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Header Actions -->
                <div class="cwp-header__actions">

                    <!-- Submit Coupon link (if enabled) -->
                    <?php
                    $submit_page = kwl_get_option( 'submit_coupon_page', 0 );
                    if ( $submit_page ) :
                    ?>
                    <a href="<?php echo esc_url( get_permalink( $submit_page ) ); ?>" class="cwp-btn cwp-btn--outline cwp-btn--sm">
                        <?php esc_html_e( 'Submit Coupon', 'kwl-coupon-wp' ); ?>
                    </a>
                    <?php endif; ?>

                    <!-- Mobile search toggle -->
                    <button
                        class="cwp-menu-toggle cwp-search-toggle"
                        aria-label="<?php esc_attr_e( 'Toggle search', 'kwl-coupon-wp' ); ?>"
                        aria-expanded="false"
                        aria-controls="kwl-mobile-search"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <!-- Mobile menu toggle -->
                    <button
                        class="cwp-menu-toggle cwp-nav-toggle"
                        aria-label="<?php esc_attr_e( 'Toggle navigation', 'kwl-coupon-wp' ); ?>"
                        aria-expanded="false"
                        aria-controls="kwl-primary-nav"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10zm0 5.25a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                </div>

            </div>
        </div>

        <!-- Mobile Search Bar (hidden by default, shown on toggle) -->
        <div id="kwl-mobile-search" class="cwp-mobile-search" hidden>
            <div class="cwp-container">
                <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <div class="cwp-search">
                        <svg class="cwp-search__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                        <input
                            type="search"
                            class="cwp-search__input"
                            name="s"
                            value="<?php echo esc_attr( get_search_query() ); ?>"
                            placeholder="<?php esc_attr_e( 'Search stores, coupons…', 'kwl-coupon-wp' ); ?>"
                            autocomplete="off"
                        >
                        <button type="submit" class="cwp-search__btn">
                            <?php esc_html_e( 'Search', 'kwl-coupon-wp' ); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </header><!-- /#kwl-header -->


    <!-- =====================================================================
         CATEGORY NAVIGATION BAR
         ===================================================================== -->
    <?php if ( get_theme_mod( 'kwl_show_nav', true ) ) : ?>
    <nav id="kwl-primary-nav" class="cwp-nav" aria-label="<?php esc_attr_e( 'Category Navigation', 'kwl-coupon-wp' ); ?>">
        <div class="cwp-container">
            <div class="cwp-nav__inner">

                <!-- All Stores link -->
                <a href="<?php echo esc_url( get_post_type_archive_link( 'kwl_store' ) ); ?>"
                   class="cwp-nav__item <?php echo is_post_type_archive( 'kwl_store' ) ? 'active' : ''; ?>">
                    <?php esc_html_e( 'All Stores', 'kwl-coupon-wp' ); ?>
                </a>

                <!-- WordPress nav menu (if assigned) or fallback to categories -->
                <?php if ( has_nav_menu( 'kwl-primary' ) ) : ?>
                    <?php
                    wp_nav_menu( [
                        'theme_location'  => 'kwl-primary',
                        'menu_class'      => '',
                        'container'       => false,
                        'fallback_cb'     => false,
                        'items_wrap'      => '%3$s',
                        'walker'          => new KWL_Nav_Walker(),
                    ] );
                    ?>
                <?php else : ?>
                    <?php
                    // Fallback: show top coupon categories
                    $nav_cats = get_terms( [
                        'taxonomy'   => 'kwl_coupon_cat',
                        'hide_empty' => true,
                        'number'     => 10,
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                        'parent'     => 0,
                    ] );

                    if ( ! is_wp_error( $nav_cats ) ) {
                        foreach ( $nav_cats as $cat ) {
                            $is_current = is_tax( 'kwl_coupon_cat', $cat->term_id );
                            printf(
                                '<a href="%s" class="cwp-nav__item %s">%s</a>',
                                esc_url( get_term_link( $cat ) ),
                                $is_current ? 'active' : '',
                                esc_html( $cat->name )
                            );
                        }
                    }
                    ?>
                <?php endif; ?>

            </div>
        </div>
    </nav>
    <?php endif; ?>


    <!-- =====================================================================
         PAGE CONTENT STARTS HERE
         ===================================================================== -->
    <div id="kwl-content" class="cwp-site-content">
