/**
 * AI Content Playground - Content Generation
 * This module handles AI content generation and applying generated content to selected items.
 */

$(function() {
    // Content generation functionality
    const contentGenerator = (function() {
        // DOM elements as jQuery objects
        const $generateBtn = $('#generate-btn');
        const $applyBtn = $('#apply-btn');
        const $resultsContainer = $('#results-container');
        const $loadingIndicator = $('#loading-indicator');
        const $generatingStatus = $('#generating-status');
        const $promptResponse = $('#prompt-response');
        const $responseContainer = $('#response-container');
        const $modelSelect = $('#model');
        const $providerSelect = $('#provider');
        const $promptTextarea = $('#prompt');
        const $systemMessage = $('#system-message');
        const $temperatureRange = $('#temperature');
        const $temperatureValue = $('#temperature-value');
        const $contentFinalSelector = $('#content-final-selector');

        // Templates
        const postsTemplate = $('#posts-template').html();
        const descriptionTemplate = $('#description-template').html();

        // Generated content storage
        let generatedContent = null;

        /**
         * Initialize the content generator
         */
        function init() {
            // Set up event handlers
            bindEvents();

            // Initialize temperature slider
            initTemperatureSlider();
        }

        /**
         * Bind all event handlers
         */
        function bindEvents() {
            // Generate button click handler
            $generateBtn.on('click', handleGenerate);

            // Apply button click handler
            $applyBtn.on('click', handleApply);
        }

        /**
         * Initialize temperature slider
         */
        function initTemperatureSlider() {
            $temperatureRange.on('input', function() {
                const value = $(this).val();
                $temperatureValue.text(value);
            });
        }

        /**
         * Handle generate button click
         */
        function handleGenerate() {
            // Validate required fields
            if (!validateInputs()) {
                return;
            }

            // Get content type, content ID, and other parameters
            const contentType = getActiveContentType();
            const contentId = $contentFinalSelector.val();
            const provider = $providerSelect.val();
            const model = $modelSelect.val();
            const prompt = $promptTextarea.val();
            const systemMessage = $systemMessage.val();
            const temperature = $temperatureRange.val();

            // Show loading state
            $loadingIndicator.removeClass('hidden');
            $generateBtn.prop('disabled', true);
            $generateBtn.html('<span class="mr-2"><span class="iconify animate-spin" data-icon="mdi-loading"></span></span> Đang tạo...');

            // Show results container with loading status
            $resultsContainer.removeClass('hidden');
            $generatingStatus.html(`
                <div class="flex items-center p-4 mb-4 bg-blue-50 text-blue-700 rounded-lg">
                    <span class="mr-2"><span class="iconify animate-spin" data-icon="mdi-loading"></span></span>
                    <span>Đang tạo nội dung với AI...</span>
                </div>
            `);
            $promptResponse.empty();
            $responseContainer.empty();

            // Send request to server
            $.ajax({
                url: '/admin/ai-dashboard/generate-sample',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    content_type: contentType,
                    content_id: contentId,
                    provider: provider,
                    model: model,
                    prompt: prompt,
                    system_message: systemMessage,
                    temperature: temperature
                },
                success: function(response) {
                    console.log('Generation response:', response); // Log the response for debugging

                    if (response.success) {
                        // Store generated content for later use
                        generatedContent = {
                            result: response.result,
                            content: response.content,
                            contentType: contentType
                        };

                        // Display the generated content
                        displayGeneratedContent(response, contentType);

                        // Show success status
                        $generatingStatus.html(`
                            <div class="flex items-center p-4 mb-4 bg-green-50 text-green-700 rounded-lg">
                                <span class="mr-2 iconify" data-icon="mdi-check-circle"></span>
                                <span>Nội dung được tạo thành công!</span>
                            </div>
                        `);

                        // Enable apply button
                        $applyBtn.prop('disabled', false);
                    } else {
                        // Show error status
                        $generatingStatus.html(`
                            <div class="flex items-center p-4 mb-4 bg-red-50 text-red-700 rounded-lg">
                                <span class="mr-2 iconify" data-icon="mdi-alert-circle"></span>
                                <span>Lỗi: ${response.error || 'Không thể tạo nội dung'}</span>
                            </div>
                        `);

                        // Disable apply button
                        $applyBtn.prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error generating content:', error);

                    // Show error status
                    $generatingStatus.html(`
                        <div class="flex items-center p-4 mb-4 bg-red-50 text-red-700 rounded-lg">
                            <span class="mr-2 iconify" data-icon="mdi-alert-circle"></span>
                            <span>Lỗi: ${xhr.responseJSON?.error || 'Có lỗi xảy ra khi tạo nội dung'}</span>
                        </div>
                    `);

                    // Disable apply button
                    $applyBtn.prop('disabled', true);
                },
                complete: function() {
                    // Reset button state
                    $loadingIndicator.addClass('hidden');
                    $generateBtn.prop('disabled', false);
                    $generateBtn.html(`
                        <span class="mr-2">
                            <span class="iconify" data-icon="mdi-lightning-bolt"></span>
                        </span>
                        Tạo Nội Dung
                    `);
                }
            });
        }

        /**
         * Display generated content based on content type
         */
        function displayGeneratedContent(data, contentType) {
            // Clear existing content
            $promptResponse.empty();
            $responseContainer.empty();

            // Show the prompt used if available
            if (data.prompt) {
                $promptResponse.html(`
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <h5 class="font-medium text-blue-700 mb-1">Prompt Used:</h5>
                        <p class="text-blue-900 text-sm">${data.prompt}</p>
                    </div>
                `);
            }

            // Get the generated result - it's HTML content in the 'result' field
            const generatedHtml = data.result || '';

            // Display the generated content
            if (generatedHtml) {
                let contentLabel = '';

                switch (contentType) {
                    case 'posts':
                        contentLabel = 'Generated Meta Content';
                        break;
                    case 'chapters':
                        contentLabel = 'Generated Chapter Description';
                        break;
                    case 'books':
                        contentLabel = 'Generated Book Description';
                        break;
                    case 'book_groups':
                        contentLabel = 'Generated Book Group Description';
                        break;
                    default:
                        contentLabel = 'Generated Content';
                }

                $responseContainer.html(`
                    <div>
                        <h5 class="font-medium text-gray-700 mb-2">${contentLabel}:</h5>
                        <div class="p-4 bg-white border border-gray-200 rounded-lg">
                            ${generatedHtml}
                        </div>
                    </div>
                `);
            } else {
                $responseContainer.html(`
                    <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg">
                        <span class="iconify mr-2" data-icon="mdi-alert"></span>
                        Không có nội dung được tạo
                    </div>
                `);
            }
        }

        // These functions are no longer needed as we're displaying the HTML directly
        // from the result field

        /**
         * Handle apply button click
         */
        function handleApply() {
            if (!generatedContent) {
                if (window.createAlert) {
                    window.createAlert('Không có nội dung để áp dụng', 'warning', $resultsContainer, true);
                }
                return;
            }

            // Get content type and ID
            const contentType = getActiveContentType();
            const contentId = $contentFinalSelector.val();

            // Show loading state
            $applyBtn.prop('disabled', true);
            $applyBtn.html('<span class="mr-2"><span class="iconify animate-spin" data-icon="mdi-loading"></span></span> Đang áp dụng...');

            // Send request to apply content
            $.ajax({
                url: '/admin/ai-dashboard/apply-prompt',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    content_type: contentType,
                    content_id: contentId,
                    generated_content: generatedContent.result,
                    original_content: generatedContent.content
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        if (window.createAlert) {
                            window.createAlert('Nội dung đã được áp dụng thành công!', 'success', $resultsContainer, true);
                        }

                        // Update content details display
                        if (window.updateContentDetails) {
                            window.updateContentDetails(contentId, true);
                        }
                    } else {
                        // Show error message
                        if (window.createAlert) {
                            window.createAlert(`Error: ${response.error || 'Failed to apply content'}`, 'danger', $resultsContainer, true);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error applying content:', xhr);

                    // Show error message
                    if (window.createAlert) {
                        window.createAlert(`Error: ${xhr.responseJSON?.error || 'An error occurred while applying content'}`, 'danger', $resultsContainer, true);
                    }
                },
                complete: function() {
                    // Reset button state
                    $applyBtn.prop('disabled', false);
                    $applyBtn.html(`
                        <span class="mr-2">
                            <span class="iconify" data-icon="mdi-check"></span>
                        </span>
                        Áp Dụng Thay Đổi
                    `);
                }
            });
        }

        /**
         * Validate that all required inputs are filled
         */
        function validateInputs() {
            const contentId = $contentFinalSelector.val();
            const provider = $providerSelect.val();
            const model = $modelSelect.val();
            const prompt = $promptTextarea.val();

            if (!contentId) {
                if (window.createAlert) {
                    window.createAlert('Vui lòng chọn nội dung', 'warning', $('#content-details-card'), true);
                } else {
                    alert('Vui lòng chọn nội dung');
                }
                return false;
            }

            if (!provider) {
                if (window.createAlert) {
                    window.createAlert('Vui lòng chọn nhà cung cấp AI', 'warning', $('#content-details-card'), true);
                } else {
                    alert('Vui lòng chọn nhà cung cấp AI');
                }
                return false;
            }

            if (!model) {
                if (window.createAlert) {
                    window.createAlert('Vui lòng chọn mô hình AI', 'warning', $('#content-details-card'), true);
                } else {
                    alert('Vui lòng chọn mô hình AI');
                }
                return false;
            }

            if (!prompt) {
                if (window.createAlert) {
                    window.createAlert('Vui lòng nhập prompt', 'warning', $('#content-details-card'), true);
                } else {
                    alert('Vui lòng nhập prompt');
                }
                return false;
            }

            return true;
        }

        /**
         * Get the active content type
         */
        function getActiveContentType() {
            return $('.content-type-btn.border-indigo-300').data('type');
        }

        // Public interface
        return {
            init: init
        };
    })();

    // Initialize content generator
    contentGenerator.init();
});
