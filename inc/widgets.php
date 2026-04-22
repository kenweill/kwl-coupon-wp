<?php
/**
 * KWL Coupon WP — Widgets
 *
 * Registers custom sidebar widgets specific to coupon sites:
 *
 * 1. KWL_Widget_Popular_Stores  — Most-clicked stores with logos
 * 2. KWL_Widget_Top_Categories  — Category list with coupon counts
 * 3. KWL_Widget_Latest_Coupons  — Latest active coupons
 * 4. KWL_Widget_Store_Coupons   — Coupons for a specific store (used on store pages)
 * 5. KWL_Widget_Search          — Coupon-focused search box
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/**
 * Register all widgets.
 */
function kwl_register_widgets(): void {

    register_widget( 'KWL_Widget_Popular_Stores' );
    register_widget( 'KWL_Widget_Top_Categories' );
    register_widget( 'KWL_Widget_Latest_Coupons' );
    register_widget( 'KWL_Widget_Store_Coupons' );
    register_widget( 'KWL_Widget_Search' );

}
add_action( 'widgets_init', 'kwl_register_widgets' );


/* =============================================================================
   WIDGET BASE CLASS
   ============================================================================= */

/**
 * Abstract base class for KWL widgets.
 * Provides shared form helpers.
 */
abstract class KWL_Widget_Base extends WP_Widget {

    /**
     * Render a text input in the widget form.
     *
     * @param array  $instance  Widget instance values.
     * @param string $key       Field key.
     * @param string $label     Field label.
     * @param string $default   Default value.
     */
    protected function text_field( array $instance, string $key, string $label, string $default = '' ): void {
        $value = ! empty( $instance[ $key ] ) ? $instance[ $key ] : $default;
        printf(
            '<p><label for="%s">%s</label><input class="widefat" id="%s" name="%s" type="text" value="%s"></p>',
            esc_attr( $this->get_field_id( $key ) ),
            esc_html( $label ),
            esc_attr( $this->get_field_id( $key ) ),
            esc_attr( $this->get_field_name( $key ) ),
            esc_attr( $value )
        );
    }

    /**
     * Render a number input in the widget form.
     */
    protected function number_field( array $instance, string $key, string $label, int $default = 5, int $min = 1, int $max = 20 ): void {
        $value = isset( $instance[ $key ] ) ? absint( $instance[ $key ] ) : $default;
        printf(
            '<p><label for="%s">%s</label><input class="tiny-text" id="%s" name="%s" type="number" value="%d" min="%d" max="%d"></p>',
            esc_attr( $this->get_field_id( $key ) ),
            esc_html( $label ),
            esc_attr( $this->get_field_id( $key ) ),
            esc_attr( $this->get_field_name( $key ) ),
            $value, $min, $max
        );
    }

    /**
     * Render a checkbox in the widget form.
     */
    protected function checkbox_field( array $instance, string $key, string $label, bool $default = false ): void {
        $checked = isset( $instance[ $key ] ) ? (bool) $instance[ $key ] : $default;
        printf(
            '<p><label><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label></p>',
            esc_attr( $this->get_field_id( $key ) ),
            esc_attr( $this->get_field_name( $key ) ),
            checked( $checked, true, false ),
            esc_html( $label )
        );
    }

}


/* =============================================================================
   WIDGET 1: POPULAR STORES
   ============================================================================= */

class KWL_Widget_Popular_Stores extends KWL_Widget_Base {

    public function __construct() {
        parent::__construct(
            'kwl_popular_stores',
            __( 'KWL: Popular Stores', 'kwl-coupon-wp' ),
            [ 'description' => __( 'Displays the most-visited stores with logos.', 'kwl-coupon-wp' ) ]
        );
    }

