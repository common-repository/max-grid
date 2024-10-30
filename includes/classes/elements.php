<?php
/**
 * Additional grid elements.
 */

namespace MaxGrid;

use \MaxGrid\Youtube;

defined( 'ABSPATH' ) || exit;

/**
 * @class Elements.
 */
class Elements {

	/**
	 * Options data instance.
	 *
	 * @var getOptions
	 */
	public $get_options;
	
	/**
	 * $args attributes.
	 */
	public $post_id;
	public $post_type;
	public $layout_type;
	public $the_title;
	public $the_excerpt;
	public $ribbon_position;
	public $share_icon_size;
	public $grid;
	public $featured_type;
	public $page;
	public $thumb;
	public $url;
	public $terms;
	
	/**
     * Constructor.
	 */
	function __construct($args=array()) {
		$post_type = isset($args['post_type']) ? $args['post_type'] : 'post';
		$preset_name = isset($args['preset_name']) ? $args['preset_name'] : MAXGRID_DFLT_LAYOUT_NAME;
		
		$presets_args = array('source_type' => $post_type, 'preset_name' => $preset_name);
		$this->get_presets = new getPresets($presets_args);
		
		$this->get_options 		= maxgrid()->get_options;
		
		$this->post_id 			= isset($args['post_id']) ? $args['post_id'] : '';
		$this->post_type 		= isset($args['post_type']) ? $args['post_type'] : '';
		$this->layout_type 		= isset($args['layout_type']) ? $args['layout_type'] : '';
		$this->the_title 		= isset($args['the_title']) ? $args['the_title'] : '';
		$this->the_excerpt 		= isset($args['the_excerpt']) ? $args['the_excerpt'] : '';
		$this->ribbon_position 	= isset($args['ribbon_position']) ? $args['ribbon_position'] : '';
		$this->share_icon_size 	= isset($args['share_icon_size']) ? $args['share_icon_size'] : '';
		$this->grid 			= isset($args['grid']) ? $args['grid'] : '';
		$this->featured_type 	= isset($args['featured_type']) ? $args['featured_type'] : 'image';
		$this->page 			= isset($args['page']) ? $args['page'] : '';
		$this->thumb 			= isset($args['thumb']) ? str_replace('http://', 'https://', $args['thumb'] ) : '';
		$this->small_thumb		= isset($args['small_thumb']) ? str_replace('http://', 'https://', $args['thumb'] ) : '';
		$this->url 				= isset($args['url']) ? $args['url'] : '';
		$this->terms 			= isset($args['terms']) ? $args['terms'] : '';
    }
	
	/**
	 * Woocommerce Add To Cart Button
	 *
	 * @since: 1.0.0
	 *
	 * @return string
	 */
    public function addToCart($args) {
		$product_id  = isset($args['product_id']) ? $args['product_id'] : '';
		$label 		 = isset($args['label']) ? $args['label'] : '';
		$size 		 = isset($args['size']) ? $args['size'] : ' small';
		$spin 		 = isset($args['spin']) ? $args['spin'] : ' inner-spin';
		$rounded 	 = isset($args['rounded']) ? $args['rounded'] : ' pointed';
		$thin 		 = isset($args['thin']) ? $args['thin'] : ' thick';
		$disable_qty = isset($args['disable_qty']) ? true : null;		
		$target 	 = isset($args['target']) ? $args['target'] : 'grid';
		$bloc_h_fit  = isset($args['bloc_h_fit']) ? ' '.$args['bloc_h_fit'] : '';		
		$target 	 = $target == 'lightbox' ? ' in_lightbox' : '';	
		
		$_product  	 = wc_get_product( $product_id );
		$_currency   = ' '.get_woocommerce_currency_symbol();
		$_sale 		 = $_product->get_sale_price() ? '<span class="regular_price-sale">'.$_product->get_regular_price().$_currency.'</span>' : '';
		
		$add_cart_btn = '<div class="input-group plus-minus-input' . $bloc_h_fit . $rounded . $spin . $size . $target . '">
							<span class="pg_price">
								'.$_sale.'
								<span class="get_price">'.$_product->get_price().$_currency.'</span>
							</span>
							<span class="pg_add_to_cart">';
		if(!isset($disable_qty)){
		$add_cart_btn .= '<div>
							<button type="button" class="plus-minus-button minus'. $thin .'" data-quantity="minus" data-field="quantity"></button>
							<input class="input-group-field" type="number" step="1" min="1" max="" name="quantity" value="1">
							<button type="button" class="plus-minus-button plus'.$thin.'" data-quantity="plus" data-field="quantity"></button>
							</div>';
		}
		$add_cart_btn .= '<button type="button" data-quantity="1" data-product_id="'.$product_id.'" class="button alt ajax_add_to_cart add_to_cart_button product_type_simple">'.$label.'</button>
							<span class="add-to-cart-spinner"></span>
							</span>
						</div>';
		return $add_cart_btn;
	}
	
