/*-------------------------------------------------------------------------*/
/*	Max Grid Builder - UI-Panel Modal
/*-------------------------------------------------------------------------*/

jQuery(document).ready(function($){
	
	if( $(document.getElementById("maxgrid_ui-panel")).length !== 0 ) {
		maxgrid_dragElement(document.getElementById("maxgrid_ui-panel"));
	}	
	
	// Close Panel Button
	$('body').on('click','#close_panel', function() {
		
		$(".ui-panel-content.elements-list").css('background', '#f1f1f1').css('border-top', '1px solid #ccc');
		$('.maxgrid_ui-panel_overlay').css('display', 'none');
				
		$('.ui-panel-content').removeClass('elements-list');
		$('#maxgrid_ui-panel-footer').removeClass('elements-list-footer');
	});	

	$('body').on('change','#sb_separator_type', function() {
		if( $(this).val() === 'no_spacer' ) { $('.sb_separator').css('display', 'none');}
		else { $('.sb_separator').css('display', 'block');}
	});
	
	/*-------------------------------------------------------------------------*/
	/*	Simplify controls - Button
	/*-------------------------------------------------------------------------*/

	$('body').on('click','.simplify-control-triger', function() {
		var target = $(this).attr('data-target');
		if($(this).is(':checked')){
			$('.'+target+'_simplify-control_container .maxgrid-fields__block').addClass('maxgrid_simplified');
			$('.'+target+'_simplify-control_container .simplify-control').addClass('maxgrid_simplified');
			$('.'+target+'_simplify-control_container .ui-simplify').css('display', 'none');
		} else {
			$('.'+target+'_simplify-control_container .maxgrid-fields__block').removeClass('maxgrid_simplified');
			$('.'+target+'_simplify-control_container .simplify-control').removeClass('maxgrid_simplified');
			$('.'+target+'_simplify-control_container .ui-simplify').css('display', 'inline-block');
		}
	});
	
	$('body').on('keyup','.maxgrid_simplified', function() {
		var target = $(this).attr('data-target');
		$('input.'+target).val($(this).val());
	});

	/*-------------------------------------------------------------------------*/
	/*	ShareThis Settings - Panel
	/*-------------------------------------------------------------------------*/
	
	$('body').on('change','#inside_tooltip, #horizontal_list', function() {
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
	});	
});

/*----------------------------------------*/
/* 	Functions
/*----------------------------------------*/

// Draggagle Box Function
function maxgrid_dragElement(elmnt) {
	var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
	if (document.getElementById(elmnt.id + "header")) {
		document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
	} else {
		elmnt.onmousedown = dragMouseDown;
	}
	function dragMouseDown(e) {
	e = e || window.event;
		// get the mouse cursor position at startup:
		pos3 = e.clientX;
		pos4 = e.clientY;
		document.onmouseup = closeDragElement;
		// call a function whenever the cursor moves:
		document.onmousemove = elementDrag;
	}
	function elementDrag(e) {
		e = e || window.event;
		// calculate the new cursor position:
		pos1 = pos3 - e.clientX;
		pos2 = pos4 - e.clientY;
		pos3 = e.clientX;
		pos4 = e.clientY;
		// set the element's new position:
		elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
		elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
	}

	function closeDragElement() {
		document.onmouseup = null;
		document.onmousemove = null;
	}
}