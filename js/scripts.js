function wpMediaEditor() {
    wp.media.editor.open();
    wp.media.editor.send.attachment = function(props, attachment) {
        jQuery('input.mm-sua-attachment-id').val(attachment.id);
        jQuery('figure.mm-sua-attachment-image img').remove();
        jQuery('figure.mm-sua-attachment-image').append(
            jQuery('<img>').attr({
                'src': attachment.sizes.thumbnail.url,
                'class': 'mm-sua-attachment-thumb',
                'alt': attachment.title
            })
        );
        jQuery('button.mm-sua-remove-media').fadeIn(250);
    };
}

function simpleUserAvatar() {
    var buttonAdd = jQuery('button.mm-sua-add-media');
    var buttonRemove = jQuery('button.mm-sua-remove-media');

    buttonAdd.on('click', function(event) {
        event.preventDefault();
        wpMediaEditor();
    });

    buttonRemove.on('click', function(event) {
        event.preventDefault();
        jQuery('input.mm-sua-attachment-id').val(0);
        jQuery('figure.mm-sua-attachment-image img').remove();
        jQuery(this).fadeOut(250);
    });

    jQuery(document).on('click', 'figure.mm-sua-attachment-image img', function() {
        wpMediaEditor();
    });

    if(
        jQuery('input.mm-sua-attachment-id').val() === 0
        || !jQuery('figure.mm-sua-attachment-image img').length
    ) buttonRemove.css( 'display', 'none' );
}

jQuery(document).ready(function() {
    simpleUserAvatar();

    jQuery('table.form-table tr.user-profile-picture').remove();
});
