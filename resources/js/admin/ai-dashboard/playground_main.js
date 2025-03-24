/**
 * AI Content Playground
 * This module provides functionality for testing and applying AI-generated content
 * to different content types (posts, chapters, books, book groups).
 */

// Hierarchical content selection system with AJAX loading
const hierarchicalSelector = (function() {
    // DOM elements
    const $contentTypeButtons = $('.content-type-btn');
    const $contentTypeSelect = $('#content-type');
    const $categorySelector = $('#category-selector');
    const $groupSelector = $('#group-selector');
    const $bookSelector = $('#book-selector');
    const $chapterSelector = $('#chapter-selector');
    const $contentFinalSelector = $('#content-final-selector');
    const $quickFilter = $('#quick-filter');
    const $clearFilter = $('#clear-filter');
    const $filterStatus = $('#filter-status');
    const $contentCount = $('#content-count');
    const $refreshHierarchical = $('#refresh-hierarchical');

    // Container references
    const $categorySelectorContainer = $('#category-selector-container');
    const $groupSelectorContainer = $('#group-selector-container');
    const $bookSelectorContainer = $('#book-selector-container');
    const $chapterSelectorContainer = $('#chapter-selector-container');
    const $contentSelectorContainer = $('#content-selector-container');
    const $quickFilterContainer = $('#quick-filter-container');
    const $contentSelectorSpinner = $('#content-selector-spinner');
    const $contentSelectorLabel = $('#content-selector-label');

    // Content details elements
    const $contentDetailsPlaceholder = $('#content-details-placeholder');
    const $contentDetails = $('#content-details');
    const $contentTitle = $('#content-title');
    const $contentPath = $('#content-path');
    const $contentBadge = $('#content-badge');
    const $metaTitleValue = $('#meta-title-value');
    const $metaDescValue = $('#meta-desc-value');
    const $editContentLink = $('#edit-content-link');

    // Track current state
    let currentContentType = '';
    let currentCategoryId = '';
    let currentGroupId = '';
    let currentBookId = '';
    let currentChapterId = '';

    /**
     * Initialize the content selector
     */
    function init() {
        // Set initial content type based on the hidden select
        currentContentType = $contentTypeSelect.val();

        // Configure selectors based on content type
        updateSelectorConfiguration();

        // Load initial categories
        loadCategories();

        // Set up event handlers
        bindEvents();
    }

    /**
     * Bind all event handlers
     */
    function bindEvents() {
        // Content type selection
        $contentTypeButtons.on('click', function() {
            // Update visual state of buttons
            $contentTypeButtons.removeClass('border-indigo-300 bg-indigo-50').addClass('border-transparent bg-gray-50');
            $(this).removeClass('border-transparent bg-gray-50').addClass('border-indigo-300 bg-indigo-50');

            resetHierarchicalSelectors();

            // Get selected content type
            currentContentType = $(this).data('type');

            // Update selector visibilities and labels based on content type
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
                $contentSelectorContainer.hide();
                $quickFilterContainer.hide();
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
                    loadFinalContent();
                    $filterStatus.text('Đã chọn nhóm sách');
                } else {
                    $bookSelectorContainer.show();
                    loadBooks(currentGroupId);
                    $filterStatus.text('Đang xem sách trong nhóm đã chọn');
                }
            } else {
                $bookSelectorContainer.hide();
                $chapterSelectorContainer.hide();
                $contentSelectorContainer.hide();
                $quickFilterContainer.hide();
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
                    loadFinalContent();
                    $filterStatus.text('Đã chọn sách');
                } else {
                    $chapterSelectorContainer.show();
                    loadChapters(currentBookId);
                    $filterStatus.text('Đang xem chương trong sách đã chọn');
                }
            } else {
                $chapterSelectorContainer.hide();
                $contentSelectorContainer.hide();
                $quickFilterContainer.hide();
                $filterStatus.text('Chọn sách để tiếp tục');
            }

            updateContentCount();
        });

        // Chapter selection
        $chapterSelector.on('change', function() {
            currentChapterId = $(this).val();
            resetSelectors('content');

            if (currentChapterId) {
                if (currentContentType === 'chapters') {
                    // For chapters, this is the final selection
                    loadFinalContent();
                    $filterStatus.text('Đã chọn chương sách');
                } else {
                    loadFinalContent();
                    $filterStatus.text('Đang xem bài viết trong chương đã chọn');
                }
            } else {
                $contentSelectorContainer.hide();
                $quickFilterContainer.hide();
                $filterStatus.text('Chọn chương để tiếp tục');
            }

            updateContentCount();
        });

        // Final content selection
        $contentFinalSelector.on('change', function() {
            if ($(this).val()) {
                updateContentDetails($(this).val());
                $filterStatus.text('Đã chọn nội dung');
            }
        });

        // Quick filter functionality
        $quickFilter.on('input', function() {
            const filterText = $(this).val().toLowerCase();

            // Filter options in the final selector
            $('#content-final-selector option').each(function() {
                const optionText = $(this).text().toLowerCase();
                if (optionText.includes(filterText) || $(this).val() === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            if (filterText) {
                $clearFilter.show();
            } else {
                $clearFilter.hide();
            }
        });

        // Clear filter button
        $clearFilter.on('click', function() {
            $quickFilter.val('').trigger('input');
            $(this).hide();
        });

        // Refresh button
        $refreshHierarchical.on('click', function() {
            const $icon = $(this).find('.iconify');

            // Animate refresh icon
            $icon.css({
                'transition': 'transform 0.5s',
                'transform': 'rotate(360deg)'
            });

            setTimeout(() => {
                $icon.css('transform', 'rotate(0deg)');
            }, 500);

            // Reload current view
            refreshCurrentView();
        });
    }

    /**
     * Update selector configuration based on content type
     */
    function updateSelectorConfiguration() {
        // Reset all selectors
        resetHierarchicalSelectors();

        // Configure selectors based on content type
        switch (currentContentType) {
            case 'posts':
                $contentSelectorLabel.text('Bài Viết');
                $categorySelectorContainer.show();
                break;
            case 'chapters':
                $contentSelectorLabel.text('Chương Sách');
                $categorySelectorContainer.show();
                break;
            case 'books':
                $contentSelectorLabel.text('Sách');
                $categorySelectorContainer.show();
                break;
            case 'book_groups':
                $contentSelectorLabel.text('Nhóm Sách');
                $categorySelectorContainer.show();
                break;
        }
    }

    /**
     * Reset all hierarchical selectors
     */
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

        $contentFinalSelector.empty().append($('<option>', {
            value: '',
            text: '-- Chọn nội dung --'
        }));

        // Hide all containers except category
        $groupSelectorContainer.hide();
        $bookSelectorContainer.hide();
        $chapterSelectorContainer.hide();
        $contentSelectorContainer.hide();
        $quickFilterContainer.hide();

        // Reset current IDs
        currentCategoryId = '';
        currentGroupId = '';
        currentBookId = '';
        currentChapterId = '';

        // Reset filter status
        $filterStatus.text('Chọn danh mục để bắt đầu');

        // Clear quick filter
        $quickFilter.val('');
        $clearFilter.hide();

        // Reset content details
        showContentPlaceholder();
    }

    /**
     * Reset selectors from a specific level down
     * @param {string} fromLevel - The level to start resetting from ('group', 'book', 'chapter', 'content')
     */
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
            // Fall through to reset lower levels
            case 'content':
                $contentFinalSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Chọn nội dung --'
                }));
                $contentSelectorContainer.hide();
                $quickFilterContainer.hide();
                break;
        }
    }

    /**
     * Show the content placeholder
     */
    function showContentPlaceholder() {
        $contentDetailsPlaceholder.removeClass('hidden');
        $contentDetails.addClass('hidden');
    }

    /**
     * Load categories via AJAX
     */
    function loadCategories() {
        // Show spinner
        $contentSelectorSpinner.show();

        $.ajax({
            url: '/admin/ai-dashboard/content/categories',
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

                // Hide spinner
                $contentSelectorSpinner.hide();

                // Update count
                updateContentCount();
            },
            error: function(error) {
                console.error('Error loading categories:', error);
                $contentSelectorSpinner.hide();
                $filterStatus.text('Lỗi khi tải danh mục');
            }
        });
    }

    /**
     * Load book groups via AJAX
     * @param {string|number} categoryId - The category ID to load book groups for
     */
    function loadBookGroups(categoryId) {
        // Show spinner
        $contentSelectorSpinner.show();

        $.ajax({
            url: `/admin/ai-dashboard/content/book-groups/${categoryId}`,
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

                // Hide spinner
                $contentSelectorSpinner.hide();

                // Update count
                updateContentCount();
            },
            error: function(error) {
                console.error('Error loading book groups:', error);
                $contentSelectorSpinner.hide();
                $filterStatus.text('Lỗi khi tải nhóm sách');
            }
        });
    }

    /**
     * Load books via AJAX
     * @param {string|number} groupId - The group ID to load books for
     */
    function loadBooks(groupId) {
        // Show spinner
        $contentSelectorSpinner.show();

        $.ajax({
            url: `/admin/ai-dashboard/content/books/${groupId}`,
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

                // Hide spinner
                $contentSelectorSpinner.hide();

                // Update count
                updateContentCount();
            },
            error: function(error) {
                console.error('Error loading books:', error);
                $contentSelectorSpinner.hide();
                $filterStatus.text('Lỗi khi tải sách');
            }
        });
    }

    /**
     * Load chapters via AJAX
     * @param {string|number} bookId - The book ID to load chapters for
     */
    function loadChapters(bookId) {
        // Show spinner
        $contentSelectorSpinner.show();

        $.ajax({
            url: `/admin/ai-dashboard/content/chapters/${bookId}`,
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

                // Hide spinner
                $contentSelectorSpinner.hide();

                // Update count
                updateContentCount();
            },
            error: function(error) {
                console.error('Error loading chapters:', error);
                $contentSelectorSpinner.hide();
                $filterStatus.text('Lỗi khi tải chương');
            }
        });
    }

    /**
     * Load final content via AJAX
     */
    function loadFinalContent() {
        // Show spinner
        $contentSelectorSpinner.show();

        let apiUrl = '';
        let targetId = '';

        // Determine API endpoint based on content type
        switch (currentContentType) {
            case 'posts':
                apiUrl = `/admin/ai-dashboard/content/posts/${currentChapterId}`;
                break;
            case 'chapters':
                apiUrl = `/admin/ai-dashboard/content/chapters/${currentBookId}`;
                targetId = currentChapterId;
                break;
            case 'books':
                apiUrl = `/admin/ai-dashboard/content/books/${currentGroupId}`;
                targetId = currentBookId;
                break;
            case 'book_groups':
                apiUrl = `/admin/ai-dashboard/content/book-groups/${currentCategoryId}`;
                targetId = currentGroupId;
                break;
        }

        $.ajax({
            url: apiUrl,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(contentItems) {
                // Clear the selector and add default option
                $contentFinalSelector.empty().append($('<option>', {
                    value: '',
                    text: '-- Chọn nội dung --'
                }));

                // Sort content items
                contentItems.sort((a, b) => (a.title || a.name).localeCompare(b.title || b.name));

                // Add content items to selector
                contentItems.forEach(item => {
                    const hasMeta = currentContentType === 'posts' ?
                        (item.meta_title && item.meta_description) :
                        !!item.description;

                    $contentFinalSelector.append($('<option>', {
                        value: item.id,
                        text: item.title || item.name,
                        class: hasMeta ? 'text-green-600' : 'text-red-600',
                        'data-has-meta': hasMeta ? 'true' : 'false'
                    }));
                });

                // Show content selector if we have items
                if (contentItems.length > 0) {
                    $contentSelectorContainer.show();
                    $quickFilterContainer.show();

                    // If target ID is set (for pre-selection), select it
                    if (targetId) {
                        $contentFinalSelector.val(targetId).trigger('change');
                    }
                    // If only one item, select it automatically
                    else if (contentItems.length === 1) {
                        $contentFinalSelector.val(contentItems[0].id).trigger('change');
                    }
                } else {
                    $contentSelectorContainer.hide();
                    $quickFilterContainer.hide();

                    // Update filter status to show no items found
                    $filterStatus.text('Không tìm thấy nội dung phù hợp');
                }

                // Hide spinner
                $contentSelectorSpinner.hide();

                // Update count
                updateContentCount();
            },
            error: function(error) {
                console.error('Error loading content items:', error);
                $contentSelectorSpinner.hide();
                $filterStatus.text('Lỗi khi tải nội dung');
            }
        });
    }

    /**
     * Update content details via AJAX
     * @param {string|number} contentId - The content ID to update details for
     * @param {boolean} suppressUrlUpdate - Whether to suppress URL update
     */
    function updateContentDetails(contentId, suppressUrlUpdate) {
        if (!contentId) {
            showContentPlaceholder();
            return;
        }

        // Show content details placeholder with loading indicator
        $contentDetailsPlaceholder.removeClass('hidden');
        $contentDetails.addClass('hidden');
        $contentDetailsPlaceholder.html(`
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <div class="mb-3 h-10 w-10 animate-spin rounded-full border-b-2 border-t-2 border-indigo-500"></div>
                <p class="text-sm text-gray-500">Đang tải thông tin nội dung...</p>
            </div>
        `);

        // Update the URL to reflect the current selection (for bookmarking)
        if (!suppressUrlUpdate) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('content_type', currentContentType);
            currentUrl.searchParams.set('content_id', contentId);

            // Use history.replaceState to update URL without reloading the page
            window.history.replaceState({}, '', currentUrl.toString());
        }

        $.ajax({
            url: `/admin/ai-dashboard/content/details/${currentContentType}/${contentId}`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(content) {
                // Hide placeholder
                $contentDetailsPlaceholder.addClass('hidden');
                $contentDetails.removeClass('hidden');

                // Update details based on content type
                switch (currentContentType) {
                    case 'posts':
                        $contentTitle.text(content.title || 'Không có tiêu đề');

                        // Update path/breadcrumb information
                        let pathText = '';
                        if (content.chapter?.book?.group?.category) {
                            pathText = `<span class="path-item text-indigo-600">${content.chapter.book.group.category.name || 'N/A'}</span> &raquo; `;
                        }
                        if (content.chapter?.book?.group) {
                            pathText += `<span class="path-item text-indigo-600">${content.chapter.book.group.name || 'N/A'}</span> &raquo; `;
                        }
                        if (content.chapter?.book) {
                            pathText += `<span class="path-item text-indigo-600">${content.chapter.book.name || 'N/A'}</span> &raquo; `;
                        }
                        if (content.chapter) {
                            pathText += `<span class="path-item text-indigo-600">${content.chapter.name || 'N/A'}</span>`;
                        }
                        $contentPath.html(pathText);

                        // Update metadata values
                        if (content.meta_title) {
                            $metaTitleValue.html(content.meta_title);
                        } else {
                            $metaTitleValue.html('<em class="text-gray-400">Chưa có tiêu đề meta</em>');
                        }

                        if (content.meta_description) {
                            $metaDescValue.html(content.meta_description);
                        } else {
                            $metaDescValue.html('<em class="text-gray-400">Chưa có mô tả meta</em>');
                        }

                        // Update badge
                        if (content.meta_title && content.meta_description) {
                            $contentBadge.removeClass('bg-red-100 text-red-700').addClass('bg-green-100 text-green-700').text('Đã có meta');
                        } else {
                            $contentBadge.removeClass('bg-green-100 text-green-700').addClass('bg-red-100 text-red-700').text('Thiếu meta');
                        }

                        // Update edit link
                        $editContentLink.attr('href', `/admin/posts/${content.id}/edit`);
                        break;

                    case 'chapters':
                        $contentTitle.text(content.name || 'Không có tên');

                        // Update path/breadcrumb
                        let chapPathText = '';
                        if (content.book?.group?.category) {
                            chapPathText = `<span class="path-item text-indigo-600">${content.book.group.category.name || 'N/A'}</span> &raquo; `;
                        }
                        if (content.book?.group) {
                            chapPathText += `<span class="path-item text-indigo-600">${content.book.group.name || 'N/A'}</span> &raquo; `;
                        }
                        if (content.book) {
                            chapPathText += `<span class="path-item text-indigo-600">${content.book.name || 'N/A'}</span>`;
                        }
                        $contentPath.html(chapPathText);

                        // Update metadata values
                        if (content.description) {
                            $metaDescValue.html(content.description);
                        } else {
                            $metaDescValue.html('<em class="text-gray-400">Chưa có mô tả</em>');
                        }

                        // Update badge
                        if (content.description) {
                            $contentBadge.removeClass('bg-red-100 text-red-700').addClass('bg-green-100 text-green-700').text('Đã có mô tả');
                        } else {
                            $contentBadge.removeClass('bg-green-100 text-green-700').addClass('bg-red-100 text-red-700').text('Thiếu mô tả');
                        }

                        // Update edit link
                        $editContentLink.attr('href', `/admin/book-chapters/${content.id}/edit`);
                        break;

                    case 'books':
                        $contentTitle.text(content.name || 'Không có tên');

                        // Update path/breadcrumb
                        let bookPathText = '';
                        if (content.group?.category) {
                            bookPathText = `<span class="path-item text-indigo-600">${content.group.category.name || 'N/A'}</span> &raquo; `;
                        }
                        if (content.group) {
                            bookPathText += `<span class="path-item text-indigo-600">${content.group.name || 'N/A'}</span>`;
                        }
                        $contentPath.html(bookPathText);

                        // Update metadata values
                        if (content.description) {
                            $metaDescValue.html(content.description);
                        } else {
                            $metaDescValue.html('<em class="text-gray-400">Chưa có mô tả</em>');
                        }

                        // Update badge
                        if (content.description) {
                            $contentBadge.removeClass('bg-red-100 text-red-700').addClass('bg-green-100 text-green-700').text('Đã có mô tả');
                        } else {
                            $contentBadge.removeClass('bg-green-100 text-green-700').addClass('bg-red-100 text-red-700').text('Thiếu mô tả');
                        }

                        // Update edit link
                        $editContentLink.attr('href', `/admin/books/${content.id}/edit`);
                        break;

                    case 'book_groups':
                        $contentTitle.text(content.name || 'Không có tên');

                        // Update path/breadcrumb
                        let groupPathText = '';
                        if (content.category) {
                            groupPathText = `<span class="path-item text-indigo-600">${content.category.name || 'N/A'}</span>`;
                        }
                        $contentPath.html(groupPathText);

                        // Update metadata values
                        if (content.description) {
                            $metaDescValue.html(content.description);
                        } else {
                            $metaDescValue.html('<em class="text-gray-400">Chưa có mô tả</em>');
                        }

                        // Update badge
                        if (content.description) {
                            $contentBadge.removeClass('bg-red-100 text-red-700').addClass('bg-green-100 text-green-700').text('Đã có mô tả');
                        } else {
                            $contentBadge.removeClass('bg-green-100 text-green-700').addClass('bg-red-100 text-red-700').text('Thiếu mô tả');
                        }

                        // Update edit link
                        $editContentLink.attr('href', `/admin/book-groups/${content.id}/edit`);
                        break;
                }

                // Also load the appropriate prompt template
                if (window.promptTemplates && window.promptTemplates[currentContentType]) {
                    $('#prompt').val(window.promptTemplates[currentContentType]);
                }
            },
            error: function(error) {
                console.error('Error loading content details:', error);
                $contentDetailsPlaceholder.html(`
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <div class="mb-3 rounded-full bg-red-100 p-3">
                            <span class="iconify text-3xl text-red-600" data-icon="mdi-alert-circle-outline"></span>
                        </div>
                        <h5 class="mb-1 text-lg font-medium text-gray-700">Lỗi khi tải nội dung</h5>
                        <p class="text-sm text-gray-500">Không thể tải thông tin chi tiết về nội dung này</p>
                    </div>
                `);
            }
        });
    }

    /**
     * Update the content count display
     */
    function updateContentCount() {
        let countText = '';

        switch (true) {
            case $contentFinalSelector.is(':visible'):
                countText = `${$contentFinalSelector.find('option').length - 1} mục`;
                break;
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

    /**
     * Refresh the current view
     */
    function refreshCurrentView() {
        // Reload the current level based on what's visible
        if ($contentFinalSelector.is(':visible')) {
            loadFinalContent();
        } else if ($chapterSelector.is(':visible')) {
            loadChapters(currentBookId);
        } else if ($bookSelector.is(':visible')) {
            loadBooks(currentGroupId);
        } else if ($groupSelector.is(':visible')) {
            loadBookGroups(currentCategoryId);
        } else {
            loadCategories();
        }

        // Reset content details
        showContentPlaceholder();
    }

    // Public interface
    return {
        init: init,
        updateContentDetails: updateContentDetails
    };
})();

