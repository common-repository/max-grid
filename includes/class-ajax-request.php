<?php
/**
 * Max Grid Builder - Ajax requests.
 */

use \MaxGrid\Tabs;
use \MaxGrid\getPresets;	
use \MaxGrid\Template;
use \MaxGrid\getOptions;
use \MaxGrid\Elements;
use \MaxGrid\countMeta;
use \MaxGrid\Youtube;
use \MaxGrid\share;

defined( 'ABSPATH' ) || exit;

/**
 * @class Max_Grid_Ajax_Request.
 */
class Max_Grid_Ajax_Request {
	
	/**
	 * Initiate new ajax request
	 */
	public function __construct() {
		$ajax = array(
			'maxgrid_get_sharethis_content' 	=> 'get_sharethis_content',
			'maxgrid_lightbox_rightside_items' 	=> 'lightbox_rightside_items',
			'maxgrid_get_last_comment' 			=> 'get_last_comment',
			'maxgrid_get_comment_replies' 		=> 'get_comment_replies',
			'maxgrid_load_more_comments' 		=> 'load_more_comments',
			'maxgrid_construct' 				=> 'grid_construct',
			'maxgrid_decrypt_file_url' 			=> 'decrypt_file_url',
			'maxgrid_set_session' 				=> 'set_session',
			'maxgrid_lightbox_body' 			=> 'lightbox_body',
		);
		foreach($ajax as $action => $function){
			add_action( 'wp_ajax_'.$action, array( $this, $function ) );
			add_action( 'wp_ajax_nopriv_'.$action, array( $this, $function ) );
		}	
	}
	
