/* ------------------------------------------------------------------------
	Max Grid Lightbox
	Version: 1.0.0
------------------------------------------------------------------------- */

jQuery(document).ready(function($){
	
	// Get Grid ID
	function get_grid_id(triger){
		return triger.closest('.maxgrid-body').attr('data-g-uid');
	}
	
	// Check if playlist is already loaded
	function slider_items_checker(){
		var gridID 		= $('#maxgrid_lightbox_modal').attr('data-g-uid');
		var prev_gridID = $('.playlist-slider_container').attr('data-prev-g-uid'),
			orderby 	= $('[data-g-uid="'+gridID+'"]').find('.chosen_orderby').val();
		$('.playlist-slider_container').attr('data-prev-g-uid', gridID);
		return $('.playlist-slider_container').html() === '' || orderby !== $('.playlist-slider_container .ul-thumbs-list').attr('data-orderby') || ( prev_gridID !== undefined && prev_gridID !== gridID );
	}
	
	// Fake Slideshow
	function update_fake_slideshow() {
		var winWidth = $(window).width();
		if($('#maxgrid_lightbox_modal').attr('class').indexOf('slideshow-visible') > -1 ) {
			winWidth = $(window).width() - $('.playlist-slider_container').outerWidth();
		}
		var marg = winWidth - $('.single-type-download').innerWidth();		
		$('.fake-slideshow.next-el').css('left', 'calc(100% + '+marg+'px)');
		$('.fake-slideshow.prev-el').css('right', 'calc(100% + '+marg+'px)');
	}
	
	// Slideshow navigations
	function nav_arrow_show_hide(el, Target) {
		
		if ( !Target ){
			Target = 'playlist-slider_container';
		}	
		var This, next_id,
			active = $('.'+Target+' ul li.active'),
			first_use = false,
			getThis = $('.li-post-thumb.active');
		if(el){
			first_use = true;
			getThis = el;		
		}		
		if ( active.next().attr('data-id') === $('ul.ul-thumbs-list li').last().attr('data-id') ){
			$('#maxgrid_lightbox_modal #next').removeClass('isvisible');
			$('.ismobile-nav #next').removeClass('isvisible');
		}
		if ( active.prev().attr('data-id') === $('ul.ul-thumbs-list li').first().attr('data-id') ){
			$('#maxgrid_lightbox_modal #prev').removeClass('isvisible');
			$('.ismobile-nav #prev').removeClass('isvisible');
		}		
		if(getThis.attr('data-id')) {
			This = getThis;
			next_id = getThis.attr('data-id');
			active.removeClass('active');				
			if (getThis.attr('class') === 'li-post-description' ) {
				getThis.prev().addClass('active');
			} else {
				getThis.addClass('active');
			}			
		} else if(getThis.attr('data-target') === 'next') {
			This = active.next();
			next_id = This.attr('data-id');			
			if (next_id === active.attr('data-id')) {
				This = This.next();
				next_id = This.attr('data-id');
			}			
			active.removeClass('active');
			This.addClass('active');
			$('.li-post-description').removeClass('active');				
		} else if(getThis.attr('data-target') === 'prev') {
			This = active.prev();
			next_id = This.attr('data-id');
			if (next_id === This.prev().attr('data-id')) {
				This = This.prev();
				next_id = This.attr('data-id');
			}
			active.removeClass('active');
			This.addClass('active');
			$('.li-post-description').removeClass('active');
		}			
		var Next = This;
		if ( !This.next().attr('tabindex') ){
			Next = This.next();
		}		
		if (first_use) {			
			if ( !Next.next().attr('data-id')){
				$('#maxgrid_lightbox_modal #next, .ismobile-nav #next').removeClass('isvisible');
			} else {
				$('#maxgrid_lightbox_modal #next, .ismobile-nav #next').addClass('isvisible');
			}			
			if ( !This.prev().attr('data-id')){
				$('#maxgrid_lightbox_modal #prev, .ismobile-nav #prev').removeClass('isvisible');
			} else {
				$('#maxgrid_lightbox_modal #prev, .ismobile-nav #prev').addClass('isvisible');
			}			
			if ( next_id === undefined ) {
				el.removeClass('isvisible');
				return;
			}
			var gridID = $('#maxgrid_lightbox_modal').attr('data-g-uid');
			
			var target = $(el).attr('data-target');
			lightBoxLoad(gridID, next_id, This, target);
		} else {				
			if ( Next.next().attr('data-id') === undefined ){
				$('#maxgrid_lightbox_modal #next, .ismobile-nav #next').removeClass('isvisible');
			} else {
				$('#maxgrid_lightbox_modal #next, .ismobile-nav #next').addClass('isvisible');
			}
			if ( This.prev().attr('data-id') === undefined ){
				$('#maxgrid_lightbox_modal #prev, .ismobile-nav #prev').removeClass('isvisible');
			} else {
				$('#maxgrid_lightbox_modal #prev, .ismobile-nav #prev').addClass('isvisible');
			}
		}		
	}
	
	// Get youtube video content - description comments and stats	
	function get_ytb_content(lightbox_id, post_title, preset_name) {	
		var ajaxURL = Const.ajaxurl;
		jQuery.ajax({
				type : "POST",
				 url : ajaxURL,
				 data : {
					 action	: 'maxgrid_get_ytb_videos_info',
					 lightbox_id   : lightbox_id,
					 post_title    : post_title,
					 pslug	   	   : preset_name,
				 },
				beforeSend:function(xhr){
					var args = new Object();
					args.color = 'grey';
					args.size = 'medium';					
					$('.modal-bottom-content').html(maxgrid_lds_rolling_loader(args));
					if ($('.playlist-slider_container').html() === '' ) {						
						args.color = null;
						$('.playlist-slider_container').html(maxgrid_lds_rolling_loader(args));
					}					
				},
				success: function(data) {					
					$('.modal-bottom-content').html(data);
					if (!maxgrid_isMobile()){
						$('.ytb-comments .tab-playlist').css('display', 'none');
						$('.ytb-comments #tab-playlist').css('display', 'none');
					}
					if ( typeof tab_name !== 'undefined' ) {
						$('.reviews_tabs_container ul li.tab-description, .reviews_tabs_container ul li.tab-reviews, .reviews_tabs_container ul li.tab-playlist, .reviews_tabs_container #tab-description, .reviews_tabs_container #tab-reviews, .reviews_tabs_container #tab-playlist').removeClass('current');
						$('.reviews_tabs_container ul li.'+tab_name+', .reviews_tabs_container #'+tab_name ).addClass('current');
					}
					
					// Render Youtube Subscribe button
					maxgrid_renderYtSubscribeButton();
					
					// Reinitialize  getNiceScroll
					$("#reach_content_outer").getNiceScroll().show().resize();					
					if ( $('.playlist-slider_container').html() !== '' ) {
						if ( $('.playlist-slider_container ul') ) {
							var li_id;
							$('.playlist-slider_container ul li').each(function(i, obj) {						
								li_id = obj.getAttribute('data-id');
								if ( li_id === lightbox_id ) {
									obj.classList.add('active');									
									$('.navigation-arrow').addClass('isvisible');
								}
							});
						}
					}					
					if ( slider_items_checker() ) {
						get_slider_items();
					}
					$('.navigation-arrow').addClass('is-loaded');
				}
			 });
	}
	
	// Get playlist items
	function get_slider_items(nextPageToken, offset) {		
		var gBody, el = document.querySelectorAll('.maxgrid-body'),
			gridID 	  = $('#maxgrid_lightbox_modal').attr('data-g-uid');
		
		for (var i = 0; i < el.length; i++) {
			if (el[i].getAttribute('data-g-uid') === gridID ) {
				gBody = $(el[i]);
			}
		}
		var post_type = gBody.find('.chosen_orderby').attr('data-post-type'),
			action 	  = Const.maxgrid_ytb && post_type === 'youtube_stream' ? 'maxgrid_lightbox_rightside_ytb_items' : 'maxgrid_lightbox_rightside_items',
			gFormData = gBody.find('.get-form-data');
		jQuery.ajax({
				type : "POST",
				 url : Const.ajaxurl,
				 data : {
					 action		     : action,
					 post_type	     : post_type,
					 pslug	     	 : gFormData.attr('data-preset-name'),
					 grid_id     	 : gridID,
					 source_type   	 : gFormData.attr('data-ytb-type'),
					 source_id	     : gFormData.attr('data-ytb-id'),
					 order 		     : gBody.find('.chosen_orderby').val(),
					 tag 			 : gFormData.attr('data-ytb-tag'),
					 RetrieveHD 	 : gFormData.attr('data-ret-hd'),
					 ppp 		     : gBody.attr('data-grid-ppp'),
					 all_categories	 : gFormData.attr('data-all-cats'),
					 next_page_token : nextPageToken,
					 offset			 : offset,
				 },
				beforeSend:function(xhr) {
					if (nextPageToken === undefined ) {
						var args = new Object();
						args.size = 'medium';
						
						if (!maxgrid_isMobile()){
							$('.playlist-tab-content').html(maxgrid_lds_rolling_loader(args));
						} else {
							args.size = 'small';
							$('.playlist-tab-content').html(maxgrid_lds_rolling_loader(args));
						}
					} else {
						var html = '<div class="dots-rolling"><span></span><span></span><span></span></div>';
						$('.load-more.ligthbox-playlist').html('<span class="out-me visible">'+Const.load_more+'</span>'+html);
					}					
				},
				success: function(response) {
					
					$('.playlist-load-more-container').remove();
					
					if (nextPageToken === undefined ) {	
						if (!maxgrid_isMobile()){
							$('.playlist-slider_container').html(response);
						} else {
							$('.playlist-tab-content').html(response);
						}
					} else {
						var li_id;
						if (!maxgrid_isMobile()){
							$('.playlist-slider_container ul').append(response);
							$('#maxgrid_lightbox_modal #next').css('display', 'inline-block');							
						} else {
							$('.playlist-tab-content ul').append(response);							
						}						
					}					
					if ( response.indexOf('img data-src') === -1 ){
						$('.playlist-load-more-container').remove();
						var html = '<li class="playlist-load-more-container top-dashed"><span class="no-more-result">'+Const.no_more_result+'</span></li>';
						if (!maxgrid_isMobile()){
							$('.playlist-slider_container ul').append(html);						
						} else {
							$('.playlist-tab-content ul').append(html);							
						}
					}					
					var li_id;
					if (!maxgrid_isMobile()){
						$('.playlist-slider_container ul li').each(function(i, obj) {						
							li_id = obj.getAttribute('data-id');
							if ( li_id === lightbox_id ) {
								obj.classList.add('active');
								nav_arrow_show_hide();
							}
						});
						
						// Reload getNiceScroll script - in test
						setTimeout(function() {
						  $(".playlist-slider_container").getNiceScroll().show().resize();
						}, 550);

					} else {
						$('.playlist-tab-content ul li').each(function(i, obj) {						
							li_id = obj.getAttribute('data-id');
							if ( li_id === lightbox_id ) {
								obj.classList.add('active');
								nav_arrow_show_hide(null, 'playlist-tab-content');
								if (nextPageToken === undefined ) {
									$('.playlist-tab-content').animate({
											scrollTop: $('.playlist-tab-content .ul-thumbs-list li.li-post-thumb.active').position().top
									 }, 'slow');
								}
							}
						});
						$('.playlist-tab-content ul').attr('id', 'isMobile_ul-thumbs-list');
					}
					setTimeout(function(){
						if ( nextPageToken === undefined && $('.li-post-thumb.active').length !== 0 ) {
							$('.li-post-thumb.active')[0].scrollIntoView();
						}
					}, 600);
					
					var inputSearch = maxgrid_isMobile() ? $('.tab-playlist-search[data-is-mobile="on"]')[0] : $('.tab-playlist-search[data-is-mobile="off"]')[0];
					postPlaylistFilter(inputSearch);
					
					// Inisialize playlist-slider_container Scroll
					var timesRun = 0;
					var interval = setInterval(function(){
						timesRun += 1;
						if(timesRun === 12){
							clearInterval(interval);
						}
						$(".playlist-slider_container").getNiceScroll().show().onResize();
					}, 150);
				}
			 });
	}
	
	/*-------------------------------------------------------------------------*/
	/*	Load lightbox
	/*-------------------------------------------------------------------------*/
	
	$('body').addClass($('.maxgrid_grid_container').attr('data-lb-theme'));
	$('body').addClass($('.maxgrid_grid_container').attr('data-lb-search-bar'));
	
	$('body').on('click','.insert-as-lightbox #lightbox-enabled, #lightbox-enabled.maxgrid_title, .insert-as-iframe.image #lightbox-enabled', function() {
		var This = $(this),
			id_token = '';	
		
		var gridID = get_grid_id(This);		
		$('body').addClass('islightbox');
		$('.playlist-slider_container').css('opacity', '1');		
		if ( $(this)[0].classList.contains('class') && $(this).attr('class').indexOf('fa-search') !== -1 ) {			
			This = $(this).closest('.pg_featured-layer').next();
		}	
		
		var id_token = '';
		if ( This.attr('data-id') ) {
			id_token = This.attr('data-id');
		}
		$('.playlist-slider_container ul li').removeClass('active');
		This.addClass('active');
		
		lightBoxLoad(gridID, id_token, This, 'open');
		$('#html_nicescroll_rails').toggleClass('html_nicescroll_rails');
		$('html').addClass('stop-scrolling');
	});
	
	// Get the modal
	var modal = document.getElementById('maxgrid_lightbox_modal');	
	var reachContent = document.getElementById("reach_content_outer");
	
	function lightBoxLoad(gridID, id_token, This, target) {
		var gBody, el = document.querySelectorAll('.maxgrid-body');		
		for (var i = 0; i < el.length; i++) {
			if (el[i].getAttribute('data-g-uid') === gridID ) {
				gBody = $(el[i]);
			}
		}
		
		modal.style.display = "block";
		modal.setAttribute('data-g-uid', gridID);		
		var lightbox_id, post_type, featured_type, data_href, post_title='',
			preset_name = gBody.find('.get-form-data').attr('data-preset-name');	
		
		if ( id_token === '' ) {
			lightbox_id = This.attr('data-lightbox-id');
			post_type = This.attr('data-post-type');
			featured_type = This.attr('data-featured-type');
			data_href = This.attr('data-href');
			post_title = This.closest('.video-wrapper').attr('data-the-title');			
		} else {
			lightbox_id = id_token;
			post_type = 'youtube_stream';
			featured_type = 'youtube';
			data_href = 'https://www.youtube.com/watch?v='+id_token;			
			if(This.attr('data-post-type')){
				post_type = This.attr('data-post-type');
			}
			if(This.attr('data-featured-type')){
				featured_type = This.attr('data-featured-type');
			}
			if(This.attr('data-href')){
				data_href = This.attr('data-href');
			}
		}
		
		post_title = post_title !== undefined ? post_title : This.attr('data-the-title');		
		window.lightbox_id = lightbox_id;
		
		jQuery.ajax({
				type : "POST",
				 url : Const.ajaxurl,
				 data : {
					 action: 'maxgrid_lightbox_body',
					 lightbox_id   : lightbox_id,
					 post_type	   : post_type,
					 featured_type : featured_type,
					 data_href	   : data_href,
					 pslug	   	   : preset_name,
				 },
				beforeSend:function(xhr){
					$('.nav-thumbnails-content').addClass('is-loading');					
					modal.classList.add(post_type);
					modal.classList.add('visible');					
					set_small_screen_style();					
					reachContent.style.display = "block";					
					$('#maxgrid_reach_content').removeClass('move-to-'+target+'-in');
					$('#maxgrid_reach_content').addClass('move-to-'+target+'-out');
					if ( target === 'open' ) {
						var args = new Object();
						args.version = '2';
						args.form = 'ball';
						reachContent.innerHTML = maxgrid_lds_rolling_loader(args);
					}
					$('.navigation-arrow').removeClass('is-loaded');
					set_medium_screen_style();
				},
				success: function(response) {					
					$('#maxgrid_reach_content').removeClass('move-to-'+target+'-out');					
					reachContent.innerHTML = response;
					
					$('#tab-description img').each(function(){
						if($(this).parent().is("[href]")) {
							$(this).parent().removeAttr('href');
						} 
					});
					
					update_fake_slideshow();
					if (!maxgrid_isMobile()){							
						// Load playlist items if it is not already loaded
						if ( $('.playlist-slider_container').html() !== '' ) {
							if ( $('.playlist-slider_container ul') ) {
								var li_id;
								$('.playlist-slider_container ul li').each(function(i, obj) {						
									li_id = obj.getAttribute('data-id');									
									if ( li_id === lightbox_id ) {
										obj.classList.add('active');
										var This = $('.'+obj.getAttribute('class').replace(' ', '.') );
										nav_arrow_show_hide();
									}
								});
							}
						}
					}
					if ( slider_items_checker() && post_type !== 'youtube_stream' ) {
						get_slider_items();
					}
					
					// reload niceScroll for the lightbox slideshow box					
					$("#reach_content_outer").getNiceScroll().show().resize();					
					var winHeight, contentHeight, scrollHeight, railsHeight, ratio1, ratio2;
					var interval = setInterval(function() {						
						winHeight 	  = $("#reach_content_outer").outerHeight(true);
						contentHeight = $("#maxgrid_reach_content").outerHeight(true);
						scrollHeight  = $("#slideshow_nicescroll_rails").outerHeight(true);
						railsHeight   = $("#slideshow_nicescroll_rails .nicescroll-cursors").outerHeight();
						ratio1 		  = ( contentHeight / winHeight );
						ratio2 		  = ( scrollHeight / railsHeight );						
						if ( ( ratio1 - ratio2 ) < 0.05 ) {							
							clearInterval(interval);							
						}
						$("#reach_content_outer").getNiceScroll().show().resize();
					}, 100);					
					if ( post_type === 'youtube_stream' ) {
						get_ytb_content(lightbox_id, post_title, preset_name);	
					}
					
					if (!maxgrid_isMobile()){
						$('.reviews_tabs_container .tab-playlist').css('display', 'none');
						$('.reviews_tabs_container #tab-playlist').css('display', 'none');
					}					
					if ( $('#maxgrid_grid_container').length !== 0 ) {
						if ($('#maxgrid_grid_container').attr('data-dflt-playlist') === 'open') {
							setTimeout(function(){
								$(".playlist-slider_container").getNiceScroll().show().resize();
								
								if($('.li-post-thumb.active').length > 0) {
									$('.li-post-thumb.active')[0].scrollIntoView();
								}
									
							}, 550);							
							$('#maxgrid_lightbox_modal, .playlist-search-bar, .playlist-slider_container').addClass('slideshow-visible');
							$(".playlist-slider_container").getNiceScroll()[0].show().onResize();
						}
					}					
					$('#maxgrid_reach_content').css('heigh', 'calc(100% - 70px)');
					
					// Initializing embedded twitter follow button
					var twitterContainer = document.getElementById('twitter_lightbox_container');
					if ( twitterContainer !== null ) {
						twttr.widgets.createFollowButton(
							twitterContainer.getAttribute('data-twitter-id'),
							twitterContainer,
							{
								size: Const.twttrSize,
								count: Const.twttrCount,
								showScreenName: Const.twttrScreenName
							}
						);
					}
					
					try{
						var el = document.getElementById('g-recaptcha');
						var widgetId = grecaptcha.render(el);
						grecaptcha.reset(widgetId);
					}catch(e){
						//alert(e);
					}
					
					
					var img = document.querySelector("img.e_small");
					if ( img !== null ) {
						setTimeout(function(){							
							var realWidth = img.naturalWidth;
							if ( realWidth <= $("img.e_small").width() ) {
								$('#magnify').addClass('zoom-disabled');
							}
						}, 250);
					}					
					$('.maxgrid_lightbox-modal').addClass(preset_name);					
					if ( post_type !== 'youtube_stream' ) {
						$('.navigation-arrow').addClass('is-loaded');
					}					
				}				
			 });
	}
	
	/*-------------------------------------------------------------------------*/
	/*	Description & Comments Tabs	
	/*-------------------------------------------------------------------------*/
	
	// Active Tab - add remove Class
	$('body').on('click','ul.tabs li', function(e) {
		var tab_id = $(this).attr('data-tab');
		$('ul.tabs li').removeClass('current');
		$('.tab-content').removeClass('current');
		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
	});
	
	// Youtube comment - Inner links : "Starts playing time", "#tag" and links
	$('body').on('click','.comments-container.ytb p > a', function(ev) {
		var hms = $(this).attr('href').split('&t=')[1];	
				
		if ( hms === undefined ) {
			$(this).attr('target', '_blank');
		}
		
		if ( $(this).attr('href').indexOf('?t=') > -1 ) {
			return;
		}
		
		$("#ik_player_iframe")[0].src += "&autoplay=1&start=" + hms_to_s(hms);
		$("#reach_content_outer").scrollTop(0);
		ev.preventDefault();
	});
	
	// Convert Youtube starts playing time to second
	function hms_to_s(hms) {
		var H 	= hms.indexOf('h') > -1 ? hms.split('h')[0] : 0,
			M 	= hms.indexOf('h') > -1 ? hms.substring(
					hms.lastIndexOf("h") + 1, 
					hms.lastIndexOf("m")
				) : hms.split('m')[0],
			S 	= hms.indexOf('m') > -1 ? hms.substring(
					hms.lastIndexOf("m") + 1, 
					hms.lastIndexOf("s")
				) : hms.split('s')[0];
		return parseInt(H) * 60 * 60 + parseInt(M) * 60 + parseInt(S);
	}
	
	// Load More Comments
	$('body').on( 'click', '.load-more_comments.ytb', function () {
		
		var html = '<div class="dots-rolling"><span></span><span></span><span></span></div>';					
		$(this).html('<span class="out-me visible">'+Const.load_more+'</span>'+html);
		var next_page_token = $(this).attr('data-next-page-token'),
			video_id = $(this).attr('data-video-id');
		
		$.ajax({
		  	type : "POST",
			url  : Const.ajaxurl,
			data : {
				action 	 		: 'maxgrid_ytb_load_more_comments',
				video_id 		: video_id,
				next_page_token : next_page_token,
			},
			success: function(response) {
				if(response){
					$('.load-more_container.comments').remove();					
					$(".comments-container.ytb").append(response);
					$('.load-more_comments').html(Const.load_more);
				}
			}
		});
	});
	
	/*-------------------------------------------------------------------------*/
	/*	WooCommerce
	/*-------------------------------------------------------------------------*/
	
	$('body').on( 'click', '.maxgrid_product-thumbs_slider:not(.active) .maxgrid_product-thumbs', function () {
		$('#magnify img.e_small').attr('src', $(this).attr('data-img-url'));		
		setTimeout(function(){
			var img = document.querySelector("img.e_small");
			var realWidth = img.naturalWidth;
			if ( realWidth <= $("img.e_small").width() ) {
				$('#magnify').addClass('zoom-disabled');
			} else {
				$('#magnify').removeClass('zoom-disabled');
			}
		}, 550);
	});
	
	$('body').on('click','.maxgrid_product-thumbs', function(e) {
		e.stopPropagation();	
	});
	
	/*-------------------------------------------------------------------------*/
	/*	Slideshow Navigations
	/*-------------------------------------------------------------------------*/
	
	$('body').on('click','#next.is-loaded, #prev.is-loaded, .nav-thumbnails-content, .playlist-slider_container ul li:not(.playlist-load-more-container), .playlist-tab-content ul li:not(.playlist-load-more-container)', function(e) {
		e.stopPropagation();
		if($("#ik_player_iframe")[0]){
			$("#ik_player_iframe")[0].src += "&autoplay=0";
		}		
		var Target = null;
		if (maxgrid_isMobile()) {			
			Target = 'playlist-tab-content';			
		}
		nav_arrow_show_hide($(this), Target);	
	});
	
	/*-------------------------------------------------------------------------*/
	/*	Playlist
	/*-------------------------------------------------------------------------*/
	
	// Show / Hide playlist
	$('body').on('click','.open-slideshow', function(e) {
		e.stopPropagation();		
		var admin_bar = $('#maxgrid_lightbox_modal').attr('data-admin-bar');
		if(admin_bar==='on'){
			$('#slider_nicescroll_rails').addClass('is_admin_bar');
		}
		$('#slideshow_nicescroll_rails').addClass('slideshow_nicescroll_rails');
		$('#slider_nicescroll_rails').addClass('slideshow_nicescroll_rails');		
		setTimeout(function(){
			$('#slideshow_nicescroll_rails').removeClass('slideshow_nicescroll_rails');
			$('#slider_nicescroll_rails').removeClass('slideshow_nicescroll_rails');
			$("#reach_content_outer").getNiceScroll().show().resize();
			if($('.li-post-thumb.active')[0]){
				$('.li-post-thumb.active')[0].scrollIntoView();
			}			
		}, 350);		
		if($('#ul-thumbs-list').html() === undefined ) {			
			setTimeout(function() {
				$(".playlist-slider_container").getNiceScroll().show().onResize();
			}, 1000);
		} else {
			setTimeout(function() {
				$(".playlist-slider_container").getNiceScroll().show().onResize();
			}, 400);
		}		
		$('#maxgrid_lightbox_modal, .playlist-search-bar, .playlist-slider_container').toggleClass('slideshow-visible');		
		$(".playlist-slider_container").getNiceScroll()[0].show().onResize();		
		set_medium_screen_style();
		update_fake_slideshow();
	});
	
	// Add "ishover" class to the hovered playlist item
	$('body').on( 'hover', 'li.li-post-description', function () {
		$(this).prev().toggleClass('ishover');
	});
	
	// Load more items
	$('body').on('click','.load-more.ligthbox-playlist', function() {
		var nextPageToken = $(this).attr('data-next-page-token');
		var offset = $(this).attr('data-offset');
		get_slider_items(nextPageToken, offset);
	});
	
	// Search bar - Clear woord button
	$('body').on('click','.clear-woord', function() {
		$('input#playlist-search').val('');
		postPlaylistFilter(this);
	});
	
	// Apply niceScroll to playlist	
	if (!maxgrid_isMobile()){
		$("#reach_content_outer").niceScroll({
			scrollspeed: 60,
			mousescrollstep: 40,
			cursorwidth: 12,
			cursorborder: 0,
			cursorcolor: '#3b3b3b',
			cursorborderradius: 6,
			autohidemode: false,
			horizrailenabled: false,
			zindex: 100011,						
			scrollbarid: "slideshow_nicescroll_rails",
		});

		// set niceScroll to the slider thumbnails box
		$(".playlist-slider_container").niceScroll({
			background: "#2a2a2a",
			scrollspeed: 60,
			mousescrollstep: 40,
			directionlockdeadzone: 20,
			cursorwidth: 13,
			railpadding: { top: 1, right: 1, left: 0, bottom: 1 },
			disableoutline: true,
			cursorborder: 0,
			cursorcolor: '#424242',
			cursorborderradius: 6,
			autohidemode: false,
			horizrailenabled: false,
			zindex: 100022,
			scrollbarid: "slider_nicescroll_rails",
		});
	}
	
	/*-------------------------------------------------------------------------*/
	/*	Devices & Screens
	/*-------------------------------------------------------------------------*/
	
	// Medium screen func
	function is_mediumScreen() {
		var side_val = 0;
		if ( $('.playlist-slider_container').attr('class').indexOf('slideshow-visible') !== -1 ) {
			side_val = $('.playlist-slider_container').width();
		}
		if ($(window).width() - side_val < 1000 ) {
			return true;
		} else {
			return false;
		}
	}
	
	// Small screen func
	function is_smallScreen() {
		if ($(window).width() < 700 || maxgrid_isMobile() ) {
			return true;
		} else {
			return false;
		}
	}
	
	// Set small screen styles
	function set_small_screen_style() {
		if(is_smallScreen()){			
			$('.ismobile-nav').addClass('isvisible');
			$('#maxgrid_lightbox_modal, .playlist-search-bar, .playlist-slider_container').addClass('ismobile');
			if ( maxgrid_isMobile() ) {
				$('.ismobile-nav').addClass('ismobile');
				$('#maxgrid_lightbox_modal').addClass('overwrite');
			}
			$("#reach_content_outer").getNiceScroll().show().resize();
			$(".playlist-slider_container").getNiceScroll().show().resize();
		} else {
			$('.ismobile-nav').removeClass('ismobile');
			$('.ismobile-nav').removeClass('isvisible');
			$('#maxgrid_lightbox_modal, .playlist-search-bar, .playlist-slider_container').removeClass('ismobile');
			$("#reach_content_outer").getNiceScroll().show().resize();
			$(".playlist-slider_container").getNiceScroll().show().resize();
		}
	}
	
	// Set small medium styles
	function set_medium_screen_style() {
		if(is_mediumScreen()){
			$('#maxgrid_lightbox_modal, .playlist-search-bar, .playlist-slider_container').addClass('nav-in-top');
		} else {
			$('#maxgrid_lightbox_modal, .playlist-search-bar, .playlist-slider_container').removeClass('nav-in-top');
		}
	}
	
	/*-------------------------------------------------------------------------*/
	/*	On windows resize
	/*-------------------------------------------------------------------------*/
	
	window.onresize = resize;
	function resize() {
		set_small_screen_style();
		set_medium_screen_style();
		update_fake_slideshow();
	}
	
	var rtime;
	var timeout = false;
	var delta = 200;
	$(window).resize(function() {
		rtime = new Date();
		if (timeout === false) {
			timeout = true;
			setTimeout(resizeend, delta);
		}
	});

	function resizeend() {
		if (new Date() - rtime < delta) {
			setTimeout(resizeend, delta);
		} else {
			timeout = false;
			$('.grid-layout-row').masonry('reloadItems');
		}               
	}
	
	/*-------------------------------------------------------------------------*/
	/*	Video paly / pause buttons
	/*-------------------------------------------------------------------------*/
	
	// Play Button
	$('body').on('click','.ytb-play-btn', function() {
		
		if ( $(this).attr('data-type') === 'mp4' ) {
			var video = $(this).closest('.video-wrapper').find('video')[0];
			video.play();			
		} else {
			var iframe 		= $(this).closest( '.video-wrapper' ).find('iframe')[0],
				new_iframe 	= document.createElement( "iframe" ),
				iframe_data	= $(this).closest('.video-wrapper').find('.iframe-data')[0],
				iframe_src  = iframe_data.getAttribute( 'data-src' );
			if ( iframe === undefined ) {
				new_iframe.setAttribute( "frameborder", "0" );
				new_iframe.setAttribute( "allowfullscreen", "" );
				new_iframe.setAttribute( "src", iframe_src +"&autoplay=1" );

				iframe_data.parentNode.insertBefore(new_iframe, iframe_data);
			} else {
				iframe.src += '&autoplay=1';
			}
			
		}
		$(this).closest('.video-wrapper').addClass('playing');
	});
	
	// Pause Button
	$('body').on('click','.ytb-pause-btn', function() {
		$(this).closest('.video-wrapper').removeClass('playing');		
		if ( $(this).attr('data-type') === 'mp4' ) {
			var video = $(this).closest('.video-wrapper').find('video')[0];
			video.pause();
		} else {
			var videoURL = $(this).closest('.video-wrapper').find('iframe')[0].src;
				videoURL = videoURL.replace("&autoplay=1", "");
			$(this).closest('.video-wrapper').find('iframe')[0].src = videoURL;
		}
	});
	
	/*-------------------------------------------------------------------------*/
	/*	Close Lightbox modal
	/*-------------------------------------------------------------------------*/
	
	$('body').on('click','.pg_lightbox-toolbar .icon-cross, .ismobile-nav .icon-cross', function(e) {
		e.stopPropagation();
		var gridID = $('#maxgrid_lightbox_modal').attr('data-g-uid');
		$('.playlist-slider_container').attr('data-prev-g-uid', gridID);		
		closeLightbox();
		var addClassTo = ['.pg_lightbox-toolbar', '.full_magnify-icon'];
		toggleFullscreen(this.parent, addClassTo, 'force');
		document.getElementById("reach_content_outer").innerHTML = '';
	});
	
	// Close lightbox by clicking on overlay	
	$('#maxgrid_lightbox_modal').addClass( $('#maxgrid_grid_container').attr('data-overlay-click-close') );
	
	$('body').on('click','#maxgrid_lightbox_modal.close-is-on', function(e) {
		e.stopPropagation();
		closeLightbox();
		var addClassTo = ['.pg_lightbox-toolbar', '.full_magnify-icon'];
		toggleFullscreen(this.parent, addClassTo, 'force');
	});	
	
	$('body').on('click','#maxgrid_reach_content, .pg_lightbox-toolbar', function(e) {
		e.stopPropagation();
	});
	
	// close Lightbox function
	function closeLightbox(){
		$('body').removeClass('islightbox');
		reachContent.style.display = "none";				
		$('#maxgrid_lightbox_modal, .playlist-search-bar, .playlist-slider_container').removeClass('slideshow-visible');
		$('.navigation-arrow').removeClass('isvisible');				
		setTimeout(function(){
			modal.classList.remove('visible');
			$('.ismobile-nav').removeClass('isvisible');
			$('.playlist-slider_container').css('opacity', '0');
			$('#html_nicescroll_rails').toggleClass('html_nicescroll_rails');
		}, 250);		
		$('.video-wrapper.pg_lightbox iframe').attr('src', '');	
		$('html').removeClass('stop-scrolling');
	}

	/*-------------------------------------------------------------------------*/
	/*	Image Magnifier
	/*-------------------------------------------------------------------------*/
	
	$('body').on('click','.full_magnify-icon', function(e) {
		e.stopPropagation();		
	});
	
	$('body').on('click','.magnify:not(.zoom-disabled):not(.fullscreen-is-on)', function(e) {
		e.stopPropagation();				
		$(".magnify").toggleClass('active');
		$(".maxgrid_product-thumbs_slider").toggleClass('active');		
		if($(".maxgrid_product-thumbs_slider").attr('class').indexOf('active') === -1 ) {
			$('.maxgrid_product-thumbs_slider :radio').attr('disabled', false);
		} else {
			$('.maxgrid_product-thumbs_slider :radio').attr('disabled', true);
		}	
		var magnify_offset = $('.magnify').offset(),
			mx = e.pageX - magnify_offset.left,
			my = e.pageY - magnify_offset.top,
			rx = Math.round(mx/$(".e_small").width()*native_width - $(".e_large").width()/2)*-1,
			ry = Math.round(my/$(".e_small").height()*native_height - $(".e_large").height()/2)*-1,
			bgp = rx + "px " + ry + "px",
			px = mx - $(".e_large").width()/2,
			py = my - $(".e_large").height()/2;
		$(".e_large").css({left: px, top: py, backgroundPosition: bgp});				
	});
		
	var native_width = 0;
	var native_height = 0;
	
	$('body').on('mousemove','.magnify', function(e) {		
		$(".e_large").css("background-image","url('" + $(".e_small").attr("src") + "')");
		$(".e_large").css("background-repeat","no-repeat");
		$(".e_large").css("height", $(".e_large").css('width'));
		$(".e_large").css("box-shadow", '0 0 0 '+$(".e_large").width()/35+'px rgba(255, 255, 255, 0.85), 0 0 7px 7px rgba(0, 0, 0, 0.25), inset 0 0 40px 2px rgba(0, 0, 0, 0.25)');	
		
		var magnify_offset = $(this).offset(),
			mx = e.pageX - magnify_offset.left,
			my = e.pageY - magnify_offset.top;
		
		if(!native_width && !native_height) {			
			var image_object = new Image();
			image_object.src = $(".e_small").attr("src");
			native_width = image_object.width;
			native_height = image_object.height;			
		} else {			
			if($(".e_large").is(":visible")) {
				var rx = Math.round(mx/$(".e_small").width()*native_width - $(".e_large").width()/2)*-1;
				var ry = Math.round(my/$(".e_small").height()*native_height - $(".e_large").height()/2)*-1;
				var bgp = rx + "px " + ry + "px";
				var px = mx - $(".e_large").width()/2;
				var py = my - $(".e_large").height()/2;
				$(".e_large").css({left: px, top: py, backgroundPosition: bgp});
			}			
		}
	});
	
	// Toggle featured image Fullscreen mode
	$('body').on('click','.full_magnify-icon', function() {
		if ( $('#maxgrid_lightbox_modal').attr('class').indexOf('iframe-mode') !== -1 ) {
			alert('Full screen option disabled in this page.');
			return false;
		}
		$('#magnify').addClass('fullscreen-is-on');
		
		var addClassTo = ['.magnify'];
		toggleFullscreen($(this).parent().get(0), addClassTo);
	});
	
	// Toggle Lightbox fullscreen mode
	$('body').on('click','.fullscreen-icon', function() {
		
		if ( $('#maxgrid_lightbox_modal').attr('class').indexOf('iframe-mode') !== -1 ) {
			alert('Full screen option disabled in this page.');
			return false;
		}
		var addClassTo = ['.pg_lightbox-toolbar', '.full_magnify-icon'];
		toggleFullscreen(this.parent, addClassTo);
	});
	
	
	// On fullscreen state change
	var addClassTo = ['.magnify', '.pg_lightbox-toolbar', '.full_magnify-icon'];
	document.addEventListener("fullscreenchange", function () {
		if(!window.fullScreen) {
			for (var i = 0; i < addClassTo.length; i++) {
				$(addClassTo[i]).removeClass('fullscreen-is-on');
			}
		}
	}, false);

	document.addEventListener("mozfullscreenchange", function () {
		if(!window.fullScreen) {
			for (var i = 0; i < addClassTo.length; i++) {
				$(addClassTo[i]).removeClass('fullscreen-is-on');
			}
		}
	}, false);

	document.addEventListener("webkitfullscreenchange", function () {
		if(!window.fullScreen) {
			$('.pg_lightbox-toolbar').removeClass('fullscreen-is-on');
			for (var i = 0; i < addClassTo.length; i++) {
				$(addClassTo[i]).removeClass('fullscreen-is-on');
			}
		}
	}, false);

	document.addEventListener("msfullscreenchange", function () {
		if(!window.fullScreen) {
			$('.pg_lightbox-toolbar').removeClass('fullscreen-is-on');
			for (var i = 0; i < addClassTo.length; i++) {
				$(addClassTo[i]).removeClass('fullscreen-is-on');
			}
		}
	}, false);
	
	/*-------------------------------------------------------------------------*/
	/*	AJAX Complete
	/*-------------------------------------------------------------------------*/
	
	$(document).ajaxComplete(function(){
		$("#reach_content_outer").getNiceScroll().show().resize();
	});
		
});

