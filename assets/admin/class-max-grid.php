<?php
/**
 * Max_Grid setup
 */

use \MaxGrid\Template;
use \MaxGrid\g_recaptcha;
use \MaxGrid\get_post_meta;
use \MaxGrid\getOptions;
use \MaxGrid\Table;
use \MaxGrid\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Main Max_Grid Class.
 *
 * @class Max_Grid
 */
final class Max_Grid {
			
	/**
	 * The single instance of the class.
	 *
	 * @var Max_Grid
	 */
	protected static $_instance = null;
	
	/**
	 * Max Grid Builder default settings instance.
	 *
	 * @var Max_Grid_Settings
	 */
	public $settings = null;
	
	/**
	 * Max Grid Builder Transient instance.
	 *
	 * @var Max_Grid_Transient
	 */
	public $transient = null;
	
	/**
	 * Options data instance.
	 *
	 * @var getOptions
	 */
	public $get_options = null;
	
	/**
	 * Table data instance.
	 *
	 * @var Table
	 */
	public $table = null;
	
	/**
	 * Template data instance.
	 *
	 * @var Template
	 */
	public $template = null;
	
	/**
	 * Rating data instance.
	 *
	 * @var Rating
	 */
	public $rating = null;
	
	/**
	 *  Post like data instance.
	 *
	 * @var Max_Grid_Post_Like
	 */
	public $post_like = null;
	
	/**
	 *  GB Builder ajax instance.
	 *
	 * @var Max_Grid_Builder_Ajax_Request
	 */
	public $builder_ajax = null;
	
	/**
	 *  reCAPTCHA instance.
	 *
	 * @var g_recaptcha
	 */
	public $g_recaptcha = null;
	
	/**
	 *  Max_Grid_Woo instance.
	 *
	 * @var wc
	 */
	public $woo = null;
	
	/**
	 *  Max_Grid_Download instance.
	 *
	 * @var download
	 */
	public $download = null;
	
	/**
	 *  Max_Grid_Youtube instance.
	 *
	 * @var youtube
	 */
	public $youtube = null;
	
	/**
	 *  Max_Grid_Premium instance.
	 *
	 * @var premium
	 */
	public $premium = null;
	
	/**
	 *  Get post meta instance.
	 *
	 * @var get_post_meta
	 */
	public $get_post_meta = null;
	
	/**
	 *  Assets instance.
	 *
	 * @var Max_Grid_Admin_Assets
	 */
	public $assets = null;
	
	/**
	 * Main Max_Grid Instance.
	 *
	 * Ensures only one instance of Max_Grid is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see maxgrid()
	 * @return Max_Grid - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Max_Grid Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		do_action( 'maxgrid_builder_loaded' );
	}
	
	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );	
		add_filter( 'comments_open', array( $this, 'disable_ajax_comment_form' ), 20, 2);
		add_filter( 'pings_open', array( $this, 'disable_ajax_comment_form' ), 20, 2);
		add_filter( 'comments_array', array( $this, 'default_comments_version' ), 10, 2);		
		add_filter( 'post_thumbnail_html', array( $this, 'hide_featured' ), 10, 3 );		
		add_filter( 'plugin_row_meta', array( $this, 'maxgrid_plugin_row_meta' ), 10, 2 );
		
		// Review Request
		//add_action( 'admin_footer_text', array( $this, 'admin_footer_text') );
	}	

	/**
	 * Plugin row meta links
	 *
	 * @param array $input already defined meta links
	 * @param string $file plugin file path and name being processed
	 * @return array $input
	 */
	public function maxgrid_plugin_row_meta( $input, $file ) {
		if ( $file != 'max-grid/max-grid.php' ) {
			return $input;
		}
		$plugin_data = get_plugin_data( MAXGRID_ABSPATH . '/max-grid.php');
		
		$new_links = array(
			'doc' 	  => '<a href="' . $plugin_data['PluginURI'] . '/docs/" target="_blank">' . esc_html__( 'Documentation', 'max-grid' ) . '</a>',
			'support' => '<a href="' .$plugin_data['AuthorURI'] . '/support/" target="_blank">' . esc_html__( 'Support', 'max-grid' ) . '</a>'
		);

		$input = array_merge( $input, $new_links );

		return $input;
	}

