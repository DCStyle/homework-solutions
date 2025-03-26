<nav class="sticky top-0 shadow-md header-container text-white z-10">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left side with logo -->
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="text-lg font-semibold text-decoration-none">
                        @if(setting('site_logo'))
                            {{-- Use Storage::url(...) directly to avoid malformed URLs --}}
                            <img src="{{ Storage::url(setting('site_logo')) }}"
                                 alt="{{ setting('site_name') }}"
                                 class="h-8" />
                        @else
                            {{ setting('site_name', 'Homework Solutions') }}
                        @endif
                    </a>
                </div>

                <!-- Main navigation -->
                <div class="hidden ml-6 items-center xl:flex">
                    @foreach($menuItems->where('parent_id', null) as $menuItem)
                        @if($menuItem->children->count() > 0)
                            <div class="relative group">
                                @if($menuItem->type === 'category')
                                    <a href="{{ route('categories.show', ['category_slug' => $menuItem->category?->slug]) }}"
                                       class="flex items-center gap-2 text-lg px-4 py-2 hover:text-gray-200">
                                        @if($menuItem->icon)
                                            <span class="iconify" data-icon="{{ $menuItem->icon }}"></span>
                                        @endif
                                        {{ $menuItem->name }}
                                        <span class="iconify" data-icon="mdi-chevron-down"></span>
                                    </a>
                                @else
                                    <button class="flex items-center gap-2 text-lg py-2 px-4">
                                        @if($menuItem->icon)
                                            <span class="iconify" data-icon="{{ $menuItem->icon }}"></span>
                                        @endif
                                        {{ $menuItem->name }}
                                        <span class="iconify" data-icon="mdi-chevron-down"></span>
                                    </button>
                                @endif

                                <div class="absolute left-0 top-full hidden group-hover:block w-48 bg-white shadow-lg text-gray-800 py-2 rounded-b">
                                    @foreach($menuItem->children as $child)
                                        @if($child->children->count() > 0)
                                            <div class="relative group/submenu">
                                                @if($child->type === 'category')
                                                    <a href="{{ route('categories.show', ['category_slug' => $child->category?->slug]) }}"
                                                       class="flex items-center justify-between px-4 py-2 hover:bg-gray-100">
                                                        @if($child->icon)
                                                            <span class="iconify mr-2" data-icon="{{ $child->icon }}"></span>
                                                        @endif
                                                        <span>{{ $child->name }}</span>
                                                        <span class="iconify" data-icon="mdi-chevron-right"></span>
                                                    </a>
                                                @else
                                                    <a href="{{ $child->url }}"
                                                       class="flex items-center justify-between px-4 py-2 hover:bg-gray-100">
                                                        @if($child->icon)
                                                            <span class="iconify mr-2" data-icon="{{ $child->icon }}"></span>
                                                        @endif
                                                        <span>{{ $child->name }}</span>
                                                        <span class="iconify" data-icon="mdi-chevron-right"></span>
                                                    </a>
                                                @endif

                                                <div class="absolute left-full top-0 hidden group-hover/submenu:block w-48 bg-white shadow-lg text-gray-800 py-2 rounded-r">
                                                    @foreach($child->children as $grandChild)
                                                        @if($grandChild->type === 'category')
                                                            <a href="{{ route('categories.show', ['category_slug' => $grandChild->category?->slug]) }}"
                                                               class="flex items-center px-4 py-2 hover:bg-gray-100">
                                                                @if($grandChild->icon)
                                                                    <span class="iconify mr-2" data-icon="{{ $grandChild->icon }}"></span>
                                                                @endif
                                                                {{ $grandChild->name }}
                                                            </a>
                                                        @else
                                                            <a href="{{ $grandChild->url }}"
                                                               class="flex items-center px-4 py-2 hover:bg-gray-100">
                                                                @if($grandChild->icon)
                                                                    <span class="iconify mr-2" data-icon="{{ $grandChild->icon }}"></span>
                                                                @endif
                                                                {{ $grandChild->name }}
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            @if($child->type === 'category')
                                                <a href="{{ route('categories.show', ['category_slug' => $child->category?->slug]) }}"
                                                   class="flex items-center px-4 py-2 hover:bg-gray-100">
                                                    @if($child->icon)
                                                        <span class="iconify mr-2" data-icon="{{ $child->icon }}"></span>
                                                    @endif
                                                    {{ $child->name }}
                                                </a>
                                            @else
                                                <a href="{{ $child->url }}"
                                                   class="flex items-center px-4 py-2 hover:bg-gray-100">
                                                    @if($child->icon)
                                                        <span class="iconify mr-2" data-icon="{{ $child->icon }}"></span>
                                                    @endif
                                                    {{ $child->name }}
                                                </a>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            @if($menuItem->type === 'category')
                                <a href="{{ route('categories.show', ['category_slug' => $menuItem->category?->slug]) }}"
                                   class="text-lg py-2 px-4 flex items-center gap-2 hover:text-gray-200">
                                    @if($menuItem->icon)
                                        <span class="iconify" data-icon="{{ $menuItem->icon }}"></span>
                                    @endif
                                    {{ $menuItem->name }}
                                </a>
                            @else
                                <a href="{{ $menuItem->url }}"
                                   class="text-lg py-2 px-4 flex items-center gap-2 hover:text-gray-200">
                                    @if($menuItem->icon)
                                        <span class="iconify" data-icon="{{ $menuItem->icon }}"></span>
                                    @endif
                                    {{ $menuItem->name }}
                                </a>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex items-center gap-4">
                <!-- Search -->
                <span class="iconify text-2xl cursor-pointer search-toggle"
                      data-icon="mdi-magnify"></span>

                <!-- Mobile menu -->
                <span class="iconify text-2xl text-white cursor-pointer xl:hidden mobile-menu-toggle"
                      data-icon="mdi-view-grid"></span>

                <!-- Administrator button -->
                @if(auth()->user()?->isAdmin() ?? false)
                    <a href="{{ route('admin.dashboard') }}" target="_blank" class="text-white">
                        <span class="iconify text-2xl cursor-pointer"
                              data-icon="mdi-cog"></span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>

