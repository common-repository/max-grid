<?php
/**
 * Functions to run upon plugin activation.
 */

namespace MaxGrid;

defined( 'ABSPATH' ) || exit;

/**
 * @class Table.
 */
class Table {
	
	private $wpdb;
	
	/**
	 * Constructor.
	 */	
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		
		$ajax = array(
			'maxgrid_layout_preset_delete' 	=> 'delete_layout',
			'maxgrid_export_templates' 		=> 'export_templates',
			'maxgrid_import_templates' 		=> 'import_templates',
		);
		foreach($ajax as $action => $function){
			add_action( 'wp_ajax_'.$action, array( $this, $function ) );
			add_action( 'wp_ajax_nopriv_'.$action, array( $this, $function ) );
		}
	}
	
	/**
	 * Delete record.
	 */
	public function deleteRecord($target) {
		if ( !$target ) {
			echo 'Error: empty data';
			return false;
		}
		
		$table = $this->wpdb->prefix . MAXGRID_LAYOUTS_TABLE_NAME;		
		$delete_data = $this->wpdb->query( 'DELETE FROM ' . $table . ' WHERE ' . $target['key'] . '="' . $target['value'] . '" AND source_type="' . $target['source_type'] . '"');
		
		if ( $delete_data ) {
			echo "SUCCESS";
		} else {
			echo "Record cannot be deleted";
		}
	}
	
	/**
	 * Insert new record
	 */
	public function insertRecord($data, $target = null, $plug_install=false ) {
		global $wpdb;
		if ( $data === NULL || empty($data) ) {
			echo 'Error: Empty Data';
			return false;
		}		
		
		$table = $wpdb->prefix . MAXGRID_LAYOUTS_TABLE_NAME;
					
		// Check if record exists
		if ( isset($target['action_type']) && $target['action_type'] !== 'edit' && !isset($target['import']) ) {
			$results = $wpdb->get_results('SELECT * FROM ' . $table . ' WHERE ' . $target['key'] . '="' . $target['value'] . '" AND source_type="' . $data['source_type'] . '"');
		} else {
			$results = array();
		}
		
		// Check if record exists
		if ( isset($target['import']) ) {
			$check = $wpdb->get_results('SELECT * FROM ' . $table . ' WHERE pslug="' . $data['pslug'] . '" AND source_type="' . $data['source_type'] . '"');
			if(count($check) != 0) {
				$target = array(
					'key' => 'pslug',
					'value' => $data['pslug'],
					'source_type' => $data['source_type'],
				);
				$this->deleteRecord($target);
			}
		} 
		
		if ( isset($target['action_type']) && $target['action_type'] === 'edit' ) {
			if( $wpdb->update( $table, array('pcontent' => $data['pcontent']), array('pslug' => $data['pslug'], 'source_type' => $data['source_type']) ) === FALSE ) {
				echo "Oops, something went wrong!";
			} else {				
				echo 'SUCCESS';
			}
		} else {
			// if recorde not exist
			if(count($results) == 0) {	
				if( $wpdb->insert( $table, $data ) === FALSE ) {
					echo "Oops, something went wrong!";
				} else {
					if ( isset($target['import']) ) {
						echo '<option data-source-type="'.$data['source_type'].'" value="'.$data['pslug'].'">'.$data['pname'].'</option>';
					} else {
						echo 'SUCCESS';
					}					
				}
			} else {
				echo 'Template name already exists or has been previously used.';
			}
		}		
	}
	
	/**
	 * Export new record
	 */
	public function exportRecord($source_type) {		
		$table = $this->wpdb->prefix . MAXGRID_LAYOUTS_TABLE_NAME;
		
		$source_types_arr = maxgrid_available_source_types();
		if ( $source_type != 'all' ) {
			$source_types_arr = ['post'];
			$source_types_arr = [$source_type];
		}
		$source_types = implode("', '", $source_types_arr);		
		
		$resultset 	   = $this->wpdb->get_results("SELECT * FROM $table WHERE source_type in ('$source_types') ORDER BY id DESC", OBJECT);
		
		$filename 	   = 'maxgrid_'.$source_type.'_templates-backup_'. date('Y-m-d_\a\t_h-i\_a') . '.txt';			
		$csv_file_path = MAXGRID_ABSPATH . 'builder/backup/' . $filename;
			
		$txt_fopen 	   = fopen($csv_file_path, 'w');
		$fields 	   = array('version' => MAXGRID_BACKUP_TEMPLATES_VERSION);
		
		foreach ($resultset as $result) {
			if( strpos($result->pslug, 'maxgrid_demo') !== false ) {
				continue;
			}

			$fields[] = array($result->id, $result->source_type, $result->pslug, $result->pname, $result->pcontent);
		}
		
		fwrite($txt_fopen, base64_encode(serialize($fields)) );		
		fclose($txt_fopen);
		
		$file_url = MAXGRID_ABSURL . '/builder/backup/' . $filename;
		return $file_url;	
	}
	
	/**
	 * Import new record
	 */
	public function importRecord($file, $plug_install=false) {
		if (($handle = file_get_contents($file)) !== FALSE) {			
			foreach ( unserialize(base64_decode($handle)) as $row ) {
				
				if( !is_array($row) ){continue;}
				
				$data = array(
					'source_type' => isset($row[1]) ? $row[1] : '',
					'pslug' 	  => isset($row[2]) ? $row[2] : '',
					'pname' 	  => isset($row[3]) ? $row[3] : '',
					'pcontent' 	  => isset($row[4]) ? $row[4] : '',
				);
				$target = array(
					'import' => true,
				);
				
				if ($plug_install==true) {					
					$table = $this->wpdb->prefix . MAXGRID_LAYOUTS_TABLE_NAME;
					
					$check = $this->wpdb->get_results('SELECT * FROM ' . $table . ' WHERE pslug="' . $data['pslug'] . '" AND source_type="' . $row[1] . '"');
					if(count($check) != 0) {
						$target = array('key' => 'pslug', 'value' => $data['pslug']);
						$this->wpdb->query( 'DELETE FROM ' . $table . ' WHERE ' . $target['key'] . '="' . $target['value'] . '" AND source_type="' . $row[1] . '"');
					}
					$this->wpdb->insert( $table, $data );
				} else {
					$this->insertRecord($data, $target, $plug_install);
				}
			}
		} else {
			return "Oops, something went wrong!";
		}	
	}
	
	/**
	 * Import new record
	 */
	public function restoreRecord( $file, $pslug, $pname, $source_type ) {
		$preset_options = '';
		$not_dflt = null;
		$dflt_pslug = '';
		
		$dflt_pslugs = array(
			'post_default', 'download_default', 'product_default', 'youtube_stream_default', 'all_elements', 'fill_cover_overlay', 'featured_only', 'black_and_white', 'embedded_contents', 'direction_aware_hover', 'term_colors'
		);
		
		if ( !in_array($pslug, $dflt_pslugs) ) {
			$dflt_pslug = $source_type.'_default';
			$current_pslug = $pslug;
			$not_dflt = true;
		}
		
		if (($handle = file_get_contents($file)) !== FALSE) {			
			foreach ( unserialize(base64_decode($handle)) as $row ) {
				if( !is_array($row) ){continue;}
				
				if ( $row[2] == $dflt_pslug && $row[1] == $source_type ) {
					$dflt_preset_options = unserialize($row[4]);
				}
				
				if ( $row[2] == $pslug && $row[1] == $source_type || $not_dflt && $row[2] == $dflt_pslug ) {
					
					if ( !$not_dflt ) {
						$preset_options = unserialize($row[4]);
					} else {
						$pslug = $current_pslug;
					}					
					
					$data = array(
						'source_type' => $source_type,
						'pslug' 	  => $pslug,
						'pname' 	  => $pname,
						'pcontent' 	  => isset($row[4]) ? $row[4] : '',
					);
					
					$table = $this->wpdb->prefix . MAXGRID_LAYOUTS_TABLE_NAME;

					$check = $this->wpdb->get_results('SELECT * FROM ' . $table . ' WHERE pslug="' . $pslug . '" AND source_type="' . $source_type . '"');
					if(count($check) != 0) {
						$this->wpdb->query( 'DELETE FROM ' . $table . ' WHERE pslug="' . $pslug . '" AND source_type="' . $source_type . '"');
					}
					$this->wpdb->insert( $table, $data );
				}
			}		
		}
		return $preset_options != '' ? $preset_options : $dflt_preset_options;
	}

	/**
	 * Delete layout.
	 *
	 * @return string
	 */
	public function delete_layout() {
		global $wpdb, $source_type;

		$pname 		 = sanitize_text_field( $_POST['preset_name'] );
		$source_type = sanitize_text_field( $_POST['source_type'] );

		// strip out all whitespace
		$pname_clean = str_replace(' ', '_', $pname);

		// convert the string to all lowercase
		$pslug = strtolower($pname_clean);

		$target = array(
				'key' => 'pslug',
				'value' => $pslug,
				'source_type' => $source_type,
			);

		/**
		 * Call Table Class to delete record
		 */
		$this->deleteRecord($target);

		die();
	}

	/**
	 * Export layouts.
	 *
	 * @return string
	 */
	public function export_templates() {
		$post_type = $_POST['post_type'];
		echo $this->exportRecord($post_type);
		die();
	}

	/**
	 * Import layouts.
	 *
	 * @return string
	 */
	public function import_templates() {		
		if( $_FILES['file']['type'] == 'text/plain'){
			echo $this->importRecord($_FILES['file']['tmp_name']);
		} else {
		  	die("Sorry, this file type not allowed");
		}
		die();
	}	
}