(function( $ ) {
	'use strict';

	$( '.button-refresh' ).on( 'click', function( e ) {
		e.preventDefault();

		var activeTab,
			$button,
			buttonOrigText,
			buttonRefreshingText,
			$metaboxContainer,
			$spinner,
			data;

		// Set up all variables and objects.
		activeTab = $( '#poststuff' ).attr( 'data-wpu-tab' );
		$button = $( this );
		buttonOrigText = $button.val();
		buttonRefreshingText = $button.attr( 'data-wpu-refreshing-text' );
		$metaboxContainer = $( '.wp-dev-dashboard-metaboxes' ).fadeTo( 'fast', 0.4 );

		$button.val( buttonRefreshingText ).prop( 'disabled', true );
		$spinner = $button.next( '.spinner' ).toggleClass( 'is-active');

		data = {
			'action':         'refresh_tickets',
			'ticket_type':    activeTab,
			'force_refresh' : true,
		};

		// Run Ajax request.
		jQuery.post( ajaxurl, data, function( response ) {
			$metaboxContainer.fadeTo( 'slow', 1 ).html( response );
			$button.val( buttonOrigText ).prop( 'disabled', false );
			$spinner.toggleClass( 'is-active');
		});

	});

})( jQuery );
