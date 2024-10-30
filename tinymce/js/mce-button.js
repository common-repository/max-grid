/**
 * Max Grid - tinyMCE Plugin & Visual Composer
 * Visual Shortcode Generator
 *
 * @since 1.0.0
 * 
 */
var Tagfilter = 'off';

jQuery(function($) {	
(function() {
	tinymce.PluginManager.add('maxgrid', function( editor, url ) {		
		
		// Shortcode tag name
		var sh_tag = 'maxgrid';
		var premRequire = !isTemplatesOpened ? 'mxg-premium-required' : '';
		
		
		// When the TinyMCE instance is ready
		jQuery( document ).on( 'tinymce-editor-init', function( event, editor ) {
			
			var newContent, content, gridBody = editor.dom.get('maxgrid_mce_input');
			
			if (gridBody === null) {
				getLastFeatured();
				return 0;
			}
			
			content = editor.getContent();			
			newContent = constrictContent(content);
			editor.setContent(newContent);
			
			editor.dom.setAttribs(gridBody.parentNode, {'class' : 'maxgrid-mce-container disable-select', 'contentEditable' : 'false'});
			
			initGridBlock();
			getLastFeatured();
		});
		
		// Run Youtube ID Checker
		jQuery('body').on('click', '.ytb-id-checker-btn.icon-loop2:not(.vc-checker)', function() {
			maxgrid_ytbIDChecker(document.getElementById('youtube_selector').value);			
		});
		
		jQuery('body').on('change', '[name="incl_excl"]', function() {
			var gridContainer = document.querySelectorAll('[name*="exclude"]');
			var args = {};
			args.include = 'include for';
			args.exclude = 'exclude from';
			
			for (var i = 0; i < gridContainer.length; i++) {
				$(gridContainer[i]).next().html($(gridContainer[i]).next().html().replace('exclude from', args[this.value]).replace('include for', args[this.value]));
			}
		});
		
		// Re-constrict Content
		function constrictContent(content) {
			// Load the HTML string as XML, wrap it in a <root> tag as a XML			
			var doc = new DOMParser().parseFromString('<root>' + content + '</root>', 'text/xml');
			
			// Remove all div elements
			var div = doc.querySelectorAll('div');		
			for (var i = 0; i < div.length; i++) {
				div[i].parentNode.removeChild(div[i]);
			}
			
			// Return the modified document's HTML content
			return doc.documentElement.innerHTML;
			
		}
			
		/*
		 * On tinymce editor init
		 * Get recent post thumbnail for post, custom post download and product
		 * add data attribute "data-lp-featured" to body tag 
		 *
		 */
		function getLastFeatured(){
			jQuery.ajax({
				type : "POST",
				 url : ajaxurl,
				data : {
					action : "get_recent_post_thumbnail",
				},
				success: function(data){
					$('body').attr('data-lp-featured', maxgrid_utf8_to_b64(data));
					initGridBlock(data);
				}
			});
		}
		
		// Get youtube channel custom URL
		function get_ytb_channel_custom_url(channel_id){
			jQuery.ajax({
				type : "POST",
				 url : ajaxurl,
				data : {
					action : "get_ytb_channel_custom_url",
					channel_id: channel_id,
				},
				beforeSend: function (xhr) {
					document.getElementById("youtube_id").style.color = '#adb0b3';
					document.getElementById("youtube_id").readOnly = true;
				},
				success: function(data) {
					document.getElementById("youtube_id").style.color = '#32373c';
					document.getElementById("youtube_id").readOnly = false;
					document.getElementById("youtube_id").value = data;
				}
			});
		}
		

		// Inisialize Grid block
		function initGridBlock(data) {
			
			var newData = data, title, gridID, imgID, imgURL, dflt_featured, block_preview, imgPreview, post_type,
				gridBody = editor.dom.get('maxgrid_mce_input');		

			if (editor === null || gridBody === null ) {
				return 0;
			}

			setTimeout(function () {
				
				var child = editor.dom.select('.maxgrid_mce_input');
				var parent = $(child).parent();
				
				parent.each( function() {
					var div 	   = document.createElement("div"),
						edit 	   = document.createElement("div"),
						remove 	   = document.createElement("div");
					
					gridBody = $(this).find('.maxgrid_mce_input')[0];
					
					// block Image Preview
					title  = gridBody.attributes['data-sh-attr'].value;
					title  = window.decodeURIComponent(title);
					gridID = getAttr(title,'id');
					
					editor.dom.setAttribs(div, {'class' : 'maxgrid-mce-btn-group disable-select', 'contentEditable' : 'false', 'spellcheck' : 'false'});
					editor.dom.setAttribs(edit, {'class' : 'icon-pencil disable-select', 'contentEditable' : 'false', 'spellcheck' : 'false'});
					editor.dom.setAttribs(remove, {'class' : 'icon-cross disable-select', 'contentEditable' : 'false', 'spellcheck' : 'false' });

					div.appendChild(edit);
					div.appendChild(remove);
					
					editor.dom.setAttribs(this, {'id' : 'maxgridBody', 'class' : 'maxgrid-mce-container disable-select id-'+gridID, 'data-id' : gridID, 'contenteditable' : 'false'});
	
					var haveDiv = false, havePtype;
					if (this.hasChildNodes()) {
						var children = this.childNodes;
						for (var i = 0; i < children.length; i++) {							
							if (children[i].nodeName === 'DIV') {
								haveDiv = true;
							}
							if (children[i].nodeName === 'DIV' && children[i].className.indexOf('maxgrid-mce-ptype') > -1 ) {					
								havePtype = true;
							}
						}
					}

					if(haveDiv===false){
						this.appendChild(div);
					}
					
					if(!havePtype){
						grid_summary_details(gridBody);
					}
					
					post_type = exGetAttr(gridBody, 'post_type');					
					dflt_featured = MAXGRID_ABSURL+'/includes/css/img/no-image.svg';

					if(!data){
						newData = $('body').attr('data-lp-featured');
						newData = newData ?  maxgrid_b64_to_utf8(newData) : '';
					}
					
					block_preview = $(this).find('.maxgrid-mce-block-preview')[0];

					if(!block_preview) {
						imgPreview = document.createElement("div");
						editor.dom.setAttribs(imgPreview, {'id' : 'maxgrid-block-preview', 'class' : 'maxgrid-mce-block-preview disable-select', 'contentEditable' : 'false', 'spellcheck' : 'false'});
						
						this.appendChild(imgPreview);
					}
					
					if (block_preview !== undefined || data !== undefined ) {
						imgID  = getAttr(title,'last_video');
						if(post_type==='post'){
							imgURL = newData.split(',')[0];
						} else if (post_type==='download'){
							imgURL = newData.split(',')[1]; 
						} else if (post_type==='product'){
							imgURL = newData.split(',')[2];  
						} else if (post_type==='youtube_stream'){
							imgURL = 'https://img.youtube.com/vi/'+imgID+'/mqdefault.jpg';   
						}	

						imgURL = imgURL !== undefined && imgURL.indexOf('http') > -1 ? imgURL : dflt_featured;
						if (block_preview !== undefined ) {
							block_preview.style.background= 'url('+imgURL+') 50% 0';
						}
					}
				});
			}, 10);
		}

		// Helper functions 
		function getAttr(s, n) {
			n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
			return n ? window.decodeURIComponent(n[1]) : '';
		}

		function exGetAttr(Body, name){
			var gridBody = Body !== null ? Body : editor.dom.get('maxgrid_mce_input');
			if (gridBody === null) {return 0;}
			var title  	 = gridBody.attributes['data-sh-attr'].value;
				title    = window.decodeURIComponent(title);
			return getAttr(title,name);
		}

		// Constrict the grid summary details
		function grid_summary_details(gridBody) {
			var pType, preset,
				title  = gridBody.attributes['data-sh-attr'].value;
				title  = window.decodeURIComponent(title);
			
			var div   = document.createElement("div"),
				block = document.createElement("div"),
				pType = getAttr(title,'post_type');
			var divContent = document.createTextNode(MAXGRID_PLUGIN_LABEL_NAME+' - block');
			block.appendChild(divContent);
			editor.dom.setAttribs(div, {'id' : 'mce-ptype', 'class' : 'maxgrid-mce-ptype disable-select '+pType, 'contentEditable' : 'false'});
			
			preset = getAttr(title,'preset').replace(/post_default|download_default|product_default|youtube_stream_default/g,'Default Template');
			
			div.appendChild(block);
			div.appendChild(pType !== 'youtube_stream' ? document.createTextNode('Type: ' + pType.toCapitalize()) : document.createTextNode('Type: Youtube ' +getAttr(title,'ytb_type').toCapitalize()));
			div.appendChild(document.createElement("div"));
			div.appendChild(document.createTextNode('Preset: '+ preset.toCapitalize()));
			div.appendChild(document.createElement("div"));
			div.appendChild(document.createTextNode('Masonry: '+getAttr(title,'masonry')));
			div.appendChild(document.createElement("div"));
			div.appendChild(document.createTextNode('Total post: '+getAttr(title,'total')));
			
			gridBody.parentNode.appendChild(div);
		}
		
		String.prototype.toCapitalize = function() {
			//Replace all underscores then Capitalize words
			return this.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
		};	
		
		// Shortcode replace - shortcode to html node
		function replaceShortcodes( content ) {
			return content.replace( /\[maxgrid([^\]]*)\]/g, function( all, attr) {
				return html( 'maxgrid_mce_input', attr);
			});
		}
		
		// Replacement html element
		function html( cls, data) {
			var post_type = getAttr(data,'post_type');
			data = window.encodeURIComponent( data );						
			return '<input id="maxgrid_mce_input" class="mceItem ' + cls + ' ' + post_type + '" ' + 'data-sh-attr="' + data + '" data-mce-resize="false" data-mce-placeholder="1" spellcheck="false" readonly>';
		}

		// Restore Shortcode - html node to shortcode
		function restoreShortcodes( content ) {
			var n_content = content.replace(/(<p[^>]+?>|<p>|<\/p>)/img, "");
			return n_content.replace( /(?:<p(?: [^>]+)?>)*(<input [^>]+>)(?:<\/p>)*/g, function( match, input ) {				
				var data = getAttr( input, 'data-sh-attr' );
				if ( data ) {
					return '<p>[' + sh_tag + data + ']</p>';
					
				}				
				return match;
			});
		}
		
		// Popup Pannel - post type selctor 
		$('body').on('change', '#gd_ptype_sel', function() {
			var expr = $(this).val();
			GetExclCats(expr);			
			$('#mce-ytb_keyword_filter').addClass('mce-disabled-field');			
			if ( ConstMCE.maxgrid_ytb ) {
				maxgrid_ytbIDChecker('channel');
			}
		});
		
		// On change post type
		function ptype_onchange(post_type){
			
			if ( post_type === 'youtube_stream' ) {
				$('#MCEpostcatHelper').css('display', 'none');
				$('#grid_dfltview').css('display', 'none');
				$('#YoutubeOptions').css('display', 'block');
				$('#fullcontent').closest('.mce-container').css('display', 'none');
				$('#grid_ribbon').closest('.mce-container').css('display', 'none');
				$('#list_ppp').closest('.mce-container').css('display', 'none');			
				$("#grid_pagination_select option[value='numeric_pagination']").css('display', 'none');				
			} else {
				$('#MCEpostcatHelper').css('display', 'block');
				$('#grid_dfltview').css('display', 'block');
				$('#YoutubeOptions').css('display', 'none');
				$('#fullcontent').closest('.mce-container').css('display', 'block');
				$('#grid_ribbon').closest('.mce-container').css('display', 'block');
				$('#list_ppp').closest('.mce-container').css('display', 'block');			
				$("#grid_pagination_select option[value='numeric_pagination']").css('display', 'block');
			}
			
			if ( ConstMCE.maxgrid_ytb ) {
				$('#gd_ptype_sel option[value="youtube_stream"]').css('display', 'block');
			} else {
				$('#gd_ptype_sel option[value="youtube_stream"]').css('display', 'none');
			}
			
			if ( ConstMCE.maxgrid_woo ) {
				$('#gd_ptype_sel option[value="product"]').css('display', 'block');
			} else {
				$('#gd_ptype_sel option[value="product"]').css('display', 'none');
			}
			
			if ( ConstMCE.maxgrid_download ) {
				$('#gd_ptype_sel option[value="download"]').css('display', 'block');
			} else {
				$('#gd_ptype_sel option[value="download"]').css('display', 'none');
			}
			
			if ( !ConstMCE.maxgrid_ytb && !ConstMCE.maxgrid_woo && !ConstMCE.maxgrid_download ) {
				$('#grid_post_type').css('display', 'none');
			}
			
		}
		
		
		$('body').off('change', '#allPostCats, #gd_ptype_sel');
		
		// Presets selector		
		$('body').on('change', '#allPostCats, #gd_ptype_sel', function() {
			setTimeout(function () {
				mce_items_shift_down();
			}, 100);
		});
		
		// Youtube ID Checker - Helper
		$('body').on('keyup paste', '.ytb-channel-id', function(e) {
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

			if ( ytb_id && ytb_id.attr('data-id').toLowerCase() === ytb_id.val().toLowerCase() ) {
				checker.innerHTML = '<i class="fa fa-check"></i>';			
				$('.ytb-id-checker-btn.icon-loop2').addClass('disabled');
				var total = jQuery(".ytb-channel-id").attr('data-total'),
					plural = parseInt(total) > 1 ? 's' : '';
				response.innerHTML = '<div>'+total+'</div><div>video'+plural+' found.</div>';
				response.classList.remove('gd-alert');
			}

		});
			
		// Dynamically auto overlay all tinyMCE modal blocks
		function mce_items_shift_down() {
			
			var previous = null, shift_value, height=0, index = 0, prev_height,
				DynamicBlock = $("#MCEpostcatHelper"),
				parent = DynamicBlock.closest('div');
			
			parent.closest('div').children().each(function() {
				
				height += parseInt($(this).css('height').match(/\d+/));
				if( this.style.display === 'none'){
					height -= parseInt($(this).css('height').match(/\d+/));
				}
				
				if ( this.id === DynamicBlock[0].id ) {
					shift_value = $(this).find('ul.chosen-choices').outerHeight() - 29;
				}
				
				if ( this.nodeName === 'INPUT' ) {return true;}
				
				if (previous) {
					
					prev_height =  previous.scrollHeight;

					if ( previous.id === 'YoutubeOptions') {
						prev_height = previous.scrollHeight - 19;
					}
					
					if( previous.style.display === 'none'){
						$(this).css('top', parseInt(previous.style.top.match(/\d+/)) );
					} else {
						$(this).css('top', parseInt(previous.style.top.match(/\d+/)) + prev_height + 10 );
					}
				}
				index += 10;
				previous = this;				
			});
			
			parent.css('height', height + index + shift_value);
			parent.parent().css('height', height + index + shift_value);
		}
						
		//add button
		editor.addButton('maxgrid', {
			icon: 'maxgrid dashicons-grid-view',
			tooltip: MAXGRID_PLUGIN_LABEL_NAME+' Visual Shortcode',
			onclick: function() {				 
				editor.execCommand('maxgrid_popup','',{
					post_type 		: 'post',
					preset    		: 'post_default',
					incl_excl_cats	: 'exclude',
					exclude   		: '',
					ytb_type   		: 'channel',
					ytb_id   		: '',
					ytb_tag   		: '',
					retrieve_hd   	: '',
					ytb_banner   	: 'on',
					ytb_dflt_filter : 'relevance',
					dflt_mode 	    : 'grid',
					items_per_row 	: '3',
					pagination 		: 'load_more_button',
					full_content 	: 'off',
					masonry 		: 'on',
					orderby_ftr   	: 'on',
					order_ftr   	: 'on',
					view_ftr   		: 'on',
					tax_ftr   		: 'on',
					tag_ftr   		: 'on',
					ribbon 			: 'on',
					grid_ppp		: '9',
					list_ppp		: '5',
					id 	    		: '',
					total			: '0',
					last_video		: '',
				});
			}
		});

		// Youtube types options switch
		$('body').on('change', '#youtube_selector', function() {
			ytbTypeSwapOptions($(this).val());
		});
		
		// The youtube options switch function
		function ytbTypeSwapOptions(ytb_type){
			if(ytb_type === 'channel') {
				$('#youtube_id').attr('placeholder', 'Enter YouTube Channel ID.');
				$('#dropdownCheckList.ytb-options').removeClass('mce-disabled-field');
			} else {
				$('#youtube_id').attr('placeholder', 'Enter YouTube Playlist ID.');
				$('.ytb-id-checker_response').css('margin-top', '-57px');
				$('#dropdownCheckList.ytb-options').addClass('mce-disabled-field');
				
			}
		}
				
		// Set post catégories
		function GetExclCats(ptype) {
			var pLabel 	 = htmlLabelConstrict('Template', true),
				exLabel  = htmlLabelConstrict('', true),
				postCat  = document.getElementById('MCEpostcatHelper'),
				presets  = document.getElementById('post_presets');
			switch (ptype) {
				case 'post':
					presets.innerHTML = pLabel+postPresetsList+'</div>';
					postCat.innerHTML = exLabel+'<div class="mce-postcat-helper">' + wpCat + "</div></div>";
					break;
				case 'download':
					presets.innerHTML = pLabel+dldPresetsList+'</div>';
					postCat.innerHTML = exLabel+'<div class="mce-postcat-helper">' + maxgridCat + "</div></div>";
					break;
				case 'product':
					presets.innerHTML = pLabel+wooPresetsList+'</div>';
					postCat.innerHTML = exLabel+'<div class="mce-postcat-helper">' + wooCat + "</div></div>";
					break;
				case 'youtube_stream':
					presets.innerHTML = pLabel+ytbPresetsList+'</div>';
					$('#allPostCats').val('').trigger("chosen:updated");
					break;
				default:
					// None!
			}
			ptype_onchange(ptype);
			//Reload chosen plugin
			chosen_reload(ptype);
		}
		
		//Reload chosen plugin function
		function chosen_reload(ptype) {
			if (ptype === 'youtube_stream') {
				$('#MCEpostcatHelper').addClass('mce-disabled-field');
				$('#YoutubeOptions').removeClass('mce-disabled-field');
			} else {
				$('#MCEpostcatHelper').removeClass('mce-disabled-field');
				$('#YoutubeOptions').addClass('mce-disabled-field');
			}
			jQuery('.chosen').chosen({width: "100%"});
			
		}
		
		function get_ytb_options(){
			var ytbParms 		 = htmlLabelConstrict('Type', true),
				//ytbOPT   		 = document.getElementById('YoutubeOptions'),
				ytb_opt_header   = '<div id="dropdownCheckList" class="dropdown-check-list ytb-options noselect" tabindex="100"><span class="anchor">Extras Options :</span>',
				mceDivider 		 = '<div class="mce-divider"></div>',
				tag_filter_html  = Tagfilter === 'on' ? '<span class="keyword-filter"><label><input id="youtube_tag_filter_check" value="1" onclick="maxgrid_mceKeywordFilter(this);" type="checkbox">Keyword Filter</label> <input type="text" id="mce-ytb_keyword_filter" class="mce-id-field" value="" data-triger="filter" placeholder="Enter keyword." style="margin-left: 0 !important; width: 35%;"/></span>'+mceDivider : '',
				retrieve_hd_html = '<label class="checkbox-inline"><input id="youtube_retrieve_hd" value="1" onclick="maxgrid_mceYtbRetrieveHD(this);" type="checkbox">Retrieve HD Videos</label>',
				ytb_banner_html  = '<label class="checkbox-inline" style="margin-bottom: 10px;"><input id="youtube_channel_banner" value="1" onclick="maxgrid_mceYtbBanner(this);" type="checkbox" checked>Youtube Banner</label>',
				dfltFilterHTML 	 = '<span class="ytb-filter-label">Default Filter :</span><select id="ytb_dflt_filter" onchange="maxgrid_ytbFilterChange(this)" class="maxgrid-tinymce_select"><option value="relevance">Relevance</option><option value="date" selected>Upload date</option><option value="viewCount">Views count</option><option value="rating">Rating</option></select>',
				dflt_ch_id 		 = exGetAttr(null, 'ytb_id') ? exGetAttr(null, 'ytb_id') : default_channel_id;
			
			var HTML = ytbParms+'<div class="mce-ytbopt-helper ytb-box">';
			HTML += '<div style="display: flex;"><div class="maxgrid-select-style"><select id="youtube_selector" onchange="maxgrid_ytbIDChecker(this)" data-triger="filter" class="maxgrid-tinymce_select"><option value="channel">Channel</option><option value="playlist">Playlist</option></select></div><input type="text" id="youtube_id" class="mce-id-field ytb-channel-id" value="'+dflt_ch_id+'" data-triger="id" placeholder="Enter YouTube Channel ID."/><span class="ytb-id-checker-btn icon-loop2"></span><div id="ytb-id-checker_indicator"><i class="fa fa-check grey"></i></div></div>';
			HTML += ytb_opt_header;
			HTML += '<div class="channel-search-filter">';
			HTML += '<div class="top-lvl">'+tag_filter_html+retrieve_hd_html+ytb_banner_html+'</div>'+mceDivider+'<div class="bot-lvl maxgrid-select-style">'+dfltFilterHTML+'</div></div>';
			HTML += '</div></div>';
			HTML += '<div id="ytb-id-checker_response" class="maxgrid-secondary"></div>';
			HTML += '</div>'; // end ytb_opt_header div
			return HTML;
		}
		
		// Grid Filter
		function get_filter_options(){
			
			var ftrParms 	 	 = htmlLabelConstrict('Filter', true),
				ftr_opt_header	 = '<div id="dropdownCheckList" class="dropdown-check-list ftr-options noselect" tabindex="100"><span class="anchor">Options :</span>',
				orderby_html = '<label class="checkbox-inline"><input id="mxg_orderby_opt" data-filter="orderby" value="1" onclick="maxgrid_mceFilter(this);" type="checkbox">Order by</label>',
				order_html = '<label class="checkbox-inline"><input id="mxg_order_opt" data-filter="order" value="1" onclick="maxgrid_mceFilter(this);" type="checkbox">ASC / DESC Toggle</label>',
				view_html = '<label class="checkbox-inline"><input id="mxg_view_opt" data-filter="view" value="1" onclick="maxgrid_mceFilter(this);" type="checkbox">Grid / List Toggle</label>',
				tax_html = '<label class="checkbox-inline '+premRequire+'"><input id="mxg_tax_opt" data-filter="tax" value="1" onclick="maxgrid_mceFilter(this);" type="checkbox">Query post by categries</label>',
				tag_html = '<label class="checkbox-inline '+premRequire+'"><input id="mxg_tag_opt" data-filter="tag" value="1" onclick="maxgrid_mceFilter(this);" type="checkbox">Query post by tags</label>';
			
			var HTML = ftrParms+'<div class="mce-ftropt-helper ftr-box">';
			HTML += ftr_opt_header;
			HTML += '<div class="grid-filter-options">';
			HTML += orderby_html+order_html+view_html+tax_html+tag_html;
			HTML += '</div>'; // end grid-filter-options
			HTML += '</div>'; // end dropdownCheckList
			HTML += '</div>'; // end mce-ftropt-helper
			HTML += '</div>'; // end main container
			
			return HTML;
		}
		
		// Label Constrictor
		function htmlLabelConstrict(label, no_arrow) {			
			var Class = ' class="maxgrid-select-style"';
			if(no_arrow){
				Class = '';
			}
			return '<div'+Class+' style="display: flex;"><div id="maxgrid-tinymce_label">'+label+'</div>';
		}
		
		// DoropDown List Constrictor - <selct></selct>
		function htmlSelectConstrict(select, obj, dflt) {
			var PremRequirNote;
			for( val in obj.labels ) {
				PremRequirNote = '';
				var opt  = document.createElement('option');
				if(obj.values){
					opt.value = obj.values[val];
				} else {
					opt.value = obj.labels[val].replace(/ /g,"_").toLowerCase();
				}
				
				var premOPT = ['infinite_scroll', 'direction_aware_hover'];
				if ( premOPT.indexOf( obj.values[val] ) > -1 && !isTemplatesOpened ) {
					opt.setAttribute( 'disabled', 'disabled' );
					opt.setAttribute( 'class', 'mxg-premium-required' );
					PremRequirNote = ' (Premium)'
				} 
				
				opt.innerHTML = obj.labels[val]+PremRequirNote;
				select.appendChild(opt);
			};
			select.value=dflt;	
			document.getElementById(select.getAttribute('data-target')).value = dflt;
		}
	
		//add popup
		editor.addCommand('maxgrid_popup', function(ui, v) {
			
			//setup defaults
			var post_type 		= v.post_type ? v.post_type : 'post',
				preset 	  		= v.preset ? v.preset : '',
				incl_excl_cats 	= v.incl_excl_cats ? v.incl_excl_cats : 'exclude',
				exclude   		= v.exclude ? v.exclude : '',
				ytb_type   		= v.ytb_type ? v.ytb_type : 'channel',
				ytb_id   		= v.ytb_id ? v.ytb_id : default_channel_id,
				ytb_tag   		= v.ytb_tag ? v.ytb_tag : '',
				retrieve_hd   	= v.retrieve_hd ? v.retrieve_hd : '',
				ytb_banner   	= v.ytb_banner ? v.ytb_banner : 'on',
				ytb_dflt_filter = v.ytb_dflt_filter ? v.ytb_dflt_filter : 'relevance',
				dflt_mode   	= v.dflt_mode ? v.dflt_mode : 'grid',
				items_per_row   = v.items_per_row ? v.items_per_row : '3',
				pagination   	= v.pagination ? v.pagination : 'load_more_button',
				full_content   	= v.full_content ? v.full_content : 'off',
				masonry   		= v.masonry ? v.masonry : 'on',
				ribbon   		= v.ribbon ? v.ribbon : 'on',
				orderby_ftr   	= v.orderby_ftr ? v.orderby_ftr : '',
				order_ftr   	= v.order_ftr ? v.order_ftr : '',
				view_ftr   		= v.view_ftr ? v.view_ftr : '',
				tax_ftr   		= v.tax_ftr ? v.tax_ftr : '',
				tag_ftr   		= v.tag_ftr ? v.tag_ftr : '',
				grid_ppp   		= v.grid_ppp ? v.grid_ppp : '9',
				list_ppp   		= v.list_ppp ? v.list_ppp : '5';
				
			var InsertButtonName = window.isEditMode === true ? 'Save' : 'Insert';
			window.isEditMode = null;
			var lolo = null;
			editor.windowManager.open( {
				autoScroll: true,
				width: 670,
				height: 500,
				classes: 'maxgrid-sc-popup',
				title: MAXGRID_PLUGIN_LABEL_NAME+" Visual Shortcode",				
				body: [
					{type: 'label', name: 'post_type', multiline: true, style: 'height: 30px;', id: 'grid_post_type', classes: 'grid_post_type',
					 	onPostRender : function() {this.getEl().innerHTML =  htmlLabelConstrict('Source Type')+'<select id="gd_ptype_sel" onchange="maxgrid_selectOnchange(this)" data-target="pt_val" class="maxgrid-tinymce_select"></select>';}, tooltip: 'Select the type of post you want'
					},
					
					// Presets list
					{type: 'label', name: 'post_presets', multiline: true, style: 'height: 30px;', id: 'post_presets',
						onPostRender : function() {this.getEl().innerHTML =  htmlLabelConstrict('Template')+postPresetsList+'</div>';}
                    },
					
					//Divider
					{type: 'container', classes: 'top-divider empty-content'},
					
					{type: 'label', name: 'incl_excl_cats', multiline: true, style: 'height: 30px;', id: 'incl_excl_cats', classes: 'incl_excl_cats',
					 	onPostRender : function() {this.getEl().innerHTML =  htmlLabelConstrict('Include or Exclude Categories')+'<select id="incl_excl_cats_sel" onchange="maxgrid_selectOnchange(this)" data-target="incl_excl_val" class="maxgrid-tinymce_select"></select>';}
					},
					
					// Exclude By Categories
					{type: 'label', name: 'MCEpostcatHelper', multiline: true, style: 'height: 30px;', id: 'MCEpostcatHelper',
						onPostRender : function() {this.getEl().innerHTML = htmlLabelConstrict('Exclude')+'' + wpCat + "</div>";}
                    },
					
					// Youtube Options
					{type: 'label', name: 'YoutubeOptions', multiline: true, style: 'height: 68px', id: 'YoutubeOptions', classes: 'top-divider noselect',
						onPostRender : function() {this.getEl().innerHTML = get_ytb_options();}
                    },
					
					//Divider
					{type: 'container', classes: 'top-divider empty-content'},
					
					// Default View	   
					{type: 'label', name: 'dfltview', multiline: true, style: 'height: 30px;', id: 'grid_dfltview', classes: 'grid_dfltview',
						onPostRender : function() {this.getEl().innerHTML =  htmlLabelConstrict('Default View')+'<select id="grid_dfltview_select" onchange="maxgrid_selectOnchange(this)" data-target="dflt_view" class="maxgrid-tinymce_select"></select>';}
                    },
					
					// Maximum items Per Row
					{type: 'label', name: 'max_items_row', multiline: true, style: 'height: 30px;', id: 'grid_max_items_row', classes: 'grid_max_items_row',
						onPostRender : function() {this.getEl().innerHTML =  htmlLabelConstrict('Maximum items Per Row')+'<select id="grid_max_items_row_select" onchange="maxgrid_selectOnchange(this)" data-target="max_items_pr" class="maxgrid-tinymce_select"></select>';}
                    },
					
					//Divider
					{type: 'container', classes: 'top-divider empty-content'},
					
					// Full Content Excerpt
                    {type: 'checkbox', name: 'fullcontent', label: 'Show Full Content', checked: full_content === 'on' ? true : false, id: 'fullcontent'},
					
					// Masonry Layout
                    {type: 'checkbox', name: 'masonry', label: 'Masonry Layout', checked: masonry === 'on' ? true : false, id: 'masonry'},
                    {type: 'container', html: '<p id="masonry_label" style="font-size: 12px; color: #5e5e5e; font-style: italic; margin-top: -5px;">Enable <a  style="font-size: 12px; " href="https://masonry.desandro.com/" target="_blank">Masonry</a> Cascading grid layout.</p>'},
 					
					// Pagination
					{type: 'label', name: 'pagination', multiline: true, style: 'height: 30px;', id: 'grid_pagination', classes: 'grid_pagination top-divider',
						onPostRender : function() {this.getEl().innerHTML =  htmlLabelConstrict('Pagination Type')+'<select id="grid_pagination_select" onchange="maxgrid_selectOnchange(this)" data-target="pagination_val" class="maxgrid-tinymce_select"></select>';}
                    },
					
					// Filter Options
					{type: 'label', name: 'filterOptions', multiline: true, id: 'filterOptions', classes: 'top-divider noselect',
						onPostRender : function() {this.getEl().innerHTML = get_filter_options();}
                    },
					
					{type: 'checkbox', name: 'ribbon', label: 'Disable Ribbons', checked: ribbon === 'off' ? true : false, id: 'grid_ribbon'},
					
					//Divider
					{type: 'container', classes: 'top-divider empty-content'},
                    
					{type: 'textbox', name: 'grid_ppp', label: 'Posts per Page on Grid View', value: grid_ppp, inline: true, classes: 'postsperpage'},
                    {type: 'textbox', name: 'list_ppp', label: 'Posts per Page on List View', value: list_ppp, inline: true, classes: 'postsperpage', id: 'list_ppp'},
					
					// large space on bottom
					{type: 'label', label: '', style: 'height: 50px; opacity: 0;', checked: false, classes: 'mxg_large_space'},
					// Hidden fields
					{type: 'textbox', hidden: true, name: 'youtube_type', classes: 'exclcat', id: 'youtubeType'},
					{type: 'textbox', hidden: true, name: 'youtube_id', classes: 'exclcat', id: 'youtubeId'},
					{type: 'textbox', hidden: true, name: 'youtube_tag_filter', classes: 'exclcat', id: 'youtubeTagFilter'},
					{type: 'textbox', hidden: true, name: 'youtube_tag', classes: 'exclcat', id: 'youtubeTag'},
					{type: 'textbox', hidden: true, name: 'youtube_retrieve_hd', classes: 'exclcat', id: 'youtubeRetrieveHD'},
					{type: 'textbox', hidden: true, name: 'youtube_channel_banner', classes: 'exclcat', id: 'youtubeChannelBanner'},
					{type: 'textbox', hidden: true, name: 'ytb_dflt_filter', classes: 'exclcat', id: 'youtubeDefaultFilter'},
					{type: 'textbox', hidden: true, name: 'block_id', classes: 'exclcat', id: 'block_id'},
					
					{type: 'textbox', hidden: true, name: 'pt_val', classes: 'pt_val', id: 'pt_val'},
					{type: 'textbox', hidden: true, name: 'incl_excl_val', classes: 'incl_excl_val', id: 'incl_excl_val'},
					{type: 'textbox', hidden: true, name: 'dflt_view', classes: 'dflt_view', id: 'dflt_view'},
					{type: 'textbox', hidden: true, name: 'max_items_pr', classes: 'max_items_pr', id: 'max_items_pr'},
					{type: 'textbox', hidden: true, name: 'pagination_val', classes: 'pagination_val', id: 'pagination_val'},
					{type: 'textbox', hidden: true, name: 'mxg_orderby_opt', classes: 'exclcat', id: 'mxg_orderby_filter'},
					{type: 'textbox', hidden: true, name: 'mxg_order_opt', classes: 'exclcat', id: 'mxg_order_filter'},
					{type: 'textbox', hidden: true, name: 'mxg_view_opt', classes: 'exclcat', id: 'mxg_view_filter'},
					{type: 'textbox', hidden: true, name: 'mxg_tax_opt', classes: 'exclcat', id: 'mxg_tax_filter'},
					{type: 'textbox', hidden: true, name: 'mxg_tag_opt', classes: 'exclcat', id: 'mxg_tag_filter'},
				],
				
				buttons: [{
					text: InsertButtonName,
					classes: 'primary',
					onclick: 'submit',
				},
				{
					text: 'Cancel',
					id: 'hd-fancybox-button-cancel',
					onclick: 'close'
				}],
				onsubmit: function( e ) {
					var shortcode_str = '[' + sh_tag + ' post_type="' + e.data.pt_val + '"';
					
					//Creates dynamic id attributes		
					var unique_id = Math.random().toString(36).substr(2, 16);
					
					shortcode_str += ' id="' + unique_id + '"';
					shortcode_str += ' incl_excl="' + e.data.incl_excl_val + '"';		
					shortcode_str += ' exclude="' + $('#allPostCats').val() + '"';					
					shortcode_str += ' preset="' + $('#presets_selsector').val() + '"';
					
					if(e.data.pt_val === 'youtube_stream') {
						shortcode_str += ' ytb_type="' + e.data.youtube_type + '"';
						shortcode_str += ' ytb_id="' + e.data.youtube_id + '"';
						if ( e.data.youtube_type === 'channel' && e.data.youtube_tag_filter === 'on' && e.data.youtube_tag.length ) {
							var tag = e.data.youtube_tag;
							shortcode_str += ' ytb_tag="' + tag.split(' ').join('+') + '"';
						}
						if ( e.data.youtube_type === 'channel' && e.data.youtube_retrieve_hd === 'on' ) {
							shortcode_str += ' retrieve_hd="'+e.data.youtube_retrieve_hd + '"';
						}
						if ( e.data.youtube_type === 'channel' && e.data.youtube_channel_banner === 'on' ) {
							shortcode_str += ' ytb_banner="'+e.data.youtube_channel_banner + '"';
						} else {
							shortcode_str += ' ytb_banner="off"';
						}
						if ( e.data.youtube_type === 'channel' ) {
							shortcode_str += ' ytb_dflt_filter="'+e.data.ytb_dflt_filter + '"';
						}
					}
					
					shortcode_str += ' dflt_mode="'+e.data.dflt_view+'"';
					shortcode_str += ' items_per_row="'+e.data.max_items_pr+'"';
					
					if(e.data.masonry === false) {
						shortcode_str += ' masonry="off"';
					}else{
						shortcode_str += ' masonry="on"';
					}
					
					if(e.data.pt_val !== 'youtube_stream') {
						if (e.data.fullcontent === false) {shortcode_str += ' full_content="off"';}
						else{shortcode_str += ' full_content="on"';}
						if (e.data.ribbon === false) {shortcode_str += ' ribbon="on"';}
						else{shortcode_str += ' ribbon="off"';}
					}
					
					shortcode_str += ' pagination="'+e.data.pagination_val+'"';
					
					if ( e.data.mxg_orderby_opt === 'off' ) {
						shortcode_str += ' orderby_ftr="'+e.data.mxg_orderby_opt + '"';
					}					
					if ( e.data.mxg_order_opt === 'off' ) {
						shortcode_str += ' order_ftr="'+e.data.mxg_order_opt + '"';
					}
					if ( e.data.mxg_view_opt === 'off' ) {
						shortcode_str += ' view_ftr="'+e.data.mxg_view_opt + '"';
					}
					if ( e.data.mxg_tax_opt === 'off' ) {
						shortcode_str += ' tax_ftr="'+e.data.mxg_tax_opt + '"';
					}
					if ( e.data.mxg_tag_opt === 'off' ) {
						shortcode_str += ' tag_ftr="'+e.data.mxg_tag_opt + '"';
					}
					
					shortcode_str += ' grid_ppp="' + e.data.grid_ppp+'"';
					
					if(e.data.pt_val !== 'youtube_stream') {shortcode_str += ' list_ppp="' + e.data.list_ppp+'"';}
					
					// Get count posts
					var count, count_posts = $('body').attr('data-lp-featured');
					count_posts = count_posts ?  maxgrid_b64_to_utf8(count_posts) : '';
					
					if(e.data.pt_val==='post') {count = count_posts.split(',')[3];}
					else if (e.data.pt_val==='download') {count = count_posts.split(',')[4];}
					else if (e.data.pt_val==='product') {count = count_posts.split(',')[5];}
					else if (e.data.pt_val==='youtube_stream'){count = $('#ytb-id-checker_response > div').html();}
					
					if(e.data.pt_val === 'youtube_stream') {
						shortcode_str += ' last_video="' + e.data.block_id + '"';
					}
					
					shortcode_str += ' total="' + count+'"';
					
					//close shortcode
					shortcode_str += ']';
					
					// Select current grid block if edit mode					
					if( typeof selected_block !== "undefined" && selected_block !== null) {
						editor.selection.select(editor.dom.select('.id-'+selected_block)[0]);
					}
					
					//Insert shortcode
					//editor.insertContent( '<br>');
					editor.insertContent( shortcode_str);
					
					// Update content
					update_content();
				},
				onClose: function() {
					window.selected_block = null;
				}
			});
			
			// Reload post presets and categories
			GetExclCats(post_type);

			// Post type
			var postType_obj = {
				labels: ['Post', 'Download', 'WooCommerce','Youtube'],
				values: ['post', 'download', 'product','youtube_stream'],
			};
			htmlSelectConstrict(document.getElementById("gd_ptype_sel"), postType_obj, post_type);
			
			// Post type
			var inclExclCats_obj = {
				labels: ['Include', 'Exclude'],
				values: ['include', 'exclude'],
			};
			htmlSelectConstrict(document.getElementById("incl_excl_cats_sel"), inclExclCats_obj, incl_excl_cats);
			
			// Set to default the excluded categories			
			var exclude_array = exclude.split(',');
			$('#allPostCats').val(exclude_array).trigger("chosen:updated");
			
			if ( post_type === 'youtube_stream' ) {
				// Youtube Options
				document.getElementById("youtube_selector").value = ytb_type;
				document.getElementById("youtubeType").value = ytb_type;

				// Swaping youtube types options
				ytbTypeSwapOptions(ytb_type);
				
				document.getElementById("youtube_id").value = ytb_id;
				document.getElementById("youtubeId").value = ytb_id;
				get_ytb_channel_custom_url(ytb_id);
				
				if ( ytb_tag !== '' && Tagfilter === 'on' ) {
					document.getElementById("youtube_tag_filter_check").checked = true;
					document.getElementById("mce-ytb_keyword_filter").value = ytb_tag;
				} else {
					$('#mce-ytb_keyword_filter').addClass('mce-disabled-field');
				}
				if ( retrieve_hd === 'on' ) {
					document.getElementById("youtube_retrieve_hd").checked = true;
				}
									
				if ( ytb_banner === 'off' ) {
					document.getElementById("youtube_channel_banner").checked = false;
				}
				
				document.getElementById("ytb_dflt_filter").value = ytb_dflt_filter;
				document.getElementById("youtubeDefaultFilter").value = ytb_dflt_filter;
			}
			
			if ( orderby_ftr === 'off' ) {
				document.getElementById("mxg_orderby_opt").checked = false;
				document.getElementById('mxg_orderby_filter').value = 'off';
			} else {
				document.getElementById("mxg_orderby_opt").checked = true;
			}
			
			if ( order_ftr === 'off' ) {
				document.getElementById("mxg_order_opt").checked = false;
				document.getElementById('mxg_order_filter').value = 'off';
			} else {
				document.getElementById("mxg_order_opt").checked = true;
			}
			if ( view_ftr === 'off' ) {
				document.getElementById("mxg_view_opt").checked = false;
				document.getElementById('mxg_view_filter').value = 'off';
			} else {
				document.getElementById("mxg_view_opt").checked = true;
			}
			if ( tax_ftr === 'off' || !isTemplatesOpened ) {
				document.getElementById("mxg_tax_opt").checked = false;
				document.getElementById('mxg_tax_filter').value = 'off';
			} else {
				document.getElementById("mxg_tax_opt").checked = true;
			}
			if ( tag_ftr === 'off' || !isTemplatesOpened ) {
				document.getElementById("mxg_tag_opt").checked = false;
				document.getElementById('mxg_tag_filter').value = 'off';
			} else {
				document.getElementById("mxg_tag_opt").checked = true;
			}	
			
			// Set default preset
			$('#presets_selsector').val(preset).trigger("chosen:updated");
			
			// Default View List
			var dflt_view_obj = {
				labels: ['Grid', 'List'],
				values: ['grid', 'list'],
			};
			htmlSelectConstrict(document.getElementById("grid_dfltview_select"), dflt_view_obj, dflt_mode);
			
			// max Items Per Row List
			var itemsRow_obj = {
				labels: ['6 Items','5 Items', '4 Items', '3 Items', '2 Items','1 Item'],
				values: ['6', '5', '4', '3', '2','1'],
			};
			htmlSelectConstrict(document.getElementById("grid_max_items_row_select"), itemsRow_obj, items_per_row);
			
			// Pagination List
			var pagination_obj = {
				labels: ['Numeric Pagination', 'Load More Button', 'Infinite Scrolling', 'None'],
				values: ['numeric_pagination', 'load_more_button', 'infinite_scroll', 'none'],
			};
			htmlSelectConstrict(document.getElementById("grid_pagination_select"), pagination_obj, pagination);
			
			// on load popup run youtube id checker
			var ytb_type_selector = document.getElementById('youtube_selector');
			if ( ytb_type_selector !== null && ConstMCE.maxgrid_ytb ) {
				maxgrid_ytbIDChecker(ytb_type_selector.value);
			}

			// unblind click to prevent twice click on this element
			$('body').off('click', '#dropdownCheckList.ytb-options .anchor');
			$('body').off('click', '#dropdownCheckList.ftr-options .anchor');
			
			//Toggle youtube options
			$('body').on('click', '#dropdownCheckList.ytb-options .anchor', function() {				
				$('#dropdownCheckList.ytb-options').toggleClass('visible');				
			});
			
			$(document).click(function(e) {
				$('#dropdownCheckList.ytb-options').removeClass('visible');
			});
			
			//Toggle youtube options
			$('body').on('click', '#dropdownCheckList.ftr-options .anchor', function() {				
				$('#dropdownCheckList.ftr-options').toggleClass('visible');
			});
			
			$(document).click(function(e) {
				$('#dropdownCheckList.ftr-options').removeClass('visible');		
			});
			
			//Toggle filter options
			$('body').on('click', '#dropdownCheckList, .channel-search-filter, .mce-maxgrid-sc-popup', function(e) {
				e.stopPropagation();
			});
			
			$('body').on('click', '.mce-i-'+sh_tag, function(e) {
				window.selected_block = null;
			});
			
			ptype_onchange(post_type);
			chosen_reload(post_type);
			mce_items_shift_down();

	    });
		
		//replace from shortcode to an image placeholder
		editor.on('BeforeSetcontent', function(event){
			event.content = replaceShortcodes( event.content );
		});
		
		// replace from input placeholder to shortcode
		editor.on('GetContent', function(event) {
			if(event.content.indexOf('maxgrid_mce_input') === -1 ){
				return;
			}
			
			if(event.content.indexOf('div></p>') > -1 ){
				return;
			}
			var content = restoreShortcodes(event.content);
			event.content =	constrictContent( content );
			
			setTimeout(function () {
				initGridBlock();
			}, 10);
		});
		
		editor.onMouseDown.add(
            function (ed, evt) {
                if ( evt.target.className.match(/(maxgrid-mce-container|maxgrid_mce_input|maxgrid-mce-btn-group|icon-pencil|icon-cross|maxgrid-mce-ptype|maxgrid-bloc-preview)/) ) {return 0;}
            }
        );
		
		editor.on('redo undo',function() {
			update_content();
		});
		
		function update_content(){
			setTimeout(function () {
				var content = editor.getContent();
				editor.setContent(content);
				// Reload all Max Grid block elements if doesn't found.
				if(!find_allElements()){					
					initGridBlock();
				}
			}, 25);
		}
		
		// Check if all GD block elements exist
		function find_allElements(){
			var bodyHTML = editor.dom.get('maxgridBody');
			if ( bodyHTML !== null && /^(?=.*maxgrid_mce_input)(?=.*maxgrid-mce-btn-group)(?=.*icon-pencil)(?=.*icon-cross)(?=.*maxgrid-mce-ptype)(?=.*maxgrid-block-preview)(?=.*http)/.test(bodyHTML.innerHTML)){return true;}
			return 0;
		}
		
		// Get inner editor body HTML
		function getBody(){
			return editor.getBody().innerHTML;
		}
		
		// Edit or Remove Max Grid block
		editor.on('Click',function(e) {
			
			// Open popup on edit button click
			if ( e.target.nodeName === 'DIV' && e.target.className.indexOf('icon-pencil') > -1 ) {
				
				window.selected_block = $(e.target).closest('p').attr('data-id');
				window.isEditMode = true;
				
				var title = e.target.parentNode.parentNode.firstChild.attributes['data-sh-attr'].value;
				title = window.decodeURIComponent(title);
				
				editor.execCommand('maxgrid_popup','',{
					id 				: getAttr(title,'id'),
					post_type  		: getAttr(title,'post_type'),
					preset  		: getAttr(title,'preset'),
					incl_excl_cats	: getAttr(title,'incl_excl'),
					exclude  		: getAttr(title,'exclude'),
					ytb_type  		: getAttr(title,'ytb_type'),
					ytb_id  		: getAttr(title,'ytb_id'),
					ytb_tag  		: getAttr(title,'ytb_tag'),
					retrieve_hd  	: getAttr(title,'retrieve_hd'),
					ytb_banner  	: getAttr(title,'ytb_banner'),
					ytb_dflt_filter : getAttr(title,'ytb_dflt_filter'),
					dflt_mode 		: getAttr(title,'dflt_mode'),
					items_per_row   : getAttr(title,'items_per_row'),
					pagination	    : getAttr(title,'pagination'),
					full_content    : getAttr(title,'full_content'),
					masonry    		: getAttr(title,'masonry'),
					orderby_ftr  	: getAttr(title,'orderby_ftr'),
					order_ftr  		: getAttr(title,'order_ftr'),
					view_ftr  		: getAttr(title,'view_ftr'),
					tax_ftr  		: getAttr(title,'tax_ftr'),
					tag_ftr  		: getAttr(title,'tag_ftr'),
					ribbon    		: getAttr(title,'ribbon'),
					grid_ppp   		: getAttr(title,'grid_ppp'),
					list_ppp   		: getAttr(title,'list_ppp'),
					total 			: getAttr(title,'total'),
					last_video		: getAttr(title,'last_video')
				});
			}
			
			// Remove Max Grid block on remove button click
			if ( e.target.nodeName === 'DIV' && e.target.className.indexOf('icon-cross') > -1 ) {
				// Displays an confirm box.
				tinymce.activeEditor.windowManager.confirm("Press OK to delete section, Cancel to leave", function(s) {					
					if (s) {
						$(e.target).closest('p').remove();
					}
				});
			}
		});
		
		editor.on('click',function(e){
		  if (e.target.className.match(/(maxgrid-mce-container|maxgrid_mce_input|maxgrid-mce-btn-group|icon-pencil|icon-cross|maxgrid-mce-ptype|maxgrid-mce-block-preview)/)){
				e.preventDefault();
			}
		});
		// Prevent drag and drop GD block elements
		editor.on('MouseDown',function(e) {			
			if (e.target.className.match(/(maxgrid-mce-container|maxgrid_mce_input|maxgrid-mce-btn-group|icon-pencil|icon-cross|maxgrid-mce-ptype|maxgrid-mce-block-preview)/)){
				e.preventDefault();
				return false;
			}
		});
		
	});
	
})();

});

