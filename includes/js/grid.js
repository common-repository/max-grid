
/*---------------------------------------------------- */
/*	Max Grid  - JS
/*---------------------------------------------------- */

// Render Youtube Subscribe button
function maxgrid_renderYtSubscribeButton() {
  var head = document.getElementsByTagName('head')[0];
  var script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = 'https://apis.google.com/js/platform.js';
  head.appendChild(script);
}

jQuery(document).ready(function($){	
	// Get Grid ID
	function get_grid_id(triger){
		return triger.closest('.maxgrid-body').attr('data-g-uid');
	}
	
	// Render Youtube Subscribe button
	maxgrid_renderYtSubscribeButton();
	
	// AJAX Add to cart button
	$('body').on('click','.ajax_add_to_cart', function() {
		var This = $(this);
		
		window.addtoCartBtnLabel = This.html();
		window.ThisATC = This;

		var args = new Object();
			args.size = 'small';
		var spinner_html = maxgrid_lds_rolling_loader(args);
		var spinner = This.closest('span').find('.add-to-cart-spinner');
				
		if ( This[0].nodeName === 'I') {
			This.addClass('is-downloading');
			spinner = This;
			spinner_html = '<div class="dld-spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>';
		}
		
		spinner.html(spinner_html);
		
		if ( This[0].nodeName !== 'I') {
			spinner.css('left', '-'+This[0].offsetWidth/2+'px');
			This.css('min-width', This[0].offsetWidth+'px');
			This.html('');
		}
	});
		 
	/*-------------------------------------------------------------------------*/
	/*	On window resize - Update Grid styles
	/*-------------------------------------------------------------------------*/
	
	$( window ).resize(function() {
		eqcss();
		var right_stat_grid = $('.right-stat-grid')[0];		
		if(right_stat_grid === undefined ){
			return false;
		}
		
		var mince = right_stat_grid.offsetWidth+5;	
	});
	
	// compare positions between two elements function
	var overlaps = (function () {
		function getPositions( elem ) {
			var pos, width, height;
			pos = $( elem ).position();
			width = $( elem ).width() / 2;
			height = $( elem ).height();
			return [ [ pos.left, pos.left + width ], [ pos.top, pos.top + height ] ];
		}

		function comparePositions( p1, p2 ) {
			var r1, r2;
			r1 = p1[0] < p2[0] ? p1 : p2;
			r2 = p1[0] < p2[0] ? p2 : p1;
			return r1[1] > r2[0] || r1[0] === r2[0];
		}

		return function ( a, b ) {
			var pos1 = getPositions( a ),
				pos2 = getPositions( b );
			return comparePositions( pos1[0], pos2[0] ) && comparePositions( pos1[1], pos2[1] );
		};
	})();

	/*-------------------------------------------------------------------------*/
	/*	Direction-Aware Hover Effect
	/*-------------------------------------------------------------------------*/
		
	$('body').on('mouseenter mouseleave','.video-wrapper.direction_aware', function(event) {
		
		//disable on touch devices
		if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
			return;
		}
		var direction = getDirection($(this), event);
		
		var rotate_mode = null; // make it 'true' to enable the Direction Aware rotate mode.		
		if(rotate_mode){
			var in_out = event.type === 'mouseenter' ? 'in' : 'out';
			var aware 	= $(this)[0].querySelector('.post-excerpt');	
			aware.classList.remove("in-top", "in-left", "in-bottom", "in-right", "out-top", "out-left", "out-bottom", "out-right", "s-t-l", "s-t-r", "s-b-r", "s-b-l");
			var $target = $(this);
			switch (direction) {
				case 0:
					aware.classList.add(in_out+'-top', getSide($target, event));
					break;
				case 1:
					aware.classList.add(in_out+'-right', getSide($target, event));
					break;
				case 2:
					aware.classList.add(in_out+'-bottom', getSide($target, event));
					break;
				case 3:
					aware.classList.add(in_out+'-left', getSide($target, event));
					break;
			}			
		} else {
			animateOverlay($(this)[0], direction, event.type === 'mouseenter');		
			var elem 	= $(this)[0].querySelector('.post-links');
			elem.classList.remove("top", "left", "bottom", "right");
			switch (direction) {
				case 0:
					elem.classList.add('top');
					break;
				case 1:
					elem.classList.add('right');
					break;
				case 2:
					elem.classList.add('bottom');
					break;
				case 3:
					elem.classList.add('left');
					break;
			}	
		}
	});

	function animate(element, values, duration) {
		element.offsetLeft;
		element.style.transitionDuration = duration + 'ms';
		for (var property in values) {
			element.style[property] = values[property] + 'px';
		}
	}

	function animateOverlay(parent, slideDirection, isHover) {
		var overlay = parent.querySelector('.fillcover.direction_aware');
		var top = 0, left = 0;
		if (slideDirection === 0) {
			top = -parent.offsetHeight;
		}
		if (slideDirection === 1) {
			left = parent.offsetWidth;
		}
		if (slideDirection === 2) {
			top = parent.offsetHeight;
		}
		if (slideDirection === 3) {
			left = -parent.offsetWidth;
		}
		if (isHover) {
			showAnimation(overlay, top, left);
		} else {
			hideAnimation(overlay, top, left);
		}
	}

	function showAnimation(overlay, top, left) {		
		animate(overlay, {
			top: top,
			left: left
		}, 0);
		animate(overlay, {
			left: 0,
			top: 0
		}, 400);
	}

	function hideAnimation(overlay, top, left) {
	  animate(overlay, {
		left: left,
		top: top
	  }, 400);
	}
	
	function getDirection($target, event) {
		//reference: http://stackoverflow.com/questions/3627042/jquery-animation-for-a-hover-with-mouse-direction
		var w = $target.width(),
			h = $target.height(),
			x = (event.pageX - $target.offset().left - (w / 2)) * (w > h ? (h / w) : 1),
			y = (event.pageY - $target.offset().top - (h / 2)) * (h > w ? (w / h) : 1),
			direction = Math.round((((Math.atan2(y, x) * (180 / Math.PI)) + 180) / 90) + 3) % 4;		
		return direction;
	}
	
	function getSide($target, event) {
		var w = $target.width(),
			h = $target.height(),
			x = (event.pageX - $target.offset().left - (w / 2)) * (w > h ? (h / w) : 1),
			y = (event.pageY - $target.offset().top - (h / 2)) * (h > w ? (w / h) : 1);
		var side = 's-b-r';
		if ( x>0 && y>0 ) {
			side = 's-b-r';
		} else if ( x<0 && y>0 ) {
			side = 's-b-l';
		} else if ( x<0 && y<0 ) {
			side = 's-t-l';
		} else if ( x>0 && y<0 ) {
			side = 's-t-r';
		}
		return side;
	}
	
	/*-------------------------------------------------------------------------*/
	/*	External Link
	/*-------------------------------------------------------------------------*/
	
	$('body').on('click','[data-external-link]', function() {		
		var ExternalLink = $(this).attr('data-external-link');	
		location.href = ExternalLink;		
	});		
	
	/*-------------------------------------------------------------------------*/
	/*	Go Back to category url
	/*-------------------------------------------------------------------------*/
	
	$('body').on('click','[data-return="on"]', function() {		
		var queryString = 'category='+$(this).attr('data-category');
		var backURL = updateQueryStringParameter(document.referrer, 'category', $(this).attr('data-category'));		
		location.href = backURL;		
	});
	
	function updateQueryStringParameter(uri, key, value) {
	  var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
	  var separator = uri.indexOf('?') !== -1 ? "&" : "?";
	  if (uri.match(re)) {
		return uri.replace(re, '$1' + key + "=" + value + '$2');
	  }
	  else {
		return uri + separator + key + "=" + value;
	  }
	}

	/*-------------------------------------------------------------------------*/
	/*	Share This Button - ytb style - show / hide 
	/*-------------------------------------------------------------------------*/
	
	$('body').on('mouseover','.f_share-btn', function() {
		$(this).parent().addClass('visible');
	});
	
	$('body').on('mouseleave','.f_share-this', function() {
		$(this).removeClass('visible');
	});
	
	/*-------------------------------------------------------------------------*/
	/*	AJAX Download Procces - Decode file then run the download
	/*-------------------------------------------------------------------------*/
	
	/**
	 * Get the URL parameters
	 *
	 * @param  {String} url The URL
	 * @return {String}     The URL parameters
	 */
	var getParams = function (url) {
		var params = '';
		var parser = document.createElement('a');
		parser.href = url;
		var query = parser.search.substring(1);
		var vars = query.split('&');

		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split('='),
				pair2 = vars[i].split('&'),
				btnTriger = decodeURIComponent(pair[0]) === 'onclickbtn' ? true : null;
			if(btnTriger){
				continue;
			}
			params += '&'+pair2;
		}
		return params;
	};
	
	/**
	 * Update URL parameters
	 * @param  {String} id The Post ID
	 */
	function updateURL(id) {		
		var oldParms = getParams(window.location.href).substring(1).replace('history=go', ''),
		newParms = oldParms !== '' ? oldParms+'&onclickbtn='+id : 'onclickbtn='+id;
		if (history.pushState) {
			var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?'+newParms;
			window.history.pushState({path:newurl},'',newurl);
		}
	}
	
	$('body').on('click','#maxgrid_download_single', function() {		
		var This 	 = $(this),
			post_id  = This.attr('data-post-id'),
			meta_key = This.attr('data-meta-key'),
			once 	 = This.attr('data-once'),
			URL 	 = This.attr('data-href'),
			restrict = maxgrid_GET('restrict_download');
		
		updateURL(post_id);
		
		$.ajax({
			type : "POST",
			url : Const.ajaxurl,
			data : {
				action   : 'maxgrid_decrypt_file_url',
				url    	 : URL,
				demo 	 : window.location.href.indexOf('fx-console-free-ae-plugin') > -1 ? true : null,
			},
			beforeSend:function(xhr) {				
				maxgrid_beforeSendButton(This);
			},
			success:function(response){
				
				if( response !== 'ERROR' ){
					var args = new Object();
						args.post_id  = post_id;
						args.meta_key = meta_key;
						args.once 	  = once;
						args.response = response;
						args.This 	  = This;
					
					maxgrid_ajaxSetMetaCount(args);
					
					if ( This[0].nodeName === 'I') {
						setTimeout(function() {
							This.removeClass('is-downloading');
							This.find('.dld-spinner').css('display', 'none');
						}, 1000);
					}
				} else {
					swal({
						text: Const.be_logged_in_alert,
						buttons: {
							cancel: true,
							confirm: "Login",
						  },
					})
					.then(function(willLogged) {
						if (willLogged) {
							window.location.replace(Const.login_page+'?redirect_to='+window.location.href);
						}
					});
				
					$('.ajax_dl-spiner').html('');
					$('.ajax_dl-spiner').removeClass('visible');
				}				
			}
		});
	});
	
	/*-------------------------------------------------------------------------*/
	/*	Set Cookie
	/*-------------------------------------------------------------------------*/

	$('body').on('click','input.list_view, input.grid_view, input.asc_sort, input.desc_sort', function() {
		var cname = $(this).attr('name')+'_'+get_grid_id($(this));
		maxgrid_setCookie({cname: cname, value: $(this).val()});
	});
	
	$('body').on('click','.maxgrid_grid_container a.read-more, a.maxgrid_title, a.featured-link', function() {
		var cname = 'id_page_'+get_grid_id($(this));
		maxgrid_setCookie({cname: cname, value: $(this).attr('data-page-pid')});
	});
	
	/*-------------------------------------------------------------------------*/
	/*	Construct GRID
	/*-------------------------------------------------------------------------*/
	
	try{
		
		$('body').on('click','.gd-post-catname', function(e) {
			e.preventDefault();
			var Container 	= $(this).closest('.maxgrid-body').find('.maxgrid_grid_container')[0],
				ID 		  	= $(this).closest('.maxgrid-body').attr('data-g-uid'),
				slugcatlink = $(this).attr('data-catslug');
			load_posts({Container: Container, ID: ID, slugcatlink: slugcatlink, Layout: maxgrid_getLayout(ID)});
		});
		
		$('body').on('click','.gd-post-author', function(e) {
			e.preventDefault();
			var Container 	= $(this).closest('.maxgrid-body').find('.maxgrid_grid_container')[0],
				ID 		  	= $(this).closest('.maxgrid-body').attr('data-g-uid'),
				AuthorIDget = $(this).attr('data-authorname');
			load_posts({Container: Container, ID: ID, AuthorIDget: AuthorIDget, Layout: maxgrid_getLayout(ID)});
		});
		
		var ID, gridContainer = document.querySelectorAll('.maxgrid_grid_container');		
		for (var i = 0; i < gridContainer.length; i++) {
			
			Container = gridContainer[i];
			ID = get_grid_id($(Container));
			
			var idPage;
			if ( maxgrid_getCookie("id_page_"+ID) ) {
				idPage = maxgrid_getCookie("id_page_"+ID);
			} else {
				idPage = 1;
			}
			
			// Reload if maxgrid_GET['category']
			var parm = maxgrid_getQueryString('category');
			if (parm) {
				$(Container).closest('.maxgrid-body').find('.chosen_category').val(parm);
				$(Container).closest('.maxgrid-body').find('.chosen_category').trigger("chosen:updated");
			}
			
			load_posts({Container: Container, ID: ID, idPage: idPage, Layout: maxgrid_getLayout(ID)});
			
			if ($(Container).closest('.maxgrid-body').find('.chosen_category, .chosen_tags').val() === null ) {
				$('.show-all-cat-btn').fadeOut();
			}
		}
		
		// Prevent if no Milti Grid support ( Max Grid not installed )
		var max_grid_id = document.querySelectorAll('[data-g-uid="max-grid-id"]');		
		for (var x = 0; x < max_grid_id.length; x++) {
			if ( x >= 1 ) {
				$(max_grid_id[x]).remove();
			}
		}		
		
		$('.show-all-cat-btn').on('click', function() {
			$(this).fadeOut();
			var Container = $(this).closest('.maxgrid-body').find('.maxgrid_grid_container')[0],
				ID 		  = get_grid_id($(this));
			$(this).closest('.maxgrid-body').find('.chosen_category, .chosen_tags').val('').trigger("chosen:updated");
			load_posts({Container: Container, ID: ID, Layout: maxgrid_radioReader('layout')});
			window.history.pushState({}, document.title, '?category=');
		});

		$('.chosen_category, .chosen_tags').on('change', function() {
			if ($(this).val() === null ) {
				$(this).closest('.maxgrid-body').find('.show-all-cat-btn').fadeOut();
			}
		});
		
		$('.asc_sort, .desc_sort').on('click', function(e) {
			e.preventDefault();
			var Container = $(this).closest('.maxgrid-body').find('.maxgrid_grid_container')[0],
				ID 		  = get_grid_id($(this));
			
			load_posts({Container: Container, ID: ID, Layout: maxgrid_radioReader('layout')});
		});

		$('.list_view, .grid_view').on('click', function(e) {
			e.preventDefault();
			var Container = $(this).closest('.maxgrid-body').find('.maxgrid_grid_container')[0],
				ID 		  = get_grid_id($(this));
			
			load_posts({Container: Container, ID: ID, Layout: $(this).val()});
		});
		
		$('.chosen_orderby').chosen({disable_search_threshold: 10},{width: "150px"}).on('change', function(e) {
			e.preventDefault();
			var Container = $(this).closest('.maxgrid-body').find('.maxgrid_grid_container')[0],
				ID 		  = get_grid_id($(this));
			
			var idPage = $(this).attr("data-page");
			if ( $(this).attr("data-post-type") === 'youtube_stream' ) {
				load_posts({Container: Container, ID: ID, Layout: maxgrid_radioReader('layout'), notAppend: true});
			} else {
				load_posts({Container: Container, ID: ID, Layout: maxgrid_radioReader('layout')});
			}			
		});

		$('.chosen_category, .chosen_tags').chosen({disable_search_threshold: 10},{width: "95%"}).on('change', function(e) {
			e.preventDefault();
			var Container = $(this).closest('.maxgrid-body').find('.maxgrid_grid_container')[0],
				ID 		  = get_grid_id($(this));
			load_posts({Container: Container, ID: ID, Layout: maxgrid_radioReader('layout')});
		});
				
		var ajax_Complete  = null;		
		window.ajaxRunning = null;
		window.gCTN 	   = null;
		
		// Construct Post GRID - Post, Product and Download posts
		function load_posts(args) {
			
			var defaultParams = { Container: '', ID: '', idPage: 1, slugcatlink: '', AuthorIDget: ""},
				action, gData, PPP, params = defaultParams;
			
			for (var key in args) {
				if (args.hasOwnProperty(key)) {
					if (args[key] !== undefined) {
						params[key] = args[key];
					}
				}
			}
			
			var gCTN 	  = $(params.Container),				
				gID 	  = params.ID,
				gBody  	  = gCTN.closest('.maxgrid-body'),
				gFormData = gBody.find('.get-form-data'),
				Layout 	  = params.Layout ? params.Layout : maxgrid_getLayout(gID),
				page_id   = params.idPage;
			
			PPP = Layout === 'list' ? gBody.attr('data-list-ppp') : gBody.attr('data-grid-ppp');
			
			var offset 	 = (parseInt(page_id) * PPP ) - (PPP - 1),
				page 	 = page_id,
				catlink  = params.slugcatlink,
				authorid = params.AuthorIDget,
				filter 	 = $('#epg_builder_filter'),
				showAll;
			
			if ( gBody.find('.chosen_category').val() !== null || gBody.find('.chosen_tags').val() !== null ) {
				showAll = 'show';
			} else {
				showAll = 'hide';
			}
			
			var post_type 	 	 = gFormData.attr('data-post-type'),
				items_per_row 	 = gFormData.attr('data-items-pr');
			window.post_type 	 = post_type;
			window.gCTN 	 	 = gCTN;
			window.items_per_row = items_per_row;
			eqcss();
			var formData  = {
					post_type  		: post_type,
					preview_mode	: gFormData.attr('data-preview-mode'),
					page_id  		: gFormData.attr('data-page-id'),
					orderby  		: gBody.find('.chosen_orderby').val(),
					categoryfilter  : gBody.find('.chosen_category').val(),
					tag  			: gBody.find('.chosen_tags').val(),
					preset_name		: gFormData.attr('data-preset-name'),
					pagination  	: gFormData.attr('data-pagination'),
					masonry_layout  : gFormData.attr('data-masonry'),
					items_per_row  	: items_per_row,
					source_type		: gFormData.attr('data-ytb-type'),
					source_id		: gFormData.attr('data-ytb-id'),
					ytb_tag			: gFormData.attr('data-ytb-tag'),
					RetrieveHD		: gFormData.attr('data-ret-hd'),
					grid_container  : gFormData.attr('data-grid-container'),
					list_container	: gFormData.attr('data-list-container'),
				};			
			
			var order  = $("[data-g-uid="+gID+"] input[name='order']:checked").val(),
				layout = $("[data-g-uid="+gID+"] input[name='layout']:checked").val();
			
			if ( post_type !== 'youtube_stream' ) {
				formData.order			= order !== undefined ? order : 'DESC';
				formData.layout			= layout !== undefined ? layout : 'grid';
				formData.action			= gFormData.attr('data-action');
				formData.default_layout	= gFormData.attr('data-dflt-view');
				formData.ribbon			= gFormData.attr('data-ribbon');
				formData.full_content	= gFormData.attr('data-full-content');
				formData.excl_cat		= gFormData.attr('data-excl-cats');
				formData.all_categories	= gFormData.attr('data-all-cats');
				formData.all_tags		= gFormData.attr('data-all-tags');
			}
			
			var varData = Object.keys(formData).map(function(key) {
				return key + '=' + formData[key];
			}).join('&');
			
			gData = varData + '&ppp=' + PPP + '&offset=' + offset + '&page=' + page + '&catlink=' + catlink + '&authorid=' + authorid + '&layout=' + Layout;
			
			if ( post_type === 'youtube_stream' ) {
				Layout = 'grid';
				action = '&action=maxgrid_' + post_type + '_filter';
				gData  = action + '&' + varData + '&ppp=' + PPP + '&page=' + page;
				
			}
			
			if ( gBody.find('.load-more').attr('data-end-page') === 'end' ) {
				return true;
			}
						
			$.ajax({
				url : filter.attr('action'),
				data: gData,
				type: filter.attr('method'),
				beforeSend:function(xhr) {
					var pagination = gBody.find('.maxgrid_grid_container').attr('data-pagination');					
					var html = '<div class="dots-rolling"><span></span><span></span><span></span></div>';
					if ( pagination === 'load_more_button' ||  pagination === 'infinite_scroll' ) {
						gBody.find('.load-more').html('<span class="out-me visible">'+Const.load_more+'</span>'+html);
					}
					
					gCTN.addClass('ajax-onloading');
					if ( pagination === 'infinite_scroll' ) {
						gBody.find('.load-more_container.post').css('display', 'block');
					}
					
					var args = new Object();
						args.size = 'large';
						args.color = 'grey';
					
					if ( gCTN.html() === '' || params.notAppend === true ) {						
						var masonryClass = gFormData.attr('data-masonry') === 'on' ? ' masonry-grid-layout' : '';
						gCTN.html('<div id="grid_response" class="ajax-grid_response grid-layout-row' + gFormData.attr('data-grid-container') + masonryClass + '">' + maxgrid_lds_rolling_loader(args) + '</div>');
					} else if ( pagination !== 'load_more_button' && pagination !== 'infinite_scroll' ) {
						//gCTN.append(maxgrid_lds_rolling_loader(args));
						
					} else {
						gCTN.append(maxgrid_lds_rolling_loader(args));
					}					
				},
				success:function(data) {
					
					ajax_Complete  = true;					
					
					// General options
					var d = document.createElement('div');
					
					d.innerHTML	   = data;
					var next_page  = d.firstChild.getAttribute('data-page'),
						max_page   = d.firstChild.getAttribute('data-max-page'),
						pagination = d.firstChild.getAttribute('data-pagination');
					
					// if Youtube Stream
					var elements   = $(data),
						nextPageToken = $('.block-grid-container', $(data)).attr('data-next-page-token');
					
					if ( post_type === 'youtube_stream' && nextPageToken !== undefined && pagination === 'infinite_scroll' ) {						
						gBody.find('.load-more.ytb').attr('data-page', nextPageToken);
					} else {
						gBody.find('.load-more').attr('data-page', next_page).attr('data-max-page', max_page);
						
					}
					
					if ( $('.block-grid-container', elements).attr('data-prev') !== '' && nextPageToken === '' ) {
						gBody.find('.load-more').attr('data-end-page', 'end');
					}
					
					if ( parseInt(next_page) === parseInt(max_page) || pagination === 'numeric_pagination' ) {
						gBody.find('.load-more').remove();
					} else {
						gBody.find('.load-more').html(Const.load_more);
						gBody.find('.load-more').css('display', 'inline-block');
					}
					
					next_page = nextPageToken === undefined ? next_page : 2;
					
					window.gCTN = gCTN;
					
					if ( parseInt(next_page) > 1 && pagination !== 'numeric_pagination' ) {	
						gBody.find('.ng-scope').remove();
						if ( Layout === 'grid' ) {
							gCTN.find('.ajax-grid_response').append(d.firstChild.innerHTML);						
						} else if ( Layout === 'list' ) {
							gCTN.find('.ajax-list_response').append(d.firstChild.innerHTML);
						}
						
						$.ajax({
							url : filter.attr('action'),
							data: gData + '&page=' + nextPageToken,
							type: filter.attr('method')
						});
					} else {
						gCTN.html(data);
					}
					
					if ( Layout === 'grid' ) {
						gCTN.addClass('mg-grid__view');
					} else {
						gCTN.removeClass('mg-grid__view');
					}
					
					gCTN.removeClass('ajax-onloading');
					
					// Initialize new media elements.
					window.wp.mediaelement.initialize();
					gBody.find('.maxgrid_grid_container > div').css('padding-bottom', '0');
					if ( pagination === 'infinite_scroll' ) {
						gBody.find('.load-more_container.post').css('display', 'none');						
						gBody.find('.load-more_container.post').css('margin-top', '-160px');
					}	

					gBody.find('.masonry-grid-layout').css('min-height', 'unset');
					gBody.find('.maxgrid_grid_container').css('min-height', 'unset');

					if ( catlink !== '' || authorid !== '' || showAll == 'show' ) {
						gBody.find(".show-all-cat-btn").fadeIn();
					}					
					
					// Set Value to "layout" button radio
					if (post_type === 'youtube_stream') {
						return true;
					}
					var listRadiobtn = gBody.find('.list_view')[0];
					var gridRadiobtn = gBody.find('.grid_view')[0];
					
					if ( Layout === 'grid') {
						listRadiobtn.checked = false;
						gridRadiobtn.checked = true;
					} else if ( Layout === 'list') {
						listRadiobtn.checked = true;
						gridRadiobtn.checked = false;
					}
					
					var ascRadiobtn  = gBody.find('.asc_sort')[0];
					var descRadiobtn = gBody.find('.desc_sort')[0];
					
					if ( maxgrid_getCookie('order_'+gID) === 'ASC') {
						ascRadiobtn.checked = true;
						descRadiobtn.checked = false;
					} else {
						ascRadiobtn.checked = false;
						descRadiobtn.checked = true;
					}
					
					// Disable 3d wrapped effect on ribbon if is it stuck on featured
					$('.corner-ribbon').each(function(i, obj) {
						if ( obj.parentElement.className.indexOf('video-wrapper') !== -1 ) {
							obj.classList.remove('wrapped');
						}
					});
					
					if( maxgrid_GET('onclickbtn')){
						$('#maxgrid_download_single[data-post-id="'+maxgrid_GET('onclickbtn')+'"]').click();
					}

				},				
			});

			return false;
			//});
		}
		
		// Masonry Update
		var masonryUpdate = function() {
			setTimeout(function() {
				$('.masonry-grid-layout').masonry({
					itemSelector : '.block-grid-container',
					//columnWidth	 : '.block-grid-container'
			   });
				$('.masonry-grid-layout').masonry( 'reloadItems' ).masonry( 'layout' );
			}, 10);
		};
		
		// AJAX Complete
		$(document).ajaxComplete(function() {
			
			// ajax add to cart button
			// remove spinner and reintialize button label
			if(window.addtoCartBtnLabel){
				$('.add-to-cart-spinner div').remove();
				window.ThisATC.html(window.addtoCartBtnLabel);
			}
			
			if(ajax_Complete) {				
				masonryUpdate();
				
				var timesRun = 0;
				var interval = setInterval(function(){
					timesRun += 1;
					if(timesRun === 5){
						clearInterval(interval);
					}
					masonryUpdate();
				}, 150);
				
				$('#block_filter').css('opacity', '1');

				if ( pagination === 'infinite_scroll' ) {
					window.ajaxRunning = true;
				}
				eqcss();		
			}			
			ajax_Complete = null;
		});
		
	}
	catch(e) {
		console.log('Error: ' + e);
	}
	
	window.loadMore  = null;
	function eqcss() {
		var gCTN 		= window.gCTN,
			forced_iRow = parseInt(window.items_per_row);
		if( gCTN === null ){
			return;
		}
		var gBody 	= gCTN.closest('.maxgrid-body'),
			iRow 	= parseInt(gCTN.attr('data-old-items-per-row'), 10),
			cWidth 	= gCTN.width();
		
		if( window.loadMore ){			
			var oldIRow = gBody.attr('data-items-per-row');
			iRow = oldIRow !== undefined ? parseInt(oldIRow) : parseInt(gCTN.attr('data-old-items-per-row'), 10);
		}
		
		if ( isBetween(cWidth, 1400, 1200) && iRow > 5 ) {
			iRow = 5;
		} else if ( isBetween(cWidth, 1200, 900) && iRow > 4 ) {
			iRow = 4;
		} else if ( isBetween(cWidth, 900, 650) && iRow > 3 ) {
			iRow = 3;
		} else if (isBetween(cWidth, 650, 425) && iRow > 2 ) {
			iRow = 2;
		} else if (cWidth < 425 && iRow > 1 ) {
			iRow = 1;
		}
		
		var newclass='', classes = gBody[0].className.split(' ');
		
		for (var x = 0; x < classes.length; x++) {
			if( classes[x].indexOf('natural-irow-') >-1 ){
				continue;
			}
			newclass += ' '+classes[x];
		}
		
		gBody.attr('class', newclass);
		
		
		if ( isBetween(cWidth, 1400, 1200) && forced_iRow < 5 ) {
			gBody[0].classList.add('natural-irow-5');
		} else if ( isBetween(cWidth, 1200, 900) && forced_iRow < 4 ) {
			gBody[0].classList.add('natural-irow-4');
		} else if ( isBetween(cWidth, 900, 650) && forced_iRow < 3 ) {
			gBody[0].classList.add('natural-irow-3');
		} else if (isBetween(cWidth, 650, 425) && forced_iRow < 2 ) {
			gBody[0].classList.add('natural-irow-2');
		}
				
		gBody[0].setAttribute('data-items-per-row', iRow);
	}
	
	function isBetween(n, a, b) {
	   	return (n - a) * (n - b) <= 0;
	}
	
	// Youtube Stream - Numeric navigation
	jQuery('.maxgrid-navigation.youtube a').live('click', function(e){
		console.log('This is temp test!');
		e.preventDefault();
		idPage = $(this).attr("data-href");
	 });
	
	/*-------------------------------------------------------------------------*/
	/*	GRID Pagination
	/*-------------------------------------------------------------------------*/
	
	jQuery('.maxgrid-navigation:not(.youtube) a').live('click', function(e){
		e.preventDefault();
		
		var Container = $(this).closest('.maxgrid-body').find('.maxgrid_grid_container')[0],
			idPage 	  = $(this).attr("data-href"),			
			ID 		  = get_grid_id($(this));
		
		load_posts({Container: Container, ID: ID, idPage: parseInt(idPage), Layout: maxgrid_getLayout(ID)});
	 });
	
	// Load More
	$("body").on("click", ".load-more:not(.ligthbox-playlist)", function(e) {		
		e.preventDefault();
		window.loadMore  = true;
		var Container = $(this).closest('.maxgrid-body').find('.maxgrid_grid_container')[0],
			idPage 	  = $(this).attr("data-page"),
			ID 		  = get_grid_id($(this));
		
		if ( $(this).attr("data-post-type") === 'youtube_stream' ) {
			load_posts({Container: Container, ID: ID, idPage: idPage, Layout: maxgrid_getLayout(ID)});
		} else {
			
			load_posts({Container: Container, ID: ID, idPage: parseInt(idPage)+1, Layout: maxgrid_getLayout(ID)});
		}
	});
	
	// Infinite Scroll
	var pagination = $('#maxgrid_grid_container').attr('data-pagination');
	var post_type = $('#maxgrid_grid_container').attr('data-post-type');	
	if ( pagination === 'infinite_scroll' ) {
		var deviceAgent = navigator.userAgent.toLowerCase();
		var agentID = deviceAgent.match(/(iphone|ipod|ipad)/);		
		$(window).scroll(function() {
			
			var cond1 = ($(window).scrollTop() + $(window).height()) === $(document).height(),
				cond2 = agentID && ($(window).scrollTop() + $(window).height()) + 150 > $(document).height(),
				cond3 = ($(window).scrollTop() + $(window).height()) === maxgrid_getDocHeight()-1,
				cond4 = agentID && ($(window).scrollTop() + $(window).height()) + 150 > maxgrid_getDocHeight()-1;			
			if( cond1 || cond2 || cond3 || cond4 ) {
				
				var This 	  = $('[data-pagination="infinite_scroll"]'),
					gBody 	  = This.closest('.maxgrid-body'),
					ID 		  = get_grid_id(This),
					post_type = gBody.find('.maxgrid_grid_container').attr('data-post-type'),
					data_page = gBody.find('.load-more').attr('data-page'),
					page 	  = parseInt(gBody.find('.load-more').attr("data-page"))+1,
					winMaxPage = gBody.find('.load-more').attr("data-max-page"),
					maxPage   = parseInt(winMaxPage);
				
				if ( ajaxRunning ) {
					
					if ( post_type === 'youtube_stream' && data_page !== '' ) {
						page  = data_page;
					}
					if ( page < maxPage || post_type === 'youtube_stream' ) {
						load_posts({Container: gBody.find('.maxgrid_grid_container')[0], ID: ID, idPage: page, Layout: maxgrid_getLayout(ID)});					
					}
					window.ajaxRunning = false;					
				}
			}
		});
	}
	
	/*-------------------------------------------------------------------------*/
	/*	Woocommerce
	/*-------------------------------------------------------------------------*/
	
	// Add to cart with quantity - AJAX
	
	// This button will increment the value
    $("body").on("click", ".plus-minus-button.plus", function(e) {
        e.preventDefault();
		var currentVal = parseInt($(this).prev().val());
        if (!isNaN(currentVal)) {
			$(this).prev().val(currentVal + 1);
			// when product quantity changes, update quantity attribute on add-to-cart button
			$(this).closest('div').next().data("quantity", currentVal + 1);
        } else {
            $(this).prev().val(1);
			$(this).closest('div').next().data("quantity", 1);
        }
    });
	
    // This button will decrement the value till 0
	$("body").on("click", ".plus-minus-button.minus", function(e) {
        e.preventDefault();
		var currentVal = parseInt($(this).next().val());
		
        if (!isNaN(currentVal) && currentVal > 1) {
			$(this).next().val(currentVal - 1);
			// when product quantity changes, update quantity attribute on add-to-cart button
			$(this).closest('div').next().data("quantity", currentVal - 1);
        } else {
            $(this).next().val(1);
			$(this).closest('div').next().data("quantity", 1);
        }
		
    });
		
	// On keyup
	$("body").on("keyup", "input.input-group-field", function(e) {
        e.preventDefault();
		var currentVal = parseInt($(this).val());		
        if (!isNaN(currentVal) && currentVal > 1) {			
			// when product quantity changes, update quantity attribute on add-to-cart button
			$(this).closest('div').next().data("quantity", currentVal);			
        } else {
            $(this).val(1);
			$(this).closest('div').next().data("quantity", 1);
        }	
    });
	
	/*-------------------------------------------------------------------------*/
	/*	Sharthis button with Popup
	/*-------------------------------------------------------------------------*/

	$('body').on('click','#share-trigger', function(e) {
		e.stopPropagation();		
		var gBody, gridID;
		//
		
		if ( $(this).attr('data-id') !== undefined && $(this).attr('data-id').indexOf('stats_bar') > -1 ) {
			// if triger is grid stats bar
			gridID = get_grid_id($(this));
		} else {
			// if triger is lightbox
			gridID = $('#maxgrid_lightbox_modal').attr('data-g-uid');
		}
		
		var gridBody = document.querySelectorAll('.maxgrid-body');		
		for (var i = 0; i < gridBody.length; i++) {
			if (gridBody[i].getAttribute('data-g-uid') === gridID ) {
				gBody = $(gridBody[i]);
			}
		}
		
		window.prev_class = $(this).find('.share-popup-container').attr('class');
		$('.share-popup-container').removeClass('focused');
		
		var This 	  = $(this),
			id 		  = This.attr('data-id'),
			post_id   = This.attr('data-post-id'),
			count     = This.attr('data-count'),
			post_type = This.attr('data-post-type'),
			pslug 	  = gBody !== undefined ? gBody.find('.get-form-data').attr('data-preset-name') : '';		
		
		if ( This.html().indexOf('pg_share-popup') === -1 ) {
			$.ajax({
				type : "POST",
				 url : Const.ajaxurl,
				 data : {
					 action	   : 'maxgrid_get_sharethis_content',
					 id   	   : id,
					 post_id   : post_id,
					 post_type : post_type,
					 pslug 	   : pslug,
				 },
				beforeSend:function(xhr){
					var args = new Object();
						args.color = 'grey';
						args.size = 'small';
					
					var Class = updatePopupPosition(This[0]);
					var Width = (40*count)+(32+30);					
					This.prepend('<div style="width: '+Width+'px; height: 227px;" id="pg_share-popup" class="share-popup-container focused'+Class+'">' + maxgrid_lds_rolling_loader(args) + '</div>');	
				},
				success: function(response) {
					This.find('.share-popup-container').remove();
					This.prepend(response);
					shareThisPopupShow(This);
				}
			});
		} else {
			shareThisPopupShow(This);
		}
	});
	
	function shareThisPopupShow(This){
		This.find('.share-popup-container').removeClass('to-the-left to-the-right to-the-top to-the-bottom' );
		if( prev_class && prev_class.indexOf('focused') !== -1 ) {
			This.find('.share-popup-container').removeClass('focused');
		} else {
			This.find('.share-popup-container').addClass('focused');
		}
		$('input.share-input').select();
		$('input.share-input').val(This.find('input.share-input').attr('data-href'));
		var Class = updatePopupPosition(This[0]);		
		This.find('#pg_share-popup').addClass(Class);
	}
	
	function updatePopupPosition(DOM_Element) {		
		var PopupClass = '';
		if ( getCoords(DOM_Element).left > getCoords(DOM_Element).right ) {
			PopupClass += ' to-the-left';
		} else if ( getCoords(DOM_Element).left < getCoords(DOM_Element).right ) {			
			PopupClass += ' to-the-right';
		}
		if ( getCoords(DOM_Element).top > 420 ) {
			PopupClass += ' to-the-top';
		} else {
			PopupClass += ' to-the-bottom';
		}
		return PopupClass;
	}

	// Close Share Popup
	$('body').on('click','.icon-cross', function() {
		$('.share-popup-container').removeClass('focused');
	});
	
	window.addEventListener('click', function(e){  
	
	  if ( document.getElementById('pg_share-popup') && !document.getElementById('pg_share-popup').contains(e.target) ) {
		$('.share-popup-container').removeClass('focused');
		$('input.share-input').css('border-bottom-color', '#4a4a4a');
	  }
	});
	
	$('body').on('click','#maxgrid_lightbox_modal, .single-type-download', function() {
		$('.share-popup-container').removeClass('focused');
		$('input.share-input').css('border-bottom-color', '#4a4a4a');
	});
	
	$('body').on('click','.share-popup-container', function(e) {
		e.stopPropagation();
	});
 	
	// Get document coordinates of the element
	function getCoords(elem) {
		var docHeight = $(window).height();
		var docWidth  = $(window).width();
		var box 	  = elem.getBoundingClientRect();		
		return {
			top: box.top,
			bottom: docHeight - box.top + pageYOffset,
			left: box.left + pageXOffset,
			right: docWidth - box.left + pageXOffset,
		};
	}

	// Copy to clipboard
	$('body').on('click','.copy-to-clipboard-button', function() {
		$('input.share-input').val($('input.share-input').attr('data-href'));
		$('input.share-input').select();
		$('input.share-input').css('border-bottom-color', '#2793e6');		
		document.execCommand( 'copy' );
		$(this).html(Const.copied);
		setTimeout(function(){
			$('.copy-to-clipboard-button').html(Const.copy);
		}, 2000);
	});
	
});

