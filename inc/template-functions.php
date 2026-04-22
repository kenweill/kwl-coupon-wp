<?php
/**
 * KWL Coupon WP — Template Functions
 *
 * Shared helper functions used across template files.
 * Includes pagination, empty states, and other display utilities.
 *
 * @package KWL_Coupon_WP
 */

defined( 'ABSPATH' ) || exit;


/**
 * Render pagination for archive pages.
 *
 * Uses the global $wp_query by default, or accepts a custom query.
 *
 * @param WP_Query|null $query  Optional custom query. Defaults to global.
 */
function kwl_render_pagination( ?WP_Query $query = null ): void {

    global $wp_query;
    $q        = $query ?? $wp_query;
    $total    = (int) $q->max_num_pages;
    $paged    = max( 1, get_query_var( 'paged' ) );

    if ( $total <= 1 ) {
        return;
    }

    $links = paginate_links( [
        'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
        'format'    => '?paged=%#%',
        'current'   => $paged,
        'total'     => $total,
        'prev_text' => '&larr;',
        'next_text' => '&rarr;',
        'type'      => 'array',
        'end_size'  => 2,
        'mid_size'  => 1,
    ] );

    if ( empty( $links ) ) {
        return;
    }

    echo '<nav class="cwp-pagination" aria-label="' . esc_attr__( 'Pagination', 'kwl-coupon-wp' ) . '">';

    foreach ( $links as $link ) {
        if ( strpos( $link, 'current' ) !== false ) {
            // Current page
            echo str_replace( 'page-numbers current', 'cwp-pagination__item cwp-pagination__item--active', $link ); // phpcs:ignore
        } elseif ( strpos( $link, 'dots' ) !== false ) {
            echo '<span class="cwp-pagination__dots">&hellip;</span>';
        } else {
            echo str_replace( 'page-numbers', 'cwp-pagination__item', $link ); // phpcs:ignore
        }
    }

    echo '</nav>';

}


/**
 * Render the coupon filter tabs CSS + JS inline.
 *
 * Filter tabs on single store page filter by type attribute.
 * Pure CSS/vanilla JS — no framework.
 */
