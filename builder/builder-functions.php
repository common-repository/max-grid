<?php
/**
 * Max Grid Builder - The Builder functions.
 *
 */

defined( 'ABSPATH' ) || exit;

/**
 * Convert String to Array.
 *
 * @since 1.0.0
 *
 * @param string $cut1 	 Pass first delimiter "&"
 * @param string $cut2 	 Pass second delimiter "="
 * @param string $string string to convert
 *
 * @return array
 */
function maxgrid_string_to_array($cut1, $cut2, $string) {
	$raw_array = explode($cut1, $string);
	$array = Array();
	foreach ($raw_array as $data){
		$parts = explode($cut2, $data);
		if ( ! isset($parts[1])) {
		   $parts[1] = null;
		}
		$array[$parts[0]] = $parts[1];	
	}
	return $array;
}

/**
 * Template loader.
 *
 * @since 1.0.0
 *
 * @param string $t_name Template name
 *
 * @return array
 */
function maxgrid_template_load($t_name) {	
	global $wpdb, $source_type;
	$table_name = $wpdb->prefix . MAXGRID_LAYOUTS_TABLE_NAME;
	if($t_name=='all_elements'){
		$source_type = 'post';
	}
	return $wpdb->get_var("SELECT pcontent FROM $table_name WHERE pslug='" . $t_name . "' AND source_type='" . $source_type. "'" );
}

/**
 * Fit to width field.
 *
 * @param string $current Current element option name.
 *
 * @return string
 */
function maxgrid_fit_width_field($current, $border=true) {
	 $top_border = isset($border) && $border == true ? ' border-top' : '';
	?>
	<div class="maxgrid_ui-col divider-col<?php echo $top_border;?>">
		<div class="maxgrid_ui-edit_form_line">
			<div class="maxgrid_ch-box">
				<input id="fit_width" name="fit_width" value="1" type="checkbox" <?php if( isset($current['fit_width']) && maxgrid_string_to_bool($current['fit_width'])==1):echo"checked";endif; ?>>
				<label for="fit_width">Fit width to container</label>
				<p>Fit element to container and ignore parent padding.</p>
			</div>				
		</div>
	</div>
	<?php
}

/**
 * Get last active options tab ID.
 *
 * @param string $tab_id Tab ID.
 *
 * @return string
 */
function maxgrid_get_last_options_tab_id($tab_id){
	return isset($_SESSION[$tab_id.'-tabs']) && strpos($_SESSION[$tab_id.'-tabs'], 'tab') !== false ? $_SESSION[$tab_id.'-tabs'] : 'tab1';
}