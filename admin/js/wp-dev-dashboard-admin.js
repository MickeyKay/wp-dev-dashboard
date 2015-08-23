(function( $ ) {
	'use strict';

	// Load main content via Ajax on inital page load.
	$( document ).ready( function() {
		load_ajax_content();

	});

	// Hook up refresh button functionality.
	$( '.wpdd-button-refresh' ).on( 'click', function( e ) {
		e.preventDefault();

		var $button = $( this );
		load_ajax_content( true, $button );

	});


	$( '#wp-dev-dashboard-settings' ).on( 'click', '.wpdd-sub-tab-nav .button', function( e ) {

		e.preventDefault();

		var $button,
			targetTabClass,
			$tabsContainer;

		$button = $( this );

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

	});

	/**
	 * Load content into main container via Ajax.
	 *
	 * @since 1.0.0
	 *
	 * @param {bool} forceRefresh Whether or not to force a cache-busting fetch.
	 * @param {JQuery} $button Button used to call refresh, if used.
	 */
	var load_ajax_content = function( forceRefresh, $button ) {

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
			'force_refresh' : forceRefresh,
		};

		// Trigger event before refresh.
		$( document ).trigger( 'wpddRefreshBefore' );

		// Run Ajax request.
		jQuery.post( ajaxurl, data, function( response ) {

			$ajaxContainer.fadeTo( 'slow', 1 ).html( response );

			if ( $button ) {
				$button.val( buttonOrigText ).prop( 'disabled', false );
				$spinner.toggleClass( 'is-active');
			}

			// Trigger event after refresh.
			$( document ).trigger( 'wpddRefreshAfter' );
		});

	}

})( jQuery );
