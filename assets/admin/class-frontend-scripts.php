<?php
/**
 * Handle frontend scripts
 *
 * @version     1.0.0
 */

use \MaxGrid\getPresets;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend scripts Class.
 */
class Max_Grid_Frontend_Scripts {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'wp_footer', array( __CLASS__, 'max_footer_styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dynamic_css' ) );
	}

	/**
	 * Scripts enqueue.
	 */
	public static function load_scripts($post) {
		
		// WP Media Element		
		if ( is_single() ) {
			global $post;			
			wp_enqueue_script( 'maxgrid-builder-set-meta-count', MAXGRID_ABSURL . '/includes/js/set-meta-views-count.js', array(), null, true );
			wp_enqueue_style( 'maxgrid-builder-set-meta-count' );
			wp_localize_script(
				'maxgrid-builder-set-meta-count',
				'views_const',
				array( 
					  'post_id' 	=> $post->ID,
					  'meta_key' 	=> is_maxgrid_premium_activated() ? MAXGRID_VIEWS_META_KEY	: '',		  
					 ) 
		);
		}
		
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script('wp-mediaelement');

		wp_register_style('maxgrid_wp-mediaelement', maxgrid()->plugin_url() . '/includes/css/wp-mediaelement.css');
		wp_enqueue_style('maxgrid_wp-mediaelement');
		
		extract(maxgrid_get_options());
		
		$get_options = maxgrid()->get_options;
		$opt_arry 	 = $get_options->option(MAXGRID_LOGS_OPT_NAME);
		$login_page	 = is_array($opt_arry) && array_key_exists('login_page', $opt_arry) ? $opt_arry['login_page'] : get_home_url().'/wp-login.php';		
		$options 	 = isset(get_option(MAXGRID_SETTINGS_OPT_NAME)['forms']) ? get_option(MAXGRID_SETTINGS_OPT_NAME)['forms'] : array();
		$recaptcha 	 = isset($options['enable_recaptcha']) && $options['enable_recaptcha'] == 'enable_recaptcha' ? 'recaptcha' : 'no_recaptcha';
		
		wp_enqueue_script( 'maxgrid-grid-js', MAXGRID_ABSURL . '/includes/js/grid.js', array('jquery'));
		wp_enqueue_script( 'maxgrid-builder-comments', MAXGRID_ABSURL . '/includes/js/comments.js', array( 'jquery' ));
		wp_register_script( 'maxgrid-builder-core-functions', MAXGRID_ABSURL . '/assets/js/core-functions.js', array( 'jquery' ) );
		wp_localize_script(
				'maxgrid-builder-core-functions',
				'Const',
				array(
					  	'ajaxurl'  				=> admin_url( 'admin-ajax.php' ),
					  	'maxgrid_ytb' 			=> is_maxgrid_youtube_activated() ? true : null,
						'maxgrid_premium' 			=> is_maxgrid_premium_activated() ? true : null,
					  	'view_replies_label' 	=> __( 'View all %s replies', 'max-grid' ),
					  	'view_replay_label' 	=> __( 'View reply', 'max-grid' ),
					  	'hide_replay_label' 	=> __( 'Hide replies', 'max-grid' ),
					  	'conflict_error' 		=> __( 'Conflict detected.', 'max-grid' ),
					  	'load_more' 			=> __( 'Load More', 'max-grid' ),
					  	'share_label' 			=> __( 'share', 'max-grid' ),
					  	'thx_msg' 				=> __( 'Thanks for your comment. We appreciate your response.', 'max-grid' ),
					  	'unknown_error' 		=> __( 'An error occurred while processing your form.', 'max-grid' ),
					  	'recaptcha' 			=> $recaptcha,
					  	'captcha_error' 		=> __( 'Please check if you\'ve completed the captcha security check.', 'max-grid' ),
					  	'empty_name' 			=> __( 'Please enter your name.', 'max-grid' ),
					  	'empty_email' 			=> __( 'Please enter your email.', 'max-grid' ),
					  	'invalid_email' 		=> __( 'Please enter a valid e-mail address.', 'max-grid' ),
					  	'empty_rating' 			=> __( 'Please choose a rating.', 'max-grid' ),
						'empty_comment' 		=> __( 'Please provide a detailed description or comment.', 'max-grid' ),
					  	'no_more_result' 		=> __( 'There are no more results to show.', 'max-grid' ),
					  	'twttrSize' 			=> $twttr_btn_size,
						'twttrCount' 			=> $twttr_count,
					  	'twttrScreenName' 		=> $twttr_screen_name,
					  	'be_logged_in_alert' 	=> __('You must be logged in to download this content.', 'max-grid'),
					  	'login_page' 		 	=> $login_page,
					  	'is_user_logged_in' 	=> is_user_logged_in(),	  
					  	'copy' 					=> __( 'copy', 'max-grid' ),
					  	'copied' 				=> __( 'copied', 'max-grid' ),
					 ) 
		);
		
		wp_register_script( 'maxgrid-builder-lightbox', MAXGRID_ABSURL . '/assets/common/lightbox/lightbox.js', array('jquery'), NULL, false);		
		wp_register_script( 'maxgrid-builder-google-platform', 'https://apis.google.com/js/platform.js', array('jquery'), null, true );
		wp_register_script( 'chosen', MAXGRID_ABSURL . '/assets/lib/chosen/chosen.jquery.min.js', array( 'jquery' ), NULL, false );
		wp_register_script( 'maxgrid-builder-chosen-init', MAXGRID_ABSURL . '/assets/lib/chosen/chosen.init.js', array( 'jquery' ), NULL, false );
		wp_register_script( 'maxgrid-builder-nicescroll', MAXGRID_ABSURL . '/assets/lib/nicescroll-master/jquery.nicescroll.min.js', array( 'jquery' ), NULL, false );			
		wp_register_script( 'maxgrid-builder-tooltip', MAXGRID_ABSURL . '/assets/common/tooltip/tooltip.js', array( 'jquery' ), NULL, false );
		wp_enqueue_script( 'maxgrid-builder-lightbox' );
		wp_enqueue_script( 'maxgrid-builder-core-functions' );
		wp_enqueue_script( 'maxgrid-builder-google-platform' ) ;
		wp_enqueue_script( 'chosen' );
		wp_enqueue_script( 'maxgrid-builder-chosen-init' );
		wp_enqueue_script( 'maxgrid-builder-nicescroll' );
		wp_enqueue_script( 'maxgrid-builder-tooltip' );

		//Pull Masonry
		wp_register_script( 'masonry', MAXGRID_ABSURL . '/assets/lib/masonry.pkgd.min.js', array( 'jquery' ), '4.2.2', false );
		wp_enqueue_script( 'masonry' );

		// SweetAlert
		wp_register_script( 'sweetalert', MAXGRID_ABSURL . '/assets/lib/sweetalert/sweetalert.min.js', array( 'jquery' ), NULL, false );
		wp_enqueue_script( 'sweetalert' );

		wp_register_style( 'maxgrid-builder-dialog-style', MAXGRID_ABSURL . '/assets/css/dialog.css');
		wp_enqueue_style( 'maxgrid-builder-dialog-style' );
		
		// CSS enqueue
		wp_register_style( 'chosen',MAXGRID_ABSURL . '/assets/lib/chosen/chosen.min.css' );
		wp_register_style( 'maxgrid-builder-comments',MAXGRID_ABSURL . '/includes/css/comments.css' );
		wp_register_style( 'maxgrid-builder-styles',MAXGRID_ABSURL . '/includes/css/styles.css' );
		wp_register_style( 'maxgrid-builder-tooltip',MAXGRID_ABSURL . '/assets/common/tooltip/tooltip.css' );
		wp_register_style( 'maxgrid-builder-icomoon', maxgrid()->plugin_url() . '/assets/lib/icomoon/style.css');
		wp_register_style( 'maxgrid-builder-buttons', MAXGRID_ABSURL . '/assets/css/buttons.css');
		wp_register_style( 'maxgrid-builder-lightbox', MAXGRID_ABSURL . '/assets/common/lightbox/lightbox.css');
		wp_register_style( 'maxgrid-builder-ajax-spinner', MAXGRID_ABSURL . '/assets/css/ajax-spinner.css');
		wp_register_style( 'maxgrid-builder-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		wp_register_style( 'maxgrid-builder-ribbon', MAXGRID_ABSURL . '/includes/css/ribbons.css');
		
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'chosen' );
		wp_enqueue_style( 'maxgrid-builder-comments' );
		wp_enqueue_style( 'maxgrid-builder-styles' );
		wp_enqueue_style( 'maxgrid-builder-tooltip' );
		wp_enqueue_style( 'maxgrid-builder-icomoon' );
		wp_enqueue_style( 'maxgrid-builder-buttons' );
		wp_enqueue_style( 'maxgrid-builder-lightbox' );
		wp_enqueue_style( 'maxgrid-builder-ajax-spinner' );
		wp_enqueue_style( 'maxgrid-builder-font-awesome' );
		wp_enqueue_style( 'maxgrid-builder-ribbon' );
	}
	
	/**
	 * Footer styles enqueue.
	 */
	public static function max_footer_styles(){
		extract(maxgrid_get_options());
		?>
		<style type="text/css">
		/*-------------------------------------------------------------------------*/
		/*	Max Grid Builder Styles
		/*-------------------------------------------------------------------------*/
		<?php			
		echo str_replace(MAXGRID_CSS_CODE_COMMENT, '', $custom_css_code) . "\n";
		?>
		</style>

		<?php
	}
	
	/**
	 * Dynamic CSS enqueue.
	 */
	public static function dynamic_css() {
		extract(maxgrid_get_options());

		$get_presets = new getPresets('');
		$a_p = $get_presets->rows('audio_row');	
		?>
		<style type="text/css">
			<?php
			if ( $enable_recaptcha ) {
			?>/* Display Double submite comment button */
			#commentform #submit:not(.captcha-submit), #commentform .form-submit:not(.e_pg_lightbox) {
				display: none;
			}
			<?php
			}
			?>/*-------------------------------------------------------------------------*/
			/*	Max Grid Builder Dynamic Style
			/*-------------------------------------------------------------------------*/

			.product-list_read-more-btn:hover, .ul-thumbs-list .load-more:hover {
				background-color: <?php echo $extra_color_1;?>;
				color: <?php echo $extra_color_2;?>;
			}
			body li.playlist-load-more-container .no-more-result {
				color: <?php echo $extra_color_1;?>!important;
			}
			.load-more.ligthbox-playlist:hover .dots-rolling span {
				background: <?php echo $extra_color_2;?>!important;
			}

			/*-------------------------------------------------------------------------*/
			/*	Post Like CSS
			/*-------------------------------------------------------------------------*/

			.like:hover {
				color: <?php echo $extra_color_1;?>!important;
			}
			.post-like:hover a .like, .single-post-like:hover > span > a .like {
				color: <?php echo $extra_color_1;?>!important;
			}
			.post-like:hover a.alreadyvoted .like, .single-post-like:hover > span > a.alreadyvoted  .like {
				color: #e74b4b!important;
			}

			/* Social Share CSS */
			.social-share-container-grid .maxgrid_share.blogger:hover svg path, .social-share-container-grid .maxgrid_share.blogger:focus svg path,
			.social-share-container-list .maxgrid_share:hover svg path, .social-share-container-list .maxgrid_share:focus svg path,
			.float-share-bar .maxgrid_share.blogger:hover svg path {
				fill: <?php echo $extra_color_1;?>!important;
			}
			.social-share-container-grid .maxgrid_share:hover, .social-share-container-grid .maxgrid_share:focus,
			.social-share-container-list .maxgrid_share:hover, .social-share-container-list .maxgrid_share:focus,
			.float-share-bar .maxgrid_share:hover, .float-share-bar .maxgrid_share:focus {
				color: <?php echo $extra_color_1;?>!important;
			}
			.download_meta span a:link:hover {
				color: <?php echo $extra_color_1;?>!important;
			}

			/*-------------------------------------------------------------------------*/
			/*	Post Like CSS
			/*-------------------------------------------------------------------------*/
			
			.icon-cross:hover, .icon-cross:focus,
			li.li-post-description span:last-of-type {
				color: <?php echo $extra_color_1;?>!important;
			}

			li.li-post-thumb.ishover,
			.playlist-tab-content ul li.li-post-thumb.active,
			.playlist-slider_container ul li.li-post-thumb.active, li.li-post-thumb:hover {
				border-color: <?php echo $extra_color_1;?>;
			}
			.fullscreen-icon:hover path, .open-slideshow:hover path, .full_magnify-icon:hover path {
				fill: <?php echo $extra_color_1;?>;
			}
			body.light-color #slider_nicescroll_rails .nicescroll-cursors:hover {
				background: <?php echo $extra_color_1;?> !important;
			}
			
			/*-------------------------------------------------------------------------*/
			/*	Review Star Ratings
			/*-------------------------------------------------------------------------*/

			.e_comment-form-rating .highlight, .e_comment-form-rating .selected {
				color: <?php echo $review_stars_color;?>!important;
				text-shadow: 0 0 1px <?php echo maxgrid_colourBrightness($review_stars_color, -0.85);?>!important;
			}
			.star-ratings-sprite-rating:before {
				color: <?php echo $review_stars_color;?>!important;
			}
			
			/*-------------------------------------------------------------------------*/
			/*	Submit Button - Aiax comment form
			/*-------------------------------------------------------------------------*/
			.maxgrid input[type="button"]#ajax_submit {
				background-color: <?php echo $btn_bg_color;?>;
				background-image: -moz-linear-gradient(top,<?php echo $btn_bg_color;?>,<?php echo maxgrid_colourBrightness($btn_bg_color, -0.85);?>);
				background-image: linear-gradient(to top,<?php echo $btn_bg_color;?>,<?php echo maxgrid_colourBrightness($btn_bg_color, -0.85);?>);
				border: 1px solid <?php echo maxgrid_colourBrightness($btn_bg_color, -0.75);?>;
				color: <?php echo $btn_text_color;?>;
			}
			
			body #maxgrid-replay-to.active {
				background: <?php echo $extra_color_1;?>!important;
			}
			/*-------------------------------------------------------------------------*/
			/*	WP Audio Player
			/*-------------------------------------------------------------------------*/

			/* WP 3.6 Native Audio Player styling*/
			.maxgrid-audio-player-row {
				margin-top: <?php echo isset($a_p['margin_top'])?$a_p['margin_top']:'10';?>px;
				margin-bottom: <?php echo isset($a_p['margin_bottom'])?$a_p['margin_bottom']:'10';?>px;
			}

			/* change the color of the background */
			.mejs-controls,
			.mejs-mediaelement,
			.mejs-container {
				background: url(<?php echo isset($a_p['bg_image'])? maxgrid_url_encode($a_p['bg_image']):'';?>)!important;
				background-color: <?php echo isset($a_p['bg_color'])?$a_p['bg_color']:'#464646';?>!important;
				border-top-left-radius: <?php echo isset($a_p['border_top_left_radius'])?$a_p['border_top_left_radius']:'3';?>px!important;
				border-top-right-radius: <?php echo isset($a_p['border_top_right_radius'])?$a_p['border_top_right_radius']:'3';?>px!important;
				border-bottom-left-radius: <?php echo isset($a_p['border_bottom_left_radius'])?$a_p['border_bottom_left_radius']:'3';?>px!important;
				border-bottom-right-radius: <?php echo isset($a_p['border_bottom_right_radius'])?$a_p['border_bottom_right_radius']:'3';?>px!important;
			}

			/* change the color of the current horizontal volume */
			.mejs-controls .mejs-time-rail .mejs-time-current,
			.mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-current {
				background-color: <?php echo isset($a_p['front_trak_color'])?$a_p['front_trak_color']:'#31c1eb';?> !important;
			}
			.mejs-time-handle-content {
				border: 4px solid <?php echo isset($a_p['front_trak_color'])?maxgrid_hex_to_rgb($a_p['front_trak_color'], 0.8 ): 'rgba(49,193,235,0.8)';?>!important;
			}
			.mejs-time-hovered {
				background: <?php echo isset($a_p['front_trak_color'])?maxgrid_hex_to_rgb($a_p['front_trak_color'], 1 ): 'rgba(49,193,235, 1)';?>!important;
			}
			.mejs-controls .mejs-time-rail .mejs-time-total {
				background-color: <?php echo isset($a_p['back_trak_color'])?maxgrid_hex_to_rgb($a_p['back_trak_color'], 0.7 ) : 'rgba(255,255,255, 0.7)';?>!important;
			}
			.mejs-controls .mejs-time-rail .mejs-time-loaded {
				background-color: <?php echo isset($a_p['back_trak_color'])?maxgrid_hex_to_rgb($a_p['back_trak_color'], 0.6 ) : 'rgba(255,255,255, 0.7)';?>!important;
			}
			.mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-total {
				background: <?php echo isset($a_p['back_trak_color'])?maxgrid_hex_to_rgb($a_p['back_trak_color'], 0.7 ): 'rgba(255,255,255, 0.7)';?>!important;
			}

			/* change the color of the lettering */
			body .mejs-controls .mejs-button button,
			.mejs-currenttime,
			.mejs-duration {
				color: <?php echo isset($a_p['font_color'])?$a_p['font_color']:'#d2d2d2';?>!important;
			}

			/* Pause Button */
			body .mejs-controls .mejs-pause button {
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

			/*-------------------------------------------------------------------------*/
			/*	Ajax Response + Loading Spinner
			/*-------------------------------------------------------------------------*/
			.small .lds-rolling div, .small .lds-rolling div:after,
			.medium .lds-rolling div, .medium .lds-rolling div:after,
			.large .lds-rolling div, .large .lds-rolling div:after,
			.biggest .lds-rolling div, .biggest .lds-rolling div:after {
				border-color: <?php echo $extra_color_1;?>;
			}
			.inner-ball, .outer-ball {
				border-color: <?php echo $extra_color_1;?>!important;
				border-top: 5px solid rgba(0,0,0,0)!important;
			}
			.inner-ball {
				box-shadow: 0 0 15px <?php echo $extra_color_1;?>!important;
			}
			.outer-ball {
				box-shadow: 0 0 35px <?php echo $extra_color_1;?>!important;
			}

			/*-------------------------------------------------------------------------*/
			/*	Lightbox - Navigations
			/*-------------------------------------------------------------------------*/
			#next:hover .next-arrow path, #prev:hover .prev-arrow path {
				fill: <?php echo $extra_color_1;?>!important;
			}
			body.lb-light-color .nav-in-top span.icon-cross:hover,
			body.lb-light-color .ismobile-nav span.icon-cross:hover,
			.lb-light-color .ismobile-nav .navigation-arrow.isvisible:hover,
			.ismobile-nav .navigation-arrow.isvisible:hover, body .pg_light_theme.nav-in-top span.icon-cross:hover {
				color: <?php echo $extra_color_1;?>!important;
			}
			.lb-light-color .nav-in-top .pg_lightbox-toolbar svg:hover path,
			.lb-light-color .ismobile-nav svg:hover path {
				fill: <?php echo $extra_color_1;?>!important;!important;
			}
		   </style>
		<?php	
	}
}

Max_Grid_Frontend_Scripts::init();