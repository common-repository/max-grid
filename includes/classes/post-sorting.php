<?php
/**
 * @class Max_Grid_Post_Sorting
 */
class Max_Grid_Post_Sorting {
	
	/**
	 * Get all posts categories for post, product and download post type.
	 *
	 * @param string    $post_type  Post Type
	 * @param array 	$exclude Excluded Categories
	 *
	 * @return array
	 */
	public function all_cat($post_type, $exclude) {
		if( $post_type == 'product' && !is_maxgrid_woo_activated() ){
			return;
		}
		
		if( $post_type == 'download' && !is_maxgrid_download_activated() ){
			return;
		}
		
		$terms = array(
			MAXGRID_POST => get_terms(MAXGRID_CAT_TAXONOMY, array('hide_empty' => false)),
			'product' => get_terms('product_cat', array('hide_empty' => true)),
			'post' => get_categories(),
		);
				
		$clean = array();
		
		foreach($terms[$post_type] as $term){
			if ( !in_array($term->slug, $exclude) ) {
				$clean[] = $term->slug;
			}
		}
		
		return rtrim(implode(',', $clean), ',');
	}
}