<?php
/**
 * Max Grid Builder - HTML Elements Constructor
 *
 * @since 1.0.0
 */

namespace MaxGrid;

defined( 'ABSPATH' ) || exit;

/**
 * Fields Class.
 */
class Max_Grid_Fields {
	
	/**
	 * Option name.
	 *
	 * @var string
	 */
	public static $opt_name;
	
	/**
	 * Parent option name.
	 *
	 * @var string
	 */
	public static $parent;
			
	/**
	 * Element Option Name.
	 *
	 * @var string $name Element ID
	 * @var string $parent Parent option name
	 *
	 * @return string
	 */
	private static function name($id, $parent='') {
		return self::$opt_name . $parent . '[' . $id . ']';
	}
	
	/**
     * Form Fields.
	 *
	 * @var array  $data Element attributes
	 * @var string $opt_name Option name
     *
     * @return string
     */
	private static function Current($data, $opt_name) {		
		if (!empty(self::$parent)){
			return isset(get_option($opt_name)[self::$parent][$data['id']]) ? get_option($opt_name)[self::$parent][$data['id']] : $data['default'];
		}
		return isset(get_option($opt_name)[$data['id']]) ? get_option($opt_name)[$data['id']] : $data['default'];
	}
	
	/**
     * Form Fields.
	 *
	 * @var array  $data Element attributes
	 * @var string $opt_name Option name
     *
     * @return string
     */
	private static function chkbox_Current($data, $opt_name) {		
		if (!empty(self::$parent)){
			return isset(get_option($opt_name)[self::$parent][$data['id']]) ? true : false;
		}
		return isset(get_option($opt_name)[$data['id']]) ? true : false;
	}
			
	/**
	 * Output element container - open surround.
	 */
	private static function Surround($data) {	
		$direction 	 = isset($data['direction']) ? ' '.$data['direction'] : '';
		$height 	 = isset($data['height']) ? ' '.$data['height'] : '';
		$width 		 = isset($data['width']) ? ' '.$data['width'] : '';
		$class 		 = isset($data['class']) ? ' '.$data['class'] : '';
		$style 		 = isset($data['style']) ? ' '.$data['style'] : '';
		$element_id  = isset($data['id']) ? ' '.$data['id'] : '';
		$tab_trigger = isset($data['tab_target']) ? ' maxgrid_tab-trigger' : '';
		$data_attr   = isset($data['data_attr']) ? ' '.$data['data_attr'] : '';		
		$raw_style = isset($data['raw_style']) ? ' style="'.$data['raw_style'].'"' : '';
		
		return '<div class="maxgrid-metaoptions-row ' . strtolower($data['type']) . $class . $element_id . self::Classname($data) . $direction . $height . $width . $style . $tab_trigger . '"'. $data_attr . $raw_style . '>';
	}
	
	/**
	 * Output element class.
	 */
	private static function Classname($data) {		
		$class  = isset($data['class']) ? ' '.$data['class'] : '';
		$class .= isset($data['display']) ? ' '.$data['display'] : '';		
		if ( isset($data['direction']) && $data['direction'] == 'vertical' ) : $class .= ' inline'; endif;		
		$class .= isset($data['target']) ? ' '.$data['target'] : '';
		$class .= isset($data['select_target']) ? ' '.$data['select_target'] : '';
		$class .= isset($data['target_visibility']) ? ' '.$data['target_visibility'] : '';		
		$class .= isset($data['border']) ? ' border' : '';
		
		return strtolower($class);
	}
	
	/**
	 * Output divider line.
	 */
	public static function Separator($data) {
		$width = isset($data['width']) ? $data['width'] : '';
		echo self::Surround($data);
		?>		
		<div class="maxgrid-separator <?php if(!empty($data['direction'])) : echo ' '.$data['direction']; endif; echo ' '.$data['style'];?>" <?php if ( !empty($data['class']) ) { echo 'class="'.$data['class'].'"';} ?> data-width="<?php echo $width;?>" data-height="<?php echo $data['height'];?>"></div>		
		</div> <!-- Close Surround -->	
		<?php
	}
	