	/**
	 * Download
	 *
	 * @since: 1.0.0
	 *
	 * @return string
	 */
	public function Download($args) {		
		$options 	   = $this->get_presets->rows('download_row');		
		$track_options = $this->get_options->option(MAXGRID_LOGS_OPT_NAME);
		
		$view 		   = isset($args['view']) ? $args['view'] : 'grid';
		$bloc_h_fit	   = isset($args['fit']) ? $args['fit'] : '';
		$box	   	   = isset($args['box']) && $args['box'] == 'none' ? null : true;
		$class	   	   = isset($args['class']) ? ' '.$args['class'] : '';
		
		$button_label  = !empty($options['button_label'])   ? $options['button_label'] 	 : 'Download';
		$button_size   = isset($options['button_size']) ? $options['button_size'] : 'small';
		$button_size   = isset($view) && $view == 'list' ? 'medium' : $button_size;
		$full_width    = isset($options['fullwidth']) &&  maxgrid_string_to_bool($options['fullwidth'])==1 ?  'style="width: 100%"': '';
		$btn_pos 	   = isset($options['button_position']) ? $options['button_position'] : 'right';
		$btn_pos  	   = isset($view) && $view == 'list' ? 'left' : $btn_pos;
				
		$url_file 	   = maxgrid()->get_post_meta->download($this->post_id)['file'];
		$once 		   = isset($track_options['re_download_count']) && ( $track_options['re_download_count'] == 're_download_count' || $track_options['re_download_count'] == 'true') ? true : false;
		
		$margin_top    = isset($options['margin_top']) ? $options['margin_top'].'px' : '5px';
		$margin_bottom = isset($options['margin_bottom']) ? $options['margin_bottom'].'px' : '5px';
		
		if(isset($view) && $view == 'list'){
			$margin_top    = 0;
			$margin_bottom = 0;
		}
		$download_btn = '';
		if(isset($box)){
			$download_btn = '<div class="pg_wrapper download_bar' . $bloc_h_fit . '" style="margin-top:'.$margin_top.';margin-bottom:'.$margin_bottom.';text-align: '.$btn_pos.'">';
		}	
		$download_btn.= '<div id="maxgrid-button-style_container" class="'.$class.'"'.$full_width.'>
								<span id="maxgrid_download_single" class="maxgrid-button maxgrid-error download '.$button_size.'" data-post-id="'.$this->post_id.'" data-meta-key="'.MAXGRID_DOWNLOAD_META_KEY.'" data-once="'.$once.'" data-href="'.maxgrid_encrypt($url_file, 'e').'">'.$button_label.'</span>
								<span class="ajax_dl-spiner"></span>
							</div>';
		if(isset($box)){
			$download_btn .= '</div>';
		}
		return $download_btn;
	}
	
	/**
	 * Average Rating Stats for download post
	 *
	 * @since: 1.0.0
	 *
	 * @return string
	 */
	public function AverageRating($bloc_h_fit) {		
		$options 	 = $this->get_presets->rows('average_rating_row');		
		$description = isset($options['description']) ? $options['description'] : 'reviews';
		$stars_size  = isset($options['stars_size']) ? $options['stars_size'] : 'small';
		$align 		 = isset($options['align']) ? $options['align'] : 'left';		
		$logged_msg  = isset($options['logged_in_msg']) ? $options['logged_in_msg'] : 'You must be logged-in to download this component!';
		
		$rating = maxgrid()->rating;
		$data = array(
				'post_id' 	  => $this->post_id,
				'stars_size'  => $stars_size,
				'description' => $description,
			);
		$average = $rating->get_average($data);
		
		$margin_top 	= isset($options['margin_top']) ? $options['margin_top'] : 5;
		$margin_bottom  = isset($options['margin_bottom']) ? $options['margin_bottom'] : 5;
		
		return '<div class="pg_wrapper average_rating_bar' . $bloc_h_fit . '" style="margin-top:'.$margin_top.'px; margin-bottom:'.$margin_bottom.'px; text-align: '.$align.';">' . $average . '</div>';
	}
	
