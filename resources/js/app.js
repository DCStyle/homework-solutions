import './bootstrap';

$(document).ready(function() {
    // Initialize all Select2 elements outside of modals
    $('[data-plugin-select2]').not('.modal [data-plugin-select2]').each(function() {
        $(this).select2();
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

    /**
     * CustomModal - A jQuery plugin for modals without Bootstrap dependency
     */
    (function($) {
        // Global modal registry for managing open modals
        const modalRegistry = [];

        // Default options
        const defaults = {
            backdrop: true,
            keyboard: true,
            focus: true
        };

        // Add global event handler to close modals with escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && modalRegistry.length > 0) {
                const topModal = modalRegistry[modalRegistry.length - 1];
                if (topModal.options.keyboard) {
                    topModal.hide();
                }
            }
        });

        // Handle backdrop clicks
        $(document).on('click', '.modal-backdrop', function(e) {
            if (e.target === this && modalRegistry.length > 0) {
                const topModal = modalRegistry[modalRegistry.length - 1];
                if (topModal.options.backdrop) {
                    topModal.hide();
                }
            }
        });

        /**
         * CustomModal constructor
         * @param {HTMLElement} element - The modal element
         * @param {Object} options - Custom options
         */
        function CustomModal(element, options) {
            this.element = element;
            this.$element = $(element);
            this.options = $.extend({}, defaults, options);
            this.isShown = false;
            this.init();
        }

        /**
         * Initialize the modal
         */
        CustomModal.prototype.init = function() {
            const self = this;

            // Add click handlers for elements that dismiss the modal
            this.$element.find('[data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                self.hide();
            });
        };

        /**
         * Show the modal
         */
        CustomModal.prototype.show = function() {
            if (this.isShown) return;

            const self = this;
            this.isShown = true;

            // Add modal to registry
            modalRegistry.push(this);

            // Create backdrop if needed
            if (this.options.backdrop && $('.modal-backdrop').length === 0) {
                $('<div class="modal-backdrop fixed inset-0 bg-black bg-opacity-50 z-40"></div>')
                    .appendTo('body')
                    .fadeIn(150);
            }

            // Prevent body scrolling
            $('body').addClass('overflow-hidden');

            // Show the modal
            this.$element
                .css('display', 'flex')
                .removeClass('hidden')
                .addClass('z-50')
                .attr('aria-hidden', 'false');

            // Add animation
            setTimeout(() => {
                this.$element.find('.modal-content')
                    .removeClass('opacity-0 scale-95')
                    .addClass('opacity-100 scale-100');
            }, 10);

            // Handle focus
            if (this.options.focus) {
                setTimeout(() => {
                    const $input = this.$element.find('input, textarea, select').filter(':visible').first();
                    if ($input.length > 0) {
                        $input.trigger('focus');
                    } else {
                        this.$element.find('button:not([data-dismiss="modal"])').first().trigger('focus');
                    }
                }, 200);
            }

            // Trigger shown event
            this.$element.trigger('shown.modal');
        };

        /**
         * Hide the modal
         */
        CustomModal.prototype.hide = function() {
            if (!this.isShown) return;

            const self = this;
            this.isShown = false;

            // Remove from registry
            const index = modalRegistry.indexOf(this);
            if (index > -1) {
                modalRegistry.splice(index, 1);
            }

            // Add animation
            this.$element.find('.modal-content')
                .removeClass('opacity-100 scale-100')
                .addClass('opacity-0 scale-95');

            // Wait for animation to complete
            setTimeout(() => {
                // Hide the modal
                self.$element
                    .addClass('hidden')
                    .css('display', 'none') // Explicitly set display to none
                    .attr('aria-hidden', 'true');

                // Remove backdrop if no modals are open
                if (modalRegistry.length === 0) {
                    $('.modal-backdrop').fadeOut(150, function() {
                        $(this).remove();

                        // FIXED: Only remove overflow-hidden class after backdrop is fully removed
                        // to prevent brief interaction with background elements
                        $('body').removeClass('overflow-hidden');
                    });
                }

                // Trigger hidden event
                self.$element.trigger('hidden.modal');
            }, 150);
        };

        // jQuery plugin method
        $.fn.customModal = function(option) {
            return this.each(function() {
                const $this = $(this);
                let data = $this.data('custom-modal');
                const options = typeof option === 'object' && option;

                if (!data) {
                    data = new CustomModal(this, options);
                    $this.data('custom-modal', data);
                }

                if (typeof option === 'string') {
                    data[option]();
                }
            });
        };

        // Initialize modals declared in markup
        $(document).ready(function() {
            // Set up click handlers for elements with data-toggle="modal"
            $(document).on('click', '[data-toggle="modal"]', function(e) {
                e.preventDefault();
                const $this = $(this);
                const target = $this.data('target');
                const $target = $(target);

                if ($target.length > 0) {
                    $target.customModal('show');
                }
            });

            // FIXED: Force cleanup any existing backdrops and overflow states on page load
            if ($('.modal-backdrop').length > 0) {
                $('.modal-backdrop').remove();
                $('body').removeClass('overflow-hidden');
            }
        });
    })(jQuery);

    /**
     * Custom Alert component implementation
     * Replace Bootstrap alerts with TailwindCSS + jQuery
     */
    (function($) {
        // Initialize dismissible alerts
        $(document).ready(function() {
            // Set up click handlers for elements with data-dismiss="alert"
            $(document).on('click', '[data-dismiss="alert"]', function(e) {
                e.preventDefault();

                // Find the parent alert container
                const $alert = $(this).closest('.alert');

                // Fade out and remove
                $alert.removeClass('opacity-100').addClass('opacity-0');
                setTimeout(() => {
                    $alert.remove();
                }, 150);
            });

            // Auto-dismiss alerts with data-auto-dismiss attribute
            $('.alert[data-auto-dismiss]').each(function() {
                const $alert = $(this);
                const delay = parseInt($alert.data('auto-dismiss'), 10) || 5000;

                setTimeout(() => {
                    $alert.find('[data-dismiss="alert"]').trigger('click');
                }, delay);
            });
        });

        // Helper to create alerts programmatically
        window.createAlert = function(message, type, container, autoDismiss) {
            const types = {
                success: 'bg-green-100 border-green-500 text-green-700',
                danger: 'bg-red-100 border-red-500 text-red-700',
                warning: 'bg-yellow-100 border-yellow-500 text-yellow-700',
                info: 'bg-blue-100 border-blue-500 text-blue-700'
            };

            const $container = $(container || 'body');
            const dismissTime = autoDismiss === true ? 5000 : (typeof autoDismiss === 'number' ? autoDismiss : null);

            const $alert = $(
                `<div class="alert ${types[type] || types.info} transition-opacity duration-150 opacity-0 border-l-4 p-4 my-4 rounded" role="alert"${dismissTime ? ' data-auto-dismiss="' + dismissTime + '"' : ''}>
                <div class="flex items-center justify-between">
                    <p>${message}</p>
                    <button type="button" class="text-gray-500 hover:text-gray-800" data-dismiss="alert">
                        <span class="iconify" data-icon="mdi-close"></span>
                    </button>
                </div>
            </div>`
            );

            // Append to container and animate in
            $container.append($alert);
            setTimeout(() => {
                $alert.removeClass('opacity-0').addClass('opacity-100');
            }, 10);

            // Auto-dismiss if needed
            if (dismissTime) {
                setTimeout(() => {
                    $alert.find('[data-dismiss="alert"]').trigger('click');
                }, dismissTime);
            }

            return $alert;
        };
    })(jQuery);

    /**
     * UI Components - Tabs, Dropdowns, Tooltips without Bootstrap dependency
     */
    (function($) {
        /**
         * Tab component implementation
         */
        function initTabs() {
            $(document).on('click', '[data-toggle="tab"]', function(e) {
                e.preventDefault();

                const $this = $(this);
                const target = $this.attr('href') || $this.data('target');

                if (!target) return;

                // Find all tab links in the same group and deactivate them
                const $tabLinks = $this.closest('[role="tablist"]').find('[data-toggle="tab"]');
                $tabLinks.removeClass('active bg-white border-indigo-500 text-indigo-600')
                    .addClass('text-gray-500 hover:text-gray-700 hover:border-gray-300');

                // Activate the clicked tab
                $this.addClass('active bg-white border-indigo-500 text-indigo-600')
                    .removeClass('text-gray-500 hover:text-gray-700 hover:border-gray-300');

                // Hide all tab panes
                const $tabContent = $(target).closest('.tab-content');
                $tabContent.find('.tab-pane').removeClass('block').addClass('hidden');

                // Show the target tab pane
                $(target).removeClass('hidden').addClass('block');

                // Trigger events
                $this.trigger('shown.tab');
            });
        }

        /**
         * Dropdown component implementation
         */
        function initDropdowns() {
            // Toggle dropdown
            $(document).on('click', '[data-toggle="dropdown"]', function(e) {
                e.preventDefault();

                const $this = $(this);
                const $dropdown = $this.next('.dropdown-menu');

                if ($dropdown.hasClass('hidden')) {
                    // Close other open dropdowns
                    $('.dropdown-menu:not(.hidden)').addClass('hidden');

                    // Open this dropdown
                    $dropdown.removeClass('hidden');

                    // Add global event listener to close dropdown when clicking outside
                    setTimeout(() => {
                        $(document).one('click', function closeDropdown(e) {
                            if (!$this.is(e.target) && !$dropdown.is(e.target) && $dropdown.has(e.target).length === 0) {
                                $dropdown.addClass('hidden');
                            } else if (!$dropdown.hasClass('hidden')) {
                                // Re-bind the event if clicking inside the dropdown
                                setTimeout(() => {
                                    $(document).one('click', closeDropdown);
                                }, 0);
                            }
                        });
                    }, 0);
                } else {
                    // Close this dropdown
                    $dropdown.addClass('hidden');
                }
            });
        }

        /**
         * Tooltip component implementation
         */
        function initTooltips() {
            // Create tooltip element if it doesn't exist
            if ($('#custom-tooltip').length === 0) {
                $('<div id="custom-tooltip" class="absolute z-50 p-2 bg-gray-900 text-white text-sm rounded-md shadow-lg opacity-0 pointer-events-none transition-opacity duration-150"></div>')
                    .appendTo('body');
            }

            const $tooltip = $('#custom-tooltip');

            // Show tooltip on hover
            $(document).on('mouseenter', '[data-tooltip]', function() {
                const $this = $(this);
                const text = $this.data('tooltip');
                const position = $this.data('tooltip-position') || 'top';

                if (!text) return;

                // Set tooltip content
                $tooltip.text(text);

                // Position tooltip
                const thisRect = this.getBoundingClientRect();
                const tooltipRect = $tooltip[0].getBoundingClientRect();

                let top, left;
                switch (position) {
                    case 'top':
                        top = thisRect.top - tooltipRect.height - 5;
                        left = thisRect.left + (thisRect.width / 2) - (tooltipRect.width / 2);
                        break;
                    case 'bottom':
                        top = thisRect.bottom + 5;
                        left = thisRect.left + (thisRect.width / 2) - (tooltipRect.width / 2);
                        break;
                    case 'left':
                        top = thisRect.top + (thisRect.height / 2) - (tooltipRect.height / 2);
                        left = thisRect.left - tooltipRect.width - 5;
                        break;
                    case 'right':
                        top = thisRect.top + (thisRect.height / 2) - (tooltipRect.height / 2);
                        left = thisRect.right + 5;
                        break;
                }

                // Adjust for window edges
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;

                top = Math.max(5, Math.min(windowHeight - tooltipRect.height - 5, top));
                left = Math.max(5, Math.min(windowWidth - tooltipRect.width - 5, left));

                // Set position and show tooltip
                $tooltip.css({
                    top: `${top}px`,
                    left: `${left}px`
                }).removeClass('opacity-0').addClass('opacity-100');
            });

            // Hide tooltip on mouse leave
            $(document).on('mouseleave', '[data-tooltip]', function() {
                $tooltip.removeClass('opacity-100').addClass('opacity-0');
            });
        }

        /**
         * Custom Select component styling
         */
        function initCustomSelects() {
            // Apply custom styling to select elements with data-custom-select
            $('select[data-custom-select]').each(function() {
                const $select = $(this);
                const $wrapper = $('<div class="custom-select-wrapper relative"></div>');

                // Style the select
                $select.addClass('appearance-none w-full rounded-md border border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200');

                // Add dropdown icon
                const $icon = $('<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg></div>');

                // Wrap select in custom container
                $select.wrap($wrapper);
                $select.after($icon);
            });
        }

        /**
         * Custom Range input styling
         */
        function initRangeInputs() {
            // Apply custom styling to range inputs with data-custom-range
            $('input[type="range"][data-custom-range]').each(function() {
                const $range = $(this);
                const $wrapper = $('<div class="custom-range-wrapper flex items-center"></div>');

                // Get value display element if it exists
                let $valueDisplay;
                const valueDisplayId = $range.data('value-display');
                if (valueDisplayId) {
                    $valueDisplay = $('#' + valueDisplayId);
                } else {
                    // Create value display
                    $valueDisplay = $('<div class="ml-2 text-sm font-medium text-gray-700"></div>');
                }

                // Style the range input
                $range.addClass('w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer');

                // Update value display
                const updateValue = function() {
                    $valueDisplay.text($range.val());
                };

                // Initial value
                updateValue();

                // Update on input
                $range.on('input', updateValue);

                // Wrap range in custom container
                $range.wrap($wrapper);

                // Add value display if not already attached
                if (!valueDisplayId) {
                    $range.after($valueDisplay);
                }
            });
        }

        // Initialize all components
        $(document).ready(function() {
            initTabs();
            initDropdowns();
            initTooltips();
            initCustomSelects();
            initRangeInputs();
        });
    })(jQuery);
});
