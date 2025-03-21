$(document).ready(function() {
    // Variables for storing prompts
    let availablePrompts = [];
    let defaultPrompts = {}; // Store default prompts

    // Load default prompts on initialization
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

                // Initialize with default prompt for current content type
                const contentType = $('.content-type-btn.border-indigo-300').data('type');
                if (contentType && defaultPrompts[contentType]) {
                    $('#prompt').val(defaultPrompts[contentType]);
                }
            },
            error: function(error) {
                console.error('Error loading default prompts:', error);
            }
        });
    }

    // Function to load saved prompts for the current content type
    function loadSavedPrompts(contentType) {
        const $savedPromptsSelect = $('#saved-prompts');

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
                    prompts.forEach(prompt => {
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
                    $('#prompt').val(defaultPrompts[contentType]);
                }
            },
            error: function(error) {
                console.error('Error loading prompts:', error);
                $savedPromptsSelect.find('option:disabled').text('Lỗi khi tải prompts');
            }
        });
    }

    // Handle prompt selection
    $('#saved-prompts').on('change', function() {
        const promptId = $(this).val();
        const contentType = $('.content-type-btn.border-indigo-300').data('type');

        if (!promptId) {
            // If no prompt selected, use default prompt for current content type
            if (defaultPrompts[contentType]) {
                $('#prompt').val(defaultPrompts[contentType]);
            }
            return;
        }

        // Find the selected prompt
        const selectedPrompt = availablePrompts.find(p => p.id == promptId);

        if (selectedPrompt) {
            // Update the prompt textarea
            $('#prompt').val(selectedPrompt.prompt_text);

            // Update model if specified
            if (selectedPrompt.ai_model) {
                $('#model').val(selectedPrompt.ai_model).trigger('change');
            }

            // Update system message if specified
            if (selectedPrompt.system_message) {
                $('#system-message').val(selectedPrompt.system_message);

                // If the model is not DeepSeek but system message exists, switch to DeepSeek
                if (!$('#model').val().startsWith('deepseek') && selectedPrompt.system_message) {
                    $('#model').val('deepseek-v3').trigger('change');
                }
            }
        }
    });

    // Reset prompt button handler
    $('#reset-prompt-btn').on('click', function() {
        const contentType = $('.content-type-btn.border-indigo-300').data('type');

        // Reset prompt selector
        $('#saved-prompts').val('');

        // Reset to default prompt
        if (defaultPrompts[contentType]) {
            $('#prompt').val(defaultPrompts[contentType]);
        } else {
            $('#prompt').val('');
        }

        // Reset system message
        $('#system-message').val('');
    });

    // Load prompts when content type changes
    function updatePromptSelector() {
        const contentType = $('.content-type-btn.border-indigo-300').data('type');
        if (contentType) {
            loadSavedPrompts(contentType);
        }
    }

    // Call updatePromptSelector when content type changes
    $('.content-type-btn').on('click', function() {
        // After the content type button click handler runs
        setTimeout(updatePromptSelector, 100);
    });

    // Load default prompts on page load
    loadDefaultPrompts();

    // Initial load of prompts
    updatePromptSelector();

    // Save Prompt Modal Functionality
    const savePromptModal = document.getElementById('save-prompt-modal');

    // Open modal when save button is clicked
    $('#save-prompt-btn').on('click', function() {
        // Get current content type
        const contentType = $('.content-type-btn.border-indigo-300').data('type');
        const promptText = $('#prompt').val();
        const aiModel = $('#model').val();
        const systemMessage = $('#system-message').val();

        // Validate prompt text
        if (!promptText.trim()) {
            alert('Vui lòng nhập nội dung prompt trước khi lưu');
            return;
        }

        // Populate the save prompt modal
        $('#prompt-name').val('');
        $('#prompt-description').val('');
        $('#save-system-message').val(systemMessage);

        // Show/hide system message field based on model
        if (aiModel.startsWith('deepseek') && systemMessage) {
            $('#save-system-message-container').removeClass('hidden');
        } else {
            $('#save-system-message-container').addClass('hidden');
        }

        // Show the modal
        $(savePromptModal).removeClass('hidden');
    });

    // Close modal functionality
    $('.close-modal').on('click', function() {
        $(savePromptModal).addClass('hidden');
    });

    // Save prompt button click handler
    $('#save-prompt-confirm-btn').on('click', function() {
        const name = $('#prompt-name').val();
        const description = $('#prompt-description').val();
        const promptText = $('#prompt').val();
        const aiModel = $('#model').val();
        const systemMessage = $('#save-system-message').val();
        const contentType = $('.content-type-btn.border-indigo-300').data('type');

        // Validate
        if (!name.trim()) {
            alert('Vui lòng nhập tên cho prompt');
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
        const $saveBtn = $('#save-prompt-confirm-btn');
        const originalText = $saveBtn.html();
        $saveBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Đang lưu...');
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
                    // Close the modal
                    $(savePromptModal).addClass('hidden');

                    // Show success message
                    alert('Prompt đã được lưu thành công!');

                    // Reload prompts for the current content type
                    loadSavedPrompts(contentType);
                } else {
                    alert('Lỗi khi lưu prompt: ' + (response.error || 'Không xác định'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Đã xảy ra lỗi khi lưu prompt.';

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                }

                alert(errorMessage);
            },
            complete: function() {
                // Restore button state
                $saveBtn.html(originalText);
                $saveBtn.prop('disabled', false);
            }
        });
    });
});
