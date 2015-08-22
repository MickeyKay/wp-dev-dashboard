(function( $ ) {
	'use strict';

	$( document ).ready( function() {
		load_ajax_content();
	});

	$( '.button-refresh' ).on( 'click', function( e ) {
		e.preventDefault();

		var $button = $( this );
		load_ajax_content( true, $button );

	});

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
			console.log(data );
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
