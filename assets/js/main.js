/**
 * KWL Coupon WP — Main Frontend JavaScript
 *
 * Handles:
 * - Coupon code reveal (AJAX)
 * - Clipboard copy
 * - Vote buttons (works / doesn't work)
 * - Mobile menu toggle
 * - Mobile search toggle
 * - Coupon type filter tabs (store page)
 * - Cookie-based vote duplicate prevention
 *
 * Zero jQuery. Pure vanilla JS (ES2020+).
 * All strings come from kwlData (wp_localize_script).
 *
 * @package KWL_Coupon_WP
 */

( function () {
    'use strict';

    const { ajaxUrl, nonce, strings } = window.kwlData || {};


    /* =========================================================================
       UTILS
       ========================================================================= */

    /**
     * Make an AJAX POST request.
     *
     * @param {string} action  WordPress AJAX action.
     * @param {Object} data    Additional POST data.
     * @returns {Promise<Object>}
     */
    async function ajaxPost( action, data = {} ) {
        const body = new URLSearchParams( { action, nonce, ...data } );
        const res  = await fetch( ajaxUrl, { method: 'POST', body } );
        if ( ! res.ok ) throw new Error( `HTTP ${ res.status }` );
        return res.json();
    }

    /**
     * Set a cookie.
     *
     * @param {string} name
     * @param {string} value
     * @param {number} days
     */
    function setCookie( name, value, days = 365 ) {
        const expires = new Date( Date.now() + days * 864e5 ).toUTCString();
        document.cookie = `${ name }=${ value }; expires=${ expires }; path=/; SameSite=Lax`;
    }

    /**
     * Get a cookie value.
     *
     * @param {string} name
     * @returns {string|null}
     */
    function getCookie( name ) {
        const match = document.cookie.match( new RegExp( `(?:^|; )${ name }=([^;]*)` ) );
        return match ? decodeURIComponent( match[1] ) : null;
    }

    /**
     * Copy text to clipboard.
     *
     * @param {string} text
     * @returns {Promise<boolean>}
     */
    async function copyToClipboard( text ) {
        try {
            if ( navigator.clipboard && window.isSecureContext ) {
                await navigator.clipboard.writeText( text );
                return true;
            }
            // Fallback for non-HTTPS
            const textarea = document.createElement( 'textarea' );
            textarea.value = text;
            textarea.style.cssText = 'position:fixed;left:-9999px;top:-9999px;';
            document.body.appendChild( textarea );
            textarea.select();
            document.execCommand( 'copy' );
            document.body.removeChild( textarea );
            return true;
        } catch {
            return false;
        }
    }

    /**
     * Show a temporary status message on a button.
     *
     * @param {HTMLElement} el       Target element.
     * @param {string}      message  Text to show.
     * @param {number}      duration Milliseconds to show. Default 2000.
     */
    function flashMessage( el, message, duration = 2000 ) {
        const original = el.textContent;
        el.textContent = message;
        el.setAttribute( 'aria-live', 'polite' );
        setTimeout( () => { el.textContent = original; }, duration );
    }


    /* =========================================================================
       COUPON REVEAL — CARD (archive/store pages)
       ========================================================================= */

    /**
     * Handle click on reveal button in a coupon card.
     * Uses event delegation on document to cover dynamically added cards.
     */
    document.addEventListener( 'click', async function ( e ) {

        const btn = e.target.closest( '.cwp-btn-reveal[data-action="reveal"]' );
        if ( ! btn ) return;

        e.preventDefault();

        const couponId     = btn.dataset.couponId;
        const affiliateUrl = btn.dataset.affiliate;

        if ( ! couponId ) return;

        // Already revealed — copy again + open affiliate on second click
        if ( btn.classList.contains( 'cwp-btn-reveal--revealed' ) ) {
            const code = btn.dataset.code || '';
            if ( code ) {
                await copyToClipboard( code );
                flashMessage( btn, window.kwlData?.copiedText || 'Copied!' );
            }
            if ( affiliateUrl ) {
                window.open( affiliateUrl, '_blank', 'noopener,noreferrer' );
            }
            return;
        }

        // Open affiliate window BEFORE async call to avoid popup blocker.
        // Browsers only allow window.open() in direct response to a user gesture.
        // We open it immediately, then set its URL once we have it from AJAX.
        const affiliateWindow = affiliateUrl
            ? window.open( 'about:blank', '_blank', 'noopener,noreferrer' )
            : null;

        // Set loading state
        btn.disabled    = true;
        btn.textContent = '…';

        try {
            const resp = await ajaxPost( 'kwl_reveal_coupon', { coupon_id: couponId } );

            if ( ! resp.success ) {
                btn.disabled    = false;
                btn.textContent = window.kwlData?.revealText || 'Show Code';
                if ( affiliateWindow ) affiliateWindow.close();
                return;
            }

            const { code, affiliate_url } = resp.data;
            const dest = affiliate_url || affiliateUrl;

            if ( code ) {
                // Show code on button
                btn.textContent = code;
                btn.dataset.code = code;
                btn.classList.add( 'cwp-btn-reveal--revealed' );
                btn.disabled = false;
                btn.setAttribute( 'aria-label', `${ window.kwlData?.copyText || 'Copy' }: ${ code }` );

                // Copy to clipboard
                await copyToClipboard( code );

                // Navigate the pre-opened window to affiliate URL
                if ( affiliateWindow && dest ) {
                    affiliateWindow.location.href = dest;
                } else if ( dest ) {
                    window.open( dest, '_blank', 'noopener,noreferrer' );
                }

            } else {
                // Deal type — no code, just navigate
                btn.disabled    = false;
                btn.textContent = window.kwlData?.getDealText || 'Get Deal';
                if ( affiliateWindow && dest ) {
                    affiliateWindow.location.href = dest;
                } else if ( dest ) {
                    window.open( dest, '_blank', 'noopener,noreferrer' );
                }
            }

        } catch ( err ) {
            btn.disabled    = false;
            btn.textContent = window.kwlData?.revealText || 'Show Code';
            if ( affiliateWindow ) affiliateWindow.close();
            console.error( 'KWL reveal error:', err );
        }

    } );


    /* =========================================================================
       COUPON REVEAL — SINGLE PAGE
       ========================================================================= */

    const revealBtn   = document.getElementById( 'kwl-reveal-btn' );
    const codePreview = document.getElementById( 'kwl-code-preview' );
    const codeReveal  = document.getElementById( 'kwl-code-revealed' );
    const codeText    = document.getElementById( 'kwl-code-text' );
    const copyBtn     = document.getElementById( 'kwl-copy-btn' );

    if ( revealBtn ) {

        revealBtn.addEventListener( 'click', async function () {

            const couponId     = revealBtn.dataset.couponId;
            const affiliateUrl = revealBtn.dataset.affiliate;

            // Open affiliate window immediately (before async) to avoid popup blocker
            const affiliateWindow = affiliateUrl
                ? window.open( 'about:blank', '_blank', 'noopener,noreferrer' )
                : null;

            revealBtn.disabled    = true;
            revealBtn.textContent = '…';

            try {
                const resp = await ajaxPost( 'kwl_reveal_coupon', { coupon_id: couponId } );

                if ( ! resp.success ) {
                    revealBtn.disabled    = false;
                    revealBtn.textContent = window.kwlData?.revealText || 'Show Code';
                    if ( affiliateWindow ) affiliateWindow.close();
                    return;
                }

                const { code, affiliate_url } = resp.data;
                const dest = affiliate_url || affiliateUrl;

                // Show the revealed UI
                if ( codePreview ) codePreview.hidden = true;
                revealBtn.hidden = true;

                if ( codeReveal && codeText ) {
                    codeText.textContent = code || '';
                    codeReveal.hidden    = false;
                }

                // Copy to clipboard
                if ( code ) {
                    await copyToClipboard( code );
                }

                // Navigate pre-opened window to affiliate URL
                if ( affiliateWindow && dest ) {
                    affiliateWindow.location.href = dest;
                } else if ( dest ) {
                    window.open( dest, '_blank', 'noopener,noreferrer' );
                }

            } catch ( err ) {
                revealBtn.disabled    = false;
                revealBtn.textContent = window.kwlData?.revealText || 'Show Code';
                if ( affiliateWindow ) affiliateWindow.close();
                console.error( 'KWL reveal error:', err );
            }

        } );

    }

    // Copy button on single coupon page
    if ( copyBtn && codeText ) {
        copyBtn.addEventListener( 'click', async function () {
            const code = codeText.textContent;
            const ok   = await copyToClipboard( code );
            if ( ok ) {
                flashMessage( copyBtn, window.kwlData?.copiedText || 'Copied!' );
            }
        } );
    }


    /* =========================================================================
       VOTE BUTTONS
       ========================================================================= */

    document.addEventListener( 'click', async function ( e ) {

        const btn = e.target.closest( '.cwp-vote-btn[data-vote]' );
        if ( ! btn ) return;

        e.preventDefault();

        const couponId = btn.dataset.couponId;
        const vote     = btn.dataset.vote;
        const feedback = document.getElementById( 'kwl-vote-feedback' );

        if ( ! couponId || ! vote ) return;

        // Check cookie for duplicate vote
        const cookieKey = `kwl_voted_${ couponId }`;
        if ( getCookie( cookieKey ) ) {
            if ( feedback ) {
                feedback.textContent = strings?.voteDuplicate || 'You already voted.';
                feedback.hidden = false;
            }
            return;
        }

        // Disable both vote buttons
        const allVoteBtns = document.querySelectorAll( `[data-coupon-id="${ couponId }"].cwp-vote-btn` );
        allVoteBtns.forEach( b => b.disabled = true );

        try {
            const resp = await ajaxPost( 'kwl_vote_coupon', { coupon_id: couponId, vote } );

            if ( resp.success ) {
                // Mark as voted
                setCookie( cookieKey, vote, 365 );
                btn.classList.add( 'cwp-vote-btn--active' );

                // Update counts
                const { votes_up, votes_down } = resp.data;
                const upBtn   = document.querySelector( `[data-coupon-id="${ couponId }"][data-vote="up"] .cwp-vote-count` );
                const downBtn = document.querySelector( `[data-coupon-id="${ couponId }"][data-vote="down"] .cwp-vote-count` );
                if ( upBtn )   upBtn.textContent   = votes_up   > 0 ? `(${ votes_up })`   : '';
                if ( downBtn ) downBtn.textContent = votes_down > 0 ? `(${ votes_down })` : '';

                if ( feedback ) {
                    feedback.textContent = strings?.voteSuccess || 'Thanks for your feedback!';
                    feedback.hidden = false;
                }

            } else if ( resp.data?.duplicate ) {
                setCookie( cookieKey, vote, 365 );
                if ( feedback ) {
                    feedback.textContent = strings?.voteDuplicate || 'You already voted.';
                    feedback.hidden = false;
                }
                allVoteBtns.forEach( b => b.disabled = false );
            } else {
                allVoteBtns.forEach( b => b.disabled = false );
                if ( feedback ) {
                    feedback.textContent = strings?.voteError || 'Something went wrong.';
                    feedback.hidden = false;
                }
            }

        } catch ( err ) {
            allVoteBtns.forEach( b => b.disabled = false );
            console.error( 'KWL vote error:', err );
        }

    } );


    /* =========================================================================
       MOBILE MENU TOGGLE
       ========================================================================= */

    const navToggle = document.querySelector( '.cwp-nav-toggle' );
    const primaryNav = document.getElementById( 'kwl-primary-nav' );

    if ( navToggle && primaryNav ) {
        navToggle.addEventListener( 'click', function () {
            const isOpen = primaryNav.style.display === 'block';
            primaryNav.style.display = isOpen ? '' : 'block';
            navToggle.setAttribute( 'aria-expanded', String( ! isOpen ) );
        } );

        // Close on outside click
        document.addEventListener( 'click', function ( e ) {
            if (
                primaryNav.style.display === 'block' &&
                ! primaryNav.contains( e.target ) &&
                ! navToggle.contains( e.target )
            ) {
                primaryNav.style.display = '';
                navToggle.setAttribute( 'aria-expanded', 'false' );
            }
        } );
    }


    /* =========================================================================
       MOBILE SEARCH TOGGLE
       ========================================================================= */

    const searchToggle  = document.querySelector( '.cwp-search-toggle' );
    const mobileSearch  = document.getElementById( 'kwl-mobile-search' );

    if ( searchToggle && mobileSearch ) {
        searchToggle.addEventListener( 'click', function () {
            const isOpen = ! mobileSearch.hidden;
            mobileSearch.hidden = isOpen;
            searchToggle.setAttribute( 'aria-expanded', String( ! isOpen ) );

            if ( ! isOpen ) {
                // Focus the input when opened
                const input = mobileSearch.querySelector( 'input[type="search"]' );
                if ( input ) input.focus();
            }
        } );
    }


    /* =========================================================================
       COUPON TYPE FILTER TABS (Store page)
       ========================================================================= */

    const filterTabs     = document.querySelectorAll( '.cwp-filter-tab[data-filter]' );
    const couponsWrapper = document.getElementById( 'kwl-store-coupons' );

    if ( filterTabs.length && couponsWrapper ) {

        filterTabs.forEach( tab => {
            tab.addEventListener( 'click', function () {
                const filter = this.dataset.filter;

                // Update active tab
                filterTabs.forEach( t => {
                    t.classList.remove( 'active' );
                    t.setAttribute( 'aria-selected', 'false' );
                } );
                this.classList.add( 'active' );
                this.setAttribute( 'aria-selected', 'true' );

                // Show/hide coupon cards
                const cards = couponsWrapper.querySelectorAll( '.cwp-coupon-card' );
                cards.forEach( card => {
                    if ( filter === 'all' ) {
                        card.style.display = '';
                    } else {
                        // The card has data-coupon-id; we check its type badge
                        // For type detection, look for the type badge or use a data attr
                        const hasBadge = card.querySelector( `.cwp-badge--${ filter }` );
                        const typeMatch = hasBadge ||
                            ( filter === 'code' && ! card.querySelector( '.cwp-btn-deal' ) && card.querySelector( '.cwp-btn-reveal' ) ) ||
                            ( filter === 'deal' && card.querySelector( '.cwp-btn-deal' ) && ! card.querySelector( '.cwp-badge--freeshipping' ) ) ||
                            ( filter === 'freeshipping' && card.querySelector( '.cwp-badge--freeshipping' ) );
                        card.style.display = typeMatch ? '' : 'none';
                    }
                } );

                // Show empty message if all hidden
                const visible = couponsWrapper.querySelectorAll( '.cwp-coupon-card:not([style*="none"])' );
                let emptyMsg = couponsWrapper.querySelector( '.kwl-filter-empty' );
                if ( visible.length === 0 ) {
                    if ( ! emptyMsg ) {
                        emptyMsg = document.createElement( 'p' );
                        emptyMsg.className = 'kwl-filter-empty cwp-text-muted';
                        emptyMsg.style.padding = '2rem';
                        emptyMsg.style.textAlign = 'center';
                        couponsWrapper.appendChild( emptyMsg );
                    }
                    emptyMsg.textContent = `No ${ filter } type coupons available.`;
                    emptyMsg.hidden = false;
                } else if ( emptyMsg ) {
                    emptyMsg.hidden = true;
                }
            } );
        } );

    }


    /* =========================================================================
       DEAL LINK CLICK TRACKING
       ========================================================================= */

    document.addEventListener( 'click', async function ( e ) {
        const link = e.target.closest( '.cwp-btn-deal[data-action="deal"], a[data-action="deal"]' );
        if ( ! link ) return;

        const couponId = link.dataset.couponId;
        if ( ! couponId ) return;

        // Fire and forget — track the click via AJAX
        ajaxPost( 'kwl_reveal_coupon', { coupon_id: couponId } ).catch( () => {} );
    } );


    /* =========================================================================
       STICKY HEADER SHADOW ON SCROLL
       ========================================================================= */

    const header = document.getElementById( 'kwl-header' );

    if ( header ) {
        const onScroll = () => {
            header.classList.toggle( 'cwp-header--scrolled', window.scrollY > 4 );
        };
        window.addEventListener( 'scroll', onScroll, { passive: true } );
        onScroll(); // Run on load
    }


    /* =========================================================================
       SEARCH DROPDOWN CLOSE ON OUTSIDE CLICK
       ========================================================================= */

    document.addEventListener( 'click', function ( e ) {
        const dropdowns = document.querySelectorAll( '.cwp-search-dropdown' );
        dropdowns.forEach( dd => {
            if ( ! dd.closest( 'form' )?.contains( e.target ) ) {
                dd.hidden = true;
            }
        } );
    } );


} )();
