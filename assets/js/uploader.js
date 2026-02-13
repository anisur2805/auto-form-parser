jQuery(document).ready(function($) {
    const $uploadZone = $('#afp-upload-zone');
    const $status = $('#afp-status');
    const $form = $('#afp-main-form');
    const $resetBtn = $('#afp-reset');

    // Drag and Drop Handlers
    $uploadZone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    $uploadZone.on('dragleave', function() {
        $(this).removeClass('dragover');
    });

    $uploadZone.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFileUpload(files[0]);
        }
    });

    $uploadZone.on('click', function() {
        $('<input type="file" accept=".md,.txt,.pdf,.docx">').on('change', function() {
            if (this.files.length > 0) {
                handleFileUpload(this.files[0]);
            }
        }).click();
    });

    function handleFileUpload(file) {
        const formData = new FormData();
        formData.append('action', 'afp_parse_document');
        formData.append('file', file);
        formData.append('nonce', afp_vars.nonce);

        showStatus('Reading Resume...', 'info');

        $.ajax({
            url: afp_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    fillForm(response.data);
                    showStatus('Resume Parsed! Reviewing details...', 'success');
                } else {
                    showStatus(response.data, 'error');
                }
            },
            error: function() {
                showStatus('An error occurred while parsing the resume.', 'error');
            }
        });
    }

    function fillForm(data) {
        $form.find('.afp-field').removeClass('highlighted');
        
        for (const [key, value] of Object.entries(data)) {
            const $input = $(`#afp_${key}`);
            if ($input.length && value) {
                $input.val(value).closest('.afp-field').addClass('highlighted');
            }
        }
    }

    function showStatus(message, type) {
        $status.text(message).removeClass('success error info').addClass(type).fadeIn();
        if (type === 'success') {
            setTimeout(() => {
                $status.fadeOut();
            }, 5000);
        }
    }

    $resetBtn.on('click', function() {
        $form[0].reset();
        $form.find('.afp-field').removeClass('highlighted');
        $status.fadeOut();
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        alert('Application Sent! Thank you for applying.');
    });
});
