<?php
/**
 * Max Grid Builder Caches
 */

use \MaxGrid\Template;
use \MaxGrid\Youtube;

/**
 * @class Max_Grid_Transient.
 */
class Max_Grid_Transient {
		
	/**
	* Transient
	*
	* @access private
	*
	* @var string
	*/
	public $transient_sec;
		
	/**
	* Data
	*
	* @access private
	*
	* @var array
	*/
		
	const PREFIX = 'maxgrid-builder';
	
	/**
	* Transient prefix.
	*
	* @var string
	*/
	public $prefix = 'maxgrid-builder';
	
	/**
	* Transient expire time.
	*
	* @var string
	*/
	public $expire_time = 12 * HOUR_IN_SECONDS;
	
	/**
	* Current pretent name.
	*/
	public $preset_name;
	
	/**
	 * Constructor.
	 *
	 * @param array $data
	 */
	public function __construct($data=array()){
		if(isset($data)){
			$this->preset_name		= isset($data['preset_name']) ? $data['preset_name'] : MAXGRID_DFLT_LAYOUT_NAME;		
			$get_options 			= maxgrid()->get_options;		
			$enable_caching			= isset($get_options->option('api_options')['enable_caching']) && $get_options->option('api_options')['enable_caching'] != 'enable_caching' ? $get_options->option('api_options')['enable_caching'] : true;
			$cache_delay 			= isset($get_options->option('api_options')['clear_cache_delay']) ? $get_options->option('api_options')['clear_cache_delay'] : 12;
			$delay_timeunits 		= isset($get_options->option('api_options')['delay_timeunits']) ? $get_options->option('api_options')['delay_timeunits'] : 'hours';			
			$this->transient_sec  	= $this->time_in_seconds($cache_delay, $delay_timeunits);
			$this->template 		= maxgrid()->template;
		}
		add_action( 'wp_ajax_maxgrid_ytb_clear_transient', array( $this, 'clear_transient' ) );
		add_action( 'wp_ajax_nopriv_maxgrid_ytb_clear_transient', array( $this, 'clear_transient' ) );
	}
	
	/**
	 * transient_sec converter.
	 *
	 * @since 1.0.0
	 *
	 * @param int 	 $value
	 * @param string $unite
	 */
	public function time_in_seconds($value, $unite){
		$time_unite  = array(
			'minutes'  => $value * MINUTE_IN_SECONDS,
			'hours'    => $value * HOUR_IN_SECONDS,
			'days'     => $value * DAY_IN_SECONDS,
		);
		return $time_unite[$unite];
	}
		
	/**
	 * Get Lightbox Playlist Items
	 *
	 * @since 1.0.0
	 *
	 * @param object $query
	 * @param array  $args
	 * @param int	 $page
	 */
	public function get_post_items_list($query, $args, $page) {		
		$name = $args['post_type'] . $args['grid_id'] . $args['orderby'] . $args['meta_key'] . $args['offset'];
		// . $args['pslug']
		$transient_name = $this->prefix . '-lightbox-modal-left-list-content_' . md5($name);
		
		if ( $this->transient_sec > 0 && ( $transient = get_transient($transient_name) ) !== false ) {
			$response = $transient;
		} else {		 
			if( $query->have_posts() ) {
				$offset = $args['offset'];
				$ppp = $args['posts_per_page'];				
				$i = 0;
				if ($offset > 1) {
					$i = $offset;
				}
				$max   = $query->post_count * $query->max_num_pages;				
				$ismobile = wp_is_mobile() ? 'isMobile_' : '';
				
				if ($offset==0) {
					$html = '<ul id="'.$ismobile.'ul-thumbs-list" class="ul-thumbs-list" data-orderby="'.$args['orderby'].'">';
				}
				while( $query->have_posts() ): $query->the_post();
					$post_id = get_the_ID();
					$featured_type = maxgrid()->get_post_meta->grid($post_id)['f_type'];			
					$date_args = array(
						'post_id' 	   => $post_id,
						'return_empty' => true,
					);
					$post_thumbnails = $this->template->get_post_thumbnail($date_args);

					// Get Product Price
					$get_price = '';
					if ( $args['post_type'] == 'product' ) {
						$_product  	= wc_get_product( $post_id );
						$_currency  = ' '.get_woocommerce_currency_symbol();				
						$get_price = '<div class="list_get_price">'.$_product->get_price().$_currency.'</div>';
					}
				
					$html .= '<li data-id="'.$post_id.'" data-post-type="'.$args['post_type'].'" data-href="' . $this->template->get_custom_featured($post_id, $featured_type) . '" data-featured-type="'.$featured_type.'" data-offset="'.$i.'" class="li-post-thumb">
								<img data-src="'. $post_thumbnails .'" src="'. $post_thumbnails .'">'
								.$get_price.'
								</li>';

					// Post excerpt
					$getPost = get_post_field('post_content', $post_id);		
					
					$the_excerpt = preg_replace('/\[[^\]]+\]/', '', $getPost);  # strip shortcodes, keep shortcode content
					$the_excerpt = wp_trim_words($the_excerpt, 15);
				
					$time_args = array(
						'time_ago' 	=> true,
						'date'		=> false,
						'time'		=> false,
						'date_time'	=> get_the_date('Y-m-d'), // string
					);
					
					$html .= '<li data-id="'.$post_id.'" data-post-type="post" data-featured-type="'.$featured_type.'" class="li-post-description">
								<span>'.get_the_title().'</span>
								<span>'.wp_strip_all_tags($the_excerpt).'</span>
								<span>'.$this->template->dateTime($time_args).'</span>
								</li>';
					$i++;
					$next_page = $page+1;
					
					if ( $i > $offset+$ppp-1 && $i < $max ) {
						$html .= '<li class="playlist-load-more-container"><span class="load-more ligthbox-playlist" data-next-page-token="'. $next_page .'" data-offset="'.$i.'">' . __( 'Load More', 'max-grid' ) . '</span></li>';
					}
				endwhile;
				if ($offset==0) {
					$html .= '</ul>';
				}
				
				$response = $html;
				if ( isset($response) && !empty($response) && $this->transient_sec != 0 ) {
					set_transient( $transient_name, $response, $this->transient_sec );
				}
				wp_reset_postdata();
			}			
		}		
		return $response;
	}
	
	/**
	 * Delete All transient
	 * @since 1.0.0
	 */
	public function delete_all_transient($prefix=null) {		
		global $wpdb;
		
		$t_prefix = isset($prefix) ? $prefix : $this->prefix;
		// transient SQL
		$sql = "SELECT `option_name` AS `name`, `option_value` AS `value`
				FROM  $wpdb->options
				WHERE `option_name` LIKE '%_transient_timeout_%'
				ORDER BY `option_name`";

		$transients = $wpdb->get_results($sql);
		$deleted_name = array();
		if ($transients) {

			// loop through each transient option
			foreach ($transients as $transient) {

				// if transient option name matched then delete it (only if can expire)
				if ( strpos( $transient->name, $t_prefix) ) {
					$name = str_replace( '_transient_timeout_', '', $transient->name );
					if ( delete_transient($name) ) {
						$deleted_name[] = $name;
					}

				}

			}	

		}
		return $deleted_name;
	}
		
	/**
	 * Clear Youtube Transient
	 *
	 * @return array
	 */
	public function clear_transient() {
		$prefix = isset($_POST['prefix']) ? $_POST['prefix'] : null;
		maxgrid_debug($this->delete_all_transient($prefix));
		die();
	}
}