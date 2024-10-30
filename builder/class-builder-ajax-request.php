<?php
/**
 * Max Grid Builder - Builder Ajax requests.
 */

use \MaxGrid\Youtube;
use \MaxGrid\Table;
use \MaxGrid\g_recaptcha; // include this if g_recaptcha_checker() used

defined( 'ABSPATH' ) || exit;

/**
 * @class Max_Grid_Builder_Ajax_Request.
 */
class Max_Grid_Builder_Ajax_Request {
	
	/**
	 * Max Grid Builder - The Builder.
	 *
	 * @var Max_Grid_Builder
	 */
	public $builder = null;
	
	/**
	 * require max grid plus class
	 */
	public $required_class;
	
	/**
	 * require max grid plus note
	 */
	public $required_premium_note;
	
	/**
	 * Constructor
	 */
	public function __construct() {		
		$ajax = array(
			'maxgrid_get_default_settings'	=> 'get_default_settings',
			'maxgrid_duplicate_template' 	=> 'duplicate_template',
			'maxgrid_add_row' 				=> 'elements_panel',
			'maxgrid_layout_preset_edit' 	=> 'edit_layout',
			'maxgrid_add_new_element' 		=> 'add_new_element',
			'maxgrid_filter_row' 			=> 'grid_filter',
			'maxgrid_lightbox_row' 			=> 'lightbox_settings',
			'maxgrid_about_builder' 		=> 'about_builder',
			'maxgrid_restore_all' 			=> 'restore_all_elements',
			'maxgrid_featured_row' 			=> 'featured_element',
			'maxgrid_title_row' 			=> 'title_element',
			'maxgrid_description_row'		=> 'summary_element',
			'maxgrid_readmore_element' 		=> 'readmore_element',
			'maxgrid_info_row' 				=> 'post_meta_element',
			'maxgrid_date_options' 			=> 'date_time_options',
			'maxgrid_blocks_row' 			=> 'container_block',
			'maxgrid_add_to_cart_row' 		=> 'add_to_cart_element',
			'maxgrid_audio_row' 			=> 'audio_player_element',
			'maxgrid_download_row' 			=> 'download_element',
			'maxgrid_average_rating_row' 	=> 'average_rating_element',
			'maxgrid_ytb_description_row' 	=> 'ytb_video_description_element',
			'maxgrid_divider_row' 			=> 'divider_element',
			'maxgrid_stats_row' 			=> 'statsbar_element',
			'maxgrid_sharethis_options' 	=> 'sharethis_options',
			'maxgrid_youtube_api_checker' 	=> 'youtube_api_checker',
		);
		
		foreach($ajax as $action => $function){
			add_action( 'wp_ajax_'.$action, array( $this, $function ) );
			add_action( 'wp_ajax_nopriv_'.$action, array( $this, $function ) );
		}
		
		$this->required_class = !is_maxgrid_premium_activated() ? 'mgpremium-require__class' : '';
		$this->required_premium_note = !is_maxgrid_premium_activated() ? maxgrid_premium_require_note(MAXGRID_SITE_HOME_PAGE) : '';
	}
	
	/**
	 * Get default settings.
	 *
	 * @return string
	 */
	public function get_default_settings() {
		$func = sanitize_text_field( $_POST['settings'] );
		$output = maxgrid()->settings->$func;
		echo maxgrid_array_to_string($output);
		die();
	}	
	
	/**
	 * Edit layout.
	 *
	 * @return string
	 */
	public function edit_layout() {
		global $source_type;
		$builder = new Max_Grid_Builder;
		$pname = sanitize_text_field( $_POST['pslug'] );
		$source_type = sanitize_text_field( $_POST['source_type'] );
		
		// strip out all whitespace
		$pname_clean = str_replace(' ', '_', $pname);

		// convert the string to all lowercase
		$pslug = strtolower($pname_clean);
		
		$args = array('pslug' => $pslug, 'force_source_default' => false , 'source_type' => $source_type);
		echo $builder->construct($args);
		die();
	}	

	/**
	 * Duplicate template.
	 *
	 * @return string
	 */
	public function duplicate_template() {
		global $source_type;

		$source_type = sanitize_text_field( $_POST['source_type'] );
		$pname = sanitize_text_field( $_POST['preset_name'] );

		// strip out all whitespace
		$pname_clean = str_replace(' ', '_', $pname);

		// convert the string to all lowercase
		$pslug = strtolower($pname_clean);

		$pcontent = serialize(get_option( MAXGRID_BUILDER_OPT_NAME ));

		/**
		 * @var array $data The associative array containing fieldnames as keys and values
		 */
		$data = array(
				'source_type' => $source_type,
				'pslug' 	  => $pslug,
				'pname' 	  => $pname,
				'pcontent' 	  => $pcontent,
			);

		/**
		 * @var array $check Check if a record exist
		 */
		$action_type = isset($_POST['action_type']) ? sanitize_text_field( $_POST['action_type'] ) : 'save';
		$target = array(
				'action_type' => $action_type,
				'key' 		  => 'pname',
				'value' 	  => $pname,
			);

		/**
		 * Call Table Class to insert new record (layout preset)
		 */
		$insert_record = maxgrid()->table;
		$insert_record->insertRecord($data, $target);
		
		die();
	}
	
	/**
	 * Element to add panel.
	 *
	 * @return string
	 */
	public function elements_panel() {
		$current 	 = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->add_row;
		$source_type = sanitize_text_field( $_POST['source_type'] );
		
		$source_name = array(
			'post' 			 => 'Post',
			'product' 		 => 'Product',
			'download' 		 => 'Download',
			'youtube_stream' => 'Video',
		);
		
		$data = array(
			'featured_bar' => array(
				'title' 		=> 'The Featured',
				'description' 	=> 'Add Featured image / video.',
				'icon' 			=> '\f128',
			),
			'post_title' => array(
				'title' 		=> 'Post Title',
				'description' 	=> 'Add '. $source_name[$source_type].' title',
				'icon' 			=> '\f217',
			),
			'post_description' 	=> array(
				'title' 		=> 'The Excerpt',
				'description' 	=> 'Add Excerpt, '.$source_name[$source_type].' categories and Read more button.',
				'icon' 			=> '\f206',
			),
			'info_bar' => array(
				'title' 		=> 'Meta Data',
				'description' 	=> 'Add Author name, Publication date and Comments link.',
				'icon' 			=> '\f510',
			),
			'divider_bar' => array(
				'title' 		=> 'Divider',
				'description' 	=> 'Add space between elements',
				'icon' 			=> '\f460',
				'duplicata' 	=> true,
			),
		);
		
		if ( is_maxgrid_woo_activated() || is_maxgrid_download_activated() ) {
			$data['average_rating_bar'] = array(
				'title' 		=> 'Average Rating',
				'description' 	=> 'This will show a post\'s average rating score.',
				'icon' 			=> '\f155 \f155 \f155 \f459 \f154',
			);
		}
		if ( is_maxgrid_woo_activated() ) {
			$data['add_to_cart_bar'] = array(
										'title' 		=> 'Add To Cart',
										'description' 	=> 'Add Product Price, Quantity and “Add To Cart” button.',
										'icon' 			=> '\f174',
										'parent' 		=> 'woo-bar',
									);
			$data['woo_stats_bar'] = array(
				'title' 		=> 'Product Stats',
				'description' 	=> 'Add Product statistics.',
				'icon' 			=> '\f238',
				'parent' 		=> 'woo-bar',
			);
		}
		//if ( is_maxgrid_download_activated() ) {
			$data['download_bar'] = array(
				'title' 		=> 'Download',
				'description'	=> 'Add a Download button.',
				'icon' 			=> '\f316',
			);
		//}
		
		//if ( is_maxgrid_premium_activated() ) {
			$data['stats_bar'] 	= array(
				'title' 		=> 'Post Stats',
				'description' 	=> 'Add Share Button and (Downloads, Views & Reviews counters).',
				'icon' 			=> '\f238',
			);
			
			$data['audio_bar'] 	= array(
				'title' 		=> 'Audio Player',
				'description' 	=> 'Add Audio player.',
				'icon' 			=> '\f521',
			);
		//}
		
		if ( is_maxgrid_youtube_activated() ) {
			$data['ytb_description'] = array(
				'title' 		=> 'Video Description',
				'description' 	=> 'Add brief description of the Youtube video.',
				'icon' 			=> '\f493',
				'parent' 		=> 'ytb-bar',
			);
			$data['ytb_vid_stats_bar'] = array(
				'title' 		=> 'Video Stats',
				'description' 	=> 'Add Youtube video statistics.',
				'icon' 			=> '\f238',
				'parent' 		=> 'ytb-bar',
			);
		}
		
		$sources = array(
			'post' 		 	 => array('featured_bar', 'audio_bar', 'post_title', 'post_description', 'info_bar', 'divider_bar', 'download_bar', 'stats_bar'),
			'download' 		 => array('featured_bar', 'audio_bar', 'post_title', 'post_description', 'info_bar', 'divider_bar', 'download_bar', 'average_rating_bar', 'stats_bar'),
			'product' 		 => array('featured_bar', 'audio_bar', 'post_title', 'post_description', 'info_bar', 'divider_bar', 'add_to_cart_bar', 'average_rating_bar', 'woo_stats_bar'),
			'youtube_stream' => array('featured_bar', 'post_title', 'ytb_description', 'divider_bar', 'ytb_vid_stats_bar'),
		);
		
		?>
		<!-- add this for Light Theme Interface: class="ui-light-theme" -->
		<form id="blocks_row_form" action="">
			<ul class="ui-elements-panel">
			<?php
			foreach($data as $key => $value ) {
				$class_added = '';
				$class_available = '';
				$check_key = $key;
				if ( strpos($key, 'stats_bar') !== false) {
					$check_key = 'stats_bar';
				}

				if ( !in_array($key, $sources[$source_type]) ) {
					$class_available = ' el-not_available';
				}
				if ( strpos($_POST['dataForm'], $check_key.'&') && in_array($key, $sources[$source_type]) && $key != 'divider_bar' ) {
					$class_added = ' el-added';
				}
				if ( ( strpos($key, 'stats_bar') !== false || $key == 'audio_bar' ) && !is_maxgrid_premium_activated() ) {
					$class_added = ' el-added mgpremium-required';
				}
				if ( $key == 'download_bar' && !is_maxgrid_download_activated() ) {
					$class_added = ' el-added mgdownload-required';
				}
				
				$parent_source = isset($value['parent']) ? ' '.$value['parent'] : '';
				$dld_stats_bar = $source_type=='download' ? ' dld_stats_bar' : '';
				
				$description = $key == 'stats_bar' && $source_type == 'download' ? 'Add sharethis, download count, average ratings.' : $value['description'];
				
				?>
				<li class="element-to-insert<?php echo $parent_source . ' ' . $key . $dld_stats_bar . $class_added.$class_available;?>" data-element-id="<?php echo $key;?>"><span><strong><?php echo str_replace('Post', ucfirst(str_replace('youtube_stream', 'Video', $source_type)), $value['title']);?></strong><p><?php echo $description;?></p></span></li>
				<?php
			}
			?>
			</ul>
			</form>
			<style type="text/css">
				.element-to-insert.featured_bar:before {
					content: '<?php echo $data['featured_bar']['icon'];?>';				
				}
			<?php //if ( is_maxgrid_premium_activated() ) { ?>
				.element-to-insert.audio_bar:before {
					content: '<?php echo $data['audio_bar']['icon'];?>';				
				}
				.element-to-insert.stats_bar:before {
					content: '<?php echo $data['stats_bar']['icon'];?>';				
				}
			<?php // } ?>
				.element-to-insert.post_title:before {
					content: '<?php echo $data['post_title']['icon'];?>';				
				}
				.element-to-insert.post_description:before {
					content: '<?php echo $data['post_description']['icon'];?>';				
				}
				.element-to-insert.info_bar:before {
					content: '<?php echo $data['info_bar']['icon'];?>';
					letter-spacing: 10px;
					font-size: 28px;
				}
				.element-to-insert.divider_bar:before {
					content: '<?php echo $data['divider_bar']['icon'];?>';				
				}
				
			<?php if ( is_maxgrid_woo_activated() || is_maxgrid_download_activated() ) { ?>			
				.element-to-insert.average_rating_bar:before {
					content: '<?php echo $data['average_rating_bar']['icon'];?>';
					font-size: 21px;
					letter-spacing: 3px;
				}
			<?php } ?>
				
			<?php if ( is_maxgrid_woo_activated() ) { ?>				
				.element-to-insert.add_to_cart_bar:before {
					content: '<?php echo $data['add_to_cart_bar']['icon'];?>';				
				}
				.element-to-insert.woo_stats_bar:before {
					content: '<?php echo $data['woo_stats_bar']['icon'];?>';
				}
			<?php } ?>
				
			<?php //if ( is_maxgrid_download_activated() ) { ?>	
				.element-to-insert.download_bar:before {
					content: '<?php echo $data['download_bar']['icon'];?>';				
				}
			<?php // } ?>
				
			<?php if ( is_maxgrid_youtube_activated() ) { ?>
				.element-to-insert.ytb_description:before {
					content: '<?php echo $data['ytb_description']['icon'];?>';				
				}
				.element-to-insert.ytb_vid_stats_bar:before {
					content: '<?php echo $data['ytb_vid_stats_bar']['icon'];?>';
				}
			<?php } ?>
				
			</style>
			<?php
		die();
	}
	
	/**
	 * Element constructor. 
	 * Get all elements from DB, then extract the concerned element to add.
	 *
	 * @return string
	 */
	public function add_new_element() {
		global $source_type;
		$builder = new Max_Grid_Builder;
		
		$pslug = 'all_elements';
		$options = unserialize(maxgrid_template_load($pslug));

		$source_type  = sanitize_text_field( $_POST['source_type'] );		
		$args = array('options' => $options, 'source_type' => $source_type);
		echo $builder->construct($args);
		die();
	}
	
