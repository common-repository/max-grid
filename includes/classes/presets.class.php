<?php
/**
 * Get settings from layout presets 
 */

namespace MaxGrid;

/**
 * @class getPresets
 */
class getPresets {

	/**
	 * Template name.
	 *
	 * @var string
	 */
	public $preset_name;
	
	/**
	 * Post type : Post|Download|Product|Youtube.
	 *
	 * @var string
	 */
	public $source_type;
	
	/**
	 * Parent option name.
	 *
	 * @var string
	 */
	const PARENT_OPT = 'grid_layout';
	
	/**
	 * Rows option name.
	 *
	 * @var string
	 */
	const ROWS_OPT = 'rows_options';
		
	/**
	 * Constructor.
	 */
	function __construct($args) {
       	$this->preset_name = isset($args['preset_name']) ? $args['preset_name'] : MAXGRID_DFLT_LAYOUT_NAME;
		$this->source_type = isset($args['source_type']) ? $args['source_type'] : 'post';
    }
	
	/**
	 * Get all settings
	 *
	 * @return array
	 */
	public function get_option(){
		return get_option( MAXGRID_BUILDER_OPT_NAME );
	}

	/**
	 * Get element settings
	 *
	 * @param  string $name Option name.
	 *
	 * @return array
	 */
	public function option($name='grid_layout') {		
		if( $name=='grid_layout' ) {
			return unserialize($this->get_presets())[$name];
		} else {
			return isset($this->get_option()[$name]) ? $this->get_option()[$name] : unserialize($this->get_presets())[$name];
		}
	}
	
	/**
	 * Get all preset settings
	 *
	 * @param  string $pslug Template name.
	 *
	 * @return array
	 */
	public function get_presets($pslug=MAXGRID_DFLT_LAYOUT_NAME){
		global $wpdb;
		
		$table_name = $wpdb->prefix . MAXGRID_LAYOUTS_TABLE_NAME;
		$response = $wpdb->get_var("SELECT pcontent FROM $table_name WHERE pslug='".$pslug."' AND source_type='".$this->source_type."'" );
		
		if (!isset($response)){
			$pslug = $this->source_type.'_default';
			return $wpdb->get_var("SELECT pcontent FROM $table_name WHERE pslug='".$pslug."'" );
		}
		return $response;
	}
	
	/**
	 * Unserialize preset settings
	 *
	 * @param  string $pslug Template name.
	 * @param  string $name Parent option name.
	 *
	 * @return array
	 */
	public function _unserialize($pslug=MAXGRID_DFLT_LAYOUT_NAME, $name=self::PARENT_OPT){
		// if Preview Mode		
		if ( $this->preset_name == 'use_current' ) {
			return isset($this->get_option()[$name]) ? $this->get_option()[$name] : unserialize($this->get_presets())[$name];
		}
		
		$get_presets = $this->get_presets($pslug);
		
		if ( $name && $get_presets ) {
			$preset = unserialize($get_presets)[$name];
		}
		
		if (!isset($preset)) {
			return isset($this->get_option()[$name]) ? $this->get_option()[$name] : unserialize($this->get_presets())[$name];
		}
		return $preset;
	}
	
	/**
	 * Unserialize preset settings
	 *
	 * @param  string $pslug Template name.
	 * @param bool	  $not_array Return array or string.
	 *
	 * @return array|string
	 */
	public function rows($name, $not_array=false){
		$get_options  = $this->_unserialize($this->preset_name);		
		$options 	  = isset($get_options) ? $get_options : $this->option();			
		$rows_options = isset($options[self::ROWS_OPT][$name]) ? $options[self::ROWS_OPT][$name] : '';
		
		if ($not_array){
			return $rows_options;
		}
		
		return maxgrid_string_to_array("&", "=", $rows_options);
	}
	
	/**
	 * Get parent settings
	 *
	 * @return array
	 */
	public function get_parent() {
		// if Preview Mode
		if ( isset($this->get_option()[self::PARENT_OPT]) && $this->preset_name == 'use_current' ) {		
			return $this->get_option()[self::PARENT_OPT];
		}
		
		return unserialize($this->get_presets($this->preset_name))[self::PARENT_OPT];
	}
}