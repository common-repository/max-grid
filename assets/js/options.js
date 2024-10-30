/*-------------------------------------------------------------------------*/
/*	1. Max Grid Meta Options - JS
/*-------------------------------------------------------------------------*/

jQuery( function( $ ) {
	
	//Youtube API Checker
	$('#youtube_api_key').attr('data-value', $('#youtube_api_key').val());

	maxgrid_ytbAPIChecker($('#save_ytb_api_key'));
	
	$('body').on('keyup change', '#youtube_api_key', function () {
		if ($(this).val() === '') {
			$('#save_ytb_api_key').addClass('is-empty');
		} else {
			$('#save_ytb_api_key').removeClass('is-empty');
			$('.ytb_api_key_status').html('Press connect button!');
			$('.ytb_api_key_status').removeClass('key-valid');
			$('.ytb_api_key_status').addClass('key-invalid');
		}
	});

	$('body').on('click', '#save_ytb_api_key', function () {
		maxgrid_ytbAPIChecker($(this));
	});
	
	// Youtube ID Checker - Helper
	$('[class*="maxgrid"] .ytb-channel-id').bind("keyup paste",function(e) { 
		var key 	 = e.keyCode,
			checker  = document.getElementById('ytb-id-checker_indicator'),
			response = document.getElementById('ytb-id-checker_response'),
			ytb_id 	 = jQuery(".ytb-channel-id"),
			onPaste  = e.originalEvent.clipboardData ? e.originalEvent.clipboardData.getData('text') : null;
		
		// Skip if key pressed writing nothing
		if ( onPaste !== null || key >= 48 && key <= 57 || key >= 65 && key <= 90 || key >= 97 && key <= 122 || key === 8 || key === 46 ) {
			$('.ytb-id-checker-btn.icon-loop2').removeClass('disabled');
			checker.innerHTML = '<i class="fa fa-exclamation-triangle"></i>';
			response.innerHTML = '<div>Press Reload Button</div>';
			response.classList.add('gd-alert');
		}
	});
	
	// reCAPTCHA Site Key Checker
	$('body').on('keyup', '[class*="maxgrid"] #site_key', function () {	
		var sitekey = $(this).val();

		if ( sitekey.length < 40 ) {
			$('.g-recaptcha-checker').html('');
			return false;
		}
		$('.g-recaptcha-checker').html($('<div id="g-recaptcha" class="g-recaptcha"></div>'));			
		var captchaContainer = null;
		var loadCaptcha = function() {
			captchaContainer = grecaptcha.render('g-recaptcha', {
				'sitekey' : sitekey,
				'callback' : function(response) {
					console.log(response);
				}
			});
		};
		loadCaptcha();
	});			
			
	/*-------------------------------------------------------------------------*/
	/*	PostGrid Builder  - Restore All Row
	/*-------------------------------------------------------------------------*/

	$('body').on('click', '[class*="maxgrid"] #maxgrid_reset_all_settings', function () {
		var This 	  = $(this),
			hardReset = 'false',
			message   = $(this).attr('data-message'),
			settings  = Const.MAXGRID_SETTINGS_OPT_NAME;
		
		swal({
			text: message,
			buttons: true,
			closeOnClickOutside: false,
		})
		.then(function(willReset) {
			if (willReset) {
				if ($(this).attr('id') === 'maxgrid_reset_all_settings') {
					hardReset = 'true';
				}
				jQuery.ajax({
					type: "POST",
					url: Const.ajaxurl,
					data: {
						action: 'maxgrid_get_default_settings',
						settings: settings,
					},
					beforeSend: function (xhr) {
						$('.maxgrid-metaoptions-row.grid-builder-parent').css('opacity', '0.4');
						maxgrid_beforeSendButton(This);
						maxgrid_swalSpinner();
					},
					success: function (data) {					
						default_settings = maxgrid_strToArray(data);
						for (var key in default_settings) {
							var value = default_settings[key];
							var input = jQuery('[name="' + key + '"]');
							input = $('[name="' + settings + '[forms][' + key + ']"]');
							maxgrid_setDefaultValue(input, value, settings, key);
							input = $('[name="' + settings + '[general_options][social_list][' + key + ']"]');
							maxgrid_setDefaultValue(input, value, settings, key);
							input = $('[name="' + settings + '[general_options][' + key + ']"]');
							maxgrid_setDefaultValue(input, value, settings, key);
							input = $('[name="' + settings + '[api_options][' + key + ']"]');
							maxgrid_setDefaultValue(input, value, settings, key);
							input = $('[name="' + settings + '[track_options][' + key + ']"]');
							maxgrid_setDefaultValue(input, value, settings, key);
							input = $('[name="' + settings + '[extras_options][' + key + ']"]');
							maxgrid_setDefaultValue(input, value, settings, key);
							window.gd_ace_set_default = true;
						}

						$('#source_type').val('post');
						$('#source_type').trigger("chosen:updated");

						$('.maxgrid-metaoptions-row.grid-builder-parent').html(data);
						$('.maxgrid-metaoptions-row.grid-builder-parent').css('opacity', '1');
						$('input.fill_space').closest('li').addClass('null_object');

						$('.ajax_dl-spiner').html('');
						$('.ajax_dl-spiner').removeClass('visible');
						$('.swal-overlay').removeClass('swal-overlay--show-modal');
						
						maxgrid_ui_sortable();
					}
				});
		
			}
		});
	});	
	
	/*-------------------------------------------------------------------------*/
	/*	Easy Post Grid Builder Settings - All Settings Save Changes
	/*-------------------------------------------------------------------------*/
	
	try{
		$('body').on('click','[class*="maxgrid"] #maxgrid_settings_save_changes', function() {

			var This = $(this),
				response = $(this).closest('div').prev();
			
			jQuery.ajax({
				type : "POST",					
				 url : 'options.php',
				 data: $('#maxgrid_options_form').serialize({ checkboxesAsBools: true }),
				beforeSend:function(xhr) {
					This.prev().css('display', 'inline-block');
				},
				success: function(data) {		
					response.html('<span>Settings Saved!</span>');
					setTimeout(function(){
						$('.maxgrid_ajax_response span').addClass('hidded');
					}, 3500);
					window.save_success = true;
					This.prev().css('display', 'none');
				}
			});
		});

		/*-------------------------------------------------------------------------*/
		/*	Easy Post Grid Builder Settings - All Settings Save Changes
		/*-------------------------------------------------------------------------*/
		
		jQuery('.chosen').chosen({disable_search_threshold: 10, width: "100%"});

		// Set "Source Type:" label to source type dropdown
		maxgrid_setSourceTypeLabel();

		$('body').on('click','.tab-label', function() {
			setTimeout(function() {
				jQuery('#source_type_chosen').css('width', 'auto');
			}, 200);

		});

		$('.chosen_auto_width').chosen({disable_search_threshold: 10, width: '100%' });
		$('.chosen-drop').css({minWidth: '100%', width: 'auto'});
		// remove th from table options
		var th_width = parseInt($('#maxgrid_metaoptions_container').closest('tr').find('th').css('width').match(/\d+/)[0]),
			th_padding_left = parseInt($('#maxgrid_metaoptions_container').closest('tr').find('th').css('padding-left').match(/\d+/)[0]),
			th_padding_right = parseInt($('#maxgrid_metaoptions_container').closest('tr').find('th').css('padding-right').match(/\d+/)[0]),
			td_padding_left = parseInt($('#maxgrid_metaoptions_container').closest('td').css('padding-left').match(/\d+/)[0]);

		var marginLeft = th_width + ((th_padding_left + th_padding_right) + td_padding_left);
		$('.maxgrid-options__title.full-width').css('margin-left', marginLeft*-1 );

		var elements = document.querySelectorAll('input.extras-triggers, select.extras-triggers');

		maxgrid_extrasTriggers(elements);

		$('body').on('click change','input.extras-triggers, select.extras-triggers', function() {
			elements = $(this);
			maxgrid_extrasTriggers(elements);
		});
		/*
		// Temp system
		$('body').on('change','#styles', function() {
			if ($(this).val() !== 'left-hand-side-image' ){
				document.getElementById("triangle_separator_chbox").checked = false;
				maxgrid_toggle('maxgrid-metaoptions-row triangle_separator_target', 'disable' );
			}

			if ($(this).val() !== 'full-background-image' ){
				maxgrid_toggle('maxgrid-metaoptions-row full-background-image_target', 'disable' );
			} else {
				maxgrid_toggle('maxgrid-metaoptions-row full-background-image_target', 'enable' );
			}
		});
		if ($('#styles').val() !== 'left-hand-side-image' ){
			maxgrid_toggle('maxgrid-metaoptions-row triangle_separator_target', 'disable' );
		}
		if ($('#styles').val() !== 'full-background-image' ){
			maxgrid_toggle('maxgrid-metaoptions-row full-background-image_target', 'disable' );
		}
		//end temp
		*/
		
		// delete Cookie from cache
		var cname = "optin_newsletter_popup";

		$('body').on('click','#clear_cache', function(event) {
			event.stopPropagation();
			document.cookie = cname + '=1; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';

			var newItem = document.createElement("SPAN"),
				clear_cache_btn = document.getElementById("clear_cache"),
				newText = "Cookies successfully removed!";

			newItem.innerHTML = "progressing...";
			insertAfter(newItem, clear_cache_btn, "progressing...");
			clear_cache_btn.nextSibling.style.color = '#60686b';
			clear_cache_btn.nextSibling.style.opacity = '1';
			setTimeout(function(){
				newItem.innerHTML = newText;
				insertAfter(newItem, clear_cache_btn, newText);
			}, 1000);	

		});

		function insertAfter(el, referenceNode, newText) {		
			if ( typeof referenceNode.nextSibling.innerHTML === 'undefined' || !referenceNode.nextSibling.innerHTML){
				referenceNode.parentNode.insertBefore(el, referenceNode.nextSibling);
			} else {
				referenceNode.nextSibling.innerHTML = newText;
			}
			referenceNode.nextSibling.style.color = 'green';
		}

		document.onclick = myClickHandler;
		function myClickHandler() {
			var clear_cache_btn = document.getElementById("clear_cache");
			if (clear_cache_btn === null){return 0;}
			setTimeout(function(){
				clear_cache_btn.nextSibling.style.opacity = '0';
			}, 3000);
		}

		// Image Preview
		$('body').on('click','.bp-image_prev_container .close', function() {
			var input_id =  $(this).closest('div').attr('id'),
				prev_imgContainer = $(this).closest('div');
			prev_imgContainer.css('display', 'none');
			$('#'+input_id).val('');
		});

		try{		
			var uploadInput = document.getElementsByClassName('bp-upload-input');
			for (var i = 0; i < uploadInput.length; i++){			
				if ( uploadInput[i].value === '' ) {
					uploadInput[i].closest('div').querySelector('.bp-image_prev_container').style.display = 'none';
				} else {
					uploadInput[i].closest('div').querySelector('.bp-image_prev_container img').src = uploadInput[i].value;
				}
			}

		}catch(e){}

		/*-------------------------------------------------------------------------*/
		/*	Single Image Uploder
		/*-------------------------------------------------------------------------*/
		$('.maxgrid.img-wrap').each(function(i, obj) {

			var img = obj.lastElementChild.getAttribute('src');

			if (img === ''){
				obj.style.display = 'none';
			}

		});


		$('body').on('click','.img-wrap .close', function() {
			var idBg = $(this).closest('.img-wrap');
			idBg.css('display', 'none');

			idBg.prev().prev().val('');
		});

		/*-------------------------------------------------------------------------*/
		/*	Accordion tabs
		/*-------------------------------------------------------------------------*/
		$('body').on('click','[data-tab-target]', function() {
			var target = $(this).attr('data-tab-target');
			$(this).find('.maxgrid_toggle-button').toggleClass('tab-collapsed');
			$('#'+target).toggleClass('tab-collapsed');
		});
		
		
	
		//Clear Caches
		$('body').on('click', '#clear_caches.maxgrid-button', function () {
			var This = $(this);
			maxgrid_ClearCaches(This);
		});
	
	}catch(e) {
		console.log( 'Error: ' + e );
	}
});