	/**
	 * The Featured
	 *
	 * @since: 1.0.0
	 *
	 * @return string
	 */
	public function Featured($view='grid', $ribbon='', $bloc_h_fit='', $first_row='') {
		
		if ( $this->thumb == '' && $this->featured_type == 'image' ) {
			return '';
		}
				
		$track_options 	  = $this->get_options->option(MAXGRID_LOGS_OPT_NAME);		
		$featured_options = $this->get_presets->rows('featured_row');
		$post_type 		  = $this->post_type;		
		$vimeo_url 		  = maxgrid()->get_post_meta->grid($this->post_id)['vimeo_url'];
		$youtube_url 	  = maxgrid()->get_post_meta->grid($this->post_id)['youtube_url'];
		$mp4_url 		  = maxgrid()->get_post_meta->grid($this->post_id)['mp4_url'];
		$mp4_v_duration	  = get_post_meta($this->post_id, 'maxgrid_mp4_v_duration', true);
		$mp4_duration 	  = !empty($mp4_v_duration) ? $mp4_v_duration : '00:00';
		$thumbnail_url 	  = get_post_meta($this->post_id, 'maxgrid_thumbnail_url', true);
			
		// Video Duration
		$duration 		  = isset($featured_options['duration']) && maxgrid_string_to_bool($featured_options['duration']) == 1 ? true : null;		
		$vid_duration 	  = '';
		
		if ($duration) {
			if ( $this->featured_type == 'vimeo' ) {
				$video_id = (int) substr(parse_url($vimeo_url, PHP_URL_PATH), 1);
				$api_url  = "http://vimeo.com/api/v2/video/{$video_id}.json";
				$hash 	  = json_decode( wp_remote_retrieve_body( wp_remote_get( $api_url ) ) );
				$vidTime  = $hash[0]->duration;
				$vid_duration = '<div class="vid-duration">'. maxgrid_duration_to_time($vidTime, 'vimeo').'</div>';				
			} else if ( $this->featured_type == 'youtube' ) {
				$get_options = maxgrid()->get_options;
				$api_key = isset($get_options->option('api_options')['youtube_api_key']) ? $get_options->option('api_options')['youtube_api_key'] : '';				
				parse_str( parse_url( $youtube_url, PHP_URL_QUERY ), $vars );
				$vid_id = $vars['v'];
				$args = array(
					'vid_id'  => $vid_id,
					'api_key' => trim($api_key),
				);
				
				$duration = maxgrid_get_ytb_v_duration($args);				
				$vid_duration = '<div class="vid-duration">'.$duration.'</div>';		
			} else if ( $this->featured_type == 'mp4' ) {
				$vid_duration = '<div class="vid-duration">'.$mp4_duration.'</div>';			
			}		
		}
		
		// Switch between Grid & List view
		$insert_as = isset($featured_options['insert_as']) ? $featured_options['insert_as'] : 'lightbox';
		$insert_as = !is_maxgrid_premium_activated() && $insert_as == 'iframe' ? 'lightbox' : $insert_as;
		
		// hover effects
		$h_zoom_in    	= isset($featured_options['hover_zoom_in']) && maxgrid_string_to_bool($featured_options['hover_zoom_in']) == 1 ? ' hover-zoom-in' : '';
		$h_box_shadow 	= isset($featured_options['hover_box_shadow']) && maxgrid_string_to_bool($featured_options['hover_box_shadow']) == 1 ? ' hover-box-shadow' : '';

		// Play Button
		$play_btn_html = '<div data-type="'.$this->featured_type.'" class="ytb-play-btn"></div>';
		
		// Links
		if ( $insert_as == 'single' ) {
			$permalink = get_permalink($this->post_id);			
			$href_return = '';
		} else {
			$permalink = '#';
			$href_return = ' onclick="return false;"';
		}
		
		if ( $view == 'grid' ) {
			$terms = $this->terms[$post_type][0];			
			
			// Components options
			$share_this   	= isset($featured_options['share_this']) && maxgrid_string_to_bool($featured_options['share_this']) == 1 ? true : null;
			$love_this		= isset($featured_options['love_this']) && maxgrid_string_to_bool($featured_options['love_this']) == 1 ? true : null;
			$post_title   	= isset($featured_options['the_title']) && maxgrid_string_to_bool($featured_options['the_title']) == 1 ? true : null;
			$post_excerpt 	= isset($featured_options['post_excerpt']) && maxgrid_string_to_bool($featured_options['post_excerpt']) == 1 ? true : null;
			$post_category 	= isset($featured_options['category']) && maxgrid_string_to_bool($featured_options['category']) == 1 ? true : null;
			$download_btn	= isset($featured_options['download_btn']) && maxgrid_string_to_bool($featured_options['download_btn']) == 1 ? true : null;
			$custom_term_c 	= isset($featured_options['custom_term_color']) && maxgrid_string_to_bool($featured_options['custom_term_color']) == 1 ? true : null;
			$product_price 	= isset($featured_options['price']) && maxgrid_string_to_bool($featured_options['price']) == 1 ? true : null;
			
			$post_stats 	= isset($featured_options['post_stats']) && maxgrid_string_to_bool($featured_options['post_stats']) == 1 && ( isset($featured_options['download_count']) && maxgrid_string_to_bool($featured_options['download_count']) == 1 || isset($featured_options['views_count']) && maxgrid_string_to_bool($featured_options['views_count']) == 1 || isset($featured_options['like_count']) && maxgrid_string_to_bool($featured_options['like_count']) == 1 ) ? true : null;
			
			$published_at	= isset($featured_options['published_at']) && maxgrid_string_to_bool($featured_options['published_at']) == 1 ? true : null;
			$download_count = isset($featured_options['download_count']) && maxgrid_string_to_bool($featured_options['download_count']) == 1 ? true : null;
			$views_count 	= isset($featured_options['views_count']) && maxgrid_string_to_bool($featured_options['views_count']) == 1 ? true : null;
			$fillcover   	= isset($featured_options['fillcover_overlay']) && maxgrid_string_to_bool($featured_options['fillcover_overlay']) == 1 ? ' fillcover' : '';
			$ov_transition 	= isset($featured_options['overlay_transition']) ? ' '.$featured_options['overlay_transition'] : ' slide_up';
						
			$total_sales   	= isset($featured_options['total_sales']) && maxgrid_string_to_bool($featured_options['total_sales']) == 1 ? true : null;
			$like_count   	= isset($featured_options['like_count']) && maxgrid_string_to_bool($featured_options['like_count']) == 1 ? true : null;
			
			// Embed options									
			$networks = array();
			foreach ( $featured_options as $key => $value ){
				if(strpos($key, 'netw_') !== false && maxgrid_string_to_bool($value) == 1 ) {
					$networks[] = str_replace('netw_', '', $key);
				}
			}
					
			// Share This  Button
			$share_this_html = '';
			
			if ( is_maxgrid_premium_activated() ) {			
				if ( $share_this ) {				
					$twitter = get_the_author_meta('maxgrid_twitter', $this->post_id);
					$share_data = array(
							'source' 			=> str_replace(' ', '_', get_bloginfo('name')),
							'href' 				=> get_permalink($this->post_id),
							'title' 			=> get_the_title($this->post_id),
							'thumbnail_medium' 	=> get_the_post_thumbnail_url($this->post_id, 'medium'),
							'thumbnail_full' 	=> get_the_post_thumbnail_url($this->post_id, 'full'),
							'twitter_username' 	=> $twitter,
						);
					$share = new Share($share_data);
					$return = 'link';
					$args = array(
						//'id' => $key,
						'media' => $networks,
						'return' => 'link'
					);
					$share_this_html = '<div class="f_share-this">
											'.$share->get_media($args).'
											<div class="f_share-btn"></div>
										</div>';
				}
			}
			
			// Love This  Button
			$love_this_html = '';
			if ( $love_this && is_maxgrid_premium_activated() ) {				

				$views_meta_key = maxgrid_use_custom_post_meta_key( 'views' );
				$views_meta_key = $views_meta_key ? $views_meta_key : MAXGRID_VIEWS_META_KEY;

				$likes_meta_key = maxgrid_use_custom_post_meta_key( 'likes' );
				$likes_meta_key = $likes_meta_key ? $likes_meta_key : MAXGRID_LIKES_META_KEY;
				
				$once = isset($track_options['block_re_vote']) && ( $track_options['block_re_vote'] == 'block_re_vote' || $track_options['block_re_vote'] == 'true') ? true : false;
					
				$has_already_voted = countMeta::once_per_ip($this->post_id, $likes_meta_key, $once);
				if($has_already_voted) {
					$voted = ' alreadyvoted';
				} else {
					$voted = '';
				}
				
				$love_this_html = '<div class="f_love-this'.$voted.'" data-post_id="'.$this->post_id.'"></div>';
			}
			
			// Post Category
			$post_cat_html = '';
			if ($post_category) {
				$cat_bg_color = isset($featured_options['bg_color_term']) ? $featured_options['bg_color_term'] : 'rgba(26,38,41,0.8)';
				$cat_color = isset($featured_options['color_term']) ? $featured_options['color_term'] : 'rgba(26,38,41,0.8)';
				
				if ( !$custom_term_c && is_maxgrid_premium_activated() ) {
					$bg_color 	  = get_term_meta( $terms->term_id, 'cat_bg_color', true );
					$cat_bg_color = preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $bg_color ) ? $bg_color : '#0081ff';
					$cat_color    = get_term_meta( $terms->term_id, 'cat_color', true );
					$cat_color    = preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $cat_color ) ? $cat_color : '#ffffff';
				}
				
				$cats_links = '';
				$arr_termes = $this->terms[$post_type];
				$numItems   = count($arr_termes);
				$i 			= 0;
				foreach($arr_termes as $key => $value){					
					$cats_links .= '<span data-catslug="'.$arr_termes[$key]->slug.'" class="maxgrid-post-catname">'.$arr_termes[$key]->name.'</span>';
					if(++$i !== $numItems) {
						$cats_links .= ', ';
					}
				}
				
				$post_cat_html = '<div class="post-category" id="catnamepost" style="background: '.$cat_bg_color.'; color: '.$cat_color.';">'.$cats_links.'</div>';
				
				if(isset($featured_options['fillcover_overlay']) && maxgrid_string_to_bool($featured_options['fillcover_overlay']) == 1 ) {
					$post_cat_html = '<div class="post-category fillcover" id="catnamepost"><strong>In</strong> '.$cats_links.'</div>';
				}
				
				if($download_btn) {
					$post_cat_html = '<div class="post-category inside-slide-up" id="catnamepost"><strong>In</strong> '.$cats_links.'</div>';
				}
			}
			
