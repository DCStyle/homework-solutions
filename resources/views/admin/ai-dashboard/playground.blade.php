@extends('admin_layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-3xl font-bold text-primary">Khu vực thử nghiệm</h2>
                <p class="mt-1 text-gray-600">Thử nghiệm với các mô hình AI và thiết lập prompt</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ai-dashboard.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-stroke py-2 px-4 text-center font-medium text-black hover:bg-gray-50 sm:px-6">
                    <span class="iconify" data-icon="mdi-arrow-left"></span>
                    Quay Lại Dashboard
                </a>
                <button id="save-prompt-btn" class="inline-flex items-center justify-center gap-2.5 rounded-md bg-primary py-2 px-4 text-center font-medium text-white hover:opacity-90 sm:px-6">
                    <span class="iconify" data-icon="mdi-content-save-outline"></span>
                    Lưu Prompt
                </button>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- Selector Panel -->
            <div class="col-span-12 lg:col-span-8">
                <div class="rounded-xl border border-indigo-100 bg-white p-6 shadow-lg">
                    <h3 class="mb-5 text-xl font-semibold text-indigo-700 flex items-center">
                        <span class="iconify text-2xl mr-2" data-icon="mdi-file-document-edit-outline"></span>
                        Lựa Chọn Nội Dung
                    </h3>

                    <div class="space-y-5">
                        <!-- Content Type Selector with Visual Buttons -->
                        <div>
                            <label class="mb-3 block text-sm font-medium text-gray-700">Loại Nội Dung</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button type="button" id="content-type-posts" class="content-type-btn flex flex-col items-center justify-center rounded-lg border-2 border-transparent bg-gray-50 px-3 py-3 transition hover:bg-indigo-50 hover:border-indigo-200" data-type="posts">
                                    <span class="iconify text-2xl mb-1 text-indigo-600" data-icon="mdi-file-document-outline"></span>
                                    <span class="text-sm font-medium">Bài Viết</span>
                                </button>
                                <button type="button" id="content-type-chapters" class="content-type-btn flex flex-col items-center justify-center rounded-lg border-2 border-indigo-300 bg-indigo-50 px-3 py-3 transition hover:bg-indigo-50 hover:border-indigo-200" data-type="chapters">
                                    <span class="iconify text-2xl mb-1 text-indigo-600" data-icon="mdi-book-open-variant"></span>
                                    <span class="text-sm font-medium">Chương Sách</span>
                                </button>
                                <button type="button" id="content-type-books" class="content-type-btn flex flex-col items-center justify-center rounded-lg border-2 border-transparent bg-gray-50 px-3 py-3 transition hover:bg-indigo-50 hover:border-indigo-200" data-type="books">
                                    <span class="iconify text-2xl mb-1 text-indigo-600" data-icon="mdi-book-outline"></span>
                                    <span class="text-sm font-medium">Sách</span>
                                </button>
                                <button type="button" id="content-type-book_groups" class="content-type-btn flex flex-col items-center justify-center rounded-lg border-2 border-transparent bg-gray-50 px-3 py-3 transition hover:bg-indigo-50 hover:border-indigo-200" data-type="book_groups">
                                    <span class="iconify text-2xl mb-1 text-indigo-600" data-icon="mdi-bookshelf"></span>
                                    <span class="text-sm font-medium">Nhóm Sách</span>
                                </button>
                            </div>
                            <!-- Hidden original select -->
                            <select id="content-type" class="hidden">
                                <option value="posts">Bài Viết</option>
                                <option value="chapters" selected>Chương Sách</option>
                                <option value="books">Sách</option>
                                <option value="book_groups">Nhóm Sách</option>
                            </select>
                        </div>

                        <!-- Smart Search with Autocomplete -->
                        <div>
                            <label for="content-search" class="mb-3 block text-sm font-medium text-gray-700">Tìm Kiếm Nhanh</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="iconify text-gray-500" data-icon="mdi-magnify"></span>
                                </div>
                                <input id="content-search" type="text" class="w-full rounded-lg border border-gray-300 pl-10 pr-12 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" placeholder="Nhập từ khóa để tìm kiếm nhanh...">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button type="button" id="search-clear" class="text-gray-400 hover:text-gray-600">
                                        <span class="iconify" data-icon="mdi-close-circle"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-1 text-xs text-gray-500 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                Tìm kiếm theo tên, tiêu đề hoặc phần nội dung
                            </div>
                        </div>

                        <!-- Content Browser with Category Structure -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700">Chọn Nội Dung</label>
                                <span id="content-count" class="text-xs font-medium px-2 py-1 rounded-full bg-indigo-100 text-indigo-700">0 mục</span>
                            </div>

                            <!-- Hierarchical Selectors Container -->
                            <div id="hierarchical-selectors" class="space-y-3">
                                <!-- Category Selector (Always visible) -->
                                <div class="selector-container" id="category-selector-container">
                                    <label class="mb-1 text-xs font-medium text-gray-600">Danh Mục</label>
                                    <div class="relative">
                                        <select id="category-selector" class="w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                            <option value="">-- Chọn danh mục --</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Book Group Selector (Visible for all) -->
                                <div class="selector-container" id="group-selector-container" style="display:none">
                                    <label class="mb-1 text-xs font-medium text-gray-600">Nhóm Sách</label>
                                    <div class="relative">
                                        <select id="group-selector" class="w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                            <option value="">-- Chọn nhóm sách --</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Book Selector (For Posts, Chapters, Books) -->
                                <div class="selector-container" id="book-selector-container" style="display:none">
                                    <label class="mb-1 text-xs font-medium text-gray-600">Sách</label>
                                    <div class="relative">
                                        <select id="book-selector" class="w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                            <option value="">-- Chọn sách --</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Chapter Selector (For Posts, Chapters) -->
                                <div class="selector-container" id="chapter-selector-container" style="display:none">
                                    <label class="mb-1 text-xs font-medium text-gray-600">Chương Sách</label>
                                    <div class="relative">
                                        <select id="chapter-selector" class="w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                            <option value="">-- Chọn chương --</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Content Selector (Final selection based on content type) -->
                                <div class="selector-container" id="content-selector-container" style="display:none">
                                    <label class="mb-1 text-xs font-medium text-gray-600" id="content-selector-label">Nội Dung</label>
                                    <div class="relative">
                                        <select id="content-final-selector" class="w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                            <option value="">-- Chọn nội dung --</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-2 flex items-center">
                                            <div id="content-selector-spinner" class="spinner-border spinner-border-sm text-indigo-500" role="status" style="display:none">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Search Filter for Final Selector -->
                            <div class="mt-2 relative" id="quick-filter-container" style="display:none">
                                <input type="text" id="quick-filter" placeholder="Lọc nhanh..." class="w-full rounded-lg border border-gray-300 bg-white py-1.5 px-3 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                <div class="absolute inset-y-0 right-2 flex items-center">
                                    <button type="button" id="clear-filter" class="text-gray-400 hover:text-indigo-500">
                                        <span class="iconify" data-icon="mdi-close-circle-outline"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-2 flex justify-between text-xs text-gray-500 px-2">
                                <span class="flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-filter-variant"></span>
                                    <span id="filter-status">Chọn danh mục để bắt đầu</span>
                                </span>
                                <button id="refresh-hierarchical" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-refresh"></span>
                                    Làm mới
                                </button>
                            </div>
                        </div>

                        <!-- Content Details Card -->
                        <div id="content-details-card" class="rounded-xl border border-indigo-200 bg-white p-5 shadow-sm transition-all">
                            <div id="content-details-placeholder" class="flex flex-col items-center justify-center py-8 text-center">
                                <div class="mb-3 rounded-full bg-indigo-100 p-3">
                                    <span class="iconify text-3xl text-indigo-600" data-icon="mdi-file-document-outline"></span>
                                </div>
                                <h5 class="mb-1 text-lg font-medium text-gray-700">Chưa chọn nội dung</h5>
                                <p class="text-sm text-gray-500">Vui lòng chọn một mục nội dung để xem chi tiết</p>
                            </div>

                            <div id="content-details" class="hidden">
                                <div class="mb-4 pb-4 border-b border-gray-100">
                                    <div class="flex justify-between items-start">
                                        <h5 id="content-title" class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">Tiêu đề nội dung</h5>
                                        <span id="content-badge" class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Đã có meta</span>
                                    </div>
                                    <p id="content-path" class="text-sm text-gray-500 line-clamp-2">
                                        <span class="path-item">Danh mục</span> &raquo;
                                        <span class="path-item">Nhóm</span> &raquo;
                                        <span class="path-item">Sách</span>
                                    </p>
                                </div>

                                <div class="space-y-3">
                                    <div class="content-meta-item">
                                        <div class="flex items-center mb-1">
                                            <span class="iconify text-indigo-600 mr-1" data-icon="mdi-text-box-outline"></span>
                                            <span class="text-sm font-medium text-gray-700" id="meta-title-label">Tiêu đề Meta</span>
                                        </div>
                                        <div id="meta-title-value" class="px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-600 break-words">
                                            <em class="text-gray-400">Chưa có tiêu đề meta</em>
                                        </div>
                                    </div>

                                    <div class="content-meta-item">
                                        <div class="flex items-center mb-1">
                                            <span class="iconify text-indigo-600 mr-1" data-icon="mdi-text-box-multiple-outline"></span>
                                            <span class="text-sm font-medium text-gray-700" id="meta-desc-label">Mô tả Meta</span>
                                        </div>
                                        <div id="meta-desc-value" class="px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-600 break-words">
                                            <em class="text-gray-400">Chưa có mô tả meta</em>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                                    <a id="edit-content-link" href="#" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                        <span class="iconify mr-1" data-icon="mdi-pencil"></span>
                                        Chỉnh sửa trực tiếp
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration & result panel -->
            <div class="col-span-12 lg:col-span-4">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Configuration -->
                    <div class="rounded-lg border border-stroke bg-white p-6 shadow-lg">
                        <h3 class="mb-4 text-lg font-semibold text-primary">Cài Đặt AI</h3>

                        <div class="space-y-4">
                            <!-- Model Selector -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="model" class="mb-2.5 block text-sm font-medium text-black">Mô Hình AI</label>
                                    <div class="relative z-20 bg-white">
                                        <select data-plugin-select2 id="model" class="relative z-20 w-full appearance-none rounded-lg border border-stroke bg-transparent py-2 px-4 outline-none transition focus:border-primary active:border-primary">
                                            <optgroup label="Grok Models">
                                                <option value="grok-2">Grok-2 (Mặc định)</option>
                                                <option value="grok-2-latest">Grok-2 Mới Nhất</option>
                                                <option value="grok-2-1212">Grok-2 1212 (Tối ưu)</option>
                                            </optgroup>
                                            <optgroup label="DeepSeek Models">
                                                <option value="deepseek-v3">DeepSeek Chat</option>
                                            </optgroup>
                                        </select>
                                        <span class="absolute top-1/2 right-4 z-10 -translate-y-1/2">
                                            <span class="iconify" data-icon="mdi-chevron-down"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- System Message (for DeepSeek) -->
                                <div id="system-message-container" class="hidden">
                                    <label for="system-message" class="mb-2.5 block text-sm font-medium text-black">Thông Điệp Hệ Thống</label>
                                    <textarea id="system-message" rows="2" class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"></textarea>
                                </div>
                            </div>

                            <!-- Parameter -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="temperature" class="mb-2.5 block text-sm font-medium text-black">Nhiệt Độ</label>
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="range"
                                            id="temperature"
                                            min="0"
                                            max="1"
                                            step="0.1"
                                            value="0.7"
                                            class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200"
                                        >
                                        <span id="temperature-value" class="w-10 text-right text-sm">0.7</span>
                                    </div>
                                </div>

                                <div>
                                    <label for="max-tokens" class="mb-2.5 block text-sm font-medium text-black">Số Token Tối Đa</label>
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="range"
                                            id="max-tokens"
                                            min="100"
                                            max="2000"
                                            step="100"
                                            value="1000"
                                            class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200"
                                        >
                                        <span id="max-tokens-value" class="w-14 text-right text-sm">1000</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="use-html-meta" class="mb-2.5 block text-sm font-medium text-black">HTML cho Mô Tả Meta</label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        id="use-html-meta"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <label for="use-html-meta" class="text-sm text-gray-600">
                                        Sử dụng định dạng HTML cho mô tả meta
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Phù hợp cho TinyMCE và hiển thị trực tiếp trên frontend</p>
                            </div>

                            <!-- Prompt Editor -->
                            <div class="mb-4">
                                <label for="saved-prompts" class="mb-2.5 block text-sm font-medium text-black">Lựa Chọn Prompt Đã Lưu</label>
                                <div class="relative z-20 bg-white">
                                    <select data-plugin-select2 id="saved-prompts" class="relative z-20 w-full appearance-none rounded-lg border border-stroke bg-transparent py-2 px-4 outline-none transition focus:border-primary active:border-primary">
                                        <option value="">-- Chọn prompt đã lưu --</option>
                                        <!-- Prompts will be loaded here dynamically -->
                                    </select>
                                    <span class="absolute top-1/2 right-4 z-10 -translate-y-1/2">
                                        <span class="iconify" data-icon="mdi-chevron-down"></span>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label for="prompt" class="mb-2.5 block text-sm font-medium text-black">Prompt</label>
                                <textarea id="prompt" rows="8" class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"></textarea>
                                <div class="mt-2">
                                    <small class="text-xs text-gray-500">Biến khả dụng: @verbatim{{title}}, {{name}}, {{chapter_name}}, {{book_name}}, {{group_name}}, {{category_name}}@endverbatim</small>
                                </div>
                            </div>

                            <!-- Submit buttons -->
                            <div class="flex items-center justify-end space-x-4">
                                <button id="reset-prompt-btn" class="flex items-center justify-center rounded-lg border border-stroke px-6 py-2 font-medium text-black hover:shadow-lg transition duration-300">
                                    <span class="mr-2">
                                        <span class="iconify" data-icon="mdi-refresh"></span>
                                    </span>
                                    Đặt Lại
                                </button>
                                <button id="generate-btn" class="flex items-center justify-center rounded-lg bg-primary px-6 py-2 font-medium text-white hover:opacity-90 transition duration-300">
                                    <span class="mr-2">
                                        <span class="iconify" data-icon="mdi-lightning-bolt"></span>
                                    </span>
                                    Tạo Nội Dung
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Results -->
                    <div id="results-container" class="hidden rounded-lg border border-stroke bg-white p-6 shadow-lg">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-primary">Kết Quả Đã Tạo</h3>
                            <div id="loading-indicator" class="hidden">
                                <div class="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
                            </div>
                        </div>

                        <div id="results" class="rounded-lg border border-stroke bg-gray-50 p-4">
                            <!-- Results will be displayed here -->
                        </div>

                        <div class="mt-4 flex items-center justify-end space-x-4">
                            <button id="apply-btn" class="flex items-center justify-center rounded-lg bg-success px-6 py-2 font-medium text-white hover:opacity-90 transition duration-300">
                                <span class="mr-2">
                                    <span class="iconify" data-icon="mdi-check"></span>
                                </span>
                                Áp Dụng Thay Đổi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prompt saving modal -->
    <div class="fixed top-0 left-0 z-50 flex h-full min-h-screen w-full items-center justify-center bg-black/50 px-4 py-5 hidden" id="save-prompt-modal">
        <div class="w-full max-w-xl rounded-lg bg-white shadow-xl">
            <div class="border-b border-stroke px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-black">
                        Lưu Prompt
                    </h3>
                    <button type="button" class="close-modal text-gray-500 hover:text-black">
                        <span class="iconify" data-icon="mdi-close"></span>
                    </button>
                </div>
            </div>
            <div class="px-6 py-4">
                <div class="mb-4">
                    <label for="prompt-name" class="mb-2.5 block text-sm font-medium text-black">Tên Prompt</label>
                    <input type="text" id="prompt-name" class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" placeholder="Nhập tên cho prompt này" required>
                </div>

                <div class="mb-4">
                    <label for="prompt-description" class="mb-2.5 block text-sm font-medium text-black">Mô Tả (Tùy chọn)</label>
                    <textarea id="prompt-description" rows="2" class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" placeholder="Thêm mô tả ngắn"></textarea>
                </div>

                <div id="save-system-message-container" class="mb-4 hidden">
                    <label for="save-system-message" class="mb-2.5 block text-sm font-medium text-black">Thông Điệp Hệ Thống</label>
                    <textarea id="save-system-message" rows="2" class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-2 px-5 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"></textarea>
                </div>
            </div>
            <div class="border-t border-stroke px-6 py-4">
                <div class="flex items-center justify-end gap-3">
                    <button type="button" class="close-modal flex items-center justify-center rounded-md border border-stroke py-2 px-6 font-medium text-black hover:shadow-lg transition duration-300">
                        Hủy
                    </button>
                    <button type="button" id="save-prompt-confirm-btn" class="flex items-center justify-center rounded-md bg-primary py-2 px-6 font-medium text-white hover:opacity-90 transition duration-300">
                        Lưu Prompt
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/ai-dashboard/playground.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/admin/ai-dashboard/playground_main.js') }}"></script>
    <script src="{{ asset('js/admin/ai-dashboard/playground_prompt_selector.js') }}"></script>
@endpush
