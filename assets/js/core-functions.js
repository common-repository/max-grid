// Front End JS
jQuery(document).ready(function($){
	
	set_ismobile_body_style();
	
	function set_ismobile_body_style() {
		if ( maxgrid_isMobile(700) ) {
			$('body').addClass('ismobile-body');
		} else {
			$('body').removeClass('ismobile-body');
		}
	}
	$(window).resize(function() {
		set_ismobile_body_style();
	});
	
	// Data Href
	$('body').on('click','div, span, h1, h2, h3, h4, h5, li', function() {
		if ( $(this).attr('data-href-url') ) {
			var url = $(this).attr('data-href-url');
			window.open(url, '_blank');
		}
	});

	// reinitialize Nice Scroll JS
	$('body').on( 'click', '.single-modal-container, .modal-close-button', function(e) {
		e.stopPropagation();
		$(".single-modal-content").removeClass('animate-zoomin');
		$(".single-modal-content").addClass('animate-zoomout');
		$(".single-modal-container").removeClass('isvisible');
	});
	$('body').on( 'click', '.single-image', function(e) {
		e.stopPropagation();
	});

	// get Timezone Offset
	var timezone_offset_minutes = new Date().getTimezoneOffset();
	
	// Timezone difference in minutes such as 330 or -360 or 0
	timezone_offset_minutes = timezone_offset_minutes === 0 ? 0 : -timezone_offset_minutes;
	maxgrid_setCookie({cname: 'timezone_offset_minutes', value: timezone_offset_minutes});
	
});

// Sortable
function maxgrid_ui_sortable() {
	try {
		jQuery('.elements_container.maxgrid_sortable').sortable({
			cancel: '.restore, .swal-button, .locked',
			update: function(event, ui) {
				//jQuery('#builder_preview_changes').addClass('is-disabled');
			}
		});

		jQuery("#maxgrid-columns").sortable({
			cancel: '.ui-btn-general-settings, .inner-storable-container, .elements_container, .maxgrid_ui-btn, .maxgrid_ui-preset-btn, .ui-combo-btn, .restore, .swal-button',
			update: function(event, ui) {
				//jQuery('#builder_preview_changes').addClass('is-disabled');
			}
		});
	} catch (e) {
		console.log('Error: ' + e);
	}
}

