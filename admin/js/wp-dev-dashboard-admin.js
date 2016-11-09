(function( $ ) {
	'use strict';

	// Load main content via Ajax on inital page load.
	$( document ).ready( function() {
		wpdd_load_ajax_content();
	});

	// Trigger actions after Ajax load.
	$( document ).on( 'wpddRefreshAfter', function() {

		// Open correct tab.
		if ( location.href.indexOf( 'orderby' ) >= 0 ) {
			$( '.button[data-wpdd-tab-target="info"]' ).addClass( 'red') ;
			wpdd_toggle_tabs( $( '.button[data-wpdd-tab-target="info"]' ) );
		}

		// Hook up responsive row expand/collapse functionality.
		$( '.wpdd-sub-tab-info tbody' ).on( 'click', '.toggle-row', function() {
			$( this ).closest( 'tr' ).toggleClass( 'is-expanded' );
		});

	});

	// Hook up refresh button functionality.
	$( '#wp-dev-dashboard-settings' ).on( 'click', '.wpdd-button-refresh', function( e ) {
		e.preventDefault();

		var $button = $( this );
		wpdd_load_ajax_content( true, $button );

	});


	$( '#wp-dev-dashboard-settings' ).on( 'click', '.wpdd-sub-tab-nav .button', function( e ) {

		e.preventDefault();

		var $button = $( this );

		wpdd_toggle_tabs( $button );

	});

	// Fix broken table-sorting links after Ajax.
	$( document ).on( 'wpddRefreshAfter', function() {

		$( '.wppd-sub-tab a[href*="admin-ajax.php"]' ).each( function() {
			var $link,
				linkIdentifier = 'admin-ajax.php',
				href,
				hrefParams,
				newHref,

			$link = $( this );
			href = $link.attr( 'href' );
			hrefParams = href.substr( href.indexOf( linkIdentifier ) + linkIdentifier.length + 1 );
			newHref = location.href;
			newHref += ( newHref.indexOf( '?' ) >= 0 ? '&' : '?' ) + hrefParams;

			// Set new href.
			$link.attr( 'href', newHref );

		});

	});

	/**
	 * Load content into main container via Ajax.
	 *
	 * @since 1.0.0
	 *
	 * @param {bool} forceRefresh Whether or not to force a cache-busting fetch.
	 * @param {JQuery} $button Button used to call refresh, if used.
	 */
	var wpdd_load_ajax_content = function( forceRefresh, $button ) {

		var $ajaxContainer,
			objectType,
			ticketType,
			data;

		// Set up all variables and objects.
		$ajaxContainer = $( '.wpdd-ajax-container' );
		objectType = $ajaxContainer.attr( 'data-wpdd-object-type' );
		ticketType = $ajaxContainer.attr( 'data-wpdd-ticket-type' );
		$ajaxContainer.fadeTo( 'fast', 0.4 );

		// Set up button stuff.
		if ( $button ) {
			var buttonOrigText,
				refreshingTexts,
				buttonRefreshingText,
				$spinner,

			buttonOrigText = $button.val();
			refreshingTexts = wpddSettings.fetch_messages;
			buttonRefreshingText = $button.attr( 'data-wpdd-refreshing-text' );
			$spinner = $button.next( '.spinner' ).toggleClass( 'is-active');

			// Get refresh message.
			buttonRefreshingText = refreshingTexts[Math.floor(Math.random()*refreshingTexts.length)];

			$button.val( buttonRefreshingText ).prop( 'disabled', true );
		}

		data = {
			'action':         'refresh_wpdd',
			'object_type':    objectType,
			'ticket_type':    ticketType,
			'force_refresh':  forceRefresh,
			'current_url':    location.href,
		};

		// Trigger event before refresh.
		$( document ).trigger( 'wpddRefreshBefore' );

		// Run Ajax request.
		jQuery.post( ajaxurl, data, function( response ) {
			var lastSortList, lastStatsOrder;

			lastSortList = $( '.wdd-stats-table' );
			if( lastSortList.length > 0 ) {
				lastStatsOrder = lastSortList[0].config.sortList;
			} else {
				lastStatsOrder = [[0,0]];
			}

			$ajaxContainer.fadeTo( 'slow', 1 ).html( response );

			if ( $button ) {
				$button.val( buttonOrigText ).prop( 'disabled', false );
				$spinner.toggleClass( 'is-active');
			}

			$( '.wdd-stats-table' ).tablesorter({
				// sort on the date column, order dsc
				sortList: lastStatsOrder
			});

			// Trigger event after refresh.
			$( document ).trigger( 'wpddRefreshAfter' );

		});

	}

	var wpdd_toggle_tabs = function( $button ) {

		var targetTabClass,
			$tabsContainer;

		// Don't do anything if this is already the active button.
		if ( ! $button.hasClass( 'button-primary' ) ) {

			// Toggle "active" status.
			$button.addClass( 'button-primary' ).siblings( '.button' ).removeClass( 'button-primary' );

			// Toggle visibility of tabs.
			targetTabClass = $button.attr( 'data-wpdd-tab-target' );
			$tabsContainer = $( '.wpdd-sub-tab-container' );

			$tabsContainer.find( '.wppd-sub-tab' ).removeClass( 'active' );
			$tabsContainer.find( '.wpdd-sub-tab-' + targetTabClass ).addClass( 'active' );

		}

		// Remove focus/outline from button.
		$button.blur();

	}

})( jQuery );
