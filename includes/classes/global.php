<?php
/**
 * Additional grid elements.
 */

namespace MaxGrid;

defined( 'ABSPATH' ) || exit;

/**
 * @class Template.
 */
class Template {
	
	/**
	 * @var string $time_format Date format.
	 */	
	public $date_format;
	
	/**
	 * @var string $time_format Time format.
	 */	
	public $time_format;
	
	/**
     * Constructor.
	 */
	public function __construct() {
		$this->date_format = __( 'F j, Y', 'max-grid' );
		$this->time_format = __( 'h:ia', 'max-grid' );
	}
	
	/**
     * Date-Time format.
     *
	 * @return string
	 */
	public function dateTime($args=array()) {
		if ( isset($args['time_ago']) && $args['time_ago'] == true ) {
			$date = maxgrid_time_ago($args['date_time']);
		} else if ( !empty($args['date_time']) ) {
			$date = date_i18n($this->date_format, strtotime($args['date_time'])).' '.date_i18n($this->time_format, strtotime($args['date_time']));
		} else if ( !empty($args['date']) ) {
			$date = date_i18n($this->date_format, strtotime($args['date']));
		} else if ( !empty($args['time']) ) {
			$date = date_i18n($this->time_format, strtotime($args['time']));
		}		
		return $date;
	}
	
	/**
     * Author infos template.
     *
	 * @return string
	 */
	public function author_meta($data) {
		
		$author_name 	= $data['author_name'];
		$author_url 	= $data['author_url'];
		$thumbnail_url 	= $data['thumbnail_url'];
		$published_on 	= $data['published_on'];
		$social_media 	= $data['social_media'];
		$class 			= $data['class'];
		
		return '<div class="post-owner-box author-meta '.$class.'">
				  	 <div class="comment-body">
						<div class="comment-author vcard">			
							<a href="'.$author_url.'" target="_blank">
								<img alt="" src="'.$thumbnail_url.'" class="avatar avatar-60 photo" width="40" height="40">
								<cite class="fn">'.$author_name.'</cite>
							</a>							
						</div>
						<div class="comment-meta commentmetadata">
							<span class="comment-time-ago">' . sprintf( __( 'Published on %s', 'max-grid' ) , $published_on ) . '</span>			
						</div>						
						<div class="maxgrid-author-meta-container">
							'.$social_media.'
						</div>
						
					</div>
				</div>';

	}
	
	/**
     * Author infos template.
     *
	 * @return string
	 */
	public function get_post_thumbnail($args) {
		
		$post_id = $args['post_id'];
		$size 	 = isset($args['size']) ? $args['size'] : 'medium';
		$empty = isset($args['default']) ? $args['default'] : MAXGRID_ABSURL . '/includes/css/img/no-image.svg';
		
		$featured_type = maxgrid()->get_post_meta->grid($post_id)['f_type'];
		
		if( isset($args['get_thumb']) && !has_post_thumbnail( $post_id ) ) {
			return $empty;
		}
		
		if($size == 'small'){
			$thumb_id = get_post_thumbnail_id($post_id);
			$thumb_url = wp_get_attachment_image_src($thumb_id,'large', true);
			return $thumb_url[0];
		}
		
		if ( has_post_thumbnail( $post_id ) && get_the_post_thumbnail_url($post_id, $size) != "" && $featured_type == 'image' ) {
			$size 	 = isset($args['size']) ? $args['size'] : 'large';
			return get_the_post_thumbnail_url($post_id, $size);
		}
		
		switch ($featured_type) {
			case 'image':
				$thumbnail = get_the_post_thumbnail_url($post_id, $size);				
				break;
			case 'mp4':
				$thumbnail = maxgrid()->get_post_meta->grid($post_id)['thumb_url'];		
				break;
			case 'vimeo':
				$url = maxgrid()->get_post_meta->grid($post_id)['vimeo_url'];
				$vid_id = (int) substr(parse_url($url, PHP_URL_PATH), 1);
				$response = wp_remote_get( 'http://vimeo.com/api/v2/video/' . $vid_id . '.php' );
				$body = wp_remote_retrieve_body( $response );
				$hash = unserialize($body);
				$thumbnail = $hash[0]['thumbnail_large'];
				break;
			case 'youtube':
				$url = maxgrid()->get_post_meta->grid($post_id)['youtube_url'];
				parse_str( parse_url( $url, PHP_URL_QUERY ), $vars_array );
				$vid_id = $vars_array['v'];
				$thumb_size = array(
					'default' 	=> 'hqdefault',
					'medium' 	=> 'mqdefault',
					'large' 	=> 'maxresdefault',
				);	
				$thumbnail = 'http://img.youtube.com/vi/' . $vid_id . '/'.$thumb_size[$size].'.jpg';
				break;
		}

		if ( !isset($thumbnail) || $thumbnail == '' ) {
			return isset($args['return_empty']) && $featured_type == 'image' || $featured_type != 'image' ? $empty : '';
		}
		return $thumbnail ;

	}