// Read URL GET parameters with JavaScript
function maxgrid_GET(param) {
	var vars = {};
	window.location.href.replace( location.hash, '' ).replace( 
		/[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
		function( m, key, value ) { // callback
			vars[key] = value !== undefined ? value : '';
		}
	);

	if ( param ) {
		return vars[param] ? vars[param] : null;	
	}
	return vars;
}

// loader spinner
function maxgrid_swalSpinner(size) {
	var args = new Object();
		args.size = size ? size : 'small';								
	swal(' ', {
		buttons: false,
		closeOnClickOutside: false,
	});
	jQuery('.swal-modal').html(maxgrid_lds_rolling_loader(args));
}

// Constrict ajax loader
function maxgrid_lds_rolling_loader(args) {	
	var HTML  	 = '',
		version  = args.version ? args.version : '1',
		relative = args.relative ? ' relative' : '',
		color 	 = args.color ? ' ' + args.color : '',
		size  	 = args.size ? ' ' + args.size : '',
		form  	 = args.form ? args.form : 'circle',
		style 	 = args.style ? ' ' + args.style : '';
	relative
	if ( version === '1') {
		HTML +='<div class="lds-css' + relative + ' ng-scope'+color+size+'"'+style+'>';
		HTML +='<div class="lds-rolling">';
		HTML +='<div></div>';
		HTML +='</div>';
	} else if ( version == '2') {
		HTML +='<div class="lds-css ng-scope'+color+size+'"'+style+'>';
		HTML +='<div class="dark-loading">';
		HTML +='<div class="outer-' + form + '"></div>';
		HTML +='<div class="inner-' + form + '"></div>';
		HTML +='</div>';
		HTML +='</div>';
	}
	return HTML;
}

// Single Image Lightbox
function maxgrid_singleImageModal(element) {
	document.getElementById("single-image").src = element.getAttribute('data-href');
	jQuery(".single-modal-container").addClass('isvisible');
	jQuery(".single-modal-content").addClass('animate-zoomin');
	jQuery(".single-modal-content").removeClass('animate-zoomout');	
}

// before Send Ajax Button - spinner style
function maxgrid_beforeSendButton(This) {
	
	if ( This.length === 0 ) {
		return;
	}
	
	var ajaxSpiner = This.next();
	if ( This[0].nodeName === 'I' ) {
		This.addClass('is-downloading');
		ajaxSpiner = This;
	}
	var spinner_html = '<div class="dld-spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>';
	ajaxSpiner.html(spinner_html);
	ajaxSpiner.addClass('visible');
	jQuery('.ajax_dl-spiner .dld-spinner').css('background-color', This.css('background-color'));
	jQuery('.ajax_dl-spiner .dld-spinner').css('border-radius', This.css('border-radius'));
}

// Replace All str
function maxgrid_replaceAll(str, find, replace) {
	return str.replace(new RegExp(find, 'g'), replace);
}

// JS maxgrid_nl2br
function maxgrid_nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

// Get Cookie
function maxgrid_getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

// Set Cookie
function maxgrid_setCookie(paramObject) {
	var expireTime,	
		defaultParams = { cname: 'null', value: '1', time: 1, units: "days", path: "/"},
		params = defaultParams;

    for (var key in paramObject) {
        if (paramObject.hasOwnProperty(key)) {
            if (paramObject[key] !== undefined) {
                params[key] = paramObject[key];
            }
        }
    }
	
	if ( params.units === "days"){
		expireTime = params.time*86400;
	} else if ( params.units === "hours"){
		expireTime = params.time*3600;
	} else if ( params.units === "minutes"){
		expireTime = params.time*60;
	} else if ( params.units === "seconds" ){
		expireTime = params.time;
	}
	
	var now = new Date();
	var exp = new Date(now.getTime() + expireTime*1000);

	document.cookie = params.cname+'='+params.value+'; expires='+exp.toUTCString()+'; path='+params.path;
}

// Device detection
function maxgrid_isMobile(screen_width) {
	if ( maxgrid_getQueryVariable('is_mobile') === 'on' ){
		return true;
	}
	
	var isMobile = false; //initiate as false
	// device detection
	if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))){ isMobile = true;}
	
	if(screen_width){
		if (jQuery(window).width() < screen_width ) {
			isMobile = true;
		}
	}
	return isMobile;
}

// Encode
function maxgrid_utf8_to_b64( str ) {
  return window.btoa(unescape(encodeURIComponent( str )));
}
// Decode
function maxgrid_b64_to_utf8( str ) {
	str = typeof str !== undefined ? str.replace(/\s/g, '') : str;
  	return decodeURIComponent(escape(window.atob( str )));
}

function maxgrid_getQueryString() {
          var key = false, res = {}, itm = null;
          // get the query string without the ?
          var qs = location.search.substring(1);
          // check for the key as an argument
          if (arguments.length > 0 && arguments[0].length > 1)
            key = arguments[0];
          // make a regex pattern to grab key/value
          var pattern = /([^&=]+)=([^&]*)/g;
          // loop the items in the query string, either
          // find a match to the argument, or build an object
          // with key/value pairs
          while (itm = pattern.exec(qs)) {
            if (key !== false && decodeURIComponent(itm[1]) === key)
              return decodeURIComponent(itm[2]);
            else if (key === false)
              res[decodeURIComponent(itm[1])] = decodeURIComponent(itm[2]);
          }

          return key === false ? res : null;
}