			$download_btn_html = '';
			if ($download_btn) {
				$args = array('view' => 'grid', 'box' => 'none');
				$download_btn_html = $this->Download($args);
			}
			
			if(isset($featured_options['fillcover_overlay']) && maxgrid_string_to_bool($featured_options['fillcover_overlay']) == 1 ) {
				$download_btn_html = '';
			}
			
			// The Title
			$post_title_html = isset($post_title) ? '<div class="post-title">'.$this->the_title.'</div>' : '';
			
			// Post Excerpt
			$post_excerpt_html = isset($post_excerpt) ? '<div class="excerpt-content">'.$this->the_excerpt.'</div>' : '';
			
			// Post Stats Bar
			$links_html = '';
			$stats_html = '';
			
			if ( $post_stats ) {
				$maxgrid_download_file = maxgrid()->get_post_meta->download($this->post_id)['file'];
				
				$stats_html = '<div class="post-stats">';	
				if($download_count && $maxgrid_download_file != '') {
					$stats_html .= '<span class="f_dld-count">' . countMeta::get_count($this->post_id, MAXGRID_DOWNLOAD_META_KEY) . '</span>';
				}
				if ( $views_count && is_maxgrid_premium_activated() ) {
					$stats_html .= '<span class="f_views-count">' . countMeta::get_count($this->post_id, $views_meta_key) . '</span>';
				}
				
				//Show Total Sales				
				if($total_sales) {
					$sales_count = get_post_meta( $this->post_id, 'total_sales', true ); 
					$units_sold = sprintf( _n( '%s Sale', '%s Sales', $sales_count, 'max-grid' ), $sales_count );
					
					$stats_html .= '<span class="f_sales-count">' . $units_sold . '</span>';
				}
				
				if ( $like_count && is_maxgrid_premium_activated() ) {
					$stats_html .= '<span class="f_like-count">' . countMeta::get_count($this->post_id, $likes_meta_key) . '</span>';
				}
				
				$stats_html .= '</div>';
			}
						