	/**
	 * Output element custom style.
	 */
	private static function Styles($data) {
		$text_align = isset($data['text_align']) ? $data['text_align'] : 'center';
		$styles = 'style="';
		$styles .= isset($data['max_width']) ? ' max-width: '.$data['max_width'].'; text-align: '.$text_align.';' : '';
		$styles .= '"';
		
		return $styles;
	}
	
	/**
	 * Output form field label.
	 */
	public static function Label($data, $help='') {		
		$style = isset($data['label_style']) ? ' '.$data['label_style'] : '';
		$for = empty($help) ? 'for="'.$data['id'].'"' : '';
		$cursor_class = empty($for) ? ' no-cursor' : '';
		if ( isset($data['label']) ) { ?>
		<label <?php echo $for;?> class="noselect subtitle-label<?php echo $style.$cursor_class;?>" title="<?php echo $data['label'];?>"><?php echo $data['label'].$help;?></label>
		<?php }
	}
	
	/**
	 * Output text block.
	 */
	public static function TextBlock($data) {
		$help = isset($data['help_Tooltip']) ? self::HelpTooltip($data) : '';
		echo self::Surround($data);
		echo $data['text'].$help;
		?>
		</div> <!-- Close Surround -->		
		<?
	}
	
	/**
	 * Output HTML block.
	 */
	public static function HtmlBlock($data) {		
		echo self::Surround($data);
		echo $data['html-content'];?>	
		</div> <!-- Close Surround -->		
		<?		
	}
	
	/**
	 * Output group of buttons (e.g. Save Changes, Reset Options or Preview buttons).
	 */
	public static function SaveChanges($data) {		
		$html_include = isset($data['html_include']) ? self::Select($data['html_include']) : '';
		$position 	  = isset($data['position']) ? $data['position'] : 'top';
		$message 	  = isset($data['message']) ? $data['message'] : 'Are you sure you want to reset all settings to the default value?';
		$reset_button = isset($data['reset_button']) ? $data['reset_button'] : true;
		$responsive   = isset($data['responsive']) ? 'responsive-'.$data['responsive'] : '';		
		$class 		  = isset($data['class']) ? $data['class'] : 'maxgrid-button';
		
		?>
		<div class="maxgrid_save_changes_container <?php echo $position; ?>">
			<?php echo $html_include; ?>
			<span class="maxgrid_ajax_response inline">&nbsp;</span>
		<?php if (isset($data['buttons']) ) { 
				foreach ($data['buttons'] as $key => $value ) {
					$button_datas = '';
					if(isset($value['datas'])) {
						foreach ($value['datas'] as $data_name ) {
							$button_datas .= ' '.$value[$data_name];
						}						
					}
					$responsive = isset($value['responsive']) ? 'responsive-'.$value['responsive'] : '';
					?>
					<div id="maxgrid-button-style_container">
						
						<?php if ( strpos($key, 'save_changes')) { ?>
							<span class="spinner"></span>
						<?php } ?>
						
						<span id="<?php echo $key; ?>"<?php echo $button_datas; ?> class="<?php echo $class; ?> <?php echo $value['color'].' '. $responsive .' '. $value['icon']; ?>"><span><?php echo $value['label']; ?></span></span>
						
						<?php if ( strpos($key, 'save_changes') === false ) { ?>
							<span class="ajax_dl-spiner"></span>
						<?php } ?>
						
					</div>
			<?php }			
				
			} else { ?>
				<div id="maxgrid-button-style_container">
					<span class="spinner"></span>
					<span id="maxgrid_settings_save_changes" class="<?php echo $class; ?> bordered <?php echo $responsive; ?> no-icon"><span><?php echo 'Save Changes'; ?></span></span>
				</div>
		<?php }?>
		<?php
		if ($reset_button == true){ ?>
			<div id="maxgrid-button-style_container">
				<span id="maxgrid_reset_all_settings" class="maxgrid-button bordered <?php echo $responsive; ?> no-icon" data-dflt-optname="<?php echo self::$opt_name.'_default'; ?>" data-message="<?php echo $message; ?>"><span><?php echo 'Reset All'; ?></span></span>
				<span class="ajax_dl-spiner"></span>
			</div>
		<?php }?>
		</div>
		<?php
	}
	
