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
     * Download data
	 *
	 * @param int    $post_ID
	 *
	 * @return array
	 */
	public function data($post_ID) {
		//$post_ID
		//echo get_post_meta( 7321, 'edd_download_files', true )[1]['file'];
		return array(
			'file' => get_post_meta( $post_ID, 'edd_download_files', true )[1]['file'],
		);
	}
}