jQuery(document).ready(function ($) {
    let file_frame;

    $('.select-file').on('click', function (e) {
        e.preventDefault();

        // If the media frame already exists, reopen it
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame
        file_frame = wp.media({
            title: 'Select File for ShareLink',
            button: { text: 'Use this file' },
            multiple: false
        });

        // When a file is selected
        file_frame.on('select', function () {
            const attachment = file_frame.state().get('selection').first().toJSON();
            $('#resource_value').val(attachment.url);
        });

        // Finally, open the modal
        file_frame.open();
    });
});
