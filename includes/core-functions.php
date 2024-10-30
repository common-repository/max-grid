<?php
/**
 * Max Grid Builder core functions.
 *
 */

use \MaxGrid\getPresets;

defined( 'ABSPATH' ) || exit;

/**
 * Check if WooCommerce is activated
 */
if ( ! function_exists( 'is_woocommerce_activated' ) ) {
	function is_woocommerce_activated() {
		if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
	}
}

/**
 * Remove Visual Composer short codes in post excerpt
 *
 * @return string
 */
function maxgrid_mailster_replace_VC_shortcodes_static( $data, $post ) {
    $shotcodes_tags = array( 'vc_row', 'vc_column', 'vc_column', 'vc_column_text', 'vc_message' );
    $data['content'] = preg_replace( '/\[(\/?(' . implode( '|', $shotcodes_tags ) . ').*?(?=\]))\]/', ' ', $data['content'] );
    return $data;
}
add_filter( 'mailster_auto_post', 'maxgrid_mailster_replace_VC_shortcodes_static', 10, 2);

/**
 * Remove Visual Composer short codes in post excerpt
 *
 * @return string
 */
function maxgrid_array_to_string($array){
	return implode('&', array_map(
			function ($v, $k) {
				$v = $v === true ? 'true' : $v;
				$v = $v === false ? 'false' : $v;
				return sprintf("%s=%s", $k, $v);
			},
			$array,
			array_keys($array)
		));
}
/**
 * URLs according to RFC 3986
 *
 * @return string
 */
