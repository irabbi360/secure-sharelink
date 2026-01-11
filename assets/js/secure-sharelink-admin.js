jQuery(document).ready(function ($) {
    let file_frame;

    // Function to update resource value visibility
    function updateResourceValueDisplay() {
        const resourceType = $('#resource_type').val();
        
        // Hide all resource value wrappers
        $('#resource_value_file').hide();
        $('#resource_value_url').hide();
        $('#resource_value_data').hide();
        
        // Show appropriate wrapper based on type
        if (resourceType === 'file') {
            $('#resource_value_file').show();
        } else if (resourceType === 'url' || resourceType === '301_redirect') {
            $('#resource_value_url').show();
        } else if (resourceType === 'data') {
            $('#resource_value_data').show();
        }
    }

    // Function to sync visible input to hidden input
    function syncResourceValue() {
        let value = '';
        
        if ($('#resource_value_file').is(':visible')) {
            value = $('#resource_value_file_input').val();
        } else if ($('#resource_value_url').is(':visible')) {
            value = $('#resource_value_url_input').val();
        } else if ($('#resource_value_data').is(':visible')) {
            value = $('#resource_value_data_input').val();
        }
        
        $('#resource_value_hidden').val(value);
    }

    // Handle resource type change
    $('#resource_type').on('change', function () {
        updateResourceValueDisplay();
    });

    // Trigger on page load to set initial state
    updateResourceValueDisplay();

    // Sync values on input change
    $(document).on('input change', '#resource_value_file_input, #resource_value_url_input, #resource_value_data_input', function () {
        syncResourceValue();
    });

    // Sync values before form submission
    $('form').on('submit', function () {
        syncResourceValue();
    });

    // Handle media picker button
    $(document).on('click', '.select-file', function (e) {
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
            // Set value to the file input in the visible wrapper
            $('#resource_value_file_input').val(attachment.url);
            syncResourceValue();
        });

        // Finally, open the modal
        file_frame.open();
    });
});
