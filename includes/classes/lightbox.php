<?php
/**
 * Max Grid Builder Caches
 */

/**
 * @class Max_Grid_Lightbox.
 */
class Max_Grid_Lightbox {
	
	/**
	 * Get Standard Post Content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 */
	public function get_post_content($args) {		
		$on_sale = '';
		if ( $args['post_type'] == 'product' ) {
			$_product = wc_get_product( $args['post_id'] );
			if($_product->get_sale_price()){
				$on_sale = '<div class="onsale">'.__( 'Sale!', 'max-grid' ).'</div>';
			}
		}
		$parms = array(
			'color'   => 'grey',
			'size'    => 'medium',
		);
		$spiner = maxgrid_lds_rolling_loader($parms);
		$content = '<div id="maxgrid_reach_content" class="maxgrid '.$args['post_type'].'">
					<div class="fake-slideshow next-el"><div>'.$spiner.'</div></div>
					<div class="fake-slideshow prev-el"><div>'.$spiner.'</div></div>';
		
		$audio_class = $args['audio_player'] != '' ? ' audio_player': '';
		$sd_class = $args['sd_player'] != '' ? ' sd_player': '';
		
		$content .= '<div id="download-'.$args['post_id'].'" class="post-'.$args['post_id'].' single-type-download status-'.$args['download_status'].' '.$args['has_thumbnail'].' download-type-'.$args['download_type'].'">
			<div class="single-download-row" style="width: 100%;">
			<!-- Left Column -->
			<div class="left-col single-download-main-image">
				<!--  Author Infos -->
				' . $args['heding_html'] . '
				<!-- end Author Infos -->
				<div class="video-wrapper '. $args['featured_type'] . $sd_class . $audio_class . ' pg_lightbox">';

				// Image Magnifier			
				if ( $args['magnify_type'] == 'magnify' ) {
					$magnify = '<div class="magnify-icon"></div>';
					$full = '';
				} else {
					$magnify = '<div class="full_magnify-icon">'. maxgrid()->template->get_svg_icon('fullscreen').maxgrid()->template->get_svg_icon('exits-fullscreen').'</div>';
					$full = ' full-magnify';
				}

				$file = $args['thumb_full'];
				$info = pathinfo($file);
				$file_name =  basename($file);

				$audio_player = maxgrid()->get_post_meta->grid($args['post_id'])['audio_player'];
				$audio_file   = maxgrid()->get_post_meta->grid($args['post_id'])['audio_file'];
				$sd_code      = wp_specialchars_decode(maxgrid()->get_post_meta->grid($args['post_id'])['soundcloud_code']);
		
				$imgs = '';
				if ( $audio_player == 'wp_player' && $audio_file != '' ) {
					$content .= $args['audio_player'];
				} else if ( $audio_player == 'soundcloud_player' && $sd_code != '' ) {
					$content .= $args['sd_player'];
				} else if ( $args['featured_type'] == "image") {
					if ( $args['post_type'] == 'product' && !empty(maxgrid_get_product_images_urls( $args['post_id'], 'thumbnail') ) ) {
						$imgs = '<div class="maxgrid_product-thumbs_slider">';
						$thumbs = maxgrid_get_product_images_urls( $args['post_id'], 'thumbnail');
						$full_img = maxgrid_get_product_images_urls( $args['post_id'], 'full');

						$imgs .= '<input type="radio" name="thumbs_switch" id="img_switch_1" checked/>
									  <label for="img_switch_1"><img src="'.$args['thumb_small'].'" class="maxgrid_product-thumbs" data-img-url="'.$file.'"/></label>';					
						$x=2;
						foreach( $thumbs as $key => $value ) {
							if(strpos($value, $args['thumb_small'])){
								continue;
							}
							$imgs .= '<input type="radio" name="thumbs_switch" id="img_switch_'.$x.'" />
									  <label for="img_switch_'.$x.'"><img src="'.$value.'" class="maxgrid_product-thumbs" data-img-url="'.$full_img[$key].'"/></label>';
							$x++;								
						}
						$imgs .= '</div>';
					}

					$content .= '<!-- Image magnifier -->						
								<div id="magnify" class="magnify' . $full . '">
									' . $on_sale . '
									' . $magnify . '
									<div class="e_large"></div>
									<img class="e_small" src="'.$args['thumb_full'].'" width="100%"/>
								</div>';						
				} else if ( $args['featured_type'] == "vimeo" ) {			
					$video_id = (int) substr(parse_url($args['data_href'], PHP_URL_PATH), 1);
					$content .= '<iframe src="https://player.vimeo.com/video/'.$video_id.'?title=0&byline=0&portrait=0" width="100%" height="auto" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				} else if ( $args['featured_type'] == "youtube" ) {
					parse_str( parse_url( $args['data_href'], PHP_URL_QUERY ), $my_array_of_vars );
					$video_id = $my_array_of_vars['v'];
					$content .= '<iframe id="ik_player_iframe" width="100%" height="100%" src="https://www.youtube.com/embed/'.$video_id.'?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
				} else if ( $args['featured_type'] == "mp4" ) {
					$content .= '<video id="my-video" class="video-js" controls preload="auto" width="100%" height="100%"
								  poster="'.$args['thumbnail_url'].'" data-setup="{}">
									<source src="'.$args['data_href'].'" type="video/mp4">
									<source src="'.preg_replace('"\.mp4$"', '.webm', $args['data_href']).'" type="video/webm">
								  </video>';
				} else {
					$content .= '<img src="'.$args['thumb'].'">';
				}				

				$content .= '</div>'.$imgs.'</div>';
				
				// Bottom Level		
				$content .= '<div class="modal-bottom-content">'.preg_replace('/<iframe.*?\/iframe>/i','', $args['bottom_content']).'</div>';

		$content .= '</div>';
		$content .= '</div></div>';
		$content .= '</div>';

		$response = $content;			
				
		return $response;
	}
	
}