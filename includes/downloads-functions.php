<?php
/**
 * Download handler
 */

defined( 'ABSPATH' ) || exit;

/**
 * Encrypt and decrypt
 * @param string $string string to be encrypted/decrypted
 * @param string $action what to do with this? e for encrypt, d for decrypt
 */
function maxgrid_encrypt( $string, $action = 'e' ) {
	$maxgrid_secret_key = 'op3wdz11miV4nZvOjuLulIveKl3Yd5U8';
    $secret_iv = 'YE9P7xC0zCMUrgJTOZ64bK3nUmsoLmjE';
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $maxgrid_secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
	
    if( $action == 'e' ) {
        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if( $action == 'd' ){
        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }
	
    return $output;
}

/**
 * Extract file name from path
 *
 * @param string  $file_path  URL to file.
 */
function maxgrid_download_basename($file_path) {
	if (!empty($file_path)) {
		$file_name = basename($file_path);
	}else{
		$file_name = "file_name_demo.zip";
	}
	return $file_name;
}

/**
 *  Get the file size of any remote resource (using get_headers()),
 *  either in bytes or - default - as human-readable formatted string.
 *
 *  @author  Stephan Schmitz <eyecatchup@gmail.com>
 *  @license MIT <http://eyecatchup.mit-license.org/>
 *  @url     <https://gist.github.com/eyecatchup/f26300ffd7e50a92bc4d>
 *
 */
function maxgrid_get_remote_filesize($url, $formatSize = true) {
    if( empty($url) ) return false;
	$head = array_change_key_case(get_headers($url, 1));
	
    // content-length of download (in bytes), read from Content-Length: field
    $clen = isset($head['content-length']) ? $head['content-length'] : 0;
	
    // cannot retrieve file size, return "-1"
    if (!$clen) {
        return -1;
    }
	
    if (!$formatSize) {
        return $clen; // return size in bytes
    }
	
    $size = $clen;
    switch ($clen) {
        case $clen < 1024:
            $size = $clen .' B'; break;
        case $clen < 1048576:
            $size = round($clen / 1024, 2) .' KB'; break;
        case $clen < 1073741824:
            $size = round($clen / 1048576, 2) . ' MB'; break;
        case $clen < 1099511627776:
            $size = round($clen / 1073741824, 2) . ' GB'; break;
    }
    return $size; // return formatted size
}

/**
 * Check if a file exist from a URL
 *
 * @param string  $file_path  URL to file.
 */
function maxgrid_url_exist($url){
	
	$response = wp_remote_get( $url );	
	$code = wp_remote_retrieve_response_code($response);
	
    if($code == 200){
       $status = true;
    }else{
      $status = false;
    }
   return $status;
}