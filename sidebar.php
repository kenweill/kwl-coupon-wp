<?php
/**
 * KWL Coupon WP — Sidebar
 *
 * Outputs the main sidebar widget area.
 * Called from templates via get_sidebar().
 *
 * @package KWL_Coupon_WP
 */

// Don't show sidebar on full-width layout
if ( kwl_get_option( 'layout', 'sidebar-right' ) === 'full-width' ) {
    return;
}
?>

<aside id="kwl-sidebar" class="cwp-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar', 'kwl-coupon-wp' ); ?>">

    <!-- Ad slot above sidebar content -->
    <?php if ( kwl_get_option( 'ads_enabled' ) && is_active_sidebar( 'kwl-ad-sidebar' ) ) : ?>
        <?php dynamic_sidebar( 'kwl-ad-sidebar' ); ?>
    <?php endif; ?>

    <!-- Main sidebar widgets -->
    <?php if ( is_front_page() && is_active_sidebar( 'kwl-sidebar-home' ) ) : ?>
        <?php dynamic_sidebar( 'kwl-sidebar-home' ); ?>
    <?php elseif ( is_active_sidebar( 'kwl-sidebar-main' ) ) : ?>
        <?php dynamic_sidebar( 'kwl-sidebar-main' ); ?>
    <?php else : ?>
        <!-- Default sidebar when no widgets are added -->
        <?php
        // Show search widget by default
        $search_widget = new KWL_Widget_Search();
        $search_widget->widget(
            [
                'before_widget' => '<div class="cwp-widget">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="cwp-widget__title">',
                'after_title'   => '</h3><div class="cwp-widget__body">',
            ],
            [ 'placeholder' => __( 'Search coupons & stores…', 'kwl-coupon-wp' ) ]
        );

        // Show top categories
        $cat_widget = new KWL_Widget_Top_Categories();
        $cat_widget->widget(
            [
                'before_widget' => '<div class="cwp-widget">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="cwp-widget__title">',
                'after_title'   => '</h3><div class="cwp-widget__body">',
            ],
            [
                'title'      => __( 'Categories', 'kwl-coupon-wp' ),
                'count'      => 10,
                'show_count' => true,
            ]
        );
        ?>
    <?php endif; ?>

</aside>
