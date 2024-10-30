<?php
/**
 * Max Grid Builder Formatting
 *
 * Functions for formatting data.
 */

use \MaxGrid\Template;
use \MaxGrid\getOptions;

defined( 'ABSPATH' ) || exit;

/**
 * Output ajax Loading spinner.
 *
 * @since 1.0.0
 */
function maxgrid_lds_rolling_loader($args) {
	$version  = isset($args['version']) ? ' '.$args['version'] : '1';
	$relative = isset($args['relative']) ? ' '.$args['relative'] : '';
	$color 	  = isset($args['color']) ? ' '.$args['color'] : '';
	$size     = isset($args['size']) ? ' '.$args['size'] : '';
	$form     = isset($args['form']) ? $args['form'] : 'circle';
	$style    = isset($args['style']) ? ' style="'.$args['style'].'"' : '';
	
	if ( $version == 1) {
		$html = '<div class="lds-css' . $relative . ' ng-scope ' . $color . $size . '"' . $style . ' >
					<div class="lds-rolling">
						<div></div>
					</div>
				</div>';
	} else if ( $version == 2) {
		$html = '<div class="lds-css ng-scope ' . $color . $size . '">
					<div class="dark-loading">
						<div class="outer-' . $form . '"></div>
						<div class="inner-' . $form . '"></div>
					</div>
				</div>';
	}
	return isset($html) ? $html : '';
}

/**
 * Generate tooltip attributes data.
 *
 * @since 1.0.0
 *
 * @param string $title tooltip title.
 * @param string $style tooltip style.
 *						default: dark
 * @return string
 */
function maxgrid_tooltip_data($title='', $style='dark') {
	return  get_option('maxgrid_tooltip', true) ? ' data-title="' . $title . '" data-rel="maxgrid_tooltip" data-style="'.$style.'"' : '';
}

/**
 * Open Graph
 *
 * @return string
 */
$using_jetpack_publicize = ( class_exists( 'Jetpack' ) && in_array( 'publicize', Jetpack::get_active_modules()) ) ? true : false;

if ( !defined('WPSEO_VERSION') && !class_exists('NY_OG_Admin') && !class_exists('Wpsso') && $using_jetpack_publicize == false) {
	add_action( 'wp_head', 'maxgrid_add_opengraph', 5 );
}

function maxgrid_add_opengraph() {		
	$template = maxgrid()->template;
	
	$get_options = maxgrid()->get_options;

	if ( isset($get_options->option('general_options')['add_open_graph']) && $get_options->option('general_options')['add_open_graph'] == 'add_open_graph' ) {	 
		global $post; // Ensures we can use post variables outside the loop
				
		$author_id = get_post_field( 'post_author', $post->ID );
		$twitter = get_the_author_meta('maxgrid_twitter', $author_id );
		
		// Post excerpt
		$getPost = get_post_field('post_content', $post->ID);		
		$string = strip_shortcodes($getPost);
		$string = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $string);
		$string = preg_replace( "/\r|\n/", "", $string );
		
		$the_excerpt = preg_replace('/\[[^\]]+\]/', '', $getPost);
		
		// add meta
		echo "<!-- ".MAXGRID_PLUGIN_LABEL_NAME." - OpenGraph -->\n";
		echo "<meta property='fb:app_id' content='87741124305'/>\n";
		echo "<meta property='og:site_name' content='". get_bloginfo('name') ."'/>\n"; // Sets the site name to the one in your WordPress settings
		echo "<meta property='og:url' content='" . get_permalink() . "'/>\n"; // Gets the permalink to the post/page			

		if (is_singular() && !is_page()) { // If we are on a blog post/page
	        echo "<meta property='og:title' content='" . get_the_title() . "'/>\n"; // Gets the page title
			echo "<meta property='og:description' content='". wp_strip_all_tags($the_excerpt) ."'/>\n";
			echo "<meta property='og:type' content='article'/>\n";// Sets the content type to be article.
			/*echo "<meta property='article:published_time' content='".get_the_date( DATE_W3C )."' />\n";
			echo "<meta property='article:modified_time' content='".get_the_modified_date( DATE_W3C )."' />\n";
			echo "<meta property='og:updated_time' content='".get_the_modified_date( DATE_W3C )."' />\n";*/

			echo "<meta name='twitter:card' content='summary' />\n";
			echo "<meta name='twitter:creator' content='@s".$twitter ."' />\n";
			echo "<meta name='twitter:description' content='".wp_strip_all_tags($the_excerpt) ."' />\n";
			echo "<meta name='twitter:title' content='".get_the_title()."' />\n";
			
	    } elseif(is_front_page() or is_home()) { // If it is the front page or home page
	    	echo "<meta property='og:title' content='" . get_bloginfo("name") . "'/>\n"; // Get the site title
	    	echo "<meta property='og:type' content='website'/>\n"; // Sets the content type to be website.
	    }
		
		$args = array(
			'post_id' 	   => $post->ID,
			'default' 	   => $get_options->option('general_options')['default_og_image'],
			'size' 	  	   => 'large',
			'return_empty' => true,
		);
		echo "<meta property='og:image' content='". $template->get_post_thumbnail($args) ."'/>\n";
	}
}

