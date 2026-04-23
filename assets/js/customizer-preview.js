/**
 * KWL Coupon WP — Customizer Live Preview
 *
 * Handles postMessage transport for the WordPress Customizer.
 * Updates CSS variables and text content in the preview iframe
 * instantly without a full page reload.
 *
 * Each wp.customize() call corresponds to a setting registered
 * in inc/customizer.php with transport: 'postMessage'.
 *
 * @package KWL_Coupon_WP
 */

/* global wp */

( function ( $ ) {
    'use strict';

    const { customize } = wp;


    /* =========================================================================
       HELPER — Update a CSS custom property on :root
       ========================================================================= */

    /**
     * Set a CSS variable on the document root.
     *
     * @param {string} varName  e.g. '--cwp-primary'
     * @param {string} value    e.g. '#2563eb'
     */
    function setCSSVar( varName, value ) {
        document.documentElement.style.setProperty( varName, value );
    }


    /* =========================================================================
       COLOR SETTINGS
       ========================================================================= */

    const colorMap = {
        kwl_color_primary : '--cwp-primary',
        kwl_color_accent  : '--cwp-accent',
        kwl_color_bg      : '--cwp-bg',
        kwl_color_surface : '--cwp-surface',
        kwl_color_text    : '--cwp-text',
        kwl_color_nav_bg  : '--cwp-nav-bg',
    };

    Object.entries( colorMap ).forEach( ( [ settingId, cssVar ] ) => {
        customize( settingId, function ( value ) {
            value.bind( function ( newVal ) {
                setCSSVar( cssVar, newVal );
            } );
        } );
    } );


    /* =========================================================================
       FOOTER BACKGROUND
       ========================================================================= */

    customize( 'kwl_footer_bg', function ( value ) {
        value.bind( function ( newVal ) {
            setCSSVar( '--cwp-footer-bg', newVal );
            const footer = document.querySelector( '.cwp-footer' );
            if ( footer ) footer.style.background = newVal;
        } );
    } );


    /* =========================================================================
       TYPOGRAPHY
       ========================================================================= */

    // Border radius
    customize( 'kwl_border_radius', function ( value ) {
        value.bind( function ( newVal ) {
            setCSSVar( '--cwp-radius', newVal );
            // Also update derived radii
            const radiusMap = {
                '0px' : { sm: '0px',  md: '0px',  lg: '0px'  },
                '4px' : { sm: '2px',  md: '6px',  lg: '8px'  },
                '8px' : { sm: '4px',  md: '12px', lg: '16px' },
                '12px': { sm: '6px',  md: '14px', lg: '18px' },
                '16px': { sm: '8px',  md: '16px', lg: '20px' },
            };
            const derived = radiusMap[ newVal ] || {};
            if ( derived.sm ) setCSSVar( '--cwp-radius-sm', derived.sm );
            if ( derived.md ) setCSSVar( '--cwp-radius-md', derived.md );
            if ( derived.lg ) setCSSVar( '--cwp-radius-lg', derived.lg );
        } );
    } );

    // Base font size
    customize( 'kwl_font_size_base', function ( value ) {
        value.bind( function ( newVal ) {
            setCSSVar( '--cwp-font-size-base', newVal + 'px' );
        } );
    } );

    // Font family
    customize( 'kwl_font_family', function ( value ) {
        value.bind( function ( newVal ) {
            const fontMap = {
                figtree      : "'Figtree', system-ui, sans-serif",
                inter        : "'Inter', system-ui, sans-serif",
                manrope      : "'Manrope', system-ui, sans-serif",
                nunito       : "'Nunito', system-ui, sans-serif",
                poppins      : "'Poppins', system-ui, sans-serif",
                'plus-jakarta': "'Plus Jakarta Sans', system-ui, sans-serif",
                system       : "system-ui, -apple-system, sans-serif",
            };
            if ( fontMap[ newVal ] ) {
                setCSSVar( '--cwp-font', fontMap[ newVal ] );
            }
        } );
    } );


    /* =========================================================================
       HEADER SETTINGS
       ========================================================================= */

    // Header search visibility
    customize( 'kwl_header_search', function ( value ) {
        value.bind( function ( newVal ) {
            const search = document.querySelector( '.cwp-header__search' );
            if ( search ) search.style.display = newVal ? '' : 'none';
        } );
    } );

    // Nav bar visibility
    customize( 'kwl_show_nav', function ( value ) {
        value.bind( function ( newVal ) {
            const nav = document.getElementById( 'kwl-primary-nav' );
            if ( nav ) nav.style.display = newVal ? '' : 'none';
        } );
    } );


    /* =========================================================================
       FOOTER SETTINGS
       ========================================================================= */

    // Footer brand description
    customize( 'kwl_footer_description', function ( value ) {
        value.bind( function ( newVal ) {
            const desc = document.querySelector( '.cwp-footer__description' );
            if ( desc ) desc.textContent = newVal;
        } );
    } );

    // Footer copyright text
    customize( 'kwl_footer_copyright', function ( value ) {
        value.bind( function ( newVal ) {
            const copy = document.querySelector( '.cwp-footer__copy' );
            if ( copy ) {
                const year = new Date().getFullYear();
                const site = document.title.split( '—' )[0]?.trim() || '';
                copy.textContent = newVal
                    .replace( '{year}', year )
                    .replace( '{site}', site );
            }
        } );
    } );


    /* =========================================================================
       SITE IDENTITY (handled by WordPress core Customizer, but we sync logo)
       ========================================================================= */

    // Site title (for text-based logo fallback)
    customize( 'blogname', function ( value ) {
        value.bind( function ( newVal ) {
            const logoText = document.querySelector( '.cwp-logo__text' );
            if ( logoText ) {
                const parts = newVal.split( ' ' );
                if ( parts.length > 1 ) {
                    logoText.innerHTML = parts[0] + '<span>' + parts.slice(1).join( ' ' ) + '</span>';
                } else {
                    logoText.textContent = newVal;
                }
            }
        } );
    } );

    // Site tagline
    customize( 'blogdescription', function ( value ) {
        value.bind( function ( newVal ) {
            const tagline = document.querySelector( '.cwp-site-tagline' );
            if ( tagline ) tagline.textContent = newVal;
        } );
    } );

} )( jQuery );