    public function widget( $args, $instance ): void {

        $title  = apply_filters( 'widget_title', $instance['title'] ?? __( 'Popular Stores', 'kwl-coupon-wp' ) );
        $count  = absint( $instance['count'] ?? 8 );

        // Get stores ordered by click count
        $stores = get_posts( [
            'post_type'      => 'kwl_store',
            'post_status'    => 'publish',
            'posts_per_page' => $count,
            'meta_key'       => '_kwl_store_click_count',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ] );

        // Fall back to featured stores if no click data
        if ( empty( $stores ) ) {
            $stores = kwl_get_featured_stores( $count );
        }

        if ( empty( $stores ) ) {
            return;
        }

        echo $args['before_widget']; // phpcs:ignore
        echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore

        echo '<ul class="kwl-widget-stores">';

        foreach ( $stores as $store ) {
            $logo_url   = kwl_get_store_logo_url( $store->ID, 'kwl-store-logo-sm' );
            $store_url  = get_permalink( $store->ID );
            $store_name = $store->post_title;
            $coupon_count = kwl_get_store_coupon_count( $store->ID );

            echo '<li class="kwl-widget-store">';
            echo '<a href="' . esc_url( $store_url ) . '" class="kwl-widget-store__link">';

            if ( $logo_url ) {
                echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $store_name ) . '" '
                    . 'width="32" height="32" loading="lazy" class="kwl-widget-store__logo">';
            } else {
                $initial = mb_strtoupper( mb_substr( $store_name, 0, 1 ) );
                echo '<span class="kwl-widget-store__initial">' . esc_html( $initial ) . '</span>';
            }

            echo '<span class="kwl-widget-store__name">' . esc_html( $store_name ) . '</span>';
            echo '<span class="kwl-widget-store__count">' . sprintf(
                /* translators: %d = number of coupons */
                _n( '%d coupon', '%d coupons', $coupon_count, 'kwl-coupon-wp' ),
                $coupon_count
            ) . '</span>';

            echo '</a>';
            echo '</li>';
        }

        echo '</ul>';

        echo $args['after_widget']; // phpcs:ignore

    }

    public function form( $instance ): void {
        $this->text_field( $instance, 'title', __( 'Title', 'kwl-coupon-wp' ), __( 'Popular Stores', 'kwl-coupon-wp' ) );
        $this->number_field( $instance, 'count', __( 'Number of stores', 'kwl-coupon-wp' ), 8 );
    }

    public function update( $new_instance, $old_instance ): array {
        return [
            'title' => sanitize_text_field( $new_instance['title'] ?? '' ),
            'count' => absint( $new_instance['count'] ?? 8 ),
        ];
    }

}


/* =============================================================================
   WIDGET 2: TOP CATEGORIES
   ============================================================================= */

class KWL_Widget_Top_Categories extends KWL_Widget_Base {

    public function __construct() {
        parent::__construct(
            'kwl_top_categories',
            __( 'KWL: Top Categories', 'kwl-coupon-wp' ),
            [ 'description' => __( 'Displays coupon categories with counts.', 'kwl-coupon-wp' ) ]
        );
    }

    public function widget( $args, $instance ): void {

        $title    = apply_filters( 'widget_title', $instance['title'] ?? __( 'Categories', 'kwl-coupon-wp' ) );
        $count    = absint( $instance['count'] ?? 10 );
        $show_count = ! empty( $instance['show_count'] );

        $categories = kwl_get_coupon_categories( $count );

        if ( empty( $categories ) ) {
            return;
        }

        echo $args['before_widget']; // phpcs:ignore
        echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore

        echo '<ul class="kwl-widget-categories">';

        foreach ( $categories as $cat ) {
            $icon     = kwl_get_category_icon( $cat );
            $cat_url  = get_term_link( $cat );

            echo '<li>';
            echo '<a href="' . esc_url( $cat_url ) . '">';
            echo '<span class="kwl-widget-cat__icon">' . esc_html( $icon ) . '</span>';
            echo '<span class="kwl-widget-cat__name">' . esc_html( $cat->name ) . '</span>';

            if ( $show_count ) {
                echo '<span class="cwp-widget-list__count">' . number_format( $cat->count ) . '</span>';
            }

            echo '</a>';
            echo '</li>';
        }

        echo '</ul>';

        echo $args['after_widget']; // phpcs:ignore

    }

    public function form( $instance ): void {
        $this->text_field( $instance, 'title', __( 'Title', 'kwl-coupon-wp' ), __( 'Categories', 'kwl-coupon-wp' ) );
        $this->number_field( $instance, 'count', __( 'Number of categories', 'kwl-coupon-wp' ), 10 );
        $this->checkbox_field( $instance, 'show_count', __( 'Show coupon count', 'kwl-coupon-wp' ), true );
    }

    public function update( $new_instance, $old_instance ): array {
        return [
            'title'      => sanitize_text_field( $new_instance['title'] ?? '' ),
            'count'      => absint( $new_instance['count'] ?? 10 ),
            'show_count' => ! empty( $new_instance['show_count'] ),
        ];
    }

}


