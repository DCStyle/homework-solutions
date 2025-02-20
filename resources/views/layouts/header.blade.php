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
            <div class="flex items-center">
                <!-- Search -->
                <div class="hidden xl:flex items-center">
                    <span class="iconify text-2xl cursor-pointer"
                          data-icon="mdi-magnify"
                          data-bs-toggle="modal"
                          data-bs-target="#search-modal"
                    ></span>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center xl:hidden ml-4">
                    <span class="iconify text-2xl text-white cursor-pointer"
                          data-icon="mdi-view-grid"
                          data-bs-toggle="modal"
                          data-bs-target="#mobile-menu-modal"></span>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Menu Modal -->
<div class="modal fade" id="mobile-menu-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
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
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Grab all dropdown "trigger" elements in mobile menu
            const triggers = document.querySelectorAll('.dropdown-trigger');

            triggers.forEach(trigger => {
                // The dropdown content is typically the next .dropdown-content sibling
                const content = trigger.parentElement.querySelector('.dropdown-content');

                if (trigger && content) {
                    trigger.addEventListener('click', (e) => {
                        // If the user clicks directly on a link, do nothing special
                        if (
                            e.target.tagName === 'A' ||
                            (e.target.parentElement && e.target.parentElement.tagName === 'A')
                        ) {
                            return;
                        }

                        const isExpanded = !content.classList.contains('hidden');

                        // Toggle arrow rotation
                        const icon = trigger.querySelector('[data-icon="mdi-chevron-down"]');
                        if (icon) {
                            icon.style.transform = isExpanded ? 'rotate(0)' : 'rotate(180deg)';
                        }

                        // Animate the open/close
                        if (isExpanded) {
                            // Closing
                            content.style.maxHeight = '0';
                            content.style.opacity = '0';
                            setTimeout(() => {
                                content.classList.add('hidden');
                                content.style.maxHeight = '';
                                content.style.opacity = '';
                            }, 200);
                        } else {
                            // Opening
                            content.classList.remove('hidden');
                            content.style.maxHeight = content.scrollHeight + 'px';
                            setTimeout(() => {
                                content.style.maxHeight = '';
                            }, 200);
                        }
                    });
                }
            });
        });
    </script>
@endpush
