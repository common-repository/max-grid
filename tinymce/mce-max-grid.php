<?php
/**
 * Max Grid Builder - Shortcode Generator
 */

require_once(ABSPATH.'wp-includes/pluggable.php');

use \MaxGrid\getPresets;
use \MaxGrid\Youtube;
use \MaxGrid\Template;
use \MaxGrid\getOptions;
use \MaxGrid\share;

defined( 'ABSPATH' ) || exit;

/**
 * @class Max_Grid_Shortcode.
 */
class Max_Grid_Shortcode {
	
	/**
	 * $shortcode_tag
	 * Holds the name of the shortcode tag.
	 * @var string
	 */
	public $shortcode_tag = 'maxgrid';
	
	/**
	 * This is the default youtube channel id.
	 *
	 * @var string
	 */
	const DFLT_YOUTUBE_CHANNEL_ID = 'UCXXBi6rvC-u8VDZRD23F7tw';
	
	/**
	 * Constructor.
	 */
	function __construct() {	
		add_action( 'save_post', array($this, 'my_project_updated_send_email') );
		if ( is_admin() ) {			
			if (maxgrid()->is_edit_page()) {
				add_action('init', array($this, 'admin_head'), 100);
				add_action( 'init', array($this , 'add_editor_style' ) );
				add_filter('tiny_mce_before_init', array($this , 'wpdocs_theme_editor_dynamic_styles') );
			}			
			
			add_action( 'admin_enqueue_scripts', array($this , 'admin_enqueue_scripts' ) );		
			add_action( 'wp_ajax_get_recent_post_thumbnail', array($this , 'get_recent_post_thumbnail' ) );
			add_action( 'wp_ajax_nopriv_get_recent_post_thumbnail', array($this , 'get_recent_post_thumbnail' ) );
		}
		add_shortcode( $this->shortcode_tag, array( $this, 'shortcode_handler' ));		
	}
	
	/**
	 * do_action when a post is saved.
	 * to prevent "Updating failed" and "Publishing failed".
	 * 
	 * @param int $post_id The post ID.
	 */
	public function my_project_updated_send_email( $post_id ) {
		do_action('post_saved');
	}
	