			if ( isset($featured_options['fillcover_overlay']) && maxgrid_string_to_bool($featured_options['fillcover_overlay']) == 1 && $insert_as != 'iframe' || isset($featured_options['fillcover_overlay']) && maxgrid_string_to_bool($featured_options['fillcover_overlay']) == 1 && $this->featured_type == 'image' ) {
				$links_html = '<div class="post-links">';
				if ( isset($featured_options['lightbox_link']) && maxgrid_string_to_bool($featured_options['lightbox_link']) == 1 ) {
					switch ($this->featured_type ) {				
						case "image" :
							$postlink = $this->thumb;
							break;
						case "vimeo" :
							$postlink = $vimeo_url;
							break;
						case "youtube" :
							$postlink = $youtube_url;
							break;				
						case "mp4" :
							$postlink = $mp4_url;
							break;
					}
					$links_html .= '<i class="fa fa-search" id="lightbox-enabled" href="'.$permalink.'" data-featured-type="'.$this->featured_type.'" data-lightbox-id="'.$this->post_id.'" data-post-type="'.$this->post_type.'" data-href="'.$postlink.'"'.$href_return.'></i>';
				}
				if ( isset($featured_options['external_link']) && maxgrid_string_to_bool($featured_options['external_link']) == 1 ) {
					$links_html .= '<i class="fa fa-link" data-external-link="'.get_permalink($this->post_id).'"></i>';
				}
				if ( isset($featured_options['add_to_cart_link']) && maxgrid_string_to_bool($featured_options['add_to_cart_link']) == 1 ) {
					$links_html .= '<i class="fa fa-shopping-cart button alt ajax_add_to_cart add_to_cart_button product_type_simple" data-quantity="1" data-product_id="'.$this->post_id.'"></i>';
				}
				if ( isset($featured_options['download_link']) && maxgrid_string_to_bool($featured_options['download_link']) == 1 ) {
					$once = isset($track_options['re_download_count']) && ( $track_options['re_download_count'] == 're_download_count' || $track_options['re_download_count'] == 'true') ? true : false;
					
					$url_file 	 = get_post_meta ($this->post_id, 'maxgrid_download_file', true);
					$links_html .= '<i id="maxgrid_download_single" class="fa fa-download" data-post-id="'.$this->post_id.'" data-meta-key="'.MAXGRID_DOWNLOAD_META_KEY.'" data-once="'.$once.'" data-href="'.maxgrid_encrypt($url_file, 'e').'"></i>';
				}			
				$links_html .= '</div>';	
			} else {			
				// Add to cart link on slide up overlay
				$add_to_cart = isset($featured_options['add_to_cart']) && maxgrid_string_to_bool($featured_options['add_to_cart']) == 1 ? true : null;
				if ( $add_to_cart && $post_type == 'product' ) {
					$links_html = '<a class="ajax_add_to_cart add_to_cart_button product_type_simple slide-up-add-t-cart" data-quantity="1" data-product_id="'.$this->post_id.'">Add To Cart</a><div class="maxgrid-loader"></div>';
				}
			}
			
			
			
			$post_featured = '<div class="video-wrapper ' . $this->layout_type . $bloc_h_fit . ' insert-as-'.$insert_as . ' ' . $this->featured_type . $h_box_shadow . $h_zoom_in . $fillcover . $first_row . $ov_transition . '">'.$ribbon;
			
			$post_featured .= '<div data-type="'.$this->featured_type.'" class="ytb-pause-btn">×</div>';
			
			// Get Product Price
			$get_price = '';
			if ( $product_price && $post_type == 'product' ) {
				$_product  	= wc_get_product( $this->post_id );
				$_currency  = ' '.get_woocommerce_currency_symbol();				
				$get_price = '<div class="get_price">'.$_product->get_price().$_currency.'</div>';
			}
			
			if ( is_maxgrid_premium_activated() ) {			
				$post_featured .= '<div class="pg_featured-layer social-icons '.$this->ribbon_position.'">
									  '.$share_this_html.'
									  '.$love_this_html.'						  
								  </div>';
			}
			
			if(isset($featured_options['fillcover_overlay']) && maxgrid_string_to_bool($featured_options['fillcover_overlay']) == 1){
				$post_featured .= $get_price.$vid_duration;
			}
			
			$f_layers = $get_price . $post_title_html . $post_excerpt_html . $links_html . $stats_html;	
			$no_layer = $f_layers == '' ? ' no-layer': '';
			
