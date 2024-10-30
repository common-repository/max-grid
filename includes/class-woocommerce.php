<?php
/**
 *  WooCommerce functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class Max_Grid_WooCommerce.
 */
class Max_Grid_WooCommerce {
	
	/**
	 * Hook in methods.
	 */
	public static function init() {		
		add_action( 'wp_footer', array( __CLASS__, 'yikes_woocommerce_direct_link_to_product_tabs' ) );
	}
	
	/**
	 * Footer script enqueue - yikes_woocommerce_direct_link_to_product_tabs
	 *
	 * Allows you to create custom URLs to activate product tabs by default, directly from the URL
	 * ex: http://mysite.com/my-product-name#reviews
	 * original code forked from: http://www.remicorson.com/access-woocommerce-product-tabs-directly-via-url/
	 */
	public static function yikes_woocommerce_direct_link_to_product_tabs(){
		if( maxgrid_is_product() ) {
		?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {

					if( window.location.hash ) {
						// Vars
						var tab = 'tab-' + window.location.hash.replace( '#', '' );

						// Tabs
						$( 'li.description_tab' ).removeClass( 'active' );
						$( 'a[href="#' + tab + '"]' ).parent( 'li' ).addClass( 'active' );

						// Tabs content
						$( '#tab-description' ).hide();
						$( '#tab-' + tab.replace( 'tab-', '' ) ).show();
					}

					// when the tab is selected update the url with the hash
					$( '.tabs a' ).click( function() { 
						window.location.hash = $( this ).parent( 'li' ).attr( 'class' ).replace( ' active', '' ).replace( '_tab', '' );
					});

				});
			</script>
		<?php
		}
	}
}
// ****** Temporary disabled
//Max_Grid_WooCommerce::init();