	/**
	 * Max Grid Builder grid construct
	 *
	 * @return string
	 */
	public function grid_construct() {
		
		$post_type    = sanitize_text_field( $_POST['post_type'] );
		$preset_name  = sanitize_text_field( $_POST['preset_name'] );
		$page_id 	  = sanitize_text_field( $_POST['page_id'] );
		$preview_mode = sanitize_text_field( $_POST['preview_mode'] );
		
		$taxonomy = [
			MAXGRID_POST => array(
				'taxonomy' => MAXGRID_CAT_TAXONOMY,
				'tag_meta_name' => MAXGRID_TAG_TAXONOMY,
			),
			'product' => array(
				'taxonomy' => 'product_cat',
				'tag_meta_name' => 'product_tag',
			),
			'post' => array(
				'taxonomy' => 'category',
				'tag_meta_name' => 'post_tag',
			),
		];
		$cats_names = '';
		// get list of categories if no categories have been chosen
		$get_all_categories = explode(',', sanitize_text_field( $_POST['all_categories'] ) );
		if( isset($_POST['categoryfilter']) && $_POST['categoryfilter'] != 'null' && $_POST['catlink'] == "" ) {			
			$categoryfilter = explode(',', sanitize_text_field( $_POST['categoryfilter'] ) );
			$cats_names = sanitize_text_field( $_POST['categoryfilter'] );
		} else if ( isset( $_POST['catlink'] ) && $_POST['catlink'] != "" ) {
			$categoryfilter = explode(',', sanitize_text_field( $_POST['catlink'] ) );
		} else {
			$categoryfilter = $get_all_categories;
		}
		
		if ( isset( $_POST['authorid'] ) && $_POST['authorid'] != "" ) {
			$author_id = sanitize_text_field( $_POST['authorid'] );
		} else {
			$author_id = '';
		}

		//$comments = get_comments($args);	
		$ppp 			= isset($_POST["ppp"]) ? sanitize_text_field( $_POST["ppp"] ) : 3;
		$offset  		= isset($_POST['offset']) ? sanitize_text_field( $_POST['offset'] ) : 1;
		$page  			= isset($_POST['page']) ? sanitize_text_field( $_POST['page'] ) : 1;
		$esc_desc_order = isset($_POST['order']) ? sanitize_text_field( $_POST['order'] ) : 'DESC';

		if ( $offset <= $ppp ) {
			$offset = 0;
		} else if ( $offset >= $ppp ) {
			$offset = $offset-1;
		}

		header("Content-Type: text/html");

		// Sort by ( Newset, downloads, views or date )
		if ( isset($_POST['orderby']) && $_POST['orderby'] == 'date') {
			$orderby = 'date';
			$meta_key = '';
		} else{
			$orderby = 'meta_value_num';	
			$meta_key = isset($_POST['orderby']) ? sanitize_text_field( $_POST['orderby'] ) : 'date';
		}
		
		$args = array(
			'post_type' 		 => $post_type,
			'author' 			 => $author_id, 
			'orderby' 			 => $orderby,
			'meta_key'   		 => $meta_key,		
			'order'				 => $esc_desc_order, // ASC or DESC
			'ignore_sticky_posts' => 1,
			'posts_per_page' 	 => $ppp,
			'offset' 			 => $offset,
			'post_status' 		 => 'publish'
		);
		
		$tags_names = '';
		if( isset( $_POST['tag']) && $_POST['tag'] != 'null' ) {			
			$tags_names = sanitize_text_field( $_POST['tag'] );
			$args['tax_query'] = array(
				'relation' => 'AND',
					array(
						'taxonomy' => $taxonomy[$post_type]['taxonomy'],
						'field'    => 'slug',
						'terms'    => $categoryfilter,
					),
					array(
						'taxonomy' => $taxonomy[$post_type]['tag_meta_name'],
						'field'    => 'slug',
						'terms'    => explode(',', sanitize_text_field( $_POST['tag'] ) ),
					),
			);

		} else {
			$args['tax_query'] = array(
					array(
						'taxonomy' => $taxonomy[$post_type]['taxonomy'],
						'field'    => 'slug',
						'terms'    => $categoryfilter,
					),
			);
		}
		
		$preview_name = $preview_mode == 'true' ? 'preview-mode': '';
		
		// Transient
		$expire_time 	= maxgrid()->transient->expire_time;		
		$name 			= $author_id.$orderby.$meta_key.$esc_desc_order.$ppp.$offset.$preview_name.$cats_names.sanitize_text_field( $_POST['excl_cat'] ).$tags_names;
		
		$transient_name = maxgrid()->transient->prefix . '_' . $preset_name . '_' . $post_type . '_' . md5($name) . '-' . $page_id;
		//$transient_name = maxgrid()->transient->prefix . '_' . $post_type . '_' . md5($name).'-'.$page_id.'-'.$preset_name;
		$notransiant = true;
		
		$query = new WP_Query( $args );
		
		if ( false === ( $grid = get_transient( $transient_name ) ) ) {
						
			$masonry_layout = sanitize_text_field( $_POST['masonry_layout'] );

			$ribbon = sanitize_text_field( $_POST['ribbon'] );

			if( isset( $_POST['layout'] ) && $_POST['layout'] ) {
				$layout_type = sanitize_text_field( $_POST['layout'] );
			} else {
				$layout_type = sanitize_text_field( $_POST[MAXGRID_DFLT_LAYOUT_NAME] );
			}

			$maxgrid_data = get_option( MAXGRID_SETTINGS_OPT_NAME );
			if(is_array($maxgrid_data)){
				$current_options = array_filter($maxgrid_data, function ($var) {
					return !is_null($var);
				});			
			}
			
			$str_serialized_options = isset($maxgrid_data['grid_layout']['rows_options']['blocks_row']) ? $maxgrid_data['grid_layout']['rows_options']['blocks_row'] : maxgrid()->settings->blocks_default();

			$massonry = $masonry_layout == 'on' ? ' masonry-grid-layout' : '';
			if($layout_type == 'grid') { 
				$grid = '<div class="ajax-grid_response grid-layout-row' . sanitize_text_field( $_POST['grid_container'] ) .$massonry.'" ' . 'data-page="'. $page . '" data-max-page="' . $query->max_num_pages . '" data-pagination="' .  sanitize_text_field( $_POST['pagination'] ) .'"'.' id="grid_response">'; 
			} else {
			 	$grid = '<div class="ajax-list_response list-layout-row' .  sanitize_text_field( $_POST['list_container'] ).'" ' .  'data-page="'.$page.'" data-max-page="'.$query->max_num_pages.'" data-pagination="'. sanitize_text_field( $_POST['pagination'] ) .'" id="list_response">';
			}
			
			if( $query->have_posts() ) :
				$i = 0;
				$Max_Grid_Templates = new Max_Grid_Templates;

				$presets_args = array('source_type' => $post_type, 'preset_name' => $preset_name );	
				$get_presets  = new getPresets($presets_args);
				$options 	  = $get_presets->get_parent();

				$description_row  		= $get_presets->rows('description_row');
				$blocks_row 	  		= $get_presets->rows('blocks_row');
				$featured_options 		= $get_presets->rows('featured_row');
				$title_options 	  		= $get_presets->rows('title_row');
				$add_to_cart_options 	= $get_presets->rows('add_to_cart_row');
				$download_options 		= $get_presets->rows('download_row');
				$audio_options 	  		= $get_presets->rows('audio_row');
				$info_options 	  		= $get_presets->rows('info_row');
				$average_rating_options = $get_presets->rows('average_rating_row');
				$s_o 					= $get_presets->rows('stats_row');

				$stats = array(
					'post' 			 => 'stats_row',
					'download' 		 => 'stats_row',
					'product' 		 => 'woo_stats_row',
					'youtube_stream' => 'ytb_vid_stats_row',
				);

				$stats_options = $get_presets->rows($stats[$post_type]);

				while( $query->have_posts() ): $query->the_post();
					$post_id = get_the_ID();
					$data = array(
						'post_id' 			=> $post_id,
						'post_type' 		=> $post_type,
						'preset_name' 		=> $preset_name,
						'layout_type' 		=> $layout_type,
						'exclude_category'  => $categoryfilter,
						'ribbon' 			=> $ribbon,
						'page' 				=> $page,
						'pagination' 		=> sanitize_text_field( $_POST['pagination'] ),
						'items_per_row' 	=> sanitize_text_field( $_POST['items_per_row'] ),
						'order' 			=> $esc_desc_order,
						'masonry_layout' 	=> $masonry_layout,
						'full_content' 		=> sanitize_text_field( $_POST['full_content'] ),
						'orderby' 			=> $orderby,
						'max_page' 			=> $query->max_num_pages,
						'get_presets' 		=> $get_presets,
						'options' 			=> $options,
						'description_row' 	=> $description_row,
						'blocks_row' 		=> $blocks_row,
						'featured_options' 	=> $featured_options,
						'title_options' 	=> $title_options,
						'add_to_cart_options' => $add_to_cart_options,
						'download_options' 	=> $download_options,
						'audio_options' 	=> $audio_options,
						'info_options' 		=> $info_options,
						'average_rating_options' => $average_rating_options,
						'stats_options' 	=> $stats_options,
						's_o' 				=> $s_o,
					);

					$grid .= $Max_Grid_Templates->grid($data);
					$i++;
				endwhile;
				wp_reset_postdata();
			else :
				$grid .= '<div class="not-found-posts">No posts found</div>';
			endif;
			
			if($layout_type == 'grid') {			
				$grid .= '</div>';
			} else {		
				if ($i == 0) {
					$grid .= 'No posts found';
				}
				$grid .= '</div>';
			}

			// Put the results in a transient. Expire after 12 hours.
			if ( $preview_mode != 'true' ) {
				set_transient( $transient_name, $grid, $expire_time );				
			}

			wp_reset_postdata();// avoid errors further down the page
		}
		
		// Numeric Pagination
		if ( $_POST['pagination'] == 'numeric_pagination' ) {
			if ( $query->max_num_pages > 1 ) {
				$max   = intval( $query->max_num_pages );
				/** Add current page to the array */
				$paged = $page;
				if ( $paged >= 1 )
					$links[] = $paged;
				/** Add the pages around the current page to the array */
				if ( $paged >= 3 ) {
					$links[] = $paged - 1;
					$links[] = $paged - 2;
				}
				if ( ( $paged + 2 ) <= $max ) {
					$links[] = $paged + 2;
					$links[] = $paged + 1;
				}

				$grid .= '<div class="maxgrid-navigation"><ul>' . "\n";

				 /** Previous Post Link */	
				if ( $page > 1 )
					$grid .= '<a data-href="'.($page-1).'"><span class="prev-nav">« '.__( 'Previous', 'max-grid' ).'</span></a>';
				/** Link to first page, plus ellipses if necessary */
				if ( ! in_array( 1, $links ) ) {

					$class = 1 == $page ? ' class="active"' : '';
					$grid .= sprintf( '<li%s><a data-href="%s" href="%s">%s</a></li>' . "\n", $class, 1, esc_url( get_pagenum_link( 1 ) ), '1' );
					if ( ! in_array( 2, $links ) )
						$grid .= '<li class="dotts-nav heigh">...</li>';
				}
				/** Link to current page, plus 2 pages in either direction if necessary */
				sort( $links );
				foreach ( (array) $links as $link ) {
					$class = $link == $page ? ' class="active"' : '';
					$grid .= sprintf( '<li%s><a data-href="%s" href="%s">%s</a></li>' . "\n", $class, $link, esc_url( get_pagenum_link( $link ) ), $link );
				}
				/** Link to last page, plus ellipses if necessary */
				if ( ! in_array( $max, $links ) ) {
					if ( ! in_array( $max - 1, $links ) )
					$grid .= '<li class="dotts-nav low">...</li>' . "\n";
					$class = $max == $page ? ' class="active"' : '';
					$grid .= sprintf( '<li%s><a data-href="%s" href="%s">%s</a></li>' . "\n", $class, $max, esc_url( get_pagenum_link( $max ) ), $max );
				}
				if ( $page < $max )
				$grid .= '<a data-href="'.($page+1).'"><span class="next-nav">'.__( 'Next', 'max-grid' ).' »</span></a>';
				/** Next Post Link */
				if ( get_next_posts_link() )			
					$grid .= sprintf( '<li>%s</li>' . "\n", next_posts_link() );
				$grid .= '</ul></div>' . "\n";
			}
		}		
		echo $grid;
		
		die();
	}
	
