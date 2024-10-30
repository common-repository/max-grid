
jQuery(document).ready(function($){
	
	// Post Comments
	$('body').on( 'click', '.load-more_comments.post', function () {
		
		var life_added = $('.comment-body.the_last');
		
		var html = '<div class="dots-rolling"><span></span><span></span><span></span></div>';					
		$(this).html('<span class="out-me visible">'+Const.load_more+'</span>'+html);
		
		var btn = $(this).parent(),
			post_id = $(this).attr('data-post-id'),
			get_last = $(this).attr('data-getlast'),
			page = $(this).attr('data-comments-page');
		
		$.ajax({
		  	type : "POST",
			url  : Const.url,
			data : {
				action 			 : 'maxgrid_load_more_comments',
				post_id 		 : post_id,
				page    		 : page,
				next_list_token  : $(this).attr('data-next-list-token'),
			},
			success: function(response){
				var elements = $(response);
	
				var found = $('[data-id="'+life_added.attr('data-id')+'"]', elements);
				if ( found ) {
					$('.comment-body.the_last').remove();
				}
				
				if(response){
					$('.load-more_container.comments').remove();
					$(".comments-container").append(response);
					$('.load-more_comments').attr('data-comments-page', parseInt(page)+1)
					$('.load-more_comments').html(Const.load_more);
				}
				
				if ( response.indexOf('data-comment-id="'+get_last+'"') !== -1 ) {
					$('.load-more_container.comments').remove();
				}
				
				if ( elements[0].firstChild.getAttribute('class').indexOf('load-more_comments')) {
					$('.load-more_container.comments').remove();
				}
			}
		});
	});
	
	$('body').on( 'click', '.maxgrid .comment-reply-link', function() {
		
		var comment_ID = $(this).attr('data-comment-id'),
			aria_label = $(this).attr('aria-label');
		$('#maxgrid-replay-to').addClass('active');
		$('#maxgrid-replay-to').html(aria_label);
		$('.maxgrid #comment_parent').val(comment_ID);
		$('#reach_content_outer #comment').focus();
		//scrollTo();
		var top = document.getElementById( 'maxgrid-replay-to' ).documentOffsetTop() - ( window.innerHeight / 2 );
		//window.scrollTo( 500, top );
		window.scrollTo({
		  top: top,
		  behavior: 'smooth',
		});
	});
	
	Element.prototype.documentOffsetTop = function () {
		return this.offsetTop + ( this.offsetParent ? this.offsetParent.documentOffsetTop() : 0 );
	};
	// Send Comment Form
	$('body').on('click','#ajax_submit', function() {
		
		$('.comment_validate-response').removeClass('active');
		
		var response = $(this).closest('div').find('.maxgrid_ajax_response'),
			post_id = $(this).attr('data-post-id'),
			author 	 = $('#commentform #author'),
			email 	 = $('#commentform #email'),
			textarea  = $('#commentform #comment'),
			action = $(this).closest('form').attr('action');
		
		if ($("#commentform #rating").length !== 0){
			var rating  = $('#commentform #rating');
		}
		var check = maxgrid_formValidate(author, email, textarea, rating);
		
		if ( check !== true ){
			$('.form_validate-alert .comment_validate-response').addClass('active');
			return false;
		}	
		
		jQuery.ajax({
			type : "POST",					
			url  : action,
			data : $('#commentform').serialize(),
			beforeSend:function(xhr){
				var args = new Object();
					args.size = 'small';
					args.relative = true;
				
				$('.comment_success-msg').html(maxgrid_lds_rolling_loader(args));
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				var ErrorMSG = Const.recaptcha === 'enable_recaptcha' ? Const.captcha_error : Const.unknown_error;
				$('.comment_success-msg').html(maxgrid_alertSurround(ErrorMSG));
				$('.comment_validate-response').addClass('active');
			},  
			success: function(data, textStatus){
				if(textStatus==="success") {
					maxgrid_getLastComment(post_id);
					$('.comment_success-msg').html(maxgrid_alertSurround(Const.thx_msg));
					$('.comment_validate-response').addClass('active');
					$('.comment_validate-response').addClass('success-msg');
				} else {
					$('.comment_success-msg').html(maxgrid_alertSurround(Const.unknown_error));
				}

            }
		});
	});
	
	$('body').on('click','.maxgrid #commentform input[type="text"], .maxgrid #commentform textarea, .comment_validate-response', function(e) {
		e.preventDefault();	
		$('.form_validate-alert .comment_validate-response').removeClass('active');
	});
	
	// Post Comments
	$('body').on( 'click', '.view-replay-btn', function () {
		
		var action = 'maxgrid_get_comment_replies',
			post_id = $(this).attr('data-post-id'),
			parent_id = $(this).attr('data-parent-id'),
			count = $(this).attr('data-count'),
			response_container = $('.e_comments_replies_response.id_'+parent_id);
		
		$(this).toggleClass('active');
		response_container.toggleClass('active');
		
		if ( response_container.attr('class').indexOf('active') !== -1 ) {
			$(this).html(Const.hide_replay_label);
		} else {
			if ( parseInt($(this).attr('data-count')) === 1 ) {
				$(this).html(Const.view_replay_label);
			} else {
				$(this).html(Const.view_replies_label.replace('%s', count));
			}
			
		}
		
		if ( response_container.html() !== '') {
			return false;
		}
		
		if ( $(this).attr('data-platform') === 'youtube' ) {
			action = 'maxgrid_ytb_comment_replies';
		}
		
		$.ajax({
		  	type : "POST",
			url  : Const.ajaxurl,
			data : {
				action 	: action,
				post_id : post_id,
				parent_id : parent_id,
			},
			beforeSend:function(xhr){
				var args = new Object();
					args.relative = true;
					args.color = 'grey';
					args.size = 'small';
				response_container.html(maxgrid_lds_rolling_loader(args));
			},
			success: function(response){
				response_container.html(response);
			}
		});
	});
	
	maxgrid_addScore(70, $("#fixture"));
});

