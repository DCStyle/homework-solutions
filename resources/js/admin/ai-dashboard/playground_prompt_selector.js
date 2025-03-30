/**
 * AI Content Playground - Prompt Management
 * This module handles saved prompts and prompt templates
 * for different content types.
 */

$(function() {
    // Prompt management functionality
    const promptManager = (function() {
        // Variables for storing prompts
        let availablePrompts = [];
        let defaultPrompts = {}; // Store default prompts by content type

        // DOM elements as jQuery objects
        const $savedPromptsSelect = $('#saved-prompts');
        const $promptTextarea = $('#prompt');
        const $systemMessage = $('#system-message');
        const $systemMessageContainer = $('#system-message-container');
        const $modelSelect = $('#model');
        const $resetPromptBtn = $('#reset-prompt-btn');
        const $contentTypeButtons = $('.content-type-btn');
        const $savePromptBtn = $('#save-prompt-btn');
        const $savePromptModal = $('#save-prompt-modal');
        const $promptNameInput = $('#prompt-name');
        const $promptDescriptionInput = $('#prompt-description');
        const $saveSystemMessage = $('#save-system-message');
        const $saveSystemMessageContainer = $('#save-system-message-container');
        const $savePromptConfirmBtn = $('#save-prompt-confirm-btn');
        const $closeModalButtons = $('.close-modal');

        /**
         * Initialize the prompt manager
         */
        function init() {
            // Load default prompts on initialization
            loadDefaultPrompts();

            // Load prompts for the initial content type
            updatePromptSelector();

            // Set up event handlers
            bindEvents();
        }

        /**
         * Bind all event handlers
         */
        function bindEvents() {
            // Handle prompt selection
            $savedPromptsSelect.on('change', handlePromptSelection);

            // Reset prompt button handler
            $resetPromptBtn.on('click', handleResetPrompt);

            // Content type change handler
            $contentTypeButtons.on('click', function() {
                // After the content type button click handler runs
                setTimeout(updatePromptSelector, 100);
            });

            // Save prompt button handler
            $savePromptBtn.on('click', openSavePromptModal);

            // Close modal handlers
            $closeModalButtons.on('click', closeSavePromptModal);

            // Save prompt confirmation handler
            $savePromptConfirmBtn.on('click', savePrompt);

            // Handle clicking outside modal to close
            $savePromptModal.on('click', function(e) {
                if (e.target === this) {
                    closeSavePromptModal();
                }
            });

            // Prevent clicks inside modal content from closing the modal
            $savePromptModal.find('.modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        }

        /**
         * Load default prompts via AJAX
         */
        function loadDefaultPrompts() {
            $.ajax({
                url: '/admin/ai-dashboard/prompts/default',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(prompts) {
                    // Store default prompts globally
                    defaultPrompts = prompts;

                    // Make available to other parts of the application
                    window.promptTemplates = defaultPrompts;

                    // Initialize with default prompt for current content type
                    const contentType = getActiveContentType();
                    if (contentType && defaultPrompts[contentType]) {
                        $promptTextarea.val(defaultPrompts[contentType]);
                    }
                },
                error: function(error) {
                    console.error('Error loading default prompts:', error);
                }
            });
        }

        /**
         * Get the currently active content type
         */
        function getActiveContentType() {
            return $('.content-type-btn.border-indigo-300').data('type');
        }

        /**
         * Load saved prompts for the current content type
         */
        function loadSavedPrompts(contentType) {
            // Ensure content type is provided
            contentType = contentType || getActiveContentType();

            // Clear existing options
            $savedPromptsSelect.empty().append($('<option>', {
                value: '',
                text: '-- Chọn prompt đã lưu --'
            }));

            // Show loading indicator
            $savedPromptsSelect.append($('<option>', {
                disabled: true,
                text: 'Đang tải prompts...'
            }));

            // Fetch prompts via AJAX
            $.ajax({
                url: '/admin/ai-dashboard/prompts/by-type',
                type: 'GET',
                data: {
                    content_type: contentType
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(prompts) {
                    // Store prompts for later use
                    availablePrompts = prompts;

                    // Remove loading indicator
                    $savedPromptsSelect.find('option:disabled').remove();

                    // Add prompts to the selector
                    if (prompts.length === 0) {
                        $savedPromptsSelect.append($('<option>', {
                            disabled: true,
                            text: 'Không có prompt nào'
                        }));
                    } else {
                        // Add prompts to dropdown
                        $.each(prompts, function(index, prompt) {
                            $savedPromptsSelect.append($('<option>', {
                                value: prompt.id,
                                text: prompt.name,
                                'data-model': prompt.ai_model || '',
                                'data-system-message': prompt.system_message || '',
                                'data-prompt-text': prompt.prompt_text || ''
                            }));
                        });
                    }

                    // Reset prompt selector to use default prompt
                    $savedPromptsSelect.val('');

                    // Apply default prompt if one exists for this content type
                    if (defaultPrompts[contentType]) {
                        $promptTextarea.val(defaultPrompts[contentType]);
                    }

                    // Refresh Select2 if available
                    if ($.fn.select2 && $savedPromptsSelect.data('select2')) {
                        $savedPromptsSelect.trigger('change');
                    }
                },
                error: function(error) {
                    console.error('Error loading prompts:', error);
                    $savedPromptsSelect.find('option:disabled').text('Lỗi khi tải prompts');
                }
            });
        }

        /**
         * Handle prompt selection from dropdown
         */
        function handlePromptSelection() {
            const promptId = $savedPromptsSelect.val();
            const contentType = getActiveContentType();

            if (!promptId) {
                // If no prompt selected, use default prompt for current content type
                if (defaultPrompts[contentType]) {
                    $promptTextarea.val(defaultPrompts[contentType]);
                }
                return;
            }

            // Find the selected prompt using jQuery's grep function
            const selectedPrompt = $.grep(availablePrompts, function(p) {
                return p.id == promptId;
            })[0];

            if (selectedPrompt) {
                // Update the prompt textarea
                $promptTextarea.val(selectedPrompt.prompt_text);

                // Update model if specified
                if (selectedPrompt.ai_model) {
                    $modelSelect.val(selectedPrompt.ai_model).trigger('change');
                }

                // Update system message if specified
                if (selectedPrompt.system_message) {
                    $systemMessage.val(selectedPrompt.system_message);

                    // If the model is not DeepSeek but system message exists, switch to DeepSeek
                    const currentModel = $modelSelect.val();
                    if (currentModel && !currentModel.startsWith('deepseek') && selectedPrompt.system_message) {
                        // Try to select any DeepSeek model
                        const $deepseekOption = $modelSelect.find('option[value^="deepseek"]').first();
                        if ($deepseekOption.length) {
                            $modelSelect.val($deepseekOption.val()).trigger('change');
                        }
                    }

                    // Show system message container
                    $systemMessageContainer.removeClass('hidden');
                }
            }
        }

        /**
         * Handle reset prompt button click
         */
        function handleResetPrompt() {
            const contentType = getActiveContentType();

            // Reset prompt selector
            $savedPromptsSelect.val('').trigger('change');

            // Reset to default prompt
            if (defaultPrompts[contentType]) {
                $promptTextarea.val(defaultPrompts[contentType]);
            } else {
                $promptTextarea.val('');
            }

            // Reset system message
            $systemMessage.val('');

            // Hide system message container if it's visible
            if (!$systemMessageContainer.hasClass('hidden') && !$modelSelect.val().startsWith('deepseek')) {
                $systemMessageContainer.addClass('hidden');
            }
        }

        /**
         * Update the prompt selector when content type changes
         */
        function updatePromptSelector() {
            const contentType = getActiveContentType();
            if (contentType) {
                loadSavedPrompts(contentType);
            }
        }

        /**
         * Open the save prompt modal
         */
        function openSavePromptModal() {
            // Get current content type
            const contentType = getActiveContentType();
            const promptText = $promptTextarea.val();
            const aiModel = $modelSelect.val();
            const systemMessage = $systemMessage.val();

            // Validate prompt text
            if (!$.trim(promptText)) {
                if (window.createAlert) {
                    window.createAlert('Vui lòng nhập nội dung prompt trước khi lưu', 'warning', $('#content-details-card'), true);
                } else {
                    alert('Vui lòng nhập nội dung prompt trước khi lưu');
                }
                return;
            }

            // Populate the save prompt modal
            $promptNameInput.val('');
            $promptDescriptionInput.val('');
            $saveSystemMessage.val(systemMessage);

            // Show/hide system message field based on model
            if (aiModel && aiModel.startsWith('deepseek') && systemMessage) {
                $saveSystemMessageContainer.removeClass('hidden');
            } else {
                $saveSystemMessageContainer.addClass('hidden');
            }

            // Show the modal with animation
            $savePromptModal.removeClass('hidden');
            setTimeout(function() {
                $savePromptModal.find('.modal-content')
                    .removeClass('opacity-0 scale-95')
                    .addClass('opacity-100 scale-100');
            }, 10);

            // Focus the name input
            setTimeout(function() {
                $promptNameInput.focus();
            }, 200);
        }

        /**
         * Close the save prompt modal
         */
        function closeSavePromptModal() {
            // Animate out
            $savePromptModal.find('.modal-content')
                .removeClass('opacity-100 scale-100')
                .addClass('opacity-0 scale-95');

            // Hide after animation
            setTimeout(function() {
                $savePromptModal.addClass('hidden');
            }, 200);
        }

        /**
         * Save the prompt
         */
        function savePrompt() {
            const name = $promptNameInput.val();
            const description = $promptDescriptionInput.val();
            const promptText = $promptTextarea.val();
            const aiModel = $modelSelect.val();
            const systemMessage = $saveSystemMessage.val();
            const contentType = getActiveContentType();

            // Validate name
            if (!$.trim(name)) {
                if (window.createAlert) {
                    window.createAlert('Vui lòng nhập tên cho prompt', 'warning', $savePromptModal.find('.modal-content'), true);
                } else {
                    alert('Vui lòng nhập tên cho prompt');
                }
                return;
            }

            // Prepare data for submission
            const data = {
                name: name,
                description: description,
                prompt_text: promptText,
                content_type: contentType,
                ai_model: aiModel,
                system_message: systemMessage
            };

            // Show a loading indicator on the button
            const $saveBtn = $savePromptConfirmBtn;
            const originalText = $saveBtn.html();
            $saveBtn.html('<span class="mr-2"><span class="iconify animate-spin" data-icon="mdi-loading"></span></span> Đang lưu...');
            $saveBtn.prop('disabled', true);

            // Send AJAX request to save the prompt
            $.ajax({
                url: '/admin/ai-dashboard/save-prompt',
                type: 'POST',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        if (window.createAlert) {
                            window.createAlert('Prompt đã được lưu thành công!', 'success', $savePromptModal.find('.modal-content'), true);
                        }

                        // Close the modal after a short delay
                        setTimeout(function() {
                            closeSavePromptModal();

                            // Reload prompts for the current content type
                            loadSavedPrompts(contentType);
                        }, 1500);
                    } else {
                        // Show error message
                        const errorMessage = response.error || 'Không xác định';
                        if (window.createAlert) {
                            window.createAlert('Lỗi khi lưu prompt: ' + errorMessage, 'danger', $savePromptModal.find('.modal-content'), true);
                        } else {
                            alert('Lỗi khi lưu prompt: ' + errorMessage);
                        }
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Đã xảy ra lỗi khi lưu prompt.';

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = $.map(errors, function(messages) {
                            return messages;
                        }).join('\n');
                    }

                    if (window.createAlert) {
                        window.createAlert(errorMessage, 'danger', $savePromptModal.find('.modal-content'), true);
                    } else {
                        alert(errorMessage);
                    }
                },
                complete: function() {
                    // Restore button state
                    $saveBtn.html(originalText);
                    $saveBtn.prop('disabled', false);
                }
            });
        }

        // Public API
        return {
            init: init,
            loadSavedPrompts: loadSavedPrompts,
            getActiveContentType: getActiveContentType
        };
    })();

    // Initialize prompt manager
    promptManager.init();

    // Make load saved prompts function available globally
    // for other modules to call when needed
    window.loadSavedPrompts = promptManager.loadSavedPrompts;
});