/**
 * Get URL parameter by name
 * @param {string} name - The parameter name
 * @returns {string} The parameter value or empty string if not found
 */
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

/**
 * Handle URL parameters for content loading
 * @param {Object} hierarchicalSelector - The hierarchical selector object
 * @returns {boolean} Whether URL parameters were handled
 */
function handleUrlParameters(hierarchicalSelector) {
    const contentType = getUrlParameter('content_type');
    const contentId = getUrlParameter('content_id');

    if (contentType && contentId) {
        // Retrieve the content details first to get path information
        $.ajax({
            url: `/admin/ai-dashboard/content/details/${contentType}/${contentId}`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(content) {
                // Find the correct content type button and click it
                $(`.content-type-btn[data-type="${contentType}"]`).click();

                // Now proceed with async selection of content hierarchy
                selectContentHierarchy(contentType, content, hierarchicalSelector);
            },
            error: function(error) {
                console.error('Error loading content from URL parameters:', error);
                // Fall back to default initialization
                $('.content-type-btn.border-indigo-300').click();
            }
        });

        return true; // Handled URL parameters
    }

    return false; // No URL parameters to handle
}

/**
 * Select hierarchical content path from content details
 * @param {string} contentType - The content type
 * @param {Object} content - The content object
 */
function selectContentHierarchy(contentType, content) {
    let categoryId, groupId, bookId, chapterId;

    // Extract IDs based on content type
    switch (contentType) {
        case 'posts':
            if (content.chapter) {
                chapterId = content.book_chapter_id;
                bookId = content.chapter.book_id;
                groupId = content.chapter.book?.group_id || content.chapter.book?.book_group_id;
                categoryId = content.chapter.book?.group?.category_id;
            }
            break;

        case 'chapters':
            chapterId = content.id;
            bookId = content.book_id;
            groupId = content.book?.group_id || content.book?.book_group_id;
            categoryId = content.book?.group?.category_id;
            break;

        case 'books':
            bookId = content.id;
            groupId = content.group_id || content.book_group_id;
            categoryId = content.group?.category_id;
            break;

        case 'book_groups':
            groupId = content.id;
            categoryId = content.category_id;
            break;
    }

    // Now select the path one step at a time with async waiting
    if (categoryId) {
        selectCategoryAndWait(categoryId, groupId, bookId, chapterId, content.id, contentType);
    }
}

