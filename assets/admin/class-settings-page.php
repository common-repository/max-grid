<?php
/**
 * Max_Grid Settings Page/Tab
 */

use \MaxGrid\Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class Max_Grid_Settings_Page.
 */
class Max_Grid_Settings_Page {
	
	public static $logs_tab = null;
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_submenu' ) );
		add_filter( 'template_include', array( $this, 'grid_preview_page' ), 99 );
		add_action( 'admin_init', array( $this, 'add_settings_fields') );
		self::$logs_tab  = is_maxgrid_premium_activated() || is_maxgrid_download_activated() ? true : null;
	}
	
	/**
	 * Menu icon.
	 *
 	 * @return string
	 */
	function add_submenu(){		
		add_menu_page( MAXGRID_STE_LABEL_NAME, MAXGRID_STE_LABEL_NAME, null, 'maxgrid-menu', null, self::menu_icon('dashicon'), '55.5' );	

		add_submenu_page(
			'maxgrid-menu', 
			MAXGRID_PLUGIN_LABEL_NAME, 
			'Grid Builder',
			'manage_options', 
			MAXGRID_BUILDER_PAGE,
			array( $this, 'builder_page' )
		);
		add_submenu_page(
			'maxgrid-menu', 
			MAXGRID_PLUGIN_LABEL_NAME . ' - Settings', 
			'Settings',
			'manage_options', 
			MAXGRID_SETTINGS_PAGE,
			array( $this, 'settings_page' )
		);
		add_submenu_page(
			'maxgrid-menu', 
			MAXGRID_PLUGIN_LABEL_NAME . ' - Extensions', 
			'Extensions',
			'manage_options', 
			MAXGRID_EXTENTIONS_PAGE,
			array( $this, 'extensions_page' )
		);
	}
	
	/**
	 * Menu icon.
	 *
 	 * @return string
	 */
	public static function menu_icon($type='') {
		if ( $type == 'dashicon' ) {
			return 'dashicons-grid-view';
		}
		if(floatval(get_bloginfo('version')) >= "3.8") {
			$current_color = get_user_option( 'admin_color' );
			if($current_color == 'light') {
				return MAXGRID_ABSURL . '/assets/images/max-grid-icon-light.png';
			} else {
				return MAXGRID_ABSURL . '/assets/images/max-grid-icon.png';
			}
		}
		return null;
	}
	
	/**
	 * Settings callable page
	 */
	public static function builder_page() {
		?>
		 <!-- Create a header in the default WordPress 'wrap' container -->
		<div class="maxgrid_wrap"> 
			<div id="icon-themes" class="icon32"></div>
			<div class="maxgrid_main_title">
				<span class="maxgrid-options-title"><?php echo MAXGRID_PLUGIN_LABEL_NAME; ?> Builder</span>
			</div>
			<div class="maxgrid_alert" style="height: 60px; max-width: 1200px; margin-right: 15px;">
				<?php settings_errors(); ?>
			</div>
			<div class="accordion default maxgrid-settings" id="maxgrid-multitabcontent" >
				
			   	<!-- <div class="maxgrid_wrap" id="maxgrid_wrap_id">   -->  
				<form method="post" id="builder_form" action='options.php'>
				  <div class="tabs tabs-full maxgrid_settings">
					 

					  <div class="tabs-wrap">
						<div class="tabss gridbuilder" id="tabcontent">
						<!-- Section 1's content -->
							<?php
								do_settings_sections("maxgrid_builder_settings");
								settings_fields("builder_section");                            
								 ?>
						</div>
					  </div>
				   </div>                                              
					 <?php   				
					?>          
				</form>
				<?php  
				echo self::how_to_use_video();
				?>
			</div><!-- end accordion --> 
		</div> <!-- end warp -->
		<?php
	}
	 
	/**
	 * Settings callable page
	 */
	public static function settings_page() {	
		$option_id = 'maxgrid_builder_settings';
		
		// Get last active options tab ID
		$current_tab = isset($_GET['tab']) ? sanitize_html_class($_GET['tab']) : maxgrid_get_last_options_tab_id($option_id);
		$current_tab = $current_tab == 'tab1' && !self::$logs_tab ? 'tab2' : $current_tab;
 		
		?>
		 <!-- Create a header in the default WordPress 'wrap' container -->
		<div class="maxgrid_wrap"> 
			<div id="icon-themes" class="icon32"></div>
			<div class="maxgrid_main_title">
				<span class="maxgrid-options-title"><?php echo MAXGRID_PLUGIN_LABEL_NAME; ?> Settings</span>
			</div>
			<div class="maxgrid_alert" style="height: 60px; max-width: 1200px; margin-right: 15px;">
				<?php settings_errors(); ?>
			</div>
			<div class="accordion default maxgrid-settings" id="maxgrid-multitabcontent" >
				
			   	<div id="maxgrid_tabs_content" class="maxgrid_content_cell"> 
					<form method="post" id="maxgrid_options_form" data-bp-option="<?php echo $option_id; ?>" action='options.php'>
					  <div class="tabs tabs-full maxgrid_settings">

					<?php if ( self::$logs_tab ) { ?>
						  <input id="tab1" type="radio" name="tabs" class="tab-input" data-target="preview_panel" <?php if ($current_tab == 'tab1') { echo 'checked' ;}; ?> />
								<label for="tab1" id="tab-label" class="tab-label tracks">
									<span class="dashicons dashicons-list-view"></span>
									<span class="maxgrid_tab_title">Logs</span>
									</label>					  
					<?php } ?>

						  <input id="tab2" type="radio" name="tabs" class="tab-input" data-target="preview_panel" <?php if ($current_tab == 'tab2') { echo 'checked' ;}; ?> />
								<label for="tab2" id="tab-label" class="tab-label general">
									<span class="dashicons dashicons-feedback"></span>
									<span class="maxgrid_tab_title">Forms</span>
									</label>
						  <input id="tab3" type="radio" name="tabs" class="tab-input" data-target="preview_panel" <?php if ($current_tab == 'tab3') { echo 'checked' ;}; ?> />
								<label for="tab3" id="tab-label" class="tab-label social">
									<span class="dashicons dashicons-networking"></span>
									<span class="maxgrid_tab_title">Social</Soci></span>
									</label>
						  <input id="tab4" type="radio" name="tabs" class="tab-input" data-target="preview_panel" <?php if ($current_tab == 'tab4') { echo 'checked' ;}; ?> />
								<label for="tab4" id="tab-label" class="tab-label api">
									<span class="dashicons dashicons-admin-network"></span>
									<span class="maxgrid_tab_title">API</span>
									</label>
						  <input id="tab5" type="radio" name="tabs" class="tab-input" data-target="preview_panel" <?php if ($current_tab == 'tab5') { echo 'checked' ;}; ?> />
								<label for="tab5" id="tab-label" class="tab-label extras">
									<span class="dashicons dashicons-plus-alt"></span>
									<span class="maxgrid_tab_title">Extras</span>
									</label>
						  <input id="tab6" type="radio" name="tabs" class="tab-input" data-target="preview_panel" <?php if ($current_tab == 'tab6') { echo 'checked' ;}; ?> />
								<label for="tab6" id="tab-label" class="tab-label custom-css">
									<span class="dashicons dashicons-editor-code"></span>
									<span class="maxgrid_tab_title">Custom CSS</span>
									</label>

						  <div class="tabs-wrap">

						<?php if ( self::$logs_tab ) { ?>
							<div class="tab" id="tab1content">
							<!-- Section 1's content -->
								<?php
									do_settings_sections("logs_settings");
									settings_fields("maxgrid_section"); 
								  ?>
							</div>
						<?php } ?>


							<div class="tab" id="tab2content">
							<!-- Section 2's content -->
								<?php
									do_settings_sections("forms_settings");
									settings_fields("maxgrid_section"); 
								  ?>
							</div>
							<div class="tab" id="tab3content">
							<!-- Section 3's content -->
								<?php
									do_settings_sections("social_settings");
									settings_fields("maxgrid_section"); 
								  ?>
							</div>
							<div class="tab" id="tab4content">
							<!-- Section 4's content -->
								<?php
									do_settings_sections("api_settings");
									settings_fields("maxgrid_section"); 
								  ?>
							</div>
							<div class="tab" id="tab5content">
							<!-- Section 5's content -->
								<?php
									do_settings_sections("extras_settings");
									settings_fields("maxgrid_section"); 
								  ?>
							</div>
							<div class="tab" id="tab6content">
							<!-- Section 6's content -->
								<?php
									do_settings_sections("css_settings");
									settings_fields("maxgrid_section"); 
								  ?>
							</div>
						  </div>
					   </div>                                              
						 <?php   				
						?>          
					</form>
				</div> 
				<?php 
				if ( !is_maxgrid_premium_activated() ) {
					echo self::mg_premium_ads();					
				}
				?>
			</div><!-- end accordion --> 
		</div> <!-- end warp -->
		<?php
	}
	
	/**
	 * Settings callable page
	 */
	public static function extensions_page() {
		?>
		 <!-- Create a header in the default WordPress 'wrap' container -->
		<div class="maxgrid_wrap"> 
			<div id="icon-themes" class="icon32"></div>
			<div class="maxgrid_main_title">
				<span class="maxgrid-options-title"><?php echo MAXGRID_PLUGIN_LABEL_NAME; ?> Extensions</span>
			</div>
			
			<div class="maxgrid-addons">
				<div class="addons-block-items">
					<div class="addons-block-item plugin-card">
						<div class="plugin-card-top">
							<div class="name column-name">
								<h3>
									YouTube Stream Add-on
									<img class="plugin-icon" src="<?php echo MAXGRID_ABSURL . '/assets/images/youtube-stream.jpg'?>">
								</h3>
							</div>
							
							<div class="desc column-description">
								<p>YouTube Stream is an add-on for <strong>Max Grid</strong> that allows you to add videos from different sources like YouTube channels and playlists.</p>
							</div>
							<div class="plugin-card-bottom">
								<a href="<?php echo MAXGRID_SITE_HOME_PAGE;?>/max-grid-youtube-stream-add-on/" class="addons-button" target="_blank">From: €29</a>
							</div>
						</div>
					</div>
						
				</div>
			</div>
			<?php  
			//echo self::how_to_use_video();
			?>
		</div> <!-- end warp -->
		<?php
	}
	
	/**
	 * How To Use Video
	 */
	public static function how_to_use_video() {
		?>
		<div id="ads-sidebar-container" class="maxgrid_content_cell">
			<div class="maxgrid-sidebar">
				<div class="maxgrid_content_cell_title maxgrid-sidebar__title">
					<?php // echo __( 'Recommendations for you', 'max-grid' )?>					
				</div>
				<div class="maxgrid-sidebar__section">
					<div style="padding: 0 20px">
						<h2 style="padding-left: 0">How To Use</h2>
						<iframe width="100%" height="auto" src="https://www.youtube.com/embed/koTPRDJOk8g" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						<br>
						<h2 style="padding-left: 0">How To Configure Posts</h2>
						<iframe width="100%" height="auto" src="https://www.youtube.com/embed/_48TvhdbwPg" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						<br>
						<br>
						<!--  class="button button-primary get-maxgrid-premium-btn" -->
						<a href="<?php echo MAXGRID_SITE_HOME_PAGE;?>/docs/" target="_blank" style="margin: 20px 0 0 0; width: 100%;">More documentations</a>
					</div>
				</div>

			</div>

		</div>
		<?php
	}
	
	/**
	 * Amx Grid Premium ADS
	 */
	public static function mg_premium_ads() {
		?>
		<div id="ads-sidebar-container" class="maxgrid_content_cell">
			<div class="maxgrid-sidebar">
				<div class="maxgrid_content_cell_title maxgrid-sidebar__title">
					<?php // echo __( 'Recommendations for you', 'max-grid' )?>					
				</div>
				<div class="maxgrid-sidebar__section">
					<h2>Upgrade to Max Grid Premium</h2>
					<ul> 
						<li>Build Unlimited Templates.</li>
						<li>More grid elements.</li>
						<li>Visual Composer integration.</li>
						<li>Social media sharing add-on.</li>
						<li>Term colors.</li>
						<li class="nostyle">And more...</li>
						<!--
						<li>Build Unlimited Templates.</li>
						<li>Migrate your grid templates from site to site in seconds.</li>
						<li>More grid elements.</li>
						<li>Ajax “Love This” button and post views counter.</li>
						<li>Advanced ribbons.</li>
						<li>WAV, MP3, OGG &amp; SoundCloud Audio player element.</li>
						<li>Visual Composer integration.</li>
						<li>Social media sharing add-on.</li>
						<li>Term color.</li>
						<li class="nostyle">And more...</li>
						-->
					</ul>
					<a href="<?php echo MAXGRID_SITE_HOME_PAGE;?>/max-grid-premium-add-on/" class="button button-primary get-maxgrid-premium-btn" target="_blank">Learn more</a>
				</div>

			</div>

		</div>
		<?php
	}

	/**
	 * Builder Settings fields
	 */
	public function builder_settings() {
		global $builder_attr;
		
		$Max_Grid_Builder = new Max_Grid_Builder;

		$pslug 			= 'post_default';
		$path 			= 'Default Template';
		
		$options 		= unserialize(maxgrid_template_load($pslug));	
		$args 	 		= array('options' => $options, 'source_type' => 'post');
		$html 	 		= $Max_Grid_Builder->construct();
		
		$tooltip_style 	= 'grey';
		$root_name 		= 'rows_options';
		$manager_icon 	= '<span class="dashicons dashicons-category"></span>';
		$duplicate_icon = '<span class="dashicons dashicons-admin-page"></span>';
		$pannel_name 	= '<div class="ui-single-btn pannel-name">Templates</div>';
		$layout_manager_btn = '<div class="ui-single-btn mid-pos layout-manager" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.__('Templates Library', 'max-grid').'" data-row-id="'.$root_name.'" data-savechanges="off" data-action="templates_manager" data-ui-panel-title="'.__('Templates Library', 'max-grid').'">'.$manager_icon.'<span></span></div>';
		$layout_path = '<span class="layout-path" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.__('Current template path', 'max-grid').'">Library / Post / '.$path.'</span>';

		$duplicate_template_btn = '<div id="duplicate-layout" class="ui-single-btn mid-pos layout-manager duplicate-layout" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.__('Duplicate template', 'max-grid').'" data-descript="' . __('Something that you can quickly identify the layout preset with.', 'max-grid') . '" data-heading="Template name :" >'.$duplicate_icon.'<span></span></div>';

		$dup_button = '<span class="ui-combo-btn layout">' . $pannel_name . $layout_manager_btn . $duplicate_template_btn . $layout_path . '</span>';
		
		// Source Type Data
		$source_data = array( 'post' => 'Post' );
		
		if ( is_maxgrid_woo_activated() ) {
			$source_data['product'] = 'WooCommerce';
		}
		
		if ( is_maxgrid_download_activated() ) {
			$source_data[MAXGRID_POST] = 'Download';
		}
		
		if ( is_maxgrid_youtube_activated() ) {
			$source_data['youtube_stream'] = 'Youtube';
		}
		
		$data_source_type = isset(get_option(MAXGRID_BUILDER_OPT_NAME)['source_type']) ? get_option(MAXGRID_BUILDER_OPT_NAME)['source_type'] : 'post';
		$source_type = array( 
				'type' 		  => 'Select',
				'chosen' 	  => true,
				'id' 		  => 'source_type',	
				'values' 	  => $source_data,
				'default' 	  => 'post',
				'data_attr'   => 'data-source-type="' . $data_source_type.'"',
				'display' 	  => 'inline',
				);

		$buttons = [
				'builder_save_changes' 	=> array(
						'label' 	 	=> 'Save Changes',
						'color' 	 	=> 'bordered',				
						'responsive' 	=> 'off',
						'icon' 		 	=> 'save no-icon responsive',
					),
				'builder_preview_changes' 	=> array(
						'label' 		=> 'Preview',
						'color' 		=> 'blue',				
						'responsive' 	=> 'off',
						'icon' 			=> 'preview-icon no-icon responsive',
					),
			];

		$builder_attr = [
			'meta_name' => MAXGRID_BUILDER_OPT_NAME,
			
			// Save changes & reset options buttons - on top.
			array( 
				'type' 			=> 'SaveChanges',
				'position' 		=> 'top',
				'buttons' 		=> $buttons,			
				'html_include'  => $source_type,			
				'reset_button'  => false,
			),
			array( 
				'type' 			=> 'HtmlBlock',
				'html-content'  => $dup_button,
				'class' 	 	=> 'layout-path-container',
			),
			
			// Max Grid Builder - The Builder HTML Content
			array( 
				'type' 			=> 'HtmlBlock',
				'html-content'  => $html,
				'class' 	 	=> 'grid-builder-parent',
			),
		];
		?>
		<div id="maxgrid_metaoptions_container">
		<?php
			$form = new Form($builder_attr);
			$form->render();
		?>
		</div>
		<?php
	}

	/**
	 * Logs Settings fields
	 */
	public function logs_settings() {
		
		$pages = get_pages();
		$page_list = array();
		$page_list[get_home_url().'/wp-login.php'] = 'Default wp-login page';
		foreach ( $pages as $page ) {
			$page_list[get_page_link( $page->ID )] = $page->post_title;
		}	

		$custom_meta_help = '<strong class="block">Example :</strong>
							  <p>
							  if you’re creating an Essential Grid using posts from the Events Manager plugin, a “Meta Reference” could be created for the post’s official “Event Date”.</p>';
		
		$track_attr = [
			'meta_name' 	=> MAXGRID_SETTINGS_OPT_NAME,
			'parent' 	  	=> MAXGRID_LOGS_OPT_NAME,
			/*
			// Save changes & reset options buttons - on top.
			array( 
				'type'	 		=> 'SaveChanges',
				'position' 		=> 'top',
			)*/
			
			];
		
		if ( is_maxgrid_download_activated() ) {
			array_push (
				$track_attr,

				//	+-------------------+
				//	| Download logs tab |
				//	*-------------------+

				array( 
					'type' 		=> 'Title',
					'tab_target'=> 'download_logs',
					'label' 	=> 'Download Restrictions',
					'class' 	=> 'subtitle', // subtitle | default is: full-width
				),

				// Tab in
				array( 
					'type'	 	=> 'AccordionTab',
					'id' 		=> 'download_logs',
					'position' 	=> 'in',
				),

				// Only allow logged in user to download
				array( 
					'type' 		  => 'CheckBox',
					'id' 		  => 'download_logged_in',
					'class' 	  => 'extras-triggers',
					'target_name' => 'download_logged_in_target',
					'label' 	  => 'Only allow logged in users to download',
					'default' 	  => true,
					'display' 	  => 'inline',
					),

				// Separator height values : thinnest | thinner | thin | wide | wide | wider | widest | default
				array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				array( 
					'type' 		  => 'Select',
					'chosen' 	  => true,
					'id' 		  => 'login_page',
					'class' 	  => 'auto_width', // auto_width to auto width chosen dropdown menu		
					'target' 	  => 'download_logged_in_target',
					'label' 	  => 'Select which login page you want to use',
					'label_style' => 'block',
					'values' 	  => $page_list,
					'default' 	  => get_home_url().'/wp-login.php',
					'display' 	  => 'inline',
					),

				array( 'type' => 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				// Only allow logged in user to download
				array( 
					'type' 		  => 'CheckBox',
					'id' 		  => 're_download_count',
					'class' 	  => 'extras-triggers',		
					'target_name' => 're_download_count_target',	
					'label' 	  => 'Block re-count by IP',
					'default' 	  => false,
					'display' 	  => 'inline-block',
					),

				array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				// Only allow logged in user to download
				array( 
					'type' 		=> 'Text',
					'id' 		=> 're_download_timeout',		
					'target' 	=> 're_download_count_target',
					'label' 	=> 'IP Timeout',
					'default' 	=> '10',
					'max_width' => '100px',
					'display' 	=> 'inline',
					),

				// Time Units
				array( 
					'type' 		=> 'Select',
					'chosen' 	=> true,
					'id' 		=> 're_download_timeunits',
					'class' 	=> 'auto_width', // auto width chosen dropdown menu		
					'target' 	=> 're_download_count_target',
					'values' 	=> array(
									'days' 	  => 'Day(s)',
									'hours'   => 'Hour(s)',		
									'minutes' => 'Minute(s)',
								),
					'default' 	=> 'minutes',
					'display' 	=> 'middle-inline',
					),

				// Tab out
				array( 
					'type'	 	=> 'AccordionTab',
					'position' 	=> 'out',
				)
			);
		}
		
		if ( is_maxgrid_premium_activated() ) {
			
			array_push (
				$track_attr,
				
				//	+-----------------+
				//	| Post Voting tab |
				//	*-----------------+

				array( 
					'type' 		=> 'Title',
					'tab_target'=> 'post_voting',
					'label' 	=> 'Post Voting',
					'class' 	=> 'subtitle', // subtitle | default is: full-width
				),

				// Tab in
				array( 
					'type'	 	=> 'AccordionTab',
					'id' 		=> 'post_voting',
					'position' 	=> 'in',
				),

				// Only allow logged in user to download
				array( 
					'type' 		=> 'CheckBox',
					'id' 		=> 'vote_logged_in',
					'label' 	=> 'Only allow logged in users to vote',
					'default' 	=> false,
					'display' 	=> 'inline-block',
					),

				array( 'type' => 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				// Only allow logged in user to download
				array( 
					'type' 		  => 'CheckBox',
					'id' 		  => 'block_re_vote',
					'class' 	  => 'extras-triggers',		
					'target_name' => 'block_re_vote_target',	
					'label' 	  => 'Block re-vote by IP',
					'default'	  => true,
					'display' 	  => 'inline-block',
					),

				array( 'type' => 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				// Only allow logged in user to download
				array( 
					'type' 		=> 'Text',
					'id' 		=> 're_vote_timeout',		
					'target' 	=> 'block_re_vote_target',
					'label' 	=> 'IP Timeout',
					'default' 	=> '24',
					'max_width' => '100px',
					'display' 	=> 'inline',
					),

				// Time Units
				array( 
					'type' 		=> 'Select',
					'chosen' 	=> true,
					'id' 		=> 're_vote_timeunits',
					'class' 	=> 'auto_width', // auto_width to auto width chosen dropdown menu		
					'target' 	=> 'block_re_vote_target',
					'values' 	=> array(
							'days' 	  => 'Day(s)',
							'hours'   => 'Hour(s)',		
							'minutes' => 'Minute(s)',
						),
					'default' 	=> 'hours',
					'display' 	=> 'middle-inline',
					),

				array( 
					'type' 		=> 'TextBlock',
					'text' 		=> '* 0 means permanent',		
					'target' 	=> 'block_re_vote_target',
					'class' 	=> 'description',
					'display' 	=> 'inline',
					),

				// Tab out
				array( 
					'type'	 	=> 'AccordionTab',
					'position' 	=> 'out',
				),

				//	+----------------+
				//	| Post views tab |
				//	*----------------+

				array( 
					'type' 		=> 'Title',
					'tab_target'=> 'custom_post_meta',
					'label' 	=> 'Custom Post Meta',
					'class' 	=> 'subtitle', // subtitle | default is: full-width
				),

				// Tab in
				array( 
					'type'	 	=> 'AccordionTab',
					'id' 		=> 'custom_post_meta',
					'position' 	=> 'in',
				),

				array( 
					'type' 		  	=> 'Title',
					'label'		  	=> 'Post Views',
					'class'		  	=> 'subtitle no-background',
					),
				
				array( 
					'type' 		  => 'CheckBox',
					'id' 		  => 'use_custom_meta_views',
					'class' 	  => 'extras-triggers',		
					'target_name' => 'custom_meta_target',	
					'label' 	  => 'Use Custom Meta',
					'default'	  => false,
					'display' 	  => 'block',
					),

				array( 
					'type' 		 => 'Text',
					'id' 		 => 'custom_meta_views',
					'label' 	 => 'Meta Key',	
					'target' 	 => 'custom_meta_target',
					'default' 	 => '',
					'text_align' => 'left',
					'max_width'  => '100%',
					'display' 	 => 'inline',
					),

				array( 
					'type' 				=> 'TextBlock',
					'text' 				=> 'Use existing custom Post Views meta from another plugin or your theme.<br><strong>Default meta key :</strong> '.MAXGRID_VIEWS_META_KEY,
					'target' 	 		=> 'custom_meta_target',
					'class' 			=> 'description',
					'display' 			=> 'block',
					'raw_style' 		=> 'font-style: normal; margin-bottom: -8px;',
					),

				array( 'type' 	=> 'Separator', 'height' => 'thinner', 'style' => 'dashed', 'raw_style' => 'margin-top: 20px' ),
				
				// Post Likes meta
				array( 
					'type' 		  	=> 'Title',
					'label'		  	=> 'Post Likes',
					'class'		  	=> 'subtitle no-background',
					),
						
				array( 
					'type' 		  => 'CheckBox',
					'id' 		  => 'use_custom_meta_likes',
					'class' 	  => 'extras-triggers',		
					'target_name' => 'custom_meta_likes_target',	
					'label' 	  => 'Use Custom Meta',
					'default'	  => false,
					'display' 	  => 'block',
					),

				array( 
					'type' 		 => 'Text',
					'id' 		 => 'custom_meta_likes',
					'label' 	 => 'Meta Key',	
					'target' 	 => 'custom_meta_likes_target',
					'default' 	 => '',
					'text_align' => 'left',
					'max_width'  => '100%',
					'display' 	 => 'inline',
					),

				array( 
					'type' 				=> 'TextBlock',
					'text' 				=> 'Use existing custom Post Likes meta from another plugin or your theme.<br><strong>Default meta key :</strong> '.MAXGRID_LIKES_META_KEY,
					'target' 	 		=> 'custom_meta_likes_target',
					'class' 			=> 'description',
					'display' 			=> 'block',
					'raw_style' 		=> 'font-style: normal; margin-bottom: -8px;',
					),

				// Tab out
				array( 
					'type'	 	=> 'AccordionTab',
					'position' 	=> 'out',
				)				
			);
		}
		
		array_push (
			$track_attr,	
			// Save changes & reset options buttons - on bottom.		
			array( 
				'type' => 'SaveChanges',
				'class' => 'button-primary',
				'position' => 'bottom',
			)
		);
		?>
		<div id="maxgrid_metaoptions_container">
		<?php
		$form = new Form($track_attr);
		$form->render();
		?>
		</div>
		<?php
	}

	/**
	 * Forms Settings fields
	 */
	public function forms_settings() {
		
		$attr = [
			'meta_name' 	=> MAXGRID_SETTINGS_OPT_NAME,
			'parent' 	  	=> 'forms',

			//	+-----------------------+
			//	| Ajax Comment Form tab |
			//	*-----------------------+
			
			array( 
				'type' 		=> 'Title',
				'tab_target'=> 'ajax_comment_form_tab',
				'label' 	=> 'Ajax Comment Form',
				'class' 	=> 'subtitle', // subtitle | default is: full-width
			),

			// Tab in
			array( 
				'type'	 	=> 'AccordionTab',
				'id' 		=> 'ajax_comment_form_tab',
				'position' 	=> 'in',
			),

			array( 
				'type' 		 => 'Text',
				'id' 		 => 'form_title',
				'label' 	 => 'Comment Form Title',
				'default' 	 => __( 'Join the discussion', 'max-grid' ),
				'display' 	 => 'inline-block',
				'raw_style'  => 'min-width: 200px;',
				),

			array( 
				'type' 		 => 'Text',
				'id' 		 => 'comments_per_page',
				'label' 	 => 'Comments per page',
				'default' 	 => '6',
				'display' 	 => 'inline-block',
				'raw_style'  => 'min-width: 200px;',
				),

			array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

			array( 
				'type' 		 => 'Color',
				'id' 		 => 'btn_bg_color',
				'label' 	 => 'Button Background Color',
				'default' 	 => '#4d90fe',
				'display' 	 => 'inline-block',
				),

			array( 
				'type' 		 => 'Color',
				'id' 		 => 'btn_text_color',
				'label' 	 => 'Button Text Color',
				'default' 	 => '#FFFFFF',
				'display' 	 => 'inline-block',
				),

			array( 'type' 	=> 'Separator', 'height' => 'thinner', 'style' => 'dashed' ),

			array( 
				'type' 		 	=> 'CheckBox',
				'id' 		 	=> 'disable_ajax_form',
				'label' 	 	=> 'Disable on single post.',
				'default' 	 	=> 'true',
				'display' 		=> 'block',
				),

			// Tab out
			array( 
				'type'	 	=> 'AccordionTab',
				'position' 	=> 'out',
			),

		];
		
		// reCAPTCHA authentication tab 
		if ( is_maxgrid_premium_activated() ) {
			array_push (
				$attr,
				array( 
					'type' 		=> 'Title',
					'tab_target'=> 'authentication_tab',
					'label' 	=> 'reCAPTCHA',
					'class' 	=> 'subtitle', // subtitle | default is: full-width
				),

				// Tab in
				array( 
					'type'	 	=> 'AccordionTab',
					'id' 		=> 'authentication_tab',
					'position' 	=> 'in',
				),

				array( 
					'type' 		 	=> 'ToggleSwitch',
					'id' 		 	=> 'enable_recaptcha',
					'class' 	  	=> 'extras-triggers',	
					'target_name' 	=> 'enable_recaptcha_target',
					'color_theme' 	=> 'green',
					'default' 	 	=> 'true',
					'display' 		=> 'block',
					),

				array( 
					'type' 		 	=> 'Text',
					'id' 		 	=> 'site_key',
					'label' 	 	=> 'Site Key',
					'default' 	 	=> '',
					'target' 	 	=> 'enable_recaptcha_target',
					'text_align' 	=> 'left',
					'max_width'  	=> '100%',
					'display' 	 	=> 'inline',
					'raw_style'  	=> 'min-width: 300px; width: 50%;',
					),

				array( 
					'type' 		 	=> 'TextBlock',
					'text' 		 	=> '* Enter your reCaptcha Public Key.',
					'target' 		=> 'enable_recaptcha_target',
					'class' 	 	=> 'description',
					'display' 	 	=> 'inline',
					),

				array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				array( 
					'type' 		 	=> 'Text',
					'id' 		 	=> 'secret_key',
					'label' 	 	=> 'Secret Key',
					'default' 	 	=> '',
					'target' 		=> 'enable_recaptcha_target',
					'text_align' 	=> 'left',
					'max_width'  	=> '100%',
					'display' 	 	=> 'inline',
					'raw_style'  	=> 'min-width: 300px; width: 50%;',
					),

				array( 
					'type' 		 	=> 'TextBlock',
					'text'  	 	=> '* Enter your reCaptcha Private Key.',
					'class' 	 	=> 'description',
					'target' 		=> 'enable_recaptcha_target',
					'display' 		=> 'inline',
					),

				array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				array( 
					'type' 			=> 'HtmlBlock',
					'class'		  	=> 'g-recaptcha-checker',
					'target' 		=> 'enable_recaptcha_target',
					'html-content'  => is_maxgrid_premium_activated() ? maxgrid()->builder_ajax->g_recaptcha_checker() : '',
					'raw_style'  	=> 'line-height: 0;min-height: 0;',
					),

				array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				array( 
					'type' 			=> 'TextBlock',
					'text' 			=> 'You can sign up for a free reCaptcha account <a href="http://www.google.com/recaptcha" target="_blank">here</a>.',
					'display' 		=> 'inline',
					'target' 		=> 'enable_recaptcha_target',
					'class' 		=> 'description',
					'raw_style' 	=> 'font-style: normal;',
					),

				// reCAPTCHA Options
				array( 'type' 	=> 'Separator', 'height' => 'thinnest', 'style' => 'dashed' ),

				array( 
					'type' 		  	=> 'Title',
					'label'		  	=> 'reCAPTCHA Options',
					'class'		  	=> 'subtitle no-background',
					'target' 		=> 'enable_recaptcha_target',
					),

				// Hide to logged in user
				array( 
					'type' 		  	=> 'CheckBox',
					'id' 		  	=> 'hide_for_logged',
					'label' 	  	=> 'Hide reCAPTCHA for logged in users',
					'default' 	  	=> false,
					'target' 		=> 'enable_recaptcha_target',
					'display' 	  	=> 'inline',
					),

				array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				array( 
					'type' 		=> 'Select',
					'chosen' 	=> true,
					'id' 		=> 'captcha_theme',
					'target' 	=> 'enable_recaptcha_target',
					'label' 	=> 'Theme',
					'values' 	=> array(
									'light'  => 'Light',
									'dark'   => 'Dark',
								),
					'default' 	=> 'light',
					'display' 	=> 'inline',
					),

				// Tab out
				array( 
					'type'	 	=> 'AccordionTab',
					'position' 	=> 'out',
				)
			);
		}
		
		// Review Star Ratings tab 
		if ( is_maxgrid_woo_activated() || is_maxgrid_download_activated() ) {
			array_push (
				$attr,
				array( 
					'type' 		=> 'Title',
					'tab_target'=> 'review_ratting_tab',
					'label' 	=> 'Review Star Ratings',
					'class' 	=> 'subtitle', // subtitle | default is: full-width
				),

				// Tab in
				array( 
					'type'	 	=> 'AccordionTab',
					'id' 		=> 'review_ratting_tab',
					'position' 	=> 'in',
				),

				// Only allow logged in user to download
				array( 
					'type' 		 => 'Text',
					'id' 		 => 'ratting_title',
					'label' 	 => 'Title',
					'default' 	 => __( 'Your rating', 'max-grid' ),
					'text_align' => 'left',
					'display' 	 => 'inline-block',
					'raw_style'  => 'display: block; min-width: 200px;',
					),

				// Default youtube channel id
				array( 
					'type' 		 => 'Color',
					'id' 		 => 'stars_color',
					'label' 	 => 'Star Color',
					'default' 	 => '#F4B30A',
					'display' 	 => 'inline-block',
					),

				// Tab out
				array( 
					'type'	 	=> 'AccordionTab',
					'position' 	=> 'out',
				)
			);
		}
		
		// Save changes & reset options buttons - on bottom.
		array_push (
			$attr,					
			array( 
				'type' => 'SaveChanges',
				'class' => 'button-primary',
				'position' => 'bottom',
			)
		);
		
		?>
		<div id="maxgrid_metaoptions_container">
		<?php
		$form = new Form($attr);
		$form->render();
		?>
		</div>
		<?php
	}

	/**
	 * Social Settings fields
	 */
	public function social_settings() {
		global $extras_attr;

		$social_list = array(
				'facebook' 	  => array('Facebook', true),
				'twitter' 	  => array('Twitter', true),
				'google' 	  => array('Google+', true),
				'blogger'     => array('Blogger', true),
				'reddit'   	  => array('reddit', true),
				'tumblr' 	  => array('Tumblr', true),
				'pinterest'   => array('Pinterest', true),
				'vkontakte'   => array('VKontakte', true),
				'linkedin' 	  => array('LinkedIn', true),
				'stumbleupon' => array('StumbleUpon', true),
				'email' 	  => array('email', true),
			);

		$default_og_image_help = 'This image is used if the post/page being hared does not contain any images.<br>
								  <strong class="block">Pixel Dimensions</strong>
								  <p>
								  The recommended image size for Facebook is 1200 by 630 pixels.</p>';

		$facebook_debugger = '<strong class="block">Facebook URL debugger.</strong>
								<p>To crawl a post, simply enter the URL and hit "Debug".</p>
								<strong class="block">Update Facebook sharing data:</strong>
								<p style="line-height: 18px;">If you’ve updated your social settings or metadata for an article and aren’t getting the right details in Facebook yet, you might need to force update Facebook’s cache of that page. To do that, go to the <a href="https://developers.facebook.com/tools/debug/sharing/" target="_blank">Facebook debug tool</a>, enter the URL you wish to update and click scrape again. After that you should be ok.</p>';

		$attr = [
			'meta_name' 	=> MAXGRID_SETTINGS_OPT_NAME,
			'parent' 	  	=> 'general_options',
		];
		
		// Social sharing tab 
		if ( is_maxgrid_premium_activated() ) {
			array_push (
				$attr,		
				array( 
					'type'		  => 'Title',
					'tab_target'  => 'social_sharing',
					'label'		  => 'Social Sharing',
					'class' 	  => 'subtitle', // subtitle | default is: full-width
				),

				// Tab in
				array( 
					'type'	 	=> 'AccordionTab',
					'id' 		=> 'social_sharing',
					'position' 	=> 'in',
				),

				array(
					'type' 		  => 'Title',
					'label'		  => 'Select Networks To Add:',
					'class'		  => 'subtitle no-background',
					),

				array( 
					'type' 		  => 'CheckBoxCombo',
					'id' 		  => 'social_list',
					'class' 	  => 'track_social_list',
					'list' 		  => $social_list,
					'max_allowed' => 0, // zero = no limiting checkboxes selection
					'default' 	  => true,
					),

				array( 'type' => 'Separator', 'height' => 'thin', 'style' => 'dashed' ),

				array( 
					'type' 		  => 'Title',
					'label'		  => 'Facebook Open Graph',
					'class'		  => 'subtitle no-background',
					),

				array( 
					'type' 		 	=> 'ToggleSwitch',
					'id' 		 	=> 'add_open_graph',
					'color_theme' 	=> 'green',
					'default' 	 	=> 'true',
					'class' 	  	=> 'extras-triggers',	
					'target_name' 	=> 'default_og_image_target',
					'display' 		=> 'block',
					),

				array( 
					'type' 				=> 'TextBlock',
					'text' 				=> 'Add Open Graph meta data to your site\'s <code>< head ></code> section, Facebook and other social networks use this data when your pages are shared.</br>* Use <a href="https://developers.facebook.com/tools/debug/sharing/" target="_blank">Facebook Debugger</a> to update Facebook sharing data.',
					'help_Tooltip' 		=> base64_encode($facebook_debugger),
					'Tooltip_style' 	=> 'light',
					'class' 			=> 'description',
					'display' 			=> 'inline',
					'raw_style' 		=> 'font-style: normal;',
					),

				array( 
					'type' 		  => 'Title',
					'label'		  => 'Default Settings',
					'class'		  => 'subtitle no-background',
					),

				array( 
					'type' 		  	=> 'SingleImageUpload',
					'id' 		  	=> 'default_og_image',	
					'label' 	  	=> 'upload / choose an image or add the URL here.',
					'help_Tooltip' 	=> base64_encode($default_og_image_help),
					'Tooltip_style' => 'light',
					'default'	  	=> '',
					'label_style' 	=> 'block',
					'display' 	  	=> 'block',
					'target' 		=> 'default_og_image_target',
					),

				// Tab out
				array( 
					'type'	 	=> 'AccordionTab',
					'position' 	=> 'out',
				)				
			);
		}
		
		// Author Follow Button tab 
		array_push (
			$attr,			
			array( 
				'type' 		=> 'Title',
				'tab_target'=> 'follow_button_tab',
				'label' 	=> 'Author Follow Button',
				'class' 	=> 'subtitle', // subtitle | default is: full-width
			),

			// Tab in
			array( 
				'type'	 	=> 'AccordionTab',
				'id' 		=> 'follow_button_tab',
				'position' 	=> 'in',
			),

			array( 
				'type' 		  	=> 'Title',
				'label'		  	=> 'Twitter',
				'class'		  	=> 'subtitle no-background',
				),
			
			array( 
				'type' => 'TextBlock',
				'text' => 'Button Size',
				'display' => 'inline',
				'raw_style' => 'font-weight: bold; margin-bottom: -5px; min-width: 100px;',
				),

			array( 
				'type' 		=> 'Select',
				'chosen' 	=> true,
				'id' 		=> 'twttr_btn_size',
				'class' 	=> 'auto_width', // auto_width to auto width chosen dropdown menu
				'values' 	=> array(
						'medium'  => 'Medium',
						'large'   => 'Large',
					),
				'default' 	=> 'medium',
				'display' 	=> 'middle-inline',
				),

			array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

			array( 
				'type' 		=> 'TextBlock',
				'text' 		=> 'Follower count',
				'display' 	=> 'inline',
				'raw_style' => 'font-weight: bold; margin-bottom: -5px; min-width: 100px;',
				),

			array( 
				'type' 		=> 'Select',
				'chosen' 	=> true,
				'id' 		=> 'twttr_count',
				'class' 	=> 'auto_width', // auto_width to auto width chosen dropdown menu
				'values' 	=> array(
						'horizontal' => 'Show',
						'none'   	 => 'Hide',
					),
				'default' 	=> 'none',
				'display' 	=> 'middle-inline',
				),

			array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

			array( 
				'type' 		=> 'TextBlock',
				'text' 		=> 'Screen Name',
				'display' 	=> 'inline',
				'raw_style' => 'font-weight: bold; margin-bottom: -5px; min-width: 100px;',
				),

			array( 
				'type' 		=> 'Select',
				'chosen' 	=> true,
				'id' 		=> 'twttr_screen_name',
				'class' 	=> 'auto_width', // auto_width to auto width chosen dropdown menu
				'values' 	=> array(
						'true' 	=> 'Show',
						'false' => 'Hide',
					),
				'default' 	=> 'true',
				'display' 	=> 'middle-inline',
				),

			// Tab out
			array( 
				'type'	 	=> 'AccordionTab',
				'position' 	=> 'out',
			),
			
			// Save Change & Reset Options buttons - in bottom		
			array( 
				'type' => 'SaveChanges',
				'class' => 'button-primary',
				'position' => 'bottom',
			)
		);
		?>
		<div id="maxgrid_metaoptions_container">
		<?php
		$form = new Form($attr);
		$form->render();
		?>
		</div>
		<?php
	}

	/**
	 * API Settings fields
	 */
	public function api_settings() {
		
		$attr = [
			'meta_name' 	=> MAXGRID_SETTINGS_OPT_NAME,
			'parent' 	  	=> 'api_options',

			//	+-----------------+
			//	| Youtube API tab |
			//	*-----------------+
			
			array( 
				'type' 		=> 'Title',
				'tab_target'=> 'api_tab',
				'label' 	=> 'Youtube API',
				'class' 	=> 'subtitle', // subtitle | default is: full-width
			),

			// in Tab Content
			array( 
				'type'	 	=> 'AccordionTab',
				'id' 		=> 'api_tab',
				'position' 	=> 'in',
			),

			// Only allow logged in user to download
			array( 
				'type' 		 => 'Text',
				'id' 		 => 'youtube_api_key',
				'label' 	 => 'Youtube API Key',
				'default' 	 => '',
				'text_align' => 'left',
				'max_width'  => '100%',
				'display' 	 => 'inline',
				'raw_style'  => 'width: calc(100% - 118px);',
				),

			array( 
				'type' 			=> 'SingleButton',
				'label' 		=> 'Connect',
				'id' 			=> 'save_ytb_api_key',
				'color' 		=> 'blue',
				'icon' 			=> 'no-icon',
				'ajax_response' => true,
				'display' 	 	=> 'inline',
				'raw_style' 	=> 'position: relative; margin: 0;',
				),

			array( 
				'type' 		=> 'TextBlock',
				'text' 		=> 'Follow this tutorial to get your Youtube Data API Key : <a href="https://developers.google.com/youtube/v3/getting-started#before-you-start" target="_blank">YouTube Data API Overview</a>.',
				'display' 	=> 'block',
				'class' 	=> 'description',
				'raw_style' => 'margin-bottom: -8px;'
				),

			array( 
				'type' 		=> 'TextBlock',
				'text' 		=> 'Status : ',
				'display' 	=> 'inline',
				'raw_style' => 'font-weight: bold; min-height: 0px;',
				),

			array( 
				'type' 		=> 'TextBlock',
				'class' 	=> 'ytb_api_key_status',
				'text' 		=> '',
				'display' 	=> 'inline',
				'raw_style' => 'font-weight: normal; min-height: 0px;',
				),
			];
		
		if ( is_maxgrid_premium_activated() ) {
			array_push (
				$attr,
				array( 'type' 	=> 'Separator', 'height' => 'thinner', 'raw_style' => 'margin-top: 10px;', 'style' => 'dashed' ),

				// Default youtube channel id
				array( 
					'type' 		 => 'Text',
					'id' 		 => 'channel_id',
					'data_id'	 => true,
					'label' 	 => 'Default Channel ID (Optional)',
					'default' 	 => '',
					'text_align' => 'left',
					'max_width'  => '100%',
					'display' 	 => 'inline',
					'raw_style'  => 'width: calc(100% - 118px);',
					'class' 	 => 'ytb-channel-id',
					),

				array( 
					'type' 		   => 'HtmlBlock',
					'html-content' => '<span class="ytb-id-checker-btn icon-loop2" style="top: -5px;"></span>',
					'display' 	   => 'inline',
					'class' 	   => 'api-ytb-channel-id-check',
					),

				array( 
					'type' 		   => 'HtmlBlock',
					'html-content' => '<div id="ytb-id-checker_indicator"><i class="fa fa-check grey"></i></div>',
					'display' 	 => 'inline',
					),

				array( 
					'type' 		   => 'HtmlBlock',
					'html-content' => '<div id="ytb-id-checker_response" class="maxgrid-secondary"></div>',
					),

				array( 
					'type' 		 => 'Hidden',
					'id' 		 => 'dflt_channel_id',
					'default' 	 => '',
					'text_align' => 'left',
					'max_width'  => '100%',
					'display' 	 => 'inline',
					),

				array( 'type' 	=> 'Separator', 'height' => 'thinnest', 'style' => 'solid' )
			);
		}
		
		array_push (
			$attr,
			// Tab out
			array( 
				'type'	 	=> 'AccordionTab',
				'position' 	=> 'out',
			),

			//	+------------------+
			//	| Cache System tab |
			//	*------------------+

			array( 
				'type'		  => 'Title',
				'tab_target'  => 't_caches',
				'label'		  => 'Cache System',
				'class' 	  => 'subtitle', // subtitle | default is: full-width
			),		

			// Tab in
			array( 
				'type'	 	=> 'AccordionTab',
				'id' 		=> 't_caches',
				'position' 	=> 'in',
			),

			// Cache Expire
			array( 
				'type' 		=> 'Text',
				'id' 		=> 'clear_cache_delay',
				'label' => 'Clear Cache Delay',
				'default' => '60',
				'max_width' => '150px',
				'display' => 'inline',
				),

			// Cache Expire Time Units
			array( 
				'type' => 'Select',
				'chosen' => true,
				'id' => 'delay_timeunits',
				'class' => 'auto_width',
				'values' => array(
					'days' => 'Day(s)',
					'hours' => 'Hour(s)',		
					'minutes' => 'Minute(s)',
				),
				'default' => 'minutes',
				'display' => 'middle-inline',
				),

			array( 
				'type' => 'TextBlock',
				'text' => '* Enter 0 to disable the caching system.',
				'class' => 'description',
				'display' => 'inline',
				),

			array( 
				'type' => 'TextBlock',
				'text' => 'This cache system will cache your youtube video grid using the WordPress Transients API, resulting in faster page load.<br>Clear caches after you change the "Clear Cache Delay" value.',
				'class' => 'description',
				'display' => 'inline',
				),

			array( 'type' => 'Separator', 'height' => 'thin', 'style' => 'dashed' ),

			array( 
				'type' 			=> 'SingleButton',
				'label' 		=> 'Clear Caches',
				'id' 			=> 'clear_caches',
				'color' 		=> 'secondary',
				'icon' 			=> 'no-icon',
				'message' 		=> 'Are you sure you want to clear caches?',
				'float' 		=> 'right',
				'ajax_response' => true,
				'wp_spinner' 	=> true,
				),

			// Tab out
			array( 
				'type'	 	=> 'AccordionTab',
				'position' 	=> 'out',
			),

			// Save Change & Reset Options buttons - in bottom		
			array( 
				'type' => 'SaveChanges',
				'class' => 'button-primary',
				'position' => 'bottom',
			)
		);
		
		?>
		<div id="maxgrid_metaoptions_container">
		<?php
		$form = new Form($attr);
		$form->render();
		?>
		</div>
		<?php
	}

	/**
	 * Extras Settings fields
	 */
	public function extras_settings() {
		
		$attr = [
			'meta_name' 	=> MAXGRID_SETTINGS_OPT_NAME,
			'parent' 	  	=> 'extras_options',

			//	+-------------------+
			//	| Color schemes tab |
			//	*-------------------+
			 
			array( 
				'type' 		=> 'Title',
				'tab_target'=> 'theme_tab',
				'label' 	=> 'Accent Colors',
				'class' 	=> 'subtitle settings-page', // subtitle | default is: full-width
			),

			// Tab in
			array( 
				'type'	 	=> 'AccordionTab',
				'id' 		=> 'theme_tab',
				'position' 	=> 'in',
			),
			
			array( 
				'type' 		=> 'Color',
				'id' 		=> 'extra-color-1',
				'label' 	=> 'Accent Color',
				'default' 	=> '#31c1eb',
				'display' 	=> 'inline-block',
				),

			array( 
				'type' 		=> 'TextBlock',
				'text' 		=> 'Primary color used for links, buttons and icons on hover state.',
				'class' 	=> 'description',
				'display' 	=> 'block',
				'raw_style' => 'font-style: normal;',
				),

			array( 
				'type' 		=> 'Color',
				'id' 		=> 'extra-color-2',
				'label' 	=> 'Text (over Base)',
				'default' 	=> '#ffffff',
				'display' 	=> 'inline-block',
				),

			array( 
				'type' 				=> 'TextBlock',
				'text' 				=> 'Color used for button\'s font color on hover state.',
				'class' 			=> 'description',
				'display' 			=> 'block',
				'raw_style' 		=> 'font-style: normal;',
				),
			
			array( 'type' 	=> 'Separator', 'height' => 'thinnest', 'style' => 'solid' ),

			array( 
				'type' 		=> 'Color',
				'id' 		=> 'extra-color-3',
				'label' 	=> 'Extra Color #1',
				'default' 	=> '#ff1053',
				'display' 	=> 'inline-block',
				),

			array( 
				'type' 		=> 'Color',
				'id' 		=> 'extra-color-4',
				'label' 	=> 'Extra Color #2',
				'default' 	=> '#333333',
				'display' 	=> 'inline-block',
				),

			array( 
				'type'	 	=> 'AccordionTab',
				'position' 	=> 'out',
			),
		];
		
		// Post Type tab
		if ( is_maxgrid_download_activated() ) {
			array_push (
				$attr,
				array( 
					'type' 		=> 'Title',
					'tab_target'=> 'custom_post_tab',
					'label' 	=> 'Post Type',
					'class' 	=> 'subtitle', // subtitle | default is: full-width
				),

				// Tab in
				array( 
					'type'	 	=> 'AccordionTab',
					'id' 		=> 'custom_post_tab',
					'position' 	=> 'in',
				),

				array( 
					'type' 		  	=> 'Title',
					'label'		  	=> 'Download',
					'class'		  	=> 'subtitle no-background',
					),
				
				array( 
					'type' 		 => 'Color',
					'id' 		 => 'dld_btn_bg_color',
					'label' 	 => 'Background Color',
					'default' 	 => '#73c02a',
					'display' 	 => 'inline-block',
					),

				array( 
					'type' 		 => 'Color',
					'id' 		 => 'dld_btn_text_color',
					'label' 	 => 'Text Color',
					'default' 	 => '#FFFFFF',
					'display' 	 => 'inline-block',
					),

				array( 'type' 	=> 'Separator', 'height' => 'thin', 'style' => 'solid' ),

				array( 
					'type' 		  	=> 'Title',
					'label'		  	=> 'Download Permalinks',
					'class'		  	=> 'subtitle no-background',
					),
				
				array( 
					'type' 		=> 'TextBlock',
					'text' 		=> 'If you like, you may enter custom structures for your download URLs here.<br>For example, using <code>apk</code> would make your download links like <code>'.get_home_url().'/apk/sample-download/</code>.',
					'class' 	=> 'description',
					'display' 	=> 'inline',
					'raw_style' => 'font-style: normal;',
					),

				array( 'type' 	=> 'Separator', 'height' => 'null', 'style' => 'no_line' ),

				// Only allow logged in user to download
				array( 
					'type' 		 => 'Text',
					'id' 		 => 'custom_base',
					'label' 	 => 'Custom base',
					'default' 	 => '/download/',
					'text_align' => 'left',
					'max_width'  => '150px',
					'display' 	 => 'inline',
					),

				array( 
					'type' => 'TextBlock',
					'text' => 'Enter a custom base to use. A base must be set or WordPress will use default instead.	',
					'class' => 'description',
					'display' => 'inline',
					),

				// Tab out
				array( 
					'type'	 	=> 'AccordionTab',
					'position' 	=> 'out',
				)
			);
		}
		// Save changes & reset options buttons - on bottom.
		array_push (
			$attr,					
			array( 
				'type' => 'SaveChanges',
				'class' => 'button-primary',
				'position' => 'bottom',
			)
		);
		?>
		<div id="maxgrid_metaoptions_container">
		<?php
		$form = new Form($attr);
		$form->render();
		?>
		</div>
		<?php
	}

	/**
	 * Extras Settings fields
	 */
	public function custom_css_settings() {
		global $attr;

		$attr = [
			'meta_name' 	=> MAXGRID_SETTINGS_OPT_NAME,
			
			//	+---------------------+
			//	| Custom CSS Code tab |
			//	*---------------------+
			
			// Tab in
			array( 
				'type'	 	=> 'AccordionTab',
				'id' 		=> 'custom_css',
				'position' 	=> 'in',
			),

			array( 
				'type' 		  => 'CustomCSS',
				'id' 		  => 'custom_css_area',
				'default' 	  => MAXGRID_CSS_CODE_COMMENT,
				),

			// Tab out
			array( 
				'type'	 	=> 'AccordionTab',
				'position' 	=> 'out',
			),

			// Save Change & Reset Options buttons - in bottom		
			array( 
				'type' => 'SaveChanges',
				'class' => 'button-primary',
				'position' => 'bottom',
			),

		];
		?>
		<div id="maxgrid_metaoptions_container">
		<?php
		$form = new Form($attr);
		$form->render();
		?>
		</div>
		<?php
	}
		
	/**
	 * add Settings
	 */
	public function add_settings_fields() {
		add_settings_section("builder_section", "", null, "maxgrid_builder_settings");
		add_settings_field(MAXGRID_BUILDER_OPT_NAME, "", array($this, 'builder_settings'), "maxgrid_builder_settings", "builder_section");
		
		register_setting("builder_section", MAXGRID_BUILDER_OPT_NAME);
		
		if ( self::$logs_tab ) {
			add_settings_section("maxgrid_section", "", null, "logs_settings");
			add_settings_field(MAXGRID_SETTINGS_OPT_NAME, "", array($this, 'logs_settings'), "logs_settings", "maxgrid_section");
		}
		
		add_settings_section("maxgrid_section", "", null, "api_settings");
		add_settings_field(MAXGRID_SETTINGS_OPT_NAME, "", array($this, 'api_settings'), "api_settings", "maxgrid_section");

		add_settings_section("maxgrid_section", "", null, "forms_settings");
		add_settings_field(MAXGRID_SETTINGS_OPT_NAME, "", array($this, 'forms_settings'), "forms_settings", "maxgrid_section");

		add_settings_section("maxgrid_section", "", null, "social_settings");
		add_settings_field(MAXGRID_SETTINGS_OPT_NAME, "", array($this, 'social_settings'), "social_settings", "maxgrid_section");

		add_settings_section("maxgrid_section", "", null, "extras_settings");
		add_settings_field(MAXGRID_SETTINGS_OPT_NAME, "", array($this, 'extras_settings'), "extras_settings", "maxgrid_section");

		add_settings_section("maxgrid_section", "", null, "css_settings");
		add_settings_field(MAXGRID_SETTINGS_OPT_NAME, "", array($this, 'custom_css_settings'), "css_settings", "maxgrid_section");

		register_setting("maxgrid_section", MAXGRID_SETTINGS_OPT_NAME);
	}
	
	/**
	 * Preview mode template
	 *
	 * @param string $template
	 */
	public function grid_preview_page( $template ) {
		if ( isset($_REQUEST['grid_preview_template']) && $_REQUEST['grid_preview_template'] == 'on') {
			$new_template = MAXGRID_ABSPATH . 'includes/preview-mode-template.php';
			if ( '' != $new_template ) {
				return $new_template ;
			}
		}
		return $template;
	}
}
new Max_Grid_Settings_Page();