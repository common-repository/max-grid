<?php
/**
 * Max Grid Builder - Default elements parameters.
 *
 * @class Max_Grid_Settings.
 */
class Max_Grid_Settings {
		
	public $maxgrid_builder_settings;
	public $blocks_row;
	public $filter_default;
	public $featured_row;
	public $audio_row;
	public $add_to_cart_row;
	public $download_row;
	public $average_rating_row;
	public $divider_row;
	public $title_row;
	public $description_row;
	public $ytb_description_row;
	public $readmore_element;
	public $stats_row;
	public $info_row;
	public $date_options;
	public $datebar_css;
	public $design_options_css;
	public $sharethis_options;
	public $statsbar_css;
	public $lightbox_row;
	
	/**
	 * Get the default parameters
	 */
	public function __construct() {
		$this->blocks_row 		  	  = $this->blocks_default();
		$this->filter_default	  	  		  = $this->filter_default();
		$this->lightbox_row 	  	  = $this->lightbox_default();
		$this->featured_row 	  	  = $this->featured_default();
		$this->audio_row    	  	  = $this->audio_player_default();
		$this->add_to_cart_row 	  	  = $this->add_to_cart_default();
		$this->download_row    	  	  = $this->download_default();
		$this->average_rating_row 	  = $this->average_rating_default() ;
		$this->divider_row 		  	  = $this->divider_default();	
		$this->title_row 		  	  = $this->title_default();	
		$this->description_row 	  	  = $this->summary_default();
		$this->ytb_description_row	  = $this->ytb_summary_default();	
		$this->stats_row 		  	  = $this->stats_default();
		$this->info_row 		  	  = $this->post_meta_default();
		$this->date_options 	  	  = $this->date_options_default();	
		$this->sharethis_options  	  = $this->sharethis_default();		
		$this->design_options_css 	  = wp_remote_retrieve_body( wp_remote_get( MAXGRID_ABSURL . '/includes/css/bb-design-option-default-styles.css' ) );	
		$this->datebar_css 		  	  = $this->datebar_css();
		$this->statsbar_css 	  	  = $this->statsbar_css();
		$this->readmore_element   	  = $this->readmore_default();
		$this->maxgrid_builder_settings = $this->maxgrid_builder_settings();
	}
	
	/**
	 * Max Grid Builder default settings. // MAXGRID_SETTINGS_OPT_NAME
	 *
	 * @return array
	 */
	public function maxgrid_builder_settings() {
		return array( 
			'download_logged_in' 	=> true,
			'login_page' 			=> '',
			're_download_count' 	=> false,
			're_download_timeout' 	=> 10,
			're_download_timeunits' => 'minutes',
			'vote_logged_in' 		=> false,
			'block_re_vote' 		=> true,
			're_vote_timeout' 		=> 24,
			're_vote_timeunits' 	=> 'hours',
			'youtube_api_key' 		=> '',
			'channel_id' 			=> '',
			'clear_cache_delay' 	=> 60,
			'delay_timeunits' 		=> 'minutes',
			'facebook' 				=> true,
			'twitter' 				=> true,
			'google' 				=> true,
			'blogger' 				=> true,
			'reddit' 				=> true,
			'tumblr' 				=> true,
			'pinterest' 			=> true,
			'vkontakte' 			=> true,
			'linkedin'		 		=> true,
			'email' 				=> true,
			'stumbleupon' 			=> true,
			'add_open_graph' 		=> true,
			'default_og_image' 		=> '',
			'dld_btn_bg_color' 		=> '#73c02a',
			'dld_btn_text_color' 	=> '#FFFFFF',
			'twttr_btn_size' 		=> 'medium',
			'twttr_count' 			=> 'none',
			'custom_base' 			=> '/download/',
			'form_title' 			=> __('Join the discussion', 'max-grid'),
			'comments_per_page' 	=> 6,
			'btn_bg_color' 			=> '#4d90fe',
			'btn_text_color' 		=> '#FFFFFF',
			'disable_ajax_form' 	=> false,
			'ratting_title' 		=> __('Your rating', 'max-grid'),
			'stars_color' 			=> '#F4B30A',
			'enable_recaptcha' 		=> true,
			'site_key'	 			=> '',
			'secret_key' 			=> '',
			'hide_for_logged' 		=> false,
			'captcha_theme' 		=> 'light',
			'color_theme' 			=> '#31c1eb',
			'extra-color-1' 		=> '#31c1eb',
			'extra-color-2' 		=> '#ffffff',
			'extra-color-3' 		=> '#ff1053',
			'extra-color-4' 		=> '#333333',
		);
	}
	
