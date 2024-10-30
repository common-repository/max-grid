/*-----------------------------------------------------------------*/
/* Accent Colors - globally defined color scheme
/*-----------------------------------------------------------------*/

jQuery(document).ready(function($){
	 
	$('body').on('change', '[data-triger="dropp"]', function () {
		var Target = $(this).closest('.maxgrid_ui-col').next();
		if(this.checked) {
			Target.removeClass('drop-hidded');
		} else {
			Target.addClass('drop-hidded');
		}
	});
	
	window.addEventListener('click', function(e){
		$(this).removeClass('js-open');
		$('.dropp-body,.js-dropp-action').removeClass('js-open');
	});
	
	$('body').on('click','.dropp-header__title, #use_color_theme', function(e) {
		e.stopPropagation();
	});
	
	$('body').on('click','.use-term-color', function() {
		var colorScheme = $(this).closest('.maxgrid_ui-col').next().next().find('.use-color-scheme');
		if(this.checked) {
			colorScheme.prop("checked", false);
			$('[data-triger="dropp"]').change();
			colorScheme[0].disabled = true;
			colorScheme.next().css('opacity', '.35');	
		} else {
			colorScheme[0].disabled = false;
			colorScheme.next().css('opacity', '1');
		}
		
	});
	
});

/*-----------------------------------------------------------------*/
/* Initialize Color Scheme DropDown menu - Extra Colors Selector
/*-----------------------------------------------------------------*/

function maxgrid_ExtraColorInitialize() {
	
	jQuery('[data-triger="dropp"]').each( function(i, obj) {
		var tar_1   = jQuery(this).closest('.maxgrid_ui-col').next('.extra_c1'),
			tar_2 	= jQuery(this).closest('.maxgrid_ui-col').next('.extra_c2'),
			tar_3 	= jQuery(this).closest('.maxgrid_ui-col').next('.tc1'),
			tar_4 	= jQuery(this).closest('.maxgrid_ui-col').next('.tc2'),
			tar_5   = jQuery(this).closest('.maxgrid_ui-col').next('.tc3');
		
		if(!jQuery(obj).prop("checked")){
			tar_1.addClass('drop-hidded');
			tar_2.addClass('drop-hidded');
			tar_3.addClass('drop-hidded');
			tar_4.addClass('drop-hidded');
			tar_5.addClass('drop-hidded');
		} else {
			tar_1.removeClass('drop-hidded');
			tar_2.removeClass('drop-hidded');
			tar_3.removeClass('drop-hidded');
			tar_4.removeClass('drop-hidded');
			tar_5.removeClass('drop-hidded');
		}
		
		$('#use_extra_c1, #use_extra_c2').prop("disabled", false);
		$('[for="use_extra_c1"]').css("opacity", 1);
		
		// Extra Color 1
		var radio = tar_1.find('#extra_c1_extra_color_1');
		radio.attr("checked", "checked");
		radio.prop("checked", true);		
		tar_1.find('.js-value').text(radio.attr('data-label'));
		
		radio.closest('label').addClass('js-open').siblings().removeClass('js-open');
		tar_1.find('.extra-color__chosen_preview').css('background', radio.attr('data-color'));
		
		// Extra Color 2
		var radio_2 = tar_2.find('#extra_c2_extra_color_2');
		radio_2.attr("checked", "checked");
		radio_2.prop("checked", true);		
		tar_2.find('.js-value').text(radio_2.attr('data-label'));
		
		radio_2.closest('label').addClass('js-open').siblings().removeClass('js-open');
		tar_2.find('.extra-color__chosen_preview').css('background', radio_2.attr('data-color'));
		
	});
}

function maxgrid_ExtraColor_contruct() {
	jQuery('[data-triger="dropp"]').each( function(i, obj){
		if(!jQuery(obj).prop("checked")){
			var Target = jQuery(this).closest('.maxgrid_ui-col').next();
			Target.addClass('drop-hidded');
		}
	})
		
	jQuery('.dropp-header').each( function(i, obj){
		jQuery(obj).click(function (e) {
			e.preventDefault();
			jQuery(this).toggleClass('js-open');
			jQuery(this).find('div').toggleClass('js-open');
			jQuery(this).next('.dropp-body').toggleClass('js-open');
		});
	})

	// Using as fake input select dropdown
	jQuery('label').click(function () {
		jQuery(this).addClass('js-open').siblings().removeClass('js-open');
		jQuery('.dropp-body,.js-dropp-action').removeClass('js-open');
		var activeColor = jQuery(this).find('input').attr('data-color');
		jQuery(this).parent().prev('.dropp-header').find('.extra-color__chosen_preview').css('background', activeColor);
	});
	
	// get the value of checked input radio and display as dropp title
	jQuery('input[data-triger="dropp-checked"]').change(function () {
		var value = jQuery(this).attr('data-label');
		jQuery(this).attr("checked", "checked");
		jQuery(this).prop("checked", true);		
		jQuery(this).closest('.dropp').find('.js-value').text(value);
	});
}