	/**
	 * Container block.
	 *
	 * @return string
	 */
	public function container_block() {
		global $source_type;
		$current 	 = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->blocks_default();
		$source_type = sanitize_text_field( $_POST['source_type'] );
		//maxgrid_debug($current);
		if ( $_POST['first_open'] == 'true' && is_maxgrid_templates_library() ) {
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ))['grid_layout']['rows_options']['blocks_row']);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->blocks_default();
		}
		
		?>
		<form id="blocks_row_form" action="">
			<div id="blocks_row_design_options" class="maxgrid_ui-tabcontent">
				
				<div class="design_option_block">
					<div class="maxgrid_ui-design_option">
						<?php
		
						$marg_args = array(
								'current' 	   => $current,
								'field_class'  => 'design-field',
								'style' 	   => 'padding-left: 0;',
								'simplify_control' => true,
							);

						echo $this->margin($marg_args);
		
						$pad_args = array(
								'current' 	   => $current,
								'field_class'  => 'block-grid-field',
								'style' 	   => 'padding-left: 0;',
								'simplify_control' => true,
							);

						echo $this->padding($pad_args);
							
						$bw_args = array(
								'current' 	   => $current,
								'field_class'  => 'block-grid-field',
								'style' 	   => 'padding-left: 0;',
								'simplify_control' => true,
							);

						echo $this->border_width($bw_args);
							
						$br_args = array(
								'current' 	   => $current,
								'field_class'  => 'block-grid-field',
								'style' 	   => 'padding-left: 0;',
								'simplify_control' => true,
							);

						echo $this->border_radius($br_args);
		
						?>						
					</div>
					
					<div class="maxgrid_ui-design_option_right">
						<div class="maxgrid_ui-col flex">
							<div class="maxgrid_ui_element_label">Border color</div>
							<div class="maxgrid_ui-edit_form_line" style="width: 150px;">
								<input name="border_color" type="text" data-name="border-color" id="maxgrid_border-color" value="<?php echo $current['border_color']; ?>" data-default-color="#dfdfdf" class="maxgrid-colorpicker block-grid-field" data-alpha="false"/>
							</div>
						</div>
						<div class="maxgrid_ui-col flex">
							<div class="maxgrid_ui_element_label">Border style</div>
							<select name="border_style" data-name="border-style" id="maxgrid_border-style" class="maxgrid_ui_select block-grid-field">
								<option value="solid" <?php if($current['border_style']=='solid'):echo"selected";endif;?>>Solid</option>
								<option value="dotted" <?php if($current['border_style']=='dotted'):echo"selected";endif;?>>Dotted</option>
								<option value="dashed" <?php if($current['border_style']=='dashed'):echo"selected";endif;?>>Dashed</option>
								<option value="none" <?php if($current['border_style']=='none'):echo"selected";endif;?>>None</option>
							</select>				
						</div>

						<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 5px; margin-bottom: 0;"></div>						
						<div class="maxgrid_ui-col color-picker-back-z-index flex">
							<div class="maxgrid_ui_element_label">Background Color</div>
							<div class="maxgrid_ui-edit_form_line" style="width: 150px;">
								<input name="background" type="text" data-name="background" id="maxgrid_background" value="<?php echo $current['background']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker block-grid-field"/>
							</div>							
						</div>
						<?php
							$data = array(
								'name' 	   	 => 'block_bg_tc',
								'position' 	 => 'relative',
								'current' 	 => $current,
								'footer' 	 => true,
								'tc_id' 	 => 'tc1',
								);
							$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
							echo $Max_Grid_Color_Scheme->term_color($data);
						?>						
						<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 5px; margin-bottom: 0;"></div>
						<div class="maxgrid_ui-col flex">
							<div class="maxgrid_ui_element_label">Shadow</div>
							<select name="box_shadow" id="maxgrid_block_box_shadow" class="maxgrid_ui_select block-grid-field">
								<option value="shadow" <?php if($current['box_shadow']=='shadow'):echo"selected";endif;?>>Box Shadow</option>
								<option value="h_shadow" <?php if($current['box_shadow']=='h_shadow'):echo"selected";endif;?>>Hover Box Shadow</option>
								<option value="none" <?php if($current['box_shadow']=='none'):echo"selected";endif;?>>None</option>
							</select>				
						</div>
						<div class="maxgrid_ui-col flex box_shadow_options_target">
							<div class="maxgrid_ui_element_label">Shadow Options :</div>
							<div class="maxgrid_ui-edit_form_line" style="min-height: 60px;">
								<div style="white-space: nowrap;">
									<p class="label ui-simplify">Blur radius</p><input name="shadow_blur_radius" type="text" id="maxgrid_shadow_blur_radius" value="<?php echo $current['shadow_blur_radius']; ?>" class="maxgrid_ui_input maxgrid_top maxgrid_small numbers-only block-grid-field"/>
									<p class="label ui-simplify">px</p>
								</div>
								<div style="white-space: nowrap; margin-top: 10px;" class="ui-simplify">
									<p class="label">Opacity</p><input name="shadow_opacity" type="text" id="maxgrid_shadow_opacity" value="<?php echo $current['shadow_opacity']; ?>" class="maxgrid_ui_input maxgrid_top maxgrid_small numbers-only block-grid-field"/>
									<p class="label">%</p>
								</div>
							</div>
						</div>
						<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 5px; margin-bottom: 0;"></div>
						<div class="maxgrid_ui-col flex">
							<div class="maxgrid_ui_element_label">Tooltip Style</div>
							<select name="tooltip_style" id="maxgrid_block_box_tooltip_style" class="maxgrid_ui_select block-grid-field">
								<option value="light" <?php if(isset($current['tooltip_style']) && $current['tooltip_style']=='light'):echo"selected";endif;?>>Light</option>
								<option value="dark" <?php if(isset($current['tooltip_style']) && $current['tooltip_style']=='dark'):echo"selected";endif;?>>Dark</option>
							</select>				
						</div>
						<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 5px; margin-bottom: 0;"></div>
						<div class="maxgrid_ui-col flex">
							<div class="maxgrid_ch-box">
								<input name="grid_container" type="checkbox" id="grid_container" class="maxgrid_checkbox form-field" value="1" <?php if(isset($current['grid_container']) && maxgrid_string_to_bool($current['grid_container'])==1):echo"checked";endif;?> />
								<label for="grid_container">Display grid container.</label>
							</div>
						</div>
						
						<div class="maxgrid_ui-param-heading-wrapper"> List View Layout Options</div>
						<?php
						if ( $source_type != 'youtube_stream' ) {
						?>
						<div class="maxgrid_ui-col flex">
							<div class="maxgrid_ch-box">
								<input name="list_container" type="checkbox" id="list_container" class="maxgrid_checkbox form-field" value="1" <?php if(isset($current['list_container']) && maxgrid_string_to_bool($current['list_container'])==1):echo"checked";endif;?> />
								<label for="list_container">Display grid container.</label>
							</div>
						</div>
						<?php
						}
		
						$pad_list_args = array(
								'list_view'    		=> true,
								'current' 	   		=> $current,
								'simplify_control' 	=> true,
							);

						echo $this->padding($pad_list_args);
						?>
					</div>
				</div>
			</div>
			<div id="blocks_row_typography_options" class="maxgrid_ui-tabcontent">
				<div class="maxgrid_ui-col" style="max-width: 280px;">
					<div class="maxgrid_ui_element_label">Font Family</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="description_font_family" id="description_font_family" class="fontselect" type="text" value="<?php echo $current['description_font_family']; ?>" />
					</div>
				</div>
				<div class="maxgrid_ui_row-divider" style="margin-top: 20px; margin-bottom: 15px;"></div>
				<div style="display: flex;">
					<div style="width: 30%;">
						<div class="maxgrid_ui-col">
							<div class="maxgrid_ui_element_label">Font color</div>
							<div class="maxgrid_ui-edit_form_line">
								<input name="description_font_color" type="text" data-name="color" id="maxgrid_description_font_color" value="<?php echo $current['description_font_color']; ?>" data-default-color="#595959" class="maxgrid-colorpicker form-field"/>
							</div>
						</div>					
						<?php
							$data = array(
								'name' 	   	 => 'block_f_tc',
								'position' 	 => 'relative',
								'current' 	 => $current,
								'footer' 	 => true,
								'tc_id' 	 => 'tc2',
								);
							$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
							echo $Max_Grid_Color_Scheme->term_color($data);
						?>						
					</div>
					<div style="width: 35%;">			
						<div class="maxgrid_ui-col">
							<div class="maxgrid_ui_element_label">Link Color</div>
							<div class="maxgrid_ui-edit_form_line">
								<input name="description_link_color" type="text" data-name="color" id="maxgrid_description_link_color" value="<?php echo $current['description_link_color']; ?>" data-default-color="#7a7a7a" class="maxgrid-colorpicker form-field"/>
							</div>
						</div>
						<div class="maxgrid_ui-col">
							<div class="maxgrid_ui_element_label">Link Style</div>
							<div class="maxgrid_ui-edit_form_line">
								<div class="maxgrid_ch-box">
									<input name="description_link_underline" type="checkbox" id="description_link_underline" class="form-field" value="1" <?php if(isset($current['description_link_underline']) && maxgrid_string_to_bool($current['description_link_underline'])==1):echo"checked";endif;?> >
									<label for="description_link_underline">Underline.</label>
								</div>
							</div>
						</div>				
					</div>
					<div style="width: 35%;">				
						<div class="maxgrid_ui-col">
							<div class="maxgrid_ui_element_label">Link Hover Color</div>
							<div class="maxgrid_ui-edit_form_line">
								<input name="description_link_h_color" type="text" data-name="color" id="description_link_h_color" value="<?php echo $current['description_link_h_color']; ?>" data-default-color="#31c1eb" class="maxgrid-colorpicker form-field"/>
							</div>
						</div>
						<?php
							$data = array(
								'name' 	   	 => 'extra_c1',
								'position' 	 => 'relative',
								'current' 	 => $current,
								//'form_field' => true,
								'footer' 	 => true,
								);
							$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
							echo $Max_Grid_Color_Scheme->color_scheme($data);
						?>
						<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 10px;"></div>
						<div class="maxgrid_ui-col">
							<div class="maxgrid_ui_element_label">Link Hover Style</div>
							<div class="maxgrid_ui-edit_form_line">
								<div class="maxgrid_ch-box">
									<input name="description_link_h_underline" type="checkbox" id="description_link_h_underline" class="form-field" value="1" <?php if(isset($current['description_link_h_underline']) && maxgrid_string_to_bool($current['description_link_h_underline'])==1):echo"checked";endif;?> >
									<label for="description_link_h_underline">Underline.</label>
								</div>
							</div>
						</div>				
					</div>
				</div>
				<div class="maxgrid_ui_row-divider" style="margin-top: 20px; margin-bottom: 30px;"></div>
			</div>
		<?php if ( $source_type != 'youtube_stream' && is_maxgrid_premium_activated() ) { 
			
			echo maxgrid()->premium->ribbons_settings($current, $source_type);
			
		}		
		die();
	}
	
	/**
	 * Lightbox settings.
	 *
	 * @return string
	 */
	public function grid_filter() {
		global $source_type;
		$current = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field($_POST['dataForm'])) : maxgrid()->settings->filter_default();
		$source_type = isset($_POST['source_type']) ?  sanitize_text_field( $_POST['source_type'] ) : 'post';
		$bar_name =  sanitize_text_field( $_POST['bar_name'] );
		if ( $_POST['first_open'] == 'true') {
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field($_POST['pslug'])))['grid_layout']['rows_options'][$bar_name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->filter_default();
		}
		?>
		<form id="filter_row_form" action="">			
			<div class="ui-panel-row post_stats_target">	
				<?php if ( $source_type != 'youtube_stream') { ?>				
				<div class="maxgrid_ui_row-divider" style="margin-bottom: 5px; margin-top: 10px;"></div>	
				<span class="maxgrid_ui_description" style="margin-left: 15px;">Select statistics elements to add.</span>
				<?php } ?>					
				<div class="maxgrid_ui-col" style="padding-top: 8;">		
					<?php
					if ( $source_type == 'youtube_stream') {
						$elements = array(
							'banner' 		=> 'Banner',
							'channel_logo' 	=> 'Channel Logo',
							'channel_name' 	=> 'Channel name',
							'subs_counter' 	=> 'Subscribers counter',
							'sub_btn' 		=> 'Subscribe button',
						);
					} else {
						$elements = array(
							'orderby' 	=> 'Order By',
							'desc-sort' => 'DESC Order',
							'asc-sort' 	=> 'ASC Order',
							'view' 		=> 'Grid/List View',
							'category' 	=> 'Category Filter',
							'tag' 		=> 'Tag Filter',
						);
					}
					$item_id = 'filter';
					$i = 0;
					foreach($elements as $key => $value) {
						?>
						<div class="maxgrid_ui-col">
							<div class="maxgrid_ui-edit_form_line">
								<div class="maxgrid_ch-box">
									<input type="checkbox" name="<?php echo $key; ?>" max="5" id="<?php echo $key; ?>" data-item-id="<?php echo $item_id; ?>" class="form-field" value="1" <?php if( isset($current[$key]) && maxgrid_string_to_bool($current[$key])==1):echo"checked";endif; ?> >
									<label for="<?php echo $key; ?>"><?php echo $value; ?></label>
								</div>				
							</div>
						</div>
						<?php
						$i++;
					}
					?>
				</div>	
			</div>	
			ici Posts
			<div class="maxgrid_ui_row-divider" style="margin-top: 10px; margin-bottom: 40px; border: none;"></div>
		</form>
		<?php	
		die();
	}
	
	/**
	 * Lightbox settings.
	 *
	 * @return string
	 */
	public function lightbox_settings() {
		global $source_type;
		$current = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field($_POST['dataForm'])) : maxgrid()->settings->lightbox_default();
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		$bar_name =  sanitize_text_field( $_POST['bar_name'] );
		if ( $_POST['first_open'] == 'true') {
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ))['grid_layout']['rows_options'][$bar_name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->lightbox_default();
		}
		?>
		<form id="lightbox_row_form" action="">

			<?php
			if ( $_POST['source_type'] == 'product' ) {
			?>
			<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 10px; margin-bottom: 10px;">Add to cart / Quantity Settings</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Size</div>
				<div class="maxgrid_ui-edit_form_line">
					<select name="add_cart_size" id="add_cart_size" class="maxgrid_ui_select form-field">
						<option value="small" <?php if($current['add_cart_size']=='small'):echo"selected";endif;?> >Normal</option>
						<option value="medium" <?php if($current['add_cart_size']=='medium'):echo"selected";endif;?> >Medium</option>
						<option value="large" <?php if($current['add_cart_size']=='large'):echo"selected";endif;?> >Large</option>
					</select>
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Spin Layout</div>
				<div class="maxgrid_ui-edit_form_line">
					<select name="spin_layout" id="spin_layout" class="maxgrid_ui_select spin_layout form-field">
						<option value="inner_spin" <?php if($current['spin_layout']=='inner_spin'):echo"selected";endif;?> >Inner Spin</option>
						<option value="outer_spin" <?php if($current['spin_layout']=='outer_spin'):echo"selected";endif;?> >Outer Spin</option>
					</select>
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Border Radius</div>
				<div class="maxgrid_ui-edit_form_line">
					<select name="border_radius" id="border_radius" class="maxgrid_ui_select border_radius form-field">
						<option value="rounded" <?php if($current['border_radius']=='rounded'):echo"selected";endif;?> >Rounded</option>
						<option value="pointed" <?php if($current['border_radius']=='pointed'):echo"selected";endif;?> >Pointed</option>
					</select>
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Plus-minus sign</div>
				<div class="maxgrid_ui-edit_form_line">
					<select name="sign_style" id="sign_style" class="maxgrid_ui_select sign_style form-field">
						<option value="thick" <?php if($current['sign_style']=='thick'):echo"selected";endif;?> >Thick</option>
						<option value="thin" <?php if($current['sign_style']=='thin'):echo"selected";endif;?> >Thin</option>
					</select>
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ch-box">
					<input id="disable_qty" name="disable_qty" value="1" type="checkbox" <?php if( isset($current['disable_qty']) && maxgrid_string_to_bool($current['disable_qty'])==1):echo"checked";endif; ?>>
					<label for="disable_qty">Disable The Quantity Field</label>
				</div>
			</div>
			<div class="maxgrid_ui_row-divider" style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="maxgrid_ui-col flex wp-colorpicker-col">
				<div class="maxgrid_ui_element_label">Background Color</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="color_theme" type="text" id="maxgrid_price_color_theme" value="<?php echo $current['color_theme']; ?>" data-default-color="#dd3333" class="maxgrid-colorpicker form-field"/>
				</div>
			</div>
			<?php
				$data = array(
					'name' 	   	 => 'extra_c1',
					'position' 	 => 'relative',
					'current' 	 => $current,
					'form_field' => true,
					'footer' 	 => true,				
					);
				$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
				echo $Max_Grid_Color_Scheme->color_scheme($data);
			?>
			<div class="maxgrid_ui_row-divider" style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="maxgrid_ui-col flex wp-colorpicker-col">
				<div class="maxgrid_ui_element_label">Font Color</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="button_font_color" type="text" id="maxgrid_button_font_color" value="<?php echo $current['button_font_color']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field"/>
				</div>
			</div>
			<?php
			}
			$item_id = 'lightbox';
			echo is_maxgrid_premium_activated() ? maxgrid()->premium->share_settings($current, $item_id): '';
			?>			
						
			<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 10px; margin-bottom: 10px;">General Settings</div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui-edit_form_line">
					<div class="maxgrid_ch-box">
						<input name="r_side_status" type="checkbox" id="r_side_status" class="form-field" value="1" <?php if(isset($current['r_side_status']) && maxgrid_string_to_bool($current['r_side_status'])==1):echo"checked";endif;?> >
						<label for="r_side_status">Expand playlist items by Default</label>
					</div>
				</div>
			</div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui-edit_form_line">
					<div class="maxgrid_ch-box">
						<input name="overlay_click_close" type="checkbox" id="overlay_click_close" class="form-field" value="1" <?php if(isset($current['overlay_click_close']) && maxgrid_string_to_bool($current['overlay_click_close'])==1):echo"checked";endif;?> >
						<label for="overlay_click_close">Close lightbox when overlay is clicked</label>
					</div>
				</div>
			</div>			
			<?php if($source_type == 'youtube_stream'){?>
				<div class="maxgrid_ui-col">
					<div class="maxgrid_ui-edit_form_line">
						<input name="like_dislike_links" type="checkbox" id="like_dislike_links" class="info_row-field" value="1" <?php if(isset($current['like_dislike_links']) && maxgrid_string_to_bool($current['like_dislike_links'])==1):echo"checked";endif;?> >
						<label for="like_dislike_links">Disable Like / Dislike links.</label>
					</div>
				</div>
			<?php }?>			
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui-edit_form_line">
					<div class="maxgrid_ch-box">
						<input name="search_bar" type="checkbox" id="lb_search_bar" class="form-field" value="1" <?php if(isset($current['search_bar']) && maxgrid_string_to_bool($current['search_bar'])==1):echo"checked";endif;?> >
						<label for="lb_search_bar">Search bar</label>
					</div>
				</div>
			</div>
			<div class="maxgrid_ui_row-divider" style="margin-top: 10px; margin-bottom: 10px;"></div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Lightbox Theme</div>
				<div class="maxgrid_ui-edit_form_line">
					<select id="lightbox_theme" name="theme" class="maxgrid_ui_select line_type form-field">
						<option value="lb-light-color" <?php if(isset($current['theme']) && $current['theme']=='lb-light-color'):echo"selected";endif;?>>Light</option>
						<option value="lb-dark-color" <?php if(isset($current['theme']) && $current['theme']=='lb-dark-color'):echo"selected";endif;?>>Dark</option>
					</select>
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Tooltip Style</div>
				<div class="maxgrid_ui-edit_form_line">
					<select id="lightbox_tooltip_style" name="tooltip_style" class="maxgrid_ui_select line_type form-field">
						<option value="light" <?php if(isset($current['tooltip_style']) && $current['tooltip_style']=='light'):echo"selected";endif;?>>Light</option>
						<option value="dark" <?php if(isset($current['tooltip_style']) && $current['tooltip_style']=='dark'):echo"selected";endif;?>>Dark</option>
					</select>
				</div>
			</div>

			<?php
			$is_premium_require_class = !is_maxgrid_premium_activated() ? ' class="mxg-premium-required" disabled' : '';
			$is_premium_require_name = !is_maxgrid_premium_activated() ? ' [Premium]' : '';
			?>
			
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">jQuery Image Zoom</div>
				<div class="maxgrid_ui-edit_form_line">
					<select id="line_type" name="jquery_img_zoom" class="maxgrid_ui_select line_type form-field">
						<option value="zoom_in" <?php if(isset($current['jquery_img_zoom']) && $current['jquery_img_zoom']=='zoom_in'):echo"selected";endif;?> >Image Zoom In</option>
						<option value="magnify" <?php if(isset($current['jquery_img_zoom']) && $current['jquery_img_zoom']=='magnify'):echo"selected";endif;?><?php echo $is_premium_require_class;?>>Image Magnifier Glass<?php echo $is_premium_require_name;?></option>
					</select>
				</div>
			</div>
			<div class="maxgrid_ui_row-divider" style="margin-top: 10px; margin-bottom: 40px; border: none;"></div>
		</form>
		<?php	
		die();
	}
	
	/**
	 * About Max Grid Builder - The Builder.
	 *
	 * @return string
	 */
	public function about_builder() {
		$plugin_data = get_plugin_data( MAXGRID_ABSPATH . '/max-grid.php');
		
		//maxgrid_debug($plugin_data);
		$transient_name = 'about_builder_transient';
		$transient_sec = 0 * HOUR_IN_SECONDS;
		if ( $transient_sec > 0 && ( $transient = get_transient($transient_name) ) !== false ) {
			echo $transient;
		} else {
			ob_start();
		?>	
		<span class="maxgrid_ui_description" style="margin: 15px 50px;">
			<strong><?php echo $plugin_data['Name']; ?> v <?php echo $plugin_data['Version']; ?></strong>
			<p style="min-height: 100px;">
				<?php echo $plugin_data['Description']; ?>
			</p>
			
			</p>
			<span class="about-footer">
				<a href="<?php echo $plugin_data['PluginURI'];?>/docs" target="_blank">Documentations</a> | <a href="<?php echo $plugin_data['AuthorURI'];?>/support" target="_blank">Support</a>
			</span>
		</span>
		<?php
			$response = ob_get_clean();
			set_transient( $transient_name, $response, $transient_sec );
			echo $response;
		}		
		die();
	}
	
	/**
	 * Restoring elements to their default settings.
	 *
	 * @return string
	 */
	public function restore_all_elements() {
		global $source_type;
		$builder = new Max_Grid_Builder;
		$source_type = isset($_POST['source_type']) ?  sanitize_text_field( $_POST['source_type'] ) : 'post';
		$pslug = $_POST['pslug'];
		$pname = $_POST['pname'];
		
		if ( $source_type == 'post' ) {
			$file = MAXGRID_ABSPATH.'builder/backup/post_templates.txt';
		} else if ( $source_type == 'download' ) {
			$file = MAXGRID_DL_ABSPATH.'templates/download_templates.txt';
		} else if ( $source_type == 'product' ) {
			$file = MAXGRID_WOO_ABSPATH.'templates/product_templates.txt';
		} else if ( $source_type == 'youtube_stream' ) {
			$file = MAXGRID_YTB_ABSPATH.'templates/youtube_stream_templates.txt';
		} else {
			$file = MAXGRID_ABSPATH.'builder/backup/post_templates.txt';
		}
		
		$table = maxgrid()->table;
		$default_options = $table->restoreRecord($file, $pslug, $pname, $source_type);
		//maxgrid_debug($default_options);
		update_option( MAXGRID_BUILDER_OPT_NAME, $default_options );
		
		$args = array('pslug' => $pslug, 'force_source_default' => true , 'source_type' => $source_type, 'default_options' => $default_options);
		
		echo $builder->construct($args);
		die();
	}
		
	/**
	 * featured element.
	 *
	 * @return string
	 */
	public function featured_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		$pslug = $_POST['pslug'];
		
		$current = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field(  $_POST['dataForm'] ) ) : maxgrid()->settings->featured_default();

		$item_id = sanitize_text_field( $_POST['item_id'] );

		$bar_name = sanitize_text_field( $_POST['bar_name'] );
				
		if( $_POST['first_open'] == 'true') {
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $pslug ) ) )['grid_layout']['rows_options'][$bar_name]);			
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->featured_default();
		}
		//$current = maxgrid()->settings->featured_default();
		//maxgrid_debug(unserialize(maxgrid_template_load( sanitize_text_field( $pslug ) ) ));
		
		$help_title = '<strong class="block">Swap between normal and :hover selectors</strong>
									  <p>
									   Apply filter on normal state instead of hover state.
									   </p>';
		$is_premium_require_class = !is_maxgrid_premium_activated() ? ' class="mxg-premium-required" disabled' : '';
		$is_premium_require_name = !is_maxgrid_premium_activated() ? ' [Premium]' : '';
		?>
		<form id="featured_row_form" action="">
			<div id="featured_design_options" class="maxgrid_ui-tabcontent">
				<div class="maxgrid_ui-design_option numeric-input" style="width: 40%;">
					
				<?php

					$marg_args = array(
							'current' 		=> $current,
							'field_class'  	=> 'form-field',
							'style' 		=> 'padding-left: 0;',
							'fields' 		=> array('t', 'b'),
						);

					echo $this->margin($marg_args);
					
					$bw_args = array(
							'current' 	   => $current,
							'field_class'  	=> 'form-field',
							'style' 	   => 'padding-left: 0;',
							'simplify_control' => true,
						);

					echo $this->border_width($bw_args);

					$br_args = array(
							'current' 	   => $current,
							'field_class'  	=> 'form-field',
							'style' 	   => 'padding-left: 0;',
							'simplify_control' => true,
						);

					echo $this->border_radius($br_args);

					?>
				</div>
				<div class="maxgrid_ui-design_option_right" style="padding-top: 15px; width: 56%; line-height: 1.3em;">
					<div class="maxgrid_ui-col flex">
						<div class="maxgrid_ui_element_label">Border Color</div>
						<div class="maxgrid_ui-edit_form_line" style="width: 150px;">
							<input name="border_color" type="text" data-name="border-color" id="maxgrid_border-color" value="<?php echo isset($current['border_color']) ? $current['border_color'] : ''; ?>" data-default-color="#dfdfdf" class="maxgrid-colorpicker block-grid-field" data-alpha="false"/>
						</div>
					</div>
					<div class="maxgrid_ui-col flex">
						<div class="maxgrid_ui_element_label">Border Style</div>
						<select name="border_style" data-name="border-style" id="maxgrid_border-style" class="maxgrid_ui_select block-grid-field">
							<option value="solid" <?php if(isset($current['border_style']) && $current['border_style']=='solid'):echo"selected";endif;?>>Solid</option>
							<option value="dotted" <?php if(isset($current['border_style']) &&$current['border_style']=='dotted'):echo"selected";endif;?>>Dotted</option>
							<option value="dashed" <?php if(isset($current['border_style']) &&$current['border_style']=='dashed'):echo"selected";endif;?>>Dashed</option>
							<option value="none" <?php if(isset($current['border_style']) &&$current['border_style']=='none'):echo"selected";endif;?>>None</option>
						</select>				
					</div>					
					<div class="maxgrid_ui-param-heading-wrapper">Hover Options</div>
					<div class="maxgrid_ui-col flex">
						<div class="maxgrid_ch-box">
							<input id="hover_box_shadow" name="hover_box_shadow" value="1" type="checkbox" <?php if( isset($current['hover_box_shadow']) && maxgrid_string_to_bool($current['hover_box_shadow'])==1):echo"checked";endif; ?>>
							<label for="hover_box_shadow">Inner Box Shadow</label>
						</div>
					</div>
					<div class="maxgrid_ui-col flex">
						<div class="maxgrid_ch-box">
							<input id="hover_zoom_in" name="hover_zoom_in" value="1" type="checkbox" <?php if( isset($current['hover_zoom_in']) && maxgrid_string_to_bool($current['hover_zoom_in'])==1):echo"checked";endif; ?>>
							<label for="hover_zoom_in">Zoom In</label>
						</div>
					</div>
					<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 0;"></div>
										
					<div class="maxgrid_ui-col flex">
						<div class="maxgrid_ui_element_label">Filter</div>
						<select name="filter" id="featured_filter-style" class="maxgrid_ui_select form-field">
							<option value="grayscale"<?php if(isset($current['filter']) && $current['filter']=='grayscale'):echo"selected";endif;?>>Grayscale</option>
							<option value="blur" <?php if(isset($current['filter']) && $current['filter']=='blur'):echo"selected";endif;?><?php echo $is_premium_require_class;?>>Blur<?php echo $is_premium_require_name;?></option>
							<option value="hue-rotate"<?php if(isset($current['filter']) && $current['filter']=='hue-rotate'):echo"selected";endif;?><?php echo $is_premium_require_class;?>>Hue Rotate<?php echo $is_premium_require_name;?></option>
							<option value="invert"<?php if(isset($current['filter']) && $current['filter']=='invert'):echo"selected";endif;?><?php echo $is_premium_require_class;?>>Invert<?php echo $is_premium_require_name;?></option>
							<option value="sepia"<?php if(isset($current['filter']) && $current['filter']=='sepia'):echo"selected";endif;?><?php echo $is_premium_require_class;?>>Sepia<?php echo $is_premium_require_name;?></option>
							<option value="none"<?php if(isset($current['filter']) && $current['filter']=='none'):echo"selected";endif;?>>None</option>
						</select>			
					</div>
					<div class="maxgrid_ui-col hidden_opt_element blur">
						<div class="maxgrid_ui_element_label"></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="blur_value" id="maxgrid_blur_value" value="<?php echo $current['blur_value'];?>" class="maxgrid_ui_input numbers-only form-field" type="text"><p><strong>px</strong></p>
						</div>
					</div>
					<div class="maxgrid_ui-col hidden_opt_element grayscale">
						<div class="maxgrid_ui_element_label"></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="grayscale_value" id="maxgrid_grayscale_value" value="<?php echo $current['grayscale_value'];?>" class="maxgrid_ui_input numbers-only form-field" type="text"><p><strong>%</strong></p>
						</div>
					</div>
					<div class="maxgrid_ui-col hidden_opt_element hue-rotate">
						<div class="maxgrid_ui_element_label"></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="hue-rotate_value" id="maxgrid_hue_rotate_value" value="<?php echo $current['hue-rotate_value'];?>" class="maxgrid_ui_input numbers-only form-field" type="text"><p><strong>deg</strong></p>
						</div>
					</div>

					<div class="maxgrid_ui-col hidden_opt_element invert">
						<div class="maxgrid_ui_element_label"></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="invert_value" id="maxgrid_invert_value" value="<?php echo $current['invert_value'];?>" class="maxgrid_ui_input numbers-only form-field" type="text"><p><strong>%</strong></p>
						</div>
					</div>
					<div class="maxgrid_ui-col hidden_opt_element sepia">
						<div class="maxgrid_ui_element_label"></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="sepia_value" id="maxgrid_sepia_value" value="<?php echo $current['sepia_value'];?>" class="maxgrid_ui_input numbers-only form-field" type="text"><p><strong>%</strong></p>
						</div>
					</div>
					<div class="maxgrid_ui-col flex" style="padding-top: 0; height: 30px;">
						<div class="maxgrid_ui_element_label"></div>
						<div class="maxgrid_ch-box">
							<input id="swap_selectors" name="swap_selectors" value="1" type="checkbox" <?php if( isset($current['swap_selectors']) && maxgrid_string_to_bool($current['swap_selectors'])==1):echo"checked";endif; ?>>
							<label for="swap_selectors">Swap Selectors</label>
							<div id="help-trigger" data-title="<?php echo base64_encode($help_title);?>" data-rel="help-tooltip" data-style="light" data-position="fixed">?</div>
						</div>
					</div>
					<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 0;"></div>
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label"><strong>Buttons BG color</strong></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="button_color" type="text" id="maxgrid_button_color" value="<?php echo $current['button_color']; ?>" data-default-color="rgba(26, 38, 41, 0.8)" class="maxgrid-colorpicker form-field" data-alpha="true"/>
						</div>
					</div>
					<div class="maxgrid_ui_row-divider" style="height: 15px; margin-bottom: 5px;"></div>
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label"><strong>Buttons BG Hover color</strong></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="button_hover_color" type="text" id="maxgrid_button_hover_color" value="<?php echo $current['button_hover_color']; ?>" data-default-color="#31c1eb" class="maxgrid-colorpicker form-field" data-alpha="true"/>
						</div>
					</div>					
					<?php
					if ( $source_type != 'youtube_stream' ) {
						$data = array(
							'name' 	   	 => 'btn_bg_h_tc',
							'position' 	 => 'relative',
							'current' 	 => $current,
							'footer' 	 => true,
							'tc_id' 	 => 'tc1',
							);
						$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
						echo $Max_Grid_Color_Scheme->term_color($data);
					}
					?>					
					<?php
						$data = array(
							'name' 	   	 => 'extra_c1',
							'position' 	 => 'relative',
							'current' 	 => $current,
							//'form_field' => true,
							'footer' 	 => true,
							);
						$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
						echo $Max_Grid_Color_Scheme->color_scheme($data);
					?>

					<div class="maxgrid_ui_row-divider" style="height: 15px; margin-bottom: 5px;"></div>

					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label"><strong>Buttons Font color</strong></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="button_font_color" type="text" id="maxgrid_button_font_color" value="<?php echo isset($current['button_font_color']) ? $current['button_font_color'] : '#ffffff'; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field" data-alpha="true"/>
						</div>
					</div>

					<div class="maxgrid_ui_row-divider" style="height: 15px; margin-bottom: 5px;"></div>

					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label"><strong>Buttons Font Hover color</strong></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="button_font_hover_color" type="text" id="maxgrid_button_font_hover_color" value="<?php echo isset($current['button_font_hover_color']) ? $current['button_font_hover_color'] : '#ffffff'; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field" data-alpha="true"/>
						</div>
					</div>

					<?php
						$data = array(
							'name' 	   	 => 'extra_c2',
							'position' 	 => 'relative',
							'current' 	 => $current,
							//'form_field' => true,
							//'footer' 	 => true,
							);

						$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
						echo $Max_Grid_Color_Scheme->color_scheme($data);
					?>

				</div>
				<?php echo maxgrid_fit_width_field($current); ?>
			</div>

			<div id="elements_options" class="maxgrid_ui-tabcontent">
				<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 30px;">Select Elements To Add</div>
				<div class="maxgrid_ui-col">		
					<?php
					if($source_type == 'youtube_stream') {
						$elements = array(
							'share_this' 	=> 'ShareThis Button',
							'duration' 		=> 'Video Duration',
							'the_title' 	=> 'The Title',
							'post_stats' 	=> 'Video Stats',
							'divider' 		=> true,
						);					
					} else if($source_type == 'product') {
						$elements = array(
							'love_this' 	=> 'Love Button',
							'share_this' 	=> 'ShareThis Button',
							'category' 		=> 'Category',
							'price' 		=> 'Product Price',
							'divider' 		=> true,
							'the_title' 	=> 'The Title',
							'post_excerpt' 	=> 'Post Excerpt',
							'post_stats' 	=> 'Product Stats',
							'add_to_cart' 	=> 'Add to Cart Link',
						);
					} else if($source_type == 'download') {
						$elements = array(
							'love_this' 	=> 'Love Button',
							'share_this' 	=> 'ShareThis Button',
							'download_btn' 	=> 'Download Button',
							'category' 		=> 'Category',
							'duration' 		=> 'Video Duration',
							'divider' 		=> true,
							'the_title' 	=> 'The Title',
							'post_excerpt' 	=> 'Download Excerpt',
							'post_stats' 	=> 'Download Stats',
						);
					} else {
						$elements = [];						
						
						$elements['category'] 	  = 'Category';
						$elements['duration'] 	  = 'Video Duration';
						$elements['love_this'] 	  = 'Love Button';
						$elements['share_this']   = 'ShareThis Button';						
						$elements['divider'] 	  = '';
						$elements['the_title'] 	  = 'The Title';						
						$elements['post_excerpt'] = 'Post Excerpt';						
						$elements['post_stats']   = 'Post Stats';
					}
					$required_premium_note = !is_maxgrid_premium_activated() ? 'Premium version required' . $this->required_premium_note : '';
					$cat_help_title = $required_premium_note.'<div class="tt_line-divider"></div><strong class="block">Term/Category color</strong>
									  <p>
									   Go to <strong>Posts</strong> > <strong>Categories</strong> and edit the category to customize the category color.<br>
									   <span class="tt_divider"></span>
									   <span class="note">
										   <strong>WooCommerce path</strong> : Products > Categories<br>
										   <strong>Download path</strong> : Download > Categories<br>
										</span>
										Learn more about <a href="'.MAXGRID_SITE_HOME_PAGE.'/docs/#term_color" target="_blank">Term Colors</a>
									   </p>';

					$category_help = '<div id="help-trigger" data-title="' . base64_encode($cat_help_title) . '" data-rel="help-tooltip" data-style="light" data-position="fixed">?</div>';

					$i = 0;
					foreach($elements as $key => $value){
						$is_disabled = '';
						$r_padd 	 = '';
						if ( !is_maxgrid_premium_activated() && in_array($key, array('love_this', 'share_this', 'post_stats') ) ) {
							$is_disabled = ' mxg-premium-required';
							$r_padd = !is_maxgrid_premium_activated() ? ' style="padding-right: 50px;"' : '';
						} 
							
						if ($key == 'divider') {
							?>
							<div class="ui-panel-row share_this_target" data-display="hidded" style="margin: 30px -15px 0 -15px">
								<?php
								echo is_maxgrid_premium_activated() ? maxgrid()->premium->share_settings($current, $item_id): '';
								?>
							</div>
							
						<?php if($source_type == 'download' ) { ?>
					
							<div class="ui-panel-row download_btn_target" data-display="hidded">
								<div class="maxgrid_ui_row-divider" style="margin-bottom: 5px; margin-top: 10px;"></div>	
								<span class="maxgrid_ui_description" style="margin-left: 15px;"><strong>Download Button Options:</strong></span>
								<div class="ui-panel-row">
									<div class="maxgrid_ui-col flex">
										<div class="maxgrid_ui_element_label"><strong>Background Color</strong></div>
										<div class="maxgrid_ui-edit_form_line">
											<input name="dld_b_bg_c" type="text" id="maxgrid_dld_b_bg_c" value="<?php echo isset($current['dld_b_bg_c']) ? $current['dld_b_bg_c'] : '#2cba6c'; ?>" data-default-color="#2cba6c" class="maxgrid-colorpicker form-field" data-alpha="true"/>
										</div>
									</div>
									<div class="maxgrid_ui-col flex">
										<div class="maxgrid_ui_element_label"><strong>Font Color</strong></div>
										<div class="maxgrid_ui-edit_form_line">
											<input name="dld_b_f_c" type="text" id="maxgrid_dld_b_f_c" value="<?php echo isset($current['dld_b_f_c']) ? $current['dld_b_f_c'] : '#ffffff'; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field" data-alpha="true"/>
										</div>
									</div>

								</div>
							</div>					
						<?php } ?>						
						<?php if($source_type != 'youtube_stream') {
							$usetermcolors_class = !is_maxgrid_premium_activated() ? ' mxg-premium-required' : '';
							$usetermcolors_r_padd = !is_maxgrid_premium_activated() ? ' style="padding-right: 50px;"' : '';									
							?>					
							<div class="ui-panel-row category_target" data-display="hidded">
								<div class="maxgrid_ui_row-divider" style="margin-bottom: 5px; margin-top: 10px;"></div>	
								<span class="maxgrid_ui_description" style="margin-left: 15px;">Category options:</span>
								<div class="maxgrid_ui-col flex">
									<div class="maxgrid_ch-box<?php echo $usetermcolors_class;?>"<?php echo $usetermcolors_r_padd;?>>
										<input id="custom_term_color" name="custom_term_color" value="1" type="checkbox" class="form-field extras-triggers" data-target="custom_term_color_target" data-state-reverse="1" <?php if( isset($current['custom_term_color']) && maxgrid_string_to_bool($current['custom_term_color'])==1):echo"checked";endif; ?>>
										<label for="custom_term_color">Use Term Colors</label>
									</div><?php echo $category_help; ?>
								</div>
								<div class="ui-panel-row custom_term_color_target" data-display="hidded">
									<div class="maxgrid_ui-col flex">
										<div class="maxgrid_ui_element_label"><strong>Background Color</strong></div>
										<div class="maxgrid_ui-edit_form_line">
											<input name="bg_color_term" type="text" id="maxgrid_bg_color_term" value="<?php echo $current['bg_color_term']; ?>" data-default-color="rgba(26,38,41,0.8)" class="maxgrid-colorpicker form-field" data-alpha="true"/>
										</div>
									</div>
									<div class="maxgrid_ui-col flex">
										<div class="maxgrid_ui_element_label"><strong>Font Color</strong></div>
										<div class="maxgrid_ui-edit_form_line">
											<input name="color_term" type="text" id="maxgrid_color_term" value="<?php echo $current['color_term']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field" data-alpha="true"/>
										</div>
									</div>
								</div>
							</div>
							<?php } ?>
							<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 30px;margin-left: -15px;margin-right: -15px;">Meta Fields</div>
							<?php
							continue;
						}

						$triggers = '';
						$target   = '';
						if ( in_array($key, array('share_this', 'download_btn', 'post_stats', 'category')) ) {
							$triggers = ' extras-triggers';
							$target   = ' data-target="'.$key.'_target"';						
						}
							
						$off_add_to_cart = $key == 'add_to_cart' ? 'links_icons_target': '';
						?>
						<div class="maxgrid_ui-col<?php echo $off_add_to_cart; ?>" style="display: inline-block;  width: auto;">
							<div class="maxgrid_ui-edit_form_line">
								<div class="maxgrid_ch-box<?php echo $is_disabled; ?>"<?php echo $r_padd; ?>>
									<input type="checkbox" name="<?php echo $key; ?>" max="5" id="<?php echo $key; ?>" data-item-id="<?php echo $item_id; ?>" class="form-field<?php echo $triggers; ?>" value="1" <?php if( isset($current[$key]) && maxgrid_string_to_bool($current[$key])==1):echo"checked";endif; ?> <?php echo $target; ?>>
									<label for="<?php echo $key; ?>"><?php echo $value; ?></label>
								</div>				
							</div>
						</div>
						<?php
						$i++;
					}
		
					if ( is_maxgrid_premium_activated() ) {
					?>
					<div class="ui-panel-row post_stats_target" data-display="hidded">	
						<?php if ( $source_type != 'youtube_stream') { ?>				
						<div class="maxgrid_ui_row-divider" style="margin-bottom: 5px; margin-top: 10px;"></div>	
						<span class="maxgrid_ui_description" style="margin-left: 15px;">Select statistics elements to add.</span>
						<?php } ?>					
						<div class="maxgrid_ui-col" style="padding-top: 8;">		
							<?php
							
							if ( $source_type == 'youtube_stream') {
								$statistics = array(
									'views_count' 	=> 'Views Count',
									'published_at' 	=> 'Publication date',
									'like_count' 	=> 'Like Count',
									'dislike_count' => 'Dislike Count',
									'top_divider' 	=> 'Enable Top Divider',
								);
							}  else if ( $source_type == 'product') {
								$statistics = array(
									'total_sales' 		=> 'Total Sales',
									'like_count' 		=> 'Like Count',
									'top_divider' 		=> 'Enable Top Divider',
								);
							} else if ( $source_type == 'download') {
								$statistics = array(
									'download_count' 	=> 'Download Count',
									'like_count' 		=> 'Like Count',
									'top_divider' 		=> 'Enable Top Divider',
								);
							} else {								
								$statistics = [];
								
								if ( is_maxgrid_download_activated() ) {
									$statistics['download_count'] = 'Download Count';
								}

								$statistics = array(
									'views_count' 		=> 'Views Count',
									'like_count' 		=> 'Like Count',
									'top_divider' 		=> 'Enable Top Divider',
								);
							}
							end($statistics);
							$last_key = key($statistics);
							$i = 0;
							foreach($statistics as $key => $value) {
								$last_el = $key == $last_key ? ' padding-left: 25px; border-left: 1px solid rgba(0,0,0,.15);': '';
								?>
								<div class="maxgrid_ui-col" style="display: inline-block; min-width: 130px; width: auto; padding-left: 0; padding-top: 0px;<?php echo $last_el; ?>">
									<div class="maxgrid_ui-edit_form_line">
										<div class="maxgrid_ch-box">
											<input type="checkbox" name="<?php echo $key; ?>" max="5" id="<?php echo $key; ?>" data-item-id="<?php echo $item_id; ?>" class="form-field" value="1" <?php if( isset($current[$key]) && maxgrid_string_to_bool($current[$key])==1):echo"checked";endif; ?> >
											<label for="<?php echo $key; ?>"><?php echo $value; ?></label>
										</div>				
									</div>
								</div>
								<?php
								$i++;
							}
							?>
						</div>						
						<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 0;"></div>						
						<?php 
						$fit_to_ovl_help_title = '<strong class="block">Meta Fields Margin</strong>
									  <p>Disable the left and right margin of meta fields.</p>';		
						$fit_to_ovl_help = '<div id="help-trigger" data-title="' . base64_encode($fit_to_ovl_help_title) . '" data-rel="help-tooltip" data-style="light" data-position="fixed">?</div>';
						?>
						<div class="maxgrid_ui-col flex">
							<div class="maxgrid_ch-box">
								<input id="fit_to_ovl" name="fit_to_ovl" value="1" type="checkbox" class="form-field" <?php if( isset($current['fit_to_ovl']) && maxgrid_string_to_bool($current['fit_to_ovl'])==1):echo"checked";endif; ?>>
								<label for="fit_to_ovl">Fit width to overlay</label><?php echo $fit_to_ovl_help; ?>
							</div>
						</div>
					</div>
					<?php
					}
					?>
					
					<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 30px;margin-left: -15px;margin-right: -15px;">Background Overlay</div>
					<div class="maxgrid_ui-col flex">
						<div class="maxgrid_ui_element_label"><strong>Background Color</strong></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="background_overlay" type="text" id="maxgrid_background_overlay" value="<?php echo $current['background_overlay']; ?>" data-default-color="rgba(0, 0, 0, 0.5)" class="maxgrid-colorpicker form-field" data-alpha="true"/>
						</div>
					</div>					
					<?php
					if ( $source_type != 'youtube_stream' ) {
						$data = array(
							'name' 	   	 => 'ovl_bg_tc',
							'position' 	 => 'relative',
							'current' 	 => $current,
							'footer' 	 => true,
							'tc_id' 	 => 'tc2',
							);
						$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
						echo $Max_Grid_Color_Scheme->term_color($data);
					}
					?>					
					<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 0;"></div>
					<div class="maxgrid_ui-col flex">
						<div class="maxgrid_ui_element_label"><strong>Font Color</strong></div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="color_overlay" type="text" id="maxgrid_color_overlay" value="<?php echo $current['color_overlay']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field" data-alpha="true"/>
						</div>
					</div>					
					<?php
					if ( $source_type != 'youtube_stream' ) {
						$data = array(
							'name' 	   	 => 'ovl_f_tc',
							'position' 	 => 'relative',
							'current' 	 => $current,
							'footer' 	 => true,
							'tc_id' 	 => 'tc3',
							);
						$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
						echo $Max_Grid_Color_Scheme->term_color($data);
					}
					?>					
					<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 0;"></div>					
					<div class="maxgrid_ui-col flex">
						<div class="maxgrid_ch-box">
							<input id="fillcover_overlay" name="fillcover_overlay" value="1" class="form-field extras-triggers" data-target="links_icons_target" type="checkbox" <?php if( isset($current['fillcover_overlay']) &&  maxgrid_string_to_bool($current['fillcover_overlay'])==1):echo"checked";endif; ?>>
							<label for="fillcover_overlay">Fill Cover</label>
						</div>
					</div>
					<div class="maxgrid_ui-col flex">
						<div class="maxgrid_ui_element_label">Hover Transition</div>
						<select name="overlay_transition" id="overlay_transition-style" class="maxgrid_ui_select form-field">
							<option value="slide_up" <?php if($current['overlay_transition']=='slide_up'):echo"selected";endif;?>>Slide Up</option>
							<option value="fade_in"<?php if($current['overlay_transition']=='fade_in'):echo"selected";endif;?>>Fade In</option>
							<option value="direction_aware"<?php if($current['overlay_transition']=='direction_aware'):echo"selected";endif;?><?php echo $is_premium_require_class;?>>Direction Aware<?php echo $is_premium_require_name;?></option>
						</select>			
					</div>
					<div class="ui-panel-row links_icons_target" data-display="hidded">
						<div class="maxgrid_ui_row-divider" style="margin-bottom: 5px; margin-top: 10px;"></div>
						<?php
						$statistics_label = 'Select links to add.';
						if ( $source_type == 'product') {
							$links_icons = array(
								'add_to_cart_link'  => '<i class="fa fa-shopping-cart"></i> Add To Cart Link',
								'external_link' 	=> '<i class="fa fa-link"></i> External Link',
								'lightbox_link' 	=> '<i class="fa fa-search"></i> Lightbox Link',
							);
						} else if ( $source_type == 'download') {
							$links_icons = array(
								'external_link' 	=> '<i class="fa fa-link"></i> External Link',
								'lightbox_link' 	=> '<i class="fa fa-search"></i> Lightbox Link',
								'download_link' 	=> '<i class="fa fa-download"></i> Download Link',
							);
						} else if ( $source_type == 'youtube_stream') {
							$statistics_label = 'Select Icon';
							$links_icons = array(
								'fa fa-search' 		=> '<i class="fa fa-search"></i> Magnifying Glass',
								'fa fa-play' 		=> '<i class="fa fa-play"></i> Play',
								'fa fa-play circle'	=> '<i class="fa fa-play-circle"></i> Play Circle',
								'none' 				=> ' None',
							);
							$links_icons = array(
								'fa_fa-search' 		=> '<i class="fa fa-search"></i> Magnifying Glass',
								'fa_fa-play' 		=> '<i class="fa fa-play"></i> Play',
								'fa_fa-play circle'	=> '<i class="fa fa-play-circle"></i> Play Circle',
								'none' 				=> ' None',
							);
						} else {
							$links_icons = array(
								'external_link' 	=> '<i class="fa fa-link"></i> External Link',
								'lightbox_link' 	=> '<i class="fa fa-search"></i> Lightbox Link',
							);
						}
						?>
						<span class="maxgrid_ui_description" style="margin-left: 15px;"><?php echo $statistics_label;?></span>
						<?php
						$i = 0;
						foreach($links_icons as $key => $value) {

							if ( $source_type == 'youtube_stream') {
								$name = 'lightbox_icon';
								$type = 'radio';
								$current_val = $key;
								$checked = isset($current[$name]) && $current[$name]==$key ? 'checked' : '';
							} else {
								$name = $key;
								$type = 'checkbox';
								$current_val = 1;
								$checked = isset($current[$name]) && maxgrid_string_to_bool($current[$name])==1 ? 'checked' : '';
							}
							?>
							<div class="maxgrid_ui-col" style="display: inline-block; min-width: 160px; width: auto;">
								<div class="maxgrid_ui-edit_form_line fas-icon">
									<div class="maxgrid_ch-box">
										<input type="<?php echo $type; ?>" name="<?php echo $name; ?>" max="5" id="<?php echo $key; ?>" data-item-id="<?php echo $item_id; ?>" class="form-field" value="<?php echo $current_val; ?>" <?php echo $checked; ?> >
										<label for="<?php echo $key; ?>"><?php echo $value; ?></label>
									</div>				
								</div>
							</div>
							<?php
							$i++;
						}
						?>
					</div>
					<div class="maxgrid_ui_row-divider" style="margin-bottom: 5px; margin-top: 20px;"></div>
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Padding</div>
						<div class="maxgrid_ui-edit_form_line">
							<div style="display: inline-block;">
								<div class="maxgrid_ui-col" style="padding-left: 0;">
									<div class="maxgrid_ui_element_label">Top</div>
									<div class="maxgrid_ui-edit_form_line">
										<input name="ovl_p_t" type="text" id="maxgrid_ovl_p_t" value="<?php echo isset($current['ovl_p_t']) ? $current['ovl_p_t'] : '10';?>" class="maxgrid_ui_input form-field numbers-only" style="max-width: 60px;"/><p>px</p>
									</div>
								</div>
							</div>							
							<div style="display: inline-block;">
								<div class="maxgrid_ui-col">
									<div class="maxgrid_ui_element_label">Right</div>
									<div class="maxgrid_ui-edit_form_line">
										<input name="ovl_p_r" type="text" id="maxgrid_ovl_p_r" value="<?php echo isset($current['ovl_p_r']) ? $current['ovl_p_r'] : '10';?>" class="maxgrid_ui_input form-field numbers-only" style="max-width: 60px;"/><p>px</p>
									</div>
								</div>
							</div>							
							<div style="display: inline-block;">
								<div class="maxgrid_ui-col">
									<div class="maxgrid_ui_element_label">Bottom</div>
									<div class="maxgrid_ui-edit_form_line">
										<input name="ovl_p_b" type="text" id="maxgrid_ovl_p_b" value="<?php echo isset($current['ovl_p_b']) ? $current['ovl_p_b'] : '10';?>" class="maxgrid_ui_input form-field numbers-only" style="max-width: 60px;"/><p>px</p>
									</div>
								</div>
							</div>							
							<div style="display: inline-block;">
								<div class="maxgrid_ui-col">
									<div class="maxgrid_ui_element_label">Left</div>
									<div class="maxgrid_ui-edit_form_line">
										<input name="ovl_p_l" type="text" id="maxgrid_ovl_p_l" value="<?php echo isset($current['ovl_p_l']) ? $current['ovl_p_l'] : '10';?>" class="maxgrid_ui_input form-field numbers-only" style="max-width: 60px;"/><p>px</p>
									</div>
								</div>
							</div>							
						</div>						
					</div>					
				</div>
				<div class="maxgrid_ui_row-divider" style="height: 15px; margin-bottom: 15px; border: none;"></div>
			</div>
			<?php
			$is_premium_require_class = !is_maxgrid_premium_activated() ? ' class="mxg-premium-required" disabled' : '';
			$is_premium_require_name = !is_maxgrid_premium_activated() ? ' [Premium]' : '';
			?>
			
			<div id="video_options" class="maxgrid_ui-tabcontent">
				<div class="maxgrid_ui-col">
					<div class="maxgrid_ui_element_label">Insert As</div>
					<div class="maxgrid_ui-edit_form_line">
						<input type="radio" name="insert_as" id="iframe" value="iframe"	<?php if( $current['insert_as']=='iframe'):echo"checked";endif; ?> class="<?php echo $this->required_class?>"> <label for="iframe" class="<?php echo $this->required_class?>">iFrame</label><span<?php echo $is_premium_require_class; ?> style="display:inline-block;width: 50px;margin-left: 6px;margin-bottom: 3px;"></span><br>
						<input type="radio" name="insert_as" id="lightbox" value="lightbox"	<?php if( $current['insert_as']=='lightbox' || $current['insert_as'] == '' ):echo"checked";endif; ?>> <label for="lightbox">Lightbox</label><br>
						<?php
					if ( $source_type != 'youtube_stream' ) {
						?>
						<input type="radio" name="insert_as" id="single" value="single"	<?php if( $current['insert_as']=='single'):echo"checked";endif; ?>> <label for="single">External Link</label><br>
					<?php
					}?>
					</div>
				</div>
			</div>
		</form>
		<?php	
		die();
	}
	
	/**
	 * Title element.
	 *
	 * @return string
	 */
	public function title_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current  = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->title_default();
		$bar_name = sanitize_text_field( $_POST['bar_name'] );
		
		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$bar_name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->title_default();
		}		
		?>
		<form id="title_row_form" action="">
			<div class="maxgrid_ui-col" style="max-width: 280px;">
				<div class="maxgrid_ui_element_label">Font Family</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="title_font_family" id="title_font_family" class="fontselect" type="text" value="<?php echo $current['title_font_family']; ?>" class="title_row-field"/>
				</div>
			</div>
			<div class="maxgrid_ui_row-divider" style="margin-top: 20px; margin-bottom: 15px;"></div>			
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui-edit_form_line">
					<div class="maxgrid_ch-box">
						<input id="nowrap" name="nowrap" value="1" type="checkbox" <?php if( isset($current['nowrap']) && maxgrid_string_to_bool($current['nowrap'])==1):echo"checked";endif; ?>>
						<label for="nowrap">Prevent line breaks</label>
					</div>				
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Link</div>
				<select name="link" id="title_link" class="maxgrid_ui_select form-field">
					<option value="lightbox" <?php if($current['link']=='lightbox'):echo"selected";endif;?>>Lightbox</option>
					<option value="external_link" <?php if($current['link']=='external_link'):echo"selected";endif;?>>External Link</option>
					<option value="none" <?php if($current['link']=='none'):echo"selected";endif;?>>None</option>
				</select>			
			</div>			
			<div class="maxgrid_ui_row-divider" style="margin-top: 20px; margin-bottom: 15px;"></div>
			<div style="display: flex;">
				<div style="width: 30%;">
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Font Size</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="title_font_size" type="text" data-name="font-size" id="maxgrid_title_font_size" value="<?php echo $current['title_font_size']; ?>" class="maxgrid_ui_input title_row-field numbers-only" style="max-width: 80px;"/><p>px</p>
						</div>
					</div>
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Line Height</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="title_line_height" type="text" data-name="line-height" id="maxgrid_title_line_height" value="<?php echo $current['title_line_height']; ?>" class="maxgrid_ui_input title_row-field numbers-only" style="max-width: 80px;"/><p>px</p>
						</div>
					</div>
				</div>
				<div style="width: 35%;">			
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="title_color" type="text" data-name="color" id="maxgrid_title_color" value="<?php echo $current['title_color']; ?>" data-default-color="#3c3c3c" class="maxgrid-colorpicker title_row-field"/>
						</div>					
						<?php
						if ( $source_type != 'youtube_stream' ) {
							$data = array(
								'name' 	   	 => 'title_f_tc',
								'position' 	 => 'relative',
								'current' 	 => $current,
								'footer' 	 => true,
								'tc_id' 	 => 'tc1',
								'styles' 	 => 'margin-left: -15px;',
								);
							$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
							echo $Max_Grid_Color_Scheme->term_color($data);
						}
						?>						
					</div>
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Style</div>
						<div class="maxgrid_ui-edit_form_line">
							<div class="maxgrid_ch-box">
								<input name="title_underline" type="checkbox" id="title_underline" class="title_row-field" value="1" <?php if(isset($current['title_underline']) && maxgrid_string_to_bool($current['title_underline'])==1):echo"checked";endif;?> >
								<label for="title_underline">Underline.</label>
							</div>
						</div>
					</div>				
				</div>
				<div style="width: 35%;">				
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Hover Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="title_h_color" type="text" data-name="color" id="maxgrid_title_h_color" value="<?php echo $current['title_h_color']; ?>" data-default-color="#31c1eb" class="maxgrid-colorpicker form-field"/>
						</div>
					</div>
					<?php
						$data = array(
							'name' 	   	 => 'extra_c1',
							'position' 	 => 'relative',
							'current' 	 => $current,
							//'form_field' => true,
							'footer' 	 => true,
							);
						$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
						echo $Max_Grid_Color_Scheme->color_scheme($data);
					?>		
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Hover Style</div>
						<div class="maxgrid_ui-edit_form_line">
							<div class="maxgrid_ch-box">
								<input name="title_h_underline" type="checkbox" id="title_h_underline" class="form-field" value="1" <?php if(isset($current['title_underline']) && maxgrid_string_to_bool($current['title_h_underline'])==1):echo"checked";endif;?> >
								<label for="title_h_underline">Underline.</label>
							</div>
						</div>
					</div>				
				</div>
			</div>
			<?php echo maxgrid_fit_width_field($current); ?>
			<div class="maxgrid_ui_row-divider" style="border: none; margin-bottom: 25px; margin-top: 10px;"></div>
		</form>
		<?php	
		die();
	}
	
	/**
	 * Post summary element.
	 *
	 * @return string
	 */
	public function summary_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';		
		$current  = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field(  $_POST['dataForm'] ) ) : maxgrid()->settings->summary_default();
		$bar_name = sanitize_text_field( $_POST['bar_name'] );		
		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$bar_name]);
		}		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->summary_default();
		}
		?>	
		<form id="description_row_form" action="">
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Excerpt Length</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="excerpt_length" min="60" type="text" id="maxgrid_excerpt_length" value="<?php echo $current['excerpt_length']; ?>" class="maxgrid_ui_input numbers-only description_row-field" style="max-width: 80px;"/><p><strong>Default :</strong> 18</p>
				</div>
			</div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Font Size</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="font_size" type="text" id="maxgrid_description_font_size" value="<?php echo $current['font_size']; ?>" class="maxgrid_ui_input description_row-field numbers-only" style="max-width: 80px;"/><p>px</p>
				</div>
			</div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Line Height</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="line_height" type="text" data-name="linr-height" id="maxgrid_description_line_height" value="<?php echo $current['line_height']; ?>" class="maxgrid_ui_input description_row-field numbers-only" style="max-width: 80px;"/><p>px</p>
				</div>
			</div>
			<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 15px; margin-bottom: 0;"></div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui-edit_form_line">
					<div class="maxgrid_ch-box">
						<input name="enable_extras" type="checkbox" id="maxgrid_infobar_enable_extras" class="description_row-field <?php echo $this->required_class?>" value="1" <?php if(isset($current['enable_extras']) && maxgrid_string_to_bool($current['enable_extras'])==1):echo"checked";endif;?> >
						<label for="maxgrid_infobar_enable_extras" class="<?php echo $this->required_class?>">Extras Options</label>
						<?php echo $this->required_premium_note?>
					</div>
				</div>
			</div>
			<?php if ( is_maxgrid_premium_activated() ) { ?>
			<div style="display: flex;" class="row_custom_color_container">
				<div style="width: 30%;">
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Background Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="background_color" type="text" id="maxgrid_background_color" value="<?php echo $current['background_color']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker description_row-field"/>
						</div>
					</div>
				</div>
				<div style="width: 35%;">			
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Text Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="text_color" type="text" id="text_color" value="<?php echo $current['text_color']; ?>" data-default-color="#595959" class="maxgrid-colorpicker description_row-field"/>
						</div>
					</div>
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Style</div>
						<div class="maxgrid_ui-edit_form_line">
							<div class="maxgrid_ch-box">
								<input name="text_underline" type="checkbox" id="text_underline" class="description_row-field" value="1" <?php if(isset($current['text_underline']) && maxgrid_string_to_bool($current['text_underline'])==1):echo"checked";endif;?> >
								<label for="text_underline">Underline.</label>
							</div>
						</div>
					</div>				
				</div>
				<div style="width: 35%;">			
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Text Hover Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="text_h_color" type="text" data-name="color" id="maxgrid_text_h_color" value="<?php echo $current['text_h_color']; ?>" data-default-color="#31c1eb" class="maxgrid-colorpicker description_row-field"/>
						</div>
					</div>
					<?php
						$data = array(
							'name' 	   	 => 'extra_c1',
							'position' 	 => 'relative',
							'current' 	 => $current,
							'form_field' => true,
							'footer' 	 => true,
							);
						$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
						echo $Max_Grid_Color_Scheme->color_scheme($data);
					?>
					<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 10px;"></div>
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Hover Style</div>
						<div class="maxgrid_ui-edit_form_line">
							<div class="maxgrid_ch-box">
								<input name="text_h_underline" type="checkbox" id="text_h_underline" class="description_row-field" value="1" <?php if(isset($current['text_h_underline']) && maxgrid_string_to_bool($current['text_h_underline'])==1):echo"checked";endif;?> >
								<label for="text_h_underline">Underline.</label>
							</div>
						</div>
					</div>				
				</div>
			</div>
			<?php } ?>
			<br>
			<?php echo maxgrid_fit_width_field($current); ?>
			<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 0px; margin-bottom: 20px; border: none;"></div>	
		</form>
		<?php	
		die();
	}
	
	/**
	 * Read more element.
	 *
	 * @return string
	 */
	public function readmore_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->readmore_default();
		$item_id = sanitize_text_field( $_POST['item_id'] );
		
		if( $_POST['first_open'] == 'true'){
			$current = unserialize( maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['post_description'];
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->readmore_default();
		}
		?>
		<form id="readmore_element_form" action="">
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Read More Text</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="label_text" id="label_text" class="maxgrid_ui_input form-field" value="<?php echo $current['label_text']; ?>" style="max-width:100%; margin-right: 10px;" type="text">
				</div>
			</div>			
			<div class="maxgrid_ui-param-heading-wrapper">Normal State</div>			
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Background Color</div>
				<div class="maxgrid_ui-edit_form_line">
					<input type="text" name="btn_bg_color" id="btn_bg_color" value="<?php echo $current['btn_bg_color']; ?>" data-default-color="#31c1eb" class="maxgrid-colorpicker form-field"/>
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Font Color</div>
				<div class="maxgrid_ui-edit_form_line">
					<input type="text" name="btn_f_color" id="btn_f_color" value="<?php echo $current['btn_f_color']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field"/>
				</div>
			</div>							
			<div class="maxgrid_ui-param-heading-wrapper">Hover State</div>				
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Background Color</div>
				<div class="maxgrid_ui-edit_form_line">
					<input type="text" name="btn_bg_h_color" id="btn_bg_h_color" value="<?php echo $current['btn_bg_h_color']; ?>" data-default-color="#09b6ea" class="maxgrid-colorpicker form-field"/>
				</div>					
			</div>
			<?php
				$data = array(
					'name' 	   	 => 'extra_c1',
					'position' 	 => 'relative',
					'current' 	 => $current,
					'form_field' => true,
					'footer' 	 => true,
					);
				$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
				echo $Max_Grid_Color_Scheme->color_scheme($data);
			?>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Font Color</div>
				<div class="maxgrid_ui-edit_form_line">
					<input type="text" name="btn_f_h_color" id="btn_f_h_color" value="<?php echo $current['btn_f_h_color']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field"/>
				</div>
			</div>			
			<?php
				$data = array(
					'name' 	   	 => 'btn_f_h_tc',
					'position' 	 => 'relative',
					'current' 	 => $current,
					'footer' 	 => true,
					'tc_id' 	 => 'tc1',
					);
				$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
				echo $Max_Grid_Color_Scheme->term_color($data);
			?>
			<div class="maxgrid_ui_row-divider" style="border: none; margin-bottom: 25px; margin-top: 10px;"></div>
		</form>
		<?php	
		die();
	}

	/**
	 * Post meta element.
	 *
	 * @return string
	 */
	public function post_meta_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current  = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->post_meta_default();
		$bar_name = sanitize_text_field( $_POST['bar_name'] );
		
		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$bar_name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->post_meta_default();
		}
		?>
		<form id="info_row_form" action="">
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Margin</div>
				<div class="maxgrid_ui-edit_form_line">
					Top <input name="margin_top" data-name="margin-top" id="sb_margin_top" style="width:50px;padding:3px" class="maxgrid_ui_input info_row-field numbers-only" value="<?php echo $current['margin_top']; ?>" type="text"><p>px</p> &nbsp;&nbsp;<span style="min-width: 100px; text-align: right; display: inline-block; padding: 0 5px;">Bottom</span><input name="margin_bottom" data-name="margin-bottom" id="sb_margin_bottom" style="width:50px;padding:3px" class="maxgrid_ui_input info_row-field numbers-only" value="<?php echo $current['margin_bottom']; ?>" type="text"><p>px</p>
				</div>
			</div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Padding</div>
				<div class="maxgrid_ui-edit_form_line">
					Top <input name="padding_top" data-name="padding-top" value="<?php echo $current['padding_top']; ?>" id="sb_padding_top" style="width:50px;padding:3px" class="maxgrid_ui_input info_row-field numbers-only" type="text"><p>px</p> &nbsp;&nbsp;<span style="min-width: 100px; text-align: right; display: inline-block; padding: 0 5px;">Bottom</span>  <input name="padding_bottom" data-name="padding-bottom" value="<?php echo $current['padding_bottom']; ?>" id="sb_padding_bottom" style="width:50px;padding:3px" class="maxgrid_ui_input info_row-field numbers-only" type="text"><p>px</p>
				</div>
			</div>
			<div class="maxgrid_ui_row-divider" style="margin-top: 25px;"></div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Font Size</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="font_size" data-name="font-size" type="text" id="maxgrid_date_font_size" value="<?php echo $current['font_size']; ?>" class="maxgrid_ui_input info_row-field numbers-only" style="max-width: 80px;"/><p>px</p>
				</div>
			</div>
			<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 15px; margin-bottom: 0;"></div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui-edit_form_line">
					<div class="maxgrid_ch-box">
						<input name="enable_extras" type="checkbox" id="maxgrid_infobar_enable_extras" class="info_row-field <?php echo $this->required_class?>" value="1" <?php if(isset($current['enable_extras']) && maxgrid_string_to_bool($current['enable_extras'])==1):echo"checked";endif;?> >
						<label for="maxgrid_infobar_enable_extras" class="<?php echo $this->required_class?>">Extras Options</label>
						<?php echo $this->required_premium_note?>
					</div>
				</div>
			</div>
			<?php if ( is_maxgrid_premium_activated() ) { ?>
			<div style="display: flex;" class="row_custom_color_container">
				<div style="width: 30%;">
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Background Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="background_color" type="text" id="maxgrid_background_color" value="<?php echo $current['background_color']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker info_row-field"/>
						</div>
					</div>
				</div>
				<div style="width: 35%;">			
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Text Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="text_color" type="text" id="text_color" value="<?php echo $current['text_color']; ?>" data-default-color="#595959" class="maxgrid-colorpicker info_row-field"/>
						</div>
					</div>
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Style</div>
						<div class="maxgrid_ui-edit_form_line">
							<div class="maxgrid_ch-box">
								<input name="text_underline" type="checkbox" id="text_underline" class="info_row-field" value="1" <?php if(isset($current['text_underline']) && maxgrid_string_to_bool($current['text_underline'])==1):echo"checked";endif;?> >
								<label for="text_underline">Underline.</label>
							</div>
						</div>
					</div>				
				</div>
				<div style="width: 35%;">				
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Text Hover Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="text_h_color" type="text" data-name="color" id="maxgrid_text_h_color" value="<?php echo $current['text_h_color']; ?>" data-default-color="#31c1eb" class="maxgrid-colorpicker info_row-field"/>
						</div>
					</div>
					<?php
						$data = array(
							'name' 	   	 => 'extra_c1',
							'position' 	 => 'relative',
							'current' 	 => $current,
							//'form_field' => true,
							'footer' 	 => true,
							);
						$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
						echo $Max_Grid_Color_Scheme->color_scheme($data);
					?>
					<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 10px;"></div>
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Hover Style</div>
						<div class="maxgrid_ui-edit_form_line">
							<div class="maxgrid_ch-box">
								<input name="text_h_underline" type="checkbox" id="text_h_underline" class="info_row-field" value="1" <?php if(isset($current['text_underline']) && maxgrid_string_to_bool($current['text_h_underline'])==1):echo"checked";endif;?> >
								<label for="text_h_underline">Underline.</label>
							</div>
						</div>
					</div>				
				</div>
			</div>
			<?php } ?>
			<?php echo maxgrid_fit_width_field($current); ?>
			<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 0px; margin-bottom: 20px; border: none;"></div>
		</form>
		<?php	
		die();
	}
	
	/**
	 * Date time options.
	 *
	 * @return string
	 */
	public function date_time_options() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current = isset($_POST['dataForm']) ? maxgrid_string_to_array( "&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : 'date_format=F j, Y';
		$item_id = sanitize_text_field( $_POST['item_id'] );

		if ( $item_id == 'info_bar' ) {		
			if( $_POST['first_open'] == 'true'){
				$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['info_bar']['date_options']);
			}
		} else {
			if( $_POST['first_open'] == 'true') {			
				$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout'][$item_id]['date_options']);
			}
		}
		?>
		<form id="date_options_form" action="" data-source-type="<?php echo sanitize_text_field( $_POST['source_type'] );?>">
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Date and Time Format</div>
				<div class="maxgrid_ui-edit_form_line">	
					<select name="date_format" data-name="date_format" id="maxgrid_date_format" class="maxgrid_ui_select info_row-field"  style="min-width: 180px; height: 104px;" size="3">
						<option value="time_ago" <?php if($current['date_format']=='time_ago'):echo"selected";endif;?>>Time Ago</option>
						<option value="F j, Y" <?php if($current['date_format']=='F j, Y'):echo"selected";endif;?>>November 6, 2010</option>
						<option value="M j, Y" <?php if($current['date_format']=='M j, Y'):echo"selected";endif;?>>Nov 6, 2010</option>
						<option value="custom" <?php if($current['date_format']=='custom'):echo"selected";endif;?>>Custom</option>
					</select>
				</div>
			</div>

			<div class="maxgrid_ui-col" id="custom_date_time_format_container">
				<div class="maxgrid_ui_element_label">Enter a custom date time format :</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="custom_date" data-name="font-size" type="text" id="custom_date_time_format" value="<?php echo isset($current['custom_date']) ? maxgrid_url_encode($current['custom_date']) : '';?>" class="maxgrid_ui_input info_row-field" style="max-width: 240px;"/><p>Example: F j, Y g:i - November 6, 2010 12:50 <a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank"> Learn more</a>.</p>
					
				</div>
			</div>


		</form>
		<?php
			$motivation = array(
				__( 'F j, Y', 'max-grid' ),
				__( 'M j, Y', 'max-grid' ),
				__( 'D M j', 'max-grid' )
			);
		die();
	}
		
	/**
	 * Add to cart element.
	 *
	 * @return string
	 */
	public function add_to_cart_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current 	= isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->add_to_cart_default();
		$bar_name   = sanitize_text_field( $_POST['bar_name'] );
		
		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$bar_name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->add_to_cart_default();
		}
		?>
		<form id="add_to_cart_row_form" action="">
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Top</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_top" class="maxgrid_ui_input number small form-field numbers-only" name="margin_top" value="<?php echo $current['margin_top']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Bottom</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_bottom" class="maxgrid_ui_input number small form-field numbers-only" name="margin_bottom" value="<?php echo $current['margin_bottom']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>
			<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 30px; margin-bottom: 10px;">Price Settings</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Font Family</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="font_family" id="price_font_family" class="fontselect" type="text" value="<?php echo $current['font_family']; ?>" />
					</div>
				</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Font Size</div>
					<div class="maxgrid_ui-edit_form_line">
						<input min="0" max="120" id="maxgrid_price_font_size" class="maxgrid_ui_input number small form-field numbers-only" name="font_size" value="<?php echo $current['font_size']; ?>" style="max-width:100px; margin-right: 10px;" type="text"><p>px</p>
					</div>
				</div>
				<div class="maxgrid_ui-col flex wp-colorpicker-col">
					<div class="maxgrid_ui_element_label">Font color</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="price_font_color" type="text" id="maxgrid_price_font_color" value="<?php echo $current['price_font_color']; ?>" data-default-color="#dd3333" class="maxgrid-colorpicker form-field"/>
					</div>
				</div>
				<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 10px; margin-bottom: 10px;">Add to cart - Quantity Settings</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Button Label</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="add_to_cart_label" type="text" id="add_to_cart_label" value="<?php echo $current['add_to_cart_label']; ?>" class="maxgrid_ui_input form-field"/>
					</div>
				</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Spin Layout</div>
					<div class="maxgrid_ui-edit_form_line">
						<select name="spin_layout" id="spin_layout" class="maxgrid_ui_select spin_layout form-field">
							<option value="inner_spin" <?php if($current['spin_layout']=='inner_spin'):echo"selected";endif;?> >Inner Spin</option>
							<option value="outer_spin" <?php if($current['spin_layout']=='outer_spin'):echo"selected";endif;?> >Outer Spin</option>
						</select>
					</div>
				</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Border Radius</div>
					<div class="maxgrid_ui-edit_form_line">
						<select name="border_radius" id="border_radius" class="maxgrid_ui_select border_radius form-field">
							<option value="rounded" <?php if($current['border_radius']=='rounded'):echo"selected";endif;?> >Rounded</option>
							<option value="pointed" <?php if($current['border_radius']=='pointed'):echo"selected";endif;?> >Pointed</option>
						</select>
					</div>
				</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Plus-minus sign</div>
					<div class="maxgrid_ui-edit_form_line">
						<select name="sign_style" id="sign_style" class="maxgrid_ui_select sign_style form-field">
							<option value="thick" <?php if($current['sign_style']=='thick'):echo"selected";endif;?> >Thick</option>
							<option value="thin" <?php if($current['sign_style']=='thin'):echo"selected";endif;?> >Thin</option>
						</select>
					</div>
				</div>			
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ch-box">
						<input id="disable_qty" name="disable_qty" value="1" type="checkbox" <?php if( isset($current['disable_qty']) && maxgrid_string_to_bool($current['disable_qty'])==1):echo"checked";endif; ?>>
						<label for="disable_qty">Disable The Quantity Field</label>
					</div>
				</div>			
				<div class="maxgrid_ui_row-divider" style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="maxgrid_ui-col flex wp-colorpicker-col">
					<div class="maxgrid_ui_element_label">Background Color</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="color_theme" type="text" id="maxgrid_price_color_theme" value="<?php echo $current['color_theme']; ?>" data-default-color="#dd3333" class="maxgrid-colorpicker form-field"/>
					</div>
				</div>
				<?php
					$data = array(
						'name' 	   	 => 'extra_c1',
						'position' 	 => 'relative',
						'current' 	 => $current,
						'form_field' => true,
						'footer' 	 => true,
						);
					$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
					echo $Max_Grid_Color_Scheme->color_scheme($data);
				?>
				<div class="maxgrid_ui_row-divider" style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="maxgrid_ui-col flex wp-colorpicker-col">
					<div class="maxgrid_ui_element_label">Font Color</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="button_font_color" type="text" id="maxgrid_button_font_color" value="<?php echo $current['button_font_color']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field"/>
					</div>
				</div>
				<?php echo maxgrid_fit_width_field($current); ?>
			</form>
			<?php	
		die();
	}
	
	/**
	 * Audio player element.
	 *
	 * @return string
	 */
	public function audio_player_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current  = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->audio_player_default();
		$bar_name = sanitize_text_field( $_POST['bar_name'] );
		
		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$bar_name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->audio_player_default();
		}
		
		?>
		<form id="audio_row_form" action="">
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Top</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_top" class="maxgrid_ui_input number small form-field numbers-only" name="margin_top" value="<?php echo $current['margin_top']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Bottom</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_bottom" class="maxgrid_ui_input number small form-field numbers-only" name="margin_bottom" value="<?php echo $current['margin_bottom']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>
			<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 20px; margin-bottom: 10px;">Player Settings</div>
				
				<?php 
					$args = array(
						'current' 	   		=> $current,
						'class'  			=> 'audio-player__col',
						'field_class'  		=> 'form-field',
						'style' 	   		=> 'padding-left: 0;',
						'simplify_control' 	=> true,
					);
		
				echo $this->border_radius($args);
				?>
				
				<div class="maxgrid_ui_row-divider" style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="maxgrid_ui-col inline-block" style="width: 50%;">
					<div class="maxgrid_ui_element_label">Background Color</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="bg_color" type="text" id="maxgrid_dld_bg_color" value="<?php echo $current['bg_color']; ?>" data-default-color="#e4e4e4" class="maxgrid-colorpicker form-field" data-alpha="true" />
					</div>
				</div>
				<div class="maxgrid_ui-col inline-block">
					<div class="maxgrid_ui_element_label">Font Color</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="font_color" type="text" id="maxgrid_dld_font_color" value="<?php echo $current['font_color']; ?>" data-default-color="#d2d2d2" class="maxgrid-colorpicker form-field"/>
					</div>
				</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Background Image</div>
					<div class="maxgrid-metaoptions-row singleimageupload default_og_image block default_og_image_target edit-mode-on">
						<label class="noselect subtitle-label block no-cursor" title="upload / choose an image or add the URL here.">Upload / Choose an image or add the URL here.</label>
						<input style="width: calc(100% - 150px); display: inline-block;" id="audioplayer_bg_image" class="single-image-upload" name="bg_image" size="100" value="<?php echo maxgrid_url_encode($current['bg_image']); ?>" type="text">
						<input id="single_upload_button" class="button-primary" value="Upload / Select File" placeholder="http://..." type="button">
						<div class="maxgrid img-wrap" style="display: none;">
							<span class="close">×</span>
							<img class="img-preview" id="img-preview" src="">
						</div>
					</div>
				</div>				
				<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 15px; margin-bottom: 5px;">Track &#38; Volume Settings</div>			
				<div class="maxgrid_ui-col flex" style="padding: 0px;">
					<div style="width: 60%; border-right: 1px solid rgba(0,0,0,.1);">
						<div class="maxgrid_ui-col">
							<div class="maxgrid_ui_element_label">Color #1</div>
							<div class="maxgrid_ui-edit_form_line">
								<input name="front_track_color" type="text" id="maxgrid_front_track_color" value="<?php echo $current['front_track_color']; ?>" data-default-color="#31c1eb" class="maxgrid-colorpicker form-field"/>
							</div>
						</div>
						<?php
							$data = array(
								'name' 	   	 => 'extra_c1',
								'position' 	 => 'relative',
								'current' 	 => $current,
								'form_field' => true,
								'footer' 	 => true,
								);
							$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
							echo $Max_Grid_Color_Scheme->color_scheme($data);
						?>						
						<div class="maxgrid_ui-col" style="margin-top: 5px;">
							<div class="maxgrid_ch-box">
								<input id="use_gradient" name="use_gradient" value="1" class="form-field" type="checkbox" <?php if( isset($current['use_gradient']) &&  maxgrid_string_to_bool($current['use_gradient'])==1):echo"checked";endif; ?> data-triger="dropp" data-id="use_gradient">
								<label for="use_gradient">Use Gradient.</label>							
							</div></div>
							<div class="maxgrid_ui-col use_gradient">
								<div class="maxgrid_ui-edit_form_line inline-block">
									<div class="maxgrid_ui_element_label">Color 1</div>
									<input name="track_grad_c1" type="text" id="maxgrid_track_grad_c1" value="<?php echo $current['track_grad_c1']; ?>" data-default-color="#ad73f8" class="maxgrid-colorpicker form-field"/>
								</div>
								<div class="maxgrid_ui-edit_form_line inline-block">
									<div class="maxgrid_ui_element_label">Color 2</div>
									<input name="track_grad_c2" type="text" id="maxgrid_track_grad_c2" value="<?php echo $current['track_grad_c2']; ?>" data-default-color="#fc496b" class="maxgrid-colorpicker form-field"/>
								</div>
							</div>						
					</div>
					<div style="width:40%;">
						<div class="maxgrid_ui-col">
							<div class="maxgrid_ui_element_label">Color #2</div>
							<div class="maxgrid_ui-edit_form_line">
								<input name="back_track_color" type="text" id="maxgrid_back_track_color" value="<?php echo $current['back_track_color']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field"/>
							</div>
						</div>
						<?php
							$data = array(
								'name' 	   	 => 'extra_c2',
								'position' 	 => 'relative',
								'current' 	 => $current,
								'form_field' => true,
								//'footer' 	 => true,
								);
							$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
							echo $Max_Grid_Color_Scheme->color_scheme($data);
						?>
					</div>
				</div>				
				<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 15px; margin-bottom: 5px;">Extras</div>			
				<div class="maxgrid_ui-col">
					<div class="maxgrid_ch-box">
						<input id="apply_shadows" name="apply_shadows" value="1" class="form-field" type="checkbox" <?php if( isset($current['apply_shadows']) &&  maxgrid_string_to_bool($current['apply_shadows'])==1):echo"checked";endif; ?>>
						<label for="apply_shadows">Apply text / box shadow</label>								
					</div>
				</div>			
				<?php echo maxgrid_fit_width_field($current, false); ?>				
				<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 0px; margin-bottom: 20px; border: none;"></div>	
			</form>
			<?php	
		die();
	}
	
	/**
	 * Download element.
	 *
	 * @return string
	 */
	public function download_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current  = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->download_default();
		$bar_name = sanitize_text_field( $_POST['bar_name'] );
		
		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$bar_name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->download_default();
		}
		?>
		<form id="download_row_form" action="">
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Top</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_top" class="maxgrid_ui_input number small form-field numbers-only" name="margin_top" value="<?php echo $current['margin_top']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Bottom</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_bottom" class="maxgrid_ui_input number small form-field numbers-only" name="margin_bottom" value="<?php echo $current['margin_bottom']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>
			<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 30px; margin-bottom: 10px;">Button Settings</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Label</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="button_label" type="text" id="dld_button_label" value="<?php echo $current['button_label']; ?>" class="maxgrid_ui_input form-field"/>
					</div>
				</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Position</div>
					<div class="maxgrid_ui-edit_form_line">
						<select name="button_position" id="button_position" class="maxgrid_ui_select button_position form-field">
							<option value="left" <?php if($current['button_position']=='left'):echo"selected";endif;?> >Left</option>
							<option value="right" <?php if($current['button_position']=='right'):echo"selected";endif;?> >Right</option>
						</select>
					</div>
				</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Size</div>
					<div class="maxgrid_ui-edit_form_line">
						<select name="button_size" id="button_size" class="maxgrid_ui_select button_size form-field">
							<option value="small" <?php if($current['button_size']=='small'):echo"selected";endif;?> >Normal</option>
							<option value="medium" <?php if($current['button_size']=='medium'):echo"selected";endif;?> >Medium</option>
							<option value="large" <?php if($current['button_size']=='large'):echo"selected";endif;?> >Large</option>
							<option value="biggest" <?php if($current['button_size']=='biggest'):echo"selected";endif;?> >Biggest</option>
						</select>
					</div>
				</div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ch-box">
						<input id="fullwidth" name="fullwidth" value="1" class="form-field" type="checkbox" <?php if( isset($current['fullwidth']) &&  maxgrid_string_to_bool($current['fullwidth'])==1):echo"checked";endif; ?>>
						<label for="fullwidth">Full Width Button</label>
					</div>
				</div>			
				<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Color Theme</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="color_theme" type="text" id="maxgrid_dld_btn_color_theme" value="<?php echo $current['color_theme']; ?>" data-default-color="#7bcd2d" class="maxgrid-colorpicker form-field"/>
					</div>
				</div>
				<?php
					$data = array(
						'name' 	   	 => 'extra_c1',
						'position' 	 => 'relative',
						'current' 	 => $current,
						'form_field' => true,
						'footer' 	 => true,
						);
					$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
					echo $Max_Grid_Color_Scheme->color_scheme($data);
				?>
				<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="maxgrid_ui-col flex">
					<div class="maxgrid_ui_element_label">Font Color</div>
					<div class="maxgrid_ui-edit_form_line">
						<input name="button_font_color" type="text" id="maxgrid_dld_btn_font_color" value="<?php echo $current['button_font_color']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker form-field"/>
					</div>
				</div>
				<?php echo maxgrid_fit_width_field($current); ?>
			</form>
			<?php	
		die();
	}
	
	/**
	 * Average rating element.
	 *
	 * @return string
	 */
	public function average_rating_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current  = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->average_rating_default();
		$bar_name = sanitize_text_field( $_POST['bar_name'] );
		
		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$bar_name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->average_rating_default();
		}

		?>
		<form id="average_rating_row_form" action="">
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Top</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_top" class="maxgrid_ui_input number small form-field numbers-only" name="margin_top" value="<?php echo $current['margin_top']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Bottom</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_bottom" class="maxgrid_ui_input number small form-field numbers-only" name="margin_bottom" value="<?php echo $current['margin_bottom']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>
			<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 15px; margin-bottom: 5px;"></div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Description</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="description" type="text" id="description" value="<?php echo $current['description']; ?>" class="maxgrid_ui_input form-field" style="max-width: 200px" onkeyup="maxgrid_averageRatingPreview(this)"/> <strong>Preview :</strong> <div class="maxgrid_averageRatingPreview"><span>34 reviews</span></div>
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Align</div>
				<div class="maxgrid_ui-edit_form_line">
					<select name="align" id="average_rating_align" class="maxgrid_ui_select average_rating_align form-field">
						<option value="left" <?php if($current['align']=='left'):echo"selected";endif;?> >Left</option>
						<option value="right" <?php if($current['align']=='right'):echo"selected";endif;?> >Right</option>
					</select>
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Star Size</div>
				<div class="maxgrid_ui-edit_form_line">
					<select name="stars_size" id="stars_size" class="maxgrid_ui_select button_size form-field">
						<option value="small" <?php if($current['stars_size']=='small'):echo"selected";endif;?> >Normal</option>
						<option value="medium" <?php if($current['stars_size']=='medium'):echo"selected";endif;?> >Medium</option>
						<option value="large" <?php if($current['stars_size']=='large'):echo"selected";endif;?> >Large</option>
						<option value="biggest" <?php if($current['stars_size']=='biggest'):echo"selected";endif;?> >Biggest</option>
					</select>
				</div>
			</div>
			<div class="maxgrid_ui-col flex">
				<div class="maxgrid_ui_element_label">Star Color</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="stars_color" type="text" id="maxgrid_dld_btn_stars_color" value="<?php echo $current['stars_color']; ?>" data-default-color="#F4B30A" class="maxgrid-colorpicker form-field"/>
				</div>
			</div>
			<?php echo maxgrid_fit_width_field($current); ?>
			</form>
			<?php	
		die();
	}
		
	/**
	 * Youtube video description element.
	 *
	 * @return string
	 */
	public function ytb_video_description_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current  = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->ytb_summary_default();
		$bar_name = sanitize_text_field( $_POST['bar_name'] );
		
		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$bar_name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->ytb_summary_default();
		}
		
		?>
		<form id="ytb_description_row_form" action="">
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Excerpt Length</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="excerpt_length" min="60" type="text" id="maxgrid_excerpt_length" value="<?php echo $current['excerpt_length']; ?>" class="maxgrid_ui_input numbers-only description_row-field" style="max-width: 80px;"/><p><strong>Default :</strong> 18 Word</p>
				</div>
			</div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Font Size</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="font_size" type="text" id="maxgrid_description_font_size" value="<?php echo $current['font_size']; ?>" class="maxgrid_ui_input description_row-field numbers-only" style="max-width: 80px;"/><p>px</p>
				</div>
			</div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Line Height</div>
				<div class="maxgrid_ui-edit_form_line">
					<input name="line_height" type="text" data-name="linr-height" id="maxgrid_description_line_height" value="<?php echo $current['line_height']; ?>" class="maxgrid_ui_input description_row-field numbers-only" style="max-width: 80px;"/><p>px</p>
				</div>
			</div>
			<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 15px; margin-bottom: 0;"></div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui-edit_form_line">
					<div class="maxgrid_ch-box">
						<input name="enable_extras" type="checkbox" id="maxgrid_infobar_enable_extras" class="description_row-field" value="1" <?php if(isset($current['enable_extras']) && maxgrid_string_to_bool($current['enable_extras'])==1):echo"checked";endif;?> >
						<label for="maxgrid_infobar_enable_extras">Enable Extras Options</label>
					</div>
				</div>
			</div>
			<div style="display: flex;" class="row_custom_color_container">
				<div style="width: 30%;">
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Background Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="background_color" type="text" id="maxgrid_background_color" value="<?php echo $current['background_color']; ?>" data-default-color="#ffffff" class="maxgrid-colorpicker description_row-field"/>
						</div>
					</div>
				</div>
				<div style="width: 70%;">			
					<div class="maxgrid_ui-col">
						<div class="maxgrid_ui_element_label">Text Color</div>
						<div class="maxgrid_ui-edit_form_line">
							<input name="text_color" type="text" id="text_color" value="<?php echo $current['text_color']; ?>" data-default-color="#595959" class="maxgrid-colorpicker description_row-field"/>
						</div>
					</div>			
				</div>
			</div>
			<?php echo maxgrid_fit_width_field($current); ?>
			<div class="maxgrid_ui_row-divider" style="width: 100%; margin-top: 0px; margin-bottom: 20px; border: none;"></div>	
		</form>
		<?php	
		die();
	}
	
	/**
	 * Divider element.
	 *
	 * @return string
	 */
	public function divider_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current  = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->divider_default();			
		$bar_name = str_replace('bar', 'row', sanitize_text_field( $_POST['bar_name'] ) );

		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$bar_name]);	
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned') {
			$current = maxgrid()->settings->divider_default();
		}
		?>
		<form id="divider_row_form" action="">

			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Top</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_top" class="maxgrid_ui_input number small form-field numbers-only" name="margin_top" value="<?php echo $current['margin_top']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Margin Bottom</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="margin_bottom" class="maxgrid_ui_input number small form-field numbers-only" name="margin_bottom" value="<?php echo $current['margin_bottom']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>			
			<div class="maxgrid_ui-param-heading-wrapper" style="margin-top: 20px;margin-bottom: 5px;" >Line Options</div>			
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Thickness</div>
				<div class="maxgrid_ui-edit_form_line">
					<input min="0" max="50" id="line_thickness" class="maxgrid_ui_input number small form-field numbers-only" name="line_thickness" value="<?php echo $current['line_thickness']; ?>" style="max-width:100px; margin-right: 10px;" type="text">px
				</div>
			</div>			
			<div class="maxgrid_ui-col inline-block">
				<div class="maxgrid_ui_element_label">Style</div>
				<div class="maxgrid_ui-edit_form_line">
					<select id="line_type" name="line_type" class="maxgrid_ui_select line_type form-field">
						<option value="no_line" <?php if($current['line_type']=='no_line'):echo"selected";endif;?> >No line</option>
						<option value="full_line" <?php if($current['line_type']=='full_line'):echo"selected";endif;?> >Full width line</option>
						<option value="small_line" <?php if($current['line_type']=='small_line'):echo"selected";endif;?> >Small line</option>
						<option value="full_dashed_line" <?php if($current['line_type']=='full_dashed_line'):echo"selected";endif;?> >Full width dashed line</option>
						<option value="small_dashed_line" <?php if($current['line_type']=='small_dashed_line'):echo"selected";endif;?> >Small dashed line</option>
					</select>
				</div>
			</div>
			<div class="maxgrid_ui-col">
				<div class="maxgrid_ui_element_label">Color</div>
				<div class="maxgrid_ui-edit_form_line">
					<input type="text" name="line_color" id="line_color" value="<?php echo $current['line_color']; ?>" data-default-color="rgba(0,0,0,.1)" class="maxgrid-colorpicker form-field" data-alpha="true"/>
				</div>
			</div>					
			<?php
			if ( $source_type != 'youtube_stream' ) {
				$data = array(
					'name' 	   	 => 'line_tc',
					'position' 	 => 'relative',
					'current' 	 => $current,
					'tc_id' 	 => 'tc1',
					'footer' 	 => true,
					);
				$Max_Grid_Color_Scheme = new Max_Grid_Color_Scheme;
				echo $Max_Grid_Color_Scheme->term_color($data);
			}
			?>
			<?php echo maxgrid_fit_width_field($current); ?>
		</form>
		<?php	
		die();
	}
	
	/**
	 * Post Stats bar element.
	 *
	 * @return string
	 */
	public function statsbar_element() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->stats_default();
		$name 	 = str_replace('bar', 'row', sanitize_text_field( $_POST['data_bar'] ) );
		
		if( $_POST['first_open'] == 'true'){
			$current = maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout']['rows_options'][$name]);
		}
		
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->stats_default();
		}
		echo is_maxgrid_premium_activated() ? maxgrid()->premium->statsbar_settings($current, $source_type) : '';
		
		die();
	}
	
	/**
	 * ShareThis options.
	 *
	 * @return string
	 */
	public function sharethis_options() {
		global $source_type;
		$source_type = isset($_POST['source_type']) ? sanitize_text_field( $_POST['source_type'] ) : 'post';
		
		$current 	 = isset($_POST['dataForm']) ? maxgrid_string_to_array("&", "=", sanitize_text_field( $_POST['dataForm'] ) ) : maxgrid()->settings->sharethis_default();
		$bar_name 	 = sanitize_text_field( $_POST['bar_name'] );
		$is_woo 	 = $source_type=='product' ? 'woo_stats_bar' : 'stats_bar';

		if( $_POST['first_open'] == 'true' ) {			
			$current = isset(unserialize( maxgrid_template_load( sanitize_text_field( $_POST['pslug'] ) ) )['grid_layout'][$is_woo][$bar_name]) ? maxgrid_string_to_array("&", "=", unserialize(maxgrid_template_load($_POST['pslug']))['grid_layout'][$is_woo][$bar_name]) : null;
		}
		 
		if(isset($_POST['cloned_element']) && $_POST['cloned_element'] === 'cloned'){
			$current = maxgrid()->settings->sharethis_default();
		}
		
		$current = strpos($_POST['dataForm'], '=') !== false && $current ? $current : maxgrid()->settings->sharethis_default();
		$item_id = sanitize_text_field( $_POST['item_id'] );
		
		echo is_maxgrid_premium_activated() ? maxgrid()->premium->sharethis_options($current, $item_id) : '';
		die();
	}
		
	/**
	 * Youtube API Checker.
	 *
	 * @return string
	 */
	public function youtube_api_checker() {		
		$api_key = isset($_POST['api_key']) ? sanitize_text_field( $_POST['api_key'] ) : '';
		$url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&key='.$api_key;
		$response = json_decode(wp_remote_fopen($url));
		if( isset($response->error->errors[0]->reason) && $response->error->errors[0]->reason == 'keyInvalid' ) {
			echo 'keyInvalid';
		} else if ( isset($response->pageInfo->totalResults) && $response->pageInfo->totalResults > 0 ) {
			echo 'keyValid';
		}
		die();
	}
		
	/**
	 * reCAPTCHA API Checker.
	 *
	 * @return string
	 */
	public function g_recaptcha_checker() {
		$g_recaptcha = new g_recaptcha();
		return $g_recaptcha->g_recaptcha();
	}
		
	/**
	 * Design option - Margin.
	 *
	 * @return string
	 */
	public function margin($args=array()) {
		$current 		  = $args['current'];
		$field_class	  = isset($args['field_class']) ? $args['field_class'] : 'block-grid-field';
		$fields			  = isset($args['fields']) ? $args['fields'] : array('l', 't', 'r', 'b');
		$simplify_control = isset($args['simplify_control']) ? true : null;
		$flex			  = isset($args['flex']) ? ' flex' : '';
		$style			  = isset($args['style']) ? ' style="'.$args['style'].'"' : '';
		
		$id = 'margin';
		$control = array('id' => $id);
		
		?>
		<div class="maxgrid_ui-col design-option__col<?php echo $flex ?>"<?php echo $style ?>>
			<div class="maxgrid_ui_element_label design-option__heading">
				<div>Margin</div>
				<?php echo isset($simplify_control) ? $this->simplify_control($control) : '';?>
			</div>
			<div class="maxgrid_ui-edit_form_line <?php echo $id ?>_simplify-control_container" style="min-width: 245px;">
				<div class="maxgrid-fields__block">
					<?php if ( in_array('l', $fields) ) { ?>					
					<label class="ui-simplify left-field__pos" for="maxgrid_margin-left">Left</label><input name="margin_left" data-name="margin-left" id="maxgrid_margin-left" class="simplify-control <?php echo $field_class ?> maxgrid_small left-field__pos numbers-only" value="<?php echo isset($current['margin_left']) ? $current['margin_left'] : ''; ?>" type="text" data-target="mg-marg_target">
					<?php } ?>
					<?php if ( in_array('r', $fields) ) { ?>	
					<label class="ui-simplify right-field__pos" for="maxgrid_margin-right">Right</label><input name="margin_right" data-name="margin-right" id="maxgrid_margin-right" class="ui-simplify mg-marg_target <?php echo $field_class ?> maxgrid_small right-field__pos numbers-only" value="<?php echo isset($current['margin_right']) ? $current['margin_right'] : ''; ?>" type="text">
					<?php } ?>
					<?php if ( in_array('t', $fields) ) { ?>	
					<label class="ui-simplify top-field__pos" for="maxgrid_margin-top">Top</label><input name="margin_top" data-name="margin-top" id="maxgrid_margin-top" class="ui-simplify mg-marg_target <?php echo $field_class ?> maxgrid_small top-field__pos numbers-only" value="<?php echo isset($current['margin_top']) ? $current['margin_top'] : ''; ?>" type="text">
					<?php } ?>
					<?php if ( in_array('b', $fields) ) { ?>	
					<label class="ui-simplify bottom-field__pos" for="maxgrid_margin-bottom">Bottom</label><input name="margin_bottom" data-name="margin-bottom" id="maxgrid_margin-bottom" class="ui-simplify mg-marg_target <?php echo $field_class ?> maxgrid_small bottom-field__pos numbers-only" value="<?php echo isset($current['margin_bottom']) ? $current['margin_bottom'] : ''; ?>" type="text">
					<?php } ?>
				</div>
			</div>
		</div>
	<?php
	}
	
	/**
	 * Design option - Padding.
	 *
	 * @return string
	 */
	public function padding($args=array()) {
		$current 		  = $args['current'];
		$field_class	  = isset($args['field_class']) ? $args['field_class'] : 'block-grid-field';
		$list_view	 	  = isset($args['list_view']) ? 'list_' : '';
		$class	 	  	  = isset($args['list_view']) ? ' list-view' : '';
		$render_field 	  = !isset($args['list_view']) ? $field_class : '';
		$fields			  = isset($args['fields']) ? $args['fields'] : array('l', 't', 'r', 'b');
		$simplify_control = isset($args['simplify_control']) ? true : null;
		$flex			  = isset($args['flex']) ? ' flex' : '';
		$style			  = isset($args['style']) ? ' style="'.$args['style'].'"' : '';
		
		$id = isset($args['list_view']) ? 'list_padding' : 'padding';
		$control = array('id' => $id);
		?>
		<div class="maxgrid_ui-col design-option__col<?php echo $flex.$class ?>"<?php echo $style ?>>
			<div class="maxgrid_ui_element_label design-option__heading">
				<div>Padding</div>
				<?php echo isset($simplify_control) ? $this->simplify_control($control) : '';?>
			</div>
			<div class="maxgrid_ui-edit_form_line <?php echo $id ?>_simplify-control_container" style="min-width: 245px;">
				<div class="maxgrid-fields__block">
					<?php if ( in_array('l', $fields) ) { ?>					
					<label class="ui-simplify left-field__pos" for="block_padding-left">Left</label><input name="<?php echo $list_view ?>padding_left" data-name="padding-left" id="block_padding-left" class="simplify-control <?php echo $render_field ?> maxgrid_small left-field__pos numbers-only" value="<?php echo isset($current[$list_view .'padding_left']) ? $current[$list_view .'padding_left'] : ''; ?>" type="text" data-target="mg-pad_target">
					<?php } ?>
					<?php if ( in_array('r', $fields) ) { ?>	
					<label class="ui-simplify right-field__pos" for="block_padding-right">Right</label><input name="<?php echo $list_view ?>padding_right" data-name="padding-right" id="block_padding-right" class="ui-simplify <?php echo $render_field ?> mg-pad_target maxgrid_small right-field__pos numbers-only" value="<?php echo isset($current[$list_view .'padding_right']) ? $current[$list_view .'padding_right'] : ''; ?>" type="text">
					<?php } ?>
					<?php if ( in_array('t', $fields) ) { ?>	
					<label class="ui-simplify top-field__pos" for="maxgrid_padding-top">Top</label><input name="<?php echo $list_view ?>padding_top" data-name="padding-top" id="maxgrid_padding-top" class="ui-simplify mg-pad_target <?php echo $render_field ?> maxgrid_small top-field__pos numbers-only" value="<?php echo isset($current[$list_view .'padding_top']) ? $current[$list_view .'padding_top'] : ''; ?>" type="text">
					<?php } ?>
					<?php if ( in_array('b', $fields) ) { ?>	
					<label class="ui-simplify bottom-field__pos" for="maxgrid_padding-bottom">Bottom</label><input name="<?php echo $list_view ?>padding_bottom" data-name="padding-bottom" id="maxgrid_padding-bottom" class="ui-simplify mg-pad_target <?php echo $render_field ?> maxgrid_small bottom-field__pos numbers-only" value="<?php echo isset($current[$list_view .'padding_bottom']) ? $current[$list_view .'padding_bottom'] : ''; ?>" type="text">
					<?php } ?>
				</div>
			</div>
		</div>
	<?php
	}
	
	/**
	 * Design option - Border width.
	 *
	 * @return string
	 */
	public function border_width($args=array()) {
		$current 		  = $args['current'];
		$field_class	  = isset($args['field_class']) ? $args['field_class'] : 'block-grid-field';
		$fields			  = isset($args['fields']) ? $args['fields'] : array('l', 't', 'r', 'b');
		$simplify_control = isset($args['simplify_control']) ? true : null;
		$flex			  = isset($args['flex']) ? ' flex' : '';
		$style			  = isset($args['style']) ? ' style="'.$args['style'].'"' : '';
		
		$id = 'border_width';
		$control = array('id' => $id);
		?>
		<div class="maxgrid_ui-col design-option__col<?php echo $flex ?>"<?php echo $style ?>>
			<div class="maxgrid_ui_element_label design-option__heading">
				<div>Border width</div>
				<?php echo isset($simplify_control) ? $this->simplify_control($control) : '';?>
			</div>
			<div class="maxgrid_ui-edit_form_line <?php echo $id ?>_simplify-control_container" style="min-width: 245px;">
				<div class="maxgrid-fields__block">
					<?php if ( in_array('l', $fields) ) { ?>					
					<label class="ui-simplify left-field__pos" for="maxgrid_border-left-width ui-simplify">Left</label><input name="border_left_width" data-name="border-left-width" id="maxgrid_border-left-width" class="simplify-control <?php echo $field_class ?> maxgrid_small left-field__pos numbers-only" value="<?php echo isset($current['border_left_width']) ? $current['border_left_width'] : ''; ?>" type="text" data-target="mg-bw_target">
					<?php } ?>
					<?php if ( in_array('r', $fields) ) { ?>	
					<label class="ui-simplify right-field__pos" for="maxgrid_border-right-width">Right</label><input name="border_right_width" data-name="border-right-width" id="maxgrid_border-right-width" class="ui-simplify mg-bw_target <?php echo $field_class ?> maxgrid_small right-field__pos numbers-only" value="<?php echo isset($current['border_right_width']) ? $current['border_right_width'] : ''; ?>" type="text">
					<?php } ?>
					
					<?php if ( in_array('t', $fields) ) { ?>	
					<label class="ui-simplify top-field__pos" for="maxgrid_border-top-width">Top</label><input name="border_top_width" data-name="border-top-width" id="maxgrid_border-top-width" class="ui-simplify mg-bw_target <?php echo $field_class ?> maxgrid_small top-field__pos numbers-only" value="<?php echo isset($current['border_top_width']) ? $current['border_top_width'] : ''; ?>" type="text">
					<?php } ?>
					<?php if ( in_array('b', $fields) ) { ?>	
					<label class="ui-simplify bottom-field__pos" for="maxgrid_border-bottom-width">Bottom</label><input name="border_bottom_width" data-name="border-bottom-width" id="maxgrid_border-bottom-width" class="ui-simplify mg-bw_target <?php echo $field_class ?> maxgrid_small bottom-field__pos numbers-only" value="<?php echo isset($current['border_bottom_width']) ? $current['border_bottom_width'] : ''; ?>" type="text">
					<?php } ?>
				</div>		
			</div>
		</div>
	<?php
	}
	
	/**
	 * Design option - Boredr radius.
	 *
	 * @return string
	 */
	public function border_radius($args=array()) {
		$current 		  = $args['current'];
		$field_class	  = isset($args['field_class']) ? $args['field_class'] : 'block-grid-field';
		$simplify_control = isset($args['simplify_control']) ? true : null;
		$flex			  = isset($args['flex']) ? ' flex' : '';
		$style			  = isset($args['style']) ? ' style="'.$args['style'].'"' : '';
		$class 	  		  = isset($args['class']) ? ' '.$args['class'] : '';
		
		$id = 'border_radius';
		$control = array('id' => $id);
		?>
		<div class="maxgrid_ui-col design-option__col<?php echo $flex.$class ?>"<?php echo $style ?>>
			<div class="maxgrid_ui_element_label design-option__heading">
				<div>Border radius</div>
				<?php echo isset($simplify_control) ? $this->simplify_control($control) : '';?>
			</div>
			<div class="maxgrid_ui-edit_form_line <?php echo $id ?>_simplify-control_container" style="min-width: 245px;">
				<div style="white-space: nowrap;">
					<label class="ui-label ui-simplify" for="maxgrid_border-top-left-radius">Top Left</label><input name="border_top_left_radius" type="text" data-name="border-top-left-radius" id="maxgrid_border-top-left-radius" value="<?php echo isset($current['border_top_left_radius']) ? $current['border_top_left_radius'] : ''; ?>" class="maxgrid_ui_input simplify-control maxgrid_small numbers-only <?php echo $field_class ?>" data-target="mg-br_target"/>
					<label class="ui-label ui-simplify" for="maxgrid_border-top-right-radius">Top Right</label><input name="border_top_right_radius" type="text" data-name="border-top-right-radius" id="maxgrid_border-top-right-radius" value="<?php echo isset($current['border_top_right_radius']) ? $current['border_top_right_radius'] : ''; ?>" class="maxgrid_ui_input ui-simplify maxgrid_small numbers-only mg-br_target <?php echo $field_class ?>"/>
				</div>
				<div style="white-space: nowrap; margin-top: 10px;" class="ui-simplify">
					<label class="ui-label" for="maxgrid_border-bottom-left-radius">Bottom Left</label><input name="border_bottom_left_radius" type="text" data-name="border-bottom-left-radius" id="maxgrid_border-bottom-left-radius" value="<?php echo isset($current['border_bottom_left_radius']) ? $current['border_bottom_left_radius'] : ''; ?>" class="maxgrid_ui_input ui-simplify maxgrid_small numbers-only mg-br_target <?php echo $field_class ?>"/>
					<label class="ui-label" for="maxgrid_border-bottom-right-radius">Bottom Right</label><input name="border_bottom_right_radius" type="text" data-name="border-bottom-right-radius" id="maxgrid_border-bottom-right-radius" value="<?php echo isset($current['border_bottom_right_radius']) ? $current['border_bottom_right_radius'] : ''; ?>" class="maxgrid_ui_input ui-simplify maxgrid_small numbers-only mg-br_target <?php echo $field_class ?>"/>
				</div>
			</div>
		</div>
	<?php
	}
	
	/**
	 * Design option - Simplify control
	 *
	 * @return string
	 */
	public function simplify_control($args=array()) {
		$id = isset($args['id']) ? $args['id'] : '';
		$left_border = isset($args['left_border']) ? ' style="padding: 0 0 0 3px;border-left: 1px solid rgba(0,0,0,.08);margin-left: 15px;"' : '';
		?>
		<div class="maxgrid_ch-box simplify-control-ch-box__container">
			<input id="maxgrid_<?php echo $id;?>_simplify" class="simplify-control-triger" value="" type="checkbox" data-target="<?php echo $id;?>">
			<label for="maxgrid_<?php echo $id;?>_simplify" class="simplify-control">Uniform</label>
		</div>
		<?php
	}
}
new Max_Grid_Builder_Ajax_Request;