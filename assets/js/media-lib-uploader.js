jQuery(document).ready(function ($) {
	var mediaUploader;
	
	$('body').on('click', '[class*="maxgrid"] #single_upload_button', function (e) {
		e.preventDefault();
		window.This = $(this);
		
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
			},
			multiple: false
		});

		// When a file is selected, grab the URL and set it as the text field's value & the src image preview
		mediaUploader.on('select', function () {

			var attachment = mediaUploader.state().get('selection').first().toJSON(),
				warp = This.next(),
				input = This.prev(),
				image = warp.find('img');
			warp.css('display', 'inline-block');
			input.val(attachment.url);
			image.attr('src', attachment.url);
		});

		// Open the uploader dialog
		mediaUploader.open();
	});
});