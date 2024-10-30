<?php
/**
 * Max_Grid Meta Boxes
 *
 * Sets up the write panels used by posts, downloads and products (custom post types).
 */

defined( 'ABSPATH' ) || exit;

/**
 * @class Max_Grid_Admin_Meta_Boxes.
 */
class Max_Grid_Admin_Meta_Boxes {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );		
		add_action(	'admin_print_scripts', array( $this, 'metabox_scripts' ) );
	}
	
	/**
	 * Add GP Meta boxes.
	 */
	public function add_meta_boxes() {
		if ( function_exists('get_current_screen')) { 
			$pt = get_current_screen()->post_type;
			if ( $pt == 'page') return;
		}
		add_meta_box( 'grid_builder_metabox', sprintf( __( '%s Settings', 'max-grid' ), 'Max Grid'), array( $this, 'settings_metabox' ), $pt, 'normal', 'default');	
	}
	
	/**
	 * Remove bloat.
	 */
	public function remove_meta_boxes() {
		global $pagenow, $typenow;
		if ( ($pagenow == 'post.php' || $pagenow == 'post-new.php') && $typenow == MAXGRID_POST ) {
			remove_meta_box( 'authordiv', MAXGRID_POST,'normal' ); // Author Metabox
			remove_meta_box( 'commentstatusdiv', MAXGRID_POST,'normal' ); // Comments Status Metabox 
			remove_meta_box( 'commentsdiv', MAXGRID_POST,'normal' ); // Comments Metabox
			remove_meta_box( 'postcustom', MAXGRID_POST,'normal' ); // Custom Fields Metabox
			remove_meta_box( 'revisionsdiv', MAXGRID_POST,'normal' ); // Revisions Metabox
			remove_meta_box( 'trackbacksdiv', MAXGRID_POST,'normal' ); // Trackback Metabox
		}
	}
	
	/**
	 * Save Order Meta Boxes.
	 *
	 * @param  int    $post_id
	 * @param  object $post
	 */
	public function save_meta_boxes( $post_id, $post ) {				
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}
		
		// Dont' save meta boxes for revisions or autosaves
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		
		$transiant = new Max_Grid_Transient(null);
		$transiant->delete_all_transient();
		// Checks save status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 'maxgrid_nonce' ] ) && wp_verify_nonce( sanitize_text_field($_POST[ 'maxgrid_nonce' ]), basename( __FILE__ ) ) ) ? 'true' : 'false';

		
		// Exits script depending on save status
		if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
			return;
		}
		
		
		// Grid Settings MetaBox
		if ( isset( $_POST[ 'maxgrid_featured_mode' ] ) ) {
			update_post_meta( $post_id, 'maxgrid_featured_mode', sanitize_text_field( $_POST[ 'maxgrid_featured_mode' ] ) );
		}
		if ( isset( $_POST[ 'maxgrid_embed_vimeo_url' ] ) ) {
			update_post_meta( $post_id, 'maxgrid_embed_vimeo_url', sanitize_text_field( $_POST[ 'maxgrid_embed_vimeo_url' ] ) );
		}
		if ( isset( $_POST[ 'maxgrid_embed_youtube_url' ] ) ) {
			update_post_meta( $post_id, 'maxgrid_embed_youtube_url', sanitize_text_field( $_POST[ 'maxgrid_embed_youtube_url' ] ) );
		}
		if ( isset( $_POST[ 'maxgrid_embed_mp4_url' ] ) ) {
			update_post_meta( $post_id, 'maxgrid_embed_mp4_url', sanitize_text_field( $_POST[ 'maxgrid_embed_mp4_url' ] ) );
		}		
		if ( isset( $_POST[ 'maxgrid_mp4_v_duration' ] ) ) {
			update_post_meta( $post_id, 'maxgrid_mp4_v_duration', sanitize_text_field( $_POST[ 'maxgrid_mp4_v_duration' ] ) );
		}		
		if ( isset( $_POST[ 'maxgrid_thumbnail_url' ] ) ) {
			update_post_meta( $post_id, 'maxgrid_thumbnail_url', sanitize_text_field( $_POST[ 'maxgrid_thumbnail_url' ] ) );
		}
		if( isset( $_POST[ 'maxgrid_hide_featured' ] ) ) {
			update_post_meta( $post_id, 'maxgrid_hide_featured', '1' );
		} else {
			update_post_meta( $post_id, 'maxgrid_hide_featured', '0' );
		}
		if ( isset( $_POST[ 'audio_player' ] ) ) {
			update_post_meta( $post_id, 'audio_player', sanitize_text_field( $_POST[ 'audio_player' ] ) );
		}
		if ( isset( $_POST[ 'maxgrid_audio_file' ] ) ) {
			update_post_meta( $post_id, 'maxgrid_audio_file', sanitize_text_field( $_POST[ 'maxgrid_audio_file' ] ) );
		}
		if ( isset( $_POST[ 'soundcloud_code' ] ) ) {
			$allowed_html = array(
				'iframe' => array(),
			);
			$soundcloud_code = wp_kses($_POST['soundcloud_code'], $allowed_html);
			update_post_meta( $post_id, 'soundcloud_code', implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $soundcloud_code ) ) ));
		}
		if ( isset( $_POST[ 'maxgrid_the_excerpt' ] ) ) {
			update_post_meta( $post_id, 'maxgrid_the_excerpt', implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['maxgrid_the_excerpt'] ) ) ));
		}
	}
	
	/**
	 * Render post settings meta boxes
	 *
	 * @param  object $post
	 */
	public function settings_metabox( $post ) {
		global $pagenow, $typenow;
		wp_nonce_field( basename( __FILE__ ), 'maxgrid_nonce' );
		$stored_meta = get_post_meta( $post->ID);
		?>
		<table class="form-table maxgrid-metabox-table">
		 <tbody>
			<tr style="display: table-row;">
				<th>
					<label for="maxgrid_featured_mode">
						<strong>Grid Post Thumbnail</strong>					
						<span class="featured_img_sublabel">Use this option to set the Grid Post Thumbnail type.</span>
					</label>
				</th>			  
				<td>
				<?php  
					$featured_options = array(
					'image' => 'Featured Image',
					'vimeo' => 'Vimeo Video',
					'youtube' => 'Youtube Video',
					'mp4' => 'MP4 Video'
				);
				// Current setting.
				$current = isset($stored_meta['maxgrid_featured_mode'][0]) ? $stored_meta['maxgrid_featured_mode'][0] : 'image';

				// Build <select> element.
				$html = '<select id="maxgrid_featured_mode" name="maxgrid_featured_mode" >';
				foreach ( $featured_options as $value => $text )
				{
					$html .= '<option value="'. $value .'"';
					// We make sure the current options selected.
					if ( $value == $current ) $html .= ' selected="selected"';
					$html .= '>'. $text .'</option>';
				}
				$html .= '</select>';
				echo( $html );
				?>           
				</td>
			</tr>

			<tr style="border: 0;">
				<th style="padding-top: 0;">
					<label>
						<span class="featured_sublabel vimeo">Enter your vimeo video URL.</span>
						<span class="featured_sublabel youtube">Enter your youtube video URL.</span>
						<span class="featured_sublabel mp4">
							<strong>Enter your MP4 video URL.</strong>
							<span class="featured_img_sublabel">Manually enter a valid URL of your mp4 file, or click "Upload File" button to upload (or choose) mp4 file.</span>
						</span>
					</label>  

				</th>

				<td style="padding-top: 0;">
					<!-- vimeo URL -->  
					<input class="embed_video_url vimeo" type="text" size="100" name="maxgrid_embed_vimeo_url" value="<?php if ( ! empty ( $stored_meta['maxgrid_embed_vimeo_url'] ) ) {
						echo esc_attr( $stored_meta['maxgrid_embed_vimeo_url'][0] );
					} ?>" placeholder="Vimeo video URL" />

					<!-- youtube URL -->
					<input class="embed_video_url youtube" type="text" size="100" name="maxgrid_embed_youtube_url" value="<?php if ( ! empty ( $stored_meta['maxgrid_embed_youtube_url'] ) ) {
						echo esc_attr( $stored_meta['maxgrid_embed_youtube_url'][0] );
					} ?>" placeholder="Youtube video URL" />

					<!-- mp4 URL -->
					<input class="embed_video_url mp4" type="text" size="100" name="maxgrid_embed_mp4_url" value="<?php if ( ! empty ( $stored_meta['maxgrid_embed_mp4_url'] ) ) {
						echo esc_attr( $stored_meta['maxgrid_embed_mp4_url'][0] );
					} ?>" placeholder="mp4 or webm video URL" />
					<input type="button" class="button-primary mp4" value="Upload / Select mp4 File" placeholder="http://..." />
					
					<!-- MP4 Video Duration -->
					<input class="maxgrid_mp4_v_duration" type="hidden" size="100" name="maxgrid_mp4_v_duration" value="<?php if ( ! empty ( $stored_meta['maxgrid_mp4_v_duration'] ) ) {
						echo esc_attr( $stored_meta['maxgrid_mp4_v_duration'][0] );
					} ?>" placeholder="mp4 or webm video URL" />
					<br>
					<video id="pg_mp4-video" class="video-js" controls preload="auto" data-setup="{}" height="10px">
						<source src="" type="video/mp4">
					</video>
					
				</td>
			</tr>

			<tr class="tr_featured_sublabel mp4" style="border: 0;">
				<th style="padding-top: 0;">
				   <label for="thumbnail_upload_button">
						<strong>Thumbnail</strong>
						<span>Upload your own Video thumbnail (JPG/GIF/PNG).</span>
					</label>        
				</th>

				<td style="padding-top: 0;">
					<input id="thumbnail_upload_button" type="button" class="button-secondary" value="Uplaod" placeholder="http://..." />
					<input type="hidden" id="maxgrid_thumbnail_url" type="text" class="maxgrid_thumbnail_url" size="100" name="maxgrid_thumbnail_url" value="<?php echo isset($stored_meta['maxgrid_thumbnail_url']) ? esc_attr( $stored_meta['maxgrid_thumbnail_url'][0] ) : '' ;?>" placeholder="" />
					<br>
					<div class="maxgrid-img-wrap-extra">
						<span class="close">&times;</span>
						<img class='prev-img-extra' id="prev-img-extra" src='<?php echo isset($stored_meta['maxgrid_thumbnail_url'][0]) ? esc_attr( $stored_meta['maxgrid_thumbnail_url'][0] ) : ""; ?>' width='100' height='100' style='max-height: 120px; width: auto; margin: 15px 0px;'>
					</div>
				</td>
			</tr>
			 
			<?php 
			if ( is_maxgrid_premium_activated() ) {
				echo maxgrid()->premium->audio_player_meta_box($stored_meta, $post->ID);
			}
			?>
			<tr>
				<th> 
					<label for="maxgrid_the_excerpt">
						<strong>List View Excerpt</strong>
						<span>Excerpts are optional handcrafted summaries of your content that can be used instead of the default excerpt.</span>
					</label>            
				</th>
				
				<td class="excerpt_wp_editor">
				<?php
					$content = $content = get_post_meta( $post->ID, 'maxgrid_the_excerpt', true );
					$editor = 'maxgrid_the_excerpt';

					$settings =   array(
							'wpautop' => true, // use wpautop?
							'media_buttons' => false, // show insert/upload button(s) // default true
							'textarea_name' => $editor, // set the textarea name to something different, square brackets [] can be used here
							'textarea_rows' => 8, // rows="..." / default get_option('default_post_edit_rows', 10)
							'tabindex' => '',
							'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
							'editor_class' => '', // add extra class(es) to the editor textarea
							'teeny' => true, // output the minimal editor config used in Press This // default false
							'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
							'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
							'tinymce' => array(
											// Items for the Visual Tab
											//'toolbar1'=> 'bold,italic,underline,bullist,numlist,link,unlink,forecolor,undo,redo,',
											'toolbar1'=> 'bold,italic,underline,bullist,numlist,link,unlink,forecolor,undo,redo,',
											),
							'quicktags' => false, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
							'template' => 'wp_theme'
						);

					$allowed_tags = array(
						'a' => array(
							'href' => array(),
							'title' => array()
						),
						'br' => array(),
						'em' => array(),
						'strong' => array(),
						'iframe' => array(),
					);

					$allowed_tags = wp_kses_allowed_html( 'post' );
					wp_editor(wp_kses($content, $allowed_tags), $editor, $settings );
				?>
				</td>
			</tr>

		</tbody>
		</table>

	  <?php 
	}
		
	/**
	 * Enqueue scripts.
	 */
	public function metabox_scripts() {
		global $pagenow, $typenow;
		if ( ( $pagenow != 'post.php' && $pagenow != 'post-new.php' ) ) {
			//return;
		}
		wp_enqueue_script( 'meta-box',MAXGRID_ABSURL . '/assets/js/metabox.js', array ( 'jquery' ), false, true);
		
		?>
		<style type="text/css">
			#grid_builder_metabox {
				border: 1px solid #ebebeb !important;
				margin: 20px 0 !important;
			}
			#grid_builder_metabox h2.hndle {
				margin-left: -1px!important;
				margin-right: -1px!important;
				margin-bottom: -1px!important;
				background: #32373c!important;
				color: #fff!important;
			}
			#grid_builder_metabox .toggle-indicator {
				color: #fff!important;
			}
			#grid_builder_metabox .form-table {
				border-collapse: collapse;
				margin-top: .5em;
				width: 100%;
				clear: both;
				font-size: 14px;
			}
			#grid_builder_metabox table.form-table {
				margin-bottom: 0;
			}
			.maxgrid-metabox-table tr:not(:first-of-type) {
				border-top: 1px solid #EEEEEE;
			}
			.maxgrid-metabox-table th {
				width: 25%!important;
			}
			#grid_builder_metabox .form-table th {
				vertical-align: top;
				text-align: left;
				padding: 20px 80px 20px 20px;
				width: 200px;
				line-height: 1.3;
				font-weight: 600;
			}		
			#grid_builder_metabox .form-table td {
				width: 45%;
			}		
			#grid_builder_metabox .form-table td {
				margin-bottom: 9px;
				padding: 15px 10px;
				line-height: 1.3;
				vertical-align: middle;
			}
			#grid_builder_metabox label {
				cursor: pointer;
			}		
			#grid_builder_metabox b {
				font-weight: 600;
			}
			.maxgrid-metabox-table label span:not(.featured_sublabel) {
				color: #999999;
				font-size: 12px;
				display: block;
				line-height: 20px;
				margin: 0px 0 0;
				font-weight: normal;
			}		
			.maxgrid-metabox-table input[type="text"], .maxgrid-metabox-table textarea {
				float: left;
				margin-right: 20px;
				width: 100%;
				transition: all 0.2s linear;
				-moz-transition: all 0.2s linear;
				-webkit-transition: all 0.2s linear;
				-o-transition: all 0.2s linear;
			}
			#maxgrid_downloads_offset, #maxgrid_protect_mode_password {
				width: 200px;
			}
			.maxgrid-metabox-table input[type="button"] {
				margin-top: 10px!important;
			}
			.maxgrid-metabox-table select {
				min-width: 200px;
			}
			#grid_builder_metabox .toggle-indicator::before {
				margin-top: -4px !important;
			}
			#grid_builder_metabox .handlediv:active,
			#grid_builder_metabox .handlediv:focus, {
				-webkit-box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.9)!important;
				box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.9)!important;
				box-shadow: none!important;
			}
			.featured_sublabel, .tr_featured_sublabel, .embed_video_url, .button-primary.mp4 {
				display: none;
			}
			.button-primary.mp4 {
				height: 27px!important;
				margin-top: -2px;
			}
			.maxgrid-grid-extra-meta-container label {
				display: inline-block;
				margin-bottom: 8px;
				margin-top: 10px;
				font-size: 14px;
				font-weight: 600;
				margin-left: 2px;
			}
			.maxgrid-grid-extra-meta-container textarea {
				width: 100%;
			}

			/* Thumbnail Preview */
			.maxgrid-img-wrap-extra {
				font-family: "Times New Roman", Georgia, Serif;
				position: relative;
				display: inline-block;
				font-size: 0;
			}
			.close {
				position: absolute;
				top: 17px;
				right: 2px;
				z-index: 100;
				background-color: #ffffff;
				color: #000;
				font-weight: bold;
				cursor: pointer;
				opacity: .2;
				text-align: center;
				font-size: 22px;
				height: 20px;
				width: 20px;
				line-height: 22px;
				border-radius: 50%;
			}
			.maxgrid-img-wrap-extra:hover .close {
				opacity: 1;
			}
			
			/* MP4 Video Player */
			#pg_mp4-video {
				visibility: hidden;
			}
		</style>
		<?php
	}
}
new Max_Grid_Admin_Meta_Boxes();