	/**
	 * Shortcode handler
	 *
	 * @param  array  $atts shortcode attributes
	 * @param  string $content shortcode content
	 *
	 * @return string
	 */
	public function shortcode_handler($atts) {
		global $post_id, $wpdb, $timezone_string;
		if ( did_action( 'post_saved' ) || is_admin() ) {
			return;
		}
				
		$page_id = get_the_ID();
		
		// Extract Meta Options 
		extract(maxgrid_get_meta_options());
		
		// Shortcode Default Values					
		$get_options 		= maxgrid()->get_options;
		$default_channel_id = isset($get_options->option('api_options')['channel_id']) && $get_options->option('api_options')['channel_id'] != '' ? $get_options->option('api_options')['channel_id'] : self::DFLT_YOUTUBE_CHANNEL_ID;
		
		if ( is_maxgrid_youtube_activated() ) {
			$youtube = new Youtube();
			$ytb_id  = $youtube->get_channel_id($default_channel_id);
		} else {
			$ytb_id = 'ERROR';
		}
		
		$a = shortcode_atts( array(
			'title'      		=> 'Auto Draft',
			'id'      			=> '',
			'post_type'  		=> MAXGRID_POST,
			'preset'      		=> MAXGRID_DFLT_LAYOUT_NAME,
			'post_preset'      	=> '',
			'wc_preset'      	=> '',
			'dld_preset'      	=> '',
			'ytb_preset'      	=> '',
			'ytb_type'      	=> 'channel',
			'ytb_id'      		=> $ytb_id != 'ERROR' ? $ytb_id : self::DFLT_YOUTUBE_CHANNEL_ID,
			'ytb_tag'      		=> '',
			'retrieve_hd'      	=> 'off',
			'ytb_banner'        => 'off',
			'ytb_dflt_filter'   => 'date',
			'incl_excl' 		=> 'exclude',
			'exclude' 			=> '',
			'post_exclude' 		=> '',
			'wc_exclude' 		=> '',
			'dld_exclude' 		=> '',
			'ytb_exclude' 		=> '',
			'dflt_mode'  		=> 'grid',
			'masonry' 			=> 'off',
			'full_content' 		=> 'off',
			'ribbon'    		=> 'on',
			'orderby_ftr'  	  	=> 'on',
			'order_ftr'  	  	=> 'on',
			'view_ftr'  	  	=> 'on',
			'tax_ftr'  	  		=> 'on',
			'tag_ftr'  	  		=> 'on',
			'pagination'   	 	=> 'numeric_pagination',
			'ytb_pagination'   	=> 'infinite_scroll',
			'grid_ppp'    		=> '8',
			'ytb_grid_ppp'    	=> '8',
			'list_ppp'    		=> '5',
			'items_per_row' 	=> '4',
			'full_width_page'   => 'on',
			'vc'   				=> 'off',
		), $atts );

		$vc 		 	 = esc_attr($a['vc']);	
		$unique_id 		 = is_maxgrid_premium_activated() ? esc_attr($a['id']) : 'max-grid-id';	
		$post_type 		 = esc_attr($a['post_type']);
		$paged 			 = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
		$ytb_type 		 = esc_attr($a['ytb_type']);
		$ytb_id		 	 = $ytb_type == 'channel' && is_maxgrid_youtube_activated() ? $youtube->get_channel_id(esc_attr($a['ytb_id'])) : esc_attr($a['ytb_id']);
		$ytb_tag		 = esc_attr($a['ytb_tag']);
		$ytb_banner		 = esc_attr($a['ytb_banner']);
		$ytb_dflt_filter = esc_attr($a['ytb_dflt_filter']);
		$retrieve_hd	 = esc_attr($a['retrieve_hd']);
		$layout_type 	 = esc_attr($a['dflt_mode']);
		$masonry_layout  = esc_attr($a['masonry']);
		$full_content 	 = esc_attr($a['full_content']);
		$full_content 	 = $full_content == '' ? 'off' : $full_content;
		$ribbon 		 = esc_attr($a['ribbon']);
		$ribbon 		 = $ribbon == '' ? 'on' : $ribbon;
		$orderby_ftr	 = $a['orderby_ftr'] != '' ? esc_attr($a['orderby_ftr']) : 'off';
		$order_ftr	 	 = $a['order_ftr'] != '' ? esc_attr($a['order_ftr']) : 'off';
		$view_ftr	 	 = $a['view_ftr'] != '' ? esc_attr($a['view_ftr']) : 'off';
		$tax_ftr	 	 = $a['tax_ftr'] != '' ? esc_attr($a['tax_ftr']) : 'off';
		$tag_ftr	 	 = $a['tag_ftr'] != '' ? esc_attr($a['tag_ftr']) : 'off';
		
		if ( !is_maxgrid_premium_activated() ) {
			$tax_ftr	 = 'off';
			$tag_ftr	 = 'off';			
		}
		
		$pagination   	 = esc_attr($a['pagination']);
		
		if ( $pagination == 'infinite_scroll' && !is_maxgrid_premium_activated() ) {
			$pagination	 = 'load_more_button';			
		}
		
		$grid_ppp 		 = esc_attr($a['grid_ppp']);
		$list_ppp 		 = esc_attr($a['list_ppp']);
		$full_width_page = esc_attr($a['full_width_page']);
		$items_per_row 	 = esc_attr($a['items_per_row']);
		$incl_excl_cats	 = esc_attr($a['incl_excl']);
		$excluded_cat	 = esc_attr($a['exclude']);
		$preset_name	 = esc_attr($a['preset']);
		
		//$preset_name	 = !is_maxgrid_premium_activated() && $preset_name == 'post_default' ? 'use_current' : $preset_name;
		$preset_name	 = !is_maxgrid_templates_library() ? 'post_default' : $preset_name;
		
		if ( $vc == 'on') {
			switch ( $post_type ) {
				case 'post':
					$preset_name  = esc_attr($a['post_preset']);
					$excluded_cat = esc_attr($a['post_exclude']);
					break;
				case 'product':
					$preset_name  = esc_attr($a['wc_preset']);
					$excluded_cat = esc_attr($a['wc_exclude']);
					break;
				case 'download':
					$preset_name  = esc_attr($a['dld_preset']);
					$excluded_cat = esc_attr($a['dld_exclude']);				
					break;
				case 'youtube_stream':
					$preset_name  = esc_attr($a['ytb_preset']);
					$pagination   = esc_attr($a['ytb_pagination']);
					$grid_ppp     = esc_attr($a['ytb_grid_ppp']);
					break;
			}
		}
		
		if ( $post_type == 'product' && !class_exists( 'WooCommerce' ) ) {
			echo '<span class="maxgrid-alert wc-alert large danger active">'.__( 'You don\'t appear to have WooCommerce activated!', 'max-grid').'<span class="close-alert">×</span></span>';
			return;
		}		
		
		if ( $post_type == 'product' && !is_maxgrid_woo_activated() ) {
			echo '<span class="maxgrid-alert wc-alert large danger active">'.__( 'You don\'t appear to have Max Grid Woo activated!', 'max-grid').'<span class="close-alert">×</span></span>';
			return;
		}		
		
		if ( $post_type == 'download' && !is_maxgrid_download_activated() ) {
			echo '<span class="maxgrid-alert wc-alert large danger active">'.__( 'You don\'t appear to have Max Grid Download activated!', 'max-grid').'<span class="close-alert">×</span></span>';
			return;
		}	
		
		if ( $post_type == 'youtube_stream' && !is_maxgrid_youtube_activated() ) {
			echo '<span class="maxgrid-alert wc-alert large danger active">'.__( 'You don\'t appear to have Max Grid Youtube activated!', 'max-grid').'<span class="close-alert">×</span></span>';
			return;
		}
		
			
		$pagination_display = "block";
		$source_type = array(
							'type' => $ytb_type,
							'id' => $ytb_id
							);
				
		if ( isset( $_GET['masonry'] ) ) {
			$masonry_layout = sanitize_html_class($_GET['masonry']);
		}
		
		$mxg_preview = 'false';
		if ( isset( $_GET['mxg_preview'] ) ) {
			$mxg_preview = sanitize_html_class($_GET['mxg_preview']);
		}
		
		if ( isset($_GET['preset']) ) {
			$preset_name = sanitize_html_class($_GET['preset']);
		}
		
		if ( isset($_GET['exclude']) ) {
			$excluded_cat = sanitize_textarea_field($_GET['exclude']);
		}
		
		// Extract Options
		$maxgrid_get_options = maxgrid_get_options($post_type, $preset_name);
		extract($maxgrid_get_options);

		$args = array(
			'source_type' => $post_type,
			'preset_name' => $preset_name
		);
		
		$get_presets = new getPresets($args);
		$options 	 = $get_presets->get_parent();
		
		$b_o 	= $get_presets->rows('blocks_row');
		$l_o 	= $get_presets->rows('lightbox_row');
		$a_p 	= $get_presets->rows('audio_row');
		$t_o 	= $get_presets->rows('title_row');
		$c_o 	= $get_presets->rows('add_to_cart_row');
		$f_o 	= $get_presets->rows('featured_row');
		$i_o 	= $get_presets->rows('info_row');
		$dld_o 	= $get_presets->rows('download_row');
		$s_o 	= $get_presets->rows('stats_row');
		
		if ( $post_type=='youtube_stream' ) {
			$d_o = $get_presets->rows('ytb_description_row');
		} else {
			$d_o = $get_presets->rows('description_row');
		}
		
		$lb_theme 			 = isset($l_o['theme']) ? $l_o['theme'] : 'lb-dark-color';		
		$lb_search_bar  	 = isset($l_o['search_bar']) && maxgrid_string_to_bool($l_o['search_bar']) == 1 ? 'lb-search-bar' : '';
		$bloc_h_fit  		 = isset($f_o['fit_width']) && maxgrid_string_to_bool($f_o['fit_width']) == 1 ? ' bloc_h_fit' : '';		
		$r_side_status 		 = isset($l_o['r_side_status']) && $l_o['r_side_status'] == 1 && !maxgrid_is_mobile() ? 'open' : 'none';
		$overlay_click_close = ( isset($l_o['overlay_click_close']) && $l_o['overlay_click_close'] == 1 ) ? 'close-is-on' : '';
		$post_hover_shadow 	 = isset($b_o['box_shadow']) ? ' '.$b_o['box_shadow'] : '';

		// Youtube API Checker
		if ( is_maxgrid_youtube_activated() && $post_type == 'youtube_stream' && $ytb_type == 'channel' ) {
			
			$settings_url 	= get_home_url().'/wp-admin/admin.php?page='.MAXGRID_SETTINGS_PAGE.'&tab=tab4';			
			$get_locale 	= get_locale();
			$lang_arr 		= explode("_", $get_locale, 2);
			$lang_id 		= $lang_arr[0];
			$ytb_help_url 	= 'https://support.google.com/youtube/answer/3250431?hl='.$lang_id;
			$my_api_key 	= isset($get_options->option('api_options')['youtube_api_key']) ? $get_options->option('api_options')['youtube_api_key'] : '';
			
			$youtube_url 	= 'https://www.googleapis.com/youtube/v3/';
			$api_key 		= $youtube->get_API_key();
			$api_url 		= $youtube_url.'search?part=snippet&channelId='.$ytb_id.'&key='.$api_key;
			$response 		= json_decode(wp_remote_fopen($api_url));

			if( isset($response->error->errors[0]->reason) && $response->error->errors[0]->reason == 'keyInvalid' ) {
				$error_loading_msg = MAXGRID_ERROR_LOADING . '<span class="alert-msg">'. sprintf( __( 'Please verify your %sAPI key%s is correctly entered on the settings page.', 'max-grid'), '<a href="'.$settings_url.'" target="_blank">', '</a>') . '</span>';
			} else if( isset($response->error->errors[0]->reason) && $response->error->errors[0]->reason == 'invalidChannelId' ) {			
				$error_loading_msg = MAXGRID_ERROR_LOADING . '<span class="alert-msg">'. sprintf( __( 'This Channel Does Not Exist!<br> Please check your channel\'s user ID or channel ID - <a href="%s" target="_blank" title="Find your YouTube user & channel IDs">YouTube Help</a>', 'max-grid'), $ytb_help_url) . '</span>';				
			} else if( !isset($my_api_key) || empty($my_api_key ) ) {
				$error_loading_msg = MAXGRID_ERROR_NOTE . '<span class="alert-msg">'. sprintf( __( 'It is recommended to use your own <a href="%s" target="_blank"> Youtube API key</a>', 'max-grid'), $settings_url ). '</span>';
				echo '<span class="maxgrid-alert large warning active">'. $error_loading_msg .'<span class="close-alert">×</span></span>';
			}
			
			if(isset($response->error->errors[0]->reason) ){
				echo '<span class="maxgrid-alert large danger active">'. $error_loading_msg .'<span class="close-alert">×</span></span>';
				return;
			}
			
			// Show Youtube header banner
			if($ytb_banner=='on'){
				echo $youtube->get_header($ytb_id);
			}
		}
		
		?>	
		
		<script type="text/javascript">
			var loadingTextDomain = '<?php echo __( 'Loading', 'max-grid' );?>';
		</script>

		<?php
		
		$allowed_post_type = array('post');
		if( $post_type == 'product' && is_maxgrid_woo_activated() ) {
			$allowed_post_type[] = 'product';
		}

		if( $post_type == 'download' && is_maxgrid_download_activated() ){
			$allowed_post_type[] = MAXGRID_POST;
		}
		
		// Post Sorting
		if($incl_excl_cats=='exclude'){
			$sort = new Max_Grid_Post_Sorting;
			$exclude_category = explode( ',', $excluded_cat );
			if ( in_array($post_type, $allowed_post_type ) ){
				$all_categories = $sort->all_cat($post_type, $exclude_category);
			}
		} else {
			$all_categories = $excluded_cat;
			$exclude_category = array();
		}
		array_push($exclude_category, "uncategorized");
		
		$sort_by_most_download_option = get_option( 'maxgrid_sort_by_most_download' );
		$sort_by_most_download = isset($sort_by_most_download_option['postlink']) ? isset($sort_by_most_download_option['postlink']) : 'on';
		$youtube_channel = isset($ytb_type) ? ' ytb_'.$ytb_type : '';
		$grid_container = (isset($b_o['grid_container']) && maxgrid_string_to_bool($b_o['grid_container']) == 1) ? '' : ' no-grid-container';

		$hide_filter = $orderby_ftr == 'off' && $order_ftr == 'off' && $view_ftr == 'off' && $tax_ftr == 'off' && $tag_ftr == 'off' ? 'off' : 'on';
		?>	
		<div class="maxgrid-body" data-g-uid="<?php echo $unique_id;?>" data-grid-ppp="<?php echo $grid_ppp; ?>" data-list-ppp="<?php echo $list_ppp; ?>">
			
			<div  id="block_filter" class="inline-select-wrapper maxgrid-parent<?php echo $youtube_channel.$grid_container.' filter-'.$hide_filter;?>" >
				<form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="epg_builder_filter">
					<?php 
						if ( isset($_GET['category']) && $_GET['category'] != '') {
							echo '<input type="hidden" name="category" value="'. sanitize_text_field( $_GET['category'] ) .'">';	
						}
						if ( isset($_GET['layout']) && $_GET['layout'] != '') {
							echo '<input type="hidden" name="layout" value="'. sanitize_text_field( $_GET['layout'] ) .'">';	
						}
						if ( isset($_GET['tag']) && $_GET['tag'] != '') {
							echo '<input type="hidden" name="tag" value="'. sanitize_text_field( $_GET['tag'] ) .'">';	
						}
						?>
					<div class="medium_dropdown" <?php echo $orderby_ftr == 'off' ? 'style="display:none"' : '';?>>
						 <select name="orderby" data-post-type="<?php echo $post_type;?>" data-page="" data-placeholder="Your Favorite Type of Bear" id="chosen_orderby" class="chosen_orderby" tabindex="12">
						<?php
						if ( $post_type == "product" ) {
							?>
							<option value="date" selected="selected" >
								<?php echo __('Newest items', 'max-grid');?>
								</option>

							<option value="_price">
							<?php echo __('Price', 'max-grid');?>
							</option>

							<option value="total_sales">
							<?php echo __('Best Sellers', 'max-grid');?>
							</option>

						<option value="_wc_average_rating" >
								<?php echo __('Best rated', 'max-grid');?>
								</option>

						<option value="_sale_price">
							<?php echo __('Promotional Products', 'max-grid');?>
							</option>
						<?php
						} else if ( $post_type == "youtube_stream" ) {				
							$ytb_order = array(
								'relevance'  => __('Relevance', 'max-grid'),
								'date' 		 => __('Upload date', 'max-grid'),
								'viewCount'  => __('View count', 'max-grid'),
								'rating' 	 => __('Rating', 'max-grid'),
							);
							foreach( $ytb_order as $key => $value ) {
								?>						
								<option value="<?php echo $key;?>" <?php echo $key == $ytb_dflt_filter ? 'selected="selected"' : '';?>>
								<?php echo $value;?>
								</option>
						<?php }?>

						<?php
						} else if ( $post_type == "post" || $post_type == "download" ) {
						?>	
						<option value="date" selected="selected" >
								<?php echo __('Newest items', 'max-grid');?>
								</option>

						<?php if ( is_maxgrid_premium_activated() ) {							 
							$views_meta_key = maxgrid_use_custom_post_meta_key( 'views' );
							$views_meta_key = $views_meta_key ? $views_meta_key : MAXGRID_VIEWS_META_KEY;

							$likes_meta_key = maxgrid_use_custom_post_meta_key( 'likes' );
							$likes_meta_key = $likes_meta_key ? $likes_meta_key : MAXGRID_LIKES_META_KEY;
							?>
							<option value="<?php echo $views_meta_key;?>">
										<?php echo __('Most Popular', 'max-grid');?>
									</option>

							<option value="<?php echo $likes_meta_key;?>" >
									<?php echo __('Most liked', 'max-grid');?>
									</option>
							 
						<?php }	?>
							 
						<?php
							if ( $sort_by_most_download == "on" && is_maxgrid_download_activated() ) {
								?>
								<option value="<?php echo MAXGRID_DOWNLOAD_META_KEY;?>" >
									<?php echo __('Most downloaded', 'max-grid');?>
								</option>
							<?php
							}
							?>
						<?php
						}
						?>
						</select>
					</div>
					
			<?php
			
			if ( in_array($post_type, $allowed_post_type ) ){ ?>
			<!-- single dropdown -->				
				<div class="switch-buttons-container" style="display: inline-block">
					<div class="menu-divider"></div><!-- divider! -->
					
					<div class="layout-sort-buttons">
						<input type="radio" name="order" id="desc_sort_<?php echo $unique_id;?>" class="desc_sort" value="DESC"/>
						<label class="desc-sort" for="desc_sort_<?php echo $unique_id;?>" title="DESC Order"></label>
						<input type="radio" name="order" id="asc_sort_<?php echo $unique_id;?>" class="asc_sort" value="ASC"/>
						<label class="asc-sort" for="asc_sort_<?php echo $unique_id;?>" title="ASC Order"></label>
					</div>

					 <div class="menu-divider"></div><!-- divider! -->
					<!-- single dropdown -->
					<div class="layout-switch-buttons">
						<input type="radio" name="layout" id="grid_view_<?php echo $unique_id;?>" class="grid_view" value="grid"/>
						<label class="grid-view" for="grid_view_<?php echo $unique_id;?>" title="Grid"></label>
						<input type="radio" name="layout" id="list_view_<?php echo $unique_id;?>" class="list_view" value="list"/>
						<label class="list-view" for="list_view_<?php echo $unique_id;?>" title="List"></label>
					</div>
				</div> <!-- end container layout-switch -->

				<div class="term_filter_container" style="display: inline-block;">
					<div class="menu-divider" id='layout_divider'></div><!-- divider! -->	 

					<select name="categoryfilter[]" data-placeholder="<?php echo __('Choose a Category', 'max-grid');?>" multiple class="chosen_category" tabindex="8">
					<?php
					
					$cat_terms = array(
						MAXGRID_POST => get_terms(MAXGRID_CAT_TAXONOMY, array('hide_empty' => false)),
						'product' => get_terms('product_cat', array('hide_empty' => true)),
						'post' => get_categories(),
					);																   	
 
					foreach($cat_terms[$post_type] as $term){
						if ( !in_array($term->slug, $exclude_category) ) {
							echo '<option value="'.$term->slug.'"';
						}
						echo '>'.$term->name.'</option>';
					}

					?>
					</select>

					<div class="menu-divider" id='tags_divider'></div><!-- divider! -->	
					<select name="tag[]" data-placeholder="<?php echo __('Choose a Tags', 'max-grid');?>" multiple class="chosen_tags" tabindex="8">				
					<?php

					$taxonomy_tag = array(
						MAXGRID_POST 	=> MAXGRID_TAG_TAXONOMY,
						'product' 		=> 'product_tag',
						'post' 			=> 'post_tag',
					);

					$tag_terms = get_terms( $taxonomy_tag[$post_type], array('hide_empty' => true) );

					$all_tags = array();
					foreach($tag_terms as $term){
						$all_tags[] = $term->slug;
						echo '<option value="'.$term->slug.'"';					
						echo '>'.$term->name.'</option>';
					}
					?>
					</select>

					<a class="show-all-cat-btn" href="#" onclick="return false;"><?php echo __( 'Show All', 'max-grid' );?></a>
				</div><!-- end container Category & Tags Filter -->
				<?php } ?>
					
				<?php
					$grid_container_filter = isset($b_o['grid_container']) && maxgrid_string_to_bool($b_o['grid_container']) == 1 ? ' style' : '';
					$list_container_filter = isset($b_o['list_container']) && maxgrid_string_to_bool($b_o['list_container']) == 1 ? ' style' : '';
				
					$form_data = array(
						'preview-mode'	  => $mxg_preview,
						'post-type'	  	  => $post_type,
						'page-id'	  	  => $page_id,
						'preset-name' 	  => $preset_name,
						'ytb-type' 		  => $source_type['type'],
						'ytb-id' 		  => $source_type['id'],
						'ytb-tag' 	  	  => $ytb_tag,
						'ret-hd' 	  	  => $retrieve_hd,
						'dflt-filter'  	  => $ytb_dflt_filter,
						'pagination'  	  => $pagination,
						'masonry'     	  => $masonry_layout,
						'items-pr'    	  => $items_per_row,
						'grid-container'  => $grid_container_filter,
						'list-container'  => $list_container_filter
					);
					
					if ( in_array($post_type, $allowed_post_type ) ){
						$form_data['action'] = 'maxgrid_construct';
						$form_data['dflt-view'] = $layout_type;
						$form_data['ribbon'] = $ribbon;
						$form_data['full-content'] = $full_content;
						$form_data['excl-cats'] = $excluded_cat;
						$form_data['all-cats'] = $all_categories;
						$form_data['all-tags'] = rtrim(implode(',', $all_tags), ',');
					}
					echo '<input type="hidden" class="get-form-data"';
					foreach($form_data as $key => $value) {
						echo ' data-'.$key.'="'.$value.'"';
					}
					echo '>';
					
				?>
			 </form>
		  </div>

	<div class="maxgrid_grid_container maxgrid-parent maxgrid<?php echo $grid_container.' '.$preset_name;?>" id="maxgrid_grid_container" data-grid-pid="<?php echo get_the_ID();?>" data-old-items-per-row="<?php echo $items_per_row; ?>" data-dflt-playlist="<?php echo $r_side_status;?>" data-post-type="<?php echo $post_type;?>" data-pagination="<?php echo $pagination;?>" data-lb-search-bar="<?php echo $lb_search_bar;?>" data-lb-theme="<?php echo $lb_theme;?>" data-overlay-click-close="<?php echo $overlay_click_close;?>"></div>
	<div></div>

	<?php if ($post_type != 'youtube_stream') { ?>
		<div class="load-more_container post <?php echo $pagination.'_pagination';?>" data-pagination="<?php echo $pagination;?>" data-post-type="<?php echo $post_type;?>"><span class="load-more" style="display: none"><?php __( 'Load More', 'max-grid' ); ?></span></div>
	<?php }?>
	</div><!-- end : maxgrid_grid_container -->
	<?php	
		$extra_colors = array(
				'extra_color_1' => $extra_color_1,
				'extra_color_2' => $extra_color_2,
				'extra_color_3' => $extra_color_3,
				'extra_color_4' => $extra_color_4,
			);
		
		$light_color_theme = maxgrid_colourBrightness($extra_color_1, 0.20);
		$mid_color_theme = maxgrid_colourBrightness($extra_color_1, 0.40);

		if ($masonry_layout == 'on'):
			$title_min_height = 'unset';
			$title_overflow = 'unset';
			$title_position = 'unset';
			$description_min_height = 'unset';
		else:
			$title_min_height = '40px';
			$title_overflow = 'hidden';
			$title_position = 'relative';
			$description_min_height = '65px';
		endif;

		$newest_ribbon 		 = isset($b_o['disable_newest_ribbon']) && maxgrid_string_to_bool($b_o['disable_newest_ribbon']) == 1 ? true : false;
		$views_ribbon 		 = isset($b_o['disable_views_ribbon']) && maxgrid_string_to_bool($b_o['disable_views_ribbon']) == 1 ? true : false;
		$liked_ribbon 		 = isset($b_o['disable_liked_ribbon']) && maxgrid_string_to_bool($b_o['disable_liked_ribbon']) == 1 ? true : false;
		$downloaded_ribbon 	 = isset($b_o['disable_downloaded_ribbon']) && maxgrid_string_to_bool($b_o['disable_downloaded_ribbon']) == 1 ? true : false;
		$onsale_ribbon 		 = isset($b_o['disable_onsale_ribbon']) && maxgrid_string_to_bool($b_o['disable_onsale_ribbon']) == 1 ? true : false;
		$bestseller_ribbon   = isset($b_o['disable_bestseller_ribbon']) && maxgrid_string_to_bool($b_o['disable_bestseller_ribbon']) == 1 ? true : false;
		
		$all_ribb = array($newest_ribbon, $views_ribbon, $liked_ribbon, $downloaded_ribbon, $onsale_ribbon, $bestseller_ribbon);
		$ribbon = in_array(1, $all_ribb) ? $ribbon : 'off';
		
		$newest_color 		 = isset($b_o['newest_color']) ? $b_o['newest_color'] : '#8224e3';
		$newest_color_2 	 = maxgrid_colourBrightness($newest_color, -0.50);
		
		$views_color 		 = isset($b_o['views_color']) ? $b_o['views_color'] : '#25b64c';
		$views_color_2 		 = maxgrid_colourBrightness($views_color, -0.50);

		$liked_color 		 = isset($b_o['liked_color']) ? $b_o['liked_color'] : '#ed1b24';
		$liked_color_2 		 = maxgrid_colourBrightness($liked_color, -0.50);

		$downloaded_color  	 = isset($b_o['downloaded_color']) ? $b_o['downloaded_color'] : '#1e5799';
		$downloaded_color_2  = maxgrid_colourBrightness($downloaded_color, -0.50);

		$onsale_color 		 = isset($b_o['onsale_color']) ? $b_o['onsale_color'] : '#f32744';
		$onsale_color_2 	 = maxgrid_colourBrightness($onsale_color, -0.50);

		$bestseller_color    = isset($b_o['bestseller_color']) ? $b_o['bestseller_color'] : '#fb2c92';
		$bestseller_color_2  = maxgrid_colourBrightness($bestseller_color, -0.50);

		$rimaxgrid_sizes 		 = array('small' => '1.1', 'medium' => '1.25', 'large' => '1.35');
		$ribbon_size 		 = isset($b_o['ribbon_size']) ? $rimaxgrid_sizes[$b_o['ribbon_size']] : 'small';

		// Download
		$dld_btn_marg_top  	 = isset($dld_o['margin_top']) ? $dld_o['margin_top'] : 5;
		$dld_btn_marg_bot  	 = isset($dld_o['margin_bottom']) ? $dld_o['margin_bottom'] : 5;
		$dld_color_theme 	 = isset($dld_o['color_theme']) ? $dld_o['color_theme'] : '#74c23b';
		$dld_extra_c1 	 	 = isset($dld_o['extra_c1']) ? $dld_o['extra_c1'] : 'extra_color_1';
		$dld_btn_color_theme = (isset($dld_o['use_extra_c1']) && maxgrid_string_to_bool($dld_o['use_extra_c1'])== 1) ? $extra_colors[$dld_extra_c1] : $dld_color_theme;
		$dld_btn_font_color  = isset($dld_o['button_font_color']) ? $dld_o['button_font_color'] : '#ffffff';
		
		// Featured download button
		$dld_b_bg_c  	 	 = isset($dld_o['dld_b_bg_c']) ? $dld_o['dld_b_bg_c'] : '#2cba6c';
		$dld_b_f_c  	 	 = isset($dld_o['dld_b_f_c']) ? $dld_o['dld_b_f_c'] : '#ffffff';
		
		// WooCommerce - Add To Cart
		$g_add_to_cart_bg  	 = isset($c_o['color_theme']) ? $c_o['color_theme'] : '#31c1eb';
		$c_o_extra_c1 	 	 = isset($c_o['extra_c1']) ? $c_o['extra_c1'] : 'extra_color_1';
		$grid_add_to_cart_bg = (isset($c_o['use_extra_c1']) && maxgrid_string_to_bool($c_o['use_extra_c1'])== 1) ? $extra_colors[$c_o_extra_c1] : $g_add_to_cart_bg;

		$g_button_font_color = isset($c_o['button_font_color']) ? $c_o['button_font_color'] : '#ffffff';	


		// Lightbox - Add To Cart
		$lb_add_to_cart_bg  	 = isset($l_o['color_theme']) ? $l_o['color_theme'] : '#31c1eb';
		$l_o_extra_c1 	 		 = isset($l_o['extra_c1']) ? $l_o['extra_c1'] : 'extra_color_1';
		$lightbox_add_to_cart_bg = (isset($l_o['use_extra_c1']) && maxgrid_string_to_bool($l_o['use_extra_c1'])== 1) ? $extra_colors[$l_o_extra_c1] : $lb_add_to_cart_bg;	
		$lb_button_font_color 	 = isset($l_o['button_font_color']) ? $l_o['button_font_color'] : '#ffffff';
		
		//comments_count date author category
		$normal_fonts = array("Arial","Comic Sans MS, cursive","Courier, monospace","Impact","Times New Roman","Lucida Console, Monaco, monospace","Tahoma","Verdana");

		$shadow_blur_radius = isset($b_o['shadow_blur_radius']) && $b_o['shadow_blur_radius'] != '' ? $b_o['shadow_blur_radius'] : 4;
		$shadow_opacity = isset($b_o['shadow_opacity']) && $b_o['shadow_opacity'] != '' ? $b_o['shadow_opacity'] : '.1';
		
		// The Featured
		
		$bg_overlay = isset($f_o['background_overlay']) ? $f_o['background_overlay'] : 'rgba(0, 0, 0, 0.5)';
		
		$post_stats = isset($f_o['post_stats']) && maxgrid_string_to_bool($f_o['post_stats']) == 1 && ( isset($f_o['download_count']) && maxgrid_string_to_bool($f_o['download_count']) == 1 || isset($f_o['views_count']) && maxgrid_string_to_bool($f_o['views_count']) == 1 || isset($f_o['like_count']) && maxgrid_string_to_bool($f_o['like_count']) == 1 ) ? true : false;
		
		$post_stats = isset($f_o['top_divider']) && maxgrid_string_to_bool($f_o['top_divider']) == 1 ? true : null;		
			
		$filter_measures = array(
				'blur' 		 => 'px',
				'grayscale'  => '%',
				'hue-rotate' => 'deg',
				'invert' 	 => '%',
				'sepia' 	 => '%',
				'none' 		 => '',
			);

		$filter 	= isset($f_o['filter']) ? $f_o['filter'] : 'none';
		$filter_val = isset($f_o[$filter.'_value']) ? $f_o[$filter.'_value'] : '0';
		
		$fit_to_ovl 	= isset($f_o['fit_to_ovl']) && maxgrid_string_to_bool($f_o['fit_to_ovl']) == 1 ? true : null;
		
		$f_padd 	= $items_per_row == '4' ? 8: 10;
		
		$ovl_p_t = isset($f_o['ovl_p_t']) && $f_o['ovl_p_t'] != '' ? $f_o['ovl_p_t'] : $f_padd;
		$ovl_p_r = isset($f_o['ovl_p_r']) && $f_o['ovl_p_r'] != '' ? $f_o['ovl_p_r'] : $f_padd;
		$ovl_p_b = isset($f_o['ovl_p_b']) && $f_o['ovl_p_b'] != '' ? $f_o['ovl_p_b'] : $f_padd;
		$ovl_p_l = isset($f_o['ovl_p_l']) && $f_o['ovl_p_l'] != '' ? $f_o['ovl_p_l'] : $f_padd;		
		
		$time_padd 	= isset($f_o['duration']) && maxgrid_string_to_bool($f_o['duration']) == 1 ? 33 : 0;
		$fillcover 	= isset($f_o['fillcover_overlay']) && maxgrid_string_to_bool($f_o['fillcover_overlay']) == 1 ? true : null;
		
		$padd_ratio = $items_per_row == '4' ? 8: 10;
		$rad_ratio 	= $items_per_row == '4' ? 10: 20;
				
		// Container radius propagation
		// If Container has border radius on top left corner
		$c_t_l_r = isset($b_o['border_top_left_radius']) ? $b_o['border_top_left_radius'] : 0;
		$f_t_l_r = isset($f_o['border_top_left_radius']) ? $f_o['border_top_left_radius'] : 0;
		$max_t_l_r = max( $c_t_l_r, $f_t_l_r);		
		if ( $max_t_l_r != '' && $max_t_l_r != 0 ) {
			if ( $max_t_l_r > $rad_ratio ) {
				$t_l_padd = ($max_t_l_r/1.7);
			} else {
				$t_l_padd = $padd_ratio;
			}
		} else {
			$t_l_padd = 0;
		}
		
		// If Container has border radius on top right corner
		$c_t_r_r = isset($b_o['border_top_right_radius']) ? $b_o['border_top_right_radius'] : 0;
		$f_t_r_r = isset($f_o['border_top_right_radius']) ? $f_o['border_top_right_radius'] : 0;
		$max_t_r_r = max( $c_t_r_r, $f_t_r_r);		
		if ( $max_t_r_r != '' && $max_t_r_r != 0 ) {
			if ( $max_t_r_r > $rad_ratio ) {
				$t_r_padd = ($max_t_r_r/1.7) > 35 ? ($max_t_r_r/1.7) : 35;
			} else {
				$t_r_padd = 35;
			}
		} else {
			$t_r_padd = 35;
		}
		
		// If Container has border radius on buttom right corner
		$c_b_r_r = isset($b_o['border_bottom_right_radius']) ? $b_o['border_bottom_right_radius'] : 0;
		$f_b_r_r = isset($f_o['border_bottom_right_radius']) ? $f_o['border_bottom_right_radius'] : 0;
		$max_b_r_r = max( $c_b_r_r, $f_b_r_r);		
		if ( $max_b_r_r != '' && $max_b_r_r != 0 ) {
			if ( $max_b_r_r > $rad_ratio ) {
				$b_r_padd = ($max_b_r_r/1.85) + $time_padd;
			} else {
				$b_r_padd = $ovl_p_r;
			}
		} else {
			$b_r_padd = $ovl_p_r;
		}
		if(!isset($fillcover)){
			$b_r_padd = 0;
			$t_r_padd = 0;
		}
		
		// If Container has border radius on buttom left corner
		$c_b_l_r = isset($b_o['border_bottom_left_radius']) ? $b_o['border_bottom_left_radius'] : 0;
		$f_b_l_r = isset($f_o['border_bottom_left_radius']) ? $f_o['border_bottom_left_radius'] : 0;
		$max_b_l_r = max( $c_b_l_r, $f_b_l_r);		
		if ( $max_b_l_r != '' && $max_b_l_r != 0 ) {
			if ( $max_b_l_r > $rad_ratio ) {
				$b_l_padd = ($max_b_l_r/1.7);
			} else {
				$b_l_padd = $padd_ratio;
			}
		} else {
			$b_l_padd = 0;
		}
		if(!isset($fillcover)) {
			$t_l_padd = $b_l_padd;
		}
		
			
		// Featured radius propagation
		// If Featured has border radius on buttom left corner
		if ( isset($f_o['border_bottom_left_radius']) && $f_o['border_bottom_left_radius'] != '' ) {
			if ( $f_o['border_bottom_left_radius'] > $rad_ratio) {
				$excerpt_l_padd = ($f_o['border_bottom_left_radius']/1.3);
			} else {
				$excerpt_l_padd = $padd_ratio;
			}
		} else {
			$excerpt_l_padd = $padd_ratio;
		}		
		
		// If Featured has border radius on buttom right corner
		if ( isset($f_o['border_bottom_right_radius']) && $f_o['border_bottom_right_radius'] != '' ) {		
			$vid_duration_margin = ($f_o['border_bottom_right_radius']/3.6);
			if ( $f_o['border_bottom_right_radius'] > $rad_ratio ) {
				$excerpt_r_padd = ($f_o['border_bottom_right_radius']/1.3);
			} else {
				$excerpt_r_padd = $padd_ratio;
			}
		} else {
			$excerpt_r_padd = $padd_ratio;
			$vid_duration_margin = 0;
		}
		
		// If Container has border radius on buttom right corner
		if ( isset($b_o['border_bottom_right_radius']) && $b_o['border_bottom_right_radius'] != '' ) {
			if ( $b_o['border_bottom_right_radius'] > $rad_ratio ) {
				$excerpt_r_padd_prop = ($b_o['border_bottom_right_radius']/1.3);
			} else {
				$excerpt_r_padd_prop = $padd_ratio;
			}
		} else {
			$excerpt_r_padd_prop = $padd_ratio;
		}
		
		// If Featured has border radius on top right corner
		if ( isset($f_o['border_top_right_radius']) && $f_o['border_top_right_radius'] != '' ) {		
			if ( $f_o['border_top_right_radius']/3.6 > 15 ) {
				$ytb_pause_margin = $f_o['border_top_right_radius']/3.6-10;
			} else {
				$ytb_pause_margin = 0;
			}
			if ( $f_o['border_top_right_radius'] > 15 ) {
				$social_icons_margin = $f_o['border_top_right_radius']/4.5;
			} else {
				$social_icons_margin = 0;
			}
		} else {
			$social_icons_margin = 0;
			$ytb_pause_margin = 0;
		}
		
		// If Container has border radius on top right corner
		if ( isset($b_o['border_top_right_radius']) && $b_o['border_top_right_radius'] != '' ) {		
			if ( $b_o['border_top_right_radius']/3.6 > 15 ) {
				$ytb_pause_marg_prop = $b_o['border_top_right_radius']/3.6-10;
			} else {
				$ytb_pause_marg_prop = 0;
			}
			if ( $b_o['border_top_right_radius'] > 15 ) {
				$social_ico_marg_prop = $b_o['border_top_right_radius']/4.5;
			} else {
				$social_ico_marg_prop = 0;
			}
		} else {
			$social_ico_marg_prop = 0;
			$ytb_pause_marg_prop = 0;
		}
		
		$block_padding_left  = isset($b_o['padding_left']) && $b_o['padding_left'] != '' ? $b_o['padding_left'] : '10';
		$block_padding_right = isset($b_o['padding_right']) && $b_o['padding_right'] != '' ? $b_o['padding_right'] : '10';
		
		// Featured button hover color
		$btn_h_color 		= isset($f_o['button_hover_color']) && $f_o['button_hover_color'] != '' ? $f_o['button_hover_color'] : '#31c1eb';
		$f_o_extra_c1 		= isset($f_o['extra_c1']) ? $f_o['extra_c1'] : 'extra_color_1';
		$featured_btn_h_color = (isset($f_o['use_extra_c1'])&&maxgrid_string_to_bool($f_o['use_extra_c1'])== 1) ? $extra_colors[$f_o_extra_c1] : $btn_h_color;

		$btn_font_h_color 	= isset($f_o['button_font_hover_color']) && $f_o['button_font_hover_color'] != '' ? $f_o['button_font_hover_color'] : '#ffffff';
		$f_o_extra_c2 		= isset($f_o['extra_c2']) ? $f_o['extra_c2'] : 'extra_color_2';
		$featured_btn_font_h_color = (isset($f_o['use_extra_c2'])&&maxgrid_string_to_bool($f_o['use_extra_c2'])== 1) ? $extra_colors[$f_o_extra_c2] : $btn_font_h_color;
		
		// Description row
		$d_text_h_color 	= isset($d_o['text_h_color']) && $d_o['text_h_color'] != '' ? $d_o['text_h_color'] : '#31c1eb';
		$d_o_extra_c1	 	= isset($d_o['extra_c1']) ? $d_o['extra_c1'] : 'extra_color_1';
		$desc_text_h_color  = (isset($b_o['use_extra_c1'])&&maxgrid_string_to_bool($b_o['use_extra_c1'])== 1) ? $extra_colors[$d_o_extra_c1] : $d_text_h_color;

		// Info row
		$i_text_h_color 	= isset($i_o['text_h_color']) && $i_o['text_h_color'] != '' ? $i_o['text_h_color'] : '#31c1eb';
		$i_o_extra_c1 		= isset($i_o['extra_c1']) ? $i_o['extra_c1'] : 'extra_color_1';
		$info_text_h_color  = (isset($i_o['use_extra_c1'])&&maxgrid_string_to_bool($i_o['use_extra_c1'])== 1) ? $extra_colors[$i_o_extra_c1] : $i_text_h_color;

		// Grid Blocks Settings
		$desc_h_color 		= isset($b_o['description_link_h_color']) && $b_o['description_link_h_color'] != '' ? $b_o['description_link_h_color'] : '#31c1eb';
		$b_o_extra_c1 		= isset($b_o['extra_c1']) ? $b_o['extra_c1'] : 'extra_color_1';
		$desc_link_h_color 	= (isset($b_o['use_extra_c1'])&&maxgrid_string_to_bool($b_o['use_extra_c1'])== 1) ? $extra_colors[$b_o_extra_c1] : $desc_h_color;

		// Title row
		$title_h_color 		= isset($t_o['title_h_color']) && $t_o['title_h_color'] != '' ? $t_o['title_h_color'] : '#31c1eb';
		$t_o_extra_c1 		= isset($t_o['extra_c1']) ? $t_o['extra_c1'] : 'extra_color_1';
		$post_title_h_color = (isset($t_o['use_extra_c1'])&&maxgrid_string_to_bool($t_o['use_extra_c1'])== 1) ? $extra_colors[$t_o_extra_c1] : $title_h_color;

		// read more button
		$rm_extra_c1       = isset($options['post_description']['extra_c1']) ? $options['post_description']['extra_c1'] : 'extra_color_1';
		$rm_btn_bg_color   = isset($options['post_description']['btn_bg_color']) ? $options['post_description']['btn_bg_color'] : '#31c1eb';		
		$rm_btn_f_color    = isset($options['post_description']['btn_f_color']) ? $options['post_description']['btn_f_color'] : '#ffffff';
		
		$rm_btn_bg_h_color1= isset($options['post_description']['btn_bg_h_color']) ? $options['post_description']['btn_bg_h_color'] : '#09b6ea';
		$rm_btn_bg_h_color = (isset($options['post_description']['use_extra_c1'])&&maxgrid_string_to_bool($options['post_description']['use_extra_c1'])== 1) ? $extra_colors[$rm_extra_c1] : $rm_btn_bg_h_color1;		
		$rm_btn_f_h_color  = isset($options['post_description']['btn_f_h_color']) ? $options['post_description']['btn_f_h_color'] : '#ffffff';


		// Audio row
		$f_track_color 		= isset($a_p['front_track_color']) && $a_p['front_track_color'] != '' ? $a_p['front_track_color'] : '#31c1eb';
		$a_p_extra_c1 		= isset($a_p['extra_c1']) ? $a_p['extra_c1'] : 'extra_color_1';
		$front_track_color 	= (isset($a_p['use_extra_c1'])&&maxgrid_string_to_bool($a_p['use_extra_c1'])== 1) ? $extra_colors[$a_p_extra_c1] : $f_track_color;

		$b_track_color 		= isset($a_p['back_track_color']) && $a_p['back_track_color'] != '' ? $a_p['back_track_color'] : '#ffffff';
		$a_p_extra_c2 		= isset($a_p['extra_c2']) ? $a_p['extra_c2'] : 'extra_color_1';
		$back_track_color 	= (isset($a_p['use_extra_c2'])&&maxgrid_string_to_bool($a_p['use_extra_c2'])== 1) ? $extra_colors[$a_p_extra_c2] : $b_track_color;
		
		// Term color
		
		$u_f_block_bg_tc = isset($b_o['use_t_block_bg_tc']) && maxgrid_string_to_bool($b_o['use_t_block_bg_tc']) ? true : null;
		$f_block_bg_tc 	 = isset($b_o['block_bg_tc']) ? $b_o['block_bg_tc'] : 'term_c1';
		
		$u_f_block_f_tc  = isset($b_o['use_t_block_f_tc']) && maxgrid_string_to_bool($b_o['use_t_block_f_tc']) ? true : null;
		$f_block_f_tc 	 = isset($b_o['block_f_tc']) ? $b_o['block_f_tc'] : 'term_c2';
		
		$u_t_title_f_tc  = isset($t_o['use_t_title_f_tc']) && maxgrid_string_to_bool($t_o['use_t_title_f_tc']) ? true : null;
		$t_title_f_tc 	 = isset($t_o['title_f_tc']) ? $t_o['title_f_tc'] : 'term_c2';
		
		$rm_o 			 = isset($options['post_description']) ? $options['post_description'] : array();
		$u_rm_btn_f_h_tc = isset($rm_o['use_t_btn_f_h_tc']) && maxgrid_string_to_bool($rm_o['use_t_btn_f_h_tc']) ? true : null;
		$rm_btn_f_h_tc 	 = isset($rm_o['btn_f_h_tc']) ? $rm_o['btn_f_h_tc'] : 'term_c3';		
			
		$u_f_btn_bg_h_tc = isset($f_o['use_t_btn_bg_h_tc']) && maxgrid_string_to_bool($f_o['use_t_btn_bg_h_tc']) ? true : null;
		$f_btn_bg_h_tc 	 = isset($f_o['btn_bg_h_tc']) ? $f_o['btn_bg_h_tc'] : 'term_c1';	
		
		$u_f_ovl_bg_tc	 = isset($f_o['use_t_ovl_bg_tc']) && maxgrid_string_to_bool($f_o['use_t_ovl_bg_tc']) ? true : null;
		$f_ovl_bg_tc 	 = isset($f_o['ovl_bg_tc']) ? $f_o['ovl_bg_tc'] : 'term_c1';
		
		$u_f_ovl_f_tc	 = isset($f_o['use_t_ovl_f_tc']) && maxgrid_string_to_bool($f_o['use_t_ovl_f_tc']) ? true : null;
		$f_ovl_f_tc 	 = isset($f_o['ovl_f_tc']) ? $f_o['ovl_f_tc'] : 'term_c1';
		
		$u_s_stats_bg_tc = isset($s_o['use_t_stats_bg_tc']) && maxgrid_string_to_bool($s_o['use_t_stats_bg_tc']) ? true : null;
		$s_stats_bg_tc 	 = isset($s_o['stats_bg_tc']) ? $s_o['stats_bg_tc'] : 'term_c1';	
		
		$u_s_stats_f_tc  = isset($s_o['use_t_stats_f_tc']) && maxgrid_string_to_bool($s_o['use_t_stats_f_tc']) ? true : null;
		$s_stats_f_tc 	 = isset($s_o['stats_f_tc']) ? $s_o['stats_f_tc'] : 'term_c2';
	
		$u_s_stats_b_tc  = isset($s_o['use_t_stats_border_tc']) && maxgrid_string_to_bool($s_o['use_t_stats_border_tc']) ? true : null;
		$s_stats_b_tc 	 = isset($s_o['stats_border_tc']) ? $s_o['stats_border_tc'] : 'term_c1';
				
		$term_css = '';		
		$use_term = $u_f_block_bg_tc || $u_f_block_f_tc || $u_t_title_f_tc || $u_f_btn_bg_h_tc || $u_s_stats_bg_tc || $u_s_stats_f_tc || $u_s_stats_b_tc || $u_rm_btn_f_h_tc || $u_f_ovl_bg_tc || $u_f_ovl_f_tc ? true : null;
		if ( $post_type != 'youtube_stream' && $use_term ) {			
			$taxonomy = array(
				MAXGRID_POST => MAXGRID_CAT_TAXONOMY,
				'product' => 'product_cat',
				'post' => 'category',
			);																   	
			$c_terms = array(
				MAXGRID_POST => get_terms(MAXGRID_CAT_TAXONOMY, array('hide_empty' => false)),
				'product' => get_terms('product_cat', array('hide_empty' => false)),
				'post' => get_categories(),
			);

			$tax = $taxonomy[$post_type];
			
			foreach ( explode( ',', $all_categories ) as $value ) {
				
				$nicename = $value;
				$terms 	  = get_term_by('slug', $nicename, $tax);
				
				$c1 	  = get_term_meta( $terms->term_id, 'cat_bg_color', true );
				$c2 	  = get_term_meta( $terms->term_id, 'cat_color', true );
				$c3 	  = get_term_meta( $terms->term_id, 'cat_extra_color', true );
				
				$tc = array(					
					'term_c1' => preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $c1 ) ? $c1 : '#0081ff',
					'term_c2' => preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $c2 ) ? $c2 : '#222',
					'term_c3' => preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $c3 ) ? $c3 : '#31c1eb',
				);
				
