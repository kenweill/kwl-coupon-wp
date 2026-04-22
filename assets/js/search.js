/**
 * KWL Coupon WP — Live Search Autocomplete
 *
 * Attaches to the header search and hero search inputs.
 * Fires AJAX after the user stops typing (debounced 300ms).
 * Shows stores and coupons in a dropdown without page reload.
 *
 * @package KWL_Coupon_WP
 */

( function () {
    'use strict';

    const { ajaxUrl, nonce } = window.kwlData || {};

    /**
     * Debounce — delays a function call until N ms after last invocation.
     *
     * @param {Function} fn
     * @param {number}   delay
     * @returns {Function}
     */
    function debounce( fn, delay ) {
        let timer;
        return ( ...args ) => {
            clearTimeout( timer );
            timer = setTimeout( () => fn( ...args ), delay );
        };
    }

    /**
     * Fetch search results from WordPress AJAX.
     *
     * @param {string} query
     * @returns {Promise<Object>}
     */
    async function fetchResults( query ) {
        const body = new URLSearchParams( {
            action: 'kwl_live_search',
            nonce,
            q: query,
        } );
        const res = await fetch( ajaxUrl, { method: 'POST', body } );
        if ( ! res.ok ) throw new Error( `HTTP ${ res.status }` );
        return res.json();
    }

    /**
     * Render results into a dropdown element.
     *
     * @param {HTMLElement} dropdown
     * @param {Object}      data     — { stores: [], coupons: [] }
     */
    function renderDropdown( dropdown, data ) {
        dropdown.innerHTML = '';

        const { stores = [], coupons = [] } = data;

        if ( stores.length === 0 && coupons.length === 0 ) {
            const empty = document.createElement( 'div' );
            empty.className = 'cwp-search-result';
            empty.textContent = 'No results found.';
            empty.style.color = 'var(--cwp-text-muted)';
            empty.style.fontStyle = 'italic';
            dropdown.appendChild( empty );
            dropdown.hidden = false;
            return;
        }

        // Store results
        if ( stores.length > 0 ) {
            const header = document.createElement( 'div' );
            header.style.cssText = 'padding:8px 16px 4px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--cwp-text-muted);';
            header.textContent = 'Stores';
            dropdown.appendChild( header );

            stores.forEach( store => {
                const link = document.createElement( 'a' );
                link.href      = store.url;
                link.className = 'cwp-search-result';

                link.innerHTML = `
                    ${ store.logo
                        ? `<img src="${ escHtml( store.logo ) }" alt="" class="cwp-search-result__logo" width="32" height="32" loading="lazy">`
                        : `<div class="cwp-search-result__logo" style="background:var(--cwp-primary-light);border-radius:4px;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--cwp-primary);">${ escHtml( store.initial ) }</div>`
                    }
                    <div>
                        <div class="cwp-search-result__name">${ escHtml( store.name ) }</div>
                        <div class="cwp-search-result__meta">${ store.coupon_count } coupon${ store.coupon_count !== 1 ? 's' : '' }</div>
                    </div>
                `;
                dropdown.appendChild( link );
            } );
        }

        // Coupon results
        if ( coupons.length > 0 ) {
            const header = document.createElement( 'div' );
            header.style.cssText = 'padding:8px 16px 4px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--cwp-text-muted); border-top:1px solid var(--cwp-border); margin-top:4px;';
            header.textContent = 'Coupons';
            dropdown.appendChild( header );

            coupons.forEach( coupon => {
                const link = document.createElement( 'a' );
                link.href      = coupon.url;
                link.className = 'cwp-search-result';

                link.innerHTML = `
                    <div style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;" aria-hidden="true">🏷️</div>
                    <div>
                        <div class="cwp-search-result__name">${ escHtml( coupon.title ) }</div>
                        <div class="cwp-search-result__meta">${ escHtml( coupon.store ) }${ coupon.discount ? ' · ' + escHtml( coupon.discount ) : '' }</div>
                    </div>
                `;
                dropdown.appendChild( link );
            } );
        }

        dropdown.hidden = false;
    }

    /**
     * Minimal HTML escape for user-visible text in innerHTML.
     *
     * @param {string} str
     * @returns {string}
     */
    function escHtml( str ) {
        if ( ! str ) return '';
        return String( str )
            .replace( /&/g, '&amp;' )
            .replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' )
            .replace( /"/g, '&quot;' );
    }

    /**
     * Attach live search behaviour to a search input + dropdown pair.
     *
     * @param {HTMLInputElement} input
     * @param {HTMLElement}      dropdown
     */
    function attachSearch( input, dropdown ) {
        if ( ! input || ! dropdown ) return;

        let lastQuery = '';

        const doSearch = debounce( async ( query ) => {
            query = query.trim();

            if ( query.length < 2 ) {
                dropdown.hidden = true;
                dropdown.innerHTML = '';
                lastQuery = '';
                return;
            }

            if ( query === lastQuery ) return;
            lastQuery = query;

            try {
                const resp = await fetchResults( query );
                if ( resp.success && query === lastQuery ) {
                    renderDropdown( dropdown, resp.data );
                }
            } catch ( err ) {
                console.error( 'KWL search error:', err );
                dropdown.hidden = true;
            }
        }, 300 );

        input.addEventListener( 'input', ( e ) => doSearch( e.target.value ) );

        // Keyboard navigation
        input.addEventListener( 'keydown', ( e ) => {
            if ( dropdown.hidden ) return;

            const links = [ ...dropdown.querySelectorAll( 'a' ) ];
            const focused = document.activeElement;
            const idx = links.indexOf( focused );

            if ( e.key === 'ArrowDown' ) {
                e.preventDefault();
                links[ idx + 1 ]?.focus() ?? links[ 0 ]?.focus();
            } else if ( e.key === 'ArrowUp' ) {
                e.preventDefault();
                idx <= 0 ? input.focus() : links[ idx - 1 ]?.focus();
            } else if ( e.key === 'Escape' ) {
                dropdown.hidden = true;
                input.focus();
            }
        } );

        // Close on blur (with small delay for link clicks)
        input.addEventListener( 'blur', () => {
            setTimeout( () => {
                if ( ! dropdown.contains( document.activeElement ) ) {
                    dropdown.hidden = true;
                }
            }, 150 );
        } );
    }

    // Attach to header search
    attachSearch(
        document.getElementById( 'kwl-header-search' ),
        document.getElementById( 'kwl-search-results' )
    );

    // Attach to hero search
    attachSearch(
        document.getElementById( 'kwl-hero-search' ),
        document.getElementById( 'kwl-hero-results' )
    );

} )();


/* =============================================================================
   AJAX HANDLER (PHP side — registered in functions.php or coupon-functions.php)

   This comment block documents the PHP AJAX handler that search.js calls.
   Add this to inc/coupon-functions.php:

   add_action( 'wp_ajax_kwl_live_search',        'kwl_ajax_live_search' );
   add_action( 'wp_ajax_nopriv_kwl_live_search', 'kwl_ajax_live_search' );

   function kwl_ajax_live_search(): void {
       check_ajax_referer( 'kwl_nonce', 'nonce' );

       $query = sanitize_text_field( $_POST['q'] ?? '' );
       if ( strlen( $query ) < 2 ) {
           wp_send_json_success( [ 'stores' => [], 'coupons' => [] ] );
       }

       // Search stores
       $store_query = new WP_Query( [
           'post_type'      => 'kwl_store',
           'post_status'    => 'publish',
           'posts_per_page' => 5,
           's'              => $query,
       ] );

       $stores = [];
       foreach ( $store_query->posts as $post ) {
           $logo_url = kwl_get_store_logo_url( $post->ID, 'kwl-store-logo-sm' );
           $stores[] = [
               'name'         => $post->post_title,
               'url'          => get_permalink( $post->ID ),
               'logo'         => $logo_url,
               'initial'      => mb_strtoupper( mb_substr( $post->post_title, 0, 1 ) ),
               'coupon_count' => kwl_get_store_coupon_count( $post->ID ),
           ];
       }

       // Search coupons
       $coupon_query = new WP_Query( [
           'post_type'      => 'kwl_coupon',
           'post_status'    => 'publish',
           'posts_per_page' => 5,
           's'              => $query,
       ] );

       $coupons = [];
       foreach ( $coupon_query->posts as $post ) {
           $store_id = kwl_get_coupon_store_id( $post->ID );
           $coupons[] = [
               'title'    => $post->post_title,
               'url'      => get_permalink( $post->ID ),
               'store'    => $store_id ? get_the_title( $store_id ) : '',
               'discount' => kwl_get_coupon_discount( $post->ID ),
           ];
       }

       wp_send_json_success( compact( 'stores', 'coupons' ) );
   }
   ============================================================================= */
