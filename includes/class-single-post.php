<?php
/**
 * Single custom post.
 */

use \MaxGrid\Template;
use \MaxGrid\countMeta;
use \MaxGrid\getOptions;
use \MaxGrid\Elements;
use \MaxGrid\share;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class Max_Grid_Single_Post.
 */
class Max_Grid_Single_Post {
	
	/**
	 * Contstructor
	 */
	public function __construct() {
		add_action('the_content', array( __CLASS__, 'get_content' ) );
	}
	
	/**
	 * Custom post content.
	 *
	 * @param string $content post content.
	 * @param string $lightbox_id Lightbox ID
	 *
	 * @return string
	 */
	public static function get_content($content, $lightbox_id='') {
		$template = maxgrid()->template;
		$Max_Grid_Reviews_Tabs = new Max_Grid_Reviews_Tabs;

		if ( is_single()  || $lightbox_id  ) {
			global $post, $single_full_width, $the_content;

			$id = $post->ID;
			if ($lightbox_id) {
				$id = $lightbox_id;
			}
			
			$track_options = maxgrid()->get_options->option(MAXGRID_LOGS_OPT_NAME);
			$once 		   = isset($track_options['re_download_count']) && ( $track_options['re_download_count'] == 're_download_count' || $track_options['re_download_count'] == 'true') ? true : false;

			if (is_singular( MAXGRID_POST )) {
				$terms = wp_get_post_terms( $id, MAXGRID_CAT_TAXONOMY );
				$category_slug_name = MAXGRID_CAT_TAXONOMY;
			} else if (is_singular( 'product' )) {
				$terms = wp_get_post_terms( $id , 'product_cat' );
				$category_slug_name = 'product_cat';
			} else {
				$terms = wp_get_post_terms( $id , 'category' );
				$category_slug_name = 'category';
			}
			$i = 0;
			$len = count($terms);
			$break = 3; // Max number of categories

			$uncategorized_textdomain = __('Uncategorized', 'max-grid').' ';
			if( $terms == "" ) {
				$in = '<strong>'.$uncategorized_textdomain.'</strong>';
			}else{
				$in = '';
			}
			$category_name = "";
			foreach ( $terms as $term ) {
				if( $i == $break ) break;
				if ( $i == $len - 1 or $i == $break -1 ) {
					$category_name .= '<a name="category" id="catnamepost" href="'.home_url().'/'.$category_slug_name.'/'.$term->slug.'" >'.$term->name.'</a>';
				} else {
					$category_name .= '<a href="'.home_url().'/'.$category_slug_name.'/'.$term->slug.'" >'.$term->name.'</a>, ';
				}
				$i++;
			}
			
			$getPost 	 = get_the_content($id);
			$the_content = wpautop( $getPost, true );
			$url_file 	 = maxgrid()->get_post_meta->download($id)['file'];
			$filetype 	 = wp_check_filetype(maxgrid_download_basename($url_file));
			$file_size 	 =  maxgrid_get_remote_filesize($url_file);
			$dl_count 	 = countMeta::get_count($id, MAXGRID_DOWNLOAD_META_KEY);
			$thumb 		 = get_the_post_thumbnail_url($id, 'large');
			$thumb_full  = get_the_post_thumbnail_url($id, 'full');
			$author_id 	 = get_post_field( 'post_author', $id );

			$email 		 = get_the_author_meta('email', $author_id );
			$twitter 	 = get_the_author_meta('maxgrid_twitter', $author_id );
			$brn_size 	 = '18px';
			$author 	 = get_the_author_meta('url', $author_id );

			extract(maxgrid_get_meta_options());
			extract(maxgrid_get_options());

			$download_status = 'none'; // newest views liked downloaded
			$download_type 	 = 'free';
			$featured_type 	 = maxgrid()->get_post_meta->grid($id)['f_type'];
			$has_thumbnail 	 = 'has-post-thumbnail';
			
			if ( $featured_type == "image" && is_singular() && !has_post_thumbnail($id) ) {
				$has_thumbnail = 'has-not-post-thumbnail';
			}
			
			$pg_theme = 'pg_light_theme';			
			$dl_count_html = _n( 'Download', 'Downloads', $dl_count, 'max-grid' );
			$download_meta = '<div class="download_meta">
										<span>'.maxgrid_download_basename($url_file).'</span>
										<span>'.$file_size.' | <span class="ajax-dl-counter">'.$dl_count.'</span> '.$dl_count_html.' | '. __('Categories', 'max-grid').': '.$in.$category_name.'</span>
									</div>';
						
			$featured_show = '<div class="video-wrapper maxgrid '.$featured_type.'">';
			if ( $featured_type == "image" ) {
				$featured_show .= '<a onclick="maxgrid_singleImageModal(this)" data-href="'.$thumb_full.'" href="#" alt="Image Name" onclick="return false;"><img id="post_thumbnail" src="'.$thumb.'"></a>';
			} else if ( $featured_type == "vimeo" ) {			
				$video_id = (int) substr(parse_url($template->get_custom_featured($id, $featured_type), PHP_URL_PATH), 1);
				$featured_show .= '<iframe src="https://player.vimeo.com/video/'.$video_id.'?title=0&byline=0&portrait=0" width="100%" height="auto" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
			} else if ( $featured_type == "youtube" ) {
				parse_str( parse_url( $template->get_custom_featured($id, $featured_type), PHP_URL_QUERY ), $my_array_of_vars );
				$video_id = $my_array_of_vars['v'];
				$featured_show .= '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/'.$video_id.'?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
			} else if ( $featured_type == "mp4" ) {
				$featured_show .= '<video id="my-video" class="video-js" controls preload="auto" width="100%" height="100%"
							  poster="'.$template->get_custom_featured($id, 'image').'" data-setup="{}">
								<source src="'.$template->get_custom_featured($id, $featured_type).'" type="video/mp4">
								<source src="'.preg_replace('"\.mp4$"', '.webm', $template->get_custom_featured($id, $featured_type)).'" type="video/webm">
							  </video>';
			}else{
				$featured_show .= '<img src="'.$thumb.'">';
			}
			$featured_show .= '</div>';
			
			$content = $featured_show.$content;
			
			if ( is_singular( MAXGRID_POST ) && ! class_exists( 'Easy_Digital_Downloads') || $lightbox_id ) {
				
				$home = isset( $_GET['history'] ) && $_GET['history'] == 'go' ? '<a href="#" onclick="window.history.go(-1); return false;">'. __( 'Home', 'max-grid' ) . '</a>' : __( 'Home', 'max-grid' );
				
				$terms_name = isset($terms[0]->name) ? $terms[0]->name : 'Uncategorized';
				$terms_slug = isset($terms[0]->slug) ? $terms[0]->slug : 'uncategorized';
				
				$category = isset( $_GET['history'] ) && $_GET['history'] == 'go' ? '<a href="#" data-return="on" data-category="' . $terms_slug . '">' . $terms_name . '</a>' : $terms_name;

				$content = '<div id="download-'.$id.'" class="post-'.$id.' maxgrid maxgrid-parent single-type-download single-post-page status-'.$download_status.' '.$has_thumbnail.' maxgrid-body download-type-'.$download_type.' '.$pg_theme.'" data-g-uid="'.$id.'">
					<h1 class="maxgrid_download_title download-entry-title">'.get_the_title($id).'</h1>

					<nav class="download-breadcrumb">
						'.$home.'
						<i class="fa fa-angle-right"></i>
						'.$category.'
						<i class="fa fa-angle-right"></i>
						'.get_the_title($id).'
					</nav>
					<div class="single-download-row">
					<!-- Left Column -->
					<div class="left-col single-download-main-image">
						<div class="video-wrapper maxgrid '.$featured_type.'">';
						if ( $featured_type == "image" ) {
							$content .= '<a onclick="maxgrid_singleImageModal(this)" data-href="'.$thumb_full.'" href="#" alt="Image Name" onclick="return false;"><img id="post_thumbnail" src="'.$thumb.'"></a>';
						} else if ( $featured_type == "vimeo" ) {			
							$video_id = (int) substr(parse_url($template->get_custom_featured($id, $featured_type), PHP_URL_PATH), 1);
							$content .= '<iframe src="https://player.vimeo.com/video/'.$video_id.'?title=0&byline=0&portrait=0" width="100%" height="auto" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
						} else if ( $featured_type == "youtube" ) {
							parse_str( parse_url( $template->get_custom_featured($id, $featured_type), PHP_URL_QUERY ), $my_array_of_vars );
							$video_id = $my_array_of_vars['v'];
							$content .= '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/'.$video_id.'?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
						} else if ( $featured_type == "mp4" ) {
							$content .= '<video id="my-video" class="video-js" controls preload="auto" width="100%" height="100%"
										  poster="'.$template->get_custom_featured($id, 'image').'" data-setup="{}">
											<source src="'.$template->get_custom_featured($id, $featured_type).'" type="video/mp4">
											<source src="'.preg_replace('"\.mp4$"', '.webm', $template->get_custom_featured($id, $featured_type)).'" type="video/webm">
										  </video>';
						}else{
							$content .= '<img src="'.$thumb.'">';
						}
						
						$post_like = '';
						$sharethis_btn = '';
				
						if ( is_maxgrid_premium_activated() ) {
							$share_data = array(
								'source' => str_replace(' ', '_', get_bloginfo('name')),
								'post_id' => $id,
								'href' => get_permalink($id),
								'title' => get_the_title($id),
								'thumbnail_medium' => get_the_post_thumbnail_url($id, 'medium'),
								'thumbnail_full' => get_the_post_thumbnail_url($id, 'full'),
								'twitter_username' => $twitter,
							);
							$share = new Share($share_data);
							$sharethis_btn = $share->this();
							
							$post_like = '<div class="stats-count">
									<div class="single-post-like">' . maxgrid()->post_like->construct($id,$is_single=true, 'dark') . '</div>
								</div>';
							$post_like 	 = '';
						}
				
						$args = array(
							'post_id' => $id,
						);

						$elements 	 = new Elements($args);
						$audio_file  = get_post_meta ($id, 'maxgrid_audio_file', true);
						$AudioPlayer_html = '<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(0,0,0,.08);">'.$elements->AudioPlayer().'</div>';

						$AudioPlayer = $audio_file != '' ? $AudioPlayer_html : '' ;
						$content .= '</div>

						<span class="shareline">
							<div class="single-social-container">
								<div class="single-social-share">'.$sharethis_btn.'</div>
							</div>
								'.$post_like.'						
						</span>	
						'.$AudioPlayer;
						
					$content .= '</div>';
					$args = array(
						'time_ago' => false,
						'date' => maxgrid_published_on_post(get_option( 'date_format' ), $id),
					);

					$data = array(
						'author_name' 	=> maxgrid_get_author($author_id, 50),
						'author_url' 	=> $author,
						'thumbnail_url' => get_avatar_url($email),
						'published_on' 	=> $template->dateTime($args),
						'social_media' 	=> '<div id="twitter_single_container" data-twitter-id="'.$twitter.'"></div>',
						'class' 		=> 'single-post',
					);
					$heding_html = $template->author_meta($data);

					$content .= '<!-- Right Column -->
					<div class="right-col single-download-summary">
						<div class="download-entry-summary is_single">

						<!-- Summary Header -->'.$heding_html;
				
				if ( is_maxgrid_download_activated() || is_maxgrid_woo_activated() ) {
					$rating = maxgrid()->rating;
					$data 	= array(
						'post_id' 	 => $id,
						'stars_size' => 'large',
						'link' 		 => 'disabled',
					);

					$content .= $rating->get_average($data);
				}
				
				if ( wp_check_filetype($url_file)['ext'] ) {
					$maxgrid_download_title = maxgrid()->get_post_meta->download($id)['title'];
					$content .= $download_meta.'
								<div class="download-button-container">
									<div>
										<span id="maxgrid_download_single" class="maxgrid-button green download biggest" data-post-id="'.$id.'" data-meta-key="'.MAXGRID_DOWNLOAD_META_KEY.'" data-single="" data-once="'.$once.'" data-href="'.maxgrid_encrypt($url_file, 'e').'">'.$maxgrid_download_title.'</span>
										<span class="ajax_dl-spiner"></span>
									</div>
								</div>';
				} else {
					$error_loading_msg = '<span class="alert-msg">'. __( 'File not found!', 'max-grid') . '</span>';
					$content .= '<span class="maxgrid-alert large warning active">
									'. $error_loading_msg .'
								</span>';
				}
				
				$dld_content = '<div class="dld-summary-content">'. $the_content . '</div>';

				$args 	= array(
					'post_id' 		=> $id,
					'the_content'	=> $dld_content
				);

				if ( !maxgrid_is_product() ) {
					$content .= $Max_Grid_Reviews_Tabs->reviews_tabs($args);				
				}

				$content .= '</div></div></div>';		
				?>
				<style type="text/css">
					#sidebar, .sidebar, .entry-header, #main-sidebar, .entry-title {display: none;}
					#post-area, #main, .rsrc-main, .content-area, #primary .entry-content {
						float: unset!important;
						display: block!important;
						width: 100%!important;
					}

				<?php if ( $featured_type !== "mp4" ) {?>
					.video-wrapper {
					  position: relative;
					  padding-bottom: 56.25%;
					  height: 0;
					  margin: 0;
					}
					.video-wrapper.image {
						padding-bottom: unset;
						height: auto;
					}
					.video-wrapper iframe {
					  position: absolute;
					  top: 0;
					  left: 0;
					  width: 100%;
					  height: 100%;
					}
					.iframe-embed {
						position: unset;
					}
				<?php }?>
			</style>
			<?php
			} else if ( is_singular() ) {
				if( is_singular( 'products' ) ) {
					$args = array(
						'post_id' 		=> $id,
					);
				} else {
					$args = array(
						'post_id' 		=> $id,
						'comments_only'	=> true,
					);
					
					$use_ds = class_exists( 'DownloadSender' ) && maxgrid()->get_post_meta->download($id)['use_ds'] ? true : null;
					
					if ( wp_check_filetype($url_file)['ext'] && !$use_ds ) {						
						$maxgrid_download_title = maxgrid()->get_post_meta->download($id)['title'];
						$content .= '<div class="is_single">'.$download_meta.'
										<div class="download-button-container">
											<div>
												<span id="maxgrid_download_single" class="maxgrid-button green download biggest" data-single="" data-post-id="'.$id.'" data-meta-key="'.MAXGRID_DOWNLOAD_META_KEY.'" data-once="'.$once.'" data-href="'.maxgrid_encrypt($url_file, 'e').'">' . $maxgrid_download_title . '</span>
												<span class="ajax_dl-spiner"></span>
											</div>
										</div>
									</div>';
					}
				}
				
				$get_options = maxgrid()->get_options;	
				$fo_o = $get_options->option('forms');
				$disable_ajax_form = isset($fo_o['disable_ajax_form']) && ( maxgrid_string_to_bool($fo_o['disable_ajax_form']) == 1 || $fo_o['disable_ajax_form'] == 'disable_ajax_form' ) ? true : false ;
				
				if ( !$disable_ajax_form && !maxgrid_is_product() ) {
					$content .= $Max_Grid_Reviews_Tabs->reviews_tabs($args);
				}
				
				?>
				<style type="text/css">
				<?php if ( $featured_type !== "mp4" ) {?>
					.video-wrapper {
					  position: relative;
					  padding-bottom: 56.25%;
					  height: 0;
					  margin: 0;
					}
					.video-wrapper.image {
						padding-bottom: unset;
						height: auto;
					}
					.video-wrapper iframe {
					  position: absolute;
					  top: 0;
					  left: 0;
					  width: 100%;
					  height: 100%;
					}
					.iframe-embed {
						position: unset;
					}
				<?php }?>
			</style><?php
			}
		}		
		return $content;
	}	
}

new Max_Grid_Single_Post;