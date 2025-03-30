<!-- ===== Header Start ===== -->
<header class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <!-- Left side: Menu button and logo -->
            <div class="flex items-center lg:ml-16">
                <!-- Mobile menu button -->
                <button type="button" id="mobile-sidebar-toggle" class="inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden">
                    <span class="sr-only">Mở rộng</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Brand logo (visible only on desktop) -->
                <div class="hidden lg:ml-3 lg:flex lg:items-center">
                    <span class="font-medium text-lg text-slate-900">{{ setting('site_name', 'Homework Solutions') }}</span>
                </div>

                <!-- Page title - show current section (for mobile) -->
                <div class="ml-4 lg:hidden">
                    <h1 class="text-base font-medium text-slate-800" id="mobile-page-title">Bảng điều khiển</h1>
                </div>
            </div>

            <!-- Right side: Notifications, search, etc. -->
            <div class="flex items-center space-x-4">
                <!-- Visit website -->
                <a href="{{ route('home') }}" class="hidden sm:inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-slate-600 ring-1 ring-inset ring-slate-300 hover:bg-slate-50" target="_blank">
                    <svg class="mr-1.5 h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                    Về trang chủ
                </a>

                <!-- User dropdown -->
                <div class="relative">
                    <button type="button" data-toggle="dropdown" class="flex items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span class="sr-only">Open user menu</span>
                        <div class="flex items-center">
                            <div class="hidden md:block mr-3 text-right">
                                <div class="text-sm font-medium text-slate-800">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-slate-500">Administrator</div>
                            </div>
                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-medium text-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <svg class="ml-1 h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown menu -->
                    <div class="dropdown-menu absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" tabindex="-1">
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
                           role="menuitem">
                            Thoát
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- ===== Header End ===== -->

<!-- Search Modal -->
<div id="search-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="modal-content opacity-0 scale-95 transform transition-all w-full max-w-2xl bg-white rounded-lg shadow-xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">Search</h3>
                    <button type="button" data-dismiss="modal" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-search-container relative">
                    <input type="text"
                           class="modal-search-input w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           placeholder="Search for anything..."
                           data-min-length="2"
                           data-is-admin="1">
                    <div class="loading-spinner absolute right-3 top-3 hidden">
                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                <div class="modal-search-results mt-4">
                    <div class="modal-results-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // This will keep the mobile page title dynamic
        $(document).ready(function() {
            // Update mobile page title based on current page
            const pageTitle = $('h1, h2.text-2xl, h2.text-3xl').first();
            const mobilePageTitle = $('#mobile-page-title');

            if (pageTitle.length && mobilePageTitle.length) {
                mobilePageTitle.text(pageTitle.text().trim());
            }

            // Toggle sidebar on mobile
            $('#mobile-sidebar-toggle').on('click', function() {
                const sidebarContainer = $('#sidebar');

                if (sidebarContainer.hasClass('-translate-x-full')) {
                    sidebarContainer.removeClass('-translate-x-full').addClass('translate-x-0');
                } else {
                    sidebarContainer.removeClass('translate-x-0').addClass('-translate-x-full');
                }
            });
        });
    </script>
@endpush
