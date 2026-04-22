    </div><!-- /#kwl-content -->


    <!-- =====================================================================
         FOOTER
         ===================================================================== -->
    <footer id="kwl-footer" class="cwp-footer" role="contentinfo">

        <div class="cwp-container">

            <!-- Footer Main Grid -->
            <div class="cwp-footer__main">

                <!-- Brand Column -->
                <div class="cwp-footer__brand">

                    <!-- Logo or site name -->
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cwp-footer__logo" rel="home">
                        <?php if ( has_custom_logo() ) : ?>
                            <?php
                            $logo_id  = get_theme_mod( 'custom_logo' );
                            $logo_img = wp_get_attachment_image( $logo_id, 'full', false, [
                                'class'  => 'cwp-footer__logo-img',
                                'style'  => 'max-height:36px; width:auto; filter:brightness(0) invert(1);',
                            ] );
                            echo $logo_img; // phpcs:ignore
                            ?>
                        <?php else : ?>
                            <?php bloginfo( 'name' ); ?>
                        <?php endif; ?>
                    </a>

                    <!-- Brand description -->
                    <?php
                    $footer_desc = get_theme_mod( 'kwl_footer_description', '' );
                    if ( empty( $footer_desc ) ) {
                        $footer_desc = get_bloginfo( 'description' );
                    }
                    if ( $footer_desc ) :
                    ?>
                    <p class="cwp-footer__description"><?php echo esc_html( $footer_desc ); ?></p>
                    <?php endif; ?>

                    <!-- Social Links -->
                    <?php kwl_footer_social_links(); ?>

                </div><!-- /.cwp-footer__brand -->

                <!-- Footer Nav Columns (from menus or auto-generated) -->
                <?php for ( $col = 1; $col <= 3; $col++ ) : ?>
                <div class="cwp-footer__col">

                    <?php if ( has_nav_menu( 'kwl-footer-' . $col ) ) : ?>

                        <?php
                        // Get menu name for the column title
                        $menu_obj = wp_get_nav_menu_object(
                            get_nav_menu_locations()[ 'kwl-footer-' . $col ] ?? 0
                        );
                        if ( $menu_obj ) :
                        ?>
                        <h3 class="cwp-footer__col-title"><?php echo esc_html( $menu_obj->name ); ?></h3>
                        <?php endif; ?>

                        <?php
                        wp_nav_menu( [
                            'theme_location' => 'kwl-footer-' . $col,
                            'menu_class'     => 'cwp-footer__links',
                            'container'      => false,
                            'depth'          => 1,
                            'fallback_cb'    => false,
                            'items_wrap'     => '<ul class="cwp-footer__links">%3$s</ul>',
                            'walker'         => new KWL_Footer_Nav_Walker(),
                        ] );
                        ?>

                    <?php else : ?>

                        <?php
                        // Auto-generate fallback columns
                        switch ( $col ) {
                            case 1:
                                kwl_footer_col_stores();
                                break;
                            case 2:
                                kwl_footer_col_categories();
                                break;
                            case 3:
                                kwl_footer_col_pages();
                                break;
                        }
                        ?>

                    <?php endif; ?>

                </div>
                <?php endfor; ?>

            </div><!-- /.cwp-footer__main -->

            <!-- Footer Bottom Bar -->
            <div class="cwp-footer__bottom">

                <!-- Copyright -->
                <p class="cwp-footer__copy">
                    <?php echo kwl_get_footer_copyright(); // phpcs:ignore ?>
                </p>

                <!-- Bottom links -->
                <nav class="cwp-footer__bottom-links" aria-label="<?php esc_attr_e( 'Footer Legal Links', 'kwl-coupon-wp' ); ?>">
                    <?php
                    // Show Privacy Policy link if set in WordPress settings
                    $privacy_page = get_privacy_policy_url();
                    if ( $privacy_page ) :
                    ?>
                    <a href="<?php echo esc_url( $privacy_page ); ?>">
                        <?php esc_html_e( 'Privacy Policy', 'kwl-coupon-wp' ); ?>
                    </a>
                    <?php endif; ?>

                    <?php
                    // Affiliate disclosure page (if set in theme options)
                    $disclosure_page = kwl_get_option( 'disclosure_page', 0 );
                    if ( $disclosure_page ) :
                    ?>
                    <a href="<?php echo esc_url( get_permalink( $disclosure_page ) ); ?>">
                        <?php esc_html_e( 'Affiliate Disclosure', 'kwl-coupon-wp' ); ?>
                    </a>
                    <?php endif; ?>

                    <?php
                    // Contact page
                    $contact_page = kwl_get_option( 'contact_page', 0 );
                    if ( $contact_page ) :
                    ?>
                    <a href="<?php echo esc_url( get_permalink( $contact_page ) ); ?>">
                        <?php esc_html_e( 'Contact', 'kwl-coupon-wp' ); ?>
                    </a>
                    <?php endif; ?>
                </nav>

            </div><!-- /.cwp-footer__bottom -->

        </div><!-- /.cwp-container -->

    </footer><!-- /#kwl-footer -->

