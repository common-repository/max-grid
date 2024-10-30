<?php
/**
 * Max Grid Builder - Grid Templates
 */

use \MaxGrid\getOptions;
use \MaxGrid\getPresets;
use \MaxGrid\Elements;
use \MaxGrid\countMeta;
use \MaxGrid\share;
use \MaxGrid\Template;

defined( 'ABSPATH' ) || exit;

/**
 * @class Max_Grid_Templates.
 */
class Max_Grid_Templates {
	
	/**
     * Constructor.
	 *
	 * @param $data array
	 */
	public function __construct($data=array()) {		
		
	}
	
	/**
	 * Grid block constructor
	 *
	 * @param array $data Grid data.
	 *
	 * @return string
	 */
	public function grid($data) {
		global $post, $wp, $wpdb;

		extract($data);
		extract(maxgrid_get_meta_options());	
		extract(maxgrid_get_options());

		$this->post_id = $post_id;

		$url = get_permalink($post_id);
		$url = $post_type == 'download' ? esc_url($url).'?history=go' : esc_url($url);

		#-----------------------------------------------------------------#
		# Get The Title
		#-----------------------------------------------------------------#

		if (empty(get_the_title($post_id))) {
			$the_title = 'Auto Draft';
		} else {
			$the_title = get_the_title($post_id);
		}

		#-----------------------------------------------------------------#
		# Get Post Categories	
		#-----------------------------------------------------------------#

		$content_post = get_post($post_id);
		$the_content = $content_post->post_content;
		
		$category = array(
					MAXGRID_POST => get_terms(MAXGRID_CAT_TAXONOMY, array('hide_empty' => false)),
					'product' => get_terms('product_cat', array('hide_empty' => true)),
					'post' => get_categories(),
				);
		$terms = array(
					MAXGRID_POST => get_the_terms( $post_id , MAXGRID_CAT_TAXONOMY ),
					'product' => get_the_terms( $post_id , 'product_cat' ),
					'post' => get_the_terms( $post_id , 'category' ),
				);

		#-----------------------------------------------------------------#
		# The Featured
		#-----------------------------------------------------------------#

		$featured_type = maxgrid()->get_post_meta->grid($post_id)['f_type'];
		
		$template = maxgrid()->template;
		$args = array(
				'post_id' 		=> $post_id,
				//'return_empty'=> true,
			);
		$thumb = $template->get_post_thumbnail($args);		
		
		$args = array(
				'post_id' 	=> $post_id,
				'size'		=> 'small',
			);		
		$small_thumb = $template->get_post_thumbnail($args);
		
		#-----------------------------------------------------------------#
		# Get Post Excerpt - List & Grid views
		#-----------------------------------------------------------------#

		$readmore_btn_title = isset($options['post_description']['label_text']) ? $options['post_description']['label_text'] : __('Read More', 'max-grid');	
		$the_excerpt = preg_replace('/\[[^\]]+\]/', '', $the_content);  # strip shortcodes, keep shortcode content
		$limit = $description_row['excerpt_length'];
		$list_limit = 100;
		if ( $full_content == 'off') {
			$the_excerpt  = wp_trim_words($the_excerpt, $limit);
		}

		$btn_readmore 	  = isset($options['post_description']['readmore']) || !isset($options['post_description']) ? ' btn' : '';
		$excerpt_metabox  = maxgrid()->get_post_meta->grid($post_id)['excerpt'];
		$the_list_excerpt = isset($excerpt_metabox) ? $excerpt_metabox : wp_trim_words($the_excerpt, $list_limit).'..';
		
		#-----------------------------------------------------------------#
		# Call Class Elements to add advanced Featured Image
		#-----------------------------------------------------------------#

		$ribbon_position = isset($blocks_row['ribbon_pos']) ? $blocks_row['ribbon_pos'] : 'left';	
		$args = array(
				'post_id' 		  => $post_id,
				'post_type'		  => $post_type,
				'preset_name'	  => $preset_name,
				'layout_type' 	  => $layout_type,
				'the_title'		  => $the_title,
				'the_excerpt'	  => $the_excerpt,
				'ribbon_position' => $ribbon_position,
				'share_icon_size' => '15px',
				'grid'		  	  => true,
				'featured_type'	  => $featured_type,
				'page'			  => $page,
				'thumb'			  => $thumb,
				'small_thumb'	  => $small_thumb,
				'url' 			  => $url,
				'terms' 		  => $terms,

			);
		$block_elements = new Elements($args);

		#-----------------------------------------------------------------#
		# Display Read More Link
		#-----------------------------------------------------------------#

		$readmore_style = isset($options['post_description']['readmore']) && $options['post_description']['readmore'] !== 'disabled' ? 'button' : 'text';

		if ( $readmore_style == 'text' ) {
			$readmore_link = '...<a href="'.$url.'" id="post_grid_read_more" data-page-id="'.$page.'" class="read-more">'.$readmore_btn_title.'</a>';
		} else {
			$readmore_link = '...';
		}	

		#-----------------------------------------------------------------#
		# Display Post Views
		#-----------------------------------------------------------------#
		
		$likes_meta_key  = '';
		$views_meta_key  = '';
		$list_post_views = '';
		
		if ( is_maxgrid_premium_activated() ) {
			$views_meta_key = maxgrid_use_custom_post_meta_key( 'views' );
			$views_meta_key = $views_meta_key ? $views_meta_key : MAXGRID_VIEWS_META_KEY;
			
			$get_count = countMeta::get_count($post_id, $views_meta_key);
			$post_views = '<span class="cover-stat-views hide-phone"><span class="views-count">'.$get_count.'</span></span>';
			
			$post_views_html = '<div class="views-stat-grid">'.$post_views.'</div>';
			$list_post_views = $post_type!='product' && $post_type!=MAXGRID_POST ? $post_views_html : '';
			
			// Post likes
			$likes_meta_key = maxgrid_use_custom_post_meta_key( 'likes' );
			$likes_meta_key = $likes_meta_key ? $likes_meta_key : MAXGRID_LIKES_META_KEY;
		}

		#-----------------------------------------------------------------#
		# Display Post Categories
		#-----------------------------------------------------------------#

		$current_url = home_url(add_query_arg(array(),$wp->request));

		$i = 0;
		$len = count($terms[$post_type]);
		$break = 3; // Show only 3 categories

		$in_textdomain = __('In', 'max-grid').' ';
		$uncategorized_textdomain = __('Uncategorized', 'max-grid').' ';
		if( $terms[$post_type] == "" ) {
			$in = $in_textdomain.'<strong>'.$uncategorized_textdomain.'</strong>';
		}else{
			$in = $in_textdomain;
		}

		$current_url=strtok($_SERVER["REQUEST_URI"],'?');
		$category_url = home_url().$current_url.'?category[]=';
		$category_name = '';
		foreach ( $terms[$post_type] as $term ) {
			if( $i == $break ) break;
			if ( $i == $len - 1 or $i == $break -1 ) {
				$category_name .= '<a name="category" id="catnamepost" class="maxgrid-post-catname" data-catslug="'.$term->slug.'" href="#">'.$term->name.'</a>';
			} else {
				$category_name .= '<a name="category" id="catnamepost" class="maxgrid-post-catname" data-catslug="'.$term->slug.'" href="#">'.$term->name.'</a>';
			}
			$i++;
		}

		#-----------------------------------------------------------------#
		# Ribbons
		#-----------------------------------------------------------------#
		
		$gridlayout_ribbons = '';
		$listlayout_ribbons = '';
		
		if( $ribbon == 'on' ) {		
			$newest_ribbon 		= isset($blocks_row['disable_newest_ribbon']) && maxgrid_string_to_bool($blocks_row['disable_newest_ribbon']) == 1 ? true : false;
			$views_ribbon 		= isset($blocks_row['disable_views_ribbon']) && maxgrid_string_to_bool($blocks_row['disable_views_ribbon']) == 1 ? true : false;
			$liked_ribbon 		= isset($blocks_row['disable_liked_ribbon']) && maxgrid_string_to_bool($blocks_row['disable_liked_ribbon']) == 1 ? true : false;
			$downloaded_ribbon 	= isset($blocks_row['disable_downloaded_ribbon']) && maxgrid_string_to_bool($blocks_row['disable_downloaded_ribbon']) == 1 ? true : false;
			$onsale_ribbon 		= isset($blocks_row['disable_onsale_ribbon']) && maxgrid_string_to_bool($blocks_row['disable_onsale_ribbon']) == 1 ? true : false;
			$bestseller_ribbon 	= isset($blocks_row['disable_bestseller_ribbon']) && maxgrid_string_to_bool($blocks_row['disable_bestseller_ribbon']) == 1 ? true : false;		
			$newest_icon 		= isset($blocks_row['newest_icon']) ? $blocks_row['newest_icon'] : 'fa-exclamation';
			$views_icon 		= isset($blocks_row['views_icon']) ? $blocks_row['views_icon'] : 'fa-none';
			$liked_icon 		= isset($blocks_row['liked_icon']) ? $blocks_row['liked_icon'] : 'fa-heart';
			$downloaded_icon 	= isset($blocks_row['downloaded_icon']) ? $blocks_row['downloaded_icon'] : 'fa-download';
			$onsale_icon 		= isset($blocks_row['onsale_icon']) ? $blocks_row['onsale_icon'] : 'fa-none';
			$bestseller_icon	= isset($blocks_row['bestseller_icon']) ? $blocks_row['bestseller_icon'] : 'fa-none';

			if ( isset($blocks_row['warpped']) && $blocks_row['warpped'] == true ) : $ribbon_style = " wrapped";else:	$ribbon_style = "";endif;
			$meta_key = !isset($meta_key) ? 'date' : $meta_key;
						
			$data = [
				'classes' => array(
						'date' 				=> 'newest',
						$views_meta_key 	=> 'views',
						$likes_meta_key		=> 'liked',
						MAXGRID_DOWNLOAD_META_KEY 	=> 'downloaded',
						'onsale' 			=> 'onsale',
						'total_sales' 		=> 'bestseller views',
					),
				'labels' => array(
						'date' 				=> isset($blocks_row['newest_txt']) ? $blocks_row['newest_txt'] : 'NEW',
						$views_meta_key		=> isset($blocks_row['views_txt']) ? $blocks_row['views_txt'] : 'POPULAR',
						$likes_meta_key		=> isset($blocks_row['liked_txt']) ? $blocks_row['liked_txt'] : 'MOST',
						MAXGRID_DOWNLOAD_META_KEY => isset($blocks_row['downloaded_txt']) ? $blocks_row['downloaded_txt'] : 'MOST',
						'onsale' 			=> isset($blocks_row['onsale_txt']) && $blocks_row['onsale_txt'] != '' ? $blocks_row['onsale_txt'] : 'SALE!',
						'total_sales' 		=> isset($blocks_row['total_sales_txt']) ? $blocks_row['total_sales_txt'] : 'BEST SELLER',
					),
				'icons' => array(
						'date' 				=> isset($newest_icon) && $newest_icon !== 'fa-none' ? '<i class="fa '.$newest_icon.'" aria-hidden="true"></i>' : '',
						$views_meta_key		=> isset($views_icon) && $views_icon !== 'fa-none' ? '<i class="fa '.$views_icon.'" aria-hidden="true"></i>' : '',
						$likes_meta_key		=> isset($liked_icon) && $liked_icon !== 'fa-none' ? '<i class="fa '.$liked_icon.'" aria-hidden="true"></i>' : '',
						MAXGRID_DOWNLOAD_META_KEY 	=> isset($downloaded_icon) && $downloaded_icon !== 'fa-none' ? '<i class="fa '.$downloaded_icon.'" aria-hidden="true"></i>' : '',
						'onsale' 			=> isset($onsale_icon) && $onsale_icon !== 'none' ? '<i class="fa '.$onsale_icon.'" aria-hidden="true"></i>' : '',
						'total_sales' 		=> isset($bestseller_icon) && $bestseller_icon !== 'fa-none' ? '<i class="fa '.$bestseller_icon.'" aria-hidden="true"></i>' : '',
					)	
			];

			$most_data = array(
					'all_cat' 	=> $category[$post_type],
					'exclude' 	=> $exclude_category,
					'post_type' => $post_type,
					'orderby' 	=> $meta_key,
					'meta_key' 	=> $orderby,
				);

			// Ribbons
			$custom_meta = array(
				'date' 			=> $newest_ribbon,
				$views_meta_key	=> $views_ribbon,
				$likes_meta_key	=> $liked_ribbon,
				MAXGRID_DOWNLOAD_META_KEY => $downloaded_ribbon,
				'onsale' 		=> $onsale_ribbon,
				'total_sales' 	=> $bestseller_ribbon,
			);

			$postIn = array();
			foreach($custom_meta as $key => $value ) {
				if ( ( $post_type=='product' && $key == MAXGRID_DOWNLOAD_META_KEY ) || !$value ) {
					continue;
				}

				$most_data['orderby'] = $key;			
				$id = maxgrid_get_the_most($most_data);
				$postIn[$id] = $key;
				if ( $post_type == 'product' && $key == 'onsale' ) {
					$_product = wc_get_product( $args['post_id'] );
					if($_product->get_sale_price()){
						$postIn[$args['post_id']] = $key;
					}
				}
			}

			$ribbon_type = isset($blocks_row['ribbon_type']) ? $blocks_row['ribbon_type'] : 'corner-ribbon';

			if ( array_key_exists($post_id, $postIn)) {			
				$position = isset($ribbon_position)&&$ribbon_position!=''? ' '.$ribbon_position : '';
				$gridlayout_ribbons = '<div class="'.$ribbon_type.' '.$data['classes'][$postIn[$post_id]].$ribbon_style.$position.'" data-status="'.$data['classes'][$meta_key].'"><span>'.$data['labels'][$postIn[$post_id]].' '.$data['icons'][$postIn[$post_id]].'</span></div>';
				$listlayout_ribbons = '<div class="'.$ribbon_type.' '.$data['classes'][$postIn[$post_id]].' list'.$ribbon_style.' left" data-status="'.$data['classes'][$meta_key].'"><span>'.$data['labels'][$postIn[$post_id]].' '.$data['icons'][$postIn[$post_id]].'</span></div>';
			}
		}

		#-----------------------------------------------------------------#
		# Block Elements
		#-----------------------------------------------------------------#

		$comment_count = get_comments_number( $post_id );
		$comment_url = esc_url($url.'#comments');
		if ( $comment_count > 1 ){
			$comments = '<a href="'.$comment_url.'" data-page-id="'.$page.'">'.$comment_count.' '.__('Comments', 'max-grid').'</a>';
		} else if ( $comment_count == 1 ){
			$comments = '<a href="'.$comment_url.'" data-page-id="'.$page.'">'.__('One Comment', 'max-grid').'</a>';
		} else if ( $comment_count == 0 ){
			$comments = '<a href="'.$comment_url.'" data-page-id="'.$page.'">'.__('No Comment', 'max-grid').'</a>';	
		}

		// Author bar
		$author_id 	= get_post_field( 'post_author', $post_id );
		$author 	= '<a data-authorname="'.$author_id.'" class="maxgrid-post-author" href="#">'.maxgrid_get_author($author_id, 50).'</a>';

		// Categories
		if ( isset($options['post_description']['categories']) && $options['post_description']['categories'] != 'disabled' ) {		
			$grid_category = $in.$category_name;
		} else {
			$grid_category = '';
		}

		#-----------------------------------------------------------------#
		# Template Constructor
		#-----------------------------------------------------------------#

		$def_data = array( 
						'featured_bar' 		=> 'Featured box',
						'post_title' 		=> 'Post title',
						'post_description' 	=> 'Post description',
						'divider_bar' 		=> 'Divider',
						'info_bar' 			=> 'Author, date and comment bar',
						'stats_bar' 		=> 'Post Stats',
		);

		$layout_data = isset($options) ? $options : $def_data;
		$maxgrid_template = '';	
		$index = 1;

		// List View Elements
		$author_id = get_post_field( 'post_author', $post_id );
		$twitter = get_the_author_meta('maxgrid_twitter', $author_id );
		$share_data = array(
				'source' 			=> str_replace(' ', '_', get_bloginfo('name')),
				'href' 				=> get_permalink($post_id),
				'title' 			=> get_the_title($post_id),
				'thumbnail_medium' 	=> get_the_post_thumbnail_url($post_id, 'medium'),
				'thumbnail_full' 	=> get_the_post_thumbnail_url($post_id, 'full'),
				'twitter_username' 	=> $twitter,
			);
		
		$is_woo = $post_type=='product' ? 'woo_' : '';
		
		if ( is_maxgrid_premium_activated() ) {
			$share = new Share($share_data);
			$args = array(
					'layout' => $layout_type,
					'id' 	 => $is_woo.'stats_bar',
					'pslug'  => $preset_name,
					'return' => 'link'
				);
			$sharethis_list = '<span>' . $share->get_media($args) . '</span>';
		}
			
		$list_author = '<span class="author_name list">'.__('By', 'max-grid').' '.$author.'</span>';

		// Published On
		$published_on 		 = maxgrid_published_on_post(__('F j, Y', 'max-grid'), $post_id);

		$list_published_on 	 = '<span class="list-date">'.$published_on.'</span>';
		$list_comments_count = '<span class="list-comments">'.$comments.'</span>';
		$list_category 		 = '<span class="list-categories">'.$in.$category_name.'</span>';

		
		
		// Get download
		$maxgrid_download_file = maxgrid()->get_post_meta->download($post_id)['file'];
		$is_download 		 = wp_check_filetype($maxgrid_download_file)['ext'] ? true : null;
		
		$dl_count 			 = countMeta::get_count($post_id, MAXGRID_DOWNLOAD_META_KEY);
		$vlist_dl_count 	 = '<div class="downloads-stat-grid"><span class="cover-stat-downlod hide-phone"><span class="dl-count">' . $dl_count . '</span></span></div>';
		if ( !isset($is_download) ) {
			$vlist_dl_count  = '';
		}

		$sales_count 		 = get_post_meta($post_id,'total_sales', true);

		$vlist_sales_count 	 = '<div class="downloads-stat-grid"><span class="cover-stat-sales hide-phone"><span class="sales-count">' . sprintf( _n( '%s Sale', '%s Sales', $sales_count, 'max-grid' ), $sales_count ) . '</span></span></div>';	
		$sales_dl_count 	 = $post_type=='product' ? $vlist_sales_count : $vlist_dl_count;	
		
		// Grt audio bar
		$audio_file 	 = get_post_meta ($post_id, 'maxgrid_audio_file', true);
		$audio_file_html = $block_elements->AudioPlayer();
				
		$sd_code	 	 = wp_specialchars_decode(get_post_meta ($post_id, 'soundcloud_code', true));		
		$sd_player_html  = $block_elements->SoundCloudPlayer();
		
		$tooltip_style	   = isset($blocks_row['tooltip_style']) ? $blocks_row['tooltip_style'] : 'dark';
		$list_post_likes   = '';
		$list_post_ratings = '';		
		$average_rating    = '';
		
		if ( is_maxgrid_download_activated() || is_maxgrid_woo_activated() ) {
			// Get Average Rating
			$get_average_data = array(
						'post_id' => $post_id,
						'stars_size' => 'small',
					);
			$rating 			 = maxgrid()->rating;
			$average_rating 	 = $rating->get_average($get_average_data);
		}
		
		if ( is_maxgrid_premium_activated() ) {			
			$list_post_likes 	 = '<div class="like-stat-grid">' . maxgrid()->post_like->construct($post_id, $layout_type, $tooltip_style) . '</div>';
			$list_post_ratings 	 = $post_type=='product' || $post_type==MAXGRID_POST ? $average_rating : $list_post_likes;
		}
		
		// Grid View Elements
		$prev_key 			 = null;
		$no_download 		 = false;
		$no_audio 			 = false;
		$is_divider 		 = null;	

		foreach ( $layout_data as $key => $value ) {
			
			// Check stats bar has border top		
			$stats_bar_names = array(
				'youtube_stream' => 'ytb_vid_stats_bar',
				'product' 		 => 'woo_stats_bar',
				'post' 			 => 'stats_bar',
				'download' 		 => 'stats_bar',
				);

			// Stats Bar CSS
			$stats_bar_key = str_replace('bar', 'row', $stats_bar_names[$post_type]);
			$border_top    = isset($get_presets->rows($stats_bar_key)['border_top_width']) ? $get_presets->rows($stats_bar_key)['border_top_width'] : '';			
			if ( $border_top != '' && $border_top != 0  && $border_top != '0' ) {
				$is_divider = true;
			}
			$is_twice = strpos($prev_key, 'divider_bar') !== false && $key == 'download_bar' && !isset($is_download) && isset($is_divider);
			
			if ( $is_twice || $key == 'rows_options' ){
				continue;
			}
			
			$grid_author 		 = '';
			$grid_published_on 	 = '';
			$grid_comments_count = '';
			$sharethis_grid 	 = '';		
			$grid_dl_count 	 	 = '';
			$grid_post_views 	 = '';
			$grid_post_ratings 	 = '';
			
			switch ($key) {
				case "featured_bar":				
					$stuck_on_featured = isset($blocks_row['stuck_on_featured']) && maxgrid_string_to_bool($blocks_row['stuck_on_featured']) == 1 ? true : false;
					$bloc_h_fit  = isset($featured_options['fit_width']) && maxgrid_string_to_bool($featured_options['fit_width']) == 1 ? ' bloc_h_fit' : '';

					if ( $index > 1 || $stuck_on_featured == true ) {
						$ribbon = $gridlayout_ribbons;
						$gridlayout_ribbons = '';
					} else {
						$ribbon = '';
					}

					$first_row = $index == 1 ? ' is-first-row' : '';
					$maxgrid_template .= $block_elements->Featured('grid', $ribbon, $bloc_h_fit, $first_row);
					break;
				case "audio_bar":		
					if ( $audio_file == '' && $sd_code == '' || !is_maxgrid_premium_activated() ) {
						$no_audio = true;
						break;
					}
					
					$bloc_h_fit  = isset($audio_options['fit_width']) && maxgrid_string_to_bool($audio_options['fit_width']) == 1 ? ' bloc_h_fit' : '';
					
					if ( $sd_code != '' ) {
						$maxgrid_template .= $block_elements->SoundCloudPlayer(array('h_fit' => $bloc_h_fit));
						break;
					}
					
					$maxgrid_template .= $block_elements->AudioPlayer(array('h_fit' => $bloc_h_fit));
					break;				
				case "post_title":					
					$bloc_h_fit  = isset($title_options['fit_width']) && maxgrid_string_to_bool($title_options['fit_width']) == 1 ? ' bloc_h_fit' : '';
					$title_link  = isset($title_options['link']) ? $title_options['link'] : 'external_link';
					if ( $title_link == 'lightbox') {						
						switch ($featured_type) {
							case "image":
								$data_href = maxgrid()->get_post_meta->grid($post_id)['thumb_url'];
								break;
							case "youtube":
								$data_href = maxgrid()->get_post_meta->grid($post_id)['youtube_url'];
								break;
							case "vimeo":
								$data_href = maxgrid()->get_post_meta->grid($post_id)['vimeo_url'];
								break;
							case "mp4":
								$data_href = maxgrid()->get_post_meta->grid($post_id)['mp4_url'];
								break;
						}
						$html_title = '<a id="lightbox-enabled" class="maxgrid_title" href="#" data-page-id="'.$page.'" data-lightbox-id="'.$post_id.'" data-post-type="'.$post_type.'" data-featured-type="'.$featured_type.'" data-href="'.$data_href.'" data-the-title="'.base64_encode($the_title).'">'.$the_title.'</a>';
					} else if ($title_link == 'external_link') {
						$html_title = '<a class="maxgrid_title" href="'.$url.'" data-page-id="'.$page.'">'.$the_title.'</a>';
					} else if ($title_link == 'none') {
						$html_title = $the_title;
					}
					
					$maxgrid_template .= '<div class="grid-layout-the-title' . $bloc_h_fit . '">'.$html_title.'</div>';

					break;
				case "add_to_cart_bar":
					if ( $post_type != 'product' || !is_maxgrid_woo_activated() ) {
						break;
					}
					$bloc_h_fit  = isset($add_to_cart_options['fit_width']) && maxgrid_string_to_bool($add_to_cart_options['fit_width']) == 1 ? 'bloc_h_fit' : '';
					$label = $add_to_cart_options['add_to_cart_label'];
					
					$size 	  = isset($add_to_cart_options['add_cart_size']) ? ' '.$add_to_cart_options['add_cart_size'] : ' small';
					$spin 	  = isset($add_to_cart_options['spin_layout']) ? ' '. str_replace('_', '-', $add_to_cart_options['spin_layout']) : ' inner-spin';
					$rounded  = isset($add_to_cart_options['border_radius']) ? ' '.$add_to_cart_options['border_radius'] : ' pointed';
					$thin 	  = isset($add_to_cart_options['sign_style']) ? ' '.$add_to_cart_options['sign_style'] : ' thick';
					$disable_qty = isset($add_to_cart_options['disable_qty']) ? true : null;
					
					$args  = array(
						'product_id'  => $post_id,
						'label' 	  => $label,
						'size' 		  => $size,
						'spin' 		  => $spin,
						'rounded' 	  => $rounded,
						'thin' 		  => $thin,
						'bloc_h_fit'  => $bloc_h_fit,
						'disable_qty' => $disable_qty,
					);
					
					$maxgrid_template .= $block_elements->addToCart($args);

					break;				
				case "download_bar":
					if ( $post_type != MAXGRID_POST && !isset($is_download) || !is_maxgrid_download_activated() ) {
						$no_download = true;
						break;
					}

					$bloc_h_fit  = isset($download_options['fit_width']) && maxgrid_string_to_bool($download_options['fit_width']) == 1 ? ' bloc_h_fit' : '';
					$args = array('view' => 'grid', 'fit' => $bloc_h_fit, 'class' => 'in-block');
					$maxgrid_template .= $block_elements->Download($args);

					break;
				case "average_rating_bar":
					if ( $post_type != MAXGRID_POST && $post_type != 'product' || !is_maxgrid_woo_activated() && !is_maxgrid_download_activated() ) {
						break;
					}
					$bloc_h_fit  = isset($average_rating_options['fit_width']) && maxgrid_string_to_bool($average_rating_options['fit_width']) == 1 ? ' bloc_h_fit' : '';
					$maxgrid_template .= $block_elements->AverageRating($bloc_h_fit);

					break;				
				case "post_description":
					$bloc_h_fit  = isset($description_row['fit_width']) && maxgrid_string_to_bool($description_row['fit_width']) == 1 ? ' bloc_h_fit' : '';
					if ( $readmore_style == 'text' ) {
						$readmore_btn = '';
					} else {
						$readmore_btn = '<a href="'.$url.'" id="post_grid_read_more" data-page-id="'.$page.'" class="read-more btn">'.$readmore_btn_title.'</a>';
					}

					$readmore_bar = '<div class="pg_wrapper readmore_bar">';
					$i = 0;
					$reverse_side = false;

					foreach ( $options[$key] as $desc_key => $value ) {

						if ( !in_array($desc_key, array('readmore', 'categories')) ){continue;}

						$i ++;
						if ( $i == 1 && $desc_key == 'readmore' ) {
							$readmore_bar .= '<div class="pg_left-side reverse-side">'.$readmore_btn.'</div>
											  <div class="pg_right-side reverse-side">'.$grid_category.'</div>';
						} else {
							$readmore_bar .= '<div class="pg_left-side">'.$grid_category.'</div>
											  <div class="pg_right-side">'.$readmore_btn.'</div>';
						}
						break;
					}
					$readmore_bar .= '</div>';				
					$post_description = '<div class="description-row' . $bloc_h_fit . '">
								<div class="grid-layout-the-description">' . $the_excerpt . '</div></div>';				
					$maxgrid_template .= $post_description.$readmore_bar;

					break;				
				case strpos($key, 'info_bar') !== false:
					$bloc_h_fit  = isset($info_options['fit_width']) && maxgrid_string_to_bool($info_options['fit_width']) == 1 ? ' bloc_h_fit' : '';
					// Author				
					if ( $options[$key]['author'] != 'disabled' ) {
						$grid_author = '<span class="author_name grid">'. __('By', 'max-grid') .' '.$author.'</span>';
					}

					// Published On
					$info_options = maxgrid_string_to_array("&", "=", $options[$key]['date_options']);
					$date_format = $info_options['date_format'] == 'custom' ? maxgrid_url_encode($info_options['custom_date']) : $info_options['date_format'];

					$published_on = maxgrid_published_on_post(__($date_format, 'max-grid'), $post_id);
					if ( $date_format == 'time_ago'){
						$date = get_the_time('U', $post_id);
						$published_on = $template->dateTime( array('time_ago' => true, 'date_time' => $date ));
					}

					if ( $options[$key]['date'] != 'disabled' ) {
						$grid_published_on = '<span class="grid-date">'.$published_on.'</span>';
					}

					// Comments Counter
					if ( $options[$key]['comments'] != 'disabled' ) {
						$grid_comments_count = '<span class="grid-comments">'.$comments.'</span>';
					}

					$custom_color = isset($info_options['theme_color']) && $info_options['theme_color'] != 'default_color' ? ' custom_color' : '';
					$elements = '<div class="info_row-container '. $key . $custom_color . $bloc_h_fit .'">';
					$i = 0;

					foreach ( $options[$key] as $info_key => $value ) {

						if ( in_array($info_key, array('clone_id', 'date_options', 'datebar_css')) ){continue;}
						$i ++;

						if ( $i == 1 ) { $elements .= '<div class="pg_wrapper first"><div class="pg_left-side">';}
						if ( $i == 2 ) { $elements .= '<div class="pg_right-side">';}

						if ( $info_key == 'author' && $i == 1 || $info_key == 'author' &&  $i == 2 ) { $elements .= $grid_author;}
						if ( $info_key == 'date' && $i == 1 || $info_key == 'date' &&  $i == 2 ) { $elements .= $grid_published_on;}
						if ( $info_key == 'comments' && $i == 1 || $info_key == 'comments' &&  $i == 2 ) { $elements .= $grid_comments_count;}

						if ( $i == 1 ) { $elements .= '</div>';}
						if ( $i == 2 ) { $elements .= '</div></div>';}

						if ( $i == 3 ) { $elements .= '<div class="pg_wrapper last"><div class="pg_left-side">';}
						if ( $i == 4 ) { $elements .= '<div class="pg_right-side">';}

						if ( $info_key == 'author' && $i == 3 || $info_key == 'author' && $i == 4  ) { $elements .= $grid_author;}
						if ( $info_key == 'date' && $i == 3 || $info_key == 'date' &&  $i == 4 ) { $elements .= $grid_published_on;}
						if ( $info_key == 'comments' && $i == 3 || $info_key == 'comments' &&  $i == 4 ) { $elements .= $grid_comments_count;}

						if ( $i == 3 ) { $elements .= '</div>';}
						if ( $i == 4 ) { $elements .= '</div></div>';}
					}
					$elements .= '</div>';
					$maxgrid_template .= $elements;

					break;				
				case strpos($key, 'stats_bar') !== false:
					if ( !is_maxgrid_premium_activated() ) {
						break;
					}
					$sharethis_options = isset($options[$key]['sharethis_options']) ? maxgrid_string_to_array("&", "=", $options[$key]['sharethis_options']) : array();
					$bloc_h_fit  = isset($stats_options['fit_width']) && maxgrid_string_to_bool($stats_options['fit_width']) == 1 ? ' bloc_h_fit' : '';

					// Post Share			
					if ( isset($options[$key]['sharethis']) && $options[$key]['sharethis'] != 'disabled' ) {					
						$popup_data = array(
								'options' => $sharethis_options,
								'key'   => $key,
								'args' 	  => $args
							);
						
						$args = array(
							'layout' => $layout_type,
							'id' => $is_woo.'stats_bar',
							'pslug'  => $preset_name,
							'return' => 'link'
						);						
						
						if ( isset($sharethis_options['share_buttons_style']) && $sharethis_options['share_buttons_style'] == 'inside_tooltip' ) {
							$sharethis_grid = '<span class="maxgrid-sharthis">
													<span></span>
													<div class="float-share-bar">'.$share->get_media($args).'</div>
												</span>';
						} else if ( isset($sharethis_options['share_buttons_style']) && $sharethis_options['share_buttons_style'] == 'popup_box' ) {
							$sharethis_grid = $this->popup_box($sharethis_options, $key);
						} else if ( isset($sharethis_options['share_buttons_style']) && $sharethis_options['share_buttons_style'] == 'horizontal_list' ) {
							$sharethis_grid = '<span>'.$share->get_media($args).'</span>';
						} else {						
							$sharethis_grid = $this->popup_box($sharethis_options, $key);
						}						
					}
					
					// Download Sales counter
					if ( isset($options[$key]['download']) && $options[$key]['download'] == 'disabled' ) {
						$sales_dl_count = '';
					}
					
					$post_likes = '';
					
					// Post Views
					if ( isset($options[$key]['views']) && $options[$key]['views'] != 'disabled' ) {
						$grid_post_views = $post_type!='product' && $post_type!=MAXGRID_POST ? '<div class="views-stat-grid">'.$post_views.'</div>' : '';
					}

					// Post Ratings	
					$tooltip_style = isset($blocks_row['tooltip_style']) ? $blocks_row['tooltip_style'] : 'dark';
					if ( isset($options[$key]['rating']) && $options[$key]['rating'] != 'disabled' ) {
						$post_likes = '<div class="like-stat-grid">' . maxgrid()->post_like->construct($post_id, $layout_type, $tooltip_style) . '</div>';
					}
					
					if ( isset($options[$key]['rating']) && $options[$key]['rating'] == 'disabled' ) {
						$average_rating = '';
					}
					
					$grid_post_ratings = $post_type=='product' || $post_type==MAXGRID_POST ? $average_rating : $post_likes;

					// share bar
					$share_buttons_style = isset($sharethis_options['share_buttons_style']) ? $sharethis_options['share_buttons_style'] : '';
					
					//$s_o = $get_presets->rows('stats_row');
					$sub_dividers = isset($s_o['sub_elements_dividers']) && $s_o['sub_elements_dividers'] == 1 ? '' : ' sub-dividers';
					
					
					$floating_sharebar = '<div class="social-share-container-grid '.$share_buttons_style.' '.$key.$sub_dividers.$bloc_h_fit.'">	
											<div>
												'.$sharethis_grid.'
												<div class="right-stat-grid">
													'.$sales_dl_count.'
													'.$grid_post_views.'
													'.$grid_post_ratings.'
												</div>
											</div>
										</div>';

					$maxgrid_template .= $floating_sharebar;
					
					break;				
				case strpos($key, 'divider_bar') !== false :				
					$new_key 	 = str_replace('bar', 'row', $key);
					$divider_row = $get_presets->rows($new_key);
					$linethick 	 = isset($divider_row['line_thickness']) ? $divider_row['line_thickness'] : 1;
					$linecolor 	 = isset($divider_row['line_color']) ? $divider_row['line_color'] : '#e5e5e5';
					$linetype    = isset($divider_row['line_type']) ? $divider_row['line_type'] : 'full_line';
					$bloc_h_fit  = isset($divider_row['fit_width']) && maxgrid_string_to_bool($divider_row['fit_width']) == 1 ? ' bloc_h_fit' : '' ;
					
					$u_line_tc	 = isset($divider_row['use_t_line_tc']) && maxgrid_string_to_bool($divider_row['use_t_line_tc']) ? true : null;
					$line_tc 	 = isset($divider_row['stats_f_tc']) ? $divider_row['stats_f_tc'] : 'term_c2';
					
					$taxonomy = array(
						MAXGRID_POST => MAXGRID_CAT_TAXONOMY,
						'product' => 'product_cat',
						'post' => 'category',
					);
					
					$tax = $taxonomy[$post_type];
					
					if ( $post_type != 'post') {
						$cat = get_the_terms($post_id, $tax);
					} else {
						$cat = get_the_category($post_id);
					}
					
					$nicename = $cat[0]->slug;
					$terms 	  = get_term_by('slug', $nicename, $tax);

					$c1 	  = get_term_meta( $terms->term_id, 'cat_bg_color', true );
					$c2 	  = get_term_meta( $terms->term_id, 'cat_color', true );
					$c3 	  = get_term_meta( $terms->term_id, 'cat_extra_color', true );

					$tc = array(					
						'term_c1' => preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $c1 ) ? $c1 : '#0081ff',
						'term_c2' => preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $c2 ) ? $c2 : '#222',
						'term_c3' => preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $c3 ) ? $c3 : '#31c1eb',
					);
					
