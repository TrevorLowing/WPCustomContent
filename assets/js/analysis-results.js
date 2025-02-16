/* global wpccAnalysis */
jQuery(function($) {
    'use strict';

    const AnalysisResults = {
        init() {
            this.bindEvents();
            this.checkBulkMessages();
        },

        bindEvents() {
            $('.wpcc-analyze-content').on('click', this.handleAnalysis);
        },

        handleAnalysis(e) {
            e.preventDefault();
            const $button = $(this);
            const $results = $button.closest('.wpcc-analysis-results');
            const postId = $button.data('post-id');

            // Disable button and show loading
            $button.prop('disabled', true);
            $results.find('.wpcc-analysis-loading').show();
            $results.find('.wpcc-analysis-content').hide();

            // Send analysis request
            $.post(ajaxurl, {
                action: 'wpcc_analyze_content',
                nonce: wpccAnalysis.nonce,
                post_id: postId
            })
            .done(response => {
                if (response.success && response.data) {
                    // Update content
                    $results.find('.wpcc-analysis-content').html(response.data.html).show();
                    
                    // Add timestamp
                    const now = new Date();
                    $results.find('.wpcc-analysis-timestamp').text(
                        wpccAnalysis.strings.lastAnalyzed.replace('%s', now.toLocaleString())
                    );
                    
                    // Show success message
                    AnalysisResults.showNotice('success', wpccAnalysis.strings.success);
                } else {
                    AnalysisResults.showNotice('error', response.data || wpccAnalysis.strings.error);
                }
            })
            .fail(() => {
                AnalysisResults.showNotice('error', wpccAnalysis.strings.error);
            })
            .always(() => {
                // Re-enable button and hide loading
                $button.prop('disabled', false);
                $results.find('.wpcc-analysis-loading').hide();
            });
        },

        showNotice(type, message) {
            const $notice = $('<div>')
                .addClass(`notice notice-${type} is-dismissible`)
                .append($('<p>').text(message));

            $('#wpbody-content').find('> .wrap > h1').after($notice);
            
            // Make notices dismissible
            $('.notice.is-dismissible').each((i, el) => {
                const $el = $(el);
                if ($el.find('button.notice-dismiss').length === 0) {
                    const $button = $('<button type="button" class="notice-dismiss">' +
                        '<span class="screen-reader-text">Dismiss this notice.</span>' +
                        '</button>');
                    $button.on('click', e => {
                        e.preventDefault();
                        $el.fadeTo(100, 0, () => {
                            $el.slideUp(100, () => {
                                $el.remove();
                            });
                        });
                    });
                    $el.append($button);
                }
            });
        },

        checkBulkMessages() {
            const urlParams = new URLSearchParams(window.location.search);
            const analyzed = urlParams.get('analyzed');
            const failed = urlParams.get('failed');

            if (analyzed || failed) {
                let message = '';
                if (analyzed > 0) {
                    message += wpccAnalysis.strings.bulkAnalyzed.replace('%d', analyzed);
                }
                if (failed > 0) {
                    message += wpccAnalysis.strings.bulkFailed.replace('%d', failed);
                }
                this.showNotice(failed > 0 ? 'warning' : 'success', message);
            }
        }
    };

    AnalysisResults.init();
});
