<?php
/**
 * Handle all settings.
 */

namespace MaxGrid;

/**
 * @class getOptions
 */
class getOptions {
	
	/**
	 * Template name.
	 *
	 * @var string
	 */
	public $preset_name;

	/**
	 * Get Main Options.
	 *
	 * @return array
	 */
	public function main(){
		return get_option( MAXGRID_SETTINGS_OPT_NAME );
	}
	
	/**
	 * Get Option.
	 *
	 * @param string  $name Option name.
	 * @param string  $default Default layout name.
	 *
	 * @return array
	 */
	public function option($name='grid_layout', $default=MAXGRID_DFLT_LAYOUT_NAME) {
		$default = isset(unserialize(maxgrid_template_load($default))[$name]) ? unserialize(maxgrid_template_load($default))[$name] : '';
		return isset($this->main()[$name]) ? $this->main()[$name] : $default;
	}
	
	/**
	 * Get options by options category.
	 *
	 * @param string    $name  Option name.
	 * @param bool 		$not_array Return array or string.
	 *
	 * @return array|string
	 */
	public function rows($name, $not_array=false){
		$rows_options = $this->option()['rows_options'][$name];
		if ($not_array){
			return $rows_options;
		}
		
		return maxgrid_string_to_array("&", "=", $rows_options);
	}
	
	/**
	 * Get post description.
	 *
	 * @param string $name Option name.
	 *
	 * @return string
	 */
	public function postDes($name){
		return $this->option()['post_description'][$name];
	}
	
	/**
	 * Get post meta.
	 *
	 * @param string $name Option name.
	 *
	 * @return array
	 */
	public function postInfo($name){
		return $this->option()['info_bar'][$name];
	}
	
	/**
	 * Get post stats.
	 *
	 * @param string $name Option name.
	 *
	 * @return array
	 */
	public function postStats($name){
		return $this->option()['stats_bar'][$name];
	}
}