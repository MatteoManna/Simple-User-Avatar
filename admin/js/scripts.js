(function( $ ) {

	'use strict';

	// Tags name
	var tagAttachmentId     = 'input[name="' + sua_obj.input_name + '"]';
	var tagAttachmentAvatar = '.sua-attachment-avatar';
	var tagAttachmentDesc   = '#sua-attachment-description';
	var tagButtonAdd        = '#btn-media-add';
	var tagButtonRemove     = '#btn-media-remove';

	// jQuery elements by tags
	var elAttachmentId      = $( tagAttachmentId );
	var elAttachmentAvatar  = $( tagAttachmentAvatar) ;
	var elAttachmentDesc    = $( tagAttachmentDesc );
	var elButtonAdd         = $( tagButtonAdd );
	var elButtonRemove      = $( tagButtonRemove );

	// WordPress default media sizes
	var WPMediaSizes        = [ 'full', 'large', 'medium', 'thumbnail' ];

	// Get default Src and default SrcSet
	var defaultSrc          = sua_obj.default_avatar_src;
	var defaultSrcSet       = sua_obj.default_avatar_srcset;


	/*
	 * Update attachment
	 *
	 * @since  3.6
	 * @return void
	 */
	function updateAttachment( attachmentSrc = '', attachmentSrcSet = '', attachmentId = null ) {

		// Change the image attributes
		elAttachmentAvatar.attr({
			'src': attachmentSrc,
			'srcset': attachmentSrcSet
		});
		
		// Set attachment ID value
		elAttachmentId.val( attachmentId === null ? '' : parseInt( attachmentId ) );

		// Toggle class hidden
		elAttachmentDesc.toggleClass( 'hidden' );
		elButtonRemove.toggleClass( 'hidden' );
	
	}


	/*
	 * Init functions
	 *
	 * @since 2.8
	 */
	$(function() {

		// Set click functions
		$(document)
			.on( 'click', tagButtonAdd, function() {

				// Open WordPress Media Library
				wp.media.editor.open();

				// WP Media Editor function
				wp.media.editor.send.attachment = function( props, attachment ) {

					// Set attachment Src to default URL
					var attachmentSrc = attachment.url;

					// If there is a smaller version I use it
					for ( const WPMediaSize of WPMediaSizes ) {
						if ( typeof attachment.sizes[WPMediaSize] !== 'undefined' && typeof attachment.sizes[WPMediaSize].url !== 'undefined' ) {
							attachmentSrc = attachment.sizes[WPMediaSize].url;
						}
					}

					// Update Attachment
					updateAttachment( attachmentSrc, attachmentSrc, attachment.id );

				}

			})
			.on( 'click', tagButtonRemove, function() {

				// Update Attachment
				updateAttachment( defaultSrc, defaultSrcSet );

			})
			.on( 'click', tagAttachmentAvatar, function() {

				// Trigger to add button
				elButtonAdd.trigger( 'click' );

			});


	});

})( jQuery );
