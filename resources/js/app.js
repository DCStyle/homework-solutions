import './bootstrap';

$(document).ready(function() {
    // Initialize all Select2 elements outside of modals
    $('[data-plugin-select2]').not('.modal [data-plugin-select2]').each(function() {
        $(this).select2();
    });

    // Handle Bootstrap modal events
    $('.modal').each(function() {
        var modal = $(this);

        // Initialize Select2 after modal is fully shown
        modal.on('shown.bs.modal', function() {
            setTimeout(function() {
                modal.find('[data-plugin-select2]').each(function() {
                    // Destroy if already initialized
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }

                    // Reinitialize with modal as parent
                    $(this).select2({
                        dropdownParent: modal
                    });
                });
            }, 50); // Short delay to ensure DOM is ready
        });

        // Clean up on modal hide
        modal.on('hide.bs.modal', function() {
            modal.find('[data-plugin-select2]').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
        });
    });

    $('[data-plugin-relative-time]').each(function() {
        $(this).relativeTime();
    });

    // Search bar
    let searchTimeout;

    // Perform search
    function performSearch($input) {
        const $container = $input.closest('.search-bar-container');
        const $results = $container.find('.search-results');
        const $spinner = $container.find('.loading-spinner');

        const searchTerm = $input.val();
        const minLength = parseInt($input.data('min-length'));

        if (searchTerm.length < minLength) {
            $results.html('').hide();
            return;
        }

        $spinner.removeClass('hidden');

        const params = {
            search: searchTerm,
            model: $input.data('model'),
            fields: $input.data('search-fields'),
            title_field: $input.data('title-field'),
            subtitle_field: $input.data('subtitle-field'),
            route_name: $input.data('route-name'),
            limit: $input.data('limit'),
            is_admin: $input.data('is-admin')
        };

        $.ajax({
            url: $input.data('route'),
            method: 'GET',
            data: params,
            success: function(data) {
                let html = '';

                if (data.length) {
                    data.forEach(function(result) {
                        html += `
                            <a href="${result.url}" class="block text-dark px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                <div class="font-medium">${result.title}</div>
                                <div class="text-sm text-gray-600">${result.subtitle || ''}</div>
                            </a>
                        `;
                    });
                } else {
                    html = '<div class="px-4 py-2 text-gray-500">No results found</div>';
                }

                $results.html(html).show();
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
                $results.html('<div class="px-4 py-2 text-red-500">An error occurred</div>').show();
            },
            complete: function() {
                $spinner.addClass('hidden');
            }
        });
    }

    // Handle input changes with debounce
    $(document).on('input', '.search-input', function() {
        const $input = $(this);
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch($input);
        }, 300);
    });

    // Close results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-bar-container').length) {
            $('.search-results').hide();
        }
    });

    // Show results again when clicking input
    $(document).on('click', '.search-input', function() {
        const $container = $(this).closest('.search-bar-container');
        const $results = $container.find('.search-results');
        if ($results.html()) {
            $results.show();
        }
    });

    // Multi search bar
    function performMultiSearch($input) {
        const $container = $input.closest('.multi-search-bar-container');
        const $results = $container.find('.search-results');
        const $resultsContent = $results.find('.results-content');
        const $spinner = $container.find('.loading-spinner');

        const searchTerm = $input.val();
        const minLength = parseInt($input.data('min-length'));

        if (searchTerm.length < minLength) {
            $results.hide();
            $resultsContent.html('');
            return;
        }

        $spinner.removeClass('hidden');

        const params = {
            search: searchTerm,
            is_admin: $input.data('is-admin'),
            models: $input.data('models')
        };

        $.ajax({
            url: '/multi-search',
            method: 'GET',
            data: params,
            success: function(data) {
                let html = '';

                if (Object.keys(data).length) {
                    Object.entries(data).forEach(([model, modelData]) => {
                        html += `
                            <div class="model-section">
                                <div class="px-4 py-2 bg-gray-50 font-medium text-orange-400">
                                    ${modelData.label}
                                </div>
                                <div class="model-results">`;

                        modelData.results.forEach(result => {
                            html += `
                                <a href="${result.url}" class="block text-dark px-4 py-2 hover:bg-gray-100">
                                    <div class="font-medium">${result.title}</div>
                                    ${result.subtitle ? `<div class="text-sm text-gray-600">${result.subtitle}</div>` : ''}
                                </a>`;
                        });

                        html += `
                                </div>
                            </div>`;
                    });
                } else {
                    html = '<div class="px-4 py-2 text-gray-500">No results found</div>';
                }

                $resultsContent.html(html);
                $results.show();
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
                $resultsContent.html('<div class="px-4 py-2 text-red-500">An error occurred</div>');
                $results.show();
            },
            complete: function() {
                $spinner.addClass('hidden');
            }
        });
    }

    // Handle input changes with debounce
    $(document).on('input', '.multi-search-input', function() {
        const $input = $(this);
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performMultiSearch($input);
        }, 300);
    });

    // Close results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.multi-search-bar-container').length) {
            $('.search-results').hide();
        }
    });

    // Show results again when clicking input
    $(document).on('click', '.multi-search-input', function() {
        const $container = $(this).closest('.multi-search-bar-container');
        const $results = $container.find('.search-results');
        if ($results.find('.results-content').html()) {
            $results.show();
        }
    });

    // Modal search
    $(document).on('input', '.modal-search-input', function() {
        const $input = $(this);
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performMultiSearchModal($input);
        }, 300);
    });

    function performMultiSearchModal($input) {
        const $container = $input.closest('.modal-search-container');
        const $results = $('.modal-search-results');
        const $resultsContent = $results.find('.modal-results-content');
        const $spinner = $container.find('.loading-spinner');

        const searchTerm = $input.val();
        const minLength = parseInt($input.data('min-length'));

        if (searchTerm.length < minLength) {
            $resultsContent.html('');
            return;
        }

        $spinner.removeClass('hidden');

        const params = {
            search: searchTerm,
            is_admin: $input.data('is-admin')
        };

        $.ajax({
            url: '/multi-search',
            method: 'GET',
            data: params,
            success: function(data) {
                let html = '';

                if (Object.keys(data).length) {
                    Object.entries(data).forEach(([model, modelData]) => {
                        html += `
                            <div class="model-section mb-4">
                                <div class="px-3 py-2 bg-gray-50 font-medium text-orange-400 rounded-t">
                                    ${modelData.label}
                                </div>
                                <div class="model-results border-x border-b rounded-b">`;

                        modelData.results.forEach(result => {
                            html += `
                                <a href="${result.url}"
                                   class="block px-4 py-3 hover:bg-gray-50 border-t first:border-t-0">
                                    <div class="font-medium text-gray-900">${result.title}</div>
                                    ${result.subtitle ? `
                                        <div class="text-sm text-gray-600 mt-1">${result.subtitle}</div>
                                    ` : ''}
                                </a>`;
                        });

                        html += `
                                </div>
                            </div>`;
                    });
                } else {
                    html = '<div class="px-4 py-3 text-gray-500">Không tìm thấy kết quả</div>';
                }

                $resultsContent.html(html);
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
                $resultsContent.html('<div class="px-4 py-3 text-red-500">Đã xảy ra lỗi</div>');
            },
            complete: function() {
                $spinner.addClass('hidden');
            }
        });
    }

    // Clear results when modal is hidden
    $('#search-modal').on('hidden.bs.modal', function () {
        const $input = $(this).find('.modal-search-input');
        $input.val('');
        $('.modal-results-content').html('');
    });

    // Focus input when modal is shown
    $('#search-modal').on('shown.bs.modal', function () {
        $(this).find('.modal-search-input').focus();
    });

    // Article load more
    let loading = false;

    $('[data-article-load-more]').click(function() {
        if (loading) return;

        const button = $(this);
        const currentPage = parseInt(button.data('page'));
        const nextPage = currentPage + 1;

        loading = true;
        button.html('<i class="fas fa-spinner fa-spin"></i> Đang tải...');

        $.ajax({
            url: '/articles/latest',
            data: { page: nextPage },
            success: function(response) {
                $('#articles-container').append(response.html);
                button.data('page', nextPage);

                if (!response.hasMore) {
                    button.remove();
                }
            },
            complete: function() {
                loading = false;
                button.html('Xem thêm');
            }
        });
    });
});