	/**
	 *  Get lightbox right side items
	 *
	 * @return string
	 */
	public function lightbox_rightside_items() {
		$post_type 		 = in_array($_POST['post_type'], array(MAXGRID_POST, 'product', 'post')) ? 'wp_post' : sanitize_text_field($_POST['post_type']);
		$pslug 			 = sanitize_text_field( $_POST['pslug'] );
 		$grid_id		 = sanitize_text_field( $_POST['grid_id'] );
		$order 			 = sanitize_text_field( $_POST['order'] );
		$offset 		 = isset($_POST['offset']) ? sanitize_text_field( $_POST['offset'] ) : '';
		$ppp 			 = isset($_POST['offset']) ? sanitize_text_field( $_POST['ppp'] ) : 50;	

		$post_type = sanitize_text_field( $_POST['post_type'] );

		$offset  = (isset($_POST['offset'])) ? sanitize_text_field( $_POST['offset'] ) : 0;
		$page  = (isset($_POST['page'])) ? sanitize_text_field( $_POST['page'] ) : 1;

		// Sort by ( Newset, downloads, views or date )
		if ( $order == 'date') {
			$orderby = 'date';
			$meta_key = '';
		} else{
			$orderby = 'meta_value_num';	
			$meta_key = $order;
		}

		$args = array(
			'post_type' 		  => $post_type,
			'pslug' 			  => $pslug,
			'grid_id' 			  => $grid_id,
			'orderby' 			  => $orderby,
			'meta_key'  		  => $meta_key,
			'ignore_sticky_posts' => 1,
			'posts_per_page' 	  => $ppp,
			'offset' 			  => $offset,
			'post_status' 		  => 'publish',
		);

		$taxonomy = [
			MAXGRID_POST => array(
				'taxonomy' => MAXGRID_CAT_TAXONOMY,
				'tag_meta_name' => MAXGRID_TAG_TAXONOMY,
			),
			'product' => array(
				'taxonomy' => 'product_cat',
				'tag_meta_name' => 'product_tag',
			),
			'post' => array(
				'taxonomy' => 'category',
				'tag_meta_name' => 'post_tag',
			),
		];

		$categoryfilter = explode(',', sanitize_text_field( $_POST['all_categories'] ) );				
		$args['tax_query'] = array(
				array(
					'taxonomy' => $taxonomy[$post_type]['taxonomy'],
					'field'    => 'slug',
					'terms'    => $categoryfilter,
				),
		);
		$query = new WP_Query( $args );

		$playlist = new Max_Grid_Transient;
		echo $playlist->get_post_items_list($query, $args, $page);

		die();
	}
	