					if ( $u_line_tc ) {
							$linecolor = $tc[$line_tc];
					}
						
					if ( $linetype != 'no_line' && $linetype !== 'full_dashed_line' && $linetype != 'small_dashed_line' ) {
						$border_style = $linethick.'px solid '.$linecolor;
					} else if ( $linetype == 'no_line' ) {
						$border_style = 'none';
					} else {
						$border_style = $linethick.'px dashed '.$linecolor;
					}
					if ( $linetype == 'small_line' || $linetype == 'small_dashed_line' ) {
						$width = '50%';
					} else {
						$width = '100%';
					}
					if ( ( isset($featured_options['fit_width']) && $featured_options['fit_width'] == 1 ) && (isset($divider_row['fit_width']) && maxgrid_string_to_bool($divider_row['fit_width']) != 1) ) {
						$block_padding_left = isset($blocks_row['padding_left']) && $blocks_row['padding_left'] != '' ? $blocks_row['padding_left'] : '10';
						$block_padding_right = isset($blocks_row['padding_right']) && $blocks_row['padding_right'] != '' ? $blocks_row['padding_right'] : '10';
						$width = 'calc(100% - '.($block_padding_left+$block_padding_right).'px)';
					}
					$margin_top = isset($divider_row['margin_top']) && $divider_row['margin_top'] != '' ? $divider_row['margin_top'] : 0;
					$margin_bottom = isset($divider_row['margin_bottom']) && $divider_row['margin_bottom'] != '' ? $divider_row['margin_bottom'] : 0;