/*---------------------------------------------------- */
/*	Direction-Aware Hover Effect - JS
/*---------------------------------------------------- */

(function ($) {
    $.fn.directionalHover = function(options) {
        // Extend default plugin options
        var opts = $.extend({}, $.fn.directionalHover.defaults, options);

        // Create bit flags
        var FLAG_T = 1, // top
            FLAG_R = 2, // right
            FLAG_B = 4, // bottom
            FLAG_L = 8; // left

        // Create bit masks
        var tlMask = FLAG_T | FLAG_L,
            trMask = FLAG_T | FLAG_R,
            blMask = FLAG_B | FLAG_L,
            brMask = FLAG_B | FLAG_R;

        function slideOverlay(overlay, direction, px, py, ew, eh, ex, ey) {
            var cornerFlags = 0; // top|right|bottom|left

            if (py - ey <= eh / 2) cornerFlags ^= FLAG_T;
            if (px - ex >= ew / 2) cornerFlags ^= FLAG_R;
            if (py - ey >  eh / 2) cornerFlags ^= FLAG_B;
            if (px - ex <  ew / 2) cornerFlags ^= FLAG_L;

            findSide(cornerFlags, overlay, direction, px-ex, py-ey, ew/2, eh/2);
        }

        function findSide(flags, overlay, direction, x, y, w, h) {
            if (testMask(flags, tlMask)) {
                testTopLeftToBottomRight(x, y, w, h) ? setOverlayPosition(overlay, direction, 0, -w*2) : setOverlayPosition(overlay, direction, -h*2, 0);
            }
            else if (testMask(flags, trMask)) {
                testBottomRightToTopLeft(x, y, w, h) ? setOverlayPosition(overlay, direction, -h*2, 0) : setOverlayPosition(overlay, direction, 0, w*2);
            }
            else if (testMask(flags, blMask)) {
                testBottomRightToTopLeft(x, y, w, h) ? setOverlayPosition(overlay, direction, 0, -w*2) : setOverlayPosition(overlay, direction, h*2, 0);
            }
            else if (testMask(flags, brMask)) {
                testTopLeftToBottomRight(x, y, w, h) ? setOverlayPosition(overlay, direction, h*2, 0) : setOverlayPosition(overlay, direction, 0, w*2);
            }
        }

        function testMask(flags, mask) {
            return (flags & mask) === mask;
        }

        function testTopLeftToBottomRight(x, y, w, h) {
            return (h * x - w * y) < 0;
        }

        function testBottomRightToTopLeft(x, y, w, h) {
            return (w * (y-h) + h * x - w * h) < 0;
        }

        function setOverlayPosition(overlay, direction, top, left) {
            if (direction === 'in') {
                overlay.animate({
                    top: top,
                    left: left
                }, 0, function() {
                    overlay.stop().animate({
                        top: 0,
                        left: 0
                    }, opts.speed, opts.easing);
                });
            }
            else if (direction === 'out') {
                overlay.animate({
                    top: 0,
                    left: 0
                }, 0, function() {
                    overlay.stop().animate({
                        top: top,
                        left: left
                    }, opts.speed, opts.easing);
                });
            }
        }

        this.css({
            position: 'relative',
            overflow: 'hidden'
        });

        this.find('.' + opts.overlay).css({
            position: 'absolute',
            top: '-100%'
        });

        return this.each(function() {
            var container = $(this);

            container.hover(function(e) {
                slideOverlay(
                    container.find('.' + opts.overlay),
                    'in',
                    e.pageX,
                    e.pageY,
                    container.width(),
                    container.height(),
                    Math.floor(container.offset().left),
                    container.offset().top
                );
            }, function(e) {
                slideOverlay(
                    container.find('.' + opts.overlay),
                    'out',
                    e.pageX,
                    e.pageY,
                    container.width(),
                    container.height(),
                    Math.floor(container.offset().left),
                    container.offset().top
                );
            });
        });
    };

    // Plugin default options
    $.fn.directionalHover.defaults = {
        overlay: "dh-overlay",
        easing: "swing",
        speed: 400
    };

}(jQuery));