			$is_rotated 	= ''; //' is-rotated'; add this to enable the Direction Aware rotate mode.		
			$post_featured .= '<div class="pg_featured-layer post-excerpt'.$fillcover.$ov_transition.$no_layer.$is_rotated.'">';	
			$post_featured .= '<div class="slide-up_inner_content">';

			if( !isset($featured_options['fillcover_overlay']) || maxgrid_string_to_bool($featured_options['fillcover_overlay']) != 1){
				$post_featured .= $get_price.$vid_duration;
			}
			if ( $this->featured_type != 'image' && $insert_as == 'iframe' && ( isset($featured_options['fillcover_overlay']) &&  maxgrid_string_to_bool($featured_options['fillcover_overlay']) == 1 ) ) {
				$post_featured .= $play_btn_html;
			}
	       	$post_featured .= $post_title_html
							   .$post_excerpt_html
							   .$links_html
							   .$post_cat_html
							   .$download_btn_html;
			$post_featured .=  $stats_html;
			$post_featured .= '</div>
							</div>';
			
			if ( $this->featured_type != 'image' && $insert_as == 'iframe' && ( isset($featured_options['fillcover_overlay']) && maxgrid_string_to_bool($featured_options['fillcover_overlay']) != 1 || !isset($featured_options['fillcover_overlay']) ) ) {
				$post_featured .= $play_btn_html;
			}
		} else if ( $view == 'list' ) {
			$post_featured = '<div class="video-wrapper ' . $this->layout_type . ' insert-as-'.$insert_as . ' ' . $this->featured_type . $h_box_shadow . $h_zoom_in . '" id="featured_container">';
			
			$post_featured .= '<div data-type="'.$this->featured_type.'" class="ytb-pause-btn">×</div>';
						
			$post_featured .= $vid_duration;
			
			if ( $this->featured_type != 'image' && $insert_as == 'iframe' ) {
				$post_featured .= $play_btn_html;
			}
		}
		
		switch ($this->featured_type ) {				
			case "image" :
				$post_featured .= '<a id="lightbox-enabled" href="'.$permalink.'" data-featured-type="image" data-page-id="'.$this->page.'" data-lightbox-id="'.$this->post_id.'" data-post-type="'.$this->post_type.'" data-href="'.$this->thumb.'"'.$href_return.'><img id="post_thumbnail" class="product-item-thumbnail grid" src="'.$this->small_thumb.'" alt=""></a>';
				
				break;				
			case "vimeo" :
				// Get Video Thumbnail from Youtube Vimeo
				$post_featured .= '<a id="lightbox-enabled" href="'.$permalink.'" data-featured-type="vimeo" data-page-id="'.$this->page.'" data-lightbox-id="'.$this->post_id.'" data-post-type="'.$this->post_type.'" data-href="'.$vimeo_url.'"'.$href_return.'><img id="post_thumbnail" class="product-item-thumbnail grid" src="'.$this->thumb.'" alt=""></a>';

				// if embed mode use beleow
				if ( $insert_as == 'iframe' ) {
					$video_id = (int) substr(parse_url($vimeo_url, PHP_URL_PATH), 1);
					$post_featured .= '<input type="hidden" data-src="https://player.vimeo.com/video/'.$video_id.'?title=0&byline=0&portrait=0" class="iframe-data">';
					/*
					$post_featured .= '<iframe src="https://player.vimeo.com/video/'.$video_id.'?title=0&byline=0&portrait=0" width="640" height="auto" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';*/
				}
				
				break;				
			case "youtube" :
				// Get Video Thumbnail from Youtube Video
				$post_featured .= '<a id="lightbox-enabled" href="'.$permalink.'" data-featured-type="youtube" data-page-id="'.$this->page.'" data-lightbox-id="'.$this->post_id.'" data-post-type="'.$this->post_type.'" data-href="'.$youtube_url.'"'.$href_return.'><img id="post_thumbnail" class="product-item-thumbnail grid" src="'.$this->thumb.'" alt=""></a>';

				// if embed mode use beleow
				if ( $insert_as == 'iframe' ) {
					parse_str( parse_url( $youtube_url, PHP_URL_QUERY ), $array_vars );
					$video_id = $array_vars['v'];
					$post_featured .= '<input type="hidden" data-src="https://www.youtube.com/embed/'.$video_id.'?rel=0&amp;showinfo=0" class="iframe-data">';
					/*$post_featured .= '<iframe style="transform: scale(1.007)!important;" width="560" height="auto" src="https://www.youtube.com/embed/'.$video_id.'?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';*/
				}
				
				break;				
			case "mp4" :
				$post_featured .= '<a id="lightbox-enabled" href="'.$permalink.'" data-featured-type="mp4" data-page-id="'.$this->page.'" data-lightbox-id="'.$this->post_id.'" data-post-type="'.$this->post_type.'" data-href="'.$mp4_url.'"'.$href_return.'><img id="post_thumbnail" class="product-item-thumbnail grid" src="'.$this->thumb.'" alt=""></a>';

				$post_featured .= '<video id="pg_mp4-video" data-postid="'.$this->page.'" class="video-js" controls preload="none" width="100%" height="100%"
							  poster="'.$thumbnail_url.'" data-setup="{}">
								<source src="'.$mp4_url.'" type="video/mp4">
								<source src="'.preg_replace('"\.mp4$"', '.webm', $mp4_url).'" type="video/webm">
							  </video>';
				
				break;				
			default :
				$post_featured .= '<a href="'. $this->featured_type . $this->url.'"><img class="product-item-thumbnail grid" src="'.$this->thumb.'" alt=""></a>';
				break;
		}
		$post_featured .= '</div>';
		
		return $post_featured;
	}
	
	/**
	 * Audio Player
	 *
	 * @since: 1.0.0
	 *
	 * @return string
	 */
	public function AudioPlayer($args=array()) {
		
		$audio_player 	= maxgrid()->get_post_meta->grid($this->post_id)['audio_player'];
		$audio_file = maxgrid()->get_post_meta->grid($this->post_id)['audio_file'];
		if ( $audio_player != 'wp_player' ) {
			return null;
		}
		
		$bloc_h_fit = isset($args['h_fit']) ? $args['h_fit'] : '';
		$target 	= isset($args['target']) ? $args['target'] : 'grid';
		
		$html    = '';
		if ( $target == 'grid' ) {
			$html .= '<div class="maxgrid-audio-player-row' . $bloc_h_fit . '">';
		}
		$attr = array(
			'src'      => $audio_file,
			'loop'     => '',
			'autoplay' => '',
			'preload'  => 'none'
		);
		$html .= wp_audio_shortcode( $attr );
		if ( $target == 'grid' ) {
			$html   .= '</div>';
		}
		return $html;		
	}	
	
	/**
	 * SoundCloud Player
	 *
	 * @since: 1.0.0
	 *
	 * @return string
	 */
	public function SoundCloudPlayer($args=array()) {
		$audio_player = maxgrid()->get_post_meta->grid($this->post_id)['audio_player'];
		$sd_code 	  = wp_specialchars_decode(maxgrid()->get_post_meta->grid($this->post_id)['soundcloud_code']);
		
		if ( $audio_player != 'soundcloud_player' ) {
			return null;
		}
		
		$bloc_h_fit = isset($args['h_fit']) ? $args['h_fit'] : '';
		$target 	= isset($args['target']) ? $args['target'] : 'grid';
			
		$sd_type = strpos($sd_code, 'playlists') ? 'playlists' : 'tracks';
		$sd_id	 = maxgrid_find_between_str($sd_code, $sd_type.'/', '&');
		$parms	 = maxgrid_find_between_str($sd_code, '&', '"');
		
		$html    = '';
		if ( $target == 'grid' ) {
			$html .= '<div class="maxgrid-audio-player-row' . $bloc_h_fit . '">';
		}
		$html   .= '<iframe width="100%" height="300" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/'.$sd_type.'/'.$sd_id.'&'.$parms.'"></iframe>';
		if ( $target == 'grid' ) {
			$html   .= '</div>';
		}
		
		return $html;		
	}
		
	/**
	 * Stats bar element HTML.
	 *
	 * @param array $current Current values
	 *
	 * @return string
	 */
	public function statsbar_element_html( $args=array() ) {
		$key 			= $args['key'];
		$maxgrid_data 	= $args['data'];
		$ui_args 		= $args['ui_args'];
		$layout_data 	= $args['layout_data'];
		$tooltip_style 	= $args['tooltip_style'];
		$def_val 		= $args['def_val'];
		$restore_title 	= $args['restore_title'];
		$root_name 		= $args['root_name'];
		$remove_icon 	= $args['remove_icon'];
		
		list($a, $b) = explode('stats_bar', $key);
		if($a){
			$cur_key = substr($a, 0, -1);
			$bar_id = $a.'stats_bar';
			$row_id = $a.'stats_row';
		} else {
			$cur_key = str_replace($b, '', $key);
			$bar_id = 'stats_bar';
			$row_id = 'stats_row';
		}
		$row_id = 'stats_row';

		$swap_likes_ratings = isset($ui_args['source_type']) && $ui_args['source_type']=='download' ? 'Average Ratings' : 'Likes';
		
		$def_data = [
			'ytb_vid' => array( 
						'views' 		=> 'Views Count',
						'date' 			=> 'Date',
						'like_count' 	=> 'Like Count',
						'dislike_count' => 'Dislike count',
						'panel_titles'  => array( 
											'date' => 'Date Settings',
											),
						'has_options'   => array('date'),
						'default' 		=> array(
											'date' => 'time_ago',
										),
						),
			'stats_bar' => array( 
						'sharethis' 	=> 'Share This',
						'download' 		=> 'Downloads',
						'views' 		=> 'Views',
						'rating' 		=> $swap_likes_ratings,
						'panel_titles'  => array( 
											'sharethis' => 'ShareThis Settings',
											//'views' 	=> 'Views Settings',
											),
						'has_options'   => array('sharethis'),
						'default' 		=> array(
											'sharethis' => maxgrid_array_to_string($def_val->sharethis_default()),
											//'views' 	=> $def_val->views_options,
										),
					),
			'woo' => array( 
						'sharethis' 	=> 'Share This',
						'download' 		=> 'Total Sales',
						//'views' 		=> 'Views',
						'rating' 		=> 'Average Ratings',
						'panel_titles'  => array( 
											'sharethis' => 'ShareThis Settings',
											//'views' 	=> 'Views Settings',
											),
						'has_options'   => array('sharethis'),
						'default' 		=> array(
											'sharethis' => maxgrid_array_to_string($def_val->sharethis_default()),
											//'views' 	=> $def_val->views_options,
										),
					),
		];

		$stats_bar_data = isset($maxgrid_data['grid_layout'][$key]) ? $maxgrid_data['grid_layout'][$key] : $def_data[$cur_key];
		$html  = '<ul class="elements_container">';
		$index = 0;
		foreach ( $def_data[$cur_key] as $stats_key => $stats_value ){
			if (in_array($stats_key, array('panel_titles', 'has_options', 'default'))){continue;};

			if ( in_array($stats_key, $stats_bar_data ) && isset($layout_data[$key][$stats_key]) && $layout_data[$key][$stats_key] != 'disabled' || empty($current_option) && isset($layout_data[$key][$stats_key]) && $layout_data[$key][$stats_key] != 'disabled' ) {
				if ( in_array($stats_key, $def_data[$cur_key]['has_options'] ) ) {
					$current = isset($maxgrid_data['grid_layout'][$key][$stats_key.'_options']) ? $maxgrid_data['grid_layout'][$key][$stats_key.'_options'] : $def_data[$cur_key]['default'][$stats_key];

					$edit_class = $key.'_'.$stats_key.'_options';
					if ( $key === $stats_key ) {
						$edit_class = $stats_key.'_options';
					}

					$html .= '<li class="row_element edit">
								<span class="el_remove dashicons dashicons-no-alt" data-row="'.$bar_id.'" data-element="'.$stats_key.'" data-editable="true">
									<input type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$stats_key.']" value="'.$stats_key.'">
								</span>
								'.$stats_value.'
								<span class="edit_row element dashicons dashicons-edit" data-row-id="'.$key.'" data-action="'.$stats_key.'_options" data-ui-panel-title="'.$def_data[$cur_key]['panel_titles'][$stats_key].'">

									<input class="'.$edit_class.'" type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$stats_key.'_options]" value="'.$current.'">
								</span>
							</li>';
				} else {
					$html .= '<li class="row_element"><span class="el_remove dashicons dashicons-no-alt" data-row="'.$bar_id.'" data-element="'.$stats_key.'"><input type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$stats_key.']" value="'.$stats_key.'"></span>'.$stats_value.'</li>';
				}

			} else {
				if ( in_array($stats_key, $def_data[$cur_key]['has_options'] ) ) {
					$current = isset($maxgrid_data['grid_layout'][$key][$stats_key.'_options']) ? $maxgrid_data['grid_layout'][$key][$stats_key.'_options'] : $def_data[$cur_key]['default'][$stats_key];
					$html .= '<li class="row_element locked"><span class="el_remove dashicons dashicons-no-alt" data-row="'.$bar_id.'" data-element="'.$stats_key.'" data-editable="true"><input type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$stats_key.']" value="disabled"></span>'.$stats_value.'<span class="edit_row element dashicons dashicons-edit" data-row-id="'.$key.'" data-action="'.$stats_key.'_options" data-ui-panel-title="'.$def_data[$cur_key]['panel_titles'][$stats_key].'"><input class="'.$key.'_'.$stats_key.'_options" type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$stats_key.'_options]" value="'.$current.'"></span></li>';
					$index ++;
				} else {
					$html .= '<li class="row_element locked"><span class="el_remove dashicons dashicons-no-alt" data-row="'.$bar_id.'" data-element="'.$stats_key.'"><input type="hidden" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.']['.$stats_key.']" value="disabled"></span>'.$stats_value.'</li>';
				}
				$index ++;
			}				
		}
		if ( $index == 0 ) { $unlock_restore_btn = ' locked'; } else {$unlock_restore_btn = '';}

		$html .= '<a title="Restore deleted elements"><span class="restore dashicons dashicons-update'.$unlock_restore_btn.'" data-rel="maxgrid_tooltip" data-style="'.$tooltip_style.'" data-title="'.$restore_title.'">&nbsp;</span></a>';
		$html .= '</ul>';

		$html .= '<span class="edit_row bar maxgrid_ui-btn dashicons dashicons-edit" data-row-id="'.$root_name.'" data-bar="'.$bar_id.'" data-action="'.$row_id.'" data-ui-panel-title="Post Stats Settings"></span>';
		$html .= '<span class="duplicate_row maxgrid_ui-btn" data-row-id="'.$root_name.'" data-action="'.$row_id.'" data-bar="'.$bar_id.'"><i class="far fa-clone" aria-hidden="true"></i></span>'.$remove_icon;

		$statsbar_css = isset($maxgrid_data['grid_layout'][$key]['statsbar_css']) ? $maxgrid_data['grid_layout'][$key]['statsbar_css'] : $def_val->statsbar_css();

		$html .= '<input type="hidden" id="'.$key.'_statsbar_css" name="'.MAXGRID_BUILDER_OPT_NAME.'[grid_layout]['.$key.'][statsbar_css]" value="'.$statsbar_css.'">';
		return $html;
	}
}