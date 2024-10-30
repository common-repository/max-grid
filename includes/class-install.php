<?php
/**
 * Installation related functions and actions.
 */

use \MaxGrid\Table;

defined( 'ABSPATH' ) || exit;

/**
 * @class Max_Grid_Install.
 */
class Max_Grid_Install {
	
	/**
     * Set up the table and options
     *
     * @return void
     */
	public function activate() {
		global $wpdb;
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		//  Install Tables
		$grid_templates_table_name = $wpdb->prefix . MAXGRID_LAYOUTS_TABLE_NAME;	
		
		if($wpdb->get_var("SHOW TABLES LIKE '$grid_templates_table_name'") != $grid_templates_table_name){
			$sql = "CREATE TABLE " . $grid_templates_table_name . " (
					id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					source_type MEDIUMTEXT NOT NULL,
					pslug MEDIUMTEXT NOT NULL,
					pname MEDIUMTEXT NOT NULL,
					pcontent MEDIUMTEXT NOT NULL,
					PRIMARY KEY  (id)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			dbDelta($sql);
		}
				
		// Update the default Grid layout preset
		$table	= new Table;		
		$file	= MAXGRID_ABSURL . '/builder/backup/post_templates.txt';
				
		// Automatically disable the plugin on activation if it doesn't found the 'post_templates.txt' file.
		if(!maxgrid_url_exist($file)){
        	deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( sprintf( __( 'The plugin could not be activated. The plugin is missing the %spost_templates.txt%s file.', 'max-grid'), '<strong>', '</strong>') );
		}
		
		$plug_install = true;
		$table->importRecord($file, $plug_install);
	}
}