	/**
	 * Grid blocks default settings.
	 *
	 * @return array
	 */
	public function blocks_default() {
		return array(
				'margin_top' 					=> 10,
				'margin_right' 					=> 10,
				'margin_bottom' 				=> 10,
				'margin_left' 					=> 10,
				'border_top_width' 				=> 1,
				'border_right_width' 			=> 1,
				'border_bottom_width' 			=> 1,
				'border_left_width' 			=> 1,
				'padding_top' 					=> 10,
				'padding_right' 				=> 10,
				'padding_bottom' 				=> '',
				'padding_left' 					=> 10,
				'list_padding_left' 			=> 10,
				'list_padding_top' 				=> 10,
				'list_padding_right' 			=> 10,
				'list_padding_bottom' 			=> 10,
				'border_color' 					=> '#dfdfdf',
				'border_style' 					=> 'solid',
				'border_top_left_radius' 		=> '',
				'border_top_right_radius' 		=> '',
				'border_bottom_left_radius' 	=> '',
				'border_bottom_right_radius' 	=> '',
				'background' 					=> '#ffffff',
				'use_t_block_bg_tc' 			=> false,
				'box_shadow' 					=> 'none',
				'shadow_blur_radius' 			=> 4,
				'shadow_opacity' 				=> 10,
				'tooltip_style' 				=> 'light',
				'grid_container' 				=> false,
				'description_font_family' 		=> 'Default',
				'description_font_color' 		=> '#595959',
				'use_t_block_f_tc' 				=> false,
				'description_link_color' 		=> '#7a7a7a',
				'description_link_underline' 	=> false,
				'description_link_h_underline' 	=> false,
				'description_link_h_color' 		=> '#31c1eb',
				'use_extra_c1' 					=> true,
				'extra_c1' 						=> 'extra_color_1',
				'ribbon_type' 					=> 'corner-ribbon',
				'ribbon_pos' 					=> 'left',
				'ribbon_size' 					=> 'small',
				'warpped' 						=> true,
				'ribbon_top_pos' 				=> 30,
				'stuck_on_featured' 			=> false,
				'disable_newest_ribbon' 		=> true,
				'newest_txt' 					=> 'NEW',
				'newest_icon' 					=> 'fa-exclamation',
				'newest_color' 					=> '#8224e3',
				'disable_views_ribbon' 			=> true,
				'views_txt' 					=> 'POPULAR',
				'views_icon' 					=> 'fa-none',
				'views_color' 					=> '#25b64c',
				'disable_liked_ribbon' 			=> true,
				'liked_txt' 					=> 'MOST',
				'liked_icon' 					=> 'fa-heart-o',
				'liked_color' 					=> '#ed1b24',
				'disable_downloaded_ribbon' 	=> true,
				'downloaded_txt' 				=> 'MOST',
				'downloaded_icon' 				=> 'fa-download',
				'downloaded_color' 				=> '#1e5799',
				'onsale_txt' 					=> 'SALE!',
				'onsale_icon' 					=> 'fa-none',
				'onsale_color' 					=> '#f32744',
				'disable_bestseller_ribbon' 	=> true,
				'bestseller_txt' 				=> 'BEST SELLER',
				'bestseller_icon' 				=> 'fa-none',
				'bestseller_color' 				=> '#fb2c92',
				'description_font_family' 		=> 'Default',
				'description_font_link' 		=> 'Default',
			);
	}
	
	/**
	 * Grid Filter default settings.
	 *
	 * @return array
	 */
	public function filter_default() {
		return array(
			'add_cart_size' 	  => 'small',
			);
	}
	
	/**
	 * Lightbox default settings.
	 *
	 * @return array
	 */
	public function lightbox_default() {
		return array(
			'add_cart_size' 	  => 'small',
			'spin_layout' 		  => 'inner_spin',
			'border_radius' 	  => 'pointed',
			'sign_style' 		  => 'thick',
			'color_theme' 		  => '#dd3333',
			'use_extra_c1' 		  => false,
			'extra_c1' 			  => 'extra_color_1',
			'button_font_color'   => '#ffffff',
			'r_side_status' 	  => false,
			'overlay_click_close' => false,
			'like_dislike_links'  => false,
			'search_bar' 		  => true,
			'jquery_img_zoom' 	  => 'zoom_in',
			'theme' 	  		  => 'lb-dark-color',
			'tooltip_style' 	  => 'dark',
			'facebook' 			  => true,
			'twitter' 			  => true,
			'google' 			  => true,
			'blogger' 			  => true,
			'reddit' 			  => true,
			'tumblr' 		  	  => true,
			'pinterest' 		  => true,
			'vkontakte' 		  => true,
			'linkedin' 			  => true,
			'email' 			  => true,
			'stumbleupon' 		  => true,
			);
	}
	
