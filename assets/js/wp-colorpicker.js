// Settings Panel JS
jQuery(document).ready(function() { 
	jQuery('.color-picker').on('focus', function(){
		var parent = jQuery(this).parent();
		jQuery(this).wpColorPicker()
		parent.find('.wp-color-result').click();
	}); 
});

(function( $ ) { 
    // Add Color Picker to all inputs that have 'color-field' class
    $(function() {
        $('.maxgrid-colorpicker').wpColorPicker();
    });
     
})( jQuery );