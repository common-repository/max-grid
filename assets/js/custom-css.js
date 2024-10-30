( function( global, $ ) {
    var editor,
		container = $('#custom_css_container'),
		cssArea   = $('#custom_css_area'),
        loadAce = function() {
            editor = ace.edit( 'maxgrid_custom_css' );
            global.safecss_editor = editor;
            editor.getSession().setUseWrapMode( true );
            editor.setShowPrintMargin( false );
            editor.getSession().setValue( cssArea.val() );
			editor.setTheme("ace/theme/monokai");
            editor.getSession().setMode( "ace/mode/css" );
            jQuery.fn.spin&&container.spin( false );
			editor.getSession().setUseWorker(false);
			
			$('body').on('keyup paste', '.ace_text-input', function () {
				cssArea.val(editor.getSession().getValue());
			});
			
			// After successfully save changes or pressing Reste options Button
			// If changes is it empty, set the Custom CSS Code field to thier default.
			$('body').on('click', '#maxgrid_settings_save_changes, #maxgrid_reset_all_settings', function () {
				
				var interval = setInterval(function(){
					// @var save_success - declared in : "/assets/js/options.js"
					// @var gd_ace_set_default - declared in : "/builder/js/builder.js"
					if( typeof save_success !== 'undefined' && editor.getSession().getValue() === '' || typeof gd_ace_set_default !== 'undefined' ){						
						editor.getSession().setValue(Const.MAXGRID_CSS_CODE_COMMENT);
						cssArea.val(Const.MAXGRID_CSS_CODE_COMMENT);
						clearInterval(interval);
					}
				}, 10);
			});	
        };	
	cssArea.hide();
    if ( $.browser.msie&&parseInt( $.browser.version, 10 ) <= 7 ) {
       	container.hide();
        cssArea.show();
        return false;
    } else {
        $( global ).load( loadAce );
    }
} )( this, jQuery );