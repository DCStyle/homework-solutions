/**
 * AI Content Playground
 * This module provides functionality for testing and applying AI-generated content
 * to different content types (posts, chapters, books, book groups).
 */

$(function() {
    // Initialize provider and model selectors
    initProviderSelector();

    // Hierarchical content selection system with AJAX loading
    const hierarchicalSelector = (function() {
        // DOM elements as jQuery objects
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

        // Container references as jQuery objects
        const $categorySelectorContainer = $('#category-selector-container');
        const $groupSelectorContainer = $('#group-selector-container');
        const $bookSelectorContainer = $('#book-selector-container');
        const $chapterSelectorContainer = $('#chapter-selector-container');
        const $contentSelectorContainer = $('#content-selector-container');
        const $quickFilterContainer = $('#quick-filter-container');
        const $contentSelectorSpinner = $('#content-selector-spinner');
        const $contentSelectorLabel = $('#content-selector-label');

        // Content details elements as jQuery objects
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
                    $(categories).sort((a, b) => a.name.localeCompare(b.name))
                        .each(function(index, category) {
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
                    $(groups).sort((a, b) => a.name.localeCompare(b.name))
                        .each(function(index, group) {
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
                    $(books).sort((a, b) => a.name.localeCompare(b.name))
                        .each(function(index, book) {
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
                    $(chapters).sort((a, b) => a.name.localeCompare(b.name))
                        .each(function(index, chapter) {
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

                    // Create jQuery array from content items and sort
                    const $sortedItems = $(contentItems).sort(function(a, b) {
                        return (a.title || a.name).localeCompare(b.title || b.name);
                    });

                    // Add content items to selector
                    $sortedItems.each(function(index, item) {
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
     * Get URL parameter by name - jQuery-friendly function
     * @param {string} name - The parameter name
     * @returns {string} The parameter value or empty string if not found
     */
    function getUrlParameter(name) {
        const results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    /**
     * Handle URL parameters for content loading
     * @returns {boolean} Whether URL parameters were handled
     */
    function handleUrlParameters() {
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
                    selectContentHierarchy(contentType, content);
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
     * Helper function to select category and wait for loading - jQuery style with interval checking
     */
    function selectCategoryAndWait(categoryId, groupId, bookId, chapterId, contentId, contentType) {
        // Wait for categories to load first (they should be loading during init)
        const checkCategoriesLoaded = setInterval(function() {
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
     * Helper function to select group and wait for loading - jQuery style with interval checking
     */
    function selectGroupAndWait(groupId, bookId, chapterId, contentId, contentType) {
        const checkGroupsLoaded = setInterval(function() {
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
     * Helper function to select book and wait for loading - jQuery style with interval checking
     */
    function selectBookAndWait(bookId, chapterId, contentId, contentType) {
        const checkBooksLoaded = setInterval(function() {
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
     * Helper function to select chapter and wait for loading - jQuery style with interval checking
     */
    function selectChapterAndWait(chapterId, contentId, contentType) {
        const checkChaptersLoaded = setInterval(function() {
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
     * Helper function to select final content and wait for loading - jQuery style with interval checking
     */
    function selectFinalContentAndWait(contentId) {
        const checkContentLoaded = setInterval(function() {
            if ($('#content-final-selector option').length > 1) {
                clearInterval(checkContentLoaded);

                // Select final content item and trigger change event
                $('#content-final-selector').val(contentId).trigger('change');
            }
        }, 100);
    }

    /**
     * Initialize provider selector functionality
     */
    function initProviderSelector() {
        const $provider = $('#provider');
        const $model = $('#model');

        // Show loading state
        $provider.empty().append($('<option>', {
            value: '',
            text: 'Đang tải nhà cung cấp...'
        }));
        $provider.prop('disabled', true);

        // Fetch available providers via jQuery AJAX
        $.ajax({
            url: '/admin/ai-dashboard/providers',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    // Clear existing options
                    $provider.empty().append($('<option>', {
                        value: '',
                        text: 'Chọn nhà cung cấp AI'
                    }));

                    // Add provider options using jQuery
                    $.each(data.providers, function(code, name) {
                        $provider.append($('<option>', {
                            value: code,
                            text: name
                        }));
                    });

                    // Check for stored provider preference
                    const savedProvider = localStorage.getItem('selectedProvider');
                    if (savedProvider && $provider.find('option[value="' + savedProvider + '"]').length > 0) {
                        $provider.val(savedProvider).trigger('change');
                    }
                } else {
                    // Handle error state
                    $provider.empty().append($('<option>', {
                        value: '',
                        text: 'Lỗi tải nhà cung cấp'
                    }));
                }
            },
            error: function(error) {
                console.error('Error loading providers:', error);
                $provider.empty().append($('<option>', {
                    value: '',
                    text: 'Lỗi tải nhà cung cấp'
                }));
            },
            complete: function() {
                $provider.prop('disabled', false);

                // Refresh Select2 if available
                if ($.fn.select2 && $provider.data('select2')) {
                    $provider.trigger('change');
                }
            }
        });

        // Handle provider change
        $provider.on('change', function() {
            const provider = $(this).val();
            if (provider) {
                localStorage.setItem('selectedProvider', provider);
                loadModelsForProvider(provider);

                // Show system message container for deepseek providers
                if (provider === 'deepseek') {
                    $('#system-message-container').removeClass('hidden');

                    // Fetch system message for deepseek
                    fetchSystemMessage('deepseek');
                } else {
                    $('#system-message-container').addClass('hidden');
                }
            } else {
                // Clear model select if no provider
                $model.empty().append($('<option>', {
                    value: '',
                    text: 'Chọn mô hình AI'
                }));
                $model.prop('disabled', true);
                $('#system-message-container').addClass('hidden');
            }
        });
    }

    /**
     * Load models for the selected provider
     * @param {string} provider - Provider code
     */
    function loadModelsForProvider(provider) {
        const $model = $('#model');

        // Show loading state
        $model.empty().append($('<option>', {
            value: '',
            text: 'Đang tải mô hình...'
        }));
        $model.prop('disabled', true);

        // Fetch models via jQuery AJAX
        $.ajax({
            url: '/admin/ai-dashboard/providers/' + provider + '/models',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                // Clear existing options
                $model.empty().append($('<option>', {
                    value: '',
                    text: 'Chọn mô hình AI'
                }));

                if (data.success && data.models) {
                    // Add model options using jQuery
                    $.each(data.models, function(id, name) {
                        $model.append($('<option>', {
                            value: id,
                            text: name
                        }));
                    });

                    // Check for saved model preference
                    const savedModel = localStorage.getItem('selectedModel');
                    if (savedModel && $model.find('option[value="' + savedModel + '"]').length > 0) {
                        $model.val(savedModel).trigger('change');
                    }
                } else {
                    $model.append($('<option>', {
                        value: '',
                        text: 'Không có mô hình nào'
                    }));
                }
            },
            error: function(error) {
                console.error('Error loading models:', error);
                $model.empty().append($('<option>', {
                    value: '',
                    text: 'Lỗi tải mô hình'
                }));
            },
            complete: function() {
                $model.prop('disabled', false);

                // Refresh Select2 if available
                if ($.fn.select2 && $model.data('select2')) {
                    $model.trigger('change');
                }
            }
        });

        // Save model selection
        $('#model').on('change', function() {
            const model = $(this).val();
            if (model) {
                localStorage.setItem('selectedModel', model);
            }
        });
    }

    /**
     * Fetch system message for a specific provider
     * @param {string} type - Provider type (e.g., 'deepseek')
     */
    function fetchSystemMessage(type) {
        $.ajax({
            url: '/admin/ai-dashboard/system-message/' + type,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success && data.message) {
                    $('#system-message').val(data.message);
                }
            },
            error: function(error) {
                console.error('Error fetching system message:', error);
            }
        });
    }

    // Initialize the content selector
    hierarchicalSelector.init();

    // Check for URL parameters and handle them
    if (!handleUrlParameters()) {
        // If no URL parameters, initialize with default content type
        $('.content-type-btn.border-indigo-300').click();
    }

    // Make updateContentDetails function available globally
    window.updateContentDetails = hierarchicalSelector.updateContentDetails;
});
