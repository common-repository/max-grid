<?php
/**
 * Google ReCaptcha Form
 *
 * @version     1.0.0
 */

namespace MaxGrid;

/**
 * @class get_post_meta
 */
class get_post_meta {
	
	/**
     * Download File post meta
	 *
	 * @param int    $post_ID
	 *
	 * @return array
	 */
	public function download($post_ID) {
		if ( is_array(get_post_meta( $post_ID, 'edd_download_files', true )) ) {
			$download_files = get_post_meta( $post_ID, 'edd_download_files', true );
			$edd_file = isset($download_files[1]) ? $download_files[1]['file'] : '';
		} else {
			$edd_file = null;
		}
		
		$maxgrid_file 	= get_post_meta($post_ID, 'maxgrid_download_file', true);
		$offset 		= get_post_meta($post_ID, 'maxgrid_downloads_offset', true);
		$title 			= get_post_meta($post_ID, 'maxgrid_download_title', true);		
		$use_ds 		= get_post_meta($post_ID, 'use_download_sender', true);	
		//$use_ds 		= get_post_meta($post_ID)['use_download_sender'][0];
		$data = array(
			'file' 	 => isset($edd_file) && $edd_file != '' ? $edd_file : $maxgrid_file,
			'title'  => $title != '' ? $title : __( 'Download', 'max-grid'),
			'offset' => $offset,
			'use_ds' => $use_ds,
		);
		
		return $data;
	}
	
	/**
     * Grid Settings post meta
	 *
	 * @param int    $post_ID
	 *
	 * @return array
	 */
	public function grid($post_ID) {
		
		$f_type 	 	 = get_post_meta($post_ID, 'maxgrid_featured_mode', true);
		$f_type 	 	 = !empty($f_type) ? $f_type : 'image';
		$thumb_url 	 	 = get_post_meta($post_ID, 'maxgrid_thumbnail_url', true);
		$vimeo_url 	 	 = get_post_meta($post_ID, 'maxgrid_embed_vimeo_url', true);
		$youtube_url 	 = get_post_meta($post_ID, 'maxgrid_embed_youtube_url', true);
		$mp4_url 	 	 = get_post_meta($post_ID, 'maxgrid_embed_mp4_url', true);
		$audio_player	 = get_post_meta($post_ID, 'audio_player', true);
		$audio_file  	 = get_post_meta($post_ID, 'maxgrid_audio_file', true);
		$soundcloud_code = get_post_meta($post_ID, 'soundcloud_code', true);
		$excerpt 	 	 = get_post_meta($post_ID, 'excerpt', true);
		
		$data = array(
			'f_type'  		  => isset($f_type) ? $f_type : 'image',
			'thumb_url' 	  => $thumb_url != '' ? $thumb_url : null,
			'vimeo_url' 	  => $vimeo_url != '' ? $vimeo_url : 'null',
			'youtube_url' 	  => $youtube_url != '' ? $youtube_url : null,
			'mp4_url' 		  => $mp4_url != '' ? $mp4_url : null,
			'audio_player' 	  => $audio_player != '' ? $audio_player : 'wp_player',
			'audio_file' 	  => $audio_file != '' ? $audio_file : '',
			'soundcloud_code' => $soundcloud_code != '' ? $soundcloud_code : '',
			'excerpt' 		  => $excerpt != '' ? $excerpt : null,
		);
		
		return $data;
	}
}