<?php
/**
 * Add extra profile fields for users in admin
 *
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Max_Grid_Admin_Profile', false ) ) :

	/**
	 * Max_Grid_Admin_Profile Class.
	 */
	class Max_Grid_Admin_Profile {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {			
			add_action( 'show_user_profile', array( $this, 'add_custom_meta_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'add_custom_meta_fields' ) );

			add_action( 'personal_options_update', array( $this, 'save_custom_meta_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_custom_meta_fields' ) );
		}
		
		/**
		 * Show Address Fields on edit user pages.
		 *
		 * @param WP_User $user
		 */
		public function add_custom_meta_fields( $user ) {
			?>
				<h3 class="author-info-box"><?php echo __( 'Author Info Box', 'max-grid' );?></h3>
				<table class="form-table author-info-box">
					<tr>
						<th><label for="twitter">Twitter username (without @)</label></th>
						<td>
							<input type="text" name="maxgrid_twitter" id="maxgrid_twitter" value="<?php echo esc_attr( get_the_author_meta( 'maxgrid_twitter', $user->ID ) ); ?>" class="regular-text" />
						</td>
					</tr>
				</table>
			<?php
		}

		/**
		 * Save Address Fields on edit user pages.
		 *
		 * @param int $user_id User ID of the user being saved
		 */
		public function save_custom_meta_fields( $user_id ) {
			if ( !current_user_can( 'edit_user', $user_id ) )
				return false;
			
			update_user_meta( $user_id, 'maxgrid_twitter', sanitize_text_field( $_POST['maxgrid_twitter'] ) );
		}
	}

endif;

return new Max_Grid_Admin_Profile();