	/**
	 * Define MaxGrid Constants.
	 */
	private function define_constants() {			
		$this->define( 'MAXGRID_ABSPATH', dirname( MAXGRID_PLUGIN_FILE ) . '/' );
		$this->define( 'MAXGRID_ABSURL', plugins_url('', MAXGRID_PLUGIN_FILE) );
		$this->define( 'MAXGRID_BACKUP_TEMPLATES_VERSION', '1.0' );
		$this->define( 'MAXGRID_POST', 'download' );
		$this->define( 'MAXGRID_BUILDER_PAGE', 'maxgrid-builder' );
		$this->define( 'MAXGRID_SETTINGS_PAGE', 'maxgrid-settings' );
		$this->define( 'MAXGRID_EXTENTIONS_PAGE', 'maxgrid-extentions' );
		$this->define( 'MAXGRID_POST_SLUG', 'downloads' );
		$this->define( 'MAXGRID_CAT_TAXONOMY', 'download_category' );//download_cat / download_category
		$this->define( 'MAXGRID_TAG_TAXONOMY', 'download_tag' );
		$this->define( 'MAXGRID_LAYOUTS_TABLE_NAME', 'maxgrid_grid_templates_tbl' );
		$this->define( 'MAXGRID_STE_LABEL_NAME', 'Max Grid' );
		$this->define( 'MAXGRID_PLUGIN_LABEL_NAME', 'Max Grid' );
		$this->define( 'MAXGRID_CSS_CODE_COMMENT', '/* Add your CSS code here. */' );
		$this->define( 'MAXGRID_DOWNLOAD_META_KEY', 'downloads_count' );
		$this->define( 'MAXGRID_DFLT_LAYOUT_NAME', 'post_default' );
		$this->define( 'MAXGRID_BUILDER_OPT_NAME', 'maxgrid_builder' );
		$this->define( 'MAXGRID_SETTINGS_OPT_NAME', 'maxgrid_builder_settings' );
		$this->define( 'MAXGRID_LOGS_OPT_NAME', 'track_options' );		
		$this->define( 'MAXGRID_ERROR_LOADING', sprintf( __( '%sERROR LOADING!%s', 'max-grid'), '<strong>', '</strong>') );
		$this->define( 'MAXGRID_ERROR_NOTE', sprintf( __( '%sNOTE: %s', 'max-grid'), '<strong>', '</strong>') );
		$this->define( 'MAXGRID_SITE_HOME_PAGE', 'https://mustaphafersaoui.fr/wp-plugins/max-grid' );
	}
	
	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
	
	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		
		/**
		 * Interfaces.
		 */
		include_once MAXGRID_ABSPATH . 'includes/classes/options.php';
		include_once MAXGRID_ABSPATH . 'includes/classes/presets.class.php';
		include_once MAXGRID_ABSPATH . 'includes/core-functions.php';
		include_once MAXGRID_ABSPATH . 'includes/formatting-functions.php';
		include_once MAXGRID_ABSPATH . 'assets/admin/class-settings-page.php';
		include_once MAXGRID_ABSPATH . 'assets/admin/class-admin-assets.php';
		include_once MAXGRID_ABSPATH . 'assets/admin/class-admin-profile.php';	
		include_once MAXGRID_ABSPATH . 'includes/classes/transient.class.php';	
		include_once MAXGRID_ABSPATH . 'includes/classes/global.php';
		include_once MAXGRID_ABSPATH . 'includes/classes/lightbox.php';
		include_once MAXGRID_ABSPATH . 'includes/classes/elements.php';
		include_once MAXGRID_ABSPATH . 'includes/classes/description-reviews-tabs.php';
		include_once MAXGRID_ABSPATH . 'includes/classes/meta-key.php';
		include_once MAXGRID_ABSPATH . 'includes/classes/post-sorting.php';
		include_once MAXGRID_ABSPATH . 'includes/classes/get-post-meta.php';	
		include_once MAXGRID_ABSPATH . 'includes/class-ajax-request.php';
		include_once MAXGRID_ABSPATH . 'assets/admin/class-html-elements.php';		
		include_once MAXGRID_ABSPATH . 'includes/class-comments-tabs.php';		
		include_once MAXGRID_ABSPATH . 'includes/template-functions.php';
		include_once MAXGRID_ABSPATH . 'includes/downloads-functions.php';		
		include_once MAXGRID_ABSPATH . 'includes/class-layout.php';
		include_once MAXGRID_ABSPATH . 'builder/class-dflt-params.php';
		include_once MAXGRID_ABSPATH . 'builder/builder-functions.php';
		include_once MAXGRID_ABSPATH . 'builder/class-builder.php';
		include_once MAXGRID_ABSPATH . 'builder/class-builder-ajax-request.php';
		include_once MAXGRID_ABSPATH . 'assets/common/color-scheme/color-scheme.php';		
		include_once MAXGRID_ABSPATH . 'assets/admin/class-admin-meta-boxes.php';		
		include_once MAXGRID_ABSPATH . 'tinymce/mce-max-grid.php';
		include_once MAXGRID_ABSPATH . 'includes/class-woocommerce.php';				
		include_once MAXGRID_ABSPATH . 'assets/admin/class-frontend-scripts.php';		
		
