/**
 * Stats Hierarchical Content Selection System
 * Enhanced version of stats page with hierarchical content selection
 */

(function() {
    // DOM elements
    const $contentTypeButtons = $('.content-type-btn');
    const $contentTypeInput = $('#content-type');
    const $categorySelector = $('#category-selector');
    const $groupSelector = $('#group-selector');
    const $bookSelector = $('#book-selector');
    const $chapterSelector = $('#chapter-selector');

    const $resetFilterBtn = $('#reset-filter-btn');
    const $applyFilterBtn = $('#apply-filter-btn');
    const $searchInput = $('#search-input');
    const $seoStatusSelect = $('#seo-status');
    const $limitSelect = $('#limit-select');

    const $loadingIndicator = $('#loading-indicator');
    const $contentTableContainer = $('#content-table-container');
    const $contentTbody = $('#content-tbody');
    const $emptyRow = $('#empty-row');
    const $tableTitle = $('#table-title');
    const $totalCount = $('#total-count');
    const $paginationContainer = $('#pagination-container');
    const $pagination = $('#pagination');
    const $filterStatus = $('#filter-status');
    const $contentCount = $('#content-count');

    // Category selector container
    const $categorySelectorContainer = $('#category-selector-container');
    const $groupSelectorContainer = $('#group-selector-container');
    const $bookSelectorContainer = $('#book-selector-container');
    const $chapterSelectorContainer = $('#chapter-selector-container');

    // Modal instances
    let generateSingleModal;
    let bulkGenerateModal;

    // Track current state
    let currentContentType = '';
    let currentCategoryId = '';
    let currentGroupId = '';
    let currentBookId = '';
    let currentChapterId = '';
    let currentPage = 1;
    let currentSearch = '';
    let currentSeoStatus = 'missing';
    let currentLimit = 50;
    let apiBaseUrl = '';

    // Store default prompts and system messages
    let defaultPrompts = {};
    let systemMessages = {};

    // Initialize function
    function init() {
        // Set API base URL
        apiBaseUrl = window.location.origin;

        // Get initial content type
        currentContentType = $contentTypeInput.val() || 'posts';

        // Highlight the active content type button
        highlightActiveContentType();

        // Initialize Bootstrap modals
        if (document.getElementById('generate-single-modal')) {
            generateSingleModal = new bootstrap.Modal(document.getElementById('generate-single-modal'));
        }

        if (document.getElementById('bulk-generate-modal')) {
            bulkGenerateModal = new bootstrap.Modal(document.getElementById('bulk-generate-modal'));
        }

        // Load default prompts
        loadDefaultPrompts();

        // Initialize all event handlers
        setupEventHandlers();

        // Set initial values for filters
        $seoStatusSelect.val(currentSeoStatus);
        $limitSelect.val(currentLimit);

        // Load categories
        loadCategories();
    }

    // Load default prompts from the API
    function loadDefaultPrompts() {
        // Load default prompts
        $.ajax({
            url: `${apiBaseUrl}/admin/ai-dashboard/prompts/default`,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                defaultPrompts = data;
            },
            error: function(error) {
                console.error('Error loading default prompts:', error);
            }
        });

        // Load the system messages for all content types
        // We'll do this for all types at once to avoid multiple API calls later
        const contentTypes = ['posts', 'chapters', 'books', 'book_groups'];

        contentTypes.forEach(type => {
            loadSystemMessage(type);
        });
    }

    // Load system message for a specific content type
    function loadSystemMessage(contentType) {
        $.ajax({
            url: `${apiBaseUrl}/admin/ai-dashboard/system-message/${contentType}`,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                // Store the system message
                systemMessages[contentType] = data;
            },
            error: function(error) {
                console.error(`Error loading system message for ${contentType}:`, error);
                // If there's an error, we'll leave it as undefined and let the backend provide a default
            }
        });
    }

    // Highlight active content type button
    function highlightActiveContentType() {
        $contentTypeButtons.removeClass('border-indigo-300 bg-indigo-50').addClass('border-transparent bg-gray-50');
        $(`.content-type-btn[data-type="${currentContentType}"]`).removeClass('border-transparent bg-gray-50').addClass('border-indigo-300 bg-indigo-50');
    }

    // Set up all event handlers
    function setupEventHandlers() {
        // Content type selection
        $contentTypeButtons.on('click', function() {
            // Update visual state of buttons
            $contentTypeButtons.removeClass('border-indigo-300 bg-indigo-50').addClass('border-transparent bg-gray-50');
            $(this).removeClass('border-transparent bg-gray-50').addClass('border-indigo-300 bg-indigo-50');

            // Reset selectors
            resetHierarchicalSelectors();

            // Get selected content type
            currentContentType = $(this).data('type');
            $contentTypeInput.val(currentContentType);

            // Update selector visibilities for the selected content type
            updateSelectorConfiguration();

            // Load initial categories
            loadCategories();
        });

        // Category selection
        $categorySelector.on('change', function() {
            currentCategoryId = $(this).val();
            resetSelectors('group');

            if (currentCategoryId) {
                $groupSelectorContainer.show();
                loadBookGroups(currentCategoryId);
                $filterStatus.text('Đang xem nhóm sách trong danh mục đã chọn');
            } else {
                $groupSelectorContainer.hide();
                $bookSelectorContainer.hide();
                $chapterSelectorContainer.hide();
                $filterStatus.text('Chọn danh mục để bắt đầu');
            }

            updateContentCount();
        });

        // Group selection
        $groupSelector.on('change', function() {
            currentGroupId = $(this).val();
            resetSelectors('book');

            if (currentGroupId) {
                if (currentContentType === 'book_groups') {
                    // For book groups, this is the final selection
                    $filterStatus.text('Đã chọn nhóm sách');
                } else {
                    $bookSelectorContainer.show();
                    loadBooks(currentGroupId);
                    $filterStatus.text('Đang xem sách trong nhóm đã chọn');
                }
            } else {
                $bookSelectorContainer.hide();
                $chapterSelectorContainer.hide();
                $filterStatus.text('Chọn nhóm sách để tiếp tục');
            }

            updateContentCount();
        });

        // Book selection
        $bookSelector.on('change', function() {
            currentBookId = $(this).val();
            resetSelectors('chapter');

            if (currentBookId) {
                if (currentContentType === 'books') {
                    // For books, this is the final selection
                    $filterStatus.text('Đã chọn sách');
                } else {
                    $chapterSelectorContainer.show();
                    loadChapters(currentBookId);
                    $filterStatus.text('Đang xem chương trong sách đã chọn');
                }
            } else {
                $chapterSelectorContainer.hide();
                $filterStatus.text('Chọn sách để tiếp tục');
            }

            updateContentCount();
        });

        // Chapter selection
        $chapterSelector.on('change', function() {
            currentChapterId = $(this).val();

            if (currentChapterId) {
                $filterStatus.text('Đã chọn chương sách');
            } else {
                $filterStatus.text('Chọn chương để tiếp tục');
            }

            updateContentCount();
        });

        // Apply filter button
        $applyFilterBtn.on('click', function() {
            // Get values from filter inputs
            currentSearch = $searchInput.val();
            currentSeoStatus = $seoStatusSelect.val();
            currentLimit = $limitSelect.val();
            currentPage = 1;

            // Load data based on filters
            loadContentData();
        });

        // Reset filter button
        $resetFilterBtn.on('click', function() {
            // Reset all filters
            resetHierarchicalSelectors();
            $searchInput.val('');
            $seoStatusSelect.val('missing');
            $limitSelect.val('50');

            // Reset state variables
            currentSearch = '';
            currentSeoStatus = 'missing';
            currentLimit = 50;
            currentPage = 1;

            // Load categories again
            loadCategories();

            // Update filter status
            $filterStatus.text('Bộ lọc đã được đặt lại');
        });

        // Select All functionality
        $(document).on('change', '#select-all', function() {
            $(".item-checkbox").prop('checked', $(this).prop('checked'));
            updateSelectedCount();
        });

        // Item checkbox click handler
        $(document).on('change', '.item-checkbox', function() {
            updateSelectedCount();

            // Check if all checkboxes are checked
            const allChecked = $(".item-checkbox:checked").length === $(".item-checkbox").length;
            const someChecked = $(".item-checkbox:checked").length > 0;

            $("#select-all").prop('checked', allChecked);
            $("#select-all").prop('indeterminate', !allChecked && someChecked);
        });

        // Generate Single button click (delegated)
        $(document).on('click', '.generate-single', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const title = $(this).data('title');

            $("#modal-content-id").val(id);
            $("#modal-content-type").val(type);
            $("#modal-content-title").text(title);

            // Reset form state
            $("#modal-prompt-source").val('default');
            $("#modal-saved-prompts-container").addClass('d-none');
            $("#modal-prompt-editor").addClass('d-none');
            $("#modal-system-message-container").addClass('d-none');
            $("#modal-results-container").addClass('d-none');
            $("#modal-apply-btn").addClass('d-none');

            // Set default prompt and system message
            setDefaultPrompt(type);

            // Reset parameters
            $("#modal-temperature").val(0.7);
            $("#modal-temperature-value").text('0.7');
            $("#modal-max-tokens").val(1000);
            $("#modal-max-tokens-value").text('1000');

            // Show modal
            if (generateSingleModal) {
                generateSingleModal.show();
            }
        });

        // Pagination click handler (delegated)
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page) {
                currentPage = page;
                loadContentData();
                // Scroll to top of results
                $contentTableContainer[0].scrollIntoView({ behavior: 'smooth' });
            }
        });

        // Temperature and max tokens sliders
        $("#modal-temperature").on('input', function() {
            $("#modal-temperature-value").text($(this).val());
        });

        $("#modal-max-tokens").on('input', function() {
            $("#modal-max-tokens-value").text($(this).val());
        });

        // Bulk temperature and max tokens sliders
        $("#bulk-temperature").on('input', function() {
            $("#bulk-temperature-value").text($(this).val());
        });

        $("#bulk-max-tokens").on('input', function() {
            $("#bulk-max-tokens-value").text($(this).val());
        });

        // Prompt source toggle
        $("#modal-prompt-source").on('change', function() {
            if ($(this).val() === 'custom') {
                $("#modal-saved-prompts-container").addClass('d-none');
                $("#modal-prompt-editor").removeClass('d-none');
            } else if ($(this).val() === 'saved') {
                $("#modal-saved-prompts-container").removeClass('d-none');
                $("#modal-prompt-editor").removeClass('d-none');
            } else {
                $("#modal-saved-prompts-container").addClass('d-none');
                $("#modal-prompt-editor").addClass('d-none');
            }
        });

        // Model toggle for system message
        $("#modal-model").on('change', function() {
            if ($(this).val().startsWith('deepseek')) {
                $("#modal-system-message-container").removeClass('d-none');
            } else {
                $("#modal-system-message-container").addClass('d-none');
            }
        });

        // Bulk model toggle for system message
        $("#bulk-model").on('change', function() {
            if ($(this).val().startsWith('deepseek')) {
                $("#bulk-system-message-container").removeClass('d-none');
            } else {
                $("#bulk-system-message-container").addClass('d-none');
            }
        });

        // Bulk Generate button
        $("#bulk-generate-btn").on('click', function() {
            const selectedItems = $(".item-checkbox:checked");

            if (selectedItems.length === 0) {
                alert('Vui lòng chọn ít nhất một mục');
                return;
            }

            // Reset form state
            $("#bulk-prompt-source").val('default');
            $("#bulk-saved-prompts-container").addClass('d-none');
            $("#bulk-prompt-editor").addClass('d-none');
            $("#bulk-system-message-container").addClass('d-none');
            $("#bulk-progress-container").addClass('d-none');

            // Set default prompt and system message
            setDefaultPrompt(currentContentType, 'bulk');

            // Reset parameters
            $("#bulk-temperature").val(0.7);
            $("#bulk-temperature-value").text('0.7');
            $("#bulk-max-tokens").val(1000);
            $("#bulk-max-tokens-value").text('1000');

            // Show modal
            if (bulkGenerateModal) {
                bulkGenerateModal.show();
            }
        });

        // Generate button in single modal
        $("#modal-generate-btn").on('click', function() {
            const contentId = $("#modal-content-id").val();
            const contentType = $("#modal-content-type").val();
            const model = $("#modal-model").val();
            const prompt = $("#modal-prompt").val();
            const temperature = $("#modal-temperature").val();
            const maxTokens = $("#modal-max-tokens").val();
            const systemMessage = $("#modal-system-message").val();
            const useHtmlMeta = $("#modal-use-html-meta").is(':checked');

            if (!contentId || !contentType || !prompt) {
                alert('Thiếu các trường bắt buộc');
                return;
            }

            // Show loading
            $("#modal-loading").removeClass('d-none');
            $("#modal-results-container").removeClass('d-none');
            $("#modal-results").html('<p class="text-gray-500">Đang tạo nội dung...</p>');

            // Create form data
            const formData = new FormData();
            formData.append('content_id', contentId);
            formData.append('content_type', contentType);
            formData.append('model', model);
            formData.append('prompt', prompt);
            formData.append('temperature', temperature);
            formData.append('max_tokens', maxTokens);
            formData.append('use_html_meta', useHtmlMeta ? '1' : '0');

            if (model.startsWith('deepseek') && systemMessage) {
                formData.append('system_message', systemMessage);
            }

            // Send AJAX request
            $.ajax({
                url: `${apiBaseUrl}/admin/ai-dashboard/generate-sample`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    // Hide loading
                    $("#modal-loading").addClass('d-none');

                    if (data.success) {
                        // Display results
                        if (contentType === 'posts') {
                            $("#modal-results").html(`
                                <div class="mb-4">
                                    <h6 class="mb-2 font-semibold">Tiêu Đề Meta</h6>
                                    <div class="rounded border border-gray-300 p-3">
                                        ${data.result.meta_title}
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-2 font-semibold">Mô Tả Meta</h6>
                                    <div class="rounded border border-gray-300 p-3">
                                        ${data.result.meta_description}
                                    </div>
                                </div>
                            `);
                        } else {
                            $("#modal-results").html(`
                                <div>
                                    <h6 class="mb-2 font-semibold">Mô Tả</h6>
                                    <div class="rounded border border-gray-300 p-3">
                                        ${data.result.split("\n").join("<br>")}
                                    </div>
                                </div>
                            `);
                        }

                        // Show apply button
                        $("#modal-apply-btn").removeClass('d-none');
                    } else {
                        $("#modal-results").html(`<p class="text-danger">${data.error || 'Đã xảy ra lỗi'}</p>`);
                    }
                },
                error: function(error) {
                    console.error('Error:', error);
                    $("#modal-loading").addClass('d-none');
                    $("#modal-results").html('<p class="text-danger">Đã xảy ra lỗi. Vui lòng thử lại.</p>');
                }
            });
        });

        // Apply button
        $("#modal-apply-btn").on('click', function() {
            const contentId = $("#modal-content-id").val();
            const contentType = $("#modal-content-type").val();
            const model = $("#modal-model").val();
            const prompt = $("#modal-prompt").val();
            const temperature = $("#modal-temperature").val();
            const maxTokens = $("#modal-max-tokens").val();
            const systemMessage = $("#modal-system-message").val();
            const useHtmlMeta = $("#modal-use-html-meta").is(':checked');

            // Show loading
            $(this).prop('disabled', true);
            $(this).text('Đang áp dụng...');

            // Create form data
            const formData = new FormData();
            formData.append('content_type', contentType);
            formData.append('filter_type', 'ids');
            formData.append('filter_id', contentId);
            formData.append('model', model);
            formData.append('prompt', prompt);
            formData.append('temperature', temperature);
            formData.append('max_tokens', maxTokens);
            formData.append('use_html_meta', useHtmlMeta ? '1' : '0');

            if (model.startsWith('deepseek') && systemMessage) {
                formData.append('system_message', systemMessage);
            }

            // Send AJAX request
            $.ajax({
                url: `${apiBaseUrl}/admin/ai-dashboard/apply-prompt`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        alert('Áp dụng thay đổi thành công!');
                        loadContentData(); // Reload content data
                        if (generateSingleModal) {
                            generateSingleModal.hide();
                        }
                    } else {
                        alert('Lỗi: ' + (data.error || 'Không thể áp dụng thay đổi'));
                        $("#modal-apply-btn").prop('disabled', false);
                        $("#modal-apply-btn").text('Áp Dụng Thay Đổi');
                    }
                },
                error: function(error) {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi. Vui lòng thử lại.');
                    $("#modal-apply-btn").prop('disabled', false);
                    $("#modal-apply-btn").text('Áp Dụng Thay Đổi');
                }
            });
        });

        let pendingBulkRequests = false;

        // Bulk generate start button
        $("#bulk-generate-start-btn").on('click', function() {
            const selectedItems = $(".item-checkbox:checked");

            if (selectedItems.length === 0) {
                alert('Vui lòng chọn ít nhất một mục');
                return;
            }

            const model = $("#bulk-model").val();
            const promptSource = $("#bulk-prompt-source").val();
            let prompt = $("#bulk-prompt").val();
            let promptId = '';
            const temperature = $("#bulk-temperature").val();
            const maxTokens = $("#bulk-max-tokens").val();
            const systemMessage = $("#bulk-system-message").val();
            const useHtmlMeta = $("#bulk-use-html-meta").is(':checked');

            // Get prompt ID if using saved prompt
            if (promptSource === 'saved') {
                promptId = $("#bulk-saved-prompt").val();
                if (!promptId) {
                    alert('Vui lòng chọn một prompt đã lưu');
                    return;
                }
            } else if (promptSource === 'default') {
                // Use default prompt for this content type
                prompt = defaultPrompts[currentContentType] || '';
            }

            if (!prompt && !promptId) {
                alert('Vui lòng cung cấp lời nhắc');
                return;
            }

            // Get all selected IDs
            const selectedIds = $.map(selectedItems, function(item) {
                return $(item).data('id');
            }).join(',');

            // Create form data
            const formData = new FormData();
            formData.append('content_type', currentContentType);
            formData.append('filter_type', 'ids');
            formData.append('filter_id', selectedIds);
            formData.append('model', model);
            formData.append('prompt', prompt);
            formData.append('prompt_id', promptId);
            formData.append('temperature', temperature);
            formData.append('max_tokens', maxTokens);
            formData.append('use_html_meta', useHtmlMeta ? '1' : '0');

            if (model.startsWith('deepseek') && systemMessage) {
                formData.append('system_message', systemMessage);
            }

            // Close the modal first before making the AJAX request
            if (typeof bulkGenerateModal !== 'undefined' && bulkGenerateModal) {
                bulkGenerateModal.hide();
            } else {
                // Fallback methods
                $('#bulk-generate-modal').modal('hide');
            }

            // Show immediate notification that job is being queued
            if (typeof showNotification === 'function') {
                showNotification({
                    title: 'Đang xử lý',
                    message: 'Đang gửi yêu cầu tạo nội dung vào hàng đợi...',
                    type: 'info'
                });
            }

            // Set pending request flag
            pendingBulkRequests = true;

            // Now send AJAX request after modal is closed
            $.ajax({
                url: `${apiBaseUrl}/admin/ai-dashboard/queue-bulk-generation`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    console.log('Bulk generation response:', data);
                    pendingBulkRequests = false;
                    
                    if (data.success) {
                        // Store the job information in localStorage
                        try {
                            const timestamp = new Date().toISOString();
                            const jobInfo = {
                                jobId: data.job_id || 'unknown',
                                contentCount: selectedItems.length,
                                timestamp: timestamp,
                                contentType: currentContentType,
                                status: 'queued'
                            };
                            
                            // Get existing jobs or initialize empty array
                            const pendingJobs = JSON.parse(localStorage.getItem('pendingBulkJobs') || '[]');
                            pendingJobs.push(jobInfo);
                            localStorage.setItem('pendingBulkJobs', JSON.stringify(pendingJobs));
                        } catch (e) {
                            console.error('Error storing job info:', e);
                        }
                        
                        if (typeof showNotification === 'function') {
                            showNotification({
                                title: 'Đã gửi yêu cầu',
                                message: 'Công việc tạo nội dung đã được gửi vào hàng đợi. Bạn có thể an toàn điều hướng đến trang khác.',
                                type: 'success',
                                duration: 8000
                            });
                        } else {
                            alert('Công việc tạo nội dung đã được gửi vào hàng đợi. Bạn sẽ nhận thông báo khi hoàn tất.');
                        }
                    } else {
                        if (typeof showNotification === 'function') {
                            showNotification({
                                title: 'Lỗi',
                                message: data.error || 'Không thể xử lý các mục',
                                type: 'error'
                            });
                        } else {
                            alert('Lỗi: ' + (data.error || 'Không thể xử lý các mục'));
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    
                    pendingBulkRequests = false;
                    
                    if (typeof showNotification === 'function') {
                        showNotification({
                            title: 'Lỗi',
                            message: 'Đã xảy ra lỗi khi gửi yêu cầu. Vui lòng thử lại sau.',
                            type: 'error'
                        });
                    } else {
                        alert('Đã xảy ra lỗi khi gửi yêu cầu. Vui lòng thử lại sau.');
                    }
                },
                complete: function() {
                    // Reset button state in case the modal reopens
                    $("#bulk-generate-start-btn").prop('disabled', false);
                    $("#bulk-generate-start-btn").text('Bắt Đầu Tạo');
                }
            });
        });

        // Add this code to display pending jobs information on page load
        $(document).ready(function() {
            try {
                const pendingJobs = JSON.parse(localStorage.getItem('pendingBulkJobs') || '[]');
                const recentJobs = pendingJobs.filter(job => {
                    // Only show jobs from the last 24 hours
                    const jobTime = new Date(job.timestamp).getTime();
                    const oneDayAgo = Date.now() - (24 * 60 * 60 * 1000);
                    return jobTime > oneDayAgo;
                });
                
                if (recentJobs.length > 0) {
                    if (typeof showNotification === 'function') {
                        showNotification({
                            title: 'Công việc đang xử lý',
                            message: `Bạn có ${recentJobs.length} công việc đang xử lý trong hàng đợi. Bạn sẽ nhận thông báo khi hoàn tất.`,
                            type: 'info',
                            duration: 6000
                        });
                    }
                }
            } catch (e) {
                console.error('Error checking pending jobs:', e);
            }
        });

        // Add this to handle navigation away from page during pending requests
        window.addEventListener('beforeunload', function(e) {
            if (pendingBulkRequests) {
                // This message may be shown, depending on browser
                const message = 'Bạn có yêu cầu tạo nội dung đang xử lý. Bạn có chắc chắn muốn rời khỏi trang?';
                e.returnValue = message;
                return message;
            }
        });

        // Bulk prompt source toggle
        $("#bulk-prompt-source").on('change', function() {
            if ($(this).val() === 'custom') {
                $("#bulk-saved-prompts-container").addClass('d-none');
                $("#bulk-prompt-editor").removeClass('d-none');
            } else if ($(this).val() === 'saved') {
                $("#bulk-saved-prompts-container").removeClass('d-none');
                $("#bulk-prompt-editor").removeClass('d-none');
            } else {
                $("#bulk-saved-prompts-container").addClass('d-none');
                $("#bulk-prompt-editor").addClass('d-none');
            }
        });

        // Saved prompt selection
        $("#modal-saved-prompt").on('change', function() {
            const selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                const promptText = selectedOption.data('prompt');
                const systemMessage = selectedOption.data('system-message');

                if (promptText) {
                    $("#modal-prompt").val(promptText);
                }

                if (systemMessage) {
                    $("#modal-system-message").val(systemMessage);
                }
            }
        });

        // Bulk saved prompt selection
        $("#bulk-saved-prompt").on('change', function() {
            const selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                const promptText = selectedOption.data('prompt');
                const systemMessage = selectedOption.data('system-message');

                if (promptText) {
                    $("#bulk-prompt").val(promptText);
                }

                if (systemMessage) {
                    $("#bulk-system-message").val(systemMessage);
                }
            }
        });
    }

    // Update selector configuration based on content type
    function updateSelectorConfiguration() {
        // Reset all selectors
        resetHierarchicalSelectors();

        // Set table title and column headers based on content type
        updateTableHeaders();

        // Configure selectors based on content type
        // All content types use categories and groups
        $categorySelectorContainer.show();

        // Posts, chapters, and books use the book selector
        if (['posts', 'chapters', 'books'].includes(currentContentType)) {
            // These will be shown when parent selectors are selected
        }

        // Posts and chapters use the chapter selector
        if (['posts', 'chapters'].includes(currentContentType)) {
            // These will be shown when parent selectors are selected
        }
    }

    // Update table headers based on content type
    function updateTableHeaders() {
        switch (currentContentType) {
            case 'posts':
                $tableTitle.text('Bài Viết Thiếu Dữ Liệu SEO');
                $('#col-title').text('Tiêu Đề');
                $('#col-parent').text('Chương / Sách');
                $('#col-status').text('Dữ Liệu Meta');
                break;
            case 'chapters':
                $tableTitle.text('Chương Thiếu Mô Tả');
                $('#col-title').text('Tên Chương');
                $('#col-parent').text('Sách / Nhóm');
                $('#col-status').text('Mô Tả');
                break;
            case 'books':
                $tableTitle.text('Sách Thiếu Mô Tả');
                $('#col-title').text('Tên Sách');
                $('#col-parent').text('Nhóm / Danh Mục');
                $('#col-status').text('Mô Tả');
                break;
            case 'book_groups':
                $tableTitle.text('Nhóm Sách Thiếu Mô Tả');
                $('#col-title').text('Tên Nhóm');
                $('#col-parent').text('Danh Mục');
                $('#col-status').text('Mô Tả');
                break;
        }
    }

    // Reset all hierarchical selectors
    function resetHierarchicalSelectors() {
        // Reset all selectors
        $categorySelector.empty().append($('<option>', {
            value: '',
            text: '-- Chọn danh mục --'
        }));

        $groupSelector.empty().append($('<option>', {
            value: '',
            text: '-- Chọn nhóm sách --'
        }));

        $bookSelector.empty().append($('<option>', {
            value: '',
            text: '-- Chọn sách --'
        }));

        $chapterSelector.empty().append($('<option>', {
            value: '',
            text: '-- Chọn chương --'
        }));

        // Hide all containers except category
        $groupSelectorContainer.hide();
        $bookSelectorContainer.hide();
        $chapterSelectorContainer.hide();

        // Reset current IDs
        currentCategoryId = '';
        currentGroupId = '';
        currentBookId = '';
        currentChapterId = '';

        // Reset filter status
        $filterStatus.text('Chọn danh mục để bắt đầu');
    }

    // Reset selectors from a specific level down
    function resetSelectors(fromLevel) {
        // Reset selectors from the specified level down
        switch (fromLevel) {
            case 'group':
                $groupSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Chọn nhóm sách --'
                }));
                currentGroupId = '';
            // Fall through to reset lower levels
            case 'book':
                $bookSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Chọn sách --'
                }));
                currentBookId = '';
            // Fall through to reset lower levels
            case 'chapter':
                $chapterSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Chọn chương --'
                }));
                currentChapterId = '';
                break;
        }
    }

    // AJAX Functions for Loading Data

    // Load categories via AJAX
    function loadCategories() {
        $categorySelector.empty().append($('<option>', {
            value: '',
            text: '-- Đang tải danh mục... --'
        }));

        $.ajax({
            url: `${apiBaseUrl}/admin/ai-dashboard/content/categories`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(categories) {
                // Clear the selector and add default option
                $categorySelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Chọn danh mục --'
                }));

                // Add categories to selector
                categories.sort((a, b) => a.name.localeCompare(b.name))
                    .forEach(category => {
                        $categorySelector.append($('<option>', {
                            value: category.id,
                            text: category.name
                        }));
                    });

                // Update count
                updateContentCount();
            },
            error: function(error) {
                console.error('Error loading categories:', error);
                $categorySelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Lỗi khi tải danh mục --'
                }));
                $filterStatus.text('Lỗi khi tải danh mục');
            }
        });
    }

    // Load book groups via AJAX
    function loadBookGroups(categoryId) {
        $groupSelector.empty().append($('<option>', {
            value: '',
            text: '-- Đang tải nhóm sách... --'
        }));

        $.ajax({
            url: `${apiBaseUrl}/admin/ai-dashboard/content/book-groups/${categoryId}`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(groups) {
                // Clear the selector and add default option
                $groupSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Chọn nhóm sách --'
                }));

                // Add groups to selector
                groups.sort((a, b) => a.name.localeCompare(b.name))
                    .forEach(group => {
                        $groupSelector.append($('<option>', {
                            value: group.id,
                            text: group.name
                        }));
                    });

                // Update count
                updateContentCount();
            },
            error: function(error) {
                console.error('Error loading book groups:', error);
                $groupSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Lỗi khi tải nhóm sách --'
                }));
                $filterStatus.text('Lỗi khi tải nhóm sách');
            }
        });
    }

    // Load books via AJAX
    function loadBooks(groupId) {
        $bookSelector.empty().append($('<option>', {
            value: '',
            text: '-- Đang tải sách... --'
        }));

        $.ajax({
            url: `${apiBaseUrl}/admin/ai-dashboard/content/books/${groupId}`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(books) {
                // Clear the selector and add default option
                $bookSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Chọn sách --'
                }));

                // Add books to selector
                books.sort((a, b) => a.name.localeCompare(b.name))
                    .forEach(book => {
                        $bookSelector.append($('<option>', {
                            value: book.id,
                            text: book.name
                        }));
                    });

                // Update count
                updateContentCount();
            },
            error: function(error) {
                console.error('Error loading books:', error);
                $bookSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Lỗi khi tải sách --'
                }));
                $filterStatus.text('Lỗi khi tải sách');
            }
        });
    }

    // Load chapters via AJAX
    function loadChapters(bookId) {
        $chapterSelector.empty().append($('<option>', {
            value: '',
            text: '-- Đang tải chương... --'
        }));

        $.ajax({
            url: `${apiBaseUrl}/admin/ai-dashboard/content/chapters/${bookId}`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(chapters) {
                // Clear the selector and add default option
                $chapterSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Chọn chương --'
                }));

                // Add chapters to selector
                chapters.sort((a, b) => a.name.localeCompare(b.name))
                    .forEach(chapter => {
                        $chapterSelector.append($('<option>', {
                            value: chapter.id,
                            text: chapter.name
                        }));
                    });

                // Update count
                updateContentCount();
            },
            error: function(error) {
                console.error('Error loading chapters:', error);
                $chapterSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Lỗi khi tải chương --'
                }));
                $filterStatus.text('Lỗi khi tải chương');
            }
        });
    }

    // Load content data based on current filters
    function loadContentData() {
        // Show loading indicator
        $loadingIndicator.removeClass('hidden');
        $contentTableContainer.addClass('opacity-50');
        $emptyRow.show();
        $contentTbody.find('tr:not(#empty-row)').remove();
        $emptyRow.find('td').html('<div class="flex justify-center"><div class="spinner-border text-indigo-600" role="status"></div></div>');

        // Build query parameters
        const params = new URLSearchParams();
        params.append('type', currentContentType);
        params.append('page', currentPage);
        params.append('limit', currentLimit);

        if (currentSearch) {
            params.append('search', currentSearch);
        }

        if (currentSeoStatus) {
            params.append('seo_status', currentSeoStatus);
        }

        // Add hierarchical filters if selected
        if (currentCategoryId) {
            params.append('category_id', currentCategoryId);
        }

        if (currentGroupId) {
            params.append('group_id', currentGroupId);
        }

        if (currentBookId) {
            params.append('book_id', currentBookId);
        }

        if (currentChapterId) {
            params.append('chapter_id', currentChapterId);
        }

        // Clear existing content rows
        $contentTbody.find('tr:not(#empty-row)').remove();

        // Make AJAX request
        $.ajax({
            url: `${apiBaseUrl}/admin/ai-dashboard/stats/data?${params.toString()}`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Hide loading indicator
                $loadingIndicator.addClass('hidden');
                $contentTableContainer.removeClass('opacity-50');

                // Update total count
                $totalCount.text(`Tìm thấy ${response.total} mục`);

                if (response.data.length === 0) {
                    // Show no data message
                    $emptyRow.show();
                    $emptyRow.find('td').html('<p class="text-gray-500">Không tìm thấy dữ liệu phù hợp với bộ lọc</p>');
                } else {
                    // Hide empty row
                    $emptyRow.hide();

                    // Render content rows
                    response.data.forEach(function(item) {
                        renderContentRow(item);
                    });

                    // Build pagination
                    buildPagination(response.current_page, response.last_page);
                }
            },
            error: function(error) {
                console.error('Error loading content data:', error);
                $loadingIndicator.addClass('hidden');
                $contentTableContainer.removeClass('opacity-50');
                $emptyRow.show();
                $emptyRow.find('td').html('<p class="text-red-500">Đã xảy ra lỗi khi tải dữ liệu. Vui lòng thử lại.</p>');
            }
        });
    }

    // Render a content row
    function renderContentRow(item) {
        let title = item.title || item.name || 'N/A';
        let parentInfo = '';
        let statusHtml = '';

        // Build parent info based on content type
        switch (currentContentType) {
            case 'posts':
                parentInfo = `<p class="text-gray-800">${item.chapter?.name || 'N/A'}</p>
                              <p class="text-xs text-gray-600">${item.chapter?.book?.name || 'N/A'}</p>`;

                statusHtml = `<div class="flex flex-col">
                                <div class="flex items-center">
                                    <span class="mr-2 inline-block h-2.5 w-2.5 rounded-full ${item.meta_title ? 'bg-green-500' : 'bg-red-500'}"></span>
                                    <p class="text-sm ${item.meta_title ? 'text-green-600' : 'text-red-600'}">Tiêu Đề Meta</p>
                                </div>
                                <div class="flex items-center mt-1">
                                    <span class="mr-2 inline-block h-2.5 w-2.5 rounded-full ${item.meta_description ? 'bg-green-500' : 'bg-red-500'}"></span>
                                    <p class="text-sm ${item.meta_description ? 'text-green-600' : 'text-red-600'}">Mô Tả Meta</p>
                                </div>
                            </div>`;
                break;

            case 'chapters':
                parentInfo = `<p class="text-gray-800">${item.book?.name || 'N/A'}</p>
                              <p class="text-xs text-gray-600">${item.book?.group?.name || 'N/A'}</p>`;

                statusHtml = `<div class="flex items-center">
                                <span class="mr-2 inline-block h-2.5 w-2.5 rounded-full ${item.description ? 'bg-green-500' : 'bg-red-500'}"></span>
                                <p class="text-sm ${item.description ? 'text-green-600' : 'text-red-600'}">
                                    ${item.description ? 'Có Mô Tả' : 'Thiếu Mô Tả'}
                                </p>
                            </div>`;
                break;

            case 'books':
                parentInfo = `<p class="text-gray-800">${item.group?.name || 'N/A'}</p>
                              <p class="text-xs text-gray-600">${item.group?.category?.name || 'N/A'}</p>`;

                statusHtml = `<div class="flex items-center">
                                <span class="mr-2 inline-block h-2.5 w-2.5 rounded-full ${item.description ? 'bg-green-500' : 'bg-red-500'}"></span>
                                <p class="text-sm ${item.description ? 'text-green-600' : 'text-red-600'}">
                                    ${item.description ? 'Có Mô Tả' : 'Thiếu Mô Tả'}
                                </p>
                            </div>`;
                break;

            case 'book_groups':
                parentInfo = `<p class="text-gray-800">${item.category?.name || 'N/A'}</p>`;

                statusHtml = `<div class="flex items-center">
                                <span class="mr-2 inline-block h-2.5 w-2.5 rounded-full ${item.description ? 'bg-green-500' : 'bg-red-500'}"></span>
                                <p class="text-sm ${item.description ? 'text-green-600' : 'text-red-600'}">
                                    ${item.description ? 'Có Mô Tả' : 'Thiếu Mô Tả'}
                                </p>
                            </div>`;
                break;
        }

        // Create row HTML
        const rowHtml = `
            <tr class="hover:bg-gray-50">
                <td class="border-b border-gray-200 py-5 px-4">
                    <div class="flex h-5 items-center">
                        <input type="checkbox" data-id="${item.id}" class="item-checkbox h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                    </div>
                </td>
                <td class="border-b border-gray-200 py-5 px-4">
                    <div class="flex flex-col">
                        <h5 class="font-medium text-gray-800">${title}</h5>
                        <p class="text-xs">ID: ${item.id}</p>
                    </div>
                </td>
                <td class="border-b border-gray-200 py-5 px-4">
                    ${parentInfo}
                </td>
                <td class="border-b border-gray-200 py-5 px-4">
                    ${statusHtml}
                </td>
                <td class="border-b border-gray-200 py-5 px-4">
                    <div class="flex items-center space-x-3.5">
                        <button
                            class="text-indigo-600 hover:text-indigo-800 generate-single"
                            data-id="${item.id}"
                            data-type="${currentContentType}"
                            data-title="${title.replace(/"/g, '&quot;')}"
                        >
                            <span class="iconify text-xl" data-icon="mdi-cog"></span>
                        </button>
                        <a href="${apiBaseUrl}/admin/ai-dashboard/playground?content_type=${currentContentType}&content_id=${item.id}" class="text-blue-600 hover:text-blue-800">
                            <span class="iconify text-xl" data-icon="mdi-link-variant"></span>
                        </a>
                        <a href="${getEditUrl(item.id)}" class="text-green-600 hover:text-green-800">
                            <span class="iconify text-xl" data-icon="mdi-pencil"></span>
                        </a>
                    </div>
                </td>
            </tr>
        `;

        // Append row to table
        $contentTbody.append(rowHtml);
    }

    // Build pagination links
    function buildPagination(currentPage, lastPage) {
        $pagination.empty();

        // No pagination needed for single page
        if (lastPage <= 1) {
            $paginationContainer.addClass('hidden');
            return;
        }

        $paginationContainer.removeClass('hidden');

        // Previous page link
        $pagination.append(`
            <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `);

        // Calculate page range
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, startPage + 4);

        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        // First page link if needed
        if (startPage > 1) {
            $pagination.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="1">1</a>
                </li>
            `);

            if (startPage > 2) {
                $pagination.append(`
                    <li class="page-item disabled">
                        <a class="page-link" href="#">...</a>
                    </li>
                `);
            }
        }

        // Page links
        for (let i = startPage; i <= endPage; i++) {
            $pagination.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Last page link if needed
        if (endPage < lastPage) {
            if (endPage < lastPage - 1) {
                $pagination.append(`
                    <li class="page-item disabled">
                        <a class="page-link" href="#">...</a>
                    </li>
                `);
            }

            $pagination.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${lastPage}">${lastPage}</a>
                </li>
            `);
        }

        // Next page link
        $pagination.append(`
            <li class="page-item ${currentPage >= lastPage ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `);
    }

    // Get edit URL based on content type
    function getEditUrl(id) {
        switch (currentContentType) {
            case 'posts':
                return `${apiBaseUrl}/admin/posts/${id}/edit`;
            case 'chapters':
                return `${apiBaseUrl}/admin/book-chapters/${id}/edit`;
            case 'books':
                return `${apiBaseUrl}/admin/books/${id}/edit`;
            case 'book_groups':
                return `${apiBaseUrl}/admin/book-groups/${id}/edit`;
            default:
                return '#';
        }
    }

    // Update the content count display
    function updateContentCount() {
        let countText = '';

        switch (true) {
            case $chapterSelector.is(':visible'):
                countText = `${$chapterSelector.find('option').length - 1} chương`;
                break;
            case $bookSelector.is(':visible'):
                countText = `${$bookSelector.find('option').length - 1} sách`;
                break;
            case $groupSelector.is(':visible'):
                countText = `${$groupSelector.find('option').length - 1} nhóm`;
                break;
            case $categorySelector.is(':visible'):
                countText = `${$categorySelector.find('option').length - 1} danh mục`;
                break;
            default:
                countText = '0 mục';
        }

        $contentCount.text(countText);
    }

    // Update the selected count display
    function updateSelectedCount() {
        const selectedCount = $(".item-checkbox:checked").length;
        $("#selected-count").text(selectedCount + " đã chọn");

        // Update bulk modal counter too
        $("#bulk-selected-count").text(
            selectedCount > 0
                ? selectedCount + " mục đã được chọn để tạo"
                : "Chưa chọn mục nào"
        );
    }

    // Set default prompt based on content type
    function setDefaultPrompt(type, target = 'modal') {
        // Set the prompt from the loaded default prompts
        if (target === 'modal') {
            if (defaultPrompts[type]) {
                $("#modal-prompt").val(defaultPrompts[type]);
            }

            // Get system message from API
            if (!systemMessages[type]) {
                // If not loaded yet, load it
                $.ajax({
                    url: `${apiBaseUrl}/admin/ai-dashboard/system-message/${type}`,
                    method: 'GET',
                    async: false, // This ensures we have the system message before continuing
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        systemMessages[type] = data;
                        $("#modal-system-message").val(data);
                    },
                    error: function(error) {
                        console.error(`Error loading system message for ${type}:`, error);
                        // Leave it empty and let the backend provide the default
                    }
                });
            } else {
                // Use the already loaded system message
                $("#modal-system-message").val(systemMessages[type]);
            }
        } else {
            // For bulk modal
            if (defaultPrompts[type]) {
                $("#bulk-prompt").val(defaultPrompts[type]);
            }

            // Get system message from API
            if (!systemMessages[type]) {
                // If not loaded yet, load it
                $.ajax({
                    url: `${apiBaseUrl}/admin/ai-dashboard/system-message/${type}`,
                    method: 'GET',
                    async: false, // This ensures we have the system message before continuing
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        systemMessages[type] = data;
                        $("#bulk-system-message").val(data);
                    },
                    error: function(error) {
                        console.error(`Error loading system message for ${type}:`, error);
                        // Leave it empty and let the backend provide the default
                    }
                });
            } else {
                // Use the already loaded system message
                $("#bulk-system-message").val(systemMessages[type]);
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(init);
})();