	/**
	 * Set session
	 */
	public function set_session() {
		if ( !session_id() ) {
			session_start();
		}
		$session_name = sanitize_text_field( $_POST['session_name'] );
		$session_value = sanitize_text_field( $_POST['session_value'] );

		$_SESSION[$session_name] = $session_value;
		die();
	}

	/**
	 * Get load more comments
	 *
	 * @return string
	 */
	public function load_more_comments() {
		$post_id 		 = sanitize_text_field( $_POST['post_id'] );
		$page 			 = sanitize_text_field( $_POST['page'] );
		$next_list_token = sanitize_text_field( $_POST['next_list_token'] );

		$maxgrid_options = maxgrid_get_options();
		extract($maxgrid_options);

		if ( get_post_type($post_id) == 'post' ) {
			$form_type = 'comments';
		} else {
			$form_type = 'reviews';
		}

		$offset = ($cpp*$page);
		if ( $offset <= $cpp ) {
			$offset = $cpp;
		}

		$data = array(
				'form_type' 	  => $form_type,
				'post_id' 		  => $post_id,
				//'status' 		  => 'approve',
				'status' 		  => 'all',
				'order' 		  => $comments_order,
				'offset' 		  => $offset,
				'number' 		  => $cpp,
				'next_list_token' => $next_list_token,
				//'comment_notes' => __( 'Your email address will not be published. Required fields are marked', 'max-grid' ),
			);
		$comments_list = new Tabs( $data );

		echo $comments_list->CommentsList();

		die();
	}
	
