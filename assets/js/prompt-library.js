/* global wpccPromptLibrary */
jQuery(function($) {
    'use strict';

    const PromptLibrary = {
        init() {
            this.bindEvents();
        },

        bindEvents() {
            // Add new prompt
            $('.wpcc-add-prompt').on('click', this.openModal);
            $('.wpcc-modal-close').on('click', this.closeModal);
            $('#wpcc-prompt-form').on('submit', this.savePrompt);

            // Prompt actions
            $('.wpcc-use-prompt').on('click', this.usePrompt);
            $('.wpcc-pin-prompt').on('click', this.pinPrompt);
            $('.wpcc-unpin-prompt').on('click', this.unpinPrompt);
            $('.wpcc-delete-prompt').on('click', this.deletePrompt);
        },

        openModal(e) {
            e.preventDefault();
            $('#wpcc-prompt-modal').show();
        },

        closeModal(e) {
            e.preventDefault();
            $('#wpcc-prompt-modal').hide();
            $('#wpcc-prompt-form')[0].reset();
        },

        savePrompt(e) {
            e.preventDefault();
            const $form = $(this);
            const data = {
                action: 'wpcc_save_prompt',
                nonce: wpccPromptLibrary.nonce,
                ...Object.fromEntries(new FormData($form[0]))
            };

            $.post(ajaxurl, data, response => {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(wpccPromptLibrary.strings.error);
                }
            });
        },

        usePrompt(e) {
            e.preventDefault();
            const $card = $(this).closest('.wpcc-prompt-card');
            const content = $card.find('.wpcc-prompt-content').text().trim();
            const $library = $(this).closest('.wpcc-prompt-library');
            const field = $library.data('field');
            
            $(`#wpcc-prompt-${field}`).val(content);
        },

        pinPrompt(e) {
            e.preventDefault();
            const $button = $(this);
            const promptId = $button.data('id');
            const $library = $button.closest('.wpcc-prompt-library');
            const field = $library.data('field');

            const data = {
                action: 'wpcc_pin_prompt',
                nonce: wpccPromptLibrary.nonce,
                prompt_id: promptId,
                field: field
            };

            $.post(ajaxurl, data, response => {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(wpccPromptLibrary.strings.error);
                }
            });
        },

        unpinPrompt(e) {
            e.preventDefault();
            const $button = $(this);
            const promptId = $button.data('id');
            const $library = $button.closest('.wpcc-prompt-library');
            const field = $library.data('field');

            const data = {
                action: 'wpcc_pin_prompt',
                nonce: wpccPromptLibrary.nonce,
                prompt_id: promptId,
                field: field
            };

            $.post(ajaxurl, data, response => {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(wpccPromptLibrary.strings.error);
                }
            });
        },

        deletePrompt(e) {
            e.preventDefault();
            if (!confirm(wpccPromptLibrary.strings.deleteConfirm)) {
                return;
            }

            const $button = $(this);
            const promptId = $button.data('id');

            const data = {
                action: 'wpcc_delete_prompt',
                nonce: wpccPromptLibrary.nonce,
                prompt_id: promptId
            };

            $.post(ajaxurl, data, response => {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(wpccPromptLibrary.strings.error);
                }
            });
        }
    };

    PromptLibrary.init();
});
