<?php
/**
 *  Reviews Tabs.
 */

use \MaxGrid\Tabs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class Max_Grid_Reviews_Tabs.
 */
class Max_Grid_Reviews_Tabs {
	
	/**
	 * Construct description and reviews tabs
	 *
	 * 'form_type' 		- choose from 'reviews' or 'comments' type
	 * 'tabs' 			- Tabs id, name
	 * 'default_tab' 	- Give tab id that should be pre-selected when the page loads.
	 * 'the_content' 	- post content.
	 * 'status' 		- Comment status to limit results by. Accepts 'hold', 'approve', 'all', or a custom comment status. 
	 * 'number' 		- Maximum number of comments to retrieve.
	 * 'comment_notes' 	- Customize the text prior to the comment form fields. Leave empty to disable it
	 *
	 * @param array     $data  comment form params.
	 *
	 * @return string
	 */
	public function reviews_tabs($args) {
		$maxgrid_get_options = maxgrid_get_options();
		extract($maxgrid_get_options);

		if ( get_post_type($args['post_id']) == 'post' ) {
			$tab_comments_name = __( 'Comments', 'max-grid' );
			$form_type = 'comments';
		} else {
			$tab_comments_name = ucfirst ( __( 'reviews', 'max-grid' ) );
			$form_type = 'reviews';
		}

		$the_content = isset($args['the_content']) ? $args['the_content'] : '';	
		$the_content = preg_replace('/\[[^\]]+\]/', '', $the_content);
		
		$page = isset($args['page']) ? $args['page'] : 1;
		$offset = ($cpp*$page)-($cpp);
		$default_tab = ( isset( $_GET['avis'] ) && $_GET['avis'] == 'on' ) ? 'tab-reviews' : 'tab-description' ;
			
		$data = array(
				'comments_only'    => isset($args['comments_only']) ? $args['comments_only'] : false,
				'tabs'			   => array(
										array(
										'id' 		  => 'tab-description',
										'name' 		  => __( 'Description', 'max-grid' ),
										'the_content' => $the_content,
										'icon' 		  => '<span class="icon-menu"></span>'
										),
										array(
										'id' 		  => 'tab-reviews',
										'name' 		  => $tab_comments_name,								
										'icon' 		  => '<span class="icon-bubbles4"></span>'
										)
									),
				'form_type' 	  => $form_type,
				'default_tab' 	  => $default_tab,
				'form_id' 		  => 'commentform',
				'post_id' 		  => $args['post_id'],
				'status' 		  => 'approve',
				'order' 		  => $comments_order, // DESC - ASC
				'offset' 		  => $offset,
				'number' 		  => $cpp,
			);

		$wp_eform = new Tabs( $data );	
		return $wp_eform->Tabs();
	}
}