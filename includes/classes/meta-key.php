<?php
/**
 * This class will Add custom post meta for post views, post likes and post downloads count.
 */

namespace MaxGrid;
use \MaxGrid\getOptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class countMeta.
 */
class countMeta {
	
	/**
	 * Update post meta.
	 *
	 * @param int      $post_id Post ID.
	 * @param string   $meta_key Meta Key.
	 * @param bool     $once Restrict users voting multiple or Limit downloads counter per user.
	 *
	 * @return string
	 */
	public static function set_count( $post_id, $meta_key, $once=false ) {		
		$count = get_post_meta($post_id, $meta_key, true);		
		if ( $count == '' ) {
			$count = 0;
			delete_post_meta($post_id, $meta_key);
			add_post_meta($post_id, $meta_key, '0');
		} else {			
			$has_already_added = self::once_per_ip($post_id, $meta_key, $once );
			if(!$has_already_added || $once == false ) {
				self::update_meta_ip( $post_id, $meta_key );
				update_post_meta($post_id, $meta_key, (int)$count+1);
			} else {
				return "already";
			}		
		}
		return $count;
	}
	
	/**
	 * Get total count of post votings or downloads count.
	 *
	 * @param int      $post_id Post ID.
	 * @param string   $meta_key Meta Key.
	 *
	 * @return int
	 */
	public static function get_count( $post_id, $meta_key ) {
		$count = get_post_meta($post_id, $meta_key, true);		
		
		if ( $meta_key == MAXGRID_DOWNLOAD_META_KEY ) {
			$offset = maxgrid()->get_post_meta->download($post_id)['offset'];
			$count 	= $offset != 0 && $offset != '' && $offset != '0' ? (int)$count + (int)$offset : $count;
		}
		
		if($count==''){
			delete_post_meta($post_id, $meta_key);
			add_post_meta($post_id, $meta_key, '0');
			return "0";
		}
		return $count;
	}
	
	/**
	 * Get logs timeout
	 *
	 * @param string $meta_key Meta Key.
	 *
	 * @return int
	 */
	private static function getTimeout($meta_key) {

		$views_meta_key = maxgrid_use_custom_post_meta_key( 'views' );
		$views_meta_key = $views_meta_key ? $views_meta_key : MAXGRID_VIEWS_META_KEY;
		
		$likes_meta_key = maxgrid_use_custom_post_meta_key( 'likes' );
		$likes_meta_key = $likes_meta_key ? $likes_meta_key : MAXGRID_LIKES_META_KEY;
		
		if ( !maxgrid_metadata_exists( $likes_meta_key ) || $meta_key == $views_meta_key ) {
			return false;
		}
		
		$timeout_data = array(
				$likes_meta_key => 're_vote_timeout',
				MAXGRID_DOWNLOAD_META_KEY => 're_download_timeout',
			);
		$timeunits_data = array(
				$likes_meta_key => 're_vote_timeunits',
				MAXGRID_DOWNLOAD_META_KEY => 're_download_timeunits',
			);
		
		$get_options = maxgrid()->get_options;
		$timeunits 	 = isset($get_options->option(MAXGRID_LOGS_OPT_NAME)[$timeunits_data[$meta_key]]) ? $get_options->option(MAXGRID_LOGS_OPT_NAME)[$timeunits_data[$meta_key]] : 'hours';
		$timeout 	 = isset($get_options->option(MAXGRID_LOGS_OPT_NAME)[$timeout_data[$meta_key]]) ? $get_options->option(MAXGRID_LOGS_OPT_NAME)[$timeout_data[$meta_key]] : '24';
		
		if ($timeunits == 'days') {
			$timer = intval($timeout) * (24*60);
		} else if ( $timeunits == 'hours' ) {
			$timer = intval($timeout) * 60;
		} else if ( $timeunits == 'minutes') {
			$timer = intval($timeout);
		} else {
			$timer = intval($timeout) * 60;
		}	
		return $timer;
	}
	
	/**
	 * Get user IP address.
	 *
	 * @return int
	 */
	private static function get_ip() {		
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			//check ip from share internet
		  	$ip=$_SERVER['HTTP_CLIENT_IP'];
		} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			//to check ip is pass from proxy
		  	$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	/**
	 * Update post meta for current user IP.
	 *
	 * @param int $post_id Post ID
	 * @param string $meta_key Meta Key.
	 */
	public static function update_meta_ip( $post_id, $meta_key ) {		
		
		$meta_ip = get_post_meta($post_id, $meta_key.'_ip');
		$added_ip = isset($meta_ip[0]) ? $meta_ip[0] : '';		
		
		if(!is_array($added_ip)) {
			$added_ip = array();
		}
		
		$ip = self::get_ip();
		$added_ip[$ip] = time();
		
		update_post_meta($post_id, $meta_key."_ip", $added_ip);	
	}
	
	/**
	 * @return booleen
	 */
	/**
	 *  Restrict users voting multiple or Limit downloads counter per user.
	 *
	 * @param int 	 $post_id Post ID.
	 * @param string $meta_key Meta Key.
	 * @param bool 	 $meta_key Enable/Disable restriction.
	 *
	 * @return bool
	 */
	public static function once_per_ip( $post_id, $meta_key, $once ) {
		$meta_ip = get_post_meta($post_id, $meta_key.'_ip');
		
		if(!isset($meta_ip[0])) {
			return false;
		}
		
		if(!is_array($meta_ip[0])) {
			$added_ip = array();	
		}
		
		if ( self::getTimeout($meta_key) == 0 || $once == false ){
			return false;
		}
		
		$ip = self::get_ip();
		$added_ip = $meta_ip[0];
		
		$timezone_offset_minutes = !empty($_COOKIE['timezone_offset_minutes']) ? $_COOKIE['timezone_offset_minutes'] : 60;
		$timezone = timezone_name_from_abbr("", $timezone_offset_minutes*60, false);
			
		date_default_timezone_set($timezone);
		$now = time();
		
		return isset( $added_ip[$ip] ) && ( round( ( $now - $added_ip[$ip] ) / 60 ) > self::getTimeout($meta_key) ) ? false : true;
		if ( isset( $added_ip[$ip] ) && ( round( ( $now - $added_ip[$ip] ) / 60 ) > self::getTimeout($meta_key) ) )  {
		  return false;
		}
		
		return true;
	}	
}