/**
 * Max Grid Builder lightbox
 *
 * @return string
 */
function maxgrid_lightbox() {
	$box_mode 		  = 'vertical-mode';
	$pg_theme 		  = ' pg_light_theme';
	$testmode 		  = true;
	$class 			  = is_admin_bar_showing() ? ' is_admin_bar_showing' : '';
	
	$ismobile   	  = maxgrid_is_mobile() ? 'on' : 'off';
	$ismobile_class   = maxgrid_is_mobile() ? ' ismobile' : '';
	
	$template 		  = maxgrid()->template; 
	$is_admin_bar 	  = is_admin_bar_showing() ? 'on' : 'off';
	$is_admin_bar 	  = is_admin_bar_showing() ? ' is_admin_bar' : '';	
	?>
	<div id="modal01" class="single-modal-container">		
		<div class="single-modal-content">
			<img class="single-image" id="single-image">
		</div>		
		<span class="modal-close-button">&times;</span>
	</div>
	
	<!-- Reach content Lightbox -->
	<div class="ismobile-nav maxgrid-parent">
		<a id="prev" class="navigation-arrow ytb" data-trigger="mobile_nav" data-target="prev">
			<!-- <i class="fas fa-step-backward"></i> -->
			<span class="icon-previous2"></span>
		</a>
		<a id="next" class="navigation-arrow ytb" data-trigger="mobile_nav" data-target="next" data-trigger="mobile_nav">
			<!-- <i class="fas fa-step-forward"></i> -->
			<span class="icon-next2"></span>
		</a>
		<span class="open-slideshow<?php echo $ismobile_class; ?>"><?php echo $template->get_svg_icon('playlist');?></span>
		<span class="icon-cross"></span>		
	</div>

	<div id="maxgrid_lightbox_modal" data-admin-bar="<?php echo $is_admin_bar;?>" class="maxgrid_lightbox-modal maxgrid-parent <?php echo $box_mode . $ismobile_class . $pg_theme;?>">
		<?php	
		if( !maxgrid_is_mobile() ){	
		?>		
		<div class="transparent_bg_nav"></div>
		<div class="pg_lightbox-toolbar<?php echo $class; ?>">
			<div>
				<span class="fullscreen-icon"><?php echo $template->get_svg_icon('fullscreen').$template->get_svg_icon('exits-fullscreen');?></span>
				<span class="open-slideshow"><?php echo $template->get_svg_icon('playlist');?></span>
				<span class="icon-cross"></span>
			</div>
		</div>		
		<span id="prev_nav-thumbnails" class="nav-thumbnails-content prev" data-target="prev">
			<img class="prev-post-thumbnails" src="" alt="" width="200" height="auto">
			<div class="prev-post-title"></div>
		</span>
		<a id="prev" class="navigation-arrow" data-target="prev">
			<span class="prev-arrow"><?php echo $template->get_svg_icon('arrow');?></span>
		</a>
		<span id="next_nav-thumbnails" class="nav-thumbnails-content next" data-target="next">
			<img class="next-post-thumbnails" src="" alt="" width="200" height="auto">
			<div class="next-post-title"></div>
		</span>
		<a id="next" class="navigation-arrow" data-target="next">
			<span class="next-arrow"><?php echo $template->get_svg_icon('arrow');?></span>
		</a>		
		<?php
		}
		?>		
		<div id="reach_content_outer" class="<?php echo $is_admin_bar;?>"></div>
	</div>
	<div class="playlist-search-bar maxgrid-parent<?php echo $ismobile_class;?>">
		<div class="box">
		  <div class="container-3">
			  <span class="icon"><i class="fa fa-search"></i></span>
			  <input type="search" id="playlist-search" class="side-playlist-search" data-trigger="" onkeyup="postPlaylistFilter(this)" data-is-mobile="<?php echo $ismobile;?>" placeholder="<?php echo ucfirst( __( 'search', 'max-grid' ));?>..." />
			  <span class="clear-woord">&times;</span>
		  </div>
		</div>
	</div>
	<div class="playlist-slider_container maxgrid-parent<?php echo $ismobile_class . $is_admin_bar;?>"></div>
	<?php
}
add_action( 'wp_head', 'maxgrid_lightbox' );