	/**
     * Author infos template.
     *
	 * @return string
	 */
	public function get_custom_featured($post_id, $type='image') {
		
		switch ($type) {			
			case 'image':
				$url = maxgrid()->get_post_meta->grid($post_id)['thumb_url'];
			case 'youtube':
				$url = maxgrid()->get_post_meta->grid($post_id)['youtube_url'];
				break;
			case 'vimeo':
				$url = maxgrid()->get_post_meta->grid($post_id)['vimeo_url'];
				break;
			case 'mp4':
				$url = maxgrid()->get_post_meta->grid($post_id)['mp4_url'];
				break;
		}
		return $url;
	}
	
	/**
     * SVG Icon Constructor.
     *
	 * @return string
	 */
	public function get_svg_icon($icon) {
		$data = array(
			'playlist' => array(
					'width' => '20',
					'height' => '16',
					'path-d' => 'M0 12h6v4H0v-4zM0 0h6v4H0V0zm8 0h12v4H8V0zm0 6h12v4H8V6zm0 6h12v4H8v-4zM0 6h6v4H0V6z',
			),
			'fullscreen' => array(
					'width' => '20',
					'height' => '16',
					'path-d' => 'M0 0h7v2H0V0zm0 2h2v4H0V2zm13-2h7v2h-7V0zm5 2h2v4h-2V2zM0 14h7v2H0v-2zm0-4h2v4H0v-4zm13 4h7v2h-7v-2zm5-4h2v4h-2v-4z',
			),
			'exits-fullscreen' => array(
					'width' => '20',
					'height' => '16',
					'path-d' => 'M16 0v4h4v2h-6V0h2zM0 4h4V0h2v6H0V4zm4 12v-4H0v-2h6v6H4zm16-4h-4v4h-2v-6h6v2z',
			),
			'arrow' => array(
					'width' => '27',
					'height' => '33',
					'path-d' => 'M2 28l11-11L2 6l6-6 17 16L9 33z',
			),
			'blogger' => array(
					'width' => '24',
					'height' => '24',
					'path-d' => 'M22.8 9h-2c-1 0-1.1-.7-1.1-1.7 0-4-3.3-7.3-7.3-7.3h-5A7.3 7.3 0 0 0 0 7.3v9.4c0 4 3.3 7.3 7.3 7.3h9.4c4 0 7.3-3.3 7.3-7.3v-6.5c0-.6-.5-1.2-1.2-1.2zM7.4 6h4.2c.8 0 1.4.7 1.4 1.5S12.4 9 11.6 9H7.4C6.6 9 6 8.3 6 7.5S6.6 6 7.4 6zm9.1 12h-9c-.8 0-1.5-.7-1.5-1.5S6.7 15 7.5 15h9c.8 0 1.5.7 1.5 1.5s-.7 1.5-1.5 1.5z',
			),
		);
		
		$width = $data[$icon]['width'];
		$height = $data[$icon]['height'];
		$path_d = $data[$icon]['path-d'];
		$color = isset($data[$icon]['color']) ? $data[$icon]['color'] : '#fff';
		
		$html = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" id="' . $icon . '">';
		$html .= '<path fill="' . $color . '" fill-rule="evenodd" d="' . $path_d . '"/>';
		$html .= '</svg>';
		
		return $html;
	}
}