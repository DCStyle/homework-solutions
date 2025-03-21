<!-- ===== Sidebar Start ===== -->
<aside class="absolute left-0 top-0 z-9999 flex h-screen w-72.5 flex-col overflow-y-hidden bg-[#1c2434] duration-300 ease-linear lg:static lg:translate-x-0 -translate-x-full">
    <!-- SIDEBAR HEADER -->
    <div class="flex items-center justify-center gap-2 px-4 lg:py-6">
        <a href="{{ route('admin.dashboard') }}" class="block text-gray-300 text-2xl font-extralight min-w-[200px]">
            {{ __('Admin Dashboard') }}
        </a>
    </div>
    <!-- SIDEBAR HEADER -->
    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <!-- Sidebar Menu -->
        <nav class="py-4 h-screen flex flex-col">
            <!-- Menu Group -->
            <div>
                <h3 class="mb-4 text-sm font-medium text-[#8a99af] px-6">Menu</h3>
                <ul class="mb-6 flex flex-col gap-1.5">
                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Tổng quan", 'itemIcon' => 'mdi-view-dashboard', 'itemLink' => route('admin.dashboard')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Cài đặt chung", 'itemIcon' => 'mdi-cog', 'itemLink' => route('admin.settings.index')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Cài đặt trang chủ", 'itemIcon' => 'mdi-cog', 'itemLink' => route('admin.settings.home')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Cài đặt menu header", 'itemIcon' => 'mdi-cog', 'itemLink' => route('admin.menu.index')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Cài đặt footer", 'itemIcon' => 'mdi-page-layout-footer', 'itemLink' => route('admin.footer.index')])
                </ul>
            </div>

            <!-- Menu Group -->
            <div class="mt-4">
                <h3 class="mb-4 text-sm font-medium text-[#8a99af] px-6">AI Tools</h3>
                <ul class="mb-6 flex flex-col gap-1.5">
                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Công cụ AI", 'itemIcon' => 'mdi-robot', 'itemLink' => route('admin.ai-dashboard.index')])
                </ul>
            </div>

            <!-- Menu Group -->
            <div class="mt-4">
                <h3 class="mb-4 text-sm font-medium text-[#8a99af] px-6">Danh mục</h3>
                <ul class="mb-6 flex flex-col gap-1.5">
                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Tất cả danh mục", 'itemIcon' => 'mdi-format-list-bulleted', 'itemLink' => route('admin.categories.index')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Tạo danh mục mới", 'itemIcon' => 'mdi-folder-plus', 'itemLink' => route('admin.categories.create')])
                </ul>
            </div>

            <!-- Menu Group -->
            <div class="mt-4">
                <h3 class="mb-4 text-sm font-medium text-[#8a99af] px-6">Sách</h3>
                <ul class="mb-6 flex flex-col gap-1.5">
                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Tất cả sách", 'itemIcon' => 'mdi-book-multiple', 'itemLink' => route('admin.books.index')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Thêm sách", 'itemIcon' => 'mdi-book-plus', 'itemLink' => route('admin.books.create')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Các môn học", 'itemIcon' => 'mdi-book-alphabet', 'itemLink' => route('admin.bookGroups.index')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Thêm môn học mới", 'itemIcon' => 'mdi-book-plus-multiple', 'itemLink' => route('admin.bookGroups.create')])
                </ul>
            </div>

            <!-- Menu Group -->
            <div class="mt-4">
                <h3 class="mb-4 text-sm font-medium text-[#8a99af] px-6">Tin tức</h3>
                <ul class="mb-6 flex flex-col gap-1.5">
                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Tất cả chuyên mục", 'itemIcon' => 'mdi-format-list-bulleted', 'itemLink' => route('admin.articleCategories.index')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Tất cả bài viết", 'itemIcon' => 'mdi-newspaper-variant-multiple', 'itemLink' => route('admin.articles.index')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Thêm bài viết", 'itemIcon' => 'mdi-newspaper-plus', 'itemLink' => route('admin.articles.create')])

                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => "Tags", 'itemIcon' => 'mdi-tag-multiple', 'itemLink' => route('admin.article-tags.index')])
                </ul>
            </div>

            <!-- Menu Group -->
            <div class="mt-auto">
                <ul class="mb-6 flex flex-col gap-1.5 opacity-60">
                    @include('admin_layouts.sidebar_menu_item', ['itemTitle' => __('Home'), 'itemIcon' => 'mdi-arrow-left', 'itemLink' => route('home')])
                </ul>
            </div>
        </nav>
        <!-- Sidebar Menu -->
    </div>
</aside>
<!-- ===== Sidebar End ===== -->
