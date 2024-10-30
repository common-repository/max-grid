<?php
/**
 *  Color Scheme - globally defined color scheme
 *
 * Max_Grid_Color_Scheme Class
 *
 * @version     1.0.0
 */

class Max_Grid_Color_Scheme {	
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		
	}
	
	/**
	 * Color sheme
	 *
	 * @param mixed  $data
	 */
	public function color_scheme($data) {
		extract(maxgrid_get_options());

		$options = array(
						'extra_color_1' => 'Accent Color',
						'extra_color_2' => 'Text (over Base)',
						'extra_color_3' => 'Extra Color #1',
						'extra_color_4' => 'Extra Color #2',
					);

		$colors = array(
						'extra_color_1' => $extra_color_1,
						'extra_color_2' => $extra_color_2,
						'extra_color_3' => $extra_color_3,
						'extra_color_4' => $extra_color_4,
					);

		$name	  	  = isset($data['name']) ? $data['name'] : 'extra_color';
		$position 	  = isset($data['position']) ? ' '.$data['position'] : '';
		$current  	  = isset($data['current']) ? $data['current'] : array();
		$active_color = isset($current[$name]) ? $colors[$current[$name]] : $extra_color_1;
		$header_title = isset($current[$name]) ? $options[$current[$name]] : 'Choose an option';
		$form_field   = isset($data['form_field']) && maxgrid_string_to_bool($data['form_field'])==1 ? ' class="form-field"' : '';

		$gs_url 	  = get_home_url().'/wp-admin/admin.php?page=' . MAXGRID_SETTINGS_PAGE.'&tab=tab5';
		?>

		<div class="maxgrid_ui-col flex" style="padding-top: 0; height: 30px;">					
			<div class="maxgrid_ch-box">
				<input id="use_<?php echo $name;?>"<?php echo $form_field;?> name="use_<?php echo $name;?>" data-triger="dropp" data-id="<?php echo $name;?>" value="1" type="checkbox" <?php if( isset($current['use_'.$name]) && maxgrid_string_to_bool($current['use_'.$name])==1):echo"checked";endif; ?> class="use-color-scheme">
				<label for="use_<?php echo $name;?>">Use Color Scheme</label>
			</div>
		</div>

		<div class="maxgrid_ui-col color-scheme-dropp-container <?php echo $name;?>">
			<div class="dropp">
				<div class="dropp-header <?php echo $name;?>">
					<span class="extra-color__chosen_preview" style="background: <?php echo $active_color;?>"></span>
					<span class="dropp-header__title js-value"><?php echo $header_title;?></span>
					<div class="js-dropp-action"><b></b></div>
				</div>

				<div class="dropp-body<?php echo $position;?>">
					<?php
					foreach($options as $key => $value) {
						?>				
						<label for="<?php echo $name.'_'.$key;?>" class="<?php if(isset($current[$name]) && $current[$name]==$key):echo"js-open";endif; ?>">
							<?php echo $value;?>
							<input type="radio" id="<?php echo $name.'_'.$key;?>"<?php echo $form_field;?> name="<?php echo $name;?>" data-triger="dropp-checked" value="<?php echo $key;?>" data-label="<?php echo $value;?>" data-color="<?php echo $colors[$key];?>" data-radio-id="<?php echo $name;?>" <?php if(isset($current[$name]) && $current[$name]==$key):echo"checked";endif; ?>/>
							<span class="extra-color__preview" style="background: <?php echo $colors[$key];?>"></span>
						</label>
						<?php				
					}
					?>
				</div>
			</div>
			<?php
			if ( isset($data['footer']) ) {
			?>
			<p style="line-height: 15px; margin-top: 8px; margin-left: 0;">Choose a color from your <a href="<?php echo $gs_url;?>" data-tab="tab1" target="_blank">globally defined color scheme</a></p>
			<?php
			}
			?>
		</div>
		<?php
	}
	
	/**
	 * Term Color
	 *
	 * @param mixed  $data
	 */
	public function term_color($data) {
		if ( !is_maxgrid_premium_activated() ) {
			return;
		}
		extract(maxgrid_get_options());

		$options = array(
						'term_c1' => 'Term Color #1',
						'term_c2' => 'Term Color #2',
						'term_c3' => 'Extra Term Color',
					);

		$name	  	  = isset($data['name']) ? $data['name'] : '';
		$position 	  = isset($data['position']) ? ' '.$data['position'] : '';
		$current  	  = isset($data['current']) ? $data['current'] : array();
		$header_title = isset($current[$name]) ? $options[$current[$name]] : 'Choose Term Color';
		$styles 	  = isset($data['styles']) ? $data['styles'] : '';
		$tc_id 	  	  = isset($data['tc_id']) ? $data['tc_id'] : 'tc_1';		
		$gs_url 	  = get_home_url().'/docs/' . '#managing_categories';
		?>
		<div class="maxgrid_ui-col flex" style="padding-top: 0; height: 30px; <?php echo $styles;?>">					
			<div class="maxgrid_ch-box">
				<input id="use_t_<?php echo $name;?>" name="use_t_<?php echo $name;?>" data-triger="dropp" data-id="<?php echo $tc_id;?>" value="1" type="checkbox" <?php if( isset($current['use_t_'.$name]) && maxgrid_string_to_bool($current['use_t_'.$name])==1):echo"checked";endif; ?> class="form-field use-term-color">
				<label for="use_t_<?php echo $name;?>">Use Term Color</label>
			</div>
		</div>

		<div class="maxgrid_ui-col color-scheme-dropp-container <?php echo $tc_id;?>" style="margin-bottom: 10px">
			<div class="dropp">
				<div class="dropp-header <?php echo $tc_id;?>">
					<span class="dropp-header__title js-value term-color-selctor"><?php echo $header_title;?></span>
					<div class="js-dropp-action"><b></b></div>
				</div>

				<div class="dropp-body<?php echo $position;?>">
					<?php
					foreach($options as $key => $value) {
						?>				
						<label for="<?php echo $tc_id.'_'.$key;?>" class="<?php if(isset($current[$name]) && $current[$name]==$key):echo"js-open";endif; ?>">
							<?php echo $value;?>
							<input type="radio" id="<?php echo $tc_id.'_'.$key;?>" name="<?php echo $name;?>" data-triger="dropp-checked" value="<?php echo $key;?>" data-label="<?php echo $value;?>" data-radio-id="<?php echo $name;?>" <?php if(isset($current[$name]) && $current[$name]==$key):echo"checked";endif; ?> class="form-field">
						</label>
						<?php				
					}
					?>
				</div>
			</div>
			<?php
			if ( isset($data['footer']) ) {
			?>
			<p style="line-height: 15px; margin-top: 8px; margin-left: 0;">Learn more about <a href="<?php echo $gs_url;?>" data-tab="tab1" target="_blank">Term Color</a>.</p>
			<?php
			}
			?>
		</div>
		<?php
	}
}