	/**
	 * Summary element default settings.
	 *
	 * @return array
	 */
	public function summary_default() {
		return array(
			'excerpt_length' 	=> 18,
			'font_size' 		=> 12,
			'line_height' 		=> 20,
			'enable_extras' 	=> false,
			'background_color' 	=> '#ffffff',
			'text_color' 		=> '#595959',
			'text_h_color' 		=> '#31c1eb',
			'text_underline' 	=> false,
			'text_h_underline' 	=> false,
			'fit_width' 		=> false
			);
	}
	
	/**
	 * Youtube video description element default settings.
	 *
	 * @return array
	 */
	public function ytb_summary_default() {
		return array(
			'excerpt_length' 	=> 18,
			'font_size' 		=> 12,
			'line_height' 		=> 18,
			'enable_extras' 	=> false,
			'background_color' 	=> '#ffffff',
			'text_color' 		=> '#595959',
			'fit_width' 		=> false
			);
	}
	
	/**
	 * The Featured element default settings.
	 *
	 * @return array
	 */
	public function featured_default() {
		return array(
			'margin_top' 				=> '',
			'margin_bottom' 			=> '',
			'border_top_left_radius' 	=> '',
			'border_top_right_radius' 	=> '',
			'border_bottom_right_radius'=> '',
			'border_bottom_left_radius' => '',
			'border_left_width' 		=> '',
			'border_top_width' 			=> '',
			'border_right_width'		=> '',
			'border_bottom_width' 		=> '',
			'border_color' 				=> '#efefef',
			'border_style' 				=> 'solid',
			'hover_box_shadow' 		  	=> true,
			'hover_zoom_in' 		  	=> true,
			'filter' 				  	=> 'none',
			'blur_value' 			  	=> 5,
			'grayscale_value' 		  	=> 100,
			'hue-rotate_value' 		  	=> 90,
			'invert_value' 			  	=> 100,
			'sepia_value' 			  	=> 100,
			'reverse_filter' 		  	=> false,
			'button_font_color' 	  	=> '#ffffff',
			'button_font_hover_color' 	=> '#ffffff',
			'button_color' 				=> 'rgba(26,38,41,0.8)',
			'button_hover_color' 		=> '#31c1eb',
			'use_t_btn_bg_h_tc'			=> false,
			'use_extra_c1' 				=> true,
			'extra_c1' 					=> 'extra_color_1',
			'use_extra_c2' 				=> true,
			'extra_c2' 					=> 'extra_color_2',
			'love_this' 				=> false,
			'category' 					=> false,
			'download_btn' 				=> false,
			'custom_term_color' 		=> false,
			'dld_b_bg_c' 				=> '#2cba6c',
			'dld_b_f_c' 				=> '#ffffff',
			'bg_color_term' 			=> 'rgba(26,38,41,0.8)',
			'color_term' 				=> '#ffffff',
			'the_title' 				=> false,
			'post_excerpt' 				=> false,
			'post_stats' 				=> false,
			'share_this' 				=> true,
			'duration' 					=> true,
			'netw_facebook' 			=> true,
			'netw_twitter' 				=> true,
			'netw_google' 				=> true,
			'netw_blogger' 				=> false,
			'netw_reddit' 				=> false,
			'netw_tumblr' 				=> false,
			'netw_pinterest'			=> false,
			'netw_stumbleupon' 			=> false,
			'netw_email' 				=> false,
			'netw_vkontakte'			=> true,
			'netw_linkedin' 			=> true,
			'total_sales' 				=> true,
			'download_count' 			=> true,
			'views_count' 				=> true,
			'like_count' 				=> true,
			'published_at' 				=> true,
			'dislike_count' 			=> true,
			'background_overlay' 		=> 'rgba(0,0,0,0.5)',
			'use_t_ovl_bg_tc'			=> false,
			'color_overlay' 			=> '#ffffff',
			'use_t_ovl_f_tc'			=> false,
			'fillcover_overlay' 		=> false,
			'overlay_transition' 		=> 'slide_up',
			'external_link' 			=> true,
			'lightbox_link' 			=> true,
			'lightbox_icon' 			=> 'fa_fa-play',
			'insert_as' 				=> 'lightbox',
			'fit_width' 				=> false,
			'ovl_p_t' 					=> 10,
			'ovl_p_r' 					=> 10,
			'ovl_p_b' 					=> 10,
			'ovl_p_l' 					=> 10
			);
	}
		