/**
 * Helper function to select category and wait for loading
 */
function selectCategoryAndWait(categoryId, groupId, bookId, chapterId, contentId, contentType) {
    // Wait for categories to load first (they should be loading during init)
    const checkCategoriesLoaded = setInterval(() => {
        if ($('#category-selector option').length > 1) {
            clearInterval(checkCategoriesLoaded);

            // Select category and trigger change event
            $('#category-selector').val(categoryId).trigger('change');

            // Wait for groups to load
            if (groupId) {
                selectGroupAndWait(groupId, bookId, chapterId, contentId, contentType);
            }
        }
    }, 100);
}

/**
 * Helper function to select group and wait for loading
 */
function selectGroupAndWait(groupId, bookId, chapterId, contentId, contentType) {
    const checkGroupsLoaded = setInterval(() => {
        if ($('#group-selector option').length > 1) {
            clearInterval(checkGroupsLoaded);

            // Select group and trigger change event
            $('#group-selector').val(groupId).trigger('change');

            // Wait for books to load (if needed)
            if (bookId && contentType !== 'book_groups') {
                selectBookAndWait(bookId, chapterId, contentId, contentType);
            }
        }
    }, 100);
}

/**
 * Helper function to select book and wait for loading
 */
function selectBookAndWait(bookId, chapterId, contentId, contentType) {
    const checkBooksLoaded = setInterval(() => {
        if ($('#book-selector option').length > 1) {
            clearInterval(checkBooksLoaded);

            // Select book and trigger change event
            $('#book-selector').val(bookId).trigger('change');

            // Wait for chapters to load (if needed)
            if (chapterId && contentType !== 'books') {
                selectChapterAndWait(chapterId, contentId, contentType);
            }
        }
    }, 100);
}