	/**
	 * Get comment replies
	 *
	 * @return string
	 */
	public function get_comment_replies(){
		$parent_comment_id = sanitize_text_field( $_POST['parent_id'] );
		$post_id = sanitize_text_field( $_POST['post_id'] );

		$data = array(
				'form_type' => 'comments',
			);
		$comments_child_list = new Tabs( $data );	
		echo $comments_child_list->getCommentChild($post_id,$parent_comment_id);

		die();
	}
	
	/**
	 * Get the last posted comment.
	 *
	 * @return string
	 */
	public function get_last_comment(){
		$post_id = $_POST['post_id'];
		$get_last = true;
		$reverse = false;

		$data = array(
				'post_id' => $post_id,
				'status' => 'all',
				'order' => 'DESC',
			);
		
		$comments_list = new Tabs($data);	
		echo $comments_list->CommentsList($get_last, $reverse);

		die();
	}

	/**
	 * Constrict sharethis popup content
	 *
	 * @return string
	 */
	public function get_sharethis_content() {
		if ( !is_maxgrid_premium_activated() ) {
			die();
		}
		$id 		= isset($_POST['id']) ? sanitize_text_field( $_POST['id'] ) : '';
		$post_id 	= isset($_POST['post_id']) ? sanitize_text_field( $_POST['post_id'] ) : '';
		$post_title = isset($_POST['post_title']) ? sanitize_text_field( $_POST['post_title'] ) : '';
		$post_type 	= isset($_POST['post_type']) ? sanitize_text_field( $_POST['post_type'] ) : '';
		$pslug 		= isset($_POST['pslug']) ? sanitize_text_field( $_POST['pslug'] ) : '';
		
		$author_id = get_post_field( 'post_author', $post_id );
		$twitter = get_the_author_meta('maxgrid_twitter', $author_id );

		switch ($post_type) {
			case 'post':
				$data = array(
						'source' 			=> str_replace(' ', '_', get_bloginfo('name')),
						'href' 				=> get_permalink($post_id),
						'title' 			=> get_the_title($post_id),
						'thumbnail_medium' 	=> get_the_post_thumbnail_url($post_id, 'medium'),
						'thumbnail_full' 	=> get_the_post_thumbnail_url($post_id, 'full'),
						'twitter_username' 	=> $twitter,
					);
				$share = new Share($data);
				echo $share->this($post_type, $id, 'popup', $pslug);
				break;
			case 'youtube_stream':
				$data = array(
					'source' => 'youtube',
					'vid_id' => $post_id,
					'href' => 'https://youtu.be/'.$post_id,
					'title' => $post_title,
					'twitter_username' => $twitter,
				);
				$share = new Share($data);

				echo $share->this($post_type, $id, 'popup', $pslug);
				break;
		}
		die();
	}
		
