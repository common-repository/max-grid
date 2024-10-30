/*-------------------------------------------------------------------------*/
/*	Tooltip JS - @since: 1.0.0
/*-------------------------------------------------------------------------*/

jQuery(document).ready(function ($) {

	var targets = $('[data-rel="maxgrid_tooltip"]'),
		target = false,
		tooltip = false,
		tip = false,
		close_btn = false,
		style = false;

	// Tooltip - Type : help
	$('body').on('click', '#maxgrid_tooltip', function (e) {
		e.stopPropagation();
	});

	$('body').on('click', '[data-rel="help-tooltip"]', function (e) {
		e.stopPropagation();
		$('#maxgrid_tooltip').remove();

		target = $(this);

		tip = target.attr('data-title');
		tooltip = $('<div id="maxgrid_tooltip" class="help-tooltip"></div>');

		style = target.attr('data-style') ? ' ' + target.attr('data-style') : '';
		tooltip.addClass('help' + style);

		close_btn = '<span class="dashicons dashicons-no-alt close"></span>';

		if (!tip || tip === '') {
			return false;
		}

		//target.removeAttr( 'data-title' );
		tooltip.css('opacity', 0)
			.html(maxgrid_b64_to_utf8(tip) + close_btn)
			.appendTo('body');

		var init_tooltip = function () {

			if ($(window).width() < tooltip.outerWidth() * 1.5)
				tooltip.css('max-width', $(window).width() / 2);
			else
				tooltip.css('max-width', 400);

			var pos_left = target.offset().left + (target.outerWidth() / 2) - (tooltip.outerWidth() / 2),
				pos_top = target.offset().top - tooltip.outerHeight() - 8;

			if (pos_left < 0) {
				pos_left = target.offset().left + target.outerWidth() / 2 - 8;
				tooltip.addClass('left');
			} else
				tooltip.removeClass('left');

			if (pos_left + tooltip.outerWidth() > $(window).width()) {
				pos_left = target.offset().left - tooltip.outerWidth() + target.outerWidth() / 2 + 8;
				tooltip.addClass('right');
			} else
				tooltip.removeClass('right');

			if (pos_top < 0) {
				var pos_top = target.offset().top + target.outerHeight();
				tooltip.addClass('top');
			} else
				tooltip.removeClass('top');

			tooltip.css({
					left: pos_left,
					top: pos_top
				})
				.animate({
					top: '+=4',
					opacity: 1
				}, 100);
		};

		init_tooltip();
		$(window).resize(init_tooltip);

		var remove_tooltip = function (status) {
			tooltip.animate({
				top: '-=4',
				opacity: 0
			}, 100, function () {
				$(this).remove();
			});
		};

		// Hide tooltip on scrolling if fixed container
		if (target.attr('data-position') === 'fixed') {
			$('#maxgrid_ui-panel-content').bind('scroll', remove_tooltip);
			$(window).bind('scroll', remove_tooltip);
		}

		target.bind('click', remove_tooltip);
		$('body, #maxgrid_tooltip .close').bind('click', remove_tooltip);
	});

	// Remove Tooltip
	$('body').on('mouseenter, mouseover', 'div', function () {
		target = $(this);
		if (target.attr('data-rel') !== undefined || target.attr('data-rel') === 'help-tooltip') {
			return false;
		}
	});
	
	$('body').on('click', '[data-rel="maxgrid_tooltip"]:not(.locked)', function () {
		$('#maxgrid_tooltip').each(function (i, obj) {
			if (obj.getAttribute('class').indexOf('help-tooltip') === -1) {
				obj.remove();
			}
		});
	});
	
	// Tooltip - Type : normal
	$('body').on('mouseenter', '[data-rel="maxgrid_tooltip"]:not(.locked)', function () {
		
		target = $(this);
		tip = target.attr('data-title');
		tooltip = $('<div id="maxgrid_tooltip"></div>');

		$('#maxgrid_tooltip').remove();

		style = target.attr('data-style') ? ' ' + target.attr('data-style') : '';
		tooltip.addClass(style);
		if (!tip || tip === '') {
			return false;
		}

		tooltip.css('opacity', 1)
			.html(tip)
			.appendTo(target);
		var h_v = 8;
		
		var init_tooltip = function () {
			
			if ( $(window).width() < tooltip.outerWidth() * 1.5 ) {
				tooltip.css('max-width', $(window).width() / 2);
			} else {
				tooltip.css('max-width', 340);
			}
			
			var pos_left = target.offset().left + (target.outerWidth() / 2) - ( tooltip.outerWidth() / 2 ),
				pos_top  = target.offset().top - tooltip.outerHeight() - h_v;
			
			var bodyRect = document.body.getBoundingClientRect(),
				elemRect = target[0].getBoundingClientRect(),
				offset 	 = elemRect.top - bodyRect.top;			
				pos_top  = offset - h_v;

			tooltip.css({
					left: pos_left,
					top: pos_top
				})
				.animate({
					top: '+=4',
					opacity: 1
				}, 100);
		};

		init_tooltip();
		$(window).resize(init_tooltip);

		var remove_tooltip = function () {
			tooltip.animate({
				top: '-=4',
				opacity: 0
			}, 100, function () {
				$(this).remove();
			});
			target.attr('data-title', tip);
		};

		target.bind('mouseleave', remove_tooltip);
		tooltip.bind('click', remove_tooltip);
		
	});
});