/**
 * Helper function to select chapter and wait for loading
 */
function selectChapterAndWait(chapterId, contentId, contentType) {
    const checkChaptersLoaded = setInterval(() => {
        if ($('#chapter-selector option').length > 1) {
            clearInterval(checkChaptersLoaded);

            // Select chapter and trigger change event
            $('#chapter-selector').val(chapterId).trigger('change');

            // Wait for final content to load (for posts)
            if (contentType === 'posts') {
                selectFinalContentAndWait(contentId);
            }
        }
    }, 100);
}

/**
 * Helper function to select final content and wait for loading
 */
function selectFinalContentAndWait(contentId) {
    const checkContentLoaded = setInterval(() => {
        if ($('#content-final-selector option').length > 1) {
            clearInterval(checkContentLoaded);

            // Select final content item and trigger change event
            $('#content-final-selector').val(contentId).trigger('change');
        }
    }, 100);
}

// Store the generated content for applying later
let generatedContent = null;

/**
 * Content generation functionality
 */
function initializeContentGeneration() {
    const $providerSelect = $('#provider');
    const $modelSelect = $('#model');
    const $generateBtn = $('#generate-btn');
    const $applyBtn = $('#apply-btn');
    const $resetBtn = $('#reset-btn');
    const $promptTextarea = $('#prompt');
    const $systemMessage = $('#system-message');
    const $systemMessageContainer = $('#system-message-container');
    const $generatingSpinner = $('#loading-indicator');
    const $generatingStatus = $('#generating-status');
    const $responseContainer = $('#response-container');
    const $contentFinalSelector = $('#content-final-selector');
    const $useHtmlMeta = $('#use-html-meta');

    // Initialize provider selector
    initProviderSelector();

    // Initialize model selector to be populated after provider selection
    initModelSelector();

    // Initialize UI controls (sliders, etc.)
    initializeUIControls();

    function initProviderSelector() {
        // Fetch available providers
        fetch('/admin/ai-dashboard/providers')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear existing options
                    $providerSelect.empty().append('<option value="">Chọn nhà cung cấp AI</option>');

                    // Add provider options
                    Object.entries(data.providers).forEach(([code, name]) => {
                        $providerSelect.append(`<option value="${code}">${name}</option>`);
                    });

                    // Check for stored provider preference
                    const savedProvider = localStorage.getItem('selectedProvider');
                    if (savedProvider && $providerSelect.find(`option[value="${savedProvider}"]`).length > 0) {
                        $providerSelect.val(savedProvider).trigger('change');
                    }
                }
            })
            .catch(error => {
                console.error('Error loading providers:', error);
            });

        // Handle provider change
        $providerSelect.on('change', function() {
            const provider = $(this).val();
            if (provider) {
                localStorage.setItem('selectedProvider', provider);
                loadModelsForProvider(provider);
            } else {
                // Clear model select if no provider
                $modelSelect.empty().append('<option value="">Chọn mô hình AI</option>');
                $modelSelect.prop('disabled', true);
            }
        });
    }

    function initModelSelector() {
        // Initially disable model selector until provider is selected
        $modelSelect.prop('disabled', true);

        // Handle model change
        $modelSelect.on('change', function() {
            const model = $(this).val();
            if (model) {
                localStorage.setItem('selectedModel', model);

                // Show system message container for specific models if needed
                if (model.includes('deepseek') || model.includes('mistral') || model.includes('llama')) {
                    $systemMessageContainer.removeClass('hidden');
                } else {
                    $systemMessageContainer.addClass('hidden');
                }
            }
        });
    }

    function loadModelsForProvider(provider) {
        // Show loading state
        $modelSelect.empty().append('<option value="">Đang tải mô hình...</option>');
        $modelSelect.prop('disabled', true);

        // Fetch models for the selected provider
        fetch(`/admin/ai-dashboard/providers/${provider}/models`)
            .then(response => response.json())
            .then(data => {
                // Clear existing options
                $modelSelect.empty().append('<option value="">Chọn mô hình AI</option>');

                if (data.success && data.models) {
                    // Add model options
                    Object.entries(data.models).forEach(([id, name]) => {
                        $modelSelect.append(`<option value="${id}">${name}</option>`);
                    });

                    // Enable model selector
                    $modelSelect.prop('disabled', false);

                    // Check for saved model preference
                    const savedModel = localStorage.getItem('selectedModel');
                    if (savedModel && $modelSelect.find(`option[value="${savedModel}"]`).length > 0) {
                        $modelSelect.val(savedModel).trigger('change');
                    }
                } else {
                    $modelSelect.append('<option value="">Không có mô hình nào</option>');
                }
            })
            .catch(error => {
                console.error('Error loading models:', error);
                $modelSelect.empty().append('<option value="">Lỗi tải mô hình</option>');
            })
            .finally(() => {
                $modelSelect.prop('disabled', false);
            });
    }

    function getActiveContentType() {
        // Get the content type from the active button that has the border-indigo-300 class
        return $('.content-type-btn.border-indigo-300').data('type') || 'posts';
    }

    // Initialize event handlers
    function initEventHandlers() {
        // Generate button click handler
        $generateBtn.on('click', function() {
            generateContent();
        });

        // Reset button click handler
        $resetBtn.on('click', function() {
            resetContentGeneration();
        });

        // Apply changes button
        $applyBtn.on('click', function() {
            applyGeneratedContent();
        });
    }

    // Main function to generate content
    function generateContent() {
        // Get selected content
        const contentId = $contentFinalSelector.val();
        const contentType = getActiveContentType();

        // Validate input
        if (!contentId) {
            alert('Vui lòng chọn nội dung cần tạo.');
            return;
        }

        if (!$promptTextarea.val().trim()) {
            alert('Vui lòng nhập prompt.');
            return;
        }

        // Reset generated content
        generatedContent = null;

        // Get form values
        const model = $modelSelect.val();
        const temperature = $('#temperature').val();
        const maxTokens = 4096; // Always use maximum token value
        const promptText = $promptTextarea.val();
        const systemMessage = $systemMessage.val();
        const useHtmlMeta = $('#use-html-meta').is(':checked');

        // Show loading
        $generatingSpinner.removeClass('hidden');
        $responseContainer.removeClass('hidden');
        $('#results').html('<p class="text-gray-500">Đang tạo nội dung...</p>');
        $generateBtn.prop('disabled', true);
        $applyBtn.addClass('hidden');

        // Create form data
        const formData = new FormData();
        formData.append('content_id', contentId);
        formData.append('content_type', contentType);
        formData.append('provider', $providerSelect.val());
        formData.append('model', model);
        formData.append('prompt', promptText);
        formData.append('temperature', temperature);
        formData.append('max_tokens', maxTokens);
        formData.append('use_html_meta', useHtmlMeta ? '1' : '0');

        // Add system message for DeepSeek models
        if (model.startsWith('deepseek') && systemMessage) {
            formData.append('system_message', systemMessage);
        }

        // Make sure response container and results container are visible
        $responseContainer.removeClass('hidden');
        $('#results-container').removeClass('hidden');

        // Show loading state
        $generatingSpinner.removeClass('hidden');
        $generatingStatus.text('Đang tạo nội dung...');
        $generateBtn.prop('disabled', true);

        // Make AJAX request
        $.ajax({
            url: '/admin/ai-dashboard/generate-sample',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Hide loading
                $generatingSpinner.addClass('hidden');
                $generateBtn.prop('disabled', false);

                if (response.success) {
                    // Store the generated content for later use
                    generatedContent = response.result;

                    // Display results based on content type
                    // Update loading state
                    $('#loading-indicator').addClass('hidden');
                    $('#generate-btn').prop('disabled', false);

                    // Display results
                    // Update results container
                    $('#results-container').removeClass('hidden');
                    $('#loading-indicator').addClass('hidden');

                    if (contentType === 'posts' && typeof response.result === 'object') {
                        // Handle meta information for posts
                        const metaResult = response.result;
                        $('#results').html(`
                            <div class="ai-response space-y-4">
                                <div>
                                    <h5 class="text-lg font-semibold mb-2">Tiêu Đề Meta</h5>
                                    <div class="meta-title p-3 bg-white border border-gray-300 rounded-lg">
                                        ${metaResult.meta_title || ''}
                                    </div>
                                </div>
                                <div>
                                    <h5 class="text-lg font-semibold mb-2">Mô Tả Meta</h5>
                                    <div class="meta-description p-3 bg-white border border-gray-300 rounded-lg">
                                        ${metaResult.meta_description || ''}
                                    </div>
                                </div>
                            </div>
                        `);
                    } else {
                        // Handle regular content
                        $('#results').html(`
                            <div class="ai-response">
                                ${typeof response.result === 'string' ? response.result : JSON.stringify(response.result)}
                            </div>
                        `);
                    }

                    // Show apply button
                    $applyBtn.removeClass('hidden');
                } else {
                    $('#results').html(`<div class="text-red-500">${response.error || 'Lỗi không xác định'}</div>`);
                }
            },
            error: function(xhr, status, error) {
                // Hide loading
                $generatingSpinner.addClass('hidden');
                $generateBtn.prop('disabled', false);

                // Display error
                $('#results').html(`
                <div class="rounded-sm border border-red-300 bg-red-50 p-4">
                    <div class="flex items-start">
                        <span class="mr-3 flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                            <i class="iconify text-red-500" data-icon="mdi-alert"></i>
                        </span>
                        <div>
                            <h5 class="mb-1 font-semibold text-red-700">
                                Lỗi
                            </h5>
                            <p class="text-sm text-red-600">
                                ${xhr.responseJSON?.error || 'Đã xảy ra lỗi khi tạo nội dung.'}
                            </p>
                        </div>
                    </div>
                </div>
            `);
            }
        });
    }

    // Apply the generated content directly (without regenerating)
    function applyGeneratedContent() {
        // Get content info
        const contentId = $contentFinalSelector.val();
        const contentType = getActiveContentType();

        // Validate we have generated content
        if (!generatedContent) {
            alert('Không có nội dung đã tạo để áp dụng.');
            return;
        }

        // Show loading
        $applyBtn.prop('disabled', true);
        $applyBtn.html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Đang áp dụng...');

        // Update the content directly
        let updateData;

        // Prepare update data based on content type
        if (contentType === 'posts' && typeof generatedContent === 'object') {
            updateData = {
                meta_title: generatedContent.meta_title,
                meta_description: generatedContent.meta_description
            };
        } else {
            updateData = {
                description: generatedContent
            };
        }

        // Add CSRF token
        updateData._token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: `/admin/${contentType === 'book_groups' ? 'book-groups' : contentType}/${contentId}`,
            type: 'PUT',
            data: updateData,
            success: function(response) {
                // Show success message
                $('#results').prepend(`
                    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4">
                        <div class="flex items-center">
                            <span class="iconify text-green-500 mr-2" data-icon="mdi-check-circle"></span>
                            <p class="text-green-700">Nội dung đã được áp dụng thành công!</p>
                        </div>
                    </div>
                `);

                // Reload content details to show updated info
                window.updateContentDetails(contentId);

                // Reset button
                $applyBtn.prop('disabled', false);
                $applyBtn.html('Áp Dụng Thay Đổi');
            },
            error: function(xhr) {
                // Handle error - display fallback method if direct update failed
                console.error('Direct update failed, using fallback method', xhr);
                applyUsingFallbackMethod();
            }
        });
    }

    // Fallback method to use the existing API if direct update fails
    function applyUsingFallbackMethod() {
        // Get content info
        const contentId = $contentFinalSelector.val();
        const contentType = getActiveContentType();

        // Create form data for the fallback API
        const formData = new FormData();
        formData.append('content_type', contentType);
        formData.append('filter_type', 'ids');
        formData.append('filter_id', contentId);
        formData.append('use_generated_content', 'true');

        // Add the generated content
        if (typeof generatedContent === 'object') {
            formData.append('generated_content', JSON.stringify(generatedContent));
        } else {
            formData.append('generated_content', generatedContent);
        }

        // Make AJAX request to the fallback endpoint
        $.ajax({
            url: '/admin/ai-dashboard/apply-prompt',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('#results').prepend(`
                        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4">
                            <div class="flex items-center">
                                <span class="iconify text-green-500 mr-2" data-icon="mdi-check-circle"></span>
                                <p class="text-green-700">Áp dụng thay đổi thành công!</p>
                            </div>
                        </div>
                    `);

                    // Reload content details to show updated info
                    window.updateContentDetails(contentId);
                } else {
                    $('#results').prepend(`
                        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                            <div class="flex items-center">
                                <span class="iconify text-red-500 mr-2" data-icon="mdi-alert-circle"></span>
                                <p class="text-red-700">${response.error || 'Lỗi khi áp dụng thay đổi'}</p>
                            </div>
                        </div>
                    `);
                }

                // Reset button
                $applyBtn.prop('disabled', false);
                $applyBtn.html('Áp Dụng Thay Đổi');
            },
            error: function(xhr) {
                // Display error
                $('#results').prepend(`
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                        <div class="flex items-center">
                            <span class="iconify text-red-500 mr-2" data-icon="mdi-alert-circle"></span>
                            <p class="text-red-700">${xhr.responseJSON?.error || 'Đã xảy ra lỗi khi áp dụng thay đổi.'}</p>
                        </div>
                    </div>
                `);

                // Reset button
                $applyBtn.prop('disabled', false);
                $applyBtn.html('Áp Dụng Thay Đổi');
            }
        });
    }

    // Reset content generation
    function resetContentGeneration() {
        // Reset all form fields
        $providerSelect.val('').trigger('change');
        $modelSelect.empty().append('<option value="">Chọn mô hình AI</option>');
        $modelSelect.prop('disabled', true);
        $promptTextarea.val('');
        $systemMessage.val('');
        $useHtmlMeta.prop('checked', false);

        // Reset generated content
        generatedContent = null;

        // Hide results and button
        $('#results-container').addClass('hidden');
        $applyBtn.addClass('hidden');
    }

    // Initialize content generation
    initEventHandlers();
}