function maxgrid_alertSurround(HTML){	
	return '<div class="comment_validate-response">' + HTML + '</div>';
}

// Form Validate
function maxgrid_formValidate(author, email, textarea, rating) {
	
	author.next().html('');
	email.next().html('');
	textarea.next().html('');
	jQuery('#review_form_wrapper > .form_validate-alert').html('');
	
	if ( textarea.attr('data-type') === 'review' ) {
		if ( rating.val() === "0") {
			jQuery('#review_form_wrapper > .form_validate-alert').html(maxgrid_alertSurround(Const.empty_rating));
			return false;
		}
    }
	
	if ( !Const.is_user_logged_in ) {		
		var emailFilter = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$/;

		if (author.val() === "") {
			author.focus();
			author.next().html(maxgrid_alertSurround(Const.empty_name));
			return false;
		}

		if (email.val() === "") {
			email.focus();
			email.next().html(maxgrid_alertSurround(Const.empty_email));
			return false;
		}
		
		if (!emailFilter.test(email.val())) {
			email.focus();
			email.next().html(maxgrid_alertSurround(Const.invalid_email));
			return false;
		}
	}
	
 	if (textarea.val() === "") {
        textarea.focus();
		textarea.next().html(maxgrid_alertSurround(Const.empty_comment));
        return false;
    }

    return true;
}

function maxgrid_getLastComment(post_id){
	jQuery.ajax({
		type : "POST",
		url  : Const.ajaxurl,
		data : {
			action 	: 'maxgrid_get_last_comment',
			post_id : post_id,
		},
		success: function(response){
			if(response){
				var last_item = jQuery(".comments-container div").last();
				if ( last_item.attr('class') === 'load-more_container comments') {
					last_item.before(response);
				} else {
					jQuery(".comments-container").append(response);	
				}
			}
		}
	});
}

function maxgrid_addScore(score, $domElement) {
	jQuery("<span class='stars-container'>").addClass("stars-" + score.toString()).text("★★★★★").appendTo($domElement);
}