/*-------------------------------------------------------------------------*/
/*	Functions
/*-------------------------------------------------------------------------*/

// Lightbox Fullscreen Mode function
function toggleFullscreen(elem, addClassTo, force_exit) {
	// elem * Element
	// addClassTo * array
	elem = elem || document.documentElement;
	if (force_exit){
		if (document.exitFullscreen) {
			document.exitFullscreen();
		} else if (document.msExitFullscreen) {
			document.msExitFullscreen();
		} else if (document.mozCancelFullScreen) {
			document.mozCancelFullScreen();
		} else if (document.webkitExitFullscreen) {
			document.webkitExitFullscreen();
		}
		
		// remove class
		for (var x = 0; x < addClassTo.length; x++) {
			jQuery(addClassTo[x]).removeClass('fullscreen-is-on');
		}
		
		return;
	}
	if (!document.fullscreenElement && !document.mozFullScreenElement &&
		!document.webkitFullscreenElement && !document.msFullscreenElement) {
		if (elem.requestFullscreen) {
			elem.requestFullscreen();
		} else if (elem.msRequestFullscreen) {
			elem.msRequestFullscreen();
		} else if (elem.mozRequestFullScreen) {
			elem.mozRequestFullScreen();
		} else if (elem.webkitRequestFullscreen) {
			elem.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
		}
		
		// add class
		for (var i = 0; i < addClassTo.length; i++) {
			jQuery(addClassTo[i]).addClass('fullscreen-is-on');
		}
		
	} else {
		if (document.exitFullscreen) {
			document.exitFullscreen();
		} else if (document.msExitFullscreen) {
			document.msExitFullscreen();
		} else if (document.mozCancelFullScreen) {
			document.mozCancelFullScreen();
		} else if (document.webkitExitFullscreen) {
			document.webkitExitFullscreen();
		}
		
		// remove class
		for (var x = 0; x < addClassTo.length; x++) {
			jQuery(addClassTo[x]).removeClass('fullscreen-is-on');
		}
		
	}
}

// Simply Button Search Filter
function postPlaylistFilter(This) {	
	// Declare variables
    var filter, ul, li, i, span;
	
	if( This !== undefined && This.getAttribute('data-is-mobile') === 'on' ) {
		filter = jQuery('.tab-playlist-search').val().toUpperCase();
		ul = document.getElementById('isMobile_ul-thumbs-list');
	} else {
		filter = jQuery('.side-playlist-search').val().toUpperCase();
		ul = document.getElementById('ul-thumbs-list');
	}
	
    if (!ul){ return false;}
 	li = ul.getElementsByTagName('li');
	
    for (i = 0; i < li.length; i++) {
		if ( li[i].className === "li-post-description" ) {
			span = li[i].getElementsByTagName("span")[0];
			if (span.innerHTML.toUpperCase().indexOf(filter) > -1 ) {
				li[i].previousSibling.style.display = "";
				li[i].style.display = "";
			} else {
				li[i].previousSibling.style.display = "none";
				li[i].style.display = "none";
			}
		}
    }
	jQuery(".playlist-slider_container").getNiceScroll().show().resize();
}