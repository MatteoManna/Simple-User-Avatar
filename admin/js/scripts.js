(function( $ ) {

	'use strict';

	/*
	 * Init functions
	 *
	 * @since 2.8
	 */
	$(function() {
		
		// If attachment_id is empty
		if ( $('.sua__attachment--id').val() === '' ) {

			// Hide remove button
			$('#btn-media-remove').addClass('hidden');

		} else {

			// Hide caption for default avatar
			$('.sua__attachment--figcaption').addClass('hidden');

		}

		$(document)
			.on( 'click', '#btn-media-add', function( event ) {

				// Prevent default
				event.preventDefault();

				// WordPress media sizes
				var mediaSizes = [ 'full', 'large', 'medium', 'thumbnail' ];

				// Open WordPress Media Library
				wp.media.editor.open();

				// On click
				wp.media.editor.send.attachment = function( props, attachment ) {

					// Attachment URL for default
					var attachmentUrl = attachment.url;

					// If there is a smaller version I use it
					for ( const mediaSize of mediaSizes ) {
						if ( typeof attachment.sizes[mediaSize] !== 'undefined' && typeof attachment.sizes[mediaSize].url !== 'undefined' ) {
							attachmentUrl = attachment.sizes[mediaSize].url;
						}
					}			

					// Set attachment_id value
					$('.sua__attachment--id').val( attachment.id );

					// Change the image attributes
					$('.sua__attachment--figure')
						.find('img')
						.attr({
							'src': attachmentUrl,
							'srcset': attachmentUrl,
							'alt': attachment.name
						});

					// Hide the figcaption
					$('.sua__attachment--figcaption').addClass('hidden');

					// Show remove button
					$('#btn-media-remove').removeClass('hidden');

				};

			})
			.on( 'click', '#btn-media-remove', function( event ) {

				// Prevent default
				event.preventDefault();

				// Get default Src and default SrcSet
				var defaultSrc = sua_obj.default_avatar_src;
				var defaultSrcSet = sua_obj.default_avatar_srcset;

				// Set default URL on the image
				$('.sua__attachment--figure')
					.find('img')
					.attr({
						'src': defaultSrc,
						'srcset': defaultSrcSet
					});

				// Set attachment_id to empty
				$('.sua__attachment--id').val( '' );

				// Show the figcaption
				$('.sua__attachment--figcaption').removeClass('hidden');

				// Hide remove button
				$(this).addClass('hidden');

			})
			.on( 'click', '.sua__attachment--figure img', function() {

				// Trigger to button
				$('#btn-media-add').trigger( 'click' );

			});

	});

})( jQuery );