/*-------------------------------------------------------------------------*/
/*	Functions
/*-------------------------------------------------------------------------*/

// Get layout cookie
function maxgrid_getLayout(id) {
	var layout;
	if ( maxgrid_getCookie("layout_"+id) === 'grid' ) {
		layout = 'grid';
	} else if ( maxgrid_getCookie("layout_"+id) === 'list' ) {
		layout = 'list';
	} else {
		layout = 'grid';
	}
	return layout;
}

window.twttr = (function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0],
		t = window.twttr || {};
	if (d.getElementById(id)) return t;
	js = d.createElement(s);
	js.id = id;
	js.src = "https://platform.twitter.com/widgets.js";
	fjs.parentNode.insertBefore(js, fjs);

	t._e = [];
	t.ready = function(f) {
		t._e.push(f);
	};
	
	return t;
}(document, "script", "twitter-wjs"));

function maxgrid_getDocHeight() {
    var D = document;
    return Math.max(
        D.body.scrollHeight, D.documentElement.scrollHeight,
        D.body.offsetHeight, D.documentElement.offsetHeight,
        D.body.clientHeight, D.documentElement.clientHeight,
        /* For opera: */
        document.documentElement.clientHeight
    );
}

function maxgrid_radioReader(radioName){
	var radios = document.getElementsByName(radioName);
	for (var i = 0, length = radios.length; i < length; i++) {
		if (radios[i].checked) {
			return radios[i].value;
			break;
		}
	}
}

// Share This Function
function maxgrid_popupShare(h) {
	var x = screen.width/2 - 600/2;
	var y = screen.height/2 - 450/2;
	window.open(h.href, 'sharegplus','height=485,width=600,left='+x+',top='+y);
}