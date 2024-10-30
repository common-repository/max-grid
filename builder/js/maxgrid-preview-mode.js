/**
 * Max Grid Builder - Preview mode functions
 */

jQuery(function($) {	
	// Changing text and background color of ribbon
	$('body').on('change, keyup', '[data-ribbon]', function () {			
		var parent = $(this).closest('.maxgrid_ui-block-col');			
		maxgrid_RibbonLivePreview(parent);
	});

	// Changing background color on preview mode
	$('.preview_mode_bg_c').wpColorPicker({
		change: function(event, ui) {
			var color = this.value;
			setTimeout(function(){
				$("iframe#grid-preview-device").contents().find('.em-pm_wrapper').css('background', color);
				maxgrid_setCookie({cname: 'preview_mode_bg_c', value: color});
			},10);

		},
	});

	// Update blocks sizes after load more posts
	$('iframe#grid-preview-device').load(function() {			
		var iframe = $('iframe#grid-preview-device').contents();			
		iframe.find(".load-more").click(function() {
			var grid = iframe.find('.ajax-grid_response');				
			var interval = setInterval(function() {
				if(grid[0].firstElementChild.clientWidth !== grid[0].lastElementChild.clientWidth){
					clearInterval(interval);
				}
				$(".dashicons-update").click();
			}, 150);
		});
	  });
	
	$('.em-toggle').on('click', function(event){
		event.preventDefault();		
		$('#preview_container').toggleClass('pmode-light');
		$(this).toggleClass('active');
	});
});

// Change screen mode
function maxgrid_swapScreen(element) {
	var w_sizes = {
		'full_width': '100%',
		'desktop': '1440px',
		'laptop': '1024px',
		'tablet': '768px',
		'mobile': '320px',
	};
	var h_sizes = {
		'full_width': '100%',
		'desktop': '900px',
		'laptop': '800px',
		'tablet': '950px',
		'mobile': '520px',
	};

	//tablet
	var id = element.getAttribute('id');
	var mobile = document.getElementById('mobile'),
		tablet = document.getElementById('tablet');

	var width, height, rotated_device = ['mobile', 'tablet'];
	if (element.classList.contains('active') === true && element.classList.contains('rotate') !== true && rotated_device.indexOf(id) !== -1) {
		element.classList.add("rotate");
		width = h_sizes[id];
		height = w_sizes[id];
	} else {
		element.classList.remove("rotate");
		width = w_sizes[id];
		height = h_sizes[id];
	}

	jQuery('li').removeClass('active');
	element.classList.toggle("active");

	var iFrame = document.getElementById('grid-preview-device');
	iFrame.width = width;
	iFrame.height = height;
	
	var rotated_el = document.getElementById('rotate');
	if(!rotated_el){
		return;
	}
	if (mobile.classList.contains('active') === true || tablet.classList.contains('active') === true) {
		rotated_el.classList.remove('disabled');
	} else {
		rotated_el.classList.add('disabled');
	}
}

// Mode options
function maxgrid_swapOptions(element) {	
	var key = element.getAttribute('id'),
		value;
	
	if (element.getAttribute('type') === 'checkbox') {
		value = element.checked ? element.getAttribute('data-true') : element.getAttribute('data-false');
	}
	
	if (element.getAttribute('type') === 'number') {
		value = element.value ? element.value : 4;
	}
	
	if (key === 'items_row') {
		var gBody = jQuery("iframe#grid-preview-device").contents().find('.maxgrid-body');
		gBody.attr('data-items-per-row', value);

		setTimeout(function() {
			jQuery("iframe#grid-preview-device").contents().find('.masonry-grid-layout').masonry({
				itemSelector : '.block-grid-container'
			});
			jQuery("iframe#grid-preview-device").contents().find('.masonry-grid-layout').masonry( 'reloadItems' ).masonry( 'layout' );

		}, 10);
		
	} else {
		jQuery("#grid-preview-options #items_row").val(4);		
		var iFrame = document.getElementById('grid-preview-device'),
		old_src = iFrame.src,
		src = maxgrid_changeUrl(old_src, key, value);
		iFrame.src = src;
	}
}