	/**
	 * Decrypt file URL
	 *
	 * @return string
	 */
	public function decrypt_file_url() {
	
		$get_options = maxgrid()->get_options;
		$opt_arry 	 = $get_options->option(MAXGRID_LOGS_OPT_NAME);

		$logged_restrict = is_array($opt_arry) && array_key_exists('download_logged_in', $opt_arry) && !is_user_logged_in() ? true : null;
		
		if ( ( isset($logged_restrict) || isset($_POST['demo']) && $_POST['demo'] == true ) && !is_user_logged_in() ) {
			echo 'ERROR';
		} else {
			echo maxgrid_encrypt( sanitize_text_field( $_POST['url'] ), 'd' );
		}
		die();
	}
	
	/**
	 * Construct the lightbox body content
	 *
	 * @return string
	 */
	public function lightbox_body() {
		global $post, $single_full_width;
		$lightbox_id 	= sanitize_text_field( $_POST['lightbox_id'] );
		$featured_type 	= sanitize_text_field( $_POST['featured_type'] );
		$data_href 		= sanitize_text_field( $_POST['data_href'] );
		$post_type 		= sanitize_text_field( $_POST['post_type'] );

		$post_id = $lightbox_id;
		
		$get_options 	= maxgrid()->get_options;
		$track_options 	= $get_options->option(MAXGRID_LOGS_OPT_NAME);
		
		$template 		= maxgrid()->template;
		$args 			= array('post_id' => $post_id, 'source_type' => $post_type, 'preset_name' => $_POST['pslug']);
		$elements 		= new Elements($args);
		$get_presets 	= new getPresets($args);
		
		$l_o = $get_presets->rows('lightbox_row');
		$b_o = $get_presets->rows('blocks_row');

		$url_file = maxgrid()->get_post_meta->download($post_id)['file'];

		if (is_singular( MAXGRID_POST ) || is_singular( 'post' ) ) {
			if (is_singular( MAXGRID_POST )) {
				$terms = get_the_terms( $post_id , MAXGRID_CAT_TAXONOMY );
				$category_slug_name = MAXGRID_CAT_TAXONOMY;
			} else {
				$terms = get_the_terms( $post_id , 'category' );
				$category_slug_name = 'category';
			}
			$i = 0;
			$len = count($terms);
			$break = 3; // Max number of categories to show

			$uncategorized_textdomain = __('Uncategorized', 'max-grid').' ';
			if( $terms == "" ) {
				$in = '<strong>'.$uncategorized_textdomain.'</strong>';
			}else{
				$in = '';
			}
			$category_name = "";
			foreach ( (array)$terms as $term ) {
				if( $i == $break ) break;
				if ( $i == $len - 1 or $i == $break -1 ) {
					$category_name .= '<a name="category" id="catnamepost" href="'.home_url().'/'.$category_slug_name.'/'.$term->slug.'" >'.$term->name.'</a>';
				} else {
					$category_name .= '<a href="'.home_url().'/'.$category_slug_name.'/'.$term->slug.'" >'.$term->name.'</a>, ';
				}
				$i++;
			}

			$file_size =  maxgrid_get_remote_filesize($url_file);
			$dl_count = countMeta::get_count($post_id, MAXGRID_DOWNLOAD_META_KEY);	
			$download_meta = '<div class="download_meta">
										<span>'.maxgrid_download_basename($url_file).' | '.$file_size.' | <span class="ajax-dl-counter">'.$dl_count.'</span> downloads | Categories: '.$in.$category_name.'</span>
									</div>';	
		}

		
		$content_post = get_post($post_id);
		/*$content = $content_post->post_content;
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
		
		//content = get_post_field('post_content', $post_id);
		$the_content = wpautop( $content, true );*/
		$the_content = $content_post->post_content;
		
		$thumb = get_the_post_thumbnail_url($post_id, 'large');
		$args = array(
				'post_id'	   => $post_id,
				'size' 		   => 'full',
				'get_thumb'    => true,
				'return_empty' => true,
			);
		$thumb_full = $template->get_post_thumbnail($args);
		
		// return small size
		$args['size'] = 'small';
		$thumb_small = $template->get_post_thumbnail($args);
		
		$author_id = get_post_field( 'post_author', $post_id );

		$email = get_the_author_meta('email', $author_id );
		$twitter = get_the_author_meta('maxgrid_twitter', $author_id );
		$authort_webesite = get_the_author_meta('url', $author_id );
		$brn_size = '18px';

		$download_status = 'none'; // newest views liked downloaded
		$download_type = 'free';

		$has_thumbnail = 'has-post-thumbnail';
		if ( $featured_type == "image" && is_singular() && !has_post_thumbnail($post_id) ) {
			$has_thumbnail = 'has-not-post-thumbnail';
		}

		if ( $post_type == 'product' ) {
			$l_b 	  	 = $get_presets->rows('lightbox_row');		
			$size 	  	 = isset($l_b['add_cart_size']) ? ' '.$l_b['add_cart_size'] : ' small';
			$spin 	  	 = isset($l_b['spin_layout']) ? ' '. str_replace('_', '-', $l_b['spin_layout']) : ' inner-spin';
			$rounded  	 = isset($l_b['border_radius']) ? ' '.$l_b['border_radius'] : ' pointed';
			$thin 	  	 = isset($l_b['sign_style']) ? ' '.$l_b['sign_style'] : ' thick';
			$disable_qty = isset($l_b['disable_qty']) ? true : null;
			
			$label = __('Add To Cart', 'max-grid');
			
			$args  = array(
				'product_id'  => $post_id,
				'label' 	  => $label,
				'size' 		  => $size,
				'spin' 		  => $spin,
				'rounded' 	  => $rounded,
				'thin' 		  => $thin,
				'disable_qty' => $disable_qty,
			);
			
			$dl_or_add_btn = $elements->addToCart($args);
		} else if ( $post_type == MAXGRID_POST ) {			
			$once = isset($track_options['re_download_count']) && ( $track_options['re_download_count'] == 're_download_count' || $track_options['re_download_count'] == 'true') ? true : false;
			
			$dl_or_add_btn = '<div class="bownload-btn_container">
								<div>
									<span id="maxgrid_download_single" class="maxgrid-button green download biggest" data-post-id="'.$post_id.'" data-meta-key="'.MAXGRID_DOWNLOAD_META_KEY.'" data-lightbox="" data-once="'.$once.'" data-href="'.maxgrid_encrypt($url_file, 'e').'">Download</span>
									<span class="ajax_dl-spiner"></span>								
								</div>
							</div>';
		} else {
			$dl_or_add_btn = '';
		}	

		$the_title = '<div class="post_title_lightbox">
						  <h1 class="maxgrid_download_title download-entry-title">' . get_the_title($post_id) . '</h1>
					 </div>';

		// Get Lightbox Bottom Content
		$bottom_content = '';
		$heding_html = '';

		$standard_post = array('post', 'product', MAXGRID_POST);		
		if ( in_array($post_type, $standard_post) ) {
			$args = array(
				'date' => maxgrid_published_on_post(get_option( 'date_format' ), $post_id),
			);

			$data = array(
				'author_name' 	=> maxgrid_get_author($author_id, 50),
				'author_url' 	=> $authort_webesite,
				'thumbnail_url' => get_avatar_url($email),
				'published_on' 	=> $template->dateTime($args),
				'social_media' 	=> '<div id="twitter_lightbox_container" data-twitter-id="'.$twitter.'"></div>',
				'class' 		=> 'post',
			);
			$heding_html = $template->author_meta($data);

			// Get Downloads & Sales Count
			$dl_count = '<div class="downloads-stat-grid"><span class="cover-stat-downlod hide-phone"><span class="dl-count">' . countMeta::get_count($post_id, MAXGRID_DOWNLOAD_META_KEY) . '</span></span></div>';

			$get_sales_count = get_post_meta($post_id,'total_sales', true);
			$sales_count = '<div class="downloads-stat-grid"><span class="cover-stat-sales hide-phone"><span class="sales-count">' . sprintf( _n( '%s Sale', '%s Sales', $get_sales_count, 'max-grid' ), $get_sales_count) . '</span></span></div>';
			$sales_dl_count = $post_type=='product' ? $sales_count : $dl_count;

			$tooltip_style = isset($l_o['tooltip_style']) ? $l_o['tooltip_style'] : 'dark';
			
			$average_rating = '';
			$post_likes 	= '';
			$share_this 	= '';
			
			if ( is_maxgrid_premium_activated() ) {
				$share_data = array(
					'source' => str_replace(' ', '_', get_bloginfo('name')),
					'vid_id' => $lightbox_id,
					'href' => get_permalink($post_id),
					'title' => get_the_title($post_id),
					'thumbnail_medium' => get_the_post_thumbnail_url($post_id, 'medium'),
					'thumbnail_full' => get_the_post_thumbnail_url($post_id, 'full'),
					'twitter_username' => $twitter,
				);

				// ShareThis
				$all_media = array(
					'facebook', 'twitter', 'google', 'blogger', 'reddit','tumblr', 'pinterest', 'vkontakte', 'linkedin', 'stumbleupon', 'email'
					);
				$media = get_option('lightbox_sharethis_media', $all_media);
				$count = count($media)-1;

				$share_this = '<div class="maxgrid-sharethis-container" id="share-trigger" data-count="'.$count.'" data-post-type="post" data-post-id="'.$post_id.'">
								<div class="ytb-share-btn">'. __( 'share', 'max-grid' ) . '</div>
								</div>';				
				$post_likes = maxgrid()->post_like->construct($post_id,$is_single=true, $tooltip_style);
			}
			
			if ( is_maxgrid_download_activated() || is_maxgrid_woo_activated() ) {
				// Get Average Rating
				$get_average_data = array(
							'post_id' 	 => $post_id,
							'stars_size' => 'small',
							'link' 		 => 'disabled',
						);
				$rating = maxgrid()->rating;
				$average_rating = $rating->get_average($get_average_data);
			}
			
			$post_ratings = $post_type=='product' || $post_type==MAXGRID_POST ? $sales_dl_count.$average_rating : $post_likes;

			if ( $share_this != '' || $post_ratings != '' ) {
				$bottom_content .= '<div class="lightbox_shareline">
										<span class="shareline">
											<div class="single-social-container">
												<div class="single-social-share">' . $share_this . '</div>
											</div>

											<div class="stats-count lightbox">
												<div class="single-post-like">' . $post_ratings . '</div>
											</div>
										</span>
									</div>';
			}
			
			$bottom_content .= '<!-- Right Column -->
						<div class="right-col single-download-summary">
							<div class="download-entry-summary">';

			$bottom_content .= '<!-- Add to Cart or Download -->
					' . $the_title . '
					' . $dl_or_add_btn . '

					<!-- The Excerpt Content -->
					<div class="dld-summary-content">';

			$args = array(
					'post_id' 		=> $post_id,
					'the_content'	=> $the_content,
					'download_meta'	=> isset($download_meta) ? $download_meta : '',
				);

			$Max_Grid_Reviews_Tabs = new Max_Grid_Reviews_Tabs;	
			$bottom_content .= $Max_Grid_Reviews_Tabs->reviews_tabs($args);

			$bottom_content .= '</div>';

		}

		$magnify_type = isset($l_o['jquery_img_zoom']) ? $l_o['jquery_img_zoom'] : 'zoom_in';
		$magnify_type = is_maxgrid_premium_activated() ? $magnify_type : 'zoom_in';
		
		// Grt audio bar
		$audio_player 	  = get_post_meta ($post_id, 'maxgrid_audio_file', true);
		$audio_player_html = $elements->AudioPlayer(array('target' => 'lightbox'));
			
		$sd_player_html   = $elements->SoundCloudPlayer(array('target' => 'lightbox'));
		
		// Construct Lightbox Modal Content
		$args = array(
			'post_type' 	  => $post_type,
			'post_id' 		  => $post_id,
			'download_status' => $download_status,
			'has_thumbnail'   => $has_thumbnail,
			'download_type'   => $download_type,
			'heding_html' 	  => $heding_html,
			'magnify_type' 	  => $magnify_type,
			'featured_type'   => $featured_type,
			'audio_player' 	  => $audio_player_html,
			'sd_player' 	  => $sd_player_html,
			'thumb_full' 	  => $thumb_full,
			'thumb_small' 	  => $thumb_small,
			'thumbnail_url'   => get_post_meta ($post_id, 'maxgrid_embed_mp4_url', true),
			'data_href' 	  => $data_href,
			'thumb' 		  => $thumb,
			'bottom_content'  => $bottom_content,
		);
		$post_content = new Max_Grid_Lightbox;
		echo $post_content->get_post_content($args);
		die();
	}
}

new Max_Grid_Ajax_Request;