/* =============================================================================
   WIDGET 3: LATEST COUPONS
   ============================================================================= */

class KWL_Widget_Latest_Coupons extends KWL_Widget_Base {

    public function __construct() {
        parent::__construct(
            'kwl_latest_coupons',
            __( 'KWL: Latest Coupons', 'kwl-coupon-wp' ),
            [ 'description' => __( 'Shows the most recently added active coupons.', 'kwl-coupon-wp' ) ]
        );
    }

    public function widget( $args, $instance ): void {

        $title = apply_filters( 'widget_title', $instance['title'] ?? __( 'Latest Coupons', 'kwl-coupon-wp' ) );
        $count = absint( $instance['count'] ?? 5 );

        $query = kwl_get_latest_coupons( $count, true );

        if ( ! $query->have_posts() ) {
            return;
        }

        echo $args['before_widget']; // phpcs:ignore
        echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore

        echo '<ul class="kwl-widget-coupons">';

        while ( $query->have_posts() ) {
            $query->the_post();
            $coupon_id = get_the_ID();
            $store_id  = kwl_get_coupon_store_id( $coupon_id );
            $discount  = kwl_get_coupon_discount( $coupon_id );
            $type      = kwl_get_coupon_type( $coupon_id );

            echo '<li class="kwl-widget-coupon">';
            echo '<a href="' . esc_url( get_permalink() ) . '">';

            if ( $discount ) {
                echo '<strong class="kwl-widget-coupon__discount">' . esc_html( $discount ) . '</strong> ';
            }

            echo '<span class="kwl-widget-coupon__title">' . esc_html( get_the_title() ) . '</span>';

            if ( $store_id ) {
                echo '<span class="kwl-widget-coupon__store">' . esc_html( get_the_title( $store_id ) ) . '</span>';
            }

            echo '</a>';
            echo '</li>';
        }

        wp_reset_postdata();

        echo '</ul>';

        echo $args['after_widget']; // phpcs:ignore

    }

    public function form( $instance ): void {
        $this->text_field( $instance, 'title', __( 'Title', 'kwl-coupon-wp' ), __( 'Latest Coupons', 'kwl-coupon-wp' ) );
        $this->number_field( $instance, 'count', __( 'Number of coupons', 'kwl-coupon-wp' ), 5 );
    }

    public function update( $new_instance, $old_instance ): array {
        return [
            'title' => sanitize_text_field( $new_instance['title'] ?? '' ),
            'count' => absint( $new_instance['count'] ?? 5 ),
        ];
    }

}


/* =============================================================================
   WIDGET 4: STORE COUPONS
   ============================================================================= */

class KWL_Widget_Store_Coupons extends KWL_Widget_Base {

    public function __construct() {
        parent::__construct(
            'kwl_store_coupons',
            __( 'KWL: Store Coupons', 'kwl-coupon-wp' ),
            [ 'description' => __( 'On store pages, auto-shows that store\'s coupons. On other pages, shows a selected store\'s coupons.', 'kwl-coupon-wp' ) ]
        );
    }

    public function widget( $args, $instance ): void {

        $title    = apply_filters( 'widget_title', $instance['title'] ?? __( 'Store Coupons', 'kwl-coupon-wp' ) );
        $count    = absint( $instance['count'] ?? 5 );

        // Auto-detect store from current page
        $store_id = 0;

        if ( is_singular( 'kwl_store' ) ) {
            $store_id = get_the_ID();
        } elseif ( is_singular( 'kwl_coupon' ) ) {
            $store_id = kwl_get_coupon_store_id( get_the_ID() );
        } elseif ( ! empty( $instance['store_id'] ) ) {
            $store_id = absint( $instance['store_id'] );
        }

        if ( ! $store_id ) {
            return;
        }

        $query = kwl_get_coupons_by_store( $store_id, true, $count );

        if ( ! $query->have_posts() ) {
            return;
        }

        $display_title = str_replace(
            '{store}',
            get_the_title( $store_id ),
            $title
        );

        echo $args['before_widget']; // phpcs:ignore
        echo $args['before_title'] . esc_html( $display_title ) . $args['after_title']; // phpcs:ignore

        echo '<ul class="kwl-widget-coupons">';

        while ( $query->have_posts() ) {
            $query->the_post();
            $coupon_id = get_the_ID();
            $discount  = kwl_get_coupon_discount( $coupon_id );
            $type      = kwl_get_coupon_type( $coupon_id );

            echo '<li class="kwl-widget-coupon">';
            echo '<a href="' . esc_url( get_permalink() ) . '">';

            if ( $discount ) {
                echo '<strong class="kwl-widget-coupon__discount">' . esc_html( $discount ) . '</strong> ';
            }

            echo '<span class="kwl-widget-coupon__title">' . esc_html( get_the_title() ) . '</span>';

            if ( $type === 'code' ) {
                $code = kwl_get_coupon_code( $coupon_id );
                if ( $code ) {
                    echo '<code class="kwl-widget-coupon__code">' . esc_html( $code ) . '</code>';
                }
            }

            echo '</a>';
            echo '</li>';
        }

        wp_reset_postdata();

        echo '</ul>';

        echo $args['after_widget']; // phpcs:ignore

    }