	/**
	 * Audio player element default settings.
	 *
	 * @return array
	 */
	public function audio_player_default() {
		return array(
				'margin_top' 				=> 10,
				'margin_bottom' 			=> 10,
				'border_top_left_radius' 	=> 3,
				'border_top_right_radius' 	=> 3,
				'border_bottom_left_radius' => 3,
				'border_bottom_right_radius'=> 3,
				'bg_color' 					=> '#e4e4e4',
				'bg_image' 					=> '',
				'font_color' 				=> '#5e5e5e',
				'front_track_color' 		=> '#31c1eb',
				'use_extra_c1' 				=> true,
				'extra_c1' 					=> 'extra_color_1',
				'use_gradient' 				=> false,
				'track_grad_c1' 			=> '#ad73f8',
				'track_grad_c2' 			=> '#fc496b',
				'back_track_color' 			=> '#ffffff',
				'use_extra_c2' 				=> true,
				'extra_c2' 					=> 'extra_color_2',
				'fit_width' 				=> false,
				'apply_shadows' 			=> false,
			);
	}
		
	/**
	 * Title element default settings.
	 *
	 * @return array
	 */
	public function title_default() {
		return array(
				'nowrap' 			=> false,
				'link' 				=> 'external_link',
				'title_font_size' 	=> 14,
				'title_line_height' => 18,
				'title_color' 		=> '#3c3c3c',
				'use_t_title_f_tc' 	=> false,
				'title_h_color' 	=> '#31c1eb',
				'use_extra_c1' 		=> true,
				'extra_c1'			=> 'extra_color_1',
				'title_underline' 	=> false,
				'title_h_underline' => false,
				'title_font_family' => 'Default',
				'title_font_link' 	=> 'Default',
				'fit_width' 		=> false,
			);
	}
		
	/**
	 * Post meta element default settings.
	 *
	 * @return array
	 */
	public function post_meta_default() {
		return array(
				'margin_top' 		=> 3,
				'margin_bottom' 	=> 3,
				'padding_top' 		=> '',
				'padding_bottom' 	=> '',
				'font_size' 		=> 11,
				'enable_extras' 	=> false,
				'background_color' 	=> '#ffffff',
				'use_extra_c1' 		=> false,
				'extra_c1' 			=> 'extra_color_1',
				'text_color' 		=> '#595959',
				'text_underline' 	=> false,
				'text_h_color' 		=> '#31c1eb',
				'text_h_underline' 	=> false,
				'fit_width' 		=> false,
			);
	}
		
	/**
	 * Date bar default CSS.
	 *
	 * @return string
	 */
	public function datebar_css() {
		return '.info_row-container {margin-top: 3px;margin-bottom: 3px;padding-top: 0;padding-bottom: 0;font-size: 11px;}';
	}
		
	/**
	 * Post Stats bar default CSS.
	 *
	 * @return string
	 */
	public function statsbar_css() {
		return '.social-share-container-grid {margin-top: 5px;margin-bottom: 0;border-top-width: 1px;border-right-width: 0;border-bottom-width: 0;border-left-width: 0;padding-top: 3px;padding-right: 10px;padding-bottom: 3px;padding-left: 10px;border-color: #e0e0e0;border-style: solid;border-top-left-radius: 0;border-top-right-radius: 0;border-bottom-left-radius: 0;border-bottom-right-radius: 0;background: #f7f7f7;color: #777777;font-size: 12px !important;}.social-share-container-grid {margin-left: -10px;margin-right: -10px;}';
	}
		