// Get URL Variables 
function maxgrid_getQueryVariable(variable) {
       var query = window.location.search.substring(1);
       var vars = query.split("&");
       for (var i=0;i<vars.length;i++) {
               var pair = vars[i].split("=");
               if(pair[0] == variable){return pair[1];}
       }
       return(false);
}
/*
// Remove a parameter from URL
function maxgrid_removeParam(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
}

// remove querystring
function maxgrid_removeQString(key) {
	var urlValue=document.location.href;
	
	//Get query string value
	var searchUrl=location.search;
	
	if(key!=="") {
		var oldValue = maxgrid_getParameterByName(key);
		removeVal=key+"="+oldValue;
		if(searchUrl.indexOf('?'+removeVal+'&')!= "-1") {
			urlValue=urlValue.replace('?'+removeVal+'&','?');
		}
		else if(searchUrl.indexOf('&'+removeVal+'&')!= "-1") {
			urlValue=urlValue.replace('&'+removeVal+'&','&');
		}
		else if(searchUrl.indexOf('?'+removeVal)!= "-1") {
			urlValue=urlValue.replace('?'+removeVal,'');
		}
		else if(searchUrl.indexOf('&'+removeVal)!= "-1") {
			urlValue=urlValue.replace('&'+removeVal,'');
		}
	}
	else {
		var searchUrl=location.search;
		urlValue=urlValue.replace(searchUrl,'');
	}
	history.pushState({state:1, rand: Math.random()}, '', urlValue);
}

function maxgrid_addNewStyle(newStyle) {
    var styleElement = document.getElementById('styles_js');
    if (!styleElement) {
        styleElement = document.createElement('style');
        styleElement.type = 'text/css';
        styleElement.id = 'styles_js';
        document.getElementsByTagName('head')[0].appendChild(styleElement);
    }
    styleElement.appendChild(document.createTextNode(newStyle));
}
*/
/*-------------------------------------------------------------------------*/
/*	Set last active options tab ID to session
/*-------------------------------------------------------------------------*/

jQuery(document).ready(function ($) {	
	var twitterContainer = document.getElementById('twitter_single_container');
	if($(twitterContainer).length) {
		twttr.ready(function (twttr) {
			twttr.widgets.createFollowButton(
			twitterContainer.getAttribute('data-twitter-id'),
			twitterContainer,
				{
					size: Const.twttrSize,
					count: Const.twttrCount,
					showScreenName: Const.twttrScreenName
				}
			);
		});
	}
					   
	$('body').on('click', '[data-bp-option] input.tab-input', function () {
		var opt_id = $('[data-bp-option]').attr('data-bp-option'),
			tab_id = $(this).attr('id');
		jQuery.ajax({
			type: "POST",
			url: Const.ajaxurl,
			data: {
				action: "maxgrid_set_session",
				session_name: opt_id+'-tabs',
				session_value: tab_id
			}
		});
	});
	
	/*-------------------------------------------------------------------------*/
	/*	Description & Comments Tabs	
	/*-------------------------------------------------------------------------*/
	$('body').on('click','.tab-link', function() {		
		maxgrid_setCookie({cname: 'current_lightbox-tab', value: $(this).attr('data-tab'), time: 20, units: "seconds"});
		window.tab_name = maxgrid_getCookie("current_lightbox-tab");
		setTimeout(function(){
			$("#reach_content_outer").getNiceScroll().show().resize();
		}, 70);
	});
	
	/*-------------------------------------------------------------------------*/
	/*	Auto Download onload page	
	/*-------------------------------------------------------------------------*/
	
	if( maxgrid_GET('onclickbtn')){
		$('#maxgrid_download_single[data-post-id="'+maxgrid_GET('onclickbtn')+'"]').click();
	}
	
});

/*-------------------------------------------------------------------------*/
/*	Color Corection
/*-------------------------------------------------------------------------*/
// Convert serialized string To Array
function maxgrid_strToArray(str) {	
	var arr = str.split('&');
	var obj = {};
	for (var i = 0; i < arr.length; i++) {
		var singleArr = arr[i].trim().split('=');
		var name = singleArr[0];
		var value = singleArr[1];
		if (obj[name] === undefined) {
			obj[name] = value;
		}
	}
	return obj;
}

