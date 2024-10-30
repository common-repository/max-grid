/**
 * Max Grid Builder - The Builder
 */

jQuery(function($) {
		 
	// Clone element
	function Clone_Element(paramObject) {
		try {
			var defaultParams = {
					insertPos: 'beforeThis',
					This: paramObject['This'],
					liContainer: paramObject['liContainer']
				},
				params = defaultParams;

			for (var key in paramObject) {
				if (paramObject.hasOwnProperty(key)) {
					if (paramObject[key] !== undefined) {
						params[key] = paramObject[key];
					}
				}
			}

			var This = params.This,
				liContainer = params.liContainer,
				insertPos = params.insertPos,
				row_id = This.attr('data-row-id'),
				bar_id = This.attr('data-bar'),
				action = This.attr('data-action'),
				inputName = liContainer.find('input').attr('name'),
				elementClone = liContainer.clone(true),
				cloneID = maxgrid_uniqid();
			
			if (liContainer.attr('data-clone-id')) {
				cloneID = liContainer.attr('data-clone-id');
			}
			newId = bar_id + "_" + cloneID;

			var default_Val = {
				'stats_row': Const.localize_stats_row_default_value,
				'info_row': Const.localize_info_row_default_value,
				'divider_row': Const.localize_divider_row_default_value,
				'ytb_vid_stats_row': Const.localize_stats_row_default_value,
				'woo_stats_row': Const.localize_stats_row_default_value
			};

			var new_row_id, clonedOptionsField = '';
			var current_el = jQuery('#maxgrid-columns .' + bar_id);

			if (bar_id.indexOf('divider_bar') !== -1) {
				new_row_id = 'rows_options_' + action + '_' + cloneID;
				clonedOptionsField = '<input class="' + new_row_id + '" name="' + Const.MAXGRID_BUILDER_OPT_NAME + '[grid_layout][rows_options][' + action + '_' + cloneID + ']" value="' + default_Val[action] + '" data-field="' + action + '" type="hidden">';
			}

			jQuery('.general-settings-field').append(clonedOptionsField);

			if (insertPos === 'beforeThis') {
				elementClone.insertBefore(liContainer);
			} else {
				jQuery('ul#maxgrid-columns').prepend(elementClone);
			}

			if (bar_id === 'divider_bar') {
				elementClone.attr('data-clone-id', cloneID);
				elementClone.append('<input type="hidden" name="' + Const.MAXGRID_BUILDER_OPT_NAME + '[grid_layout][' + bar_id + '][clone_id]" value="' + cloneID + '">');
				elementClone.find('.edit_row:not(.element)').attr('data-row-id', row_id + "_" + cloneID);
				elementClone.find('.edit_row.element').attr('data-row-id', newId);
			}

			// need to delete all : views_options
			var match = ['sharethis_options', 'views_options', 'date_options', 'datebar_css', 'statsbar_css'];
			elementClone.find('input').each(function (i, obj) {
				if (bar_id === 'divider_bar') {
					obj.setAttribute('name', obj.getAttribute('name').replace(maxgrid_findMatchingWords(obj.getAttribute('name'), bar_id), newId));

					var name = maxgrid_splitAndGetLast(obj.getAttribute('name')),
						index = match.indexOf(name);

					if (index !== -1) {
						obj.setAttribute('class', newId + '_' + match[index]);
					} else {
						obj.setAttribute('class', newId);
					}

					if (obj.getAttribute('data-field')) {
						obj.setAttribute('class', newId + "_" + obj.getAttribute('data-field'));
					}
				}
			});

			elementClone.addClass('cloned');

			// Update builder elements
			maxgrid_builderElementsUpdate();
		} catch (e) {
			console.log( 'Error: ' + e );
		}
	}
	
	// Changing ribbon type
	function swap_ribbon_type(value) {			
		$('.corner-ribbon').each(function (i, obj) {
			if(value=== 'flat-ribbon') {
				$(obj).attr('class', $(obj).attr('class').replace('corner-ribbon', 'flat-ribbon'));
				$(obj).css('background', obj.children[0].style.backgroundColor);
			} else {
				$('.corner-ribbon').css('background', 'transparent');
			}
		});
		$('.flat-ribbon').each(function (i, obj) {
			if(value=== 'flat-ribbon') {
				$(obj).css('background', obj.children[0].style.backgroundColor);
			} else {
				$(obj).attr('class', $(obj).attr('class').replace('flat-ribbon', 'corner-ribbon'));
				$('.corner-ribbon').css('background', 'transparent');
			}				
		});			
	}
		
	// use custom date format
	function custom_date_time_format_field(DateFormat) {
		if (DateFormat.val() === 'custom') {
			$('#custom_date_time_format_container').removeClass('custom_date_time_format-hidded');
		} else {
			$('#custom_date_time_format_container').addClass('custom_date_time_format-hidded');
		}
	}
	
	maxgrid_ui_sortable();
	
	//try {
		$('body').on('click','#builder_save_changes:not([data-pname])', function() {		
			var This 		= $(this),
				response 	= $(this).closest('div').prev(),
				source_type = $('#source_type').val(),
				prefix 		= $('#source_type').attr('data-pslug');
			
			jQuery.ajax({
				type : "POST",					
				 url : 'options.php',
				 data: $('#builder_form').serialize({ checkboxesAsBools: true }),
				beforeSend:function(xhr) {
					This.prev().css('display', 'inline-block');
					maxgrid_ClearCachesByPrefix(prefix)
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
		
		// get last saved source type 
		var source_type = $('.maxgrid-metaoptions-row.source_type').attr('data-source-type');
		if ( !Const.is_maxgrid_templates_library ) {
			source_type = 'post';
		}
		
		// set last saved source type to current
		var el_id = 'source_type',
			option = source_type;
		
		if ( maxgrid_is_option_exist(el_id, option) ) {
			$('#source_type').val(source_type);
		}
		
		// reload template
		var name = capitalizeFirstLetter(source_type.replace('_', ' ')+' Default');
		
		maxgrid_LayoutEdit(name);
		
		// Set current template path
		//maxgrid_currentLayoutPath();
		
		// add data "source-type" to builder parent div
		$('.grid-builder-parent').attr('data-source-type', source_type);
		
		if ( Const.is_maxgrid_templates_library ) {
			$('#builder_save_changes')[0].removeAttribute('data-pname');
		} else {
			$('#builder_save_changes').attr('data-pname', maxgrid_utf8_to_b64(name));
		}	
		
		/*-------------------------------------------------------------------------*/
		/*	Post Ribbon Visual Editor
		/*-------------------------------------------------------------------------*/
		 
		// Changing ribbon type
		$('body').on('change', '#ribbon_type', function () {
			var value = $(this).val();
			swap_ribbon_type(value);
		});
		
		// Changing ribbon postion
		$('body').on('change', '#ribbon_pos', function () {
			var value = $(this).val();
			$('.rib-container').each(function (i, obj) {
				if( value === 'left') {					
					$(obj).addClass('left');
					$(obj).removeClass('right');
				} else {
					$(obj).addClass('right');
					$(obj).removeClass('left');
				}				
			});
		});
		
		$('body').on('change', '#warpped', function () {
			if ($(this).is(':checked')) {
				$('.rib-container').addClass('wrapped');
			} else {
				$('.rib-container').removeClass('wrapped ');
			}	
		});
		
		// Changing ribbon icon
		$('body').on('click', '[data-ribbon="icon"]', function () {
			window.ribbon_parent = $(this).attr('id');
		});
		
		/*-------------------------------------------------------------------------*/
		/*	Open Graph Image
		/*-------------------------------------------------------------------------*/
		
		var ogImage = $('#default_og_image').val();
		if (ogImage !== '') {
			$('#social_sharing .maxgrid.img-wrap').css('display', 'inline-block');
			$('#social_sharing #img-preview').attr('src', ogImage);
		}

		/*-------------------------------------------------------------------------*/
		/*	Preview Mode
		/*-------------------------------------------------------------------------*/
		
		$('body').on('click', '#builder_preview_changes', function () {
			var This = $(this);			
			var source_type = $('#source_type').val();
			$('#grid-preview-device').attr('src', Const.MAXGRID_ABSURL+'/builder/loader.php');
			
			jQuery.ajax({
				type: "POST",
				url: 'options.php',
				data: $('#builder_form').serialize({
					checkboxesAsBools: true
				}),
				beforeSend: function (xhr) {
					$('.maxgrid_save_changes_container .maxgrid_ajax_response').html('<span>Preparing Preview...</span>');
				},
				success: function (data) {//return;
					var url = $('#grid-preview-device').attr('data-src');
					$('#grid-preview-device').attr('src', url + '&preset=use_current&mxg_preview=true&post_type=' + source_type );
					$('#preview_container').addClass('is-visible');
					$(window).scrollTop(0);
					$('html').addClass('stop-scrolling');

					$('.ajax_dl-spiner').html('');
					$('.ajax_dl-spiner').removeClass('visible');
					$('.maxgrid_save_changes_container .maxgrid_ajax_response').html('');
					$('#grid-preview-device').css('opacity', '1');
					
					$('#grid-preview-options #items_row').val(4);
					$('#grid-preview-options #full_content').prop("checked", true);
					$('#grid-preview-options #masonry').prop("checked", true);					
					
					if ( maxgrid_getCookie("preview_mode_bg_c") ) {
						$('.preview_mode_bg_c').val(maxgrid_getCookie("preview_mode_bg_c"));
						$('.em-pm_wrapper').css('background', maxgrid_getCookie("preview_mode_bg_c"));						
					} else {
						$('.preview_mode_bg_c').val('#272c32');
						$('.em-pm_wrapper').css('background', '#272c32');	
					}
					
					$('iframe#grid-preview-device').load(function(){
						var iframe = $('iframe#grid-preview-device').contents();
						if ( maxgrid_getCookie("preview_mode_bg_c") ) {
							iframe.find('.em-pm_wrapper').css('background', maxgrid_getCookie("preview_mode_bg_c"));					
						} else {
							iframe.find('.em-pm_wrapper').css('background', '#ffffff');	
						}
					});
					
				}
			});
		});
		
		$('body').on('click', '.close-preveiw', function () {		
			$('#grid-preview-device').attr('src', '');
			
			$('#preview_container').removeClass('is-visible');
			$('html').removeClass('stop-scrolling');
		});

		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder  - Delete Row
		/*-------------------------------------------------------------------------*/
		
		$('body').on('click', '.delete_row', function () {
			var liContainer = $(this).closest('li'),
				text = document.createElement('div');
				text.classList.add("mxg-swal-content");
				text.innerHTML = Const.delete_or_cancel;
			
			swal({
			  	content: text,
				buttons: {
					cancel: true,
					confirm: "Yes, delete it!",
				  },
			  	dangerMode: true,
				closeOnClickOutside: false,
			})
			.then(function(willDelete){
			  if (willDelete) {
				liContainer.remove();
				
				// Update builder elements
				maxgrid_builderElementsUpdate();
			  }
			});
		});

		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder  - Delete Element
		/*-------------------------------------------------------------------------*/

		$('body').on('click', '.el_remove', function () {
			$(this).find('input').val('disabled');
			$(this).addClass('locked');
			$(this).next().addClass('locked');
			$(this).closest('li').addClass('locked');
			$(this).closest('header').find('.restore').removeClass('locked');
		});
		
		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder  - Restore Deleted Elements
		/*-------------------------------------------------------------------------*/

		$('body').on('click', '.restore', function () {

			var cloneID = '',
				children = $(this).closest('header').find('span:not(.restore):not(.edit_row.maxgrid_ui-btn):not(.duplicate_row):not(.delete_row)');
			$(this).addClass('locked');

			for (var i = 0; i < children.length; i++) {
				children[i].closest('ul').firstChild.classList.remove('locked');
				children[i].closest('li').classList.remove('locked');
				children[i].closest('span').classList.remove('locked');

				if (children[i].getAttribute('data-editable') === 'true') {
					children[i].classList.add('edit');
				}
				
				if (children[i].className !== 'edit_row element') {
					children[i].innerHTML = '<input type="hidden" name="' + Const.MAXGRID_BUILDER_OPT_NAME + '[grid_layout][' + children[i].getAttribute('data-row') + cloneID + '][' + children[i].getAttribute('data-element') + ']" value="' + children[i].getAttribute('data-element') + '">';
				}
			}
		});

		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder  - OnChange Source Type
		/*-------------------------------------------------------------------------*/
		function capitalizeFirstLetter(string) {
			return string.charAt(0).toUpperCase() + string.slice(1);
		}
		$('body').on('change', '#source_type', function () {
			var source_type = $('#source_type').val();
			$('.grid-builder-parent').attr('data-source-type', source_type);
			
			// Set "Source Type:" label
			maxgrid_setSourceTypeLabel();

			// Set current template path
			maxgrid_currentLayoutPath();
			
			// Load Template
			var name = capitalizeFirstLetter(source_type.replace('_', ' ')+' Default');
			
			maxgrid_LayoutEdit(name);
			
			$('#builder_save_changes')[0].removeAttribute('data-pname');
		});
				
		maxgrid_swapLikesRatings();

		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder - Restore Block Rows
		/*-------------------------------------------------------------------------*/
		
		$('body').on('click', '#maxgrid_restore_all_row', function () {
			
			var message = Const.restore_all_el_msg,
				text 	= document.createElement('div');
				text.classList.add("mxg-swal-content");
				text.style.textAlign = 'left';
				text.innerHTML = message;
			
			swal({
				title: 'Confirm reset',
				content: text,
				buttons: {
					cancel: true,
					confirm: "Yes, reset to default",
				  },
				closeOnClickOutside: false,
			})
			.then(function(willReset) {
				if (willReset) {
					var source_type = $('#source_type').val(),
						pslug 		= source_type + '_default',
						pname 		= source_type === 'youtube_stream' ? 'Youtube Stream Default' : source_type.charAt(0).toUpperCase() + source_type.slice(1) + ' Default';
					
					pslug = $('#source_type').attr('data-pslug');
					pname = maxgrid_b64_to_utf8($('#source_type').attr('data-pname'));
					
					jQuery.ajax({
						type: "POST",
						url: Const.ajaxurl,
						data: {
							action: 'maxgrid_restore_all',
							source_type: source_type,
							swap_source: true,
							pslug: pslug,
							pname: maxgrid_b64_to_utf8($('#source_type').attr('data-pname')),
						},
						beforeSend: function (xhr) {				
							var args = new Object();
								args.color = 'grey';
								args.size = 'small';
							$('.maxgrid-metaoptions-row.grid-builder-parent').append(maxgrid_lds_rolling_loader(args));				
							$('#maxgrid-columns li').css('display', 'none');
							$('#beforeSend').css('top', '10px');							
						},
						success: function (data) {
							
							$('#source_type').val(source_type);
							$('#source_type').trigger("chosen:updated");
							
							$('.grid-builder-parent').attr('data-source-type', source_type);

							$('.maxgrid-metaoptions-row.grid-builder-parent').html(data);
							
							maxgrid_swapLikesRatings();
							
							$('input.fill_space').closest('li').addClass('null_object');
							$('.maxgrid-metaoptions-row').addClass('edit-mode-on');
							$('.swal-overlay').removeClass('swal-overlay--show-modal');
							
							if ($('.save_preset_changes').html() !== undefined) {
								$('.save_preset_changes').remove();
							}

							maxgrid_ui_sortable();
							
							$('#source_type').attr('data-pslug', pslug);
							//$('#source_type').attr('data-pname', pslug);
							
							//$('#builder_save_changes').attr('data-pname', maxgrid_utf8_to_b64(pslug));
							
							$('[data-action="blocks_row"]').attr('data-pslug', pslug);
							$('[data-action="lightbox_row"]').attr('data-pslug', pslug);
							$('[data-action="filter_row"]').attr('data-pslug', pslug);

							$('li.maxgrid-column header').each(function (i, obj) {
								obj.setAttribute('data-pslug', pslug);
							});

							$('li.row_element.edit').each(function (i, obj) {
								obj.setAttribute('data-pslug', pslug);
							});
							
							// Set "Source Type:" label
							maxgrid_setSourceTypeLabel();

							// Update builder elements
							maxgrid_builderElementsUpdate();
						}
					});
				}
			});
		});	
				
		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder - Edit Row
		/*-------------------------------------------------------------------------*/
		$( document.body ).delegate( '.edit_row, .ui-single-btn:not(.duplicate-layout):not(.refresh)', 'click', function() {
			
			var data_bar, row_id = $(this).attr('data-row-id'),
				action = $(this).attr('data-action'),
				This = $(this),
				clone_id = $(this).closest('li').attr('data-clone-id'),
				uiPanelTitle = $(this).attr('data-ui-panel-title');
			
			if ( action === 'templates_manager' && !Const.is_maxgrid_templates_library ) {
				maxgrid_premium_required_alert('Templates Manager');
				return;
			}
			// Remove Reset All Settings Button
			var ExcludeToDFLT = ['templates_manager', 'about_builder'];
			if (ExcludeToDFLT.indexOf(action) !== -1) {
				$('#set_to_default').css('display', 'none');
			} else {
				$('#set_to_default').css('display', 'inline-block');
			}

			if (clone_id && action === 'divider_row') {
				clone_id = clone_id.replace('divider_bar_', '');
			}
			if ($(this).attr('data-bar') === undefined) {
				data_bar = $(this).attr('data-row-id');
			} else {
				data_bar = $(this).attr('data-bar');
			}
			
			window.row_id = row_id;
			window.action = action;
			window.This = This;

			var popHeight;
			switch (action) {
				case 'blocks_row' :
				case 'featured_row' :
				case 'audio_row' :
				case 'stats_row' :
				case 'add_to_cart_row' :
				case 'download_row' :
				case 'average_rating_row' :
				case 'info_row' :
				case 'lightbox_row' :
					popHeight = 400;
					break;
				case 'divider_row' :
					popHeight = 360;
					break;
				case 'title_row' :
					popHeight = 330;
					break;
				case 'sharethis_options' :
				case 'views_options' :
				case 'readmore_element' :
					popHeight = 300;
					break;
				case 'templates_manager' :
					popHeight = 400;
					break;
				case 'about_builder' :
					popHeight = 200;
					break;
				default:
					popHeight = 250;
			}
			
			$('#maxgrid_ui-panel-content').css('max-height', popHeight+'px');

			// Hide Save Change Button
			if ($(this).attr('data-savechanges') === 'off') {
				$('#maxgrid_ui_save_change').css('display', 'none');
			} else {
				$('#maxgrid_ui_save_change').css('display', 'inline-block');
			}

			// Generate Options Tabs
			if ($(this).attr('data-multi-tabs')) {
				$('.maxgrid_ui-tab').css('display', 'block');

				var htmlTabs = '<div class="maxgrid_ui-tab">';

				var strTabs = $(this).attr('data-tabs-title'),
					tabsArray = strTabs.split(',');

				for (var i = 0; i < tabsArray.length; i++) {
					if ( $('#source_type').val() === 'youtube_stream' && tabsArray[i].indexOf('Ribbon') !== -1 ) {
						continue;
					}
					
					tabsArray[i] = tabsArray[i].replace(/^\s*/, "").replace(/\s*$/, "");
					htmlTabs += '<button class="maxgrid_ui-tablinks" onclick="toggleTab(event, ' + "'" + tabsArray[i].split(':')[0] + "'" + ')" id="defaultOpen">' + tabsArray[i].split(':')[1] + '</button>';
				}
				htmlTabs += '</div>';
				$('#ui_tabs_container').html(htmlTabs);
			} else {
				$('.maxgrid_ui-tab').css('display', 'none');
			}

			var data = '';
			var match = ['date_options', 'sharethis_options', 'views_options'];
			if (match.indexOf(action) !== -1) {
				data = $('input.' + row_id + '_' + action).val();
			}
			
			if (row_id.indexOf('rows_options') !== -1) {

				var row = action;
				if (action.indexOf('stats_row') !== -1) {
					row = 'stats_row';
				}
				
				if (clone_id) {
					var temp = 'rows_options_' + row + '_' + clone_id;
					data = $('input.rows_options_' + row + '_' + clone_id).val();
				} else {
					var temp = 'rows_options_' + row;
					data = $('input.rows_options_' + row).val();
				}				
			}
			
			if (row_id === 'post_description') {
				var and;
				$(this).closest('header').find('input').each(function (i, obj) {
					and = (i !== 0) ? '&' : '';
					if (obj.getAttribute('data-field') !== null) {
						data += and + obj.getAttribute('data-field') + '=' + obj.value;
					}
				});
			}

			if (action === 'add_row') {
				data = $('#builder_form').serialize();
				$('.ui-panel-content').addClass('elements-list');
				$('#maxgrid_ui-panel-footer').addClass('elements-list-footer');
				$('#maxgrid_ui-panel-content').css('max-height', '450px');
			}
			
			var item_id, bar_name;
			if (clone_id) {
				item_id = row_id + '_' + clone_id;
				bar_name = action + '_' + clone_id;
			} else {
				item_id = row_id;
				bar_name = action;
			}
			window.item_id = item_id;
			
			//info_row
			var panelContent = document.getElementById('maxgrid_ui-panel-content'),
				pslug, first_open = '',
				EditTarget = '';
			
			pslug = $('#source_type').attr('data-pslug')!== undefined ? $('#source_type').attr('data-pslug') : $('#source_type').val()+'_default';
			
			if (action === 'blocks_row' || action === 'lightbox_row' || action === 'filter_row') {				
				if ($(this).attr('data-pslug') !== undefined) {
					first_open = 'true';
				}
			} else {
				EditTarget = $(this).attr('class').indexOf('edit_row element') === -1 ? $(this).closest('header') : $(this).closest('li.row_element.edit');
				if (EditTarget.attr('data-pslug') !== undefined ) {
					first_open = 'true';
				}
			}
			
			jQuery.ajax({
				type: "POST",
				url: Const.ajaxurl,
				data: {
					action: 'maxgrid_'+action,
					dataForm: data,
					item_id: item_id,
					source_type: $('#source_type').val(),
					pslug: pslug,
					first_open: first_open,
					cloned_element: window.cloned_element === true ? 'cloned' : '',
					bar_name: bar_name,
					data_bar: data_bar,
				},
				beforeSend: function (xhr) {				
					var args = new Object();
						args.version = '1';
						args.size = 'medium';
					
					$('.maxgrid_ui-header').html(uiPanelTitle);
					
					if (action === 'add_row') {
						args.color = 'blue';
						
					} else {
						args.color = 'grey';
					}
					panelContent.innerHTML = maxgrid_lds_rolling_loader(args);
					
					$("#defaultOpen").addClass('active');
					$(".ui-panel-content.elements-list").css('background', '#0e1114').css('border-top', '1px solid transparent');
				},
				success: function (data) {
					panelContent.innerHTML = data;
					window.cloned_element = null;
					
					$('#maxgrid_ui-panel-content').attr('data-data-bar', data_bar);
					if (EditTarget.length > 0) {
						$('#maxgrid_ui-panel-content').attr('data-edit-target', EditTarget.attr('class'));
					}
					try {
						if ($('#inside_tooltip').is(":checked")) {
							$('.maxgrid_ui_description.inside_tooltip').css('display', 'block');
							$('.maxgrid_ui_description.horizontal_list').css('display', 'none');
							$('.maxgrid_ui_description.popup_box').css('display', 'none');
						}
						if ($('#horizontal_list').is(":checked")) {
							$('.maxgrid_ui_description.inside_tooltip').css('display', 'none');
							$('.maxgrid_ui_description.horizontal_list').css('display', 'block');
							$('.maxgrid_ui_description.popup_box').css('display', 'none');
						}
						if ($('#popup_box').is(":checked")) {
							$('.maxgrid_ui_description.inside_tooltip').css('display', 'none');
							$('.maxgrid_ui_description.horizontal_list').css('display', 'none');
							$('.maxgrid_ui_description.popup_box').css('display', 'block');
						}
					} catch (e) {}

					var parent = document.body.querySelector('#maxgrid_ui-panel-content');
					var elements = parent.querySelectorAll('input.extras-triggers, select.extras-triggers');
					
					maxgrid_extrasTriggers(elements);
					if (action === 'featured_row') {
						$('#featured_row_form .hidden_opt_element').attr('data-current', $('#featured_filter-style').val());
						if (!$('#fillcover_overlay').is(':checked')) {
							$("#overlay_transition-style option").each(function () {
								if ($(this).val().toLowerCase() === "direction_aware") {
									$(this).attr("disabled", "disabled");
								}
							});
						}
					}
						
					//Limiting the number of checkboxes selected by user
					if ($('#popup_box').length > 0) {
						if ($('#popup_box').is(':checked')) {
							$('#social_media_selctor_container').attr('data-max', '20');
						} else {
							$('#social_media_selctor_container').attr('data-max', '5');
						}
					}

					var Class = item_id,
						max = $('#social_media_selctor_container').attr('data-max');

					maxgrid_checkControl(Class, max);

					$('.maxgrid-colorpicker').wpColorPicker({});
					$('.chosen').chosen({
						disable_search_threshold: 10,
						width: '100%'
					});
					
					
					$('.fontselect').fontselect();

					$('.fs-drop').addClass('maxgrid-builder');

					if ($('#defaultOpen').length > 0) {
						document.getElementById("defaultOpen").click();
					}

					// Show/Hide Block shadow options
					if ($('#maxgrid_block_box_shadow').length > 0) {
						if ($('#maxgrid_block_box_shadow').val() === 'none') {
							$('.box_shadow_options_target').hide();
						} else {
							$('.box_shadow_options_target').show();
						}
					}

					// Show/Hide Info bar extras options
					if ($('#maxgrid_infobar_enable_extras').length > 0) {
						if ($('#maxgrid_infobar_enable_extras').is(':checked')) {
							$('.row_custom_color_container').css('opacity', '1').css('pointer-events', 'all');
						} else {
							$('.row_custom_color_container').css('opacity', '.5').css('pointer-events', 'none');
						}
					}

					if ($('.ribbon_type.extras-triggers').val() === 'corner-ribbon') {
						$('.corner_ribbon_target').removeClass('hidded');
						$('.ribbon_top_pos_target').addClass('hidded');
					} else {
						$('.corner_ribbon_target').addClass('hidded');
						$('.ribbon_top_pos_target').removeClass('hidded');
					}

					var bgImage = $('#audioplayer_bg_image').val();
					if (bgImage !== '') {
						$('#audio_row_form .maxgrid.img-wrap').css('display', 'inline-block');
						$('#audio_row_form #img-preview').attr('src', bgImage);
					}
					
					if ( action === 'date_options' ) {
						custom_date_time_format_field($('#maxgrid_date_format'));						
					}
					$('#newest_icon').iconpicker("#newest_icon");
					$('#views_icon').iconpicker("#views_icon");
					$('#liked_icon').iconpicker("#liked_icon");
					$('#downloaded_icon').iconpicker("#downloaded_icon");
					$('#onsale_icon').iconpicker("#onsale_icon");
					$('#bestseller_icon').iconpicker("#bestseller_icon");
					
					// Ribbon Visual Editor
					$('[data-ribbon="color"]').wpColorPicker({
						change: function(event, ui) {
							var color = this.value;
							var parent = $(this).closest('.maxgrid_ui-block-col');
							setTimeout(function(){
								maxgrid_RibbonLivePreview(parent,icon=null,color);
							},10);
						},
					});	
					var value = $('#ribbon_type').val();
					swap_ribbon_type(value);
					
					// Initialize Color Scheme DropDown menu - Extra Colors Selector			
					maxgrid_ExtraColor_contruct();
					
					// Templates Library - hide all presets except the "Post" post type presets
					$('option[data-source-type]:not([data-source-type="post"])').css('display', 'none');
					//$(".chosen-list").chosen({disable_search_threshold: 10});
					$('.chosen-list').chosen({inherit_select_classes: true});
					
					$('select.chosen-list~.chosen-container').trigger('mousedown');
					$('.chosen-list').val('').trigger('chosen:updated');
					$('ul.chosen-results li:first-of-type').removeClass('highlighted');
				}
			});
			$('.maxgrid_ui-panel_overlay').css('display', 'block');
		});

		$('body').on('change', '#featured_filter-style', function () {
			$('#featured_row_form .hidden_opt_element').attr('data-current', $(this).val());
		});

		$('body').on('click', '#inside_tooltip, #horizontal_list, #popup_box', function () {
			var max, This;

			if ($('#popup_box').is(':checked')) {
				$('#social_media_selctor_container:not(.list-view)').attr('data-max', '20');
				This = 'popup_box';
				max = 20;
			} else {
				$('#social_media_selctor_container:not(.list-view)').attr('data-max', '5');
				This = $(this).attr('id');
				max = 5;
			}
			var default_five = ['facebook', 'twitter', 'google', 'vkontakte', 'linkedin'];
			$('#social_media_selctor_container .social_media_selctor:not(.list-view)').each(function (i, obj) {
				if (This === 'popup_box') {
					obj.checked = true;
				} else {
					if (default_five.indexOf(obj.id) === -1) {
						obj.checked = false;
					} else {
						obj.checked = true;
					}
				}

			});

			maxgrid_checkControl(item_id, max);
		});
		
		$('body').on('click', '.element-to-insert', function () {
			This = $(this);
			var x = $('#builder_form').serializeArray();
			jQuery.ajax({
				type: "POST",
				url: Const.ajaxurl,
				data: {
					action: 'maxgrid_add_new_element',
					dataForm: x,
					source_type: $('#source_type').val(),
				},
				success: function (data) {
					
					var elements = $(data);
					var element = This.attr('data-element-id');
						
					var found = $('.maxgrid-column.' + element + ':not(.ytb_vid_stats_bar)', elements);
					
					if ($('.maxgrid-column[data-clone-id="' + element + '"]', elements).attr('data-clone-id') === element) {
						found = $('.maxgrid-column[data-clone-id="' + element + '"]', elements);
					}
					
					Clone_Element({
						insertPos: 'onTop',
						This: found.find('.edit_row.bar'),
						liContainer: found
					});
								
					$(".ui-panel-content.elements-list").css('background', '#f1f1f1').css('border-top', '1px solid #ccc');
					$('.ui-panel-content').removeClass('elements-list');
					$('#maxgrid_ui-panel-footer').removeClass('elements-list-footer');

					$('.maxgrid_ui-panel_overlay').css('display', 'none');
					
					window.cloned_element = true;
					$('#maxgrid-columns > li .edit_row.bar').first().trigger('click');
					maxgrid_swapLikesRatings();
					
					// Update builder elements
					maxgrid_builderElementsUpdate();
				}
			});
		});
		
		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder - The Featured Pannel
		/*-------------------------------------------------------------------------*/

		$('body').on('change', '#fillcover_overlay', function () {
			var Select_el = $('#overlay_transition-style');
			if ($(this).is(':checked')) {
				$("#overlay_transition-style option").each(function () {
					if ($(this).val().toLowerCase() === "direction_aware") {
						$(this).removeAttr("disabled");
					}
				});
			} else {
				Select_el.val('slide_up');
				$("#overlay_transition-style option").each(function () {
					if ($(this).val().toLowerCase() === "direction_aware") {
						$(this).attr("disabled", "disabled");
					}
				});
			}

			$("#maxgrid_ui-panel-content").animate({
				scrollTop: $('#maxgrid_ui-panel-content').prop("scrollHeight")
			}, 1000);
			setTimeout(function () {}, 50);
		});
		
		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder - Panel Save Changes
		/*-------------------------------------------------------------------------*/
		
		$('body').on('click', '#maxgrid_ui_save_change', function () {
			// Remove data-slug atribute from bar header					
			if (action === 'blocks_row') {				
				// Remove data-slug atribute from "source_type" select list
				$('[data-action="blocks_row"]')[0].removeAttribute('data-pslug');
				var gridblocksOptions 	  = decodeURIComponent($('#' + action + '_form').serialize()).replace(/\+/g,' '),
					description_font_name = $('#description_font_family').val().replace(/\+/g, ' ').split(':'),
					description_font_link = $('#description_font_family').val();
				
				var descriptionFont = '&description_font_family=' + description_font_name[0] + '&description_font_link=' + description_font_link;
				$('input.' + row_id + '_' + action).val(gridblocksOptions + descriptionFont);				
			} else {				
				if (action === 'lightbox_row') {
					$('[data-action="lightbox_row"]')[0].removeAttribute('data-pslug');
				}
				if (action === 'filter_row') {
					$('[data-action="filter_row"]')[0].removeAttribute('data-pslug');
				}
				var EditTarget;
				if ($('#maxgrid_ui-panel-content').attr('data-edit-target') === 'row_element edit') {
					EditTarget = ' .row_element.edit';
				} else {
					EditTarget = ' header';
				}

				var data_bar = $('#maxgrid_ui-panel-content').attr('data-data-bar');
				if (data_bar !== undefined && action !== 'lightbox_row' && action !== 'filter_row') {
					$('li.' + data_bar + EditTarget)[0].removeAttribute('data-pslug');
				}
			}

			var clone_id = This.closest('li').attr('data-clone-id'),
				bar_id = This.attr('data-bar');
			
			// Rows Options
			if (action === 'lightbox_row' || action === 'filter_row' || action === 'stats_row' || action === 'info_row' || action === 'description_row' || action === 'ytb_description_row' || action === 'title_row' || action === 'divider_row' || action === 'add_to_cart_row' || action === 'download_row' || action === 'average_rating_row' || action === 'featured_row' || action === 'audio_row' || action === 'ytb_vid_stats_row' || action === 'woo_stats_row') {
				var Options = decodeURIComponent($('#' + action + '_form').serialize()),
					titleFont = '';
				
				if (action === 'title_row') {
					var title_font_name = $('#title_font_family').val().replace(/\+/g, ' ').split(':'),
						title_font_link = $('#title_font_family').val();
					
					titleFont = '&title_font_family=' + title_font_name[0] + '&title_font_link=' + title_font_link;
				}

				if (action === 'add_to_cart_row') {
					var font_name = $('#price_font_family').val().replace(/\+/g, ' '),
						font_link = $('#price_font_family').val();
					font_name = font_name.split(':');
					titleFont = '&font_family=' + font_name[0] + '&font_link=' + font_link;
				}
				Options = Options.replace(/\+/g, ' ') + titleFont;
				
				//rows_options_divider_row_PbvfUMMR
				if (clone_id) {
					if (action === 'divider_row') {
						clone_id = clone_id.replace('divider_bar_', '');
					}

					var new_row_id = row_id.replace('_' + clone_id, '') + '_' + action + '_' + clone_id;
					
					if ($('input.' + new_row_id).length === 0) {
						var clonedOptionsField = '<input class="' + new_row_id + '" name="' + Const.MAXGRID_BUILDER_OPT_NAME + '[grid_layout][rows_options][' + action + '_' + clone_id + ']" value="' + Options + '" data-field="' + action + '" type="hidden">';
						$('.general-settings-field').append(clonedOptionsField);
					} else {
						$('input.' + row_id.replace('_' + clone_id, '') + '_' + action + '_' + clone_id).val(Options);
					}
				} else {
					var input = $('input.' + row_id + '_' + action);					
					if (input.length === 0) {
						var hiddenInput = '<input class="' + row_id + '_' + action + '" name="' + Const.MAXGRID_BUILDER_OPT_NAME + '[grid_layout][rows_options][' + action + ']" value="' + Options + '" data-field="' + action + '" type="hidden">';
						$('.general-settings-field').append(hiddenInput);
					} else {
						$('input.' + row_id + '_' + action).val(Options);
					}
				}
			}
			
			// Elements Options
			var match = ['date_options', 'sharethis_options', 'views_options'];
			if (match.indexOf(action) !== -1) {
				
				var Options2 = decodeURIComponent($('#' + action + '_form').serialize());
				Options2 = Options2.replace(/\+/g, ' ');
				
				if (clone_id) {
					$('input.' + row_id + '_' + action + '_' + clone_id).val(Options2);
				} else {
					$('input.' + row_id + '_' + action).val(Options2);
				}
			}

			// Date Row CSS
			var info_rowClassCloned = '',
				info_row_css_options = bar_id + '_datebar_css';
			if (clone_id) {
				info_rowClassCloned = '.' + clone_id;
				info_row_css_options = bar_id + '_' + clone_id + '_datebar_css';
			}

			var dateBarStyles = '';
			if (action === 'info_row') {
				dateBarStyles = '.info_row-container' + info_rowClassCloned + ' {';
				$('.info_row-field').each(function (i, obj) {
					var Unit = '',
						Value = $(this).val(),
						Propety = $(this).attr('data-name');

					if (Propety === undefined) {
						return true;
					}

					if ($(this).attr('type') === 'checkbox') {
						val = $(this).is(":checked");
					}

					if (Propety !== 'background' && Propety !== 'border-style' && Propety !== 'border-color') {
						Unit = 'px';
					}
					if (Value === '' || Value === '0' || Value === '0px') {
						Value = '0';
						Unit = '';
					}
					if (Value.indexOf('px') !== -1) {
						Unit = '';
					}

					dateBarStyles += Propety + ': ' + Value + Unit + ';';
				});
				dateBarStyles += '}';
			}

			$('input#' + info_row_css_options).val(dateBarStyles);
			
			// Post Stats Row CSS
			var classCloned = '',
				stats_row_css_options = bar_id + '_statsbar_css';
			if (clone_id) {
				classCloned = '.' + clone_id;
			}
			
			var StatsBarFitStyles = '',
				StatsBarStyles = '';
			
			if (action === 'stats_row') {
				
				StatsBarStyles = '.social-share-container-grid' + classCloned + ' {';
				StatsBarListStyles = '.social-share-container-list' + classCloned + ' {';

				$('.stats_row-field').each(function (i, obj) {
					var Unit = '',
						Value = $(this).val(),
						Propety = $(this).attr('data-name');

					if (Propety === undefined ) {
						return true;
					}
					if ( Propety === 'color' && Options.indexOf('use_term_c2') > -1 ) {
						return true;
					}
					if ( Propety === 'background' && Options.indexOf('use_term_c1') > -1 ) {
						return true;
					}
					if ($(this).attr('type') === 'checkbox') {
						val = $(this).is(":checked");
					}

					if (Propety !== 'background' && Propety !== 'color' && Propety !== 'border-style' && Propety !== 'border-color') {
						Unit = 'px';
					}
					
					if (Value === '' || Value === '0' || Value === '0px') {
						Value = '0';
						Unit = '';
					}
					if (Value.indexOf('px') > -1) {
						Unit = '';
					}
					
					StatsBarStyles += Propety + ': ' + Value + Unit + ';';
					
					if (Propety === 'background' || Propety === 'color') {
						StatsBarListStyles += Propety + ': ' + Value + Unit + ';';
					}
				});
				
				StatsBarStyles += '}';
				StatsBarListStyles += '}';
				
				// Font Color CSS
				var fontColor = maxgrid_strCSS(StatsBarStyles, '.social-share-container-grid' + classCloned, 'color');
				var StatsBarfontColor = '.social-share-container-grid' + classCloned + ' .maxgrid-sharthis,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .maxgrid-sharthis:hover,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' #share-trigger:hover > .ytb-share-btn:before,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .cover-stat-downlod:before,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .cover-stat-sales,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .cover-stat-sales:before,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .total_reviews,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .views-count,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .dl-count,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .count-grid,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .cover-stat-views,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .like,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .sales-count,';
				StatsBarfontColor += ' .social-share-container-grid' + classCloned + ' .maxgrid_share,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .maxgrid-sharthis,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .maxgrid-sharthis:hover,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .cover-stat-downlod:before,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .cover-stat-sales,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .cover-stat-sales:before,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .total_reviews,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .views-count,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .dl-count,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .count-grid,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .cover-stat-views,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .like,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .sales-count,';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .maxgrid_share { color: ' + maxgrid_rgb2hex(fontColor) + ';}';
				StatsBarfontColor += ' .social-share-container-list' + classCloned + ' .maxgrid_share svg path { fill: ' + maxgrid_rgb2hex(fontColor) + ';}';

				$('input#' + stats_row_css_options).val(StatsBarStyles + StatsBarListStyles + StatsBarFitStyles + StatsBarfontColor);
			}
			
			// blocks Row CSS		
			if (action === 'blocks_row') {
				// block-grid / Styles
				var blockGridStyles = '.block-grid {';
				var blockListStyles = '.block-list-container, .block-list-top {';
				var StatsListStyles = '.social-share-container-grid' + classCloned + ', .social-share-container-list' + classCloned + ' {';

				$('.block-grid-field').each(function (i, obj) {
					var Unit = '',
						Value = $(this).val(),
						Propety = $(this).attr('data-name');

					if (Propety === undefined) {
						return true;
					}

					if (Propety !== 'background' && Propety !== 'border-style' && Propety !== 'border-color') {
						Unit = 'px';
					}
					if (Value === '' || Value === '0' || Value === '0px') {
						Value = '0';
						Unit = '';
					}
					if (Value.indexOf('px') !== -1) {
						Unit = '';
					}

					blockGridStyles += Propety + ': ' + Value + Unit + '!important;';
					if (Propety === 'border-color' || (Propety.indexOf('border') !== -1 && Propety.indexOf('radius') !== -1)) {
						blockListStyles += Propety + ': ' + Value + Unit + '!important;';
					}

					if (Propety === 'border-bottom-left-radius' || Propety === 'border-bottom-right-radius') {
						StatsListStyles += Propety + ': ' + Value + Unit + '!important;';
						if (Value !== '' && Value !== '0' && Value !== '0px') {
							StatsListStyles += 'outline: none!important;';
						}
					}
				});

				blockGridStyles += '}';
				blockListStyles += '}';
				blockListStyles += '.block-list-top { border-bottom-left-radius: 0!important; border-bottom-right-radius: 0!important;}';
				StatsListStyles += '}';
				
				var borderLeftWidth = parseInt(maxgrid_strCSS(blockGridStyles, '.block-grid', 'border-left-width').replace('px', '')),
					borderTopWidth 	= parseInt(maxgrid_strCSS(blockGridStyles, '.block-grid', 'border-top-width').replace('px', '')),
					paddingTop 		= parseInt(maxgrid_strCSS(blockGridStyles, '.block-grid', 'padding-top').replace('px', ''));
					//listPad 		= $('#maxgrid_padding-list-layout').val().replace('px', '');

				var layoutStyles 	= '.corner-ribbon.left {left: -' + borderLeftWidth + 'px; top: -' + borderTopWidth + 'px;}';
					layoutStyles   += '.corner-ribbon.list.left {padding: ' + paddingTop + 'px!important;}';
					//layoutStyles   += '.block-list-top {padding: ' + listPad + 'px!important; padding-bottom: 0px!important;}';
					//layoutStyles   += '.block-list { margin-bottom: ' + listPad + 'px!important;}';
				
					layoutStyles   += '.block-list-top {padding-left: ' + $('[name="list_padding_left"]').val().replace('px', '') + 'px!important;';
					layoutStyles   += 'padding-top: ' + $('[name="list_padding_top"]').val().replace('px', '') + 'px!important;';
					layoutStyles   += 'padding-right: ' + $('[name="list_padding_right"]').val().replace('px', '') + 'px!important;}';
				
					layoutStyles   += '.block-list { margin-bottom: ' + $('[name="list_padding_bottom"]').val().replace('px', '') + 'px!important;}';

				// block-grid-container / Styles
				var blockGridContainerStyles = '.block-grid-container {';
				$('.design-field').each(function (i, obj) {
					var Unit = 'px',
						Value = $(this).val(),
						Propety = $(this).attr('data-name').replace('margin', 'padding');

					if (Value === '' || Value === '0' || Value === '0px') {
						Value = '0';
						Unit = '';
					}
					if (Value.indexOf('px') !== -1) {
						Unit = '';
					}
					blockGridContainerStyles += Propety + ': ' + Value + Unit + ';';
				});
				blockGridContainerStyles += '}';
				
				$('input.' + row_id + '_design_options_css').val(blockGridStyles + blockListStyles + StatsListStyles + blockGridContainerStyles + layoutStyles);
				$('input.' + row_id + '_design_options_css').attr('pslug', );
			}
			
			$('input.templates_manager').val($('#templates_manager').val());

			$('.form-field').each(function (i, obj) {
				var val = $(this).val(),
					field_id = $(this).attr('id');
				if ($(this).attr('type') === 'checkbox') {
					val = $(this).is(":checked");
				}

				if ($(this).attr('type') === 'radio') {
					if ($(this).is(":checked")) {
						val = $(this).val();
						field_id = $(this).attr('data-radio-id');
					}
				}
				$('input.' + row_id + '_' + field_id).val(val);
			});
			$('.maxgrid_ui-panel_overlay').css('display', 'none');
		});

		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder - Generate Grid Template Preset
		/*-------------------------------------------------------------------------*/
		
		$('body').on('click', '[data-exit="true"]', function () {
			$('.duplicate-layout').click();
		});

		$('body').on('click', '.duplicate-layout', function () {
			if ( !Const.is_maxgrid_templates_library ) {
				maxgrid_premium_required_alert('Duplicate Template');
				return;
			}
			
			swal({
				title: 'Template name',
				//content: "input",
				content: {
					element: "input",
					attributes: {
						id: "restrict_special_char",
						placeholder: Const.duplicate_placeholder,
					},
				},
				buttons: {
					cancel: true,
					confirm: "Save Template",
				  },
				closeOnClickOutside: false,
			})
			.then(function(name) {
				if(name === null){
					throw null;
				}
				if (name.match(/[^a-zA-Z0-9 ]/g)) {					
					swal('', Const.template_name_error, "warning");
					$('.swal-button--confirm').attr('data-exit', 'true');
				  	throw null;
				}
				if (name) {
					jQuery.ajax({
							type: "POST",
							url: 'options.php',
							data: $('#builder_form').serialize(),
							beforeSend: function (xhr) {								
								maxgrid_swalSpinner();
							},
							success: function (data) {								
								jQuery.ajax({
									type: "POST",
									url: Const.ajaxurl,
									data: {
										action: "maxgrid_duplicate_template",
										preset_name: name,
										source_type: $('#source_type').val(),
									},
									success: function (data) {
										if (data !== 'SUCCESS') {										
											swal('', data, "warning");
											$('.swal-button--confirm').attr('data-exit', 'true');
										} else {
											swal("", Const.T_Success_Gen, "success");
											maxgrid_LayoutEdit(name);
										}
										$('#builder_save_changes').attr('data-pname', maxgrid_utf8_to_b64(name));
									}
								});
							}
						});
				} else {
					swal('', Const.layout_name_required, "warning");
					$('.swal-button--confirm').attr('data-exit', 'true');
				  	throw null;
				}
		
			});
		});
		
		/*-------------------------------------------------------------------------*/
		/*	PostGrid Builder - Edit Preset and Save change
		/*-------------------------------------------------------------------------*/
		
		$('body').on('click','[data-pname]', function() {
			
			var pname = $(this).attr('data-pname');
			var This = $(this),
				response = $(this).closest('div').prev(),
				source_type = $('#source_type').val();
			
			jQuery.ajax({
				type : "POST",					
				 url : 'options.php',
				 data: $('#builder_form').serialize({ checkboxesAsBools: true }),
				beforeSend:function(xhr) {
					This.prev().css('display', 'inline-block');
					if (source_type === 'youtube_stream') {
						maxgrid_ClearCaches();
					}
				},
				success: function(data) {					
					jQuery.ajax({
						type: "POST",
						url: Const.ajaxurl,
						data: {
							action: "maxgrid_duplicate_template",
							preset_name: maxgrid_b64_to_utf8(pname),
							source_type: source_type,
							action_type: 'edit',
						},
						success: function (data) {
							This.html('Save Changes');
							response.html('<span>Changes Saved Successfully!</span>');
							setTimeout(function () {
								$('.maxgrid_ajax_response span').addClass('hidded');
							}, 3800);
							maxgrid_ClearCachesByPrefix(maxgrid_b64_to_utf8(pname))
						}
					});
					This.prev().css('display', 'none');
				}
			});
		});

		/*-------------------------------------------------------------------------*/
		/*	Limiting the number of checkboxes selected by user
		/*-------------------------------------------------------------------------*/

		$('body').on('click', '.social_media_selctor', function () {
			var Class = $(this).attr('data-item-id'),
				max = ($('#social_media_selctor_container').length > 0) ? $('#social_media_selctor_container').attr('data-max') : '20';
			maxgrid_checkControl(Class, max);
		});

		/*-------------------------------------------------------------------------*/
		/*	Prests Manager
		/*-------------------------------------------------------------------------*/
		
		// Import Prests
		$('body').on('change', '#import_presets', function () {

			var formData = new FormData();
			
			formData.append('file', $(this)[0].files[0]);
			formData.append('action', 'maxgrid_import_templates');

			jQuery.ajax({
				type: "POST",
				url: Const.ajaxurl,
				data: formData,
				contentType: false,
				processData: false,
				beforeSend: function () {
					var args = new Object();
						args.size = 'small';
					$('#beforeSend').html(maxgrid_lds_rolling_loader(args));
					$('#beforeSend').css('top', '10px');
				},
				success: function (data) {
					if (data.indexOf('file type not allowed') !== -1) {
						$('#beforeSend').html('');
						swal('', data, "warning");
					}
					if (data.indexOf('SUCCESS') > -1) {
						
						var options = maxgrid_replaceAll(data, 'SUCCESS', '');
						$('#templates_manager option[value="no-preset"]').remove(); // remove 'no preset' option
						$('#templates_manager').append(maxgrid_replaceAll(options, ' And ', ' & '));

						$('#beforeSend').css('top', '0');
						if (data.indexOf('<option') !== -1) {
							$('#beforeSend').css('color', '#039624');
							$('#beforeSend').html(Const.T_imported);
						} else {
							$('#beforeSend').css('color', '#ff2626');
							$('#beforeSend').html(Const.T_already_imported);
						}
						$('select.chosen-list~.chosen-container').trigger('mousedown');
						$('.chosen-list').trigger('chosen:updated');
					}				
				}
			});
		});

		$('body').on('click', '.maxgrid-button', function () {
			$('.maxgrid_generate_preset_response').addClass('isnt-visible');
		});
		
		// Enable edit/delete buttons
		$('body').on('change', '#templates_manager', function () {
			if(this.value === 'no-preset'){
				return false;
			}
			$(".chosen-list").trigger('chosen:change');
			$('.preset-manager-combo-btn').removeClass('disabled');
			$('.maxgrid-button').removeClass('disabled');
		});
		
		$('body').on('click', 'li.active-result', function () {
			$('.active-result').removeClass('result-selected');
			$(this).addClass('result-selected');
		});
		
		// stop Propagation
		$('body').on('click', '#maxgrid_ui-panelheader, #templates_manager option, .preset-manager-combo-btn', function (e) {
			e.stopPropagation();
		});
		
		/*-------------------------------------------------------------------------*/
		/*	Number only fields
		/*-------------------------------------------------------------------------*/
		
		$('body').on('keyup', '.numbers-only', function () {
			$(this).val($(this).val().replace(/[^0-9\.]/g, ''));
		});
		
		/*-------------------------------------------------------------------------*/
		/*	Block Box Shadow
		/*-------------------------------------------------------------------------*/

		$('body').on('change', '#maxgrid_block_box_shadow', function () {
			if ($(this).val() === 'none') {
				$('.box_shadow_options_target').hide();
			} else {
				$('.box_shadow_options_target').show();
			}
		});
		
		/*-------------------------------------------------------------------------*/
		/*	get Youtube id channel from name channel
		/*-------------------------------------------------------------------------*/

		$('#channel_id').keyup();

		/*-------------------------------------------------------------------------*/
		/*	Dual Input
		/*-------------------------------------------------------------------------*/

		$('body').on('keyup', '#stats_row_form #maxgrid_padding-top', function () {
			$('#maxgrid_padding-bottom').val($(this).val());
		});

		/*-------------------------------------------------------------------------*/
		/*	Info row theme color
		/*-------------------------------------------------------------------------*/

		$('body').on('click', '#maxgrid_infobar_enable_extras', function () {
			if ($(this).is(':checked')) {
				$('.row_custom_color_container').css('opacity', '1').css('pointer-events', 'all');
			} else {
				$('.row_custom_color_container').css('opacity', '.5').css('pointer-events', 'none');
			}
		});

		/*-------------------------------------------------------------------------*/
		/*	Presets Filter
		/*-------------------------------------------------------------------------*/
		 		
		$('body').on('click', '[name="presets_filter"]', function () {
			var target = $(this).val();
			
			$('option[data-source-type]').css('display', 'block');
			$('.preset-manager-combo-btn, .preset-manager-combo-btn .maxgrid-button').addClass('disabled');
			
			$('.chosen-results').attr('data-source-type', 'st-'+target);		
			
			if (target !== 'all') {
				$('option[data-source-type]:not([data-source-type="' + target + '"])').css('display', 'none');
			}
			
			$('select.chosen-list~.chosen-container').trigger('mousedown');
			$('.chosen-list').val('').trigger('chosen:updated');
			
		});
		
		/*-------------------------------------------------------------------------*/
		/*	Custom Date Time Format
		/*-------------------------------------------------------------------------*/
				
		$('body').on('change', '#maxgrid_date_format', function () {
			custom_date_time_format_field($(this));
		});
		
		/*-------------------------------------------------------------------------*/
		/*	Reset Settings to default
		/*-------------------------------------------------------------------------*/

		// Design Otions
		$('body').on('click', '#set_to_default', function () {
			
			var default_settings, settings, This = $(this);
			settings = $('#maxgrid_ui-panel-content form').attr('id').replace('_form', '');
			
			jQuery.ajax({
				type: "POST",
				url: Const.ajaxurl,
				data: {
					action: 'maxgrid_get_default_settings',
					settings: settings,
				},
				beforeSend: function (xhr) {
					maxgrid_beforeSendButton(This);
				},
				success: function (data) {					
					default_settings = maxgrid_strToArray(data);
					for (var key in default_settings) {
						var value = default_settings[key];
						var input = jQuery('[name="' + key + '"]');						
						maxgrid_setDefaultValue(input, value, settings, key);						
					}
					
					// Social Sharing - Social Tab		
					$('.maxgrid-metaoptions-row').removeClass('disabled');
					
					// Youtube date options
					var source_type = $('#date_options_form').attr('data-source-type');
					if (source_type !== undefined && source_type === 'youtube_stream') {
						$('[name="date_format"]').val('time_ago');
					}
						
					try {
						$('.switch-input').click();
					} catch (e) {
						console.log( 'Error: ' + e );
					}
					$('.box_shadow_options_target').css('display', 'none');

					// The Featured Row
					$('.share_this_target').removeClass('hidded');
					$('.use_cat_term_color_target').removeClass('hidded');
					$('.post_stats_target').addClass('hidded');
					$('.links_icons_target').addClass('hidded');			
					$('.download_btn_target').addClass('hidded');				
					$('.category_target').addClass('hidded');
					//$('.custom_term_color_target').addClass('hidded');
					
					$('.use_gradient').addClass('drop-hidded');
					
					// Ribbon
					$('.bordered_block[data-display="hidded"]').removeClass('hidded');
					
					if ($('.ribbon_type.extras-triggers').val() === 'corner-ribbon') {
						$('.corner_ribbon_target').removeClass('hidded');
						$('.ribbon_top_pos_target').addClass('hidded');
					} else {
						$('.corner_ribbon_target').addClass('hidded');
						$('.ribbon_top_pos_target').removeClass('hidded');
					}
					
					// Save Changes
					setTimeout(function () {
						jQuery.ajax({
							type: "POST",
							url: 'options.php',
							data: $('#builder_form').serialize({
								checkboxesAsBools: true
							})
						});
					}, 20);
										
					// Post Description
					$('.row_custom_color_container').css('opacity', '.5').css('pointer-events', 'none');

					$('.ajax_dl-spiner').html('');
					$('.ajax_dl-spiner').removeClass('visible');

					// Audio Row
					$('#audio_row_form .maxgrid.img-wrap').css('display', 'none');
					
					maxgrid_ExtraColorInitialize();
				}
			});

		});

		$('body').on('change', '.ribbon_type.extras-triggers', function () {
			if ($(this).val() === 'corner-ribbon') {
				$('.corner_ribbon_target').removeClass('hidded');
				$('.ribbon_top_pos_target').addClass('hidded');
			} else {
				$('.corner_ribbon_target').addClass('hidded');
				$('.ribbon_top_pos_target').removeClass('hidded');
			}
		});
		
		/*-------------------------------------------------------------------------*/
		/*	plus
		/*-------------------------------------------------------------------------*/

		jQuery('input.fill_space').closest('li').addClass('null_object');
		
	//} catch (e) {
		//console.log( 'Error: ' + e );
	//}
});

// Ribbon live preview
function maxgrid_RibbonLivePreview(parent, icon, color) {	
	var text = parent.find('[data-ribbon="text"]').val(),
		icon = icon ? icon : parent.find('[data-ribbon="icon"]').val(),
		icon_html = icon !== 'fa-none' ? ' <i class="fa '+icon+'" aria-hidden="true"></i>' : '',
		color = color ? color : parent.find('[data-ribbon="color"]').val();

	parent.find('.fa').attr('class', 'fa '+icon);
	parent.find('.ribbon-prev-container').html(text+icon_html);

	var parent_id = parent.attr('data-parent'),
		darker_color = decrease_brightness(color, 32);

	parent.find('.ribbon-prev-container').css('background', color);
	parent.find('.flat-ribbon').css('background', color);

	jQuery('<style>.'+parent_id+' span:before{border-left-color: '+darker_color+'!important; border-top-color:'+darker_color+'!important;} .'+parent_id+' span:after{border-right-color: '+darker_color+'!important; border-top-color:'+darker_color+'!important;} .flat-ribbon.left.'+parent_id+':before {border-right-color: '+darker_color+'!important; border-top-color: '+darker_color+'!important;} .flat-ribbon.right.'+parent_id+':before {border-left-color: '+darker_color+'!important; border-top-color: '+darker_color+'!important;}</style>').appendTo('head');	
}

// Show current layout path
function maxgrid_currentLayoutPath(pname) {
	
	var dflt_template = pname !== undefined ? pname : 'Default Template';
	var default_set = ['Post Default', 'Download Default', 'Product Default', 'Youtube Stream Default'];
		dflt_template = default_set.indexOf(dflt_template) > -1 ? 'Default Template' : dflt_template;
	jQuery('.layout-path').html('Library / ' + jQuery("#source_type option:selected").html() + ' / ' + dflt_template );	
}

// Update builder elements height
function maxgrid_builderElementsUpdate() {
	jQuery('#maxgrid-columns li').each(function(i, obj) {						
		obj.style.display = null;
	});

	// Set builder height - prevent sortable drag & drop issues
	jQuery('#tabcontent').css('height', jQuery('#maxgrid-columns').outerHeight()+164);
}

// Limiting the number of checkboxes selected by user Function
function maxgrid_checkControl(Class, max) {
	if (parseInt(max) === 0) {
		return false;
	}
	var noChecked = 0;
	jQuery.each(jQuery('.' + Class + ':not(.list-view)'), function () {
		if (jQuery(this).is(':checked')) {
			noChecked++;
		}
	});
	if (noChecked >= parseInt(max)) {
		jQuery.each(jQuery('.' + Class + ':not(.list-view)'), function () {
			if (jQuery(this).not(':checked').length == 1) {
				jQuery(this).attr('disabled', 'disabled');
			}
		});
	} else {
		jQuery('.' + Class).removeAttr('disabled');
	};
}
// Change Likes element to Ratings element
function maxgrid_swapLikesRatings() {
	var source_type = jQuery('#source_type').val();
	if (source_type === 'download') {
		var oldHTML = jQuery(".stats_bar ul.elements_container li:nth-child(4)").html();
		if(oldHTML===undefined){
			return;			
		}
		jQuery(".stats_bar ul.elements_container li:nth-child(4)").html(oldHTML.replace("Likes", "Average Ratings"));
		jQuery(".stats_bar ul.elements_container li:nth-child(3)").remove();
	}

	// Update styles of "Restore Deleted elements" button
	maxgrid_restoreDeletedElements();
}

// Edit layout
function maxgrid_LayoutEdit(dup_pslug) {
	
	var pslug, pname, source_type = jQuery('#source_type').val(), dropDown = document.getElementById("templates_manager");
	if (!dup_pslug) {
		if (dropDown.selectedIndex === -1) {
			swal('', 'No layout selected', "warning");
		}

		for (var i = 0; i <= dropDown.options.length; i++) {
			if (dropDown.options[i].selected && dropDown.options[i].value !== 'none') {
				pslug = dup_pslug ? dup_pslug : dropDown.options[i].value;
				pname = dropDown.options[i].innerHTML;
				source_type = dropDown.options[i].getAttribute('data-source-type');
				break;
			}
		}
	} else {
		pslug = dup_pslug.toLowerCase().split(' ').join('_');
		pname = dup_pslug;
	}
	
	jQuery.ajax({
			type: "POST",
			url: Const.ajaxurl,
			data: {
				action: "maxgrid_layout_preset_edit",
				pslug: pslug,
				source_type: source_type,
			},
			beforeSend: function (xhr) {				
				var args = new Object();
					args.color = 'grey';
					args.size = 'small';
				jQuery('.maxgrid-metaoptions-row.grid-builder-parent').append(maxgrid_lds_rolling_loader(args));
				
				jQuery('#maxgrid-columns li').css('display', 'none');

				jQuery('.maxgrid_ui-panel_overlay').css('display', 'none');
				jQuery('.ui-panel-content').removeClass('elements-list');
				jQuery('#maxgrid_ui-panel-footer').removeClass('elements-list-footer');
			},
			success: function (data) {
				
				jQuery('#source_type').val(source_type);
				jQuery('#source_type').trigger("chosen:updated");
				
				jQuery('.grid-builder-parent').attr('data-source-type', source_type);
				
				maxgrid_currentLayoutPath(pname);
				
				jQuery('.maxgrid-metaoptions-row.grid-builder-parent').html(data);
				
				maxgrid_swapLikesRatings();
				jQuery('input.fill_space').closest('li').addClass('null_object');
				jQuery('.maxgrid-metaoptions-row').addClass('edit-mode-on');

				if (jQuery('.save_preset_changes').html() !== undefined) {
					jQuery('.save_preset_changes').remove();
				}
				
				maxgrid_ui_sortable();
				
				jQuery('#source_type').attr('data-pslug', pslug);
				jQuery('#source_type').attr('data-pname', maxgrid_utf8_to_b64(pname));
				
				/*
				var default_pslug = ['post_default', 'product_default', 'download_default', 'youtube_stream_default'];
				if ( default_pslug.indexOf(pslug) === -1 ) {
					jQuery('#builder_save_changes').attr('data-pname', maxgrid_utf8_to_b64(pslug));
				}
				*/
				
				jQuery('#builder_save_changes').attr('data-pname', maxgrid_utf8_to_b64(pslug));
				
				jQuery('[data-action="blocks_row"]').attr('data-pslug', pslug);
				jQuery('[data-action="lightbox_row"]').attr('data-pslug', pslug);
				jQuery('[data-action="filter_row"]').attr('data-pslug', pslug);

				jQuery('li.maxgrid-column header').each(function (i, obj) {
					obj.setAttribute('data-pslug', pslug);
				});

				jQuery('li.row_element.edit').each(function (i, obj) {
					obj.setAttribute('data-pslug', pslug);
				});
				
				// Set "Source Type:" label
				maxgrid_setSourceTypeLabel();
				
				// Update builder elements
				maxgrid_builderElementsUpdate();
			}
		});

}

// Delete layout
function maxgrid_deleteLayout() {
	var preset_name, dropDown = document.getElementById("templates_manager");
	for (var i = 0; i <= dropDown.options.length; i++) {		
		if (dropDown.options[i].selected && dropDown.options[i].value !== 'none') {
			var preset_name = dropDown.options[i].innerHTML,
				source_type = dropDown.options[i].getAttribute('data-source-type'),
				showed_name = preset_name.replace('&amp;', '&');
			
			var text = document.createElement('div');
			text.classList.add("mxg-swal-content");
			text.innerHTML = Const.confirm_delete.replace('%s', showed_name);
			
			swal({
				content: text,
				buttons: true,
				closeOnClickOutside: false,
			})
			.then(function(willDelete) {
				if (willDelete) {
					dropDown.removeChild(dropDown.options[i]);
					var pname = preset_name.replace(' &amp; ', ' And ');
					jQuery.ajax({
						type: "POST",
						url: Const.ajaxurl,
						data: {
							action: "maxgrid_layout_preset_delete",
							preset_name: pname,
							source_type: source_type,
						},
						beforeSend: function (xhr) {
							maxgrid_swalSpinner();
						},
						success: function (data) {
							if (data === 'SUCCESS') {
								
								var data_pname = jQuery('#builder_save_changes').attr('data-pname');
								if ( data_pname !== undefined && pname === maxgrid_b64_to_utf8( data_pname ) ) {
									jQuery('#source_type').val('post');
									jQuery('#source_type').change();
								}
								
								jQuery('.preset-manager-combo-btn, .preset-manager-combo-btn .maxgrid-button').addClass('disabled');
								jQuery('.maxgrid_generate_preset_response').html('The layout was deleted successfully.');
								jQuery('.maxgrid_generate_preset_response').removeClass('isnt-visible');
								jQuery('.maxgrid_generate_preset_response').removeClass('maxgrid-error');
							} else {
								jQuery('.maxgrid_generate_preset_response').addClass('maxgrid-error');
								jQuery('.maxgrid_generate_preset_response').html(data);
							}
							jQuery('select.chosen-list~.chosen-container').trigger('mousedown');
							jQuery('.chosen-list').val('').trigger('chosen:updated');
							jQuery('.swal-overlay').removeClass('swal-overlay--show-modal');
							
						}
					});
				}
			});
			break;
		}
		
	}
}

// Export all layouts from DB
function maxgrid_LayoutExport(This) {
	
	var post_type = 'post', s, radios = document.getElementsByName('presets_filter');
	for (var i = 0, length = radios.length; i < length; i++) {
		if (radios[i].checked) {
			post_type = radios[i].value;
			break;
		}
	}
	s = post_type.replace('product', 'WooCommerce').replace('youtube_stream', 'Youtube');
	s = s != 'all' ? ' ' + s.charAt(0).toUpperCase() + s.slice(1) : '';
	
	var text = document.createElement('div');
		text.classList.add("mxg-swal-content");
		text.innerHTML = Const.confirm_export_all.replace(' %s', s);	
	swal({
		content: text,
		buttons: true,
		closeOnClickOutside: false,
	})
	.then(function(willExport) {
		if (willExport) {
			jQuery.ajax({
				type: "POST",
				url: Const.ajaxurl,
				data: {
					action: "maxgrid_export_templates",
					post_type: post_type,
				},
				beforeSend: function (xhr) {
					maxgrid_swalSpinner();
				},
				success: function (data) {
					var a = document.createElement('A');
					a.href = data;
					a.download = data.substr(data.lastIndexOf('/') + 1);
					document.body.appendChild(a);
					a.click();
					document.body.removeChild(a);
					jQuery('.swal-overlay').removeClass('swal-overlay--show-modal');
				}
			});
		}
	});
}

// Update styles of "Restore Deleted elements" button
function maxgrid_restoreDeletedElements() {
	var locked, all_locked = true;
	jQuery('.maxgrid-column.stats_bar .elements_container li').each(function (i, obj) {
		locked = jQuery(obj).attr('class').indexOf('locked');
		if ( locked !== -1 ) {
			all_locked = false;
		}
	});
	if ( all_locked ) {
		jQuery('.maxgrid-column.stats_bar .elements_container .restore').addClass('locked');
	}
}

// Extract CSS property from string CSS
function maxgrid_strCSS(css, selector, property) {
	var style = document.createElement('style');
	style.appendChild(document.createTextNode(css));
	document.body.appendChild(style);
	var sheet = style.sheet;
	style.remove();
	var rule = Array
		.from(sheet.cssRules)
		.find(cssRule => cssRule.selectorText === selector);
	if (rule) {
		return rule.style.getPropertyValue(property);
	}
}

// Settings Inner Tabs
function toggleTab(evt, tabID) {
	var i, tabLinks,
		tabElement = document.getElementById(tabID),
		tabContent = document.getElementsByClassName("maxgrid_ui-tabcontent");
	for (i = 0; i < tabContent.length; i++) {
		tabContent[i].style.display = "none";
	}
	tabLinks = document.getElementsByClassName("maxgrid_ui-tablinks");
	for (i = 0; i < tabLinks.length; i++) {
		tabLinks[i].className = tabLinks[i].className.replace(" active", "");
	}
	if (tabElement !== null) {
		document.getElementById(tabID).style.display = "block";
	}
	evt.currentTarget.className += " active";
}

// JQuery Serialize Method and Checkboxes
(function ($) {
	$.fn.serialize = function (options) {
		return $.param(this.serializeArray(options));
	};
	$.fn.serializeArray = function (options) {
		var o = $.extend({
			checkboxesAsBools: false
		}, options || {});
		var rselectTextarea = /select|textarea/i;
		var rinput = /text|hidden|password|search/i;
		return this.map(function () {
				return this.elements ? $.makeArray(this.elements) : this;
			})
			.filter(function () {
				return this.name && !this.disabled &&
					(this.checked ||
						(o.checkboxesAsBools && this.type === 'checkbox') ||
						rselectTextarea.test(this.nodeName) ||
						rinput.test(this.type));
			})
			.map(function (i, elem) {
				var val = jQuery(this).val();
				return val == null ?
					null :
					$.isArray(val) ?
					$.map(val, function (val, i) {
						return {
							name: elem.name,
							value: val
						};
					}) : {
						name: elem.name,
						value: (o.checkboxesAsBools && this.type === 'checkbox') ? //moar ternaries!
							(this.checked ? 'true' : 'false') : val
					};
			}).get();
	};
})(jQuery);

// Average rating live preview
function maxgrid_averageRatingPreview(e) {
	jQuery('.maxgrid_averageRatingPreview span').html('34 ' + e.value);
}

// Get querystring value
function maxgrid_getParameterByName(old_src, name) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		results = regex.exec(old_src);
	return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

// Max Grid Premium required alert
function maxgrid_premium_required_alert(title){
	var text = document.createElement('div');
	text.classList.add("mxg-swal-content");
	text.innerHTML = Const.premium_required_msg;
	
	swal({
		title: title,
		content: text,
		buttons: {
			cancel: "Cancel",
			get_mxg_premium: {
				text: "Get Premium",
				value: "catch",
			}
		},
	})
	.then((getPremium) => {
	  if (getPremium) {
		var win = window.open(Const.MAXGRID_SITE_HOME_PAGE+'/max-grid-premium-add-on/', '_blank');
		win.focus();
	  }
	});
}

//Add or modify querystring
function maxgrid_changeUrl(old_src, key, value) {
	//Get query string value
	searchUrl = old_src;
	if (searchUrl.indexOf("?") == "-1") {
		var urlValue = '?' + key + '=' + value;
		history.pushState({
			state: 1,
			rand: Math.random()
		}, '', urlValue);
	} else {
		//Check for key in query string, if not present
		if (searchUrl.indexOf(key) == "-1") {
			var urlValue = searchUrl + '&' + key + '=' + value;
		} else {
			oldValue = maxgrid_getParameterByName(old_src, key);
			if (searchUrl.indexOf("?" + key + "=") != "-1") {
				urlValue = searchUrl.replace('?' + key + '=' + oldValue, '?' + key + '=' + value);
			} else {
				urlValue = searchUrl.replace('&' + key + '=' + oldValue, '&' + key + '=' + value);
			}
		}
		return urlValue;
	}
}