	/**
	 * Output single button.
	 */
	public static function SingleButton($data) {		
		$color 	  = isset($data['color']) ? $data['color'] : 'blue';
		$icon 	  = isset($data['icon']) ? $data['icon'] : 'no-icon';
		$spinner  = isset($data['wp_spinner']) ? 'spinner' : 'ajax_dl-spiner';
		$float 	  = isset($data['float']) ? $data['float'] : 'left';
		$label 	  = isset($data['label']) ? $data['label'] : 'Send';
		$message  = isset($data['message']) ? $data['message'] : 'Are you sure you want to clear caches?';
		$ajax_response = isset($data['ajax_response']) ? $data['ajax_response'] : false;
		
		echo self::Surround($data);
		?>
			<div id="maxgrid-button-style_container">				
				<span id="<?php echo $data['id']; ?>" class="maxgrid-button <?php echo $color.' '.$icon; ?>" data-message="<?php echo $message; ?>"><span><?php echo $label; ?></span></span>
				<span class="<?php echo $spinner; ?> float-<?php echo $float; ?>"></span>
			</div>
			<?php
			if ($ajax_response == true){ ?>
				<div class="gb-ajax-btn_response"></div>
			<?php }?>
		</div>
		<?php
	}
	
	/**
	 * Output an accordion tab.
	 */
	public static function AccordionTab($data) {		
		if ( $data['position'] == 'in' ) {
			echo '<div class="bp-toggle-tab" id="'. $data['id'] . '">';
		} else if ( $data['position'] == 'out' ) {
			echo '</div>';
		}		
		return false;
	}
	
	/**
	 * Output option title.
	 */
	public static function Title($data) {
		$tab_target 	= isset($data['tab_target']) ? ' data-tab-target="'.$data['tab_target'].'"' : '';
		$tab_toggle_btn = isset($data['tab_target']) ? '<div class="maxgrid_toggle-button"></div>' : '';
		
		echo self::Surround($data);
		?>		
		<div class="maxgrid-options__title <?php if ( empty($data['class']) ) { echo 'full-width';} else { echo $data['class'];} ?>"<?php echo $tab_target;?>><?php echo $data['label'].$tab_toggle_btn;?></div>		
		</div> <!-- Close Surround -->				
		<?
	}
		
	/**
	 * Output CheckBox field.
	 */
    public static function CheckBox($data) {		
		$parent  = !empty(self::$parent) ? '['.self::$parent.']' : '';
		$name 	 = self::name($data['id'], $parent);
        $id 	 = $data['id'];
		$target  = isset($data['target_name']) ? 'data-target="'.$data['target_name'].'"' : '';
		$current  = self::chkbox_Current($data, self::$opt_name);
		
		echo self::Surround($data);
		?>	
			<input type="checkbox" id="<?php echo $id;?>" <?php echo isset($data['class']) ? 'class="'.$data['class'].'"' : ''; ?>
			name="<?php echo $name;?>" value="<?php echo $id;?>" <?php echo checked($current, true, FALSE); ?> <?php echo $target;?> >
			  <label for="<?php echo $data['id'];?>" class="noselect subtitle-label" title="<?php echo $data['label'];?>"><?php echo $data['label'];?></label>
		</div>
	<?php
	}
	