    public function form( $instance ): void {
        $this->text_field( $instance, 'title', __( 'Title (use {store} for store name)', 'kwl-coupon-wp' ), __( '{store} Coupons', 'kwl-coupon-wp' ) );
        $this->number_field( $instance, 'count', __( 'Number of coupons', 'kwl-coupon-wp' ), 5 );

        // Store selector (for non-store pages)
        $stores   = get_posts( [ 'post_type' => 'kwl_store', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        $store_id = absint( $instance['store_id'] ?? 0 );

        echo '<p><label for="' . esc_attr( $this->get_field_id( 'store_id' ) ) . '">'
            . esc_html__( 'Fallback Store (if not on a store page)', 'kwl-coupon-wp' ) . '</label>';
        echo '<select class="widefat" id="' . esc_attr( $this->get_field_id( 'store_id' ) ) . '" '
            . 'name="' . esc_attr( $this->get_field_name( 'store_id' ) ) . '">';
        echo '<option value="">' . esc_html__( '— None —', 'kwl-coupon-wp' ) . '</option>';

        foreach ( $stores as $store ) {
            printf(
                '<option value="%d" %s>%s</option>',
                $store->ID,
                selected( $store_id, $store->ID, false ),
                esc_html( $store->post_title )
            );
        }
        echo '</select></p>';
    }

    public function update( $new_instance, $old_instance ): array {
        return [
            'title'    => sanitize_text_field( $new_instance['title'] ?? '' ),
            'count'    => absint( $new_instance['count'] ?? 5 ),
            'store_id' => absint( $new_instance['store_id'] ?? 0 ),
        ];
    }

}


/* =============================================================================
   WIDGET 5: SEARCH
   ============================================================================= */

class KWL_Widget_Search extends KWL_Widget_Base {

    public function __construct() {
        parent::__construct(
            'kwl_search',
            __( 'KWL: Coupon Search', 'kwl-coupon-wp' ),
            [ 'description' => __( 'A coupon-focused search box for the sidebar.', 'kwl-coupon-wp' ) ]
        );
    }

    public function widget( $args, $instance ): void {

        $title       = apply_filters( 'widget_title', $instance['title'] ?? '' );
        $placeholder = $instance['placeholder'] ?? __( 'Search coupons & stores…', 'kwl-coupon-wp' );

        echo $args['before_widget']; // phpcs:ignore

        if ( $title ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore
        }

        ?>
        <form role="search" method="get" class="cwp-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <svg class="cwp-search__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
            </svg>
            <input
                type="search"
                class="cwp-search__input"
                placeholder="<?php echo esc_attr( $placeholder ); ?>"
                value="<?php echo esc_attr( get_search_query() ); ?>"
                name="s"
            >
            <button type="submit" class="cwp-search__btn"><?php esc_html_e( 'Search', 'kwl-coupon-wp' ); ?></button>
        </form>
        <?php

        echo $args['after_widget']; // phpcs:ignore

    }

    public function form( $instance ): void {
        $this->text_field( $instance, 'title', __( 'Title (optional)', 'kwl-coupon-wp' ), '' );
        $this->text_field( $instance, 'placeholder', __( 'Placeholder Text', 'kwl-coupon-wp' ), __( 'Search coupons & stores…', 'kwl-coupon-wp' ) );
    }

    public function update( $new_instance, $old_instance ): array {
        return [
            'title'       => sanitize_text_field( $new_instance['title'] ?? '' ),
            'placeholder' => sanitize_text_field( $new_instance['placeholder'] ?? '' ),
        ];
    }

}