function maxgrid_extrasTriggers(elements) {	
	var target_id, is_reversed, state1, state2;
	
	for (var i = 0; i < elements.length; i++) {
		target_id 	= elements[i].getAttribute('data-target');
		is_reversed = elements[i].getAttribute('data-state-reverse');
		state1 		= is_reversed ? 'disable' : 'enable';
		state2 		= is_reversed ? 'enable' : 'disable';
		
		if ( elements[i].getAttribute('type') === 'checkbox' ) {
			
			if ( elements[i].checked ) {
				maxgrid_toggle(target_id, state1);
			} else {
				maxgrid_toggle(target_id, state2);
			}
		}
		if ( elements[i].getAttribute('type') === 'radio' ) {
			if ( elements[i].checked ) {
				maxgrid_toggle(target_id+' '+elements[i].value, state1);
			} else {
				maxgrid_toggle(target_id+' '+elements[i].value, state2);
			}
		}
		
		var el_index;
		if ( elements[i].tagName === 'SELECT' ) {			
			el_index = elements[i].getAttribute('data-target').split(' ').indexOf(elements[i].value);
			
			if ( el_index !== -1 ) {				
				maxgrid_toggle('maxgrid-metaoptions-row '+elements[i].getAttribute('data-target').split(' ')[el_index], state1);			
			} else {
				var elem, a = elements[i].getAttribute('data-target').split(' ');
				for (var i = 0; i < a.length; i++) {
					maxgrid_toggle('maxgrid-metaoptions-row '+a[i], state2 );
				}
			}
		}
	}
}