					$maxgrid_template .= '<div class="categories-divider '.$key.' '.$bloc_h_fit.'" style="width: '.$width.'; height: 1px; margin-top: '.$margin_top.'px; margin-bottom: '.$margin_bottom.'px; border-bottom: '.$border_style.';"></div>';
					break;
			}

			$index++;

			// Prevent successive duplicated divider
			if ( $key == 'download_bar' && $no_download || $key == 'audio_bar' && $no_audio ) {
				continue;
			}

			$prev_key = $key;
		}

		$dl_or_add_btn = '';
		//$elements = new Elements();
		if ( $post_type == 'product' ) {

			$label = __('Add To Cart', 'max-grid');
			
			$size 	 = isset($add_to_cart_options['add_cart_size']) ? ' '.$add_to_cart_options['add_cart_size'] : ' small';
			$spin 	 = isset($add_to_cart_options['spin_layout']) ? ' '. str_replace('_', '-', $add_to_cart_options['spin_layout']) : ' inner-spin';
			$rounded = isset($add_to_cart_options['border_radius']) ? ' '.$add_to_cart_options['border_radius'] : ' pointed';
			$thin 	 = isset($add_to_cart_options['sign_style']) ? ' '.$add_to_cart_options['sign_style'] : ' thick';

			$args  = array(
				'product_id' => $post_id,
				'label' 	 => $label,
				'size' 		 => $size,
				'spin' 		 => $spin,
				'rounded' 	 => $rounded,
				'thin' 		 => $thin,
			);
		} else if ( $post_type == MAXGRID_POST ) {
			$args = array('view' => 'list');
			$dl_or_add_btn = $block_elements->Download($args);
		} else {
			$dl_or_add_btn = '';
		}	
		$fillcover = isset($featured_options['fillcover_overlay']) && maxgrid_string_to_bool($featured_options['fillcover_overlay']) == 1 ? ' fillcover' : '';
		$post_hover_shadow = isset($blocks_row['box_shadow']) ? ' '.$blocks_row['box_shadow'] : '';	
		if( $layout_type == 'list' ) {

			$audio_file_html = isset($layout_data['audio_bar']) && $audio_file != '' ? $audio_file_html : '';
			$sd_player_html  = isset($layout_data['audio_bar']) && $sd_code != '' ? $sd_player_html : '';
			
			$display = '<div class="block-list-container '.$fillcover.$post_type.'-post-type">
							<div class="block-list-top">';

			$empty_featured_class = ' empty-featured';
			if ( $block_elements->Featured('list') != '' || isset($layout_data['audio_bar']) && ( $audio_file != '' || $sd_code != '' ) ) {
				$empty_featured_class = '';
				$display .= '<!-- Column Details -->
							<div class="block-list-thumbnail">
								<div class="block-list">
									'.$listlayout_ribbons.'
									'.$block_elements->Featured('list').$audio_file_html.$sd_player_html.'
								</div>
							</div>';
			}

			$display .= '<div class="block-list-description'.$empty_featured_class.'">
									<div class="list-layout-the-title"><a class="maxgrid_title" href="'.$url.'" data-page-id="'.$page.'">'.$the_title.'</a></div>
									<span class"author-container-list"> 
										<form class="dropdown-container" id="dropdown_form" action="" method="GET">						
											<span class="list-published_on">'.sprintf( __( 'Published on %s', 'max-grid' ) , $published_on ) .'</span> | </form>
										'.$list_author.' | 
										'.$list_category.' |
										'.$list_comments_count.'
									</span>
										<div class="list-layout-the-description">'. nl2br($the_list_excerpt).'</div>
										<span class="parent-description-footer-list">'.$dl_or_add_btn.'</span>
										<span class="read-more_container">
											<a href="'.$url.'" id="post_grid_read_more" data-page-id="'.$page.'" class="read-more'.$btn_readmore.'">'.$readmore_btn_title.'</a>
										</span>
									 </div>
							</div>
							<div class="social-share-container-list share-list-view">							
								<div>
									'.$sharethis_list.'
									<div class="right-stat-grid">
										'.$sales_dl_count.'
										'.$list_post_views.'
										'.$list_post_ratings.'
									</div>
								</div>
							</div>';
			$display .= '</div>';
		}else{
			$display = '<div class="block-grid-container'.$fillcover.'">
								<div></div>
								<div class="block-grid '.$term->slug.'-term-color'.$post_hover_shadow.'" data-total-row="'. ($index-1) .'">
								'.$gridlayout_ribbons;
			$display .= $maxgrid_template;						
			$display .= '</div></div>';
		}
		return $maxgrid_template !== '' ? $display : '';
	}
	
	/**
	 * ShareThis popup box
	 *
	 * @param array $options ShareThis options.
	 * @param string $key Statistics element ID.
	 *
	 * @return string
	 */
	public function popup_box($options, $key) {
		$count=0;
		foreach($options as $media => $value ) {
			if ( strpos($media, '_') === false ) {					
				$count++;
			}
		}
				
		$html = '<div class="maxgrid-sharethis-container" id="share-trigger" data-count="'.$count.'" data-post-type="post" data-post-id="' . $this->post_id . '" data-id="' . $key . '">';
		$html .= '<div class="ytb-share-btn">'. __( 'share', 'max-grid' ) . '</div>';
		$html .= '</div>';
		return $html;
	}
}