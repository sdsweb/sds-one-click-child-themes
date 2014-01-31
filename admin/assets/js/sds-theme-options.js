( function ( $ ) {
	"use strict";

	$( function() {
		/**
		 * One-Click Child Themes
		 */

		// Navigation Tabs
		$( '.sds-theme-options-tab-wrap a' ).on( 'click', function ( e ) {
			var _this = $( this ), tab_id_prefix = _this.attr( 'href' ), $submit = $( '.submit' ), $message = $( '.sds-child-themes-message' );

			// Hide submit buttons on "One-Click" Child Themes tab
			if ( tab_id_prefix === '#one-click-child-themes' ) {
				$submit.hide();
			}

			// "One-Click" Child Themes Messages
			if ( tab_id_prefix === '#one-click-child-themes' ) {
				$message.show();
			}
			else {
				$message.attr( 'style', function( index, value ) { return value + ' display: none !important;' } );
			}
		} );

		// Window Hash
		if ( window.location.hash && $( window.location.hash + '-tab-content' ).length ) {
			var tab_id_prefix = window.location.hash, $submit = $( '.submit' ), $message = $( '.sds-child-themes-message' );

			// Hide submit buttons on "One-Click" Child Themes tab
			if ( tab_id_prefix === '#one-click-child-themes' ) {
				$submit.hide();
			}

			// "One-Click" Child Themes
			if ( tab_id_prefix === '#one-click-child-themes' ) {
				$message.show();
			}
			else {
				$message.attr( 'style', function( index, value ) { return value + ' display: none !important;' } );
			}
		}

		// Multisite (install/activate checkboxes)
		if ( sds_theme_options.is_multisite ) {
			var $activate_checkbox = $( '#sds_theme_options_one_click_child_theme_activate' ), $network_enable_checkbox = $( '#sds_theme_options_one_click_child_theme_network_enable' );

			// Activate checkbox
			$activate_checkbox.on( 'change', function( e ) {
				// If the enable checkbox is not currently checked, ensure it is
				if ( this.checked && ! $network_enable_checkbox.prop( 'checked' ) ) {
					$network_enable_checkbox.prop( 'checked', true );
				}
			} );
		}
	} );
}( jQuery, sds_theme_options ) );