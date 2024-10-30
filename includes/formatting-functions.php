<?php
/**
 * Max Grid Builder Formatting
 *
 * Functions for formatting data.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create a number formatter
 *
 * @return int
 */
function maxgrid_number_formatter($count) {	
	$lang = get_locale(); // Gets the current locale "WPLANG"
	$num = NumberFormatter::create($lang, NumberFormatter::DECIMAL);
	return $num->format($count);
}

/**
 * Cast string to boolean (e.g. 'true' or 'false') to a bool.
 *
 * @since 1.0.0
 * @param string $string String to convert.
 * @param bool $null return null.
 * @return bool
 */
function maxgrid_string_to_bool( $string, $null=false ) {
	$boolval = ( is_string($string) ? filter_var($string, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $string );
    return ( $boolval===null && !$null ? false : $boolval );
}

/**
 * Get post published on date.
 *
 * @since 1.0.0
 *
 * @param string $format Date format.
 * @param int $id Post id.
 * @return string
 */
function maxgrid_published_on_post($format='F j, Y', $id='') {
	if (!empty($id)) {
		$published_on = get_the_date( $format, $id );
	}else{
		$published_on = "";
	}
	return $published_on;
}

/**
 * Convert Date Time to Time Ago.
 *
 * @since 1.0.0
 * @param string $datetime Date time to convert.
 * @return string
 */
function maxgrid_time_ago($datetime) {
	$current_time = current_time( 'mysql' );
	$time_ago = human_time_diff(strtotime($datetime), strtotime($current_time));
	return sprintf( __('%s ago', 'max-grid'), $time_ago );
}

/**
 * Function that converts a numeric value into an exact abbreviation ( eg: 1000 -> 1k ).
 *
 * @since 1.0.0
 * @param int $n number to convert.
 * @return string
 */
function maxgrid_number_format_short( $n, $precision = 1 ) {
	if ($n > 10000) {
		$precision = 0;
	}
	if ($n < 900) {
		// 0 - 900
		$n_format = number_format($n, $precision);
		$suffix = '';
	} else if ($n < 900000) {
		// 0.9k-850k
		$n_format = number_format($n / 1000, $precision);
		$suffix = 'K';
	} else if ($n < 900000000) {
		// 0.9m-850m
		$n_format = number_format($n / 1000000, $precision);
		$suffix = 'M';
	} else if ($n < 900000000000) {
		// 0.9b-850b
		$n_format = number_format($n / 1000000000, $precision);
		$suffix = 'B';
	} else {
		// 0.9t+
		$n_format = number_format($n / 1000000000000, $precision);
		$suffix = 'T';
	}
  // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
  // Intentionally does not affect partials, eg "1.50" -> "1.50"
	if ( $precision > 0 ) {
		$dotzero = '.' . str_repeat( '0', $precision );
		$n_format = str_replace( $dotzero, '', $n_format );
	}
	return $n_format . $suffix;
}

/**
 * Adjust colour brightness.
 *
 * @param mixed $hex Color
 * @param int 	$percent Percentage to calculate.
 * @return string
 */
function maxgrid_colourBrightness($hex, $percent) {
	 $hex = strpos($hex, 'rgb') ? maxgrid_rgb2hex($hex) : $hex;
	
	// Work out if hash given
	$hash = '';
	if (stristr($hex,'#')) {
		$hex = str_replace('#','',$hex);
		$hash = '#';
	}
	/// HEX TO RGB
	$rgb = array(hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2)));
	//// CALCULATE 
	for ($i=0; $i<3; $i++) {
		// See if brighter or darker
		if ($percent > 0) {
			// Lighter
			$rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1-$percent));
		} else {
			// Darker
			$positivePercent = $percent - ($percent*2);
			$rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1-$positivePercent));
		}
		// In case rounding up causes us to go to 256
		if ($rgb[$i] > 255) {
			$rgb[$i] = 255;
		}
	}
	//// RBG to Hex
	$hex = '';
	for($i=0; $i < 3; $i++) {
		// Convert the decimal digit to hex
		$hexDigit = dechex($rgb[$i]);
		// Add a leading zero if necessary
		if(strlen($hexDigit) == 1) {
		$hexDigit = "0" . $hexDigit;
		}
		// Append to the hex string
		$hex .= $hexDigit;
	}
	return $hash.$hex;
}