</div><!-- /#kwl-page -->

<?php wp_footer(); ?>

</body>
</html>


<?php
/* =============================================================================
   FOOTER HELPER FUNCTIONS
   ============================================================================= */

/**
 * Output social media links in the footer.
 */
function kwl_footer_social_links(): void {

    $social = [
        'social_twitter'   => [ 'label' => 'Twitter / X',  'icon' => 'twitter'   ],
        'social_facebook'  => [ 'label' => 'Facebook',     'icon' => 'facebook'  ],
        'social_instagram' => [ 'label' => 'Instagram',    'icon' => 'instagram' ],
        'social_pinterest' => [ 'label' => 'Pinterest',    'icon' => 'pinterest' ],
        'social_youtube'   => [ 'label' => 'YouTube',      'icon' => 'youtube'   ],
    ];

    $has_social = false;
    foreach ( $social as $key => $data ) {
        if ( kwl_get_option( $key, '' ) ) {
            $has_social = true;
            break;
        }
    }

    if ( ! $has_social ) {
        return;
    }

    echo '<div class="cwp-footer__social">';

    foreach ( $social as $key => $data ) {
        $url = kwl_get_option( $key, '' );
        if ( empty( $url ) ) {
            continue;
        }
        printf(
            '<a href="%s" class="cwp-footer__social-link" target="_blank" rel="noopener noreferrer" aria-label="%s">%s</a>',
            esc_url( $url ),
            esc_attr( $data['label'] ),
            kwl_social_icon_svg( $data['icon'] ) // phpcs:ignore
        );
    }

    echo '</div>';

}


/**
 * Get a social media SVG icon.
 *
 * @param  string $icon  Icon slug.
 * @return string        SVG HTML.
 */
function kwl_social_icon_svg( string $icon ): string {

    $icons = [
        'twitter'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'facebook'  => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
        'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>',
        'youtube'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.495 6.205a3.007 3.007 0 00-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 00.527 6.205a31.247 31.247 0 00-.522 5.805 31.247 31.247 0 00.522 5.783 3.007 3.007 0 002.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 002.088-2.088 31.247 31.247 0 00.5-5.783 31.247 31.247 0 00-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>',
    ];

    return $icons[ $icon ] ?? '';

}


/**
 * Generate the footer copyright text.
 * Supports {year} and {site} placeholders.
 *
 * @return string
 */
function kwl_get_footer_copyright(): string {

    $custom = get_theme_mod( 'kwl_footer_copyright', '' );

    if ( empty( $custom ) ) {
        $custom = kwl_get_option( 'footer_text', '' );
    }

    if ( empty( $custom ) ) {
        $custom = '© {year} {site}. ' . __( 'All Rights Reserved.', 'kwl-coupon-wp' );
    }

    $output = str_replace(
        [ '{year}', '{site}' ],
        [ gmdate( 'Y' ), '<a href="' . esc_url( home_url( '/' ) ) . '">' . get_bloginfo( 'name' ) . '</a>' ],
        $custom
    );

    return wp_kses( $output, [ 'a' => [ 'href' => true ] ] );

}


/**
 * Auto-generate footer column 1: Popular Stores.
 */
