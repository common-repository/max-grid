<?php
/**
 * Load assets
 */

use \MaxGrid\getPresets;

defined( 'ABSPATH' ) || exit;

/**
 * Max_Grid_Admin_Assets Class.
 */
class Max_Grid_Admin_Assets {
	
	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts($hook_suffix) {
		
		// enqueue Builder page scripts			
		if( $hook_suffix == 'max-grid_page_' . MAXGRID_BUILDER_PAGE ) {
			wp_enqueue_script( 'maxgrid-builder-js', MAXGRID_ABSURL . '/builder/js/builder.js', array('jquery'));				
			wp_register_script( 'maxgrid-builder-functions', MAXGRID_ABSURL . '/builder/js/builder-functions.js', array( 'jquery' ), 'NULL', false );
			wp_register_script( 'maxgrid-builder-preview-mode', MAXGRID_ABSURL . '/builder/js/maxgrid-preview-mode.js', array( 'jquery' ), 'NULL', false );
			wp_register_script( 'maxgrid-builder-ui-panel-modal', MAXGRID_ABSURL . '/builder/js/ui-panel-modal.js', array( 'jquery' ), 'NULL', false );
			wp_register_script( 'maxgrid-builder-fontselect', MAXGRID_ABSURL . '/assets/lib/fontselect/jquery.fontselect.js', array( 'jquery' ), NULL, false );
			wp_register_script( 'maxgrid-builder-search-bar', MAXGRID_ABSURL . '/assets/lib/fontselect/search-bar.js', array( 'jquery' ), NULL, false );
			wp_register_script( 'maxgrid-builder-iconpicker', MAXGRID_ABSURL . '/assets/lib/icon-picker/simple-iconpicker.js', array( 'jquery' ), NULL, false );
			wp_register_script( 'maxgrid-builder-color-scheme', MAXGRID_ABSURL . '/assets/common/color-scheme/color-scheme.js', array( 'jquery' ), NULL, false );
			wp_register_script( 'masonry', MAXGRID_ABSURL . '/assets/lib/masonry.pkgd.min.js', array( 'jquery' ), '4.2.2', false );

			wp_enqueue_script( 'maxgrid-builder-functions' );
			wp_enqueue_script( 'maxgrid-builder-preview-mode' );
			wp_enqueue_script( 'maxgrid-builder-ui-panel-modal' );
			wp_enqueue_script( 'maxgrid-builder-fontselect' );
			wp_enqueue_script( 'maxgrid-builder-search-bar' );
			wp_enqueue_script( 'maxgrid-builder-iconpicker' );
			wp_enqueue_script( 'maxgrid-builder-color-scheme' );
			wp_enqueue_script( 'masonry' );

			wp_register_style( 'maxgrid-builder-fontselect', MAXGRID_ABSURL . '/assets/lib/fontselect/styles/fontselect-default.css');
			wp_register_style( 'maxgrid-builder-ribbon',MAXGRID_ABSURL . '/includes/css/ribbons.css');
			wp_register_style( 'maxgrid-builder-iconpicker',MAXGRID_ABSURL . '/assets/lib/icon-picker/simple-iconpicker.min.css');
			wp_register_style( 'maxgrid-builder-color-scheme',MAXGRID_ABSURL . '/assets/common/color-scheme/color-scheme.css');
			wp_register_style( 'maxgrid-builder-radio-menu',MAXGRID_ABSURL . '/assets/css/radio-menu.css');

			wp_enqueue_style( 'maxgrid-builder-fontselect' );
			wp_enqueue_style( 'maxgrid-builder-ribbon' );
			wp_enqueue_style( 'maxgrid-builder-iconpicker' );
			wp_enqueue_style( 'maxgrid-builder-color-scheme' );
			wp_enqueue_style( 'maxgrid-builder-radio-menu' );	
		}

		if( $hook_suffix == 'max-grid_page_' . MAXGRID_SETTINGS_PAGE ) {			
			// Ace Editor		
			wp_enqueue_script( 'maxgrid-ace_code_highlighter', MAXGRID_ABSURL . '/assets/lib/ace/ace.js', '', '1.0.0', true );
			wp_enqueue_script( 'maxgrid-ace_mode', MAXGRID_ABSURL . '/assets/lib/ace/mode-css.js', array( 'maxgrid-ace_code_highlighter' ), '1.0.0', true );
			wp_enqueue_script( 'maxgrid-custom_css', MAXGRID_ABSURL . '/assets/js/custom-css.js', array( 'jquery', 'maxgrid-ace_code_highlighter' ), '1.0.0', true );
		}
		
		if( $hook_suffix == 'max-grid_page_' . MAXGRID_SETTINGS_PAGE || $hook_suffix == 'max-grid_page_' . MAXGRID_BUILDER_PAGE ) {				
			wp_register_script( 'maxgrid-builder-core-functions', MAXGRID_ABSURL . '/assets/js/core-functions.js', array( 'jquery' ), NULL, false );
			wp_localize_script(
					'maxgrid-builder-core-functions',
					'Const',
					array( 
						  'ajaxurl' 							=> admin_url( 'admin-ajax.php' ),
						  'MAXGRID_SITE_HOME_PAGE' 				=> MAXGRID_SITE_HOME_PAGE,
						  'admin_color' 						=> get_user_option( 'admin_color' ),
						  'is_maxgrid_templates_library' 		=> is_maxgrid_templates_library() ? true : null,
						  'MAXGRID_ABSURL' 						=> MAXGRID_ABSURL,
						  'MAXGRID_SETTINGS_OPT_NAME' 			=> MAXGRID_SETTINGS_OPT_NAME,
						  'MAXGRID_PLUGIN_LABEL_NAME' 			=> MAXGRID_PLUGIN_LABEL_NAME,
						  'MAXGRID_BUILDER_OPT_NAME' 			=> MAXGRID_BUILDER_OPT_NAME,
						  'MAXGRID_CSS_CODE_COMMENT' 			=> MAXGRID_CSS_CODE_COMMENT,
						  'we_value' 							=> 1234,
						  'MAXGRID_DFLT_LAYOUT_NAME' 			=> MAXGRID_DFLT_LAYOUT_NAME,
						  'MAXGRID_CSS_CODE_COMMENT'			=> MAXGRID_CSS_CODE_COMMENT,
						  'localize_stats_row_default_value' 	=> maxgrid()->settings->stats_default(),
						  'localize_info_row_default_value' 	=> maxgrid()->settings->post_meta_default(),
						  'localize_divider_row_default_value' 	=> maxgrid()->settings->divider_default(),
						  'template_save_descrition' 		 	=> __('Something that you can quickly identify the layout preset with.', 'max-grid'),
						  'duplicate_placeholder' 				=> __('Specify Name.', 'max-grid'),
						  'template_name_error' 				=> __('A template name can\'t contain any of the following characters: \\/:*\'?"<>|', 'max-grid'),
						  'premium_required_msg' 				=> sprintf( __('<strong>Max Grid Premium</strong></a> is required to use this feature!', 'max-grid'), MAXGRID_SITE_HOME_PAGE . '/max-grid-premium-add-on/' ),
						  'restore_all_el_msg' 					=> sprintf( __('Resetting to defaults removes all of your custom settings.<br><br><strong>Are you sure you want to reset to default?</strong>', 'max-grid'), MAXGRID_SITE_HOME_PAGE . '/max-grid-premium-add-on/' ),
						  'T_Success_Gen' 						=> __('Template duplicated successfully.', 'max-grid'),
						  'layout_name_required' 				=> __('Template name is required.', 'max-grid'),
						  'delete_or_cancel' 					=> __('Are you sure you want to delete this element?', 'max-grid'),
						  'confirm_export_all' 					=> __('Are you sure you want to export all<strong> %s </strong>Templates?', 'max-grid'),
						  'confirm_delete' 						=> __('Are you sure you want to delete "%s" Template?', 'max-grid'),
						  'T_imported' 							=> __('Templates successfully imported.', 'max-grid'),
						  'T_already_imported' 					=> __('Templates already imported!', 'max-grid'),
						  'ssss' 							=> sprintf( __('Please rate MaxGrid ★★★★★ on WordPress.org to help us spread the word. Thank you.', 'max-grid'), MAXGRID_SITE_HOME_PAGE . '/max-grid-premium-add-on/' )
						 ) 
			);			

			// chosen
			wp_register_script( 'chosen', MAXGRID_ABSURL . '/assets/lib/chosen/chosen.jquery.min.js', array( 'jquery' ), NULL, false );
			wp_register_script( 'maxgrid-builder-chosen-init', MAXGRID_ABSURL . '/assets/lib/chosen/chosen.init.js', array( 'jquery' ), NULL, false );
			wp_register_script( 'maxgrid-builder-tooltip', MAXGRID_ABSURL . '/assets/common/tooltip/tooltip.js', array( 'jquery' ), NULL, false );

			wp_enqueue_script( 'maxgrid-builder-tooltip' );
			wp_enqueue_script( 'maxgrid-builder-jquery-ui' );
			wp_enqueue_script( 'chosen' );
			wp_enqueue_script( 'maxgrid-builder-chosen-init' );

			// IcoMoon
			wp_register_style( 'maxgrid-builder-icomoon', MAXGRID_ABSURL . '/assets/lib/icomoon/style.css' );
			wp_enqueue_style( 'maxgrid-builder-icomoon' );

			// Font Awesome
			wp_register_style( 'maxgrid-builder-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
			wp_register_style( 'maxgrid-builder-styles', MAXGRID_ABSURL . '/builder/css/styles.css');				
			wp_register_style( 'maxgrid-builder-tooltip',MAXGRID_ABSURL . '/assets/common/tooltip/tooltip.css');

			wp_enqueue_style( 'maxgrid-builder-font-awesome' );
			wp_enqueue_style( 'maxgrid-builder-styles' );		
			wp_enqueue_style( 'maxgrid-builder-tooltip' );

			wp_enqueue_script( 'wp-color-picker-alpha', MAXGRID_ABSURL . '/assets/lib/js/wp-color-picker-alpha.js', array( 'wp-color-picker' ), '1.0.0', true );
			wp_register_script( 'maxgrid-builder-options', MAXGRID_ABSURL . '/assets/js/options.js', array( 'jquery' ) );

			wp_enqueue_script( 'maxgrid-builder-core-functions' );
			wp_enqueue_script( 'maxgrid-builder-options' );

			wp_register_style( 'maxgrid-builder-dashboard', MAXGRID_ABSURL . '/assets/css/dashboard.css' );
			wp_register_style( 'maxgrid-builder-ajax-spinner', MAXGRID_ABSURL . '/assets/css/ajax-spinner.css' );
			wp_register_style( 'maxgrid-builder-buttons',MAXGRID_ABSURL . '/assets/css/buttons.css' );
			wp_register_style( 'maxgrid-builder-admin',MAXGRID_ABSURL . '/includes/css/admin.css' );
			wp_register_style( 'maxgrid-builder-toggle-switch', MAXGRID_ABSURL . '/assets/css/toggle-switch.css' );
			wp_register_style( 'chosen',MAXGRID_ABSURL . '/assets/lib/chosen/chosen.min.css' );

			wp_enqueue_style( 'maxgrid-builder-dashboard' );
			wp_enqueue_style( 'maxgrid-builder-ajax-spinner' );
			wp_enqueue_style( 'maxgrid-builder-buttons' );
			wp_enqueue_style( 'maxgrid-builder-button-styles' );
			wp_enqueue_style( 'maxgrid-builder-admin' );
			wp_enqueue_style( 'maxgrid-builder-toggle-switch' );
			wp_enqueue_style( 'chosen' );
		}

		// if extentions page
		if( $hook_suffix == 'max-grid_page_' . MAXGRID_EXTENTIONS_PAGE ) {			
			wp_register_style( 'maxgrid-builder-dashboard', MAXGRID_ABSURL . '/assets/css/dashboard.css' );
			wp_enqueue_style( 'maxgrid-builder-dashboard' );
		}
		
		if( is_admin() ) {
			// Add the color picker css file       
			wp_enqueue_style( 'wp-color-picker' );

			// Include our custom jQuery file with WordPress Color Picker dependency
			wp_enqueue_script( 'maxgrid-colorpicker', maxgrid()->plugin_url() . '/assets/js/wp-colorpicker.js', array( 'wp-color-picker' ), false, true );

			// Add the media uploader script
			wp_enqueue_media();
			wp_register_script( 'maxgrid-media-lib-uploader-js', maxgrid()->plugin_url() . '/assets/js/media-lib-uploader.js', array('jquery') );
			wp_enqueue_script( 'maxgrid-media-lib-uploader-js' );
			wp_localize_script(
					'maxgrid-media-lib-uploader-js',
					'ConstMCE',
					array( 
						  'maxgrid_premium'  => is_maxgrid_premium_activated() ? true : null,
						  'maxgrid_woo' 	 => is_maxgrid_woo_activated() ? true : null,
						  'maxgrid_download' => is_maxgrid_download_activated() ? true : null,
						  'maxgrid_ytb' 	 => is_maxgrid_youtube_activated() ? true : null,
						 ) 
				);
		}
		
		// SweetAlert
		wp_register_script( 'sweetalert', MAXGRID_ABSURL . '/assets/lib/sweetalert/sweetalert.min.js', array( 'jquery' ), NULL, false );
		wp_enqueue_script( 'sweetalert' );	
		
		wp_register_style( 'maxgrid-builder-dialog-style', MAXGRID_ABSURL . '/assets/css/dialog.css');
		wp_enqueue_style( 'maxgrid-builder-dialog-style' );
	}

	/**
	 * Construct TinyMCE Variables.
	 * 
	 * @return array
	 */
	public function get_mce_vars() {
		// get post categories and construct dropdown list
		$post_cats_header 	= '<select id="allPostCats" class="chosen" multiple data-placeholder="Select categories to exclude">';		
		$get_all_categories = get_categories();
		$categories 		= $post_cats_header;

		foreach ( $get_all_categories as $term ) {
			$categories .= '<option value="' . $term->slug . '"> ' . $term->name . '</option>';
		}
		$categories .= '</select>';
		
		$dflt_exl_cats = '';
		
		if ( is_maxgrid_download_activated() ) {
			$get_all_maxgrid_categories = get_terms( MAXGRID_CAT_TAXONOMY, array( 'hide_empty' => false ) );
			$maxgrid_categories = $post_cats_header;

			foreach ( $get_all_maxgrid_categories as $term ) {
				$maxgrid_categories .= '<option value="' . $term->slug . '"> ' . $term->name . '</option>';
			}
			$maxgrid_categories .= '</select>';
			
			if ( strpos( $maxgrid_categories, 'uncategorized' ) !== false ) {
				$dflt_exl_cats = 'uncategorized';
			}
		}
		
		$post_type = [
			array(
				'text' => 'Post',
				'value' => 'post',
			),
			array(
				'text' => 'Download',
				'value' => MAXGRID_POST,
			)
		];	

		if ( is_maxgrid_woo_activated() ) {
			$woo_categories = $post_cats_header;
			$woo_categories .= maxgrid()->woo->get_cats($post_type);
			$woo_categories .= '</select>';
		}
		

		array_push( $post_type, array(
			'text' => 'Youtube',
			'value' => 'youtube_stream',
		) );

		// Get Template Presets List
		global $wpdb;

		$table_name 	   = $wpdb->prefix . MAXGRID_LAYOUTS_TABLE_NAME;
		$results 		   = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC" );
		$presets_header    = '<select id="presets_selsector" class="chosen" data-placeholder="Select preset">';		
		$post_presets_list = $presets_header;
		$dld_presets_list  = $presets_header;
		$woo_presets_list  = $presets_header;
		$ytb_presets_list  = $presets_header;
		$post_templates    = array();
		$wc_templates 	   = array();
		$dld_templates 	   = array();
		$ytb_templates 	   = array();

		$dflt_sel_presets  = array( 'youtube_stream_default', 'product_default', 'download_default', 'post_default');
		
		foreach ( $results as $key => $row ) {
			if ( in_array( $row->pslug, $dflt_sel_presets ) ) {
				$pname = 'Default Template';
				switch ( $row->source_type ) {
					case 'post':
						$post_templates[$pname] = $row->pslug;
						$post_presets_list .= '<option value="' . $row->pslug . '">' . $pname . '</option>';
						break;
					case 'product':
						$wc_templates[$pname] = $row->pslug;
						$woo_presets_list .= '<option value="' . $row->pslug . '">' . $pname . '</option>';
						break;
					case 'download':
						$dld_templates[$pname] = $row->pslug;
						$dld_presets_list .= '<option value="' . $row->pslug . '">' . $pname . '</option>';
						break;
					case 'youtube_stream':
						$ytb_templates[$pname] = $row->pslug;
						$ytb_presets_list .= '<option value="' . $row->pslug . '">' . $pname . '</option>';
						break;
				}				
			}
		}
		
		foreach ( $results as $key => $row ) {
			if ( $row->pslug == 'all_elements' || in_array( $row->pslug, $dflt_sel_presets ) ) {
				continue;
			}
			
			$pname 	  = $row->pname;
			$disabled = '';
			$class 	  = '';
			if ( !in_array( $row->pslug, $dflt_sel_presets ) && !is_maxgrid_templates_library() ) {
				$disabled = ' disabled';
				$class 	  = ' class="mxg-premium-required"';
			}
			
			$pname = str_replace(' And ', ' & ', $pname);
			
			switch ( $row->source_type ) {
				case 'post':		
					$post_templates[$pname] = $row->pslug;
					$post_presets_list .= '<option value="' . $row->pslug . '" ' . $class . $disabled . '>' . $pname . '</option>';
					break;
				case 'product':
					$wc_templates[$pname] = $row->pslug;
					$woo_presets_list .= '<option value="' . $row->pslug . '" ' . $class . $disabled . '>' . $pname . '</option>';
					break;
				case 'download':
					$dld_templates[$pname] = $row->pslug;
					$dld_presets_list .= '<option value="' . $row->pslug . '" ' . $class . $disabled . '>' . $pname . '</option>';
					break;
				case 'youtube_stream':
					$ytb_templates[$pname] = $row->pslug;
					$ytb_presets_list .= '<option value="' . $row->pslug . '" ' . $class . $disabled . '>' . $pname . '</option>';
					break;
			}
		}
		$post_presets_list .= '</select>';
		$dld_presets_list .= '</select>';
		$woo_presets_list .= '</select>';
		$ytb_presets_list .= '</select>';

		$vars = array(
			'categories' 	 	=> $categories,
			'maxgrid_categories'=> $categories,
			'dflt_exl_cats' 	=> $dflt_exl_cats,
			'woo_categories' 	=> $categories,
			'post_type' 	 	=> $post_type,
			'post_presets_list' => $post_presets_list,
			'dld_presets_list' 	=> $dld_presets_list,
			'woo_presets_list' 	=> $woo_presets_list,
			'ytb_presets_list' 	=> $ytb_presets_list,
			//'default_channel_id'=> '',
			'post_templates'	=> $post_templates,
			'wc_templates'		=> $wc_templates,
			'dld_templates'		=> $dld_templates,
			'ytb_templates'		=> $ytb_templates,
		);

		if ( is_maxgrid_download_activated() ) {
			$vars['maxgrid_categories'] = $maxgrid_categories;
		}
		
		if ( is_maxgrid_woo_activated() ) {
			$vars['woo_categories'] = $woo_categories;
		}
		
		if ( is_maxgrid_youtube_activated() ) {
			// Get Default Channel ID
			$get_options = maxgrid()->get_options;
			$default_channel_id = isset($get_options->option('api_options')['channel_id']) ? $get_options->option('api_options')['channel_id'] : \MaxGrid\Youtube::DFLT_CHANNEL_ID;
			$vars['default_channel_id'] = $default_channel_id;
		}
		
		return $vars;		
	}
}

return new Max_Grid_Admin_Assets();