	/**
	 * Output Toggle Switch field.
	 */
    public static function ToggleSwitch($data) {		
		$parent   = !empty(self::$parent) ? '['.self::$parent.']' : '';
		$name 	  = self::name($data['id'], $parent);
        $id 	  = $data['id'];
		$target   = isset($data['target_name']) ? 'data-target="'.$data['target_name'].'"' : '';
		$current  = self::chkbox_Current($data, self::$opt_name);	
		$class    = isset($data['class']) ? ' '.$data['class'] : '';		
		$on_text  = isset($data['on_text']) ? $data['on_text'] : 'on';
		$off_text = isset($data['off_text']) ? $data['off_text'] : 'off';
		
		echo self::Surround($data);
		echo self::Label($data);
		?>	
			<label class="switch <?php echo $data['color_theme'];?>">
				 <input type="checkbox" id="<?php echo $data['id'];?>" name="<?php echo $name;?>" value="<?php echo $data['id'];?>" <?php echo checked($current, true, FALSE); ?> class="switch-input <?php echo $class;?>" <?php echo $target;?> >
				  <span class="switch-label" data-on="<?php echo $on_text;?>" data-off="<?php echo $off_text;?>"></span>
				  <span class="switch-handle"></span>
			</label>
		
		</div>	
	<?php
	}
	
	/**
	 * Output ComboBox field.
	 */
    public static function CheckBoxCombo($data) {		
		$parent  	 = !empty(self::$parent) ? '['.self::$parent.']' : '';
		$get_current = self::Current($data, self::$opt_name);
		
		echo self::Surround($data);
		
		$i = 0;
		foreach($data['list'] as $key => $value){
			
			$name = self::name($key, $parent.'['.$data['id'].']');			
			$current = isset($get_current[$key]) ? str_replace(array('true', 'false'), array(true, false), $get_current[$key]) : false;
			if(!is_array($get_current)&&$value[1]==true){
				$current = true;
			}			
			?>			
			<div class="maxgrid_ui-col" style="display: inline-block; min-width: 160px; width: auto;">
				<div class="maxgrid_ui-edit_form_line">
					<div class="maxgrid_ch-box">
						<input type="checkbox" max="<?php echo $data['max_allowed']; ?>" name="<?php echo $name; ?>" data-item-id="<?php echo $data['class']; ?>" id="<?php echo $key; ?>" class="form-field social_media_selctor <?php echo $data['class']; ?>" value="1" <?php echo checked($current, true, FALSE); ?> >
						<label for="<?php echo $key; ?>"><?php echo $value[0]; ?></label>
					</div>				
				</div>
			</div>
			<?php
			$i++;
		}
		?>
		</div>
	<?php
	}
	
	/**
	 * Output input text field.
	 */
	public static function Text($data) {		
		$parent  	= !empty(self::$parent) ? '['.self::$parent.']' : '';
		$name 		= isset($data['id']) ? ' name="'.self::name($data['id'], $parent).'"' : '';
		$id 		= isset($data['id']) ? $data['id'] : '';
		$data_id  	= isset($data['data_id']) ? ' data-value="'.self::Current($data, self::$opt_name).'"': '';
		$input_id 	= isset($data['id']) ? ' id="'.$id.'"' : '';		
		$target 	= isset($data['target_name']) ? 'data-target="'.$data['target_name'].'"' : '';
		$events  	= isset($data['events']) ? ' '.$data['events'] : '';
		$add_html  	= isset($data['add_html']) ? ' '.$data['add_html'] : '';
		
		echo self::Surround($data);
		
		$data['id'] = $id;
		echo self::Label($data);		
		?>		
		<input<?php echo $input_id.$data_id;?><?php echo $name;?> value="<?php echo self::Current($data, self::$opt_name);?>" class="maxgrid_input_field_larg<?php echo self::Classname($data);?>" <?php echo self::Styles($data). $events;?> type="text">		
		<?php echo $add_html;?>		
		</div> <!-- Close Surround -->		
		<?php
	}
	