function kwl_footer_col_stores(): void {

    $stores = kwl_get_featured_stores( 6 );

    if ( empty( $stores ) ) {
        $stores = get_posts( [
            'post_type'      => 'kwl_store',
            'post_status'    => 'publish',
            'posts_per_page' => 6,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );
    }

    if ( empty( $stores ) ) {
        return;
    }

    echo '<h3 class="cwp-footer__col-title">' . esc_html__( 'Popular Stores', 'kwl-coupon-wp' ) . '</h3>';
    echo '<ul class="cwp-footer__links">';

    foreach ( $stores as $store ) {
        printf(
            '<li><a href="%s" class="cwp-footer__link">%s</a></li>',
            esc_url( get_permalink( $store->ID ) ),
            esc_html( $store->post_title )
        );
    }

    echo '</ul>';

}


/**
 * Auto-generate footer column 2: Top Categories.
 */
function kwl_footer_col_categories(): void {

    $cats = kwl_get_coupon_categories( 6 );

    if ( empty( $cats ) ) {
        return;
    }

    echo '<h3 class="cwp-footer__col-title">' . esc_html__( 'Categories', 'kwl-coupon-wp' ) . '</h3>';
    echo '<ul class="cwp-footer__links">';

    foreach ( $cats as $cat ) {
        printf(
            '<li><a href="%s" class="cwp-footer__link">%s</a></li>',
            esc_url( get_term_link( $cat ) ),
            esc_html( $cat->name )
        );
    }

    echo '</ul>';

}


/**
 * Auto-generate footer column 3: Pages + useful links.
 */
function kwl_footer_col_pages(): void {

    echo '<h3 class="cwp-footer__col-title">' . esc_html__( 'Quick Links', 'kwl-coupon-wp' ) . '</h3>';
    echo '<ul class="cwp-footer__links">';

    // Store archive
    printf(
        '<li><a href="%s" class="cwp-footer__link">%s</a></li>',
        esc_url( get_post_type_archive_link( 'kwl_store' ) ),
        esc_html__( 'All Stores', 'kwl-coupon-wp' )
    );

    // Coupon archive
    printf(
        '<li><a href="%s" class="cwp-footer__link">%s</a></li>',
        esc_url( get_post_type_archive_link( 'kwl_coupon' ) ),
        esc_html__( 'All Coupons', 'kwl-coupon-wp' )
    );

    // WordPress pages (top level, published)
    $pages = get_pages( [
        'post_status' => 'publish',
        'number'      => 4,
        'parent'      => 0,
        'sort_column' => 'menu_order',
    ] );

    foreach ( $pages as $page ) {
        printf(
            '<li><a href="%s" class="cwp-footer__link">%s</a></li>',
            esc_url( get_permalink( $page->ID ) ),
            esc_html( $page->post_title )
        );
    }

    echo '</ul>';

}


/* =============================================================================
   NAV WALKERS
   ============================================================================= */

/**
 * Primary nav walker — renders items as .cwp-nav__item links.
 */
class KWL_Nav_Walker extends Walker_Nav_Menu {

    public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ): void {
        $item    = $data_object;
        $classes = implode( ' ', (array) $item->classes );
        $current = in_array( 'current-menu-item', (array) $item->classes, true );

        $output .= sprintf(
            '<a href="%s" class="cwp-nav__item %s" %s>%s</a>',
            esc_url( $item->url ),
            $current ? 'active' : '',
            $item->target ? 'target="' . esc_attr( $item->target ) . '"' : '',
            esc_html( $item->title )
        );
    }

    // No list wrappers — we render flat links
    public function start_lvl( &$output, $depth = 0, $args = null ): void {}
    public function end_lvl( &$output, $depth = 0, $args = null ): void {}
    public function end_el( &$output, $data_object, $depth = 0, $args = null ): void {}

}


/**
 * Footer nav walker — renders items as .cwp-footer__link list items.
 */
class KWL_Footer_Nav_Walker extends Walker_Nav_Menu {

    public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ): void {
        $item = $data_object;
        $output .= sprintf(
            '<li><a href="%s" class="cwp-footer__link" %s>%s</a></li>',
            esc_url( $item->url ),
            $item->target ? 'target="' . esc_attr( $item->target ) . '"' : '',
            esc_html( $item->title )
        );
    }

    public function start_lvl( &$output, $depth = 0, $args = null ): void {}
    public function end_lvl( &$output, $depth = 0, $args = null ): void {}
    public function end_el( &$output, $data_object, $depth = 0, $args = null ): void {}

}