/**
 * Various utility functions.
 */

// Prepare filter change
function maxgrid_ytbFilterChange(This) {
	document.getElementById("youtubeDefaultFilter").value = This.value;
}

// Update Value for select field with data-target
function maxgrid_selectOnchange(This) {
	var target = This.getAttribute('data-target');
	document.getElementById(target).value = This.value;
	if(This.value==='download'){
		document.getElementById("block_id").value = 'none';
	}
	if(This.value==='include'){
		$("#MCEpostcatHelper .chosen-search-input")[0].value = 'Select categories to include';
	} else if ( This.value==='exclude'){
		$("#MCEpostcatHelper .chosen-search-input")[0].value = 'Select categories to exclude';
	}
}

// Prepare Youtube filter checkbox (search meta key)
function maxgrid_mceKeywordFilter(This) {
	if (This.checked === true){
		jQuery('#mce-ytb_keyword_filter').removeClass('mce-disabled-field');
		jQuery('#youtubeTagFilter').val('on');
	} else {
		jQuery('#mce-ytb_keyword_filter').addClass('mce-disabled-field');
		jQuery('#youtubeTagFilter').val('off');
	}
	if ( jQuery('#mce-ytb_keyword_filter').val().length > 0 && ConstMCE.maxgrid_ytb ) {
		maxgrid_ytbIDChecker(jQuery('#youtube_selector').val());	
	}
}

// Prepare retrieve hd checkbox
function maxgrid_mceYtbRetrieveHD(This) {
	if ( !ConstMCE.maxgrid_ytb ) {
		return;
	} 
	if (This.checked === true){
		document.getElementById("youtubeRetrieveHD").value = 'on';
	} else {
		document.getElementById("youtubeRetrieveHD").value = 'off';
	}
	maxgrid_ytbIDChecker(document.getElementById('youtube_selector').value);
}

// Prepare filter checkbox
function maxgrid_mceFilter(This) {
	var filter = $(This).attr('data-filter');
	if (This.checked === true) {
		document.getElementById('mxg_'+filter+'_filter').value = 'on';
	} else {
		document.getElementById('mxg_'+filter+'_filter').value = 'off';
	}
}
// Prepare banner checkbox
function maxgrid_mceYtbBanner(This) {
	if (This.checked === true) {
		document.getElementById("youtubeChannelBanner").value = 'on';
	} else {
		document.getElementById("youtubeChannelBanner").value = 'off';
	}
}