<!-- Search Modal -->
<div id="search-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white w-full max-w-4xl mx-auto mt-20 rounded-lg shadow-xl overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-xl font-semibold">Search</h3>
            <button type="button" class="search-close text-gray-500 hover:text-gray-700">
                <span class="iconify text-2xl" data-icon="mdi-close"></span>
            </button>
        </div>
        <div class="p-4">
            <div class="modal-search-container mb-4">
                <div class="relative">
                        <span class="iconify absolute left-3 !top-1/2 !-translate-y-1/2 text-gray-400"
                              data-icon="mdi-magnify"></span>
                    <input type="text"
                           class="modal-search-input w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Tìm kiếm..."
                           data-min-length="2"
                           data-is-admin="false">
                    <div class="loading-spinner absolute right-3 top-1/2 -translate-y-1/2 hidden">
                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Search Results -->
            <div class="modal-search-results max-h-96 overflow-y-auto">
                <div class="modal-results-content"></div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Menu Modal -->
<div id="mobile-menu-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white w-full h-full overflow-y-auto">
        <div class="flex justify-end items-center p-4 border-b">
            <button type="button" class="mobile-menu-close text-gray-500 hover:text-gray-700">
                <span class="iconify text-2xl" data-icon="mdi-close"></span>
            </button>
        </div>

        <div class="p-4">
            <div class="container">
                <div class="space-y-4">
                    @foreach($menuItems->where('parent_id', null) as $menuItem)
                        @if($menuItem->children->count() > 0)
                            <div class="mobile-menu-item">
                                {{-- If the menu item is a "category" with a direct link: --}}
                                @if($menuItem->type === 'category')
                                    <a href="{{ route('categories.show', ['category_slug' => $menuItem->category?->slug]) }}"
                                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            @if($menuItem->icon)
                                                <span class="iconify" data-icon="{{ $menuItem->icon }}"></span>
                                            @endif
                                            <span>{{ $menuItem->name }}</span>
                                        </div>
                                    </a>
                                @endif

                                {{-- Dropdown trigger (no inline onclick) --}}
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer dropdown-trigger">
                                    <div class="flex items-center gap-2">
                                        @if($menuItem->icon)
                                            <span class="iconify" data-icon="{{ $menuItem->icon }}"></span>
                                        @endif
                                        <span>{{ $menuItem->name }}</span>
                                    </div>
                                    <span class="iconify" data-icon="mdi-chevron-down"></span>
                                </div>

                                {{-- Dropdown content (initially hidden) --}}
                                <div class="hidden pl-4 mt-2 space-y-2 dropdown-content">
                                    @foreach($menuItem->children as $child)
                                        @if($child->children->count() > 0)
                                            <div class="mobile-submenu-item">
                                                {{-- If child is a category with direct link --}}
                                                @if($child->type === 'category')
                                                    <a href="{{ route('categories.show', ['category_slug' => $child->category?->slug]) }}"
                                                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                        <div class="flex items-center gap-2">
                                                            @if($child->icon)
                                                                <span class="iconify" data-icon="{{ $child->icon }}"></span>
                                                            @endif
                                                            <span>{{ $child->name }}</span>
                                                        </div>
                                                    </a>
                                                @endif

                                                {{-- Dropdown trigger for child --}}
                                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer dropdown-trigger">
                                                    <div class="flex items-center gap-2">
                                                        @if($child->icon)
                                                            <span class="iconify" data-icon="{{ $child->icon }}"></span>
                                                        @endif
                                                        <span>{{ $child->name }}</span>
                                                    </div>
                                                    <span class="iconify" data-icon="mdi-chevron-down"></span>
                                                </div>

                                                {{-- Grandchild dropdown content --}}
                                                <div class="hidden pl-4 mt-2 space-y-2 dropdown-content">
                                                    @foreach($child->children as $grandChild)
                                                        @if($grandChild->type === 'category')
                                                            <a href="{{ route('categories.show', ['category_slug' => $grandChild->category?->slug]) }}"
                                                               class="flex items-center gap-2 p-3 hover:bg-gray-50 rounded-lg">
                                                                @if($grandChild->icon)
                                                                    <span class="iconify" data-icon="{{ $grandChild->icon }}"></span>
                                                                @endif
                                                                {{ $grandChild->name }}
                                                            </a>
                                                        @else
                                                            <a href="{{ $grandChild->url }}"
                                                               class="flex items-center gap-2 p-3 hover:bg-gray-50 rounded-lg">
                                                                @if($grandChild->icon)
                                                                    <span class="iconify" data-icon="{{ $grandChild->icon }}"></span>
                                                                @endif
                                                                {{ $grandChild->name }}
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            @if($child->type === 'category')
                                                <a href="{{ route('categories.show', ['category_slug' => $child->category?->slug]) }}"
                                                   class="flex items-center gap-2 p-3 hover:bg-gray-50 rounded-lg">
                                                    @if($child->icon)
                                                        <span class="iconify" data-icon="{{ $child->icon }}"></span>
                                                    @endif
                                                    {{ $child->name }}
                                                </a>
                                            @else
                                                <a href="{{ $child->url }}"
                                                   class="flex items-center gap-2 p-3 hover:bg-gray-50 rounded-lg">
                                                    @if($child->icon)
                                                        <span class="iconify" data-icon="{{ $child->icon }}"></span>
                                                    @endif
                                                    {{ $child->name }}
                                                </a>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            @if($menuItem->type === 'category')
                                <a href="{{ route('categories.show', ['category_slug' => $menuItem->category?->slug]) }}"
                                   class="flex items-center gap-2 p-3 hover:bg-gray-50 rounded-lg">
                                    @if($menuItem->icon)
                                        <span class="iconify" data-icon="{{ $menuItem->icon }}"></span>
                                    @endif
                                    {{ $menuItem->name }}
                                </a>
                            @else
                                <a href="{{ $menuItem->url }}"
                                   class="flex items-center gap-2 p-3 hover:bg-gray-50 rounded-lg">
                                    @if($menuItem->icon)
                                        <span class="iconify" data-icon="{{ $menuItem->icon }}"></span>
                                    @endif
                                    {{ $menuItem->name }}
                                </a>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        /* Additional styles for nested menus */
        .group/submenu:hover .hidden {
                   display: block;
               }

        /* Ensure submenus don't go off-screen */
        .group:hover .group-hover\:block {
            max-height: calc(100vh - 4rem);
            overflow-y: auto;
        }

        /* Mobile menu transitions */
        .mobile-menu-item .hidden,
        .mobile-submenu-item .hidden {
            transition: all 0.3s ease-in-out;
        }

        /* Hover effects for menu items */
        .menu-item-hover {
            transition: all 0.2s ease-in-out;
        }

        .menu-item-hover:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Dropdown arrow rotation on hover in desktop nav */
        .group:hover .iconify[data-icon="mdi-chevron-down"] {
            transform: rotate(180deg);
            transition: transform 0.2s ease-in-out;
        }

        /* Mobile menu item styles */
        .mobile-menu-item > .bg-gray-50:hover,
        .mobile-submenu-item > .bg-gray-50:hover {
            background-color: #f3f4f6;
        }

        /* Modal animations */
        #search-modal, #mobile-menu-modal {
            transition: opacity 0.3s ease;
        }

        #search-modal.show, #mobile-menu-modal.show {
            display: block;
            opacity: 1;
        }

        #search-modal.hiding, #mobile-menu-modal.hiding {
            opacity: 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Modal toggling
            $('.search-toggle').click(function() {
                $('#search-modal').addClass('show');
            });

            $('.search-close').click(function() {
                $('#search-modal').addClass('hiding');
                setTimeout(function() {
                    $('#search-modal').removeClass('show hiding');
                }, 300);
            });

            $('.mobile-menu-toggle').click(function() {
                $('#mobile-menu-modal').addClass('show');
            });

            $('.mobile-menu-close').click(function() {
                $('#mobile-menu-modal').addClass('hiding');
                setTimeout(function() {
                    $('#mobile-menu-modal').removeClass('show hiding');
                }, 300);
            });



            // Mobile menu dropdowns
            $('.dropdown-trigger').click(function(e) {
                // If the user clicks directly on a link, do nothing special
                if (
                    e.target.tagName === 'A' ||
                    (e.target.parentElement && e.target.parentElement.tagName === 'A')
                ) {
                    return;
                }

                var content = $(this).siblings('.dropdown-content');
                var icon = $(this).find('[data-icon="mdi-chevron-down"]');

                if (content.is(':visible')) {
                    // Closing
                    content.css({
                        'max-height': '0',
                        'opacity': '0'
                    });

                    icon.css('transform', 'rotate(0)');

                    setTimeout(function() {
                        content.hide();
                        content.css({
                            'max-height': '',
                            'opacity': ''
                        });
                    }, 200);
                } else {
                    // Opening
                    content.show();
                    var scrollHeight = content.prop('scrollHeight');
                    content.css('max-height', scrollHeight + 'px');

                    icon.css('transform', 'rotate(180deg)');

                    setTimeout(function() {
                        content.css('max-height', '');
                    }, 200);
                }
            });

            // Close mobile menu when clicking on a link
            $('#mobile-menu-modal a').click(function() {
                $('#mobile-menu-modal').addClass('hiding');
                setTimeout(function() {
                    $('#mobile-menu-modal').removeClass('show hiding');
                }, 300);
            });

            // Handle escape key for modals
            $(document).keydown(function(e) {
                if (e.key === "Escape") {
                    $('.search-close').click();
                    $('.mobile-menu-close').click();
                }
            });
        });
    </script>
@endpush