	/**
	 * Output (ACE) CSS Editor.
	 */
	public static function CustomCSS($data) {		
		$parent  = !empty(self::$parent) ? '['.self::$parent.']' : '';
		$name = isset($data['id']) ? ' name="'.self::name($data['id'], $parent).'"' : '';
		$id = isset($data['id']) ? $data['id'] : '';
		$input_id = isset($data['id']) ? ' id="'.$id.'"' : '';
		$current = self::Current($data, self::$opt_name);
		echo self::Surround($data);		
		?>		
		<div id="custom_css_container" style="margin-top: 10px;">
			<div name="maxgrid_custom_css" id="maxgrid_custom_css" style="position: relative; height: 500px; margin: -25px -25px -30px -25px;">
				<?php
					$args = array(
						'color'   => 'grey',
						'size'    => 'small',
						'style'   => 'margin-top: -30px;',
					);
					echo maxgrid_lds_rolling_loader($args);
				?>
				<span class="maxgrid-ace-version">ace editor v 1.2.8</span>
			</div>
		</div>           
	   <textarea <?php echo $input_id;?><?php echo $name;?> class="<?php echo $id;?>" cols="45" rows="6" spellcheck="false" style="resize: none; width: 424px;"><?php echo $current!=''?$current:$data['default'];?></textarea>		
		</div> <!-- Close Surround // border: 1px solid #DFDFDF; -->		
		<?php
	}	
	
	/**
	 * Output input hidden field.
	 */
	public static function Hidden($data) {		
		$parent  = !empty(self::$parent) ? '['.self::$parent.']' : '';
		$name = isset($data['id']) ? ' name="'.self::name($data['id'], $parent).'"' : '';
		$id = isset($data['id']) ? $data['id'] : '';
		$input_id = isset($data['id']) ? ' id="'.$id.'"' : '';		
		?>		
		<input<?php echo $input_id;?><?php echo $name;?> value="<?php echo self::Current($data, self::$opt_name);?>" type="hidden">	
		<?php
	}
	
	/**
	 * Output input color field.
	 */
	public static function Color($data) {		
		$parent  	= !empty(self::$parent) ? '['.self::$parent.']' : '';
		$name 		= isset($data['id']) ? ' name="'.self::name($data['id'], $parent).'"' : '';
		$id 		= isset($data['id']) ? $data['id'] : '';
		$input_id 	= isset($data['id']) ? ' id="'.$id.'"' : '';
		$default 	= isset($data['default']) ? $data['default'] : '#ffffff';		
		$target 	= isset($data['target_name']) ? 'data-target="'.$data['target_name'].'"' : '';

		echo self::Surround($data);
		
		$data['id'] = $id;
		echo self::Label($data);		
		?>
		<input <?php echo $input_id;?><?php echo $name;?> type="text" id="maxgrid_title_color" value="<?php echo self::Current($data, self::$opt_name);?>" data-default-color="<?php echo $default;?>" class="maxgrid-colorpicker<?php echo self::Classname($data);?>" <?php echo self::Styles($data);?> />		
		</div> <!-- Close Surround -->		
		<?php
	}
	
	/**
	 * Output help tooltip.
	 */
	public static function HelpTooltip($data) {
		$data_style = isset($data['Tooltip_style']) ? ' data-style="'.$data['Tooltip_style'].'"' : '';		
		return '<div id="help-trigger" data-title="'.$data['help_Tooltip'].'" data-rel="help-tooltip"' . $data_style . '>?</div>';
	}
	
