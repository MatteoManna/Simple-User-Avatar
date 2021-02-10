(function( $ ) {
	'use strict';

    /*
     * Open WP Media editor
     *
     * @since 1.0
     */
    function wpMediaEditor() {
        wp.media.editor.open();
        wp.media.editor.send.attachment = function( props, attachment ) {
            $('input.mm-sua-attachment-id').val( attachment.id );
            $('figure.mm-sua-attachment-image.sua img').remove();
            $('figure.mm-sua-attachment-image.sua')
                .append(
                    $('<img>')
                        .attr({
                            'src': attachment.sizes.thumbnail.url,
                            'srcset': attachment.sizes.thumbnail.url,
                            'class': 'avatar avatar-96 photo',
                            'height': 96,
                            'width': 96,
                            'loading': 'lazy',
                            'alt': attachment.title
                        })
                );
			$('figure.mm-sua-attachment-image.default').addClass('hidden');
            $('button#mm-sua-remove-media').removeClass('hidden');
        };
    }

    /*
     * Init basic functions for two buttons (add and remove)
     *
     * @since 1.0
     */
    function initSimpleUserAvatar() {
        if( $('input.mm-sua-attachment-id').val() == '' ) {
            $('button#mm-sua-remove-media').addClass('hidden');
        }

        $(document)
            .on( 'click', 'button#mm-sua-add-media', function(event) {
                event.preventDefault();

                wpMediaEditor();
            })
            .on( 'click', 'button#mm-sua-remove-media', function(event) {
                event.preventDefault();

                $('input.mm-sua-attachment-id').val( 0 );
                $('figure.mm-sua-attachment-image.sua img').remove();
				$('figure.mm-sua-attachment-image.default').removeClass('hidden');
                $(this).addClass('hidden');
            })
            .on('click', 'figure.mm-sua-attachment-image img', function() {
                wpMediaEditor();
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