//maxgrid_toggle('maxgrid-metaoptions-row styles_target', displayState)
function maxgrid_toggle(className, displayState) {
	var display, elements = document.getElementsByClassName(className);	
	for (var i = 0; i < elements.length; i++){
		display  = elements[i].getAttribute('data-display') ? elements[i].getAttribute('data-display') : 'disabled';
		if ( displayState === 'enable' ) {
			elements[i].classList.remove(display);
		} else if ( displayState === 'disable' && !elements[i].classList.contains(display) ) {
			elements[i].classList.add(display);
		}
	}
}

// Set "Source Type:" label to dropdown menu
function maxgrid_setSourceTypeLabel() {	
	if(jQuery('.maxgrid-metaoptions-row.source_type .chosen-single > span').length){
		jQuery('.maxgrid-metaoptions-row.source_type .chosen-single > span').prepend('<span>Source Type :</span>');
	}	
}

// Clear caches
function maxgrid_ClearCaches(This) {
	jQuery.ajax({
		type: "POST",
		url: Const.ajaxurl,
		data: {
			action: 'maxgrid_ytb_clear_transient',
		},
		beforeSend: function (xhr) {
			if (This && This.attr('id') !== 'save_ytb_api_key' ) {
				This.next().css('display', 'inline-block');
				jQuery('.gb-ajax-btn_response').html('');
			}
		},
		success: function (response) {
			jQuery('.clear_caches .gb-ajax-btn_response').html('Done!');
			This.next().css('display', 'none');

			setTimeout(function () {
				jQuery('.clear_caches .gb-ajax-btn_response').html('');
			}, 3800);
		}
	});
}