/**
 * Make HEX color darker.
 *
 * @param mixed $color  Color.
 * @param int   $factor Darker factor.
 *                      Defaults to 2.
 * @return string
 */
function maxgrid_hex_darker($rgb, $factor=2) {
    $hash = (strpos($rgb, '#') !== false) ? '#' : '';
    $rgb = (strlen($rgb) == 7) ? str_replace('#', '', $rgb) : ((strlen($rgb) == 6) ? $rgb : false);
    if(strlen($rgb) != 6) return $hash.'000000';
    $factor = ($factor > 1) ? $factor : 1;

    list($R16,$G16,$B16) = str_split($rgb,2);

    $R = sprintf("%02X", floor(hexdec($R16)/$factor));
    $G = sprintf("%02X", floor(hexdec($G16)/$factor));
    $B = sprintf("%02X", floor(hexdec($B16)/$factor));

    return $hash.$R.$G.$B;
}

/**
 * Convert RGB to HEX.
 *
 * @param mixed $color Color.
 *
 * @return string
 */
function maxgrid_rgb_to_hex($rgb) {
	if (strpos($rgb, 'rgb') === false) { return  $rgb;};
	$type = (strpos($rgb, 'rgba') !== false) ? 'rgba' : 'rgb';	
    $color = str_replace(array($type.'(', ')', ' '), '', $rgb);
	$arr = explode(',', $color);
	
	$last_val = end($arr);
	$str_rgb = '';
	foreach($arr as $key => $value){
		$str_rgb .= $value;
		if($value != $last_val) {
        	$str_rgb .=	','; 
    	}
	}
	
	$color = trim($str_rgb);
	$rgbstr = str_replace(array(',',' ','.'), ':', $color); 
	$rgbarr = explode(":", $rgbstr);
	$result = '#';
	$result .= str_pad(dechex($rgbarr[0]), 2, "0", STR_PAD_LEFT);
	$result .= str_pad(dechex($rgbarr[1]), 2, "0", STR_PAD_LEFT);
	$result .= str_pad(dechex($rgbarr[2]), 2, "0", STR_PAD_LEFT);
	$result = strtoupper($result); 
	return $result;
}

/**
 * Convert HEX to RGBA.
 *
 * @param mixed $color Color.
 *
 * @return string
 */
function maxgrid_hex_to_rgb($c, $opacity = false) {
	
	$color = (strpos($c, '#') === false) ? maxgrid_rgb_to_hex($c) : $c;	
    $default = 'rgb(0,0,0)';
	
    if (empty($color))
        return $default;
	
    if ($color[0] == '#')
        $color = substr($color, 1);
	
    if (strlen($color) == 6)
        $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
    
    elseif (strlen($color) == 3)
        $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
    else
        return $default;
       
    $rgb = array_map('hexdec', $hex);    
    if ($opacity) {
        if (abs($opacity) > 1)
            $opacity = 1.0;
        $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
    } else {
        $output = 'rgb(' . implode(",", $rgb) . ')';
    }    
    return $output;
}

/**
 * Detect if we should use a light or dark color on a background color.
 *
 * @param mixed  $color Color.
 * @param string $dark  Darkest reference.
 *                      Defaults to '#000000'.
 * @param string $light Lightest reference.
 *                      Defaults to '#FFFFFF'.
 * @return string
 */