	/**
	 * Output single image upload area.
	 */
	public static function SingleImageUpload($data) {		
		$parent = !empty(self::$parent) ? '['.self::$parent.']' : '';
		$name 	= self::name($data['id'], $parent);
		$id   	= $data['id'];
		$help 	= isset($data['help_Tooltip']) ? self::HelpTooltip($data) : '';
		
		echo self::Surround($data);
		echo self::Label($data, $help);		
		?>		
		<input style="width: calc(100% - 150px); display: inline-block;" id="<?php echo $data['id'];?>" class="single-image-upload<?php if ( isset($data['class'])) : echo ' '.$data['class'];endif;?>" type="text" name="<?php echo $name;?>" size="100" value="<?php echo self::Current($data, self::$opt_name);?>" />
		<input id="single_upload_button" type="button" class="button-primary" value="Upload / Select File" placeholder="http://..." />   	   	
    	<div class="maxgrid img-wrap">
			<span class="close">&times;</span>
			<img class="img-preview" id="img-preview" src="<?php echo self::Current($data, self::$opt_name);?>">
		</div>		
		</div> <!-- Close Surround -->		
		<?		
	}
	
	/**
	 * Output select field.
	 */
	public static function Select($data) {		
		$parent  = !empty(self::$parent) ? '['.self::$parent.']' : '';
		$name 	 = self::name($data['id'], $parent);
		$id 	 = $data['id'];
		$target  = isset($data['target_name']) ? 'data-target="'.$data['target_name'].'"' : '';
		$chosen	 = (isset($data['chosen']) && $data['chosen'] == true ) ? 'chosen' : '';
		$class   = isset($data['class']) ? ' '.$data['class'] : '';
		
		echo self::Surround($data);
		echo self::Label($data);		
		?>	
		<select id="<?php echo $data['id'];?>" class="<?php echo $chosen.$class;?>" name="<?php echo $name;?>" <?php echo $target;?> data-placeholder="Your Favorite Type of Bear">
	<?php foreach($data['values'] as $id => $label ) { ?>
			<option value="<?php echo $id;?>" 
			<?php if ( $id == self::Current($data, self::$opt_name) ) echo 'selected="selected"'; ?>					
			><?php echo $label;?></option>		
	<?php } ?>
		</select>
		</div> <!-- Close Surround -->		
		<?php
	}
	
	/**
	 * Output input number field.
	 */
	public static function Number($data) {		
		$parent = !empty(self::$parent) ? '['.self::$parent.']' : '';
		$name 	= self::name($data['id'], $parent);
		$id 	= $data['id'];
		$min 	= isset($data['min']) ? $data['min'] : 0;
		$max 	= isset($data['max']) ? $data['max'] : 100000;
		$step 	= isset($data['step ']) ? $data['step '] : 1;
		$target = isset($data['target_name']) ? 'data-target="'.$data['target_name'].'"' : '';

		echo self::Surround($data);

		if ( !empty($data['label']) ) { ?>
		<label for="<?php echo $data['id'];?>" class="noselect subtitle-label" title="<?php echo $data['label'];?>"><?php echo $data['label'];?></label>
		<?php } ?>
		<input id="<?php echo $data['id'];?>" type="text" name="<?php echo $name;?>" value="<?php echo intval(self::Current($data, self::$opt_name));?>" class="maxgrid_input_field_larg numbers-only<?php echo self::Classname($data);?>" <?php echo self::Styles($data);?> min="<?php echo $min;?>" max="<?php echo $max;?>" step="<?php echo $step;?>">
		</div>
		<?php
	}	
}

class Form extends Max_Grid_Fields {
	
	/**
     * Field Constructor.
     *
     * @since Options
    */	
	public $args;
	
	/**
	 * Constructor.
	 */
	function __construct($args) {
		$this->args 	= $args;
		self::$opt_name = $args['meta_name'];
		self::$parent 	= isset($args['parent']) ? $args['parent'] : '';
    }
	
	/**
     * Field Render Function.
     *
     * Takes the vars and outputs the HTML for each field in the settings
     *
     */
	public function render() {			
		foreach($this->args as $key => $data ) {			
			$func = isset($data['type']) ? $data['type'] : false ;
			if($func) {
				$this->$func($data);
			}			
		}
	}
}