function kwl_render_filter_tab_styles(): void {
    ?>
    <style>
        .cwp-filter-tabs {
            display: flex;
            gap: var(--cwp-space-2);
            flex-wrap: wrap;
            margin-bottom: var(--cwp-space-5);
            border-bottom: 1px solid var(--cwp-border);
            padding-bottom: var(--cwp-space-3);
        }
        .cwp-filter-tab {
            padding: var(--cwp-space-2) var(--cwp-space-4);
            border-radius: var(--cwp-radius-full);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--cwp-text-muted);
            border: 1px solid var(--cwp-border);
            background: var(--cwp-surface);
            cursor: pointer;
            transition: all var(--cwp-transition);
        }
        .cwp-filter-tab:hover {
            border-color: var(--cwp-primary);
            color: var(--cwp-primary);
        }
        .cwp-filter-tab.active {
            background: var(--cwp-primary);
            border-color: var(--cwp-primary);
            color: #fff;
        }
        .cwp-az-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: var(--cwp-space-6);
        }
        .cwp-az-filter__item {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 6px;
            border-radius: var(--cwp-radius-sm);
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--cwp-text-muted);
            border: 1px solid var(--cwp-border);
            background: var(--cwp-surface);
            text-decoration: none;
            transition: all var(--cwp-transition);
        }
        .cwp-az-filter__item:hover,
        .cwp-az-filter__item.active {
            background: var(--cwp-primary);
            border-color: var(--cwp-primary);
            color: #fff;
        }
        .cwp-archive-header {
            margin-bottom: var(--cwp-space-6);
        }
        .cwp-archive-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: var(--cwp-space-2);
        }
        .cwp-archive-count {
            font-size: 1rem;
            font-weight: 400;
            color: var(--cwp-text-muted);
        }
        .cwp-archive-description {
            color: var(--cwp-text-muted);
        }
        .cwp-empty-state {
            text-align: center;
            padding: var(--cwp-space-16) var(--cwp-space-8);
            background: var(--cwp-surface);
            border: 1px solid var(--cwp-border);
            border-radius: var(--cwp-radius-md);
        }
        .cwp-empty-state p {
            color: var(--cwp-text-muted);
            margin-bottom: var(--cwp-space-5);
        }
        .cwp-store-faq {
            margin-top: var(--cwp-space-8);
            border-top: 1px solid var(--cwp-border);
            padding-top: var(--cwp-space-8);
        }
        .cwp-store-faq h2 {
            font-size: 1.25rem;
            margin-bottom: var(--cwp-space-5);
        }
        .cwp-faq-item {
            border: 1px solid var(--cwp-border);
            border-radius: var(--cwp-radius);
            margin-bottom: var(--cwp-space-3);
            overflow: hidden;
        }
        .cwp-faq-question {
            padding: var(--cwp-space-4) var(--cwp-space-5);
            font-weight: 600;
            cursor: pointer;
            list-style: none;
            background: var(--cwp-surface);
            transition: background var(--cwp-transition);
        }
        .cwp-faq-question:hover { background: var(--cwp-surface-alt); }
        .cwp-faq-question::-webkit-details-marker { display: none; }
        .cwp-faq-answer {
            padding: var(--cwp-space-4) var(--cwp-space-5);
            color: var(--cwp-text-muted);
            border-top: 1px solid var(--cwp-border);
            background: var(--cwp-surface-alt);
        }
        .cwp-store-description {
            margin-top: var(--cwp-space-8);
            border-top: 1px solid var(--cwp-border);
            padding-top: var(--cwp-space-8);
        }
        .cwp-prose { line-height: 1.7; color: var(--cwp-text-muted); }
        .cwp-prose h2, .cwp-prose h3 { color: var(--cwp-text); margin: 1.5em 0 0.5em; }
        .cwp-prose p { margin-bottom: 1em; }
        .cwp-hero__stats {
            color: rgba(255,255,255,0.75);
            font-size: 0.9375rem;
            margin-top: var(--cwp-space-4);
        }
        .cwp-hero__stats strong { color: #fff; }
        .cwp-mobile-search {
            border-top: 1px solid var(--cwp-border);
            padding: var(--cwp-space-3) 0;
            background: var(--cwp-surface);
        }
        .cwp-search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--cwp-surface);
            border: 1px solid var(--cwp-border);
            border-radius: var(--cwp-radius-md);
            box-shadow: var(--cwp-shadow-lg);
            z-index: var(--cwp-z-dropdown);
            max-height: 360px;
            overflow-y: auto;
            margin-top: 4px;
        }
        .cwp-search-result {
            display: flex;
            align-items: center;
            gap: var(--cwp-space-3);
            padding: var(--cwp-space-3) var(--cwp-space-4);
            text-decoration: none;
            color: var(--cwp-text);
            transition: background var(--cwp-transition);
            border-bottom: 1px solid var(--cwp-border);
        }
        .cwp-search-result:last-child { border-bottom: none; }
        .cwp-search-result:hover { background: var(--cwp-surface-alt); }
        .cwp-search-result__logo { width:32px; height:32px; border-radius:4px; object-fit:contain; }
        .cwp-search-result__name { font-weight:600; font-size:0.9rem; }
        .cwp-search-result__meta { font-size:0.8rem; color:var(--cwp-text-muted); }
        .cwp-footer__social { display:flex; gap:var(--cwp-space-3); margin-top:var(--cwp-space-4); flex-wrap:wrap; }
        .cwp-footer__social-link { color:rgba(255,255,255,0.6); transition:color var(--cwp-transition); }
        .cwp-footer__social-link:hover { color:#fff; }
        /* Widget inner body div */
        .cwp-widget .cwp-widget__body { padding: var(--cwp-space-4) var(--cwp-space-5); }
        .kwl-widget-stores { margin:0; padding:0; }
        .kwl-widget-store { border-bottom:1px solid var(--cwp-border); }
        .kwl-widget-store:last-child { border-bottom:none; }
        .kwl-widget-store__link { display:flex; align-items:center; gap:var(--cwp-space-3); padding:var(--cwp-space-2) 0; text-decoration:none; color:var(--cwp-text); }
        .kwl-widget-store__link:hover { color:var(--cwp-primary); }
        .kwl-widget-store__logo { border-radius:4px; object-fit:contain; }
        .kwl-widget-store__initial { width:32px;height:32px;border-radius:4px;background:var(--cwp-primary-light);color:var(--cwp-primary);display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0; }
        .kwl-widget-store__name { font-weight:600; font-size:0.875rem; flex:1; }
        .kwl-widget-store__count { font-size:0.75rem; color:var(--cwp-text-muted); }
        .kwl-widget-categories { margin:0; padding:0; }
        .kwl-widget-categories li { border-bottom:1px solid var(--cwp-border); }
        .kwl-widget-categories li:last-child { border-bottom:none; }
        .kwl-widget-categories a { display:flex;align-items:center;gap:var(--cwp-space-2);padding:var(--cwp-space-2) 0;text-decoration:none;color:var(--cwp-text);font-size:0.875rem;font-weight:500; }
        .kwl-widget-categories a:hover { color:var(--cwp-primary); }
        .kwl-widget-cat__icon { font-size:1.1rem; }
        .kwl-widget-cat__name { flex:1; }
        .kwl-widget-coupons { margin:0; padding:0; }
        .kwl-widget-coupon { border-bottom:1px solid var(--cwp-border); padding:var(--cwp-space-3) 0; }
        .kwl-widget-coupon:last-child { border-bottom:none; }
        .kwl-widget-coupon a { text-decoration:none; color:var(--cwp-text); display:block; }
        .kwl-widget-coupon a:hover { color:var(--cwp-primary); }
        .kwl-widget-coupon__discount { color:var(--cwp-primary); display:block; font-size:0.875rem; }
        .kwl-widget-coupon__title { font-size:0.8125rem; color:var(--cwp-text-muted); display:block; }
        .kwl-widget-coupon__store { font-size:0.75rem; color:var(--cwp-text-light); display:block; margin-top:2px; }
        .kwl-widget-coupon__code { font-family:var(--cwp-font-mono);font-size:0.75rem;background:var(--cwp-primary-light);color:var(--cwp-primary);padding:1px 6px;border-radius:4px;margin-top:2px;display:inline-block; }
        /* Breadcrumbs */
        .cwp-breadcrumbs { margin: var(--cwp-space-4) 0; }
        .cwp-breadcrumbs__list { display:flex; flex-wrap:wrap; gap:var(--cwp-space-1); align-items:center; padding:0; margin:0; list-style:none; }
        .cwp-breadcrumbs__item { display:flex; align-items:center; font-size:0.8125rem; color:var(--cwp-text-muted); }
        .cwp-breadcrumbs__item + .cwp-breadcrumbs__item::before { content:'/'; margin-right:var(--cwp-space-1); color:var(--cwp-text-light); }
        .cwp-breadcrumbs__link { color:var(--cwp-text-muted); text-decoration:none; }
        .cwp-breadcrumbs__link:hover { color:var(--cwp-primary); }
        .cwp-breadcrumbs__current { color:var(--cwp-text); font-weight:500; }
        /* Coupon card discount label */
        .cwp-coupon-card__discount { font-size:1.0625rem; font-weight:700; color:var(--cwp-primary); margin-bottom:var(--cwp-space-1); }
    </style>
    <?php
}
add_action( 'wp_head', 'kwl_render_filter_tab_styles', 20 );