function maxgrid_light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {
	$hex = str_replace( '#', '', $color );

	$r = hexdec(substr($hex,0,2));
	$g = hexdec(substr($hex,2,2));
	$b = hexdec(substr($hex,4,2));

	$contrast = sqrt(
		$r * $r * .241 +
		$g * $g * .691 +
		$b * $b * .068
	);

	return $contrast > 130 ? $dark : $light;
}

/**
 * Finds a substring between two strings
 *
 * @param  string $string The string to be searched
 * @param  string $start The start of the desired substring
 * @param  string $end The end of the desired substring
 *
 * @return string
 */
function maxgrid_find_between_str($string, $start, $end) {
    $string = ' ' . $string;
	$ini = strpos($string, $start);
	if ($ini == 0) return '';
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}


/**
 * Convert Youtube & Vimeo Video Duration To Time
 * Youtube v3 api duration e.g. PT1M3S to HH:MM:SS
 * Vimeo api duration e.g. 15 to 0:15
 *
 * @param string $vidTime Video duration.
 * @param string $platform Youtube or Vimeo:
 *
 * @return string
 */
function maxgrid_duration_to_time($vidTime, $platform = '' ) {		
	if ( $platform == 'youtube' ) {			
		$yt=str_replace(['P','T'],'',$vidTime);
		foreach(['D','H','M','S'] as $a){
			$pos=strpos($yt,$a);
			if($pos!==false) ${$a}=substr($yt,0,$pos); else { ${$a}=0; continue; }
			$yt=substr($yt,$pos+1);
		}
		if($D>0){
			$M=str_pad($M,2,'0',STR_PAD_LEFT);
			$S=str_pad($S,2,'0',STR_PAD_LEFT);
			$vimeo_time = ($H+(24*$D)).":$M:$S"; // add days to hours
		} elseif($H>0){
			$M=str_pad($M,2,'0',STR_PAD_LEFT);
			$S=str_pad($S,2,'0',STR_PAD_LEFT);
			$vimeo_time = "$H:$M:$S";
		} else {
			$S=str_pad($S,2,'0',STR_PAD_LEFT);
			$vimeo_time = "$M:$S";
		}
		return $vimeo_time;

	} else if ( $platform == 'vimeo' ) {
		preg_match_all('/(\d+)/',$vidTime,$parts);
		// Put in zeros if we have less than 3 numbers.
		if (count($parts[0]) == 1) {
			array_unshift($parts[0], "0", "0");
		} elseif (count($parts[0]) == 2) {
			array_unshift($parts[0], "0");
		}

		$sec_init = $parts[0][2];
		$seconds = $sec_init%60;
		$seconds_overflow = floor($sec_init/60);

		$min_init = $parts[0][1] + $seconds_overflow;
		$minutes = ($min_init)%60;
		$minutes_overflow = floor(($min_init)/60);

		$hours = $parts[0][0] + $minutes_overflow;

		if($hours != 0) {
			$vimeo_time = $hours.':'.$minutes.':'.$seconds;
		} else {
			$vimeo_time = $minutes.':'.$seconds;
		}
		return $vimeo_time;
	} else {
		return '00:00';
	}
}

/**
 * Get Youtube video duration.
 *
 * @param array $args Video ID & API Key.
 *
 * @return string
 */
function maxgrid_get_ytb_v_duration( $args ) {
	if ( isset($args['api_key']) && $args['api_key'] != '' ) {
		$vid_url  = 'https://www.youtube.com/watch?v='.$args['vid_id'];
		$api_url  = 'https://www.googleapis.com/youtube/v3/';

		$response = wp_remote_get( $api_url . "videos?id=" . $args['vid_id'] . "&key=" . $args['api_key'] . "&part=snippet,statistics,contentDetails" );
		$body = wp_remote_retrieve_body( $response );
		
		$videoDetails = json_decode($body, true);	
		// Get Video Duration
		$vidTime = $videoDetails['items'][0]['contentDetails']['duration'];
	} else {
		$vidTime = 0;
	}
	$time_convert = maxgrid_duration_to_time($vidTime, 'youtube');
	
	return $time_convert;
}