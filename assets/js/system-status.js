jQuery(document).ready(function($) {
    const { nonce, ajaxUrl, i18n } = wpccSystemStatus;
    
    function showNotice(message, type = 'error') {
        const $notice = $('<div>')
            .addClass(`notice notice-${type} is-dismissible`)
            .html(`<p>${message}</p>`);
        
        $('#wpbody-content').prepend($notice);
        
        // Make notices dismissible
        if (window.wp && window.wp.updates) {
            window.wp.updates.addDismissible($notice);
        }
    }

    // Export Logs
    $('#wpcc-export-logs').on('click', function(e) {
        e.preventDefault();
        window.location.href = `${ajaxUrl}?action=wpcc_export_logs&nonce=${nonce}`;
    });

    // Export Settings
    $('#wpcc-export-settings').on('click', function(e) {
        e.preventDefault();
        window.location.href = `${ajaxUrl}?action=wpcc_export_settings&nonce=${nonce}`;
    });

    // Import Settings
    $('#wpcc-import-settings').on('click', function() {
        if (!confirm(i18n.confirmImport)) {
            return;
        }

        const input = $('<input type="file" accept=".json" style="display: none">');
        $('body').append(input);

        input.on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('action', 'wpcc_import_settings');
            formData.append('nonce', nonce);
            formData.append('settings_file', file);

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showNotice(i18n.importSuccess, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotice(response.data || i18n.importError);
                    }
                },
                error: function(xhr) {
                    showNotice(xhr.responseJSON?.data || i18n.importError);
                }
            });
        });

        input.click();
    });

    // Run Diagnostics
    $('#wpcc-run-diagnostics').on('click', function() {
        const $button = $(this);
        const $results = $('#wpcc-diagnostics-results');
        
        $button.prop('disabled', true)
            .text(i18n.runningDiagnostics)
            .addClass('updating-message');
        
        $.post(ajaxUrl, {
            action: 'wpcc_run_diagnostics',
            nonce: nonce
        })
        .done(function(response) {
            if (response.success) {
                let html = '<div class="wpcc-diagnostics-results">';
                html += '<h3>Diagnostic Results</h3>';
                html += '<table class="widefat">';
                html += '<thead><tr><th>Check</th><th>Status</th><th>Message</th></tr></thead>';
                html += '<tbody>';
                
                Object.values(response.data).forEach(function(result) {
                    const statusClass = result.status === 'pass' ? 'success' : 
                                      result.status === 'warn' ? 'warning' : 'error';
                    
                    html += '<tr class="wpcc-status-row-' + statusClass + '">';
                    html += '<td>' + result.name + '</td>';
                    html += '<td><span class="wpcc-status wpcc-status-' + result.status + '"></span></td>';
                    html += '<td>' + result.message + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
                
                $results.html(html).show();
            } else {
                showNotice(response.data || 'Error running diagnostics');
            }
        })
        .fail(function(xhr) {
            showNotice(xhr.responseJSON?.data || 'Diagnostics failed. Please try again.');
        })
        .always(function() {
            $button.prop('disabled', false)
                .text('Run Diagnostics')
                .removeClass('updating-message');
        });
    });
});