				$break = '
			';
				
				if($u_f_block_bg_tc){
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .block-grid.'.$nicename.'-term-color {
				background:'.$tc[$f_block_bg_tc].'!important;
			}';
					$term_css.= '[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .description-row, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .author_name, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .like {
				color:'.$tc[$f_block_f_tc].'!important;
			}';
					
					$term_css.= '[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color a.read-more.btn {
				color:'.$tc[$f_block_bg_tc].'!important;
			}';
					$term_css.= '[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color a.read-more.btn {
				background:'.$tc[$f_block_f_tc].'!important;
			}';
				}
				
				if($u_rm_btn_f_h_tc){
					$term_css .= '[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color a.read-more.btn:hover {
				color: '.$tc[$rm_btn_f_h_tc].'!important;
			}';
				}
				
				if($u_f_btn_bg_h_tc){
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-icons .f_love-this.f_love-this:not(.alreadyvoted):hover, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-icons .f_share-btn:hover, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .pg_featured-layer .maxgrid_share:hover, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .ytb-play-btn:hover, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .ytb-pause-btn:hover {
				background:'.$tc[$f_btn_bg_h_tc].'!important;
			}';
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .f_share-this a:last-of-type > div:hover:after {
				border-color: transparent transparent transparent'.$tc[$f_btn_bg_h_tc].'!important;
			}';
				}
				
				if($u_t_title_f_tc){
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .grid-layout-the-title, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .grid-layout-the-title a, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .grid-layout-the-title a:link, .grid-layout-the-title a:visited,
			[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .list-layout-the-title a, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .list-layout-the-title a:link, .list-layout-the-title a:visited, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .pg_left-side {
				color:'.$tc[$t_title_f_tc].'!important;
			}';
										
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .maxgrid_grid_container .'.$nicename.'-term-color a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):not(.alreadyvoted), [data-g-uid="'.$unique_id.'"] .maxgrid_grid_container .'.$nicename.'-term-color a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):not(.alreadyvoted):link, [data-g-uid="'.$unique_id.'"] .maxgrid_grid_container .'.$nicename.'-term-color a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):visited {
				color: '.$tc[$f_block_f_tc].' !important;
			}';
				}
				if($u_s_stats_bg_tc ){
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid {
				background:'.$tc[$s_stats_bg_tc].'!important;
			}';
				}
				if($u_s_stats_f_tc){
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .maxgrid-sharthis, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .maxgrid-sharthis:hover, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid #share-trigger:hover > .ytb-share-btn:before, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .cover-stat-downlod:before, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .cover-stat-sales, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .cover-stat-sales:before, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .total_reviews, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .views-count, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .dl-count, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .count-grid, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .cover-stat-views, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .like, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .sales-count, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid .maxgrid_share, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .maxgrid-sharthis, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .maxgrid-sharthis:hover, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .cover-stat-downlod:before, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .cover-stat-sales, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .cover-stat-sales:before, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .total_reviews, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .views-count, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .dl-count, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .count-grid, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .cover-stat-views, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .like, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .sales-count, [data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-list .maxgrid_share {
				color:'.$tc[$s_stats_f_tc].'!important;
			}';
				}
				
				if($u_s_stats_b_tc){
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .social-share-container-grid {
				border-color:'.$tc[$s_stats_b_tc].'!important;
			}';
				}
				
				if($u_f_ovl_bg_tc){
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .pg_featured-layer.post-excerpt {
				background:'.$tc[$f_ovl_bg_tc].'!important;
			}';
				}
				
				if($u_f_ovl_f_tc){
					$term_css.= $break.'[data-g-uid="'.$unique_id.'"] .'.$nicename.'-term-color .pg_featured-layer.post-excerpt {
				color:'.$tc[$f_ovl_f_tc].'!important;
			}';
				}
			}
			
			if($u_f_block_bg_tc&&!$u_rm_btn_f_h_tc){
			$term_css .= '[data-g-uid="'.$unique_id.'"] a.read-more.btn:hover {
				text-decoration: underline!important;
			}';
			}
		}
		
		$orderby_ftr_css = $orderby_ftr == 'off' ? 'none' : 'inline-block';
		$orderby_divider_css = $orderby_ftr == 'off' || $order_ftr == 'off' && $view_ftr == 'off' ? 'none' : 'inline-block';
		$switch_btn_container_css  = $order_ftr == 'off' && $view_ftr == 'off' ? 'none' : 'inline-block';
		$term_ftr_cont_pad_css = $order_ftr == 'off' && $view_ftr == 'off' ? '0' : '7px';
		$order_ftr_css = $orderby_ftr == 'off' || $order_ftr == 'off' ? 'none' : 'inline-block';
		$order_ftr_divider_css = $orderby_ftr == 'off' || $order_ftr == 'off' || $view_ftr == 'off' || $tax_ftr == 'off' && $tag_ftr == 'off' && $view_ftr == 'off' ? 'none' : 'inline-block';
		$view_ftr_css = $view_ftr == 'off' ? 'none' : 'inline-block';
		$switch_btn_divider_css = $tax_ftr == 'off' && $tag_ftr == 'off' ? 'none' : 'inline-block';
		$term_ftr_container_css = $tax_ftr == 'off' && $tag_ftr == 'off' ? 'none' : 'inline-block';
		$tax_ftr_css = $tax_ftr == 'off' ? 'none' : 'inline-block';
		$tag_ftr_css = $tag_ftr == 'off' ? 'none' : 'inline-block';
		
		?>
		<style type="text/css">
			
			/*-------------------------------------------------------------------------*/
			/*	Import Fonts
			/*-------------------------------------------------------------------------*/	
			<?php if( !in_array($t_o['title_font_family'], $normal_fonts) && $t_o['title_font_family'] != 'Default' ) :?>
				@import url('https://fonts.googleapis.com/css?family=<?php echo $t_o['title_font_link'];?>');
			<?php endif;?>
			<?php if( !in_array($b_o['description_font_family'], $normal_fonts) && $b_o['description_font_family'] != 'Default' ) :?>
			@import url('https://fonts.googleapis.com/css?family=<?php echo $b_o['description_font_link'];?>');
			<?php endif;?>

			body .maxgrid-parent, body .maxgrid-parent span, body .maxgrid-parent div, body .maxgrid-parent table, body .maxgrid-parent ul, body .maxgrid-parent li, body .maxgrid-parent cite, body .maxgrid-parent input, body .maxgrid-parent h1, body .maxgrid-parent h2, body .post-owner-box .comment-author, #reach_content_outer h1, .pg_featured-layer.post-excerpt, .maxgrid-parent input, .maxgrid-parent button, .social-share-container-grid, .grid-layout-the-title, .block-grid, .block-grid-thumbnail, .block-list, .list-thumbnail-container, .lightbox_shareline, .single-modal-content, .single-image, .chosen-container-single .chosen-drop, .chosen-container-multi .chosen-choices, .chosen-container-single .chosen-single, a.read-more.btn {
				font-family: FontAwesome,"Roboto","Helvetica Neue",Roboto,Helvetica,Arial,sans-serif;
				font-family: FontAwesome,<?php echo $b_o['description_font_family'];?>!important;
			}
			
			<?php if( $b_o['description_font_family'] != 'Default' ) :?>
			[data-g-uid="<?php echo $unique_id;?>"] .grid-layout-row, [data-g-uid="<?php echo $unique_id;?>"] .grid-layout-row strong, [data-g-uid="<?php echo $unique_id;?>"] .grid-layout-row b {
				font-family: <?php echo $b_o['description_font_family'];?>!important;
			}
			<?php endif;?>

			<?php if( $c_o['font_family'] != 'Default' ) :?>
			@import url('https://fonts.googleapis.com/css?family=<?php echo $c_o['font_link'];?>');
			<?php endif;?>

			/*-------------------------------------------------------------------------*/
			/*	Globally Styles
			/*-------------------------------------------------------------------------*/			
			
			<?php if( $c_o['font_family'] != 'Default' ) :?>
			[data-g-uid="<?php echo $unique_id;?>"] .input-group .pg_price {
				font-family: <?php echo $c_o['font_family'];?>;
			}
			<?php endif;?>

			[data-g-uid="<?php echo $unique_id;?>"] .grid-layout-row {
				margin-right: <?php echo (!isset($b_o['grid_container'])||maxgrid_string_to_bool($b_o['grid_container'])!= 1) ? '-'. ((int)$b_o['margin_right']+1) .'px' : '0';?>!important;
			}

			/* if horizontal_fit featured */
			[data-g-uid="<?php echo $unique_id;?>"] .bloc_h_fit {				
				margin-left: -<?php echo $block_padding_left;?>px!important;
				margin-right: -<?php echo $block_padding_right;?>px!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] .categories-divider.bloc_h_fit {
				width: auto!important;
			}
			
			<?php if ( $filter!='none' ) { 
				if ( isset($f_o['swap_selectors']) && maxgrid_string_to_bool($f_o['swap_selectors']) == 1 ) {	
				?>
				[data-g-uid="<?php echo $unique_id;?>"] #post_thumbnail, [data-g-uid="<?php echo $unique_id;?>"] .video-wrapper:not(.pg_lightbox) img, [data-g-uid="<?php echo $unique_id;?>"] [data-post-type="youtube_stream"] > img, [data-g-uid="<?php echo $unique_id;?>"] .ytb-play-btn + a img {
					 -webkit-filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>);
						-moz-filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>);
						 -ms-filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>);
						  -o-filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>);
							 filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>);
				}
				[data-g-uid="<?php echo $unique_id;?>"] #post_thumbnail:hover, [data-g-uid="<?php echo $unique_id;?>"] .video-wrapper:not(.pg_lightbox):hover img, [data-g-uid="<?php echo $unique_id;?>"] [data-post-type="youtube_stream"]:hover > img, [data-g-uid="<?php echo $unique_id;?>"] .ytb-play-btn:hover + a img {
					 -webkit-filter: <?php echo $filter;?>(<?php echo '0'.$filter_measures[$filter];?>)!important;
						-moz-filter: <?php echo $filter;?>(<?php echo '0'.$filter_measures[$filter];?>)!important;
						 -ms-filter: <?php echo $filter;?>(<?php echo '0'.$filter_measures[$filter];?>)!important;
						  -o-filter: <?php echo $filter;?>(<?php echo '0'.$filter_measures[$filter];?>)!important;
							 filter: <?php echo $filter;?>(<?php echo '0'.$filter_measures[$filter];?>)!important;
				}
				<?php } else {?>

				[data-g-uid="<?php echo $unique_id;?>"] #post_thumbnail:hover, [data-g-uid="<?php echo $unique_id;?>"] .video-wrapper:not(.pg_lightbox):hover img, [data-g-uid="<?php echo $unique_id;?>"] [data-post-type="youtube_stream"]:hover > img, [data-g-uid="<?php echo $unique_id;?>"] .ytb-play-btn:hover + a img {
					 -webkit-filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>)!important;
						-moz-filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>)!important;
						 -ms-filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>)!important;
						  -o-filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>)!important;
							 filter: <?php echo $filter;?>(<?php echo $filter_val.$filter_measures[$filter];?>)!important;
				}
				<?php 
					}
			}?>
			
			/*-------------------------------------------------------------------------*/
			/*	Filter elements show / ide
			/*-------------------------------------------------------------------------*/	
					
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .medium_dropdown {
				display: <?php echo $orderby_ftr_css;?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .medium_dropdown + div {
				display: <?php echo $orderby_divider_css;?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .switch-buttons-container {
				display: <?php echo $switch_btn_container_css;?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .layout-sort-buttons {
				display: <?php echo $order_ftr_css;?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .layout-sort-buttons + div  {
				display: <?php echo $order_ftr_divider_css;?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .layout-switch-buttons {
				display: <?php echo $view_ftr_css;?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .layout-switch-buttons {
				display: <?php echo $view_ftr_css;?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .layout-switch-buttons+ div {
				display: <?php echo $switch_btn_divider_css;?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .term_filter_container {
				display: <?php echo $term_ftr_container_css;?>!important;
				padding-left: <?php echo $term_ftr_cont_pad_css;?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .term_filter_container .chosen_category + div {
				display: <?php echo $tax_ftr_css;?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter .term_filter_container .chosen_tags + div,
			[data-g-uid="<?php echo $unique_id;?>"] #epg_builder_filter #tags_divider {
				display: <?php echo $tag_ftr_css;?>!important;
			}
			
			
			[data-g-uid="<?php echo $unique_id;?>"] .star-ratings-sprite-rating:before {
				color: <?php echo $stars_color;?>!important;
			}

			[data-g-uid="<?php echo $unique_id;?>"] .description-row, [data-g-uid="<?php echo $unique_id;?>"] .list-layout-the-description, [data-g-uid="<?php echo $unique_id;?>"] .author_name, [data-g-uid="<?php echo $unique_id;?>"] .parent-description-footer-list, [data-g-uid="<?php echo $unique_id;?>"] .grid-date-comments-container, [data-g-uid="<?php echo $unique_id;?>"] .pg_left-side {
				color: <?php echo isset($b_o['description_font_color']) ? $b_o['description_font_color'] : '#595959'; ?> !important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .description-row {
				font-size: <?php echo isset($d_o['font_size']) ? $d_o['font_size'] : '11'; ?>px!important;
				line-height: <?php echo isset($d_o['line_height']) ? $d_o['line_height'] : '17'; ?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .pg_wrapper.readmore_bar {
				font-size: <?php echo isset($d_o['font_size']) ? $d_o['font_size'] : '11'; ?>px!important;
			}

			[data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):not(.alreadyvoted):not(.slide-up-add-t-cart), [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):not(.alreadyvoted):not(.slide-up-add-t-cart):link, [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):not(.slide-up-add-t-cart):visited {
				color: <?php echo $b_o['description_link_color'];?>!important;
				text-decoration: <?php if ( isset($b_o['description_link_underline']) && $b_o['description_link_underline'] == 1 ) : echo 'underline'; else : echo 'none'; endif; ?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):not(.alreadyvoted):hover, [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):not(.alreadyvoted):link:hover, [data-g-uid="<?php echo $unique_id;?>"] .post-like:hover a:not(.alreadyvoted) .like {
				color: <?php echo $desc_link_h_color; ?> !important;
				text-decoration: <?php if ( isset($b_o['description_link_h_underline']) ) : echo 'underline'; else : echo 'none'; endif; ?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a.slide-up-add-t-cart, [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a.slide-up-add-t-cart:link, [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a.slide-up-add-t-cart:visited,
			[data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer:not(.fillcover) div.slide-up_inner_content a.added_to_cart {
				color: <?php echo isset($f_o['color_overlay']) ? $f_o['color_overlay'] : '#ffffff'; ?>!important;				
				font-weight: bold!important;
			}
			
			/* Featured Overlay */
			[data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer.post-excerpt {
				background: <?php echo $bg_overlay; ?>!important;
				color: <?php echo isset($f_o['color_overlay']) ? $f_o['color_overlay'] : '#ffffff'; ?>;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] .post-links .fa {
				background:<?php echo isset($f_o['button_color']) ? $f_o['button_color'] : 'rgba(26, 38, 41, 0.8)'; ?>!important;
				color:<?php echo isset($f_o['button_font_color']) && $f_o['button_font_color'] != '' ? $f_o['button_font_color'] : '#ffffff'; ?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .post-links .fa:hover {
				background:<?php echo $featured_btn_h_color; ?>!important;
				color:<?php echo $featured_btn_font_h_color; ?>!important;
			}
			.video-wrapper .vid-duration, .ytd-video-duration {
				color: #ffffff;
			}

			/* Title */
			<?php if( $t_o['title_font_family'] != 'Default' ) :?>
			[data-g-uid="<?php echo $unique_id;?>"] .grid-layout-the-title, .list-layout-the-title {
				font-family: <?php echo $t_o['title_font_family'];?>!important;
			}
			<?php endif;?>

			[data-g-uid="<?php echo $unique_id;?>"] .grid-layout-the-title, [data-g-uid="<?php echo $unique_id;?>"] .list-layout-the-title {
				font-size: <?php echo isset($t_o['title_font_size']) ? $t_o['title_font_size'] : '14'; ?>px!important;
				line-height: <?php echo isset($t_o['title_line_height']) ? $t_o['title_line_height'] : '18'; ?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .grid-layout-the-title, [data-g-uid="<?php echo $unique_id;?>"] .grid-layout-the-title a, [data-g-uid="<?php echo $unique_id;?>"] .grid-layout-the-title a:link, .grid-layout-the-title a:visited,
			[data-g-uid="<?php echo $unique_id;?>"] .list-layout-the-title a, [data-g-uid="<?php echo $unique_id;?>"] .list-layout-the-title a:link, .list-layout-the-title a:visited {
				color: <?php echo isset($t_o['title_color']) ? $t_o['title_color'] : '#3c3c3c'; ?>!important;
				text-decoration: <?php echo isset($t_o['title_underline']) && $t_o['title_underline'] == '1' ? 'underline' : 'none'; ?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] .grid-layout-the-title a:hover, [data-g-uid="<?php echo $unique_id;?>"] .grid-layout-the-title a:link:hover,
			[data-g-uid="<?php echo $unique_id;?>"] .list-layout-the-title a:hover, [data-g-uid="<?php echo $unique_id;?>"] .list-layout-the-title a:link:hover {
				color: <?php echo $post_title_h_color; ?>!important;
				text-decoration: <?php if ( isset($t_o['title_h_underline']) ) : echo 'underline'; else : echo 'none'; endif; ?>!important;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer .dld-spinner {
				margin-left: <?php echo $padd_ratio; ?>px!important;
			}
			
			<?php
			if ( isset($t_o['nowrap']) && maxgrid_string_to_bool($t_o['nowrap']) == 1 ) {
				?>				
				[data-g-uid="<?php echo $unique_id;?>"] a.maxgrid_title {
					display: block;
					overflow: hidden !important;
					text-overflow: ellipsis;
					white-space: nowrap;
				}			
				<?php
			}
			?>
			
			/*  The excerpt bar*/
			<?php
				$footer_list_display = isset($options['post_description']) && ( $options['post_description']['categories'] == 'disabled' && $options['info_bar']['author'] == 'disabled' && $options['info_bar']['date'] == 'disabled' && $options['info_bar']['comments'] == 'disabled' ) ? 'none' : 'block';
			?>
			[data-g-uid="<?php echo $unique_id;?>"] .parent-description-footer-list, .bottom-read-more-list {
				display: <?php echo $footer_list_display;?>;
			}
			
			<?php if( $post_type == 'product') { ?>
			/* WooCommerce - Add To cart */	
			.woocommerce button.button, .woocommerce-page button.button {
				border: unset!important;
				padding: unset!important;
			}
			.woocommerce button.button {
				margin: unset!important;
				border-radius: unset!important;
			}
			.woocommerce a.added_to_cart {
				padding-top: unset!important;
			}

			body div.plus-minus-input button.ajax_add_to_cart {
			  padding: 0 10px !important;
			  margin-left: 5px!important;
			 }
			body div.plus-minus-input.rounded button.add_to_cart_button {
				border-radius: 3px!important;
			}		
			[data-g-uid="<?php echo $unique_id;?>"] .block-grid .input-group {
				margin-top: <?php echo isset($c_o['margin_top'])?$c_o['margin_top']:'0';?>px;
				margin-bottom: <?php echo isset($c_o['margin_bottom'])?$c_o['margin_bottom']:'15';?>px;
			}
			body [data-g-uid="<?php echo $unique_id;?>"] span.pg_price span.get_price {
				font-size: <?php echo isset($c_o['font_size'])?$c_o['font_size']:'35';?>px!important;
				color: <?php echo isset($c_o['price_font_color'])?$c_o['price_font_color']:'#dd3333';?>!important;
			}
			body [data-g-uid="<?php echo $unique_id;?>"] .pg_price .regular_price-sale {
				font-size: <?php echo ceil( (60 / 100) * intval($c_o['font_size']) );?>px!important;
			}

			/* Grid Add To Cart Button */
			[data-g-uid="<?php echo $unique_id;?>"] .plus-minus-input div button, [data-g-uid="<?php echo $unique_id;?>"] .plus-minus-input button.add_to_cart_button {
				background: <?php echo maxgrid_hex_to_rgb($grid_add_to_cart_bg, .92);?>!important;
				color: <?php echo maxgrid_hex_to_rgb($g_button_font_color, .88);?>!important;
				border-color: <?php echo maxgrid_hex_darker($grid_add_to_cart_bg, $darker=1.05);?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .plus-minus-input div button:hover, [data-g-uid="<?php echo $unique_id;?>"] .plus-minus-input button.add_to_cart_button:hover {
				background: <?php echo maxgrid_hex_to_rgb($grid_add_to_cart_bg, 1);?>!important;
				color: <?php echo $g_button_font_color;?>!important;
				border-color: <?php echo maxgrid_hex_darker($grid_add_to_cart_bg, $darker=1.05);?>!important;
			}
			
			/* LightBox Add To Cart Button */
			[data-g-uid="<?php echo $unique_id;?>"].maxgrid_lightbox-modal .plus-minus-input div button, [data-g-uid="<?php echo $unique_id;?>"].maxgrid_lightbox-modal .plus-minus-input button.add_to_cart_button {
				background: <?php echo maxgrid_hex_to_rgb($lightbox_add_to_cart_bg, .92);?>!important;
				color: <?php echo maxgrid_hex_to_rgb($lb_button_font_color, .88);?>!important;
				border-color: <?php echo maxgrid_hex_darker($lightbox_add_to_cart_bg, $darker=1.05);?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"].maxgrid_lightbox-modal .plus-minus-input div button:hover, [data-g-uid="<?php echo $unique_id;?>"].maxgrid_lightbox-modal .plus-minus-input button.add_to_cart_button:hover {
				background: <?php echo maxgrid_hex_to_rgb($lightbox_add_to_cart_bg, 1);?>!important;
				color: <?php echo $lb_button_font_color;?>!important;
				border-color: <?php echo maxgrid_hex_darker($lightbox_add_to_cart_bg, $darker=1.05);?>!important;
			}
			<?php } ?>
			
			[data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container.mg-grid__view.no-grid-container {
			  margin-left: -<?php echo $b_o['margin_left'];?>px;
			}
			
			/* Filter */
			.inline-select-wrapper {
				font-family:Arial, Helvetica, sans-serif!important;
			}
			.inline-select-wrapper.off {
				display: none;
			}
			.inline-select-wrapper.ytb_playlist {
				position: absolute;
				opacity: 0;
			}
			#block_filter {
				margin-top: 10px;
				margin-bottom: 10px;
				padding: 0;
			}
			#block_filter.no-grid-container {
				margin-bottom: 15px;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .block-grid-container {
				padding:5px 8px;
				padding-left: <?php echo (isset($b_o['grid_container'])&&maxgrid_string_to_bool($b_o['grid_container'])== 1) ? '8px' : '0';?>;
				padding-right: <?php echo (isset($b_o['grid_container'])&&maxgrid_string_to_bool($b_o['grid_container'])== 1) ? '8px' : '16px';?>;
				display:inline-block;
				margin: <?php echo (isset($b_o['grid_container'])&&maxgrid_string_to_bool($b_o['grid_container'])== 1) ? '5px' : '0';?> 0px;
				box-sizing: border-box;
				text-align: left;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .block-list-container {
				margin: 10px 0px 20px 0px;
				margin-left: <?php echo (isset($b_o['list_container'])&&maxgrid_string_to_bool($b_o['list_container'])== 1) ? '10px' : '0';?>;
				margin-right: <?php echo (isset($b_o['list_container'])&&maxgrid_string_to_bool($b_o['list_container'])== 1) ? '10px' : '0';?>;
				border: 1px solid #e0e0e0;
				margin-top: 20px !important;
			}

			/* chosen */
			#chosen_orderby_chosen {
				position: relative;
				top: -3px;
			}
			.chosen-container.chosen-container-multi {
				position: relative;
				top: -3px;
			}
			.chosen-container-single .chosen-single {
				border: 1px solid #d2cece;
				border-radius: 1px;
				background-color: #fff;
				background: linear-gradient(#fff 20%, #f6f6f6 50%, #eee 52%, #f4f4f4 100%);
				box-shadow: none;
				font-size: 12px;
			}
			.chosen-container-single .chosen-drop {
				border-radius: 0 0 1px 1px;
			}
			.chosen-container .chosen-results li.highlighted {
				background-color: <?php echo $extra_color_1; ?>;
				background-image: linear-gradient(<?php echo $extra_color_1; ?> 20%, <?php echo maxgrid_hex_darker($extra_color_1, $darker=1.1); ?> 90%);
				text-shadow: 1px 1px rgba(0,0,0,.2);
			}
			input.chosen-search-input.default {
				font-size: 12px!important;
			}
			.show-all-cat-btn {
				display: inline-block;
				position: relative;
				height: 29px;
				line-height: 29px;
				padding: 0 10px;
				box-shadow: none;
			}
			
			/* youtube iframe border corection */
			.video-wrapper {
				overflow: hidden!important;
			}
			.video-wrapper:not(.sd_player) iframe {
				transform: scale(1.002)!important;
			}
			
			/* Read More Button */
			.grid-layout-the-description {
				padding-bottom: 10px;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] div.video-wrapper div.post-title {
				<?php if( !$fit_to_ovl ) { ?>				
					padding-left: <?php echo $t_l_padd; ?>px!important;
					padding-right: <?php echo $t_r_padd; ?>px!important;
				<?php }?>
			}
			[data-g-uid="<?php echo $unique_id;?>"] div.video-wrapper div.excerpt-content {
				<?php if( !$fit_to_ovl ) { ?>				
					padding-left: <?php echo $b_l_padd; ?>px!important;
					padding-right: <?php echo $b_r_padd; ?>px!important;
				<?php }?>				
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] .post-excerpt.fillcover div.post-category:not(.inside-slide-up) {
				margin-left: <?php echo $b_l_padd; ?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .post-excerpt.no-layer div.post-category {				
				margin-left: <?php echo $ovl_p_l; ?>px!important;
			}
			<?php if( !$fit_to_ovl ) { ?>
			[data-g-uid="<?php echo $unique_id;?>"] .post-excerpt div.post-category.inside-slide-up {				
					padding-left: <?php echo $b_l_padd; ?>px!important;				
			}
			<?php }?>
			[data-g-uid="<?php echo $unique_id;?>"] div.video-wrapper div.post-stats {
				width: calc(100% - <?php echo isset($fillcover) ? ((int)$excerpt_l_padd+(int)$excerpt_r_padd): 0; ?>px)!important;
				<?php if( !$fit_to_ovl ) { ?>
					padding-left: <?php echo $b_l_padd; ?>px!important;
					padding-right: <?php echo isset($fillcover) ? (int)$b_r_padd+$time_padd : $b_r_padd; ?>px!important;
				<?php }?>
				
				bottom: <?php echo $ovl_p_b; ?>px!important;
				
			}
			<?php if($post_stats){?>
			[data-g-uid="<?php echo $unique_id;?>"] div.video-wrapper .slide-up_inner_content div.post-stats {
				padding-top: 5px;
				height: auto;
				border-top: 1px solid <?php echo maxgrid_hex_to_rgb(maxgrid_light_or_dark(maxgrid_rgb_to_hex($bg_overlay)), 0.1)?>;
			}
			<?php }?>
			
			[data-g-uid="<?php echo $unique_id;?>"] [data-total-row="1"] .ytb-pause-btn {
				margin-top: <?php echo $ytb_pause_marg_prop; ?>px!important;
				margin-right: <?php echo $ytb_pause_marg_prop; ?>px!important;
			}		
			.video-wrapper {
			  	position: relative;
			  	margin: 0;
			}
			.video-wrapper.pg_lightbox:not(.sd_player):not(.audio_player) {
			  	padding-bottom: 56.25%;
			  	height: 0;
				background: #e0e0e0;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .block-grid-container .video-wrapper {
				padding-bottom: 0;
				height: auto;
				border-left-width: <?php echo isset($f_o['border_left_width']) && $f_o['border_left_width'] != '' ? $f_o['border_left_width'] : 0; ?>px;
				border-top-width: <?php echo isset($f_o['border_top_width']) && $f_o['border_top_width'] != '' ? $f_o['border_top_width'] : 0; ?>px;
				border-right-width: <?php echo isset($f_o['border_right_width']) && $f_o['border_right_width'] != '' ? $f_o['border_right_width'] : 0; ?>px;
				border-bottom-width: <?php echo isset($f_o['border_bottom_width']) && $f_o['border_bottom_width'] != '' ? $f_o['border_bottom_width'] : 0; ?>px;
				border-top-left-radius: <?php echo isset($f_o['border_top_left_radius']) && $f_o['border_top_left_radius'] != '' ? $f_o['border_top_left_radius'] : 0; ?>px;
				border-top-right-radius: <?php echo isset($f_o['border_top_right_radius']) && $f_o['border_top_right_radius'] != '' ? $f_o['border_top_right_radius'] : 0; ?>px;
				border-bottom-left-radius: <?php echo isset($f_o['border_bottom_left_radius']) && $f_o['border_bottom_left_radius'] != '' ? $f_o['border_bottom_left_radius'] : 0; ?>px;
				border-bottom-right-radius: <?php echo isset($f_o['border_bottom_right_radius']) && $f_o['border_bottom_right_radius'] != '' ? $f_o['border_bottom_right_radius'] : 0; ?>px;
				margin-top: <?php echo isset($f_o['margin_top']) && $f_o['margin_top'] != '' ? $f_o['margin_top'] : 0; ?>px!important;
				margin-bottom: <?php echo isset($f_o['margin_bottom']) && $f_o['margin_bottom'] != '' ? $f_o['margin_bottom'] : 0; ?>px!important;
				border-style: <?php echo isset($f_o['border_style']) ? $f_o['border_style'] : 'solid'; ?>;
				border-color: <?php echo isset($f_o['border_color']) ? $f_o['border_color'] : 'rgba(0,0,0,.07)'; ?>;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer.social-icons {
				margin-top: <?php echo $social_icons_margin; ?>px!important;
				margin-right: <?php echo $social_icons_margin; ?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .ytb-pause-btn {
				margin-top: <?php echo $ytb_pause_margin; ?>px!important;
				margin-right: <?php echo $ytb_pause_margin; ?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer.post-excerpt:not(.no-layer) {
				<?php if( !$fit_to_ovl ) { ?>
					padding-left: <?php echo $ovl_p_l; ?>px!important;
					padding-right: <?php echo $ovl_p_r; ?>px!important;
				<?php }?>
				padding-top: <?php echo $ovl_p_t; ?>px!important;
				padding-bottom: <?php echo $ovl_p_b; ?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .video-wrapper .vid-duration, .ytd-video-duration {
				margin-right: <?php echo isset($fillcover) ? (int)$vid_duration_margin+$padd_ratio : $ovl_p_r;?>px!important;
				margin-bottom: <?php echo isset($fillcover) ? (int)$vid_duration_margin+$padd_ratio : $ovl_p_b;?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] div.video-wrapper div.get_price {
				margin-right: <?php echo $ovl_p_r;?>px!important;
				margin-bottom: <?php echo $ovl_p_b; ?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .post-excerpt div.post-category:not(.inside-slide-up) {
				<?php if( $fit_to_ovl ) { ?>
					margin-left: <?php echo $ovl_p_l; ?>px!important;
				<?php }?>
			}
			[data-g-uid="<?php echo $unique_id;?>"] .post-excerpt #maxgrid-button-style_container {
				<?php if( $fit_to_ovl ) { ?>
					margin-left: <?php echo $ovl_p_l; ?>px!important;
				<?php }?>
			}			
			.video-wrapper:not(.sd_player) iframe {
			  position: absolute;
			  top: 0;
			  left: 0;
			  width: 100%;
			  height: 100%;
			}			
			[data-g-uid="<?php echo $unique_id;?>"] .social-icons .f_love-this:not(.alreadyvoted), [data-g-uid="<?php echo $unique_id;?>"] .social-icons .f_share-btn, [data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer .maxgrid_share, [data-g-uid="<?php echo $unique_id;?>"] .ytb-play-btn,	[data-g-uid="<?php echo $unique_id;?>"] .ytb-pause-btn {
				background: <?php echo isset($f_o['button_color']) ? $f_o['button_color'] : 'rgba(26, 38, 41, 0.8)'; ?>;
				color:<?php echo isset($f_o['button_font_color']) && $f_o['button_font_color']!='' ? $f_o['button_font_color'] : '#ffffff'; ?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .social-icons .f_love-this.f_love-this:not(.alreadyvoted):hover, [data-g-uid="<?php echo $unique_id;?>"] .social-icons .f_share-btn:hover, [data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer .maxgrid_share:hover, [data-g-uid="<?php echo $unique_id;?>"] .ytb-play-btn:hover,	[data-g-uid="<?php echo $unique_id;?>"] .ytb-pause-btn:hover {
				background: <?php echo $featured_btn_h_color; ?>;
				color:<?php echo $featured_btn_font_h_color; ?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .f_share-this a:last-of-type > div:after {
				border-left-color: <?php echo isset($f_o['button_color']) ? $f_o['button_color'] : 'rgba(26, 38, 41, 0.8)'; ?>;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .f_share-this a:last-of-type > div:hover:after {
				border-color: transparent transparent transparent <?php echo $featured_btn_h_color; ?>;
			}

			/* mp4 video player*/
			#my-video {
				width: 100%;
				height: auto;

			}
			
			.video-wrapper:not(.sd_player) iframe {
				opacity: 1!important;
			}
			.video-wrapper video, audio {
				visibility: visible!important;
			}
			.disabledbutton {
				pointer-events: none;
				opacity: 0.4;
			}

			/* Download Style*/
			
			[data-g-uid="<?php echo $unique_id;?>"] #maxgrid-button-style_container.in-block {				
				margin-top: <?php echo $dld_btn_marg_top;?>px!important;
				margin-bottom: <?php echo $dld_btn_marg_bot;?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .in-block .maxgrid-button.maxgrid-error {
				background-color: <?php echo $dld_btn_color_theme;?>!important;
				color: <?php echo $dld_btn_font_color;?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .maxgrid-button.maxgrid-error:hover {
				background-color: <?php echo maxgrid_colourBrightness($dld_btn_color_theme, -0.90);?>!important;
			}
			
			/* Featured Download Style*/
			[data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer .maxgrid-button.maxgrid-error {
				background-color: <?php echo $dld_b_bg_c;?>!important;
				color: <?php echo $dld_b_f_c;?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer.no-layer .maxgrid-button.maxgrid-error {
				margin-left: <?php echo $ovl_p_l ;?>px!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .pg_featured-layer .maxgrid-button.maxgrid-error:hover {
				background-color: <?php echo maxgrid_colourBrightness($dld_b_bg_c, -0.90);?>!important;
			}			
			
		<?php if( $post_type != 'youtube_stream') { ?>
			/* WP 3.6 Native Audio Player styling*/
			[data-g-uid="<?php echo $unique_id;?>"] .maxgrid-audio-player-row {
				margin-top: <?php echo isset($a_p['margin_top'])?$a_p['margin_top']:'10';?>px;
				margin-bottom: <?php echo isset($a_p['margin_bottom'])?$a_p['margin_bottom']:'10';?>px;
			}

			/* change the color of the background */
			body .mejs-container.mejs-audio {
				background: transparent!important; background-color: transparent!important
			}
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-controls,
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-mediaelement,
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-container {
				<?php echo isset($a_p['bg_image']) && $a_p['bg_image'] !== '' ?
				'background: url('.maxgrid_url_encode($a_p['bg_image']).')!important':'';?>;
				background-color: <?php echo isset($a_p['bg_color'])?$a_p['bg_color']:'#464646';?>!important;
				border-top-left-radius: <?php echo isset($a_p['border_top_left_radius']) && $a_p['border_top_left_radius'] != '' ?$a_p['border_top_left_radius']:'3';?>px!important;
				border-top-right-radius: <?php echo isset($a_p['border_top_right_radius']) && $a_p['border_top_right_radius'] != ''?$a_p['border_top_right_radius']:'3';?>px!important;
				border-bottom-left-radius: <?php echo isset($a_p['border_bottom_left_radius']) && $a_p['border_bottom_left_radius'] != ''?$a_p['border_bottom_left_radius']:'3';?>px!important;
				border-bottom-right-radius: <?php echo isset($a_p['border_bottom_right_radius']) && $a_p['border_bottom_right_radius'] != ''?$a_p['border_bottom_right_radius']:'3';?>px!important;
			}

			/* change the color of the current horizontal volume */
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-controls .mejs-time-rail .mejs-time-current,
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-current {
				background-color: <?php echo $front_track_color;?> !important;
				<?php if(isset($a_p['use_gradient']) && maxgrid_string_to_bool($a_p['use_gradient']) == 1){
					$gra_color_1 = isset($a_p['track_grad_c1']) ? $a_p['track_grad_c1'] : '#ad73f8';
					$gra_color_2 = isset($a_p['track_grad_c2']) ? $a_p['track_grad_c2'] : '#fc496b';
					?>
					background-color: <?php echo $gra_color_1;?> !important;
					background-image: -webkit-linear-gradient(left, <?php echo $gra_color_1;?>, <?php echo $gra_color_2;?>) !important;
					background-image: -moz-linear-gradient(left, <?php echo $gra_color_1;?>, <?php echo $gra_color_2;?>) !important;
					background-image: -o-linear-gradient(left, <?php echo $gra_color_1;?>, <?php echo $gra_color_2;?>) !important;
					background-image: linear-gradient(to right, <?php echo $gra_color_1;?>, <?php echo $gra_color_2;?>) !important;
				<?php }?>
			}				
			
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-time-handle-content {
				border: 4px solid <?php echo isset($a_p['front_track_color'])?maxgrid_hex_to_rgb($front_track_color, 0.8 ) : 'rgba(49,193,235,0.8)';?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-time-hovered {
				background: <?php echo isset($a_p['front_track_color'])?maxgrid_hex_to_rgb($front_track_color, 1 ): 'rgba(49,193,235, 1)';?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-controls .mejs-time-rail .mejs-time-total {
				background-color: <?php echo isset($a_p['back_track_color'])?maxgrid_hex_to_rgb($back_track_color, 0.7 ) : 'rgba(255,255,255, 0.7)';?>!important;
				<?php if(isset($a_p['apply_shadows']) && maxgrid_string_to_bool($a_p['apply_shadows']) == 1){?>
					-webkit-box-shadow: 0px 1px 1px rgba(0,0,0,.15);
					-moz-box-shadow: 0px 1px 1px rgba(0,0,0,.15);
					box-shadow: 0px 1px 1px rgba(0,0,0,.15);
				<?php }?>
			}
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-controls .mejs-time-rail .mejs-time-loaded {
				background-color: <?php echo isset($a_p['back_track_color'])?maxgrid_hex_to_rgb($back_track_color, 0.6 ) : 'rgba(255,255,255, 0.7)';?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-total {
				background: <?php echo isset($a_p['back_track_color'])?maxgrid_hex_to_rgb($back_track_color, 0.7 ): 'rgba(255,255,255, 0.7)';?>!important;
				<?php if(isset($a_p['apply_shadows']) && maxgrid_string_to_bool($a_p['apply_shadows']) == 1){?>
					-webkit-box-shadow: 0px 1px 1px rgba(0,0,0,.15);
					-moz-box-shadow: 0px 1px 1px rgba(0,0,0,.15);
					box-shadow: 0px 1px 1px rgba(0,0,0,.15);
				<?php }?>
			}

			/* change the color of the lettering */
			body [data-g-uid="<?php echo $unique_id;?>"] .mejs-controls .mejs-button button,
			.mejs-currenttime,
			.mejs-duration {
				color: <?php echo isset($a_p['font_color'])?$a_p['font_color']:'#d2d2d2';?>!important;
				<?php if(isset($a_p['apply_shadows']) && maxgrid_string_to_bool($a_p['apply_shadows']) == 1){?>
					text-shadow: 0px 1px 0px rgba(0,0,0, .3);
				<?php }?>
			}

			/* Pause Button */
			body [data-g-uid="<?php echo $unique_id;?>"] .mejs-controls .mejs-pause button {
				border-left: 3px solid <?php echo isset($a_p['font_color'])?$a_p['font_color']:'#ffffff';?>!important;
				border-right: 3px solid <?php echo isset($a_p['font_color'])?$a_p['font_color']:'#ffffff';?>!important;
			}

			/* eliminate the yellow border around the play button during playback */
			.mejs-controls .mejs-button button:focus {
				outline: none !important;
			}
			body .mejs-controls .mejs-replay button:before {
				content: "\e052"!important;
			}
	<?php } ?>
	<?php if( $ribbon == 'on' && $post_type != 'youtube_stream') { ?>			
			/* Ribbon Color
			 *
			 * 1 - Newest
			 * 2 - Most Viewed
			 * 3 - Most Liked
			 * 4 - Most Downloaded
			 *
			*/
			
			[data-g-uid="<?php echo $unique_id;?>"] .corner-ribbon {
				transform: scale(<?php echo $ribbon_size;?>);
			} 
			/* 1 - Newest*/
			[data-g-uid="<?php echo $unique_id;?>"] .newest span {background: <?php echo $newest_color;?>; background-color: <?php echo $newest_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .newest span::before {border-left-color: <?php echo $newest_color_2;?>; border-top-color: <?php echo $newest_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .newest span::after {border-right-color: <?php echo $newest_color_2;?>; border-top-color: <?php echo $newest_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .newest span .fa {font-size: 11px!important; margin-left: -3px;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.newest {background: <?php echo $newest_color;?>; background-color: <?php echo $newest_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.left.newest:before {border-right-color: <?php echo $newest_color_2;?>; border-top-color: <?php echo $newest_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.right.newest:before {border-left-color: <?php echo $newest_color_2;?>; border-top-color: <?php echo $newest_color_2;?>;}

			/* 2 - Most Viewed */
			[data-g-uid="<?php echo $unique_id;?>"] .views span {background: <?php echo $views_color;?>; background-color: <?php echo $views_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .views span::before {border-left-color: <?php echo $views_color_2;?>; border-top-color: <?php echo $views_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .views span::after {border-right-color: <?php echo $views_color_2;?>; border-top-color: <?php echo $views_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .views span .fa {font-size: 12px!important}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.views {background: <?php echo $views_color;?>; background-color: <?php echo $views_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.left.views:before {border-right-color: <?php echo $views_color_2;?>; border-top-color: <?php echo $views_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.right.views:before {border-left-color: <?php echo $views_color_2;?>; border-top-color: <?php echo $views_color_2;?>;}

			/* 3 - Most Liked */
			[data-g-uid="<?php echo $unique_id;?>"] .liked span {background: <?php echo $liked_color;?>; background-color: <?php echo $liked_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .liked span::before {border-left-color: <?php echo $liked_color_2;?>; border-top-color: <?php echo $liked_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .liked span::after {border-right-color: <?php echo $liked_color_2;?>; border-top-color: <?php echo $liked_color_2;?>;}		
			[data-g-uid="<?php echo $unique_id;?>"] .liked span .fa {font-size: 11px!important; margin-left: 0px;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.liked {background: <?php echo $liked_color;?>; background-color: <?php echo $liked_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.left.liked:before {border-right-color: <?php echo $liked_color_2;?>; border-top-color: <?php echo $liked_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.right.liked:before {border-left-color: <?php echo $liked_color_2;?>; border-top-color: <?php echo $liked_color_2;?>;}

			/* 3 - Most Downloaded */
			[data-g-uid="<?php echo $unique_id;?>"] .downloaded span {background: <?php echo $downloaded_color;?>; background-color: <?php echo $downloaded_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .downloaded span::before {border-left-color: <?php echo $downloaded_color_2;?>; border-top-color: <?php echo $downloaded_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .downloaded span::after {border-right-color: <?php echo $downloaded_color_2;?>; border-top-color: <?php echo $downloaded_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.downloaded {background: <?php echo $downloaded_color;?>; background-color: <?php echo $downloaded_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.left.downloaded:before {border-right-color: <?php echo $downloaded_color_2;?>; border-top-color: <?php echo $downloaded_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.right.downloaded:before {border-left-color: <?php echo $downloaded_color_2;?>; border-top-color: <?php echo $downloaded_color_2;?>;}

			/* On Sale */
			[data-g-uid="<?php echo $unique_id;?>"] .onsale span {background: <?php echo $onsale_color;?>; background-color: <?php echo $onsale_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .onsale span::before {border-left-color: <?php echo $onsale_color_2;?>; border-top-color: <?php echo $onsale_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .onsale span::after {border-right-color: <?php echo $onsale_color_2;?>; border-top-color: <?php echo $onsale_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .onsale span {font-size: 14px!important}
			[data-g-uid="<?php echo $unique_id;?>"] .onsale span .fa {font-size: 15px!important; margin-left: -1px;}
			[data-g-uid="<?php echo $unique_id;?>"] .corner-ribbon.onsale span .fa {margin-top: -11px;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.onsale {background: <?php echo $onsale_color;?>; background-color: <?php echo $onsale_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.left.onsale:before {border-right-color: <?php echo $onsale_color_2;?>; border-top-color: <?php echo $onsale_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.right.onsale:before {border-left-color: <?php echo $onsale_color_2;?>; border-top-color: <?php echo $onsale_color_2;?>;}

			/* Best Seller */
			[data-g-uid="<?php echo $unique_id;?>"] .bestseller span {background: <?php echo $bestseller_color;?>; background-color: <?php echo $bestseller_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .bestseller span::before {border-left-color: <?php echo $bestseller_color_2;?>; border-top-color: <?php echo $bestseller_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .bestseller span::after {border-right-color: <?php echo $bestseller_color_2;?>; border-top-color: <?php echo $bestseller_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .bestseller span {font-size: 14px!important}
			[data-g-uid="<?php echo $unique_id;?>"] .bestseller span .fa {font-size: 15px!important; margin-left: -1px;}
			[data-g-uid="<?php echo $unique_id;?>"] .corner-ribbon.bestseller span .fa {margin-top: -11px;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.bestseller {background: <?php echo $bestseller_color;?>; background-color: <?php echo $bestseller_color;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.left.bestseller:before {border-right-color: <?php echo $bestseller_color_2;?>; border-top-color: <?php echo $bestseller_color_2;?>;}
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon.right.bestseller:before {border-left-color: <?php echo $bestseller_color_2;?>; border-top-color: <?php echo $bestseller_color_2;?>;}

			<?php } ?>
			
			/* Masonry Layout Style */
			[data-g-uid="<?php echo $unique_id;?>"] .grid-layout-the-title { 
				/*min-height: <?php echo $title_min_height;?>;*/
				overflow: <?php echo $title_overflow;?>;
				max-height: <?php echo $title_min_height;?>; 
				position: <?php echo $title_position;?>; 
			}
			[data-g-uid="<?php echo $unique_id;?>"] .grid-layout-the-description {
				min-height: <?php echo $description_min_height;?>;
			}
			
			/* Link */
			[data-g-uid="<?php echo $unique_id;?>"] #maxgrid_table table a:hover, [data-g-uid="<?php echo $unique_id;?>"] #maxgrid_table table a:visited, [data-g-uid="<?php echo $unique_id;?>"] #maxgrid_table table a:link, [data-g-uid="<?php echo $unique_id;?>"] .parent-categories a:hover, [data-g-uid="<?php echo $unique_id;?>"] .parent-categories a:visited, [data-g-uid="<?php echo $unique_id;?>"] .parent-categories a:link {
				color: <?php echo $extra_color_1;?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .grid-layout-heider-the-count a:hover {
				color: <?php echo $extra_color_1;?>!important;
			}
			#maxgrid_table table th a {
				color:#666!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .list-view:hover, [data-g-uid="<?php echo $unique_id;?>"] .grid-view:hover, [data-g-uid="<?php echo $unique_id;?>"] .desc-sort:hover, [data-g-uid="<?php echo $unique_id;?>"] .asc-sort:hover, [data-g-uid="<?php echo $unique_id;?>"] input[type=radio]:hover {
				color: <?php echo $extra_color_1;?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] input[type=radio]:checked + .list-view, [data-g-uid="<?php echo $unique_id;?>"] input[type=radio]:checked + .grid-view, [data-g-uid="<?php echo $unique_id;?>"] input[type=radio]:checked + .desc-sort, [data-g-uid="<?php echo $unique_id;?>"] input[type=radio]:checked + .asc-sort, [data-g-uid="<?php echo $unique_id;?>"] input[type=radio]:checked + label::before { 
			  color: <?php echo $extra_color_1;?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .dropdown li.focus{
				background: <?php echo $light_color_theme;?>;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .dropdown li.active:before{
				color: <?php echo $extra_color_1;?>!important;
			}
			
			/* ultraselect */
			[data-g-uid="<?php echo $unique_id;?>"] .ultraselect .options .selectable.hover {
				background-color: <?php echo $light_color_theme;?>;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .ultraselect .options .selectable.checked {
				background-color: <?php echo $mid_color_theme;?>;
			}
			
			/* Animation */
			.block-grid, .block-list-container, a.read-more.btn {
				-webkit-transition: all .2s ease-in-out; 
			   -moz-transition: all .2s ease-in-out;  
			   -o-transition: all .2s ease-in-out; 
				transition: all .2s ease-in-out; 
			}
			[data-g-uid="<?php echo $unique_id;?>"] .block-grid.shadow, [data-g-uid="<?php echo $unique_id;?>"] .block-list-container.shadow {
				-webkit-box-shadow: 0 0 <?php echo $shadow_blur_radius;?>px 0 rgba(0,0,0,<?php echo $shadow_opacity/100;?>);	
				   -moz-box-shadow: 0 0 <?php echo $shadow_blur_radius;?>px 0 rgba(0,0,0,<?php echo $shadow_opacity/100;?>);
						box-shadow: 0 0 <?php echo $shadow_blur_radius;?>px 0 rgba(0,0,0,<?php echo $shadow_opacity/100;?>);
			}
			[data-g-uid="<?php echo $unique_id;?>"] .block-grid.h_shadow:hover, [data-g-uid="<?php echo $unique_id;?>"] .block-list-container.h_shadow:hover {
				-webkit-box-shadow: 0 0 <?php echo $shadow_blur_radius;?>px 0 rgba(0,0,0,<?php echo $shadow_opacity/100;?>);	
				   -moz-box-shadow: 0 0 <?php echo $shadow_blur_radius;?>px 0 rgba(0,0,0,<?php echo $shadow_opacity/100;?>);
						box-shadow: 0 0 <?php echo $shadow_blur_radius;?>px 0 rgba(0,0,0,<?php echo $shadow_opacity/100;?>);
			}
			[data-g-uid="<?php echo $unique_id;?>"] a.read-more.btn {
				background: <?php echo $rm_btn_bg_color;?>!important;
				color: <?php echo $rm_btn_f_color;?>!important;
			}
			[data-g-uid="<?php echo $unique_id;?>"] a.read-more.btn:hover {
				background: <?php echo $rm_btn_bg_h_color;?>!important;
				color: <?php echo $rm_btn_f_h_color;?>!important;
			}

			<?php
		
			// Echo Grid Blocks Styles	
			$search_arr = array(
			  '.block-' => '[data-g-uid="'.$unique_id.'"] .block-',
			  '.social-share-container' => '[data-g-uid="'.$unique_id.'"] .social-share-container', 
			  '.corner-ribbon' => '[data-g-uid="'.$unique_id.'"] .corner-ribbon',
			);
			$design_options_css = str_replace(
			  array_keys($search_arr), 
			  array_values($search_arr), 
			 $get_presets->rows('design_options_css', $not_array=true)
			);
		
			echo $design_options_css;
		
			if ( isset($f_o['fit_width']) && maxgrid_string_to_bool($f_o['fit_width']) == 1 ) {
				?>
				.social-share-container-grid {
					margin-left: 0px;
					margin-right: 0px;
				}
				.block-grid {
					padding-left: 0!important;
					padding-right: 0!important;
				}
				<?php
			} else {
				?>
				[data-g-uid="<?php echo $unique_id;?>"] .social-share-container-grid {
					margin-left: -<?php echo $block_padding_left != '' ? $block_padding_left : '0';?>px;
					margin-right: -<?php echo $block_padding_right != '' ? $block_padding_right : '0';?>px;
				}
				<?php
			}

			// Date Bar CSS			
			echo isset($options['info_bar']['datebar_css']) ? $options['info_bar']['datebar_css'] : '';
		
			if ( isset($i_o['enable_extras']) && $i_o['enable_extras'] == 1 ) {
				?>
				[data-g-uid="<?php echo $unique_id;?>"] .info_row-container {
					background: <?php echo isset($i_o['background_color']) && $i_o['background_color'] !== '' ? $i_o['background_color'] : '#ffffff';?>!important;
					color: <?php echo isset($i_o['text_color']) && $i_o['text_color'] !== '' ? $i_o['text_color'] : '#595959';?>!important;
				}
				[data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style), [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):not(.alreadyvoted):link, [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):visited {
					color: <?php echo isset($i_o['text_color']) && $i_o['text_color'] !== '' ? $i_o['text_color'] : '#5f5d5d';?>!important;
					text-decoration: <?php echo isset($i_o['text_underline']) && $i_o['text_underline'] !== 1 ? 'underline' : 'none';?>!important;
				}

				[data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container .info_row-container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):hover, [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container .info_row-container a:not(.btn):not(.maxgrid_title):not(.inset-theme-style):link:hover {
					color: <?php echo $info_text_h_color;?>!important;
					text-decoration: <?php echo isset($i_o['text_h_underline']) && $i_o['text_h_underline'] !== 1 ? 'underline' : 'none';?>!important;
				}
			<?php
			}

			if ( isset($d_o['enable_extras']) && $d_o['enable_extras'] == 1 ) {
				?>
				[data-g-uid="<?php echo $unique_id;?>"] .description-row, [data-g-uid="<?php echo $unique_id;?>"] .pg_wrapper.readmore_bar {
					background: <?php echo isset($d_o['background_color']) && $d_o['background_color'] !== '' ? $d_o['background_color'] : '#ffffff';?>!important;
					color: <?php echo isset($d_o['text_color']) && $d_o['text_color'] !== '' ? $d_o['text_color'] : '#595959';?>!important;
				}
				[data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a#catnamepost, [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a#catnamepost:link, [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a#catnamepost:visited {
					color: <?php echo isset($d_o['text_color']) && $d_o['text_color'] !== '' ? $d_o['text_color'] : '#595959';?>!important;
					text-decoration: <?php echo isset($d_o['text_underline']) && $d_o['text_underline'] !== 1 ? 'underline' : 'none';?>!important;
				}
				[data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a#catnamepost:hover, [data-g-uid="<?php echo $unique_id;?>"] .maxgrid_grid_container a#catnamepost:link:hover {
					color: <?php echo $desc_text_h_color;?>!important;
					text-decoration: <?php echo isset($d_o['text_h_underline']) && $d_o['text_h_underline'] !== 1 ? 'underline' : 'none';?>!important;
				}
			<?php
			}
			$stats_bar_names = array(
				'youtube_stream' => 'ytb_vid_stats_bar',
				'product' 		 => 'woo_stats_bar',
				'post' 			 => 'stats_bar',
				'download' 		 => 'stats_bar',
				);
		
			// Post Stats Bar CSS
			$stats_bar = isset($stats_bar_names[$post_type]) ? $stats_bar_names[$post_type] : $stats_bar_names['post'];			
			$get_statsbar_css = isset($options[$stats_bar]['statsbar_css']) ? $options[$stats_bar]['statsbar_css'] : '';;
			$search_arr = array(
			  '.social-share-container-grid' => '[data-g-uid="'.$unique_id.'"] .social-share-container-grid', 
			  '.social-share-container-list' => '[data-g-uid="'.$unique_id.'"] .social-share-container-list',
			);
			$statsbar_css= str_replace(
			  array_keys($search_arr), 
			  array_values($search_arr), 
			 $get_statsbar_css 
			);
		
			echo $statsbar_css;
		
			if ($full_width_page == 'on'){
			?>		
			#sidebar, .sidebar, .entry-header, #main-sidebar {display: none;}
			#post-area, #main, .rsrc-main, .content-area, #primary .entry-content {
				float: unset!important;
				display: block!important;
				width: 100%!important;
			}
			<?php } ?>
			[data-g-uid="<?php echo $unique_id;?>"] .flat-ribbon {
				margin-top: <?php echo isset($b_o['ribbon_top_pos']) ? $b_o['ribbon_top_pos'] : '30';?>px;
			}
			
			[data-g-uid="<?php echo $unique_id;?>"] .corner-ribbon.list.wrapped.left {
				left: -<?php echo isset($b_o['list_padding_left']) ? (int)$b_o['list_padding_left']+7 : 17;?>px;
				top: -<?php echo isset($b_o['list_padding_top']) ? (int)$b_o['list_padding_top']+8 : 18;?>px;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .corner-ribbon.list.left {
				left: -<?php echo isset($b_o['list_padding_left']) ? $b_o['list_padding_left'] : 10;?>px;
				top: -<?php echo isset($b_o['list_padding_top']) ? (int)$b_o['list_padding_top'] : 10;?>px;
			}
			[data-g-uid="<?php echo $unique_id;?>"] .corner-ribbon.wrapped.left {
				left: -7px;
				top: -8px;
			}
			/* Term Color CSS */
			<?php echo $term_css;?>
			</style>
	<?php
	}

	/**
	 * Admin head
	 *
	 * calls functions into the correct filters
	 *
	 * @return void
	 */
	public function admin_head() {				
		// check user permissions
		if( !maxgrid()->is_edit_page()){
			return;
		}
		// check if WYSIWYG is enabled
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			if ( is_admin()){
				add_filter( 'mce_external_plugins', array( $this ,'mce_external_plugins' ) );
			}
			add_filter( 'mce_buttons', array($this, 'mce_buttons' ) );
		}
	}

	/**
	 * MCE external plugins.
	 *
	 * Adds tinymce plugin
	 * @param  array $plugin_array
	 *
	 * @return array
	 */
	public function mce_external_plugins( $plugin_array ) {
		if ( get_post_type() != 'page'){
			return $plugin_array;
		}
		$plugin_array[$this->shortcode_tag] = plugins_url( 'js/mce-button.js' , __FILE__ );
		return $plugin_array;
	}

	/**
	 * MCE buttons.
	 *
	 * Adds tinymce button
	 * @param  array $buttons
	 *
	 * @return array
	 */
	public function mce_buttons( $buttons ) {
		array_push( $buttons, $this->shortcode_tag );
		return $buttons;
	}
	
	/**
	 * Get recent post thumbnail and the total number of posts found.
	 *
	 * @return string
	 */
	public function get_recent_post_thumbnail() {
		$ptype = array('post');
		
		if ( is_maxgrid_download_activated() ) {
		  array_push($ptype,'download');
		}
		
		if ( is_woocommerce_activated() ) {
		  array_push($ptype,'product');
		}
		
		$data = '';	
		foreach($ptype as $value){
			$args  = array( 'post_type' => $value );						
			$data .= isset(wp_get_recent_posts($args)[0]) ? wp_get_attachment_image_src(get_post_thumbnail_id(isset(wp_get_recent_posts($args)[0])), 'single-post-thumbnail' )[0].',' : '';
		}
		$i = 0;
		foreach($ptype as $value) {						
			$data .= wp_count_posts($value)->publish;
			if(++$i !== 3) {
				$data .= ',';
			}
		}
		echo $data;
		die();
	}
	
	/**
	 * Admin enqueue scripts.
	 * 
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( get_post_type() != 'page' ) {
			return;
		}		
		wp_register_style( 'maxgrid-builder-dashboard', MAXGRID_ABSURL . '/assets/css/dashboard.css' );
		wp_register_style( 'maxgrid-builder-mce-button', plugins_url( 'css/mce-button.css' , __FILE__ ) );
		wp_register_style( 'maxgrid-builder-ajax-spinner', MAXGRID_ABSURL . '/assets/css/ajax-spinner.css' );
		wp_register_style( 'maxgrid-builder-admin', MAXGRID_ABSURL . '/includes/css/admin.css' );
		wp_register_style( 'maxgrid-builder-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
		wp_register_style( 'maxgrid-builder-icomoon', MAXGRID_ABSURL . '/assets/lib/icomoon/style.css' );
		wp_register_style('chosen',MAXGRID_ABSURL . '/assets/lib/chosen/chosen.min.css');	

		wp_enqueue_style( 'maxgrid-builder-dashboard' );
		wp_enqueue_style( 'maxgrid-builder-mce-button' );
		wp_enqueue_style( 'maxgrid-builder-ajax-spinner' );
		wp_enqueue_style( 'maxgrid-builder-admin' );
		wp_enqueue_style( 'maxgrid-builder-font-awesome' );
		wp_enqueue_style( 'maxgrid-builder-icomoon' );
		wp_enqueue_style("chosen");

		wp_register_script( 'maxgrid-builder-core-functions', MAXGRID_ABSURL . '/assets/js/core-functions.js', array('jquery'));
		wp_register_script( 'chosen', MAXGRID_ABSURL . '/assets/lib/chosen/chosen.jquery.min.js', array( 'jquery' ), NULL, false );
		wp_register_script( 'maxgrid-builder-chosen-init', MAXGRID_ABSURL . '/assets/lib/chosen/chosen.init.js', array( 'jquery' ), NULL, false );
		
		wp_enqueue_script( 'maxgrid-builder-core-functions' );
		wp_enqueue_script( 'chosen' );
		wp_enqueue_script( 'maxgrid-builder-chosen-init' );

		if ( function_exists( 'get_current_screen' ) ) {
			$pt = get_current_screen()->post_type;
			if ( $pt != 'post' && $pt != 'page' )
				return;
		}
		
		extract(maxgrid()->assets->get_mce_vars());
		?>
		<script type="text/javascript">			
			var MAXGRID_PLUGIN_LABEL_NAME 	= '<?php echo MAXGRID_PLUGIN_LABEL_NAME; ?>';
			var MAXGRID_ABSURL 			= '<?php echo MAXGRID_ABSURL; ?>';
			var dldPostType 		= '<?php echo MAXGRID_POST; ?>';
			var wpCat 				= '<?php echo $categories; ?>';
			var maxgridCat 			= '<?php echo $maxgrid_categories; ?>';
			var wooCat 				= '<?php echo $woo_categories; ?>';
			var maxgridCatEsxclude 	= '<?php echo $dflt_exl_cats; ?>';
			var isTemplatesOpened 	= '<?php echo is_maxgrid_templates_library() ? true : null; ?>';
			var postsType 			= '<?php echo json_encode($post_type); ?>';
			var postPresetsList 	= '<?php echo $post_presets_list; ?>';
			var dldPresetsList 		= '<?php echo $dld_presets_list; ?>';
			var wooPresetsList 		= '<?php echo $woo_presets_list; ?>';
			var ytbPresetsList 		= '<?php echo $ytb_presets_list; ?>';
			var ajaxurl 			= '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			var api_settings_url 	= '<?php echo get_home_url().'/wp-admin/admin.php?page='.MAXGRID_SETTINGS_PAGE.'&tab=tab4'; ?>';		
			var default_channel_id 	= '<?php echo trim( isset($default_channel_id) && !empty($default_channel_id) ? $default_channel_id : 'BBCEarth'); ?>';
		</script>
		<?php
	}
	
	/**
	 * init
	 * calls custom editor styles
	 * @return void
	 */
	public function add_editor_style() {
		add_editor_style( MAXGRID_ABSURL.'/tinymce/css/custom-editor-style.css' );		
	}
	
	function wpdocs_theme_editor_dynamic_styles( $mceInit ) {
		$styles = '.maxgrid-mce-ptype.product:after {background: url('.MAXGRID_ABSURL.'/tinymce/css/woo-icon.png) no-repeat 0 0;}';
		if ( isset( $mceInit['content_style'] ) ) {
			$mceInit['content_style'] .= ' ' . $styles . ' ';
		} else {
			$mceInit['content_style'] = $styles . ' ';
		}
		return $mceInit;
	}
	
}

new Max_Grid_Shortcode;