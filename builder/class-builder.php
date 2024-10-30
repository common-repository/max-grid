<?php
/**
 * Max Grid - The Builder.
 */

use \MaxGrid\Elements;

defined( 'ABSPATH' ) || exit;

/**
 * @class Max_Grid_Builder.
 */
class Max_Grid_Builder {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! did_action( 'builder_elements_added' ) ) {
			add_action( 'admin_head', array( $this, 'ui_edit_panel' ) );
			add_action( 'admin_head', array( $this, 'grid_preview_mode' ) );
		}
		do_action( 'builder_elements_added' );
	}
		
	/**
	 * Edit Element UI-Panel.
	 *
	 * @return string
	 */
	public function ui_edit_panel() {
		if ( !isset($_GET['page']) || sanitize_html_class( $_GET['page'] ) != MAXGRID_BUILDER_PAGE ) {
			return false;
		}
		?>
		<div class="maxgrid_ui-panel_overlay">
			<div id="maxgrid_ui-panel" class="maxgrid_ui-panel-edit-element">
				<div id="maxgrid_ui-panelheader" class="maxgrid_ui-panel-header">
					<div class="maxgrid_ui-header"><!-- UI Panel Title --></div>
					<div class="maxgrid_ui-control-bar">
						<span id="close_panel" class="dashicons dashicons-no-alt"></span>
					</div>
				</div>
				<div id="ui_tabs_container"><!-- UI Panel Tabs --></div>
				<div id="maxgrid_ui-panel-content" class="ui-panel-content">
					<!-- UI Panel Content -->
				</div>
				<div id="maxgrid_ui-panel-footer">
					<div style="position: relative; float: left;">
						<span id="set_to_default" class="maxgrid-button bp-upload-btn theme_color biggest no-icon">Reset All Settings</span>
						<span class="ajax_dl-spiner"></span>
					</div>
					<span id="close_panel" class="maxgrid-button bp-upload-btn grey biggest no-icon">Close</span>
					<span id="maxgrid_ui_save_change" class="maxgrid-button bp-upload-btn theme_color biggest no-icon">Save changes</span>
				</div>

			</div>
		</div>
		<?php 
	}

	/**
	 * Grid preview mode.
	 *
	 * @return string
	 */
	public function grid_preview_mode() {
		if ( !isset($_GET['page']) || sanitize_html_class( $_GET['page'] ) != MAXGRID_BUILDER_PAGE ) {
			return false;
		}
		?>
		<div id="maxgrid_preview_container">
			<div id="preview_loader">
				<?php
					$args = array(
						'version' => 2,
						'color'   => 'grey',
						'form' 	  => 'ball',
					);
					echo maxgrid_lds_rolling_loader($args);
				?>
			</div>
			<div id="preview_container">
				<script type="text/javascript">
					(function( $ ) { 
						// Add Color Picker to all inputs that have 'color-field' class
						$(function() {
							var iFrame = document.getElementById( 'grid-preview-device' );			
							iFrame.width  = '1440px';

							iFrame.onload = function() {
								$("#grid-preview-device").contents().find(".maxgrid_lightbox-modal").addClass('iframe-mode');
							};
						});

					})( jQuery );
				</script>
			<body>

				<div id="grid-preview-header">
					<div class="em-toggle">
					  <div class="em-toggle-label toggle-label-dark">Dark</div>
					  <div class="em-toggle-switch"></div>
					  <div class="em-toggle-label toggle-label-light">Light</div>
					</div>
					<ul>
						<li id="mobile" onclick="maxgrid_swapScreen(this)" title="Mobile Screen"><i class="fa fa-mobile"></i></li><!--
						<li id="tablet_medium" onclick="maxgrid_swapScreen(this.id)" title="Tablet Medium"><i class="fas fa-tablet-alt"></i></li>--><!--
						--><li id="tablet" onclick="maxgrid_swapScreen(this)" title="Tablet Screen"><i class="fa fa-tablet"></i></li><!--
						--><li id="laptop" onclick="maxgrid_swapScreen(this)" title="Laptop Screen"><i class="fa fa-laptop"></i></li><!--
						--><li id="desktop" onclick="maxgrid_swapScreen(this)" title="Desktop Screen" class="active"><i class="fa fa-desktop"></i></li><!--
						--><li id="divider"></li><!--
						--><li id="full_width" onclick="maxgrid_swapScreen(this)" title="Full Width Screen"><i class="fa fa-arrows-h"></i></i></li>
					</ul>
				</div>
				<span class="close-preveiw">×</span>
			
				<?php
				$options = [
					'masonry' 		=> array(
											'label' 	  => 'Masonry',
											'type' 		  => 'checkbox',
											'true_value'  => 'on',
											'false_value' => 'off',
											'default' 	  => 'checked'
										),
					/*'full_content' 	=> array( 
											'label' 	  => 'Full Content Excerpt',
											'type' 		  => 'checkbox',
											'true_value'  => 'on',
											'false_value' => 'off',
											'default' 	  => 'checked'
										),*/
					'items_row' 	=> array( 
											'label' 	  => 'Maximum items Per Row',
											'type' 		  => 'number',
											'default' 	  => 4
										),
				];
				?>
				<div id="grid-preview-options" style="position: relative">
					<div>
					<?php foreach( $options as $key => $value ) {?>
						<li>
						<?php if ( $value['type'] == 'checkbox' ) { ?>
							<input id="<?php echo $key;?>" type="checkbox" data-true="<?php echo $value['true_value'];?>" data-false="<?php echo $value['false_value'];?>" onchange="maxgrid_swapOptions(this)" <?php echo $value['default'];?>>
							<label for="<?php echo $key;?>"><?php echo $value['label'];?></label>
						<?php }?>

						<?php if ( $value['type'] == 'number' ) { ?>
							<input id="<?php echo $key;?>" type="number" min="1" max="6" onchange="maxgrid_swapOptions(this)" onkeyup="maxgrid_swapOptions(this)" value="<?php echo $value['default'];?>" />
							<label for="<?php echo $key;?>"><?php echo $value['label'];?></label>
						<?php }?>

						</li>
					<?php }?>
						<li>
							<a class="dashicons dashicons-update" onclick="maxgrid_swapOptions($('#items_row')[0])">&nbsp;</a>
						</li>						
						<li>
							<label style="pointer-events: none">Background color:</label>
						</li>						
						<li>					
							<div class="preview_mode_bg_c-container">
								<input type="text" value="#ffffff" class="preview_mode_bg_c"/>
							</div>
						</li>
					</div>
				</div>
				<?php					
				if ( get_home_url() == MAXGRID_SITE_HOME_PAGE ) {
					$my_exclude_cats = '&exclude=audio,soundcloud';
				}
				?>
				<iframe id="grid-preview-device" class="grid-preview-device" src="" data-src="<?php echo get_home_url();?>/?grid_preview_template=on&items_row=4&masonry=on<?php echo $my_exclude_cats;?>" scrolling="yes" width="100%" height="1080px" frameborder="0"></iframe>
			</body>
			</html>
			</div>
		</div>
		<?php 
	}
	
	/**
	 * Builder construct.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ui_args
	 *
	 * @return string
	 */
	function construct($ui_args=array()) {
		global $source_type;
		
		$source_type = isset($ui_args['source_type']) ? $ui_args['source_type'] : 'post';
		//echo $source_type;
		$get_options = isset($ui_args['pslug']) ? unserialize(maxgrid_template_load($ui_args['pslug'])) : get_option( MAXGRID_BUILDER_OPT_NAME );
		$get_options = !is_maxgrid_templates_library() ? get_option( MAXGRID_BUILDER_OPT_NAME ) : $get_options;
		
		if ( isset($get_options['source_type']) && $get_options['source_type'] != $source_type ) {			
			$get_options = unserialize( maxgrid_template_load( $source_type.'_default' ) );
		}
		
		$maxgrid_data = isset($ui_args['options']) ? $ui_args['options'] : $get_options;
		$tooltip_style = 'grey';
		
		$html = '<ul id="maxgrid-columns">';
		
		$ribbons_tab_title = is_maxgrid_premium_activated() ? ', blocks_row_ribbon_options:Ribbons' : '';
		
		$blocks_tabs = 'blocks_row_design_options:Design Options, blocks_row_typography_options:Typography'. $ribbons_tab_title;
		$root_name = 'rows_options';

		$pannel_name = '<div class="ui-single-btn pannel-name">Blocks</div>';

		$add_icon = '<span class="dashicons dashicons-plus"></span>';
		$filter_icon = '<span class="dashicons dashicons-filter"></span>';
		$edit_icon = '<span class="dashicons dashicons-admin-settings"></span>';
		$lightbox_icon = '<span class="dashicons dashicons-welcome-view-site"></span>';
		$about_icon = '<span class="dashicons dashicons-info"></span>';
		$restore_icon = '<span class="dashicons dashicons-update-alt"></span>';
		
		$add_row_btn = '<div class="ui-single-btn maxgrid_add_row" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.__('Add New Element', 'max-grid').'" data-row-id="'.$root_name.'" data-action="add_row" data-ui-panel-title="'.__('Add Element', 'max-grid').'">'.$add_icon.'</div>';

		$grid_filter_btn = '';
		
		$edit_blocks_btn = '<div class="ui-single-btn mid-pos" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.__('Blocks Settings', 'max-grid').'" data-row-id="'.$root_name.'" data-action="blocks_row" data-ui-panel-title="'.__('Blocks Settings', 'max-grid').'" data-multi-tabs="true" data-tabs-title="'.$blocks_tabs.'">'.$edit_icon.'</div>';

		$lightbox_btn = '<div class="ui-single-btn mid-pos" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.__('Lightbox Settings', 'max-grid').'" data-row-id="'.$root_name.'" data-action="lightbox_row" data-ui-panel-title="'.__('Lightbox Settings', 'max-grid').'" data-ui-panel-title="'.__('Lightbox Settings', 'max-grid').'">'.$lightbox_icon.'</div>';

		$about_btn = '<div class="ui-single-btn right-pos" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.__('About', 'max-grid').'" data-row-id="'.$root_name.'" data-savechanges="off" data-action="about_builder" data-ui-panel-title="'.__('About', 'max-grid').'">'.$about_icon.'</div>';
		
		$restore_blocks_btn = '<div class="ui-single-btn refresh" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.__('Reset to default', 'max-grid').'" id="maxgrid_restore_all_row">'.$restore_icon.'</div>';
		
		$html .= '<span class="ui-combo-btn icons-version">' . $pannel_name . $add_row_btn . $edit_blocks_btn . $grid_filter_btn . $lightbox_btn . $about_btn . $restore_blocks_btn . '</span>';
		$restore_el_href_title = 'Restore sub-elements';
		
		$def_val = maxgrid()->settings;		
		$default_data = array(
				'templates_manager' 	=> 'templates_manager=none',
				'design_options_css' 	=> wp_remote_retrieve_body( wp_remote_get( MAXGRID_ABSURL . '/includes/css/bb-design-option-default-styles.css' ) ),
				'blocks_row' 			=> maxgrid_array_to_string($def_val->blocks_default()),
				'filter_row' 			=> maxgrid_array_to_string($def_val->blocks_default()),
				'featured_row' 			=> maxgrid_array_to_string($def_val->filter_default()),
				'audio_row' 			=> maxgrid_array_to_string($def_val->audio_player_default()),
				'title_row' 			=> maxgrid_array_to_string($def_val->title_default()),
				'info_row' 				=> maxgrid_array_to_string($def_val->post_meta_default()),
				'stats_row' 			=> maxgrid_array_to_string($def_val->stats_default()),
				'description_row' 		=> maxgrid_array_to_string($def_val->summary_default()),
				'ytb_description_row' 	=> maxgrid_array_to_string($def_val->ytb_summary_default()),
				'add_to_cart_row' 		=> maxgrid_array_to_string($def_val->add_to_cart_default()),
				'download_row' 			=> maxgrid_array_to_string($def_val->download_default()),
				'average_rating_row' 	=> maxgrid_array_to_string($def_val->average_rating_default()),
				'ytb_vid_stats_row' 	=> maxgrid_array_to_string($def_val->stats_default()),
				'woo_stats_row' 		=> maxgrid_array_to_string($def_val->stats_default()),
				'divider_row' 			=> maxgrid_array_to_string($def_val->divider_default()),
				'lightbox_row' 			=> maxgrid_array_to_string($def_val->lightbox_default()),
			);
		if ( isset($ui_args['force_source_default']) && $ui_args['force_source_default'] == true ) {
			$maxgrid_data = $ui_args['default_options'];
		}
		$current_values = 
		isset($maxgrid_data['grid_layout']['rows_options']) ? $maxgrid_data['grid_layout']['rows_options'] : $default_data;

		if ( isset($ui_args['swap_source']) && $ui_args['swap_source'] === true ) {
			$current_values = $default_data;
		}
		
		$html .= '<div class="general-settings-field">';

		$i=0;
		foreach ( $current_values as $key => $default ){
			$new_key = $key;
			if ( strpos($key, 'stats_row') !== false && strlen($key) !== strlen('stats_row') ) {
				$new_key = str_replace($key, 'stats_row_'.str_replace('row', 'bar', $key), $key);
			}
			$current = isset($maxgrid_data['grid_layout'][$root_name][$key]) ? $maxgrid_data['grid_layout'][$root_name][$key] : $default_data[$key];
			if ( isset($ui_args['swap_source']) && $ui_args['swap_source'] == true ) {
				$current = isset($default_data[$key]) ? $default_data[$key] : '';
			}

			$html .= '<input type="hidden"  class="'.$root_name.'_'.$new_key.'" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$root_name.']['.$key.']" value="'.$current.'" data-field="'.$key.'">';
			$i++;
		}

		$html .= '</div>';

		$def_data = array( 
						'featured_bar' 			 => 'Featured box',
						'audio_bar'    	 	 	 => 'Audio Player',
						'post_title'    	 	 => 'Post title',
						'post_description' 		 => 'Post description',
						'divider_bar' 		 	 => 'Divider',
						'info_bar' 			 	 => 'Author, date and comment bar',
						'stats_bar'			 	 => 'Post Stats',
						'add_to_cart_bar'	 	 => 'Add To Cart',
						'download_bar' 		 	 => 'Download',
						'average_rating_bar' 	 => 'Average Rating',
						'ytb_description' 		 => 'Video description',
						'ytb_vid_stats_bar' 	 => 'Youtube Video Stats',
						'woo_stats_bar' 	 	 => 'WooCommerce Stats',
		);

		$has_duplicata = array('divider_bar');

		$pslug = isset($ui_args['pslug']) ? $ui_args['pslug'] : MAXGRID_DFLT_LAYOUT_NAME;
		$layout_data = isset($maxgrid_data['grid_layout']) ? $maxgrid_data['grid_layout'] : unserialize(maxgrid_template_load($pslug))['grid_layout'];
		
		//$layout_data = isset($maxgrid_data['grid_layout']) ? $maxgrid_data['grid_layout'] : $def_data; // use the above line - use this only during developement
		
		//$layout_data = $def_data;
		if ( get_option( MAXGRID_BUILDER_OPT_NAME) != false ) {
			$current_option = array_filter(get_option( MAXGRID_BUILDER_OPT_NAME), function ($var) {
				return !is_null($var);
			});
		}
		/*
		// if swap source type	
		if ( isset($ui_args['force_source_default']) && $ui_args['force_source_default'] == true ) {
			//$layout_data = unserialize(maxgrid_template_load($pslug))['grid_layout'];
			$current_option = '';
		}
		*/
		//$layout_data = $def_data;
		$remove_icon = '<span class="delete_row dashicons dashicons-no-alt"></span>';
		
		foreach ( (array)$layout_data as $key => $value ){
			
			if ( ( $key == 'rows_options' || $key == 'null' )
				|| ( in_array( $source_type, array('post', 'product', MAXGRID_POST) ) && ( $key == 'stats_bar' || $key == 'woo_stats_bar' ) && !is_maxgrid_premium_activated() ) 
				|| ( $key == 'audio_bar' && !is_maxgrid_premium_activated() )
				|| ( $source_type != 'youtube_stream' && $key == 'ytb_vid_stats_bar' ) || ( $key == 'ytb_vid_stats_bar' && !is_maxgrid_youtube_activated() ) ) { 
				continue; 
			}
			
			$li_class = ' '.$key;
			$is_cloned_data = '';
			$is_cloned_input = '';
			$duplicata_data = ' data-duplicata="off"';
			foreach ( $def_data as $def_key => $def_value ){
				if ( strpos($key, $def_key) !== false ) {
					if ( in_array($def_key, $has_duplicata) ) {
						$duplicata_data = ' data-duplicata="on"';
					}
					$li_class = ' '.$def_key;
				}
				if ( strpos($key, $def_key) !== false && strlen($key) !== strlen($def_key) ) {
					$li_class .= ' cloned';
					$clone_id = str_replace($def_key.'_', "", $key);
					$is_cloned_data = ' data-clone-id="'.$key.'"';
					$is_cloned_input = '<input type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][clone_id]" value="'.$clone_id.'">';
				}
			}
			
			if ( strpos($key, $def_key) !== false ) {
				$li_class = ' '.$def_key;
			}

			$html .= '<li class="maxgrid-column '.$key.' '.$li_class.'" draggable="true"'.$is_cloned_data.$duplicata_data.' style="display: none;">
						<input class="'.$key.'" type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']" value="'.$key.'">'.$is_cloned_input.'<header class="not-draggable">';

			if ( $key == 'featured_bar' ) {
				$tabs_title = 'featured_design_options:Design Options, elements_options:Components, video_options:Embed';

				$html .= $remove_icon;
				$html .= '<span class="edit_row bar second-pos maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="featured_bar" data-action="featured_row" data-ui-panel-title="The Featured Settings" data-multi-tabs="true" data-tabs-title="'.$tabs_title.'"></span>';

			} else if ( $key == 'audio_bar' ) {
				$html .= $remove_icon;
				$html .= '<span class="edit_row bar second-pos maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="audio_bar" data-action="audio_row" data-ui-panel-title="Audio Player Settings"></span>';

			} else if ( $key == 'post_title' ) {
				$html .= $remove_icon;
				$html .= '<span class="edit_row bar second-pos maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="post_title" data-action="title_row" data-ui-panel-title="Title Settings"></span>';

			} else if ( $key == 'ytb_description' ) {
				$html .= $remove_icon;
				$html .= '<span class="edit_row bar second-pos maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="ytb_description" data-action="ytb_description_row" data-ui-panel-title="Video Description Settings"></span>';

			} else if ( $key == 'add_to_cart_bar' ) {
				$html .= $remove_icon;
				$html .= '<span class="edit_row bar second-pos maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="add_to_cart_bar" data-action="add_to_cart_row" data-ui-panel-title="Add To Cart Settings"></span>';
			} else if ( $key == 'download_bar' ) {
				$html .= $remove_icon;
				$html .= '<span class="edit_row bar second-pos maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="download_bar" data-action="download_row" data-ui-panel-title="Download Settings"></span>';
			} else if ( $key == 'average_rating_bar' ) {
				$html .= $remove_icon;
				$html .= '<span class="edit_row bar second-pos maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="average_rating_bar" data-action="average_rating_row" data-ui-panel-title="Average Rating Settings"></span>';

			} else if ( $key == 'post_description' ) {

				$label_text = isset($maxgrid_data['grid_layout'][$key]['label_text'])  ? $maxgrid_data['grid_layout'][$key]['label_text'] : __('Read More', 'max-grid');
				$btn_bg_color = isset($maxgrid_data['grid_layout'][$key]['btn_bg_color']) ? $maxgrid_data['grid_layout'][$key]['btn_bg_color'] : '#31c1eb';
				$btn_bg_h_color = isset($maxgrid_data['grid_layout'][$key]['btn_bg_h_color']) ? $maxgrid_data['grid_layout'][$key]['btn_bg_h_color'] : '#119af0';
				$use_extra_c1 = isset($maxgrid_data['grid_layout'][$key]['use_extra_c1']) ? maxgrid_string_to_bool($maxgrid_data['grid_layout'][$key]['use_extra_c1']) : false;
				$extra_c1 = isset($maxgrid_data['grid_layout'][$key]['extra_c1']) ? $maxgrid_data['grid_layout'][$key]['extra_c1'] : 'extra_color_1';
				$btn_f_color = isset($maxgrid_data['grid_layout'][$key]['btn_f_color']) ? $maxgrid_data['grid_layout'][$key]['btn_f_color'] : '#ffffff';
				$use_t_btn_f_h_tc = isset($maxgrid_data['grid_layout'][$key]['use_t_btn_f_h_tc']) ? maxgrid_string_to_bool($maxgrid_data['grid_layout'][$key]['use_t_btn_f_h_tc']) : false;
				$btn_f_h_tc = isset($maxgrid_data['grid_layout'][$key]['btn_f_h_tc']) ? $maxgrid_data['grid_layout'][$key]['btn_f_h_tc'] : 'term_c1';
				$btn_f_h_color = isset($maxgrid_data['grid_layout'][$key]['btn_f_h_color']) ? $maxgrid_data['grid_layout'][$key]['btn_f_h_color'] : '#ffffff';

				$html .= '<input type="hidden" class="'.$key.'_label_text" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][label_text]" value="'.$label_text.'" data-field="label_text">
						 <input type="hidden" class="'.$key.'_btn_bg_color" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][btn_bg_color]" value="'.$btn_bg_color.'" data-field="btn_bg_color">
						 <input type="hidden" class="'.$key.'_btn_bg_h_color" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][btn_bg_h_color]" value="'.$btn_bg_h_color.'" data-field="btn_bg_h_color">
						 <input type="hidden" class="'.$key.'_use_extra_c1" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][use_extra_c1]" value="'.$use_extra_c1.'" data-field="use_extra_c1">					 
						 <input type="hidden" class="'.$key.'_extra_c1" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][extra_c1]" value="'.$extra_c1.'" data-field="extra_c1">
						 <input type="hidden" class="'.$key.'_btn_f_color" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][btn_f_color]" value="'.$btn_f_color.'" data-field="btn_f_color">
						 <input type="hidden" class="'.$key.'_btn_f_h_color" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][btn_f_h_color]" value="'.$btn_f_h_color.'" data-field="btn_f_h_color">
						 <input type="hidden" class="'.$key.'_use_t_btn_f_h_tc" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][use_t_btn_f_h_tc]" value="'.$use_t_btn_f_h_tc.'" data-field="use_t_btn_f_h_tc">					 
						 <input type="hidden" class="'.$key.'_btn_f_h_tc" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][btn_f_h_tc]" value="'.$btn_f_h_tc.'" data-field="btn_f_h_tc">';

				$html .= '<ul class="elements_container maxgrid_sortable">';

				$def_post_description_data = array( 
								'categories' => 'Categories',
								'readmore' => 'Read More Button',
				);
				$post_description_data = isset($maxgrid_data['grid_layout']['post_description']) ? $maxgrid_data['grid_layout']['post_description'] : $def_post_description_data;
				
				$index = 0;
				foreach ( $post_description_data as $desc_key => $desc_value ){

					if ( $desc_key == 'label_text' || $desc_key == 'btn_bg_color' || $desc_key == 'btn_bg_h_color' || $desc_key == 'use_extra_c1' || $desc_key == 'extra_c1' || $desc_key == 'btn_f_color' || $desc_key == 'btn_f_h_color' || $desc_key == 'use_t_btn_f_h_tc' || $desc_key == 'btn_f_h_tc' || $desc_key == 'btn_font_color' ){
						continue;
					}

					if ( in_array($desc_key, $post_description_data) && $layout_data[$key][$desc_key] != 'disabled' || empty($current_option) && $layout_data[$key][$desc_key] != 'disabled' ) {
						if ( $desc_key == 'readmore' ) {
							$html .= '<li class="row_element edit"><span class="el_remove dashicons dashicons-no-alt" data-row="post_description" data-element="'.$desc_key.'"><input type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$desc_key.']" value="'.$desc_key.'">
							</span>'.$def_post_description_data[$desc_key].'<span class="edit_row element dashicons dashicons-edit" data-row-id="'.$key.'" data-action="readmore_element" data-ui-panel-title="Read More Button - Settings"></span></li>';
						} else {
							$html .= '<li class="row_element"><span class="el_remove dashicons dashicons-no-alt" data-row="post_description" data-element="'.$desc_key.'"><input type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$desc_key.']" value="'.$desc_key.'"></span>'.$def_post_description_data[$desc_key].'</li>';
						}
					} else {
						if ( $desc_key == 'readmore' ) {
							$html .= '<li class="row_element locked"><span class="el_remove dashicons dashicons-no-alt" data-row="post_description" data-element="'.$desc_key.'"><input type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$desc_key.']" value="disabled">
							</span>'.$def_post_description_data[$desc_key].'<span class="edit_row element dashicons dashicons-edit" data-row-id="'.$key.'" data-action="readmore_element" data-ui-panel-title="Read More Button - Settings"></span></li>';
							$index ++;
						} else { 
							$html .= '<li class="row_element locked"><span class="el_remove dashicons dashicons-no-alt" data-row="post_description" data-element="'.$desc_key.'"><input type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$desc_key.']" value="disabled"></span>'.$def_post_description_data[$desc_key].'</li>';
							$index ++;
						}
					}
				}

				if ( $index == 0 ) { $unlock_restore_btn = ' locked'; } else {$unlock_restore_btn = '';}

				$html .= '<span class="restore dashicons dashicons-update'.$unlock_restore_btn.'" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.$restore_el_href_title.'">&nbsp;</span>';
				$html .= '</ul>';

				$html .= '<span class="edit_row bar second-pos maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="post_description" data-action="description_row" data-ui-panel-title="The Excerpt Settings"></span>';
				$html .= $remove_icon;
			} else if ( strpos($key, 'info_bar') !== false ) {

				$html .= '<div class="inner-storable-container">
						  <ul class="elements_container maxgrid_sortable">';

				$def_date_bar_data = array( 
								'author' => 'Author',							
								'fill_space' => 'Fill Space',
								'date' => 'Date',
								'comments' => 'Comments',
				);
				$date_bar_data = isset($maxgrid_data['grid_layout'][$key]) ? $maxgrid_data['grid_layout'][$key] : $def_date_bar_data;

				$current_date_options = isset($maxgrid_data['grid_layout'][$key]['date_options']) ? $maxgrid_data['grid_layout'][$key]['date_options'] : $def_val->date_options_default();
				$index = 0;
				foreach ( $date_bar_data as $date_key => $auth_value ){
					$empty_class = '';
					if ( $date_key == 'clone_id' || $date_key == 'date_options' || $date_key == 'datebar_css' ){
						continue;
					}
					if ($date_key == 'fill_space'){
						$empty_class = 'class="fill_space"';
					}
					
					if ( in_array($date_key, $date_bar_data ) && $layout_data[$key][$date_key] != 'disabled' || empty($current_option) && $layout_data[$key][$date_key] != 'disabled' ) {
						if ( $date_key == 'date' ) {
							$html .= '<li class="row_element edit"><span class="el_remove dashicons dashicons-no-alt" data-row="info_bar" data-element="'.$date_key.'" data-editable="true"><input '.$empty_class.'type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$date_key.']" value="'.$date_key.'"></span>'.$def_date_bar_data[$date_key].'<span class="edit_row element dashicons dashicons-edit" data-row-id="'.$key.'" data-action="date_options" data-ui-panel-title="Date Settings"><input class="'.$key.'_date_options" type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][date_options]" value="'.$current_date_options.'"></span></li>';
						} else {
							$html .= '<li class="row_element"><span class="el_remove dashicons dashicons-no-alt" data-row="info_bar" data-element="'.$date_key.'"><input '.$empty_class.'type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$date_key.']" value="'.$date_key.'"></span>'.$def_date_bar_data[$date_key].'</li>';
						}
					} else {
						if ( $date_key == 'date' ) {
							$html .= '<li class="row_element locked"><span class="el_remove dashicons dashicons-no-alt" data-row="info_bar" data-element="'.$date_key.'" data-editable="true"><input '.$empty_class.'type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$date_key.']" value="disabled"></span>'.$def_date_bar_data[$date_key].'<span class="edit_row element dashicons dashicons-edit" data-row-id="'.$key.'" data-action="date_options" data-ui-panel-title="Date Settings"><input class="'.$key.'_date_options" type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][date_options]" value="'.$current_date_options.'"></span></li>';
							$index ++;

						} else {
							$html .= '<li class="row_element locked"><span class="el_remove dashicons dashicons-no-alt" data-row="info_bar" data-element="'.$date_key.'"><input '.$empty_class.'type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$date_key.']" value="disabled"></span>'.$def_date_bar_data[$date_key].'</li>';
						}

						$index ++;
					}
				}
				if ( $index == 0 ) { $unlock_restore_btn = ' locked'; } else {$unlock_restore_btn = '';}

				$html .= '<span class="restore dashicons dashicons-update'.$unlock_restore_btn.'" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.$restore_el_href_title.'">&nbsp;</span>';
				$html .= '</ul></div>';

				$html .= '<span class="edit_row bar maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="info_bar" data-action="info_row" data-ui-panel-title="Meta Data Settings"></span>';
				$html .= '<span class="duplicate_row maxgrid_ui-btn" data-row-id="'.$root_name.'" data-action="info_row" data-bar="info_bar"><i class="far fa-clone" aria-hidden="true"></i></span>'.$remove_icon;

				$datebar_css = isset($maxgrid_data['grid_layout'][$key]['datebar_css']) ? $maxgrid_data['grid_layout'][$key]['datebar_css'] : $def_val->datebar_css();

				$html .= '<input type="hidden" id="'.$key.'_datebar_css" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][datebar_css]" value="'.$datebar_css.'">';

			} else if ( strpos($key, 'stats_bar') !== false ) {
				$args = array(
					'key' 			=> $key,
					'data' 			=> $maxgrid_data,
					'ui_args' 		=> $ui_args,
					'layout_data' 	=> $layout_data,
					'tooltip_style' => $tooltip_style,
					'def_val' 		=> $def_val,
					'restore_title' => $restore_el_href_title,
					'root_name' 	=> $root_name,
					'remove_icon' 	=> $remove_icon,
				);
				
				$elements = new Elements;
				$html .= $elements->statsbar_element_html( $args );
				
			} else if ( strpos($key, 'divider_bar') !== false ) {			
				$html .= '<div></div>
							<span class="edit_row bar second-pos maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="'.$key.'" data-action="divider_row" data-ui-panel-title="Divider Settings"></span>'.$remove_icon;
			}
			$html .= '</header></li>';
			
		}
		$html .= '</ul>';

		return $html;
	}	
}
new Max_Grid_Builder;