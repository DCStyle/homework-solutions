<!-- ===== Sidebar Start ===== -->
<aside class="fixed inset-y-0 top-0 left-0 flex flex-col w-64 lg:w-72 bg-white border-r border-gray-200 pt-5 pb-4 -translate-x-full lg:translate-x-0 transform transition-transform duration-300 ease-in-out z-40">
    <!-- Sidebar Logo/Header -->
    <div class="flex items-center justify-center px-6 mb-6">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center">
            <!-- You can add your logo here -->
            <span class="text-xl font-semibold text-indigo-600">{{ setting('site_name') }}</span>
        </a>
    </div>

    <!-- Sidebar Content -->
    <div class="flex flex-col flex-1 overflow-y-auto">
        <nav class="flex-1 space-y-2 px-3">
            <!-- Dashboard Group -->
            <div class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                    Bảng điều khiển
                </a>
            </div>

            <!-- Settings Group -->
            <div class="pt-5">
                <h3 class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Cài đặt
                </h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.settings.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.settings.index') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                        </svg>
                        Cài đặt chung
                    </a>

                    <a href="{{ route('admin.settings.home') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.home') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.settings.home') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                        </svg>
                        Cài đặt trang chủ
                    </a>

                    <a href="{{ route('admin.menu.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.menu.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.menu.*') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                        Quản lý menu
                    </a>

                    <a href="{{ route('admin.footer.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.footer.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.footer.*') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                        Quản lý footer
                    </a>
                </div>
            </div>

            <!-- AI Tools Group -->
            <div class="pt-5">
                <h3 class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    AI Tools
                </h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.ai-dashboard.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.ai-dashboard.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.ai-dashboard.*') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                        Cài đặt chung
                    </a>

                    <a href="{{ route('admin.ai_api_keys.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.ai_api_keys.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.ai_api_keys.*') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v-1l1-1 1-1-1.243-1.243A6 6 0 1118 8zm-6-4a1 1 0 102 0 1 1 0 00-2 0z" clip-rule="evenodd" />
                        </svg>
                        Quản lý API Key
                    </a>
                </div>
            </div>

            <!-- Wiki Q&A Group -->
            <div class="pt-5">
                <h3 class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Trang hỏi đáp
                </h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.wiki.settings') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.wiki.settings') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.wiki.settings') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                        </svg>
                        Cài đặt chung
                    </a>

                    <a href="{{ route('admin.wiki.questions') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.wiki.questions') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.wiki.questions') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        Danh sách câu hỏi
                    </a>

                    <a href="{{ route('admin.wiki.moderation') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.wiki.moderation') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.wiki.moderation') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Nội dung chờ duyệt
                    </a>
                </div>
            </div>

            <!-- Content Management Group -->
            <div class="pt-5">
                <h3 class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Quản lý nội dung
                </h3>
                <div class="mt-2 space-y-1">
                    <!-- Categories -->
                    <button type="button" class="w-full group flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:text-indigo-700 hover:bg-indigo-50" id="categories-menu-button">
                        <svg class="mr-3 h-5 w-5 text-slate-500 group-hover:text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                        </svg>
                        Danh mục
                        <svg class="ml-auto h-5 w-5 transform group-hover:text-indigo-600 transition-transform" viewBox="0 0 20 20" fill="currentColor" id="categories-menu-icon">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div class="space-y-1 pl-10 hidden" id="categories-submenu">
                        <a href="{{ route('admin.categories.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.categories.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Tất cả danh mục</span>
                        </a>
                        <a href="{{ route('admin.categories.create') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.categories.create') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Thêm danh mục mới</span>
                        </a>
                    </div>

                    <!-- Books -->
                    <button type="button" class="w-full group flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:text-indigo-700 hover:bg-indigo-50" id="books-menu-button">
                        <svg class="mr-3 h-5 w-5 text-slate-500 group-hover:text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                        </svg>
                        Sách
                        <svg class="ml-auto h-5 w-5 transform group-hover:text-indigo-600 transition-transform" viewBox="0 0 20 20" fill="currentColor" id="books-menu-icon">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div class="space-y-1 pl-10 hidden" id="books-submenu">
                        <a href="{{ route('admin.books.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.books.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Tất cả sách</span>
                        </a>
                        <a href="{{ route('admin.books.create') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.books.create') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Thêm sách mới</span>
                        </a>
                        <a href="{{ route('admin.bookGroups.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.bookGroups.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Tất cả môn học</span>
                        </a>
                        <a href="{{ route('admin.bookGroups.create') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.bookGroups.create') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Thêm môn học mới</span>
                        </a>
                    </div>

                    <!-- Articles -->
                    <button type="button" class="w-full group flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:text-indigo-700 hover:bg-indigo-50" id="articles-menu-button">
                        <svg class="mr-3 h-5 w-5 text-slate-500 group-hover:text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd" />
                            <path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z" />
                        </svg>
                        Quản lý tin tức
                        <svg class="ml-auto h-5 w-5 transform group-hover:text-indigo-600 transition-transform" viewBox="0 0 20 20" fill="currentColor" id="articles-menu-icon">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div class="space-y-1 pl-10 hidden" id="articles-submenu">
                        <a href="{{ route('admin.articleCategories.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.articleCategories.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Danh mục tin tức</span>
                        </a>
                        <a href="{{ route('admin.articles.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.articles.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Tất cả tin tức</span>
                        </a>
                        <a href="{{ route('admin.articles.create') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.articles.create') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Thêm tin tức mới</span>
                        </a>
                        <a href="{{ route('admin.article-tags.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.article-tags.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                            <span class="truncate">Tags</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Users Group -->
            <div class="pt-5">
                <h3 class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Quản lý thành viên
                </h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.users.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.users.index') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                        </svg>
                        Tất cả thành viên
                    </a>

                    <a href="{{ route('admin.users.create') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.create') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:text-indigo-700 hover:bg-indigo-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.users.create') ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-600' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                        </svg>
                        Thêm thành viên
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Bottom Section with Website Link -->
    <div class="px-3 mt-6 mb-4">
        <a href="{{ route('home') }}" class="flex items-center px-3 py-2 text-sm font-medium text-slate-700 rounded-md hover:bg-indigo-50 hover:text-indigo-700 group">
            <svg class="mr-3 h-5 w-5 text-slate-500 group-hover:text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" />
            </svg>
            Quay lại
        </a>
    </div>
</aside>
<!-- ===== Sidebar End ===== -->

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle submenu functionality for sidebar
            const submenuButtons = [
                { button: document.getElementById('categories-menu-button'), submenu: document.getElementById('categories-submenu'), icon: document.getElementById('categories-menu-icon') },
                { button: document.getElementById('books-menu-button'), submenu: document.getElementById('books-submenu'), icon: document.getElementById('books-menu-icon') },
                { button: document.getElementById('articles-menu-button'), submenu: document.getElementById('articles-submenu'), icon: document.getElementById('articles-menu-icon') }
            ];

            submenuButtons.forEach(function(item) {
                if (item.button && item.submenu && item.icon) {
                    item.button.addEventListener('click', function() {
                        item.submenu.classList.toggle('hidden');
                        item.icon.classList.toggle('rotate-180');
                    });

                    // Auto-expand menu if current route is under this section
                    const activeLink = item.submenu.querySelector('a.bg-indigo-50');
                    if (activeLink) {
                        item.submenu.classList.remove('hidden');
                        item.icon.classList.add('rotate-180');
                    }
                }
            });
        });
    </script>
@endpush
