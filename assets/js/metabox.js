/**
 * Max Grid MetaBox
 *
 */

jQuery(document).ready(function($){
	// Thumbnail
	try{
		if ($('#maxgrid_thumbnail_url').val() === '') {
			$('.maxgrid-img-wrap-extra').css('display', 'none');
		}
		$('.maxgrid-img-wrap-extra .close').on('click', function() {
			var idExtra = $(this).closest('.maxgrid-img-wrap-extra');
			idExtra.css('display', 'none');
			$('#maxgrid_thumbnail_url').val('');
		});

		var srcExtra = document.getElementById("prev-img-extra").getAttribute('src');
		if ( srcExtra === "" ) {
			$('.maxgrid-img-wrap-extra').css('display', 'none');
		}

		// Show Hide Featured Fields
		// onload
		if( $('#maxgrid_featured_mode').val() === "image" ){
			$('#maxgrid_featured_mode').closest('tr').nextAll().eq( 0 ).css('display', 'none');
		} else {
			$('#maxgrid_featured_mode').closest('tr').nextAll().eq( 0 ).css('display', '');
		}

		$(".tr_featured_sublabel." + $('#maxgrid_featured_mode').val()).css('display', 'table-row');
		$(".featured_sublabel." + $('#maxgrid_featured_mode').val()).css('display', 'block');
		$(".embed_video_url." + $('#maxgrid_featured_mode').val() + ', .button-primary.' + $('#maxgrid_featured_mode').val() ).css('display', 'inline-block');

		// onchange
		$('#maxgrid_featured_mode').change(function() {
			$(".tr_featured_sublabel, .featured_sublabel, .embed_video_url, .button-primary.mp4").css('display', 'none');
			$(".tr_featured_sublabel." + $(this).val()).css('display', 'table-row');
			$(".featured_sublabel." + $(this).val()).css('display', 'block');
			$(".embed_video_url." + $(this).val() + ', .button-primary.' + $(this).val()).css('display', 'inline-block');

			if($(this).val() === "image" ){
				$(this).closest('tr').nextAll().eq( 0 ).css('display', 'none');
			} else {
				$(this).closest('tr').nextAll().eq( 0 ).css('display', '');
			}	
		});

		$("#meta_row_thumbnail").css('display', 'block');
		$(".maxgrid_mp4_video_thumbnail").css('display', '');

		// Show Hide Audio Player Fields
		if($('#maxgrid_audio_player').val() === "wp_player" ){
			$('.tr_maxgrid_audio_file').css('display', 'table-row');
			$('.tr_maxgrid_soundcloud_code').css('display', 'none');
		} else if($('#maxgrid_audio_player').val() === "soundcloud_player" ) {
			$('.tr_maxgrid_audio_file').css('display', 'none');
			$('.tr_maxgrid_soundcloud_code').css('display', 'table-row');
		}

		// onchange
		$('#maxgrid_audio_player').change(function() {
			if($(this).val() === "wp_player" ){
				$('.tr_maxgrid_audio_file').css('display', 'table-row');
				$('.tr_maxgrid_soundcloud_code').css('display', 'none');
			} else if($(this).val() === "soundcloud_player" ) {
				$('.tr_maxgrid_audio_file').css('display', 'none');
				$('.tr_maxgrid_soundcloud_code').css('display', 'table-row');
			}	
		});

		// Files Upload
		var mp4Uploader;
		$('.button-primary.mp4').click(function(e) {
			e.preventDefault();
			// If the uploader object has already been created, reopen the dialog
			if (mp4Uploader) {
				mp4Uploader.open();
				return;
			}

			// Extend the wp.media object
			mp4Uploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image'
			}, multiple: false });

			// When a file is selected, grab the URL and set it as the text field's value
			mp4Uploader.on('select', function() {
				var attachment = mp4Uploader.state().get('selection').first().toJSON();
				$('.embed_video_url.mp4').val(attachment.url);
				maxgrid_setVideoDuration(attachment.url);
			});

			// Open the uploader dialog
			mp4Uploader.open();
		});

		maxgrid_setVideoDuration($('.embed_video_url.mp4').val());

		$("body").on("keyup paste", ".embed_video_url.mp4", function() {	
			maxgrid_setVideoDuration(this.value);
		});

		var thumbUploader;
		$('#thumbnail_upload_button').click(function(e) {
			e.preventDefault();

			// If the uploader object has already been created, reopen the dialog
			if (thumbUploader) {
				thumbUploader.open();
				return;
			}

			// Extend the wp.media object
			thumbUploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image'
			}, multiple: false });

			// When a file is selected, grab the URL and set it as the text field's value
			thumbUploader.on('select', function() {
				var attachment = thumbUploader.state().get('selection').first().toJSON();
				$('.maxgrid-img-wrap-extra').css('display', 'inline-block');
				$('#maxgrid_thumbnail_url').val(attachment.url);
				$('.prev-img-extra').attr('src', attachment.url);
			});

			// Open the uploader dialog
			thumbUploader.open();
		});
	} catch(e){
		//alert('An error has occurred : ' + e);
	}
	
	try{
		var mediaUploader;
		$('#upload-button:not(.audio-file)').click(function(e) {
			e.preventDefault();
			
			// If the uploader object has already been created, reopen the dialog
			if (mediaUploader) {
				mediaUploader.open();
				return;
			}
			
			// Extend the wp.media object
			mediaUploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image'
			}, multiple: false });
			
			// When a file is selected, grab the URL and set it as the text field's value
			mediaUploader.on('select', function() {
				var attachment = mediaUploader.state().get('selection').first().toJSON();
				$('#maxgrid_download_file').val(attachment.url);
			});
			
			// Open the uploader dialog
			mediaUploader.open();
		});
	} catch(e){
		console.log('An error has occurred : ' + e);
	}
	
	try{	
		var audioUploader;
		$('#upload-button.audio-file').click(function(e) {
			e.preventDefault();
			
			// If the uploader object has already been created, reopen the dialog
			if (audioUploader) {
				audioUploader.open();
				return;
			}
			
			// Extend the wp.media object
			audioUploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image'
				},
				library: { 
					type: 'audio' // limits the frame to show only images
				},
				multiple: false
			});
			
			// When a file is selected, grab the URL and set it as the text field's value
			audioUploader.on('select', function() {
				var attachment = audioUploader.state().get('selection').first().toJSON();
				$('#maxgrid_audio_file').val(attachment.url);
			});
			
			// Open the uploader dialog
			audioUploader.open();
		});
	} catch(e){
		//alert('An error has occurred : ' + e);
	}
	
	// Download Type
	$('#maxgrid_protect_mode').change(function(){	
		if($(this).val() === "protect" ){
			$(this).closest('tr').nextAll().eq( 0 ).css('display', '');
		} else {
			$(this).closest('tr').nextAll().eq( 0 ).css('display', 'none');
		}

	});

	if($('#maxgrid_protect_mode').val() === "protect" ){
		$('#maxgrid_protect_mode').closest('tr').nextAll().eq( 0 ).css('display', '');
	} else {
		$('#maxgrid_protect_mode').closest('tr').nextAll().eq( 0 ).css('display', 'none');
	}
});

// Set Video Duration
function maxgrid_setVideoDuration(url){
	var video = jQuery('#pg_mp4-video')[0];
	var source = jQuery('#pg_mp4-video source')[0];
	source.setAttribute('src', url);
	video.load();
	var i = setInterval(function() {
		if(video.readyState > 0) {
			var duration = Math.round(video.duration);
			jQuery('.maxgrid_mp4_v_duration').val(maxgrid_convertVideoDuration(duration));
			jQuery('#pg_mp4-video').attr('preload', 'none');
			clearInterval(i);
		}
	}, 200);
}

// Convert Video Duration
function maxgrid_convertVideoDuration(duration) {	
	var date = new Date(duration * 1000);
	var hh = date.getUTCHours();
	var mm = date.getUTCMinutes();
	var ss = date.getSeconds();
	// These lines ensure you have two-digits
	if (hh < 10) {hh = "0"+hh;}
	if (mm < 10) {mm = "0"+mm;}
	if (ss < 10) {ss = "0"+ss;}
	// This hide Hours if == "00"
	if (hh === "00") { hh = "";} else { hh = hh+":"; }
	// This formats your string to HH:MM:SS
	var t = hh+mm+":"+ss;
	return t;
}