function maxgrid_url_encode($url) {
    $entities = array('%3A','%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%2F', '%5C', '%25', '%23', '%5B', '%5D');
    $replacements = array(':','!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "\\", "?", "%", "#", "[", "]");
	
    return str_replace($entities, $replacements, $url);
}

/**
 * Get product images urls by product id
 *
 * @since 1.0.0
 *
 * @param int 	 $product_id Product ID
 * @param string $size Image Size
 *
 * @return string
 */
function maxgrid_get_product_images_urls( $product_id, $size='full') {
	$product = new WC_Product($product_id);
	$attachmentIds = $product->get_gallery_image_ids();
	$imgUrls = array();
	foreach( $attachmentIds as $attachmentId )	{
		// Sizes : full, medium & thumbnail
		$imgUrls[] = wp_get_attachment_image_src($attachmentId, $size)[0];		
	}
	return $imgUrls;
}

/**
 * Get current Screen ID
 * function to get the current page screen
 *
 * @return string
 */
function maxgrid_current_screen() {
	global $wp_scripts;
	
	$screen = get_current_screen();
	return $screen ? $screen->id : '';
}

/**
 * Get Meta Options
 *
 * @return array
 */
function maxgrid_get_meta_options() {

	global $post;
	
	//$the_excerpt_option = (isset($post->ID)) ? get_post_meta($post->ID, 'maxgrid_the_excerpt', true) : '';
	
	$embed_vimeo_video_url = (isset($post->ID)) ? get_post_meta($post->ID, 'maxgrid_embed_vimeo_url', true) : '';
	$embed_youtube_video_url = (isset($post->ID)) ? get_post_meta($post->ID, 'maxgrid_embed_youtube_url', true) : '';
	$embed_mp4_video_url = (isset($post->ID)) ? get_post_meta($post->ID, 'maxgrid_embed_mp4_url', true) : '';
	$thumbnail_url = (isset($post->ID)) ? get_post_meta($post->ID, 'maxgrid_thumbnail_url', true) : '';
	
	$meta_options = array(
		//'the_excerpt_option' => $the_excerpt_option,
		'maxgrid_embed_vimeo_url' => $embed_vimeo_video_url,
		'maxgrid_embed_youtube_url' => $embed_youtube_video_url,
		'maxgrid_embed_mp4_url' => $embed_mp4_video_url,
		'maxgrid_thumbnail_url' => $thumbnail_url,
	);

	return $meta_options;
}

/**
 * Get main Options
 *
 * @param string $preset_name Optional. preset name to extract options from preset.
 * @param string $post_type  Optional. Post to extract options from preset.
 *
 * @return array
 */
function maxgrid_get_options($preset_name='', $post_type='') {
	global $post;
	
	$get_options = maxgrid()->get_options;
		
	$review_stars_color = isset($get_options->option('forms')['stars_color']) ? $get_options->option('forms')['stars_color'] : '#F4B30A';
	
	$args = array('source_type' => $post_type, 'preset_name' => $preset_name);
	$get_presets = new getPresets($args);
	
	// Ajax Comment Option
	$general_options = $get_options->option('general_options');	
	$forms_options = $get_options->option('forms');	
	$extras_options = $get_options->option('extras_options');
	
	$cpp = isset($forms_options['comments_per_page']) ? $forms_options['comments_per_page'] : 6;
	$btn_bg_color = isset($forms_options['btn_bg_color']) ? $forms_options['btn_bg_color'] : '#4d90fe';
	$btn_text_color = isset($forms_options['btn_text_color']) ? $forms_options['btn_text_color'] : '#fff';
	$comment_form_title = isset($forms_options['form_title']) ? $forms_options['form_title'] : __( 'Join the discussion', 'max-grid' );
	
	$comments_order = get_option('comment_order');// DESC - ASC
	
	// reCAPTCHA Options
	$captcha_options = isset(get_option(MAXGRID_SETTINGS_OPT_NAME)['forms']) ? get_option(MAXGRID_SETTINGS_OPT_NAME)['forms'] : array();
	$enable_recaptcha = isset($captcha_options['enable_recaptcha']) ? true : false;
	
	$hide_to_logged = isset($captcha_options['hide_for_logged']) ? true : false;		
	if ( $hide_to_logged == true && is_user_logged_in() ) {
		$enable_recaptcha = false;
	}

	// Custom Download Post & Custom CSS code
	$twttr_btn_size  = isset($general_options['twttr_btn_size']) ? $general_options['twttr_btn_size'] : 'medium';
	$twttr_count 	 = isset($general_options['twttr_count']) ? $general_options['twttr_count'] : 'none';
	$twttr_screen_name = isset($general_options['twttr_screen_name']) ? $general_options['twttr_screen_name'] : 'true';
	
	$custom_css_code = null !== $get_options->option('custom_css_area') ? $get_options->option('custom_css_area') : '';
		
	$average_rating_options = $get_presets->rows('average_rating_row');
	$stars_color    = isset($average_rating_options['stars_color']) ? $average_rating_options['stars_color'] : '#F4B30A';
	
	$extra_color_1  = isset(get_option(MAXGRID_SETTINGS_OPT_NAME)['extras_options']['extra-color-1']) ? get_option(MAXGRID_SETTINGS_OPT_NAME)['extras_options']['extra-color-1'] : '#31c1eb';
	$extra_color_2  = isset(get_option(MAXGRID_SETTINGS_OPT_NAME)['extras_options']['extra-color-2']) ? get_option(MAXGRID_SETTINGS_OPT_NAME)['extras_options']['extra-color-2'] : '#ffffff';
	$extra_color_3  = isset(get_option(MAXGRID_SETTINGS_OPT_NAME)['extras_options']['extra-color-3']) ? get_option(MAXGRID_SETTINGS_OPT_NAME)['extras_options']['extra-color-3'] : '#ff1053';
	$extra_color_4  = isset(get_option(MAXGRID_SETTINGS_OPT_NAME)['extras_options']['extra-color-4']) ? get_option(MAXGRID_SETTINGS_OPT_NAME)['extras_options']['extra-color-4'] : '#333333';
	
	$default_home_grid = get_option('default_home_grid') ? get_option('default_home_grid') : 'post-grid';
		
	$post_grid_options = array(
		'enable_recaptcha' 	 => $enable_recaptcha,
		'extra_color_1' 	 => $extra_color_1,
		'extra_color_2' 	 => $extra_color_2,
		'extra_color_3' 	 => $extra_color_3,
		'extra_color_4' 	 => $extra_color_4,
		'default_home_grid'  => $default_home_grid,
		'comment_form_title' => $comment_form_title,
		'cpp' 				 => $cpp,
		'btn_bg_color' 		 => $btn_bg_color,
		'btn_text_color' 	 => $btn_text_color,
		'comments_order' 	 => $comments_order,
		'review_stars_color' => $review_stars_color,
		'stars_color' 		 => $stars_color,
		'twttr_btn_size' 	 => $twttr_btn_size,
		'twttr_count' 		 => $twttr_count,
		'twttr_screen_name'  => $twttr_screen_name,
		'custom_css_code'    => $custom_css_code,
	);

	return $post_grid_options;
}

/**
 * Get the most "viewed, downloaded, liked" posts, 
 * Note: That include the excluded catégories
 *
 * @param array $data.
 *
 * @return int
 */
function maxgrid_get_the_most($data) {
	$post_type = $data['post_type'];
	$taxonomy = [
		MAXGRID_POST => array(
			'taxonomy' => MAXGRID_CAT_TAXONOMY,
			'tag_meta_name' => MAXGRID_TAG_TAXONOMY,
		),
		'product' => array(
			'taxonomy' => 'product_cat',
			'tag_meta_name' => 'product_tag',
		),
		'post' => array(
			'taxonomy' => 'category',
			'tag_meta_name' => 'post_tag',
		),
	];

	if ($data['orderby'] == 'date') {
		$orderby = 'date';
		$meta_key = '';
	} else{
		$orderby = 'meta_value_num';	
		$meta_key = $data['orderby'];
	}

	$args=array(
			'post_type'	  	 => $post_type,
			'post_status' 	 => 'publish',
			'orderby'      	 => $orderby,  /* this will look at the meta_key you set below */
			'meta_key'    	 => $meta_key,
			'order'          => 'DESC',
			'posts_per_page' => 1,
			);
	/*
	$args['tax_query'] = array(
					array(
						'taxonomy' => $taxonomy[$post_type]['taxonomy'],
						'field'    => 'slug',
						'terms'    => $data['exclude'],
					),
			);*/
	$query = new WP_Query($args);
	$post_id = null;
	while ($query->have_posts()) : $query->the_post();
		$post_id = get_the_ID();
	endwhile;
	wp_reset_postdata();
	return 	$post_id;
}

/**
 * Get Author name - Get full name if is it available, else get nickname.
 *
 * @param int $author_id Author ID.
 * @param int $limit Total number of characters allowed, if greater return nickname.
 *
 * @return string
 */
function maxgrid_get_author($author_id, $limit) {
	$fname = get_the_author_meta('first_name', $author_id );
	$lname = get_the_author_meta('last_name', $author_id );
	$nickname = get_the_author_meta('display_name', $author_id);
	$full_name = '';
	if( empty($fname)){
		$full_name = $lname;
	} else if( empty( $lname )){
		$full_name = $fname;
	} else if( empty( $lname) && (empty($fname) )){
		$full_name = $nickname;
	} else {
		$full_name = "{$fname} {$lname}".'';
	}
	if( strlen ( $full_name ) > $limit ){
		$full_name = $nickname;
	}
	if( $full_name == '' ){
		$full_name = $nickname;
	}
	return $full_name;
}

// Add script filter
function maxgrid_add_script_filter( $string ) {
	global $allowedtags;
	$allowedtags['script'] = array( 'src' => array () );
	return $string;
}
add_filter( 'pre_kses', 'maxgrid_add_script_filter' );

/**
 * Use custom post meta key
 *
 * @param string $key Meta key.
 * @return string
 */
function maxgrid_use_custom_post_meta_key( $key ) {
	// Custom post meta
	$get_options 	= maxgrid()->get_options;
	$track_options 	= $get_options->option(MAXGRID_LOGS_OPT_NAME);
	$use_custom_meta = isset($track_options['use_custom_meta_'.$key]) && ( maxgrid_string_to_bool($track_options['use_custom_meta_'.$key]) == 1 || $track_options['use_custom_meta_'.$key] == 'use_custom_meta_'.$key ) ? true : null;
	if ( $use_custom_meta && isset($track_options['custom_meta_'.$key]) && $track_options['custom_meta_'.$key] != '' ) {
		return $track_options['custom_meta_'.$key];
	} else {
		return null;
	}
}

/**
 * Determine if a meta key is set for a given object
 *
 * @param string $meta_key Meta key.
 * @return bool
 */
function maxgrid_metadata_exists( $meta_key ) {	
	global $wpdb;
	$results = $wpdb->get_results( "SELECT meta_key FROM wp_postmeta where meta_key='$meta_key'" );
	if ( count($results) > 0 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * MaxGrid Term Meta API
 *
 * @param int    $term_id Term ID.
 * @param string $key     Meta key.
 * @param bool   $single  Whether to return a single value. (default: true).
 * @return mixed
 */
function get_maxgrid_term_meta( $term_id, $key, $single = true ) {
	return function_exists( 'get_term_meta' ) ? get_term_meta( $term_id, $key, $single ) : get_metadata( 'maxgrid_term', $term_id, $key, $single );
}

/**
 * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
 *
 * @return bool
 */
function maxgrid_is_mobile(){
	return wp_is_mobile() || ( isset( $_GET['is_mobile'] ) && $_GET['is_mobile'] == 'on') ? true : null;
}

/**
 * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
 *
 * @return bool
 */
function maxgrid_is_product(){
	return class_exists( 'WooCommerce' ) && is_product();
}

/**
 * Check if Max Grid is activated
*/
function is_maxgrid_activated() {
	if ( class_exists( 'Max_Grid' ) ) { return true; } else { return false; }
}

/**
 * Check if Max Grid Premium is activated
*/
function is_maxgrid_premium_activated() {
	if ( class_exists( 'Max_Grid_Premium' ) ) { return true; } else { return false; }
}

/**
 * Check if Max Grid Woo is activated
*/
function is_maxgrid_woo_activated() {
	if ( class_exists( 'Max_Grid_Woo' ) && class_exists( 'WooCommerce' ) ) { return true; } else { return false; }
}

/**
 * Check if Max Grid Download is activated
*/
function is_maxgrid_download_activated() {
	if ( class_exists( 'Max_Grid_Download' ) ) { return true; } else { return false; }
}

/**
 * Check if Max Grid Youtube Stream is activated
*/
function is_maxgrid_youtube_activated() {
	if ( class_exists( 'Max_Grid_Youtube' ) ) { return true; } else { return false; }
}

/**
 * Check if Max Grid Youtube Stream is activated
*/
function is_maxgrid_templates_library() {
	return is_maxgrid_premium_activated() || is_maxgrid_woo_activated() || is_maxgrid_download_activated() || is_maxgrid_youtube_activated() ? true : false;
}

/**
 * Max Grid Premium required note
 */
function maxgrid_premium_require_note($site_url) {	
	return '<a href="'.$site_url.'/max-grid-premium-add-on/" class="mgpremium-require__note" target="_blank">[Get Premium]</a>';
}

/**
 * Get available source types
*/
function maxgrid_available_source_types() {
	$sources_type = ['post'];
	if ( is_maxgrid_download_activated() ) {
		$sources_type = ['download'];
	}
	if ( is_maxgrid_woo_activated() ) {
		$sources_type = ['product'];
	}
	if ( is_maxgrid_youtube_activated() ) {
		$sources_type = ['youtube_stream'];
	}
	return $sources_type;
}

/**
 * Debug function
 *
 * @param array $data.
 *
 * @return array
 */
function maxgrid_debug($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
	}