// Reset settings fields to their default values
function maxgrid_setDefaultValue(input, value, settings, key) {
	input.val(value);	
	if (input.attr('type') === 'checkbox') {		
		if (value === 'true') {
			input.prop("checked", true);
			
			// Featured Row							
			if (input.attr('class') !== undefined && input.attr('class').indexOf('social_media_selctor') !== -1) {
				input[0].removeAttribute('disabled');
			}

		} else {
			input.prop("checked", false);

			// Featured Row
			if (input.attr('class') !== undefined && input.attr('class').indexOf('social_media_selctor') !== -1) {
				input.attr('disabled', 'disabled');
			}
		}
	}

	if (input.attr('type') === 'radio') {
		jQuery('[name="' + key + '"]#' + value).prop("checked", value);
	}

	if (input.attr('class') !== undefined && input.attr('class').indexOf('maxgrid-colorpicker') > -1) {
		try {
			input.keyup();
		} catch(e){
			console.log( 'Error: ' + e );
		}
	}
	
	// If Select		
	input.trigger("chosen:updated");

	// Block Row
	if (input.attr('class') !== undefined && input.attr('class') === 'fontselect') {
		jQuery('.font-select :first-child span').html(value);
		jQuery('.font-select :first-child').removeAttr("style");
		jQuery('.font-select :first-child').css('font-weight', 400);
	}
	
	// Featured Row
	if ( settings === 'featured_row') {
		$('#featured_row_form .hidden_opt_element').attr('data-current', 'none');
	}
	
	// Average Rating Preview
	if (settings === 'average_rating_row' && key === 'description') {
		jQuery('.maxgrid_averageRatingPreview span').html('34 ' + value);
	}
}
/*	
// Decrease Brightness
function maxgrid_decreaseBrightness(hex, percent){
    var r = parseInt(hex.substr(1, 2), 16),
        g = parseInt(hex.substr(3, 2), 16),
        b = parseInt(hex.substr(5, 2), 16);

   return '#' +
       ((0|(1<<8) + r * (100 - percent) / 100).toString(16)).substr(1) +
       ((0|(1<<8) + g * (100 - percent) / 100).toString(16)).substr(1) +
       ((0|(1<<8) + b * (100 - percent) / 100).toString(16)).substr(1);
}
*/
// RGB Color To Hexadecimal Colors
function maxgrid_rgb2hex(rgb) {
	rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
	return (rgb && rgb.length === 4) ? "#" +
		("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
		("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
		("0" + parseInt(rgb[3], 10).toString(16)).slice(-2) : '';
}

// Ajax - Set count to custom postmeta type counter
function maxgrid_ajaxSetMetaCount(args) {
	if(args.meta_key===''){
		return;
	}
	var This  	  = args.This,
		post_id   = args.post_id,
		meta_key  = args.meta_key,
		once 	  = args.once ? args.once : '',
		offset 	  = args.offset ? args.offset : 0,
		response  = args.response,
		is_single = args.is_single ? args.is_single : null;
	
	jQuery.ajax({
			type : "POST",
			url : Const.ajaxurl,
			data : {
				action : 'live_counter_update',
				post_id    : post_id,
				meta_key  : meta_key,
				once  : once,
			},
			success:function(data) {
				if ( meta_key === 'downloads_count') {
					var a = document.createElement('A');
					a.href = response;
					a.download = response.substr(response.lastIndexOf('/') + 1);
					document.body.appendChild(a);
					a.click();
					document.body.removeChild(a);					
					setTimeout(function() {
						jQuery('.ajax_dl-spiner').removeClass('visible');						
						jQuery('.ajax_dl-spiner').html('');
					}, 200);
				}
				
				if ( meta_key === 'downloads_count' && data !== "already") {
					setTimeout(function() {
						if ( This[0].nodeName === 'I' ) {							
							This.closest('.block-grid-container').find('.f_dld-count').html(data);
						} else {
							This.closest('.block-grid-container').find('.dl-count').html(data);
						}
						if ( This.attr('data-single') !== undefined ) {							
							jQuery('.ajax-dl-counter').html(data);
						}
						if ( This.attr('data-lightbox') !== undefined ) {							
							This.closest('.maxgrid-parent').find('.dl-count').html(data);
						}						
					}, 200);
				}
			}
		});
}