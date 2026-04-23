/**
 * KWL Coupon WP — Admin JavaScript
 *
 * Handles:
 * - Real-time slug availability checker (Store + Coupon edit screens)
 * - Coupon type radio toggle (show/hide code field)
 * - Preset selector radio cards
 * - CSV import UI (progress, results)
 * - Reset stats confirmation
 * - Auto-fill slug from title on new posts
 *
 * Uses jQuery (already loaded in WP admin).
 * All strings come from kwlAdmin (wp_localize_script).
 *
 * @package KWL_Coupon_WP
 */

/* global kwlAdmin, jQuery */

( function ( $ ) {
    'use strict';

    const { ajaxUrl, nonce, strings } = window.kwlAdmin || {};


    /* =========================================================================
       SLUG CHECKER
       ========================================================================= */

    let slugCheckTimer = null;

    /**
     * Check slug availability via AJAX and show status.
     *
     * @param {jQuery} $field   The slug input field.
     * @param {jQuery} $status  The status span next to the field.
     */
    function checkSlug( $field, $status ) {
        const slug     = $field.val().trim();
        const postType = $field.data( 'post-type' ) || 'kwl_store';
        const postId   = $field.data( 'post-id' )   || 0;

        if ( slug.length < 2 ) {
            $status.html( '' );
            return;
        }

        $status.html( '<span class="kwl-slug-checking">⟳ Checking…</span>' );

        $.post( ajaxUrl, {
            action:    'kwl_check_slug',
            nonce,
            slug,
            post_type: postType,
            post_id:   postId,
        } )
        .done( function ( resp ) {
            if ( ! resp.success ) {
                $status.html( '<span class="kwl-slug-error">⚠ Error checking slug.</span>' );
                return;
            }

            const { available, suggestion } = resp.data;

            if ( available ) {
                $status.html( `<span class="kwl-slug-ok">✓ ${ strings?.slugAvailable || 'Available' }</span>` );
            } else {
                const suggestionHtml = suggestion
                    ? ` <a href="#" class="kwl-slug-suggest" data-slug="${ escAttr( suggestion ) }">${ strings?.slugSuggestion || 'Use:' } ${ escAttr( suggestion ) }</a>`
                    : '';
                $status.html(
                    `<span class="kwl-slug-taken">✗ ${ strings?.slugTaken || 'Already in use.' }</span>${ suggestionHtml }`
                );
            }
        } )
        .fail( function () {
            $status.html( '<span class="kwl-slug-error">⚠ Could not check slug.</span>' );
        } );
    }

    // Attach to slug fields on store/coupon edit screens
    $( document ).on( 'input', '.kwl-slug-field', function () {
        const $field  = $( this );
        const $status = $field.siblings( '.kwl-slug-status' );

        clearTimeout( slugCheckTimer );
        slugCheckTimer = setTimeout( () => checkSlug( $field, $status ), 500 );
    } );

    // Click on suggestion link — fill in the suggested slug
    $( document ).on( 'click', '.kwl-slug-suggest', function ( e ) {
        e.preventDefault();
        const suggested = $( this ).data( 'slug' );
        const $field    = $( this ).closest( 'td' ).find( '.kwl-slug-field' );
        const $status   = $( this ).closest( 'td' ).find( '.kwl-slug-status' );

        $field.val( suggested ).trigger( 'input' );
    } );


    /* =========================================================================
       AUTO-FILL SLUG FROM TITLE (New Posts)
       ========================================================================= */

    let titleSlugFilled = false;

    $( '#title' ).on( 'input', function () {
        // Only auto-fill on new posts (post-new.php), not on edit
        if ( titleSlugFilled ) return;
        if ( window.location.href.indexOf( 'post-new.php' ) === -1 ) return;

        const title    = $( this ).val();
        const $slugField = $( '.kwl-slug-field' );

        if ( $slugField.length && title ) {
            // Sanitize: lowercase, spaces to hyphens, strip special chars, preserve dots
            const slug = title
                .toLowerCase()
                .replace( /\s+/g, '-' )
                .replace( /[^a-z0-9\-\.]/g, '' )
                .replace( /-{2,}/g, '-' )
                .replace( /\.{2,}/g, '.' )
                .replace( /^[-\.]+|[-\.]+$/g, '' );

            $slugField.val( slug ).trigger( 'input' );
        }
    } );

    // Once user manually edits slug, stop auto-filling
    $( '.kwl-slug-field' ).on( 'focus', function () {
        titleSlugFilled = true;
    } );


    /* =========================================================================
       COUPON TYPE TOGGLE (Show/Hide code field)
       ========================================================================= */

    function updateCouponTypeFields() {
        const type     = $( 'input[name="kwl_coupon_type"]:checked' ).val();
        const $codeRow = $( '.kwl-row-code' );

        if ( type === 'code' ) {
            $codeRow.show();
        } else {
            $codeRow.hide();
        }
    }

    // Run on page load
    updateCouponTypeFields();

    // Run on change
    $( document ).on( 'change', 'input[name="kwl_coupon_type"]', updateCouponTypeFields );


    /* =========================================================================
       PRESET SELECTOR CARDS
       ========================================================================= */

    $( document ).on( 'change', '.kwl-preset-card input[type="radio"]', function () {
        // Remove active from all cards
        $( '.kwl-preset-card' ).removeClass( 'kwl-preset-card--active' );
        $( '.kwl-preset-active-badge' ).remove();

        // Add active to selected
        const $card = $( this ).closest( '.kwl-preset-card' );
        $card.addClass( 'kwl-preset-card--active' );
        $card.append( `<div class="kwl-preset-active-badge">${ strings?.activating || 'Active' }</div>` );
    } );


    /* =========================================================================
       RESET COUPON STATS
       ========================================================================= */

    $( document ).on( 'click', '.kwl-reset-stats', function ( e ) {
        e.preventDefault();

        if ( ! confirm( 'Reset click count and votes for this coupon? This cannot be undone.' ) ) {
            return;
        }

        const $btn    = $( this );
        const postId  = $btn.data( 'post-id' );
        const btnNonce = $btn.data( 'nonce' );

        $btn.prop( 'disabled', true ).text( 'Resetting…' );

        $.post( ajaxUrl, {
            action:  'kwl_reset_coupon_stats',
            nonce:   btnNonce,
            post_id: postId,
        } )
        .done( function ( resp ) {
            if ( resp.success ) {
                // Reload the meta box area
                window.location.reload();
            } else {
                $btn.prop( 'disabled', false ).text( 'Reset Stats' );
                alert( 'Could not reset stats. Try again.' );
            }
        } )
        .fail( function () {
            $btn.prop( 'disabled', false ).text( 'Reset Stats' );
        } );
    } );


    /* =========================================================================
       CSV IMPORT UI
       ========================================================================= */

    const $importForm     = $( '#kwl-csv-import-form' );
    const $importBtn      = $( '#kwl-import-btn' );
    const $importProgress = $( '#kwl-import-progress' );
    const $importLog      = $( '#kwl-import-log' );
    const $importStatus   = $( '#kwl-import-status' );

    if ( $importForm.length ) {

        $importForm.on( 'submit', function ( e ) {
            e.preventDefault();

            const fileInput = $( '#kwl-csv-file' )[0];
            const importType = $( 'input[name="kwl_import_type"]:checked' ).val();

            if ( ! fileInput?.files?.length ) {
                showImportStatus( 'error', 'Please select a CSV file.' );
                return;
            }

            const file = fileInput.files[0];

            if ( ! file.name.endsWith( '.csv' ) ) {
                showImportStatus( 'error', 'Only .csv files are supported.' );
                return;
            }

            const reader = new FileReader();

            reader.onload = function ( e ) {
                const csv  = e.target.result;
                const rows = parseCSV( csv );

                if ( rows.length < 2 ) {
                    showImportStatus( 'error', 'CSV appears empty or has no data rows.' );
                    return;
                }

                $importBtn.prop( 'disabled', true ).text( strings?.importing || 'Importing…' );
                $importProgress.show();
                $importLog.empty();
                $importStatus.empty();

                // Process rows in batches to avoid timeout
                const headers = rows[0].map( h => h.trim().toLowerCase() );
                const dataRows = rows.slice( 1 ).filter( r => r.some( c => c.trim() ) );

                processBatch( dataRows, headers, importType, 0, dataRows.length );
            };

            reader.readAsText( file );
        } );

    }

    /**
     * Process CSV rows in batches of 10 via AJAX.
     */
    function processBatch( rows, headers, importType, offset, total ) {
        const batch    = rows.slice( offset, offset + 10 );
        const progress = Math.round( ( offset / total ) * 100 );

        updateProgress( progress, offset, total );

        if ( batch.length === 0 ) {
            // Done
            $importBtn.prop( 'disabled', false ).text( 'Import' );
            showImportStatus( 'success', `${ strings?.importDone || 'Import complete.' } ${ total } rows processed.` );
            return;
        }

        // Convert batch to array of objects
        const batchData = batch.map( row => {
            const obj = {};
            headers.forEach( ( h, i ) => { obj[ h ] = ( row[ i ] || '' ).trim(); } );
            return obj;
        } );

        $.post( ajaxUrl, {
            action:      'kwl_csv_import_batch',
            nonce,
            import_type: importType,
            rows:        JSON.stringify( batchData ),
        } )
        .done( function ( resp ) {
            if ( resp.success && resp.data?.log ) {
                resp.data.log.forEach( entry => {
                    const cls   = entry.status === 'ok' ? 'kwl-log-ok' : 'kwl-log-skip';
                    const icon  = entry.status === 'ok' ? '✓' : '⚠';
                    $importLog.append( `<div class="${ cls }">${ icon } ${ escHtml( entry.message ) }</div>` );
                } );
                // Scroll log to bottom
                $importLog.scrollTop( $importLog[0].scrollHeight );
            }

            processBatch( rows, headers, importType, offset + 10, total );
        } )
        .fail( function () {
            $importBtn.prop( 'disabled', false ).text( 'Import' );
            showImportStatus( 'error', 'A network error occurred during import.' );
        } );
    }

    function updateProgress( percent, done, total ) {
        $importProgress.find( '.kwl-progress-bar' ).css( 'width', `${ percent }%` );
        $importProgress.find( '.kwl-progress-text' ).text( `${ done } / ${ total } rows` );
    }

    function showImportStatus( type, message ) {
        const cls = type === 'error' ? 'notice-error' : 'notice-success';
        $importStatus.html( `<div class="notice ${ cls } inline"><p>${ escHtml( message ) }</p></div>` );
    }


    /* =========================================================================
       SIMPLE CSV PARSER
       Handles quoted fields, commas within quotes, basic RFC 4180.
       ========================================================================= */

    function parseCSV( text ) {
        const rows = [];
        const lines = text.split( /\r?\n/ );

        for ( const line of lines ) {
            if ( ! line.trim() ) continue;
            const row    = [];
            let field    = '';
            let inQuotes = false;

            for ( let i = 0; i < line.length; i++ ) {
                const ch   = line[ i ];
                const next = line[ i + 1 ];

                if ( inQuotes ) {
                    if ( ch === '"' && next === '"' ) {
                        field += '"';
                        i++;
                    } else if ( ch === '"' ) {
                        inQuotes = false;
                    } else {
                        field += ch;
                    }
                } else {
                    if ( ch === '"' ) {
                        inQuotes = true;
                    } else if ( ch === ',' ) {
                        row.push( field );
                        field = '';
                    } else {
                        field += ch;
                    }
                }
            }
            row.push( field );
            rows.push( row );
        }

        return rows;
    }


    /* =========================================================================
       UTILS
       ========================================================================= */

    function escHtml( str ) {
        return String( str )
            .replace( /&/g, '&amp;' )
            .replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' )
            .replace( /"/g, '&quot;' );
    }

    function escAttr( str ) {
        return String( str ).replace( /"/g, '&quot;' );
    }


    /* =========================================================================
       AJAX RESET STATS HANDLER NOTE
       Add to functions.php or post-types.php:

       add_action( 'wp_ajax_kwl_reset_coupon_stats', function() {
           check_ajax_referer( 'kwl_admin_nonce', 'nonce' );
           if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();
           $post_id = absint( $_POST['post_id'] ?? 0 );
           delete_post_meta( $post_id, '_kwl_click_count' );
           delete_post_meta( $post_id, '_kwl_votes_up' );
           delete_post_meta( $post_id, '_kwl_votes_down' );
           wp_send_json_success();
       } );
       ========================================================================= */

} )( jQuery );