		if ( is_admin() ) {			
			include_once MAXGRID_ABSPATH . 'assets/admin/class-table.php';
		}
		include_once MAXGRID_ABSPATH . 'includes/class-install.php';	
		include_once MAXGRID_ABSPATH . 'includes/class-single-post.php';
	}
	
	/**
	 * Init Max_Grid when WordPress Initialises.
	 */
	public function init() {
		
		// Before init action.
		do_action( 'before_maxgrid_builder_init' );
		

		// Set up localisation.
		$this->load_plugin_textdomain();

		// Load class instances.
		$this->settings		 = new Max_Grid_Settings;
		$this->get_options 	 = new getOptions;
		$this->template  	 = new Template;		
		$this->builder_ajax	 = new Max_Grid_Builder_Ajax_Request;
		
		$this->get_post_meta = new get_post_meta;
		$this->assets		 = new Max_Grid_Admin_Assets();
		$this->transient 	 = new Max_Grid_Transient(null);
		
		if ( is_admin() ) {
			$this->table = new Table;
		}
				
		if ( class_exists( 'WooCommerce' ) && class_exists( 'Max_Grid_Woo' ) ) {
			$this->woo = new Max_Grid_Woo;
		}
		
		if ( class_exists( 'Max_Grid_Download' ) || class_exists( 'WooCommerce' ) && class_exists( 'Max_Grid_Woo' ) ) {
			$this->rating = new Max_Grid_Rating;
		}
		
		if ( class_exists( 'Max_Grid_Download' ) ) {
			$this->download = new Max_Grid_Download;
		}
		
		if ( class_exists( 'Max_Grid_Premium' ) ) {
			$this->g_recaptcha	 = new g_recaptcha;
			$this->post_like 	 = new Max_Grid_Post_Like;
			$this->premium 		 = new Max_Grid_Premium;
		}
		
		do_action( 'maxgrid_builder_init' );
	}
	
	/**
	 * Load translations
	 */
	public function load_plugin_textdomain() {
		unload_textdomain( 'max-grid' );
		load_plugin_textdomain( 'max-grid', false, plugin_basename( dirname( MAXGRID_PLUGIN_FILE ) ) . '/languages/' );
	}
	
	/**
	 * Disable akjax comment form.
	 *
	 * @return bool
	 */
	public function disable_ajax_comment_form() {		
		$fo_o = $this->get_options->option('forms');
		$disable_ajax_form = isset($fo_o['disable_ajax_form']) && ( maxgrid_string_to_bool($fo_o['disable_ajax_form']) == 1 || $fo_o['disable_ajax_form'] == 'disable_ajax_form' ) ? true : false ;

		if ( is_singular( 'product' ) ) {
			return true;
		}

		if ( $disable_ajax_form && !is_singular( MAXGRID_POST ) ) {
			return true;
		}

		if ( is_singular( MAXGRID_POST ) || is_single() ) {
			return false;
		}
		return true;
	}
	
	/**
	 * Disable existing comments when the ajax version is enabled
	 *
	 * @return string
	 */ 
	public function default_comments_version($comments) {
		
		$fo_o = $this->get_options->option('forms');

		$disable_ajax_form = isset($fo_o['disable_ajax_form']) && ( maxgrid_string_to_bool($fo_o['disable_ajax_form']) == 1 || $fo_o['disable_ajax_form'] == 'disable_ajax_form' ) ? true : false ;

		if ( $disable_ajax_form && !is_singular( MAXGRID_POST ) ) {
			return $comments;
		}

		if ( is_singular( 'product' ) ) {
			return $comments;
		}
		if ( is_singular( MAXGRID_POST ) || is_single() ) {
			$comments = array();
		}    
		return $comments;
	}
		
	/**
	 * Hide featured image in single download post
	 *
	 * @return string
	 */
	function hide_featured( $html, $post_id, $post_image_id ) {
		if(is_single()) {			
		}
		if ( is_singular( MAXGRID_POST ) ) {
			return '';
		}
		return $html;
	}
	
	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', MAXGRID_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( MAXGRID_PLUGIN_FILE ) );
	}
	
	/**
	 * is_edit_page 
	 * function to check if the current page is aedit page
	 * 
	 * @return boolean
	 */
	public function is_edit_page() {
		//make sure we are on the backend
		if (!is_admin()) return false;
		return !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) || get_post_type() != 'page';
	}
	
	/**
	 * Review Request
	 *
	 * @param string $text  The default footer text.
	 * @return string $text Amended footer text.
	 */
	public function admin_footer_text( $text ) {	
		  global $current_screen;

		  if ( !empty( $current_screen->id ) && strpos( $current_screen->id, MAXGRID_BUILDER_PAGE ) !== false || strpos( $current_screen->id, MAXGRID_SETTINGS_PAGE ) !== false ) {
			$url  = 'https://wordpress.org/support/plugin/max-grid/reviews/?filter=5#new-post';
			$text = sprintf( __( 'Please rate <strong>Max Grid</strong> <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%s" target="_blank">WordPress.org</a> to help us spread the word. Thank you!', 'max-grid' ), $url, $url );
		  }
		  return $text;
		}
}