// Initialize when document is ready
$(document).ready(function() {
    // Initialize the hierarchical selector
    hierarchicalSelector.init();

    // Initialize content generation functionality
    initializeContentGeneration();

    // Override the existing content details update function if needed
    if (typeof window.updateContentDetails === 'function') {
        window.originalUpdateContentDetails = window.updateContentDetails;
    }

    // Replace the global updateContentDetails function
    window.updateContentDetails = function(contentId) {
        hierarchicalSelector.updateContentDetails(contentId);
    };

    // Check for URL parameters and handle them, otherwise use default initialization
    if (!handleUrlParameters(hierarchicalSelector)) {
        // Initialize by triggering the active content type button if no URL parameters
        $(window).on('load', function() {
            // Trigger content type button click to initialize the view
            $('.content-type-btn.border-indigo-300').click();
        });
    }
});

// Initialize UI controls with default values
function initializeUIControls() {
    // Define the system message container
    const $systemMessageContainer = $('#system-message-container');

    // Model-related UI
    $('#model').on('change', function() {
        const model = $(this).val();
        if (model.startsWith('deepseek')) {
            $systemMessageContainer.removeClass('hidden');
        } else {
            $systemMessageContainer.addClass('hidden');
        }
    });

    // Temperature slider
    $('#temperature').on('input', function() {
        $('#temperature-value').text($(this).val());
    });

    // Initialize values
    $('#temperature').val('0.7').trigger('input');
}