	/**
	 * Post Stats element default settings.
	 *
	 * @return array
	 */
	public function stats_default() {
		return array(
				'margin_top' 				=> 5,
				'margin_bottom' 			=> '',
				'border_top_width' 			=> 1,
				'border_right_width' 		=> '',
				'border_bottom_width' 		=> '',
				'border_left_width' 		=> '',
				'padding_top' 				=> 3,
				'padding_right' 			=> 10,
				'padding_bottom' 			=> 3,
				'padding_left' 				=> 10,
				'border_color' 				=> '#e0e0e0',
				'use_t_stats_border_tc' 	=> false,
				'border_style' 				=> 'solid',
				'border_top_left_radius' 	=> '',
				'border_top_right_radius' 	=> '',
				'border_bottom_left_radius' => '',
				'border_bottom_right_radius'=> '',
				'background' 				=> '#f7f7f7',
				'use_t_stats_bg_tc' 		=> false,
				'font_color' 				=> '#777777',
				'use_t_stats_f_tc' 			=> false,
				'fit_width' 				=> true,
				'like_dislike_links'  		=> false,
			);
	}
	
	/**
	 * Add to cart element default settings.
	 *
	 * @return array
	 */
	public function add_to_cart_default() {
		return array(
				'margin_top' 		=> '',
				'margin_bottom' 	=> 15,
				'font_size' 		=> 35,
				'price_font_color' 	=> '#dd3333',
				'add_to_cart_label' => __('Add To Cart', 'max-grid'),
				'spin_layout' 		=> 'inner_spin',
				'border_radius' 	=> 'pointed',
				'sign_style' 		=> 'thin',
				'disable_qty' 		=> false,
				'color_theme' 		=> '#dd3333',
				'use_extra_c1' 		=> false,
				'extra_c1' 			=> 'extra_color_1',
				'button_font_color' => '#ffffff',
				'font_family' 		=> 'Default',
				'font_link' 		=> 'Default',
				'fit_width' 		=> false,
			);
	}
		
	/**
	 * Download element default settings.
	 *
	 * @return array
	 */
	public function download_default() {
		return array(
				'margin_top' 		=> 5,
				'margin_bottom' 	=> 5,
				'button_label'		=> __('Download', 'max-grid'),
				'button_position' 	=> 'left',
				'button_size' 		=> 'small',
				'fullwidth' 		=> false,
				'color_theme' 		=> '#7bcd2d',
				'use_extra_c1' 		=> false,
				'extra_c1' 			=> 'extra_color_1',
				'button_font_color' => '#ffffff',
				'fit_width' 		=> false,
			);
	}
		
	/**
	 * Average rating element default settings.
	 *
	 * @return array
	 */
	public function average_rating_default() {
		return array(
				'margin_top' 	=> '',
				'margin_bottom' => '',
				'description' 	=> __('reviews', 'max-grid'),
				'align' 		=> 'left',
				'stars_size' 	=> 'small',
				'stars_color' 	=> '#F4B30A',
				'fit_width' 	=> false,
			);
	}
		
	/**
	 * ShareThis element default settings.
	 *
	 * @return array
	 */
	public function sharethis_default() {
		return array(
				'facebook' 			  => true,
				'twitter' 			  => true,
				'google' 			  => true,
				'blogger' 			  => true,
				'reddit' 			  => true,
				'tumblr' 		  	  => true,
				'pinterest' 		  => true,
				'vkontakte' 		  => true,
				'linkedin' 			  => true,
				'email' 			  => true,
				'stumbleupon' 		  => true,
				'share_buttons_style' => 'popup_box',
				'facebook_list' 	  => true,
				'twitter_list' 		  => true,
				'google_list' 		  => true,
				'vkontakte_list' 	  => true,
				'linkedin_list' 	  => true,
			);
	}
		
	/**
	 * Read more element default settings.
	 *
	 * @return array
	 */
	public function readmore_default() {
		return array(
				'label_text'    	=> __('Read More', 'max-grid'),
				'btn_bg_color' 		=> '#31c1eb',
				'btn_bg_h_color'	=> '#09b6ea',
				'use_extra_c1' 		=> false,
				'extra_c1' 	   		=> 'extra_color_1',
				'btn_f_color' 		=> '#ffffff',
				'use_t_btn_f_h_tc' 	=> false,
				'btn_f_h_color' 	=> '#ffffff',
			);
	}
		
	/**
	 * Date options default settings.
	 *
	 * @return string
	 */
	public function date_options_default() {
		return 'date_format=F j, Y';
	}
		
	/**
	 * Divider element default settings.
	 *
	 * @return array
	 */
	public function divider_default() {
		return array(
				'margin_top' 	=> 5,
				'margin_bottom' => 5,
				'line_thickness' => 1,
				'line_type' 	=> 'full_line',
				'line_color' 	=> '#e5e5e5',
				'use_t_line_tc' => false,
				'fit_width' 	=> false,
			);
	}
}