// Clear caches
function maxgrid_ClearCachesByPrefix(prefix) {
	jQuery.ajax({
		type: "POST",
		url: Const.ajaxurl,
		data: {
			action: 'maxgrid_ytb_clear_transient',
			prefix: prefix,
		},
		success: function (response) {
			//console.log(response);
		}
	});
}

//Youtube API Checker
function maxgrid_ytbAPIChecker(This) {
	var ytb_api_key;
	jQuery.ajax({
		type: "POST",
		url: Const.ajaxurl,
		data: {
			action: 'maxgrid_youtube_api_checker',
			api_key: jQuery('#youtube_api_key').val(),
		},
		beforeSend: function (xhr) {
			maxgrid_beforeSendButton(This);
			jQuery('.ytb_api_key_status').removeClass('key-valid').removeClass('key-invalid');
			jQuery('.ytb_api_key_status').html('Connection...');
		},
		success: function (data) {
			if (data === 'keyValid') {
				jQuery('.ytb_api_key_status').html('Connected');
				jQuery('.ytb_api_key_status').removeClass('key-invalid');
				jQuery('.ytb_api_key_status').addClass('key-valid');
				jQuery('#save_ytb_api_key').addClass('is-empty');
			} else {
				jQuery('.ytb_api_key_status').html('Not Connected');
				jQuery('.ytb_api_key_status').removeClass('key-valid');
				jQuery('.ytb_api_key_status').addClass('key-invalid');
				jQuery('#save_ytb_api_key').removeClass('is-empty');
				ytb_api_key = document.getElementById("youtube_api_key");
				if (ytb_api_key !== null) {ytb_api_key.focus();}
			}

			jQuery('.ajax_dl-spiner').html('');
			jQuery('.ajax_dl-spiner').removeClass('visible');				
		}
	});
}