(function( $ ) {

	'use strict';

	/*
	 * Open WP Media editor
	 *
	 * @since 1.0
	 */
	function wpMediaEditor() {

		// Open WordPress Media Library
		wp.media.editor.open();

		// On click
		wp.media.editor.send.attachment = function( props, attachment ) {

			// Set attachment_id value
			$('.sua__attachment--id').val( attachment.id );

			// Change the image attributes
			$('.sua__attachment--figure')
				.find('img')
				.attr({
					'src': attachment.sizes.thumbnail.url,
					'srcset': attachment.sizes.thumbnail.url,
					'alt': attachment.name
				});

			// Hide the figcaption
			$('.sua__attachment--figcaption').addClass('hidden');

			// Show remove button
			$('#btn-media-remove').removeClass('hidden');

		};

	}


	/*
	 * Init basic functions for two buttons (add and remove)
	 *
	 * @since 1.0
	 */
	function initSimpleUserAvatar() {

		// If attachment_id is empty
		if( $('.sua__attachment--id').val() == '' ) {

			// Hide remove button
			$('#btn-media-remove').addClass('hidden');

		} else {

			// Hide caption for default avatar
			$('.sua__attachment--figcaption').addClass('hidden');

		}

		$(document)
			.on( 'click', '#btn-media-add', function( event ) {

				event.preventDefault();

				// Open WordPress Media Library
				wpMediaEditor();

			})
			.on( 'click', '#btn-media-remove', function( event ) {

				event.preventDefault();

				// Get default URL
				var defaultUrl = sua_obj.default_avatar_url;

				// Set default URL on the image
				$('.sua__attachment--figure')
					.find('img')
					.attr({
						'src': defaultUrl,
						'srcset': defaultUrl
					});

				// Set attachment_id to empty
				$('.sua__attachment--id').val( '' );

				// Show the figcaption
				$('.sua__attachment--figcaption').removeClass('hidden');

				// Hide remove button
				$(this).addClass('hidden');

			})
			.on('click', '.sua__attachment--figure img', function() {

				// Trigger to button
				$('#btn-media-add').trigger( 'click' );

			});

	}


	/*
	 * Init functions
	 *
	 * @since 2.8
	 */
	$(function() {
		initSimpleUserAvatar();
	});

})( jQuery );
