@extends('admin_layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-indigo-50 p-6 rounded-xl">
            <div>
                <h2 class="text-3xl font-bold text-indigo-800">Phân Tích Nội Dung SEO</h2>
                <p class="mt-1 text-indigo-600">Tìm và tối ưu hóa nội dung thiếu thông tin SEO</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ai-dashboard.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-indigo-300 py-2 px-4 text-center font-medium text-indigo-700 hover:bg-indigo-100 sm:px-6">
                    <span class="iconify" data-icon="mdi-arrow-left"></span>
                    Quay Lại Trang Chính
                </a>
                <a href="{{ route('admin.ai-history.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-indigo-300 py-2 px-4 text-center font-medium text-indigo-700 hover:bg-indigo-100 sm:px-6">
                    <span class="iconify" data-icon="mdi-history"></span>
                    Lịch Sử Tạo AI
                </a>
                <button id="bulk-generate-btn" class="inline-flex items-center justify-center gap-2.5 rounded-md bg-indigo-600 py-2 px-4 text-center font-medium text-white hover:bg-indigo-700 sm:px-6">
                    <span class="iconify" data-icon="mdi-plus"></span>
                    Tạo Hàng Loạt
                </button>
            </div>
        </div>

        <!-- Content Filter Panel -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-md">
            <div class="mb-4">
                <h4 class="text-xl font-semibold text-gray-800">Bộ Lọc Nội Dung</h4>
                <p class="mt-1 text-sm text-gray-600">Chọn nội dung để tối ưu hóa SEO</p>
            </div>

            <!-- Content Type Selector with Visual Buttons -->
            <div class="mb-5">
                <label class="mb-3 block text-sm font-medium text-gray-700">Loại Nội Dung</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button type="button" class="content-type-btn flex flex-col items-center justify-center rounded-lg border-2 border-transparent bg-gray-50 px-3 py-3 transition hover:bg-indigo-50 hover:border-indigo-200" data-type="posts">
                        <span class="iconify text-2xl mb-1 text-indigo-600" data-icon="mdi-file-document-outline"></span>
                        <span class="text-sm font-medium">Bài Viết</span>
                    </button>
                    <button type="button" class="content-type-btn flex flex-col items-center justify-center rounded-lg border-2 border-transparent bg-gray-50 px-3 py-3 transition hover:bg-indigo-50 hover:border-indigo-200" data-type="chapters">
                        <span class="iconify text-2xl mb-1 text-indigo-600" data-icon="mdi-book-open-variant"></span>
                        <span class="text-sm font-medium">Chương Sách</span>
                    </button>
                    <button type="button" class="content-type-btn flex flex-col items-center justify-center rounded-lg border-2 border-transparent bg-gray-50 px-3 py-3 transition hover:bg-indigo-50 hover:border-indigo-200" data-type="books">
                        <span class="iconify text-2xl mb-1 text-indigo-600" data-icon="mdi-book-outline"></span>
                        <span class="text-sm font-medium">Sách</span>
                    </button>
                    <button type="button" class="content-type-btn flex flex-col items-center justify-center rounded-lg border-2 border-transparent bg-gray-50 px-3 py-3 transition hover:bg-indigo-50 hover:border-indigo-200" data-type="book_groups">
                        <span class="iconify text-2xl mb-1 text-indigo-600" data-icon="mdi-bookshelf"></span>
                        <span class="text-sm font-medium">Nhóm Sách</span>
                    </button>
                </div>
                <!-- Hidden input to store current content type -->
                <input type="hidden" id="content-type" name="content_type" value="{{ $type }}">
            </div>

            <!-- Hierarchical Selectors -->
            <div id="hierarchical-selectors" class="space-y-3 mb-5">
                <!-- Category Selector (Always visible) -->
                <div class="selector-container" id="category-selector-container">
                    <label class="mb-1 text-sm font-medium text-gray-700">Danh Mục</label>
                    <div class="relative">
                        <select id="category-selector" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <option value="">-- Chọn danh mục --</option>
                        </select>
                    </div>
                </div>

                <!-- Book Group Selector (Visible for all) -->
                <div class="selector-container" id="group-selector-container" style="display:none">
                    <label class="mb-1 text-sm font-medium text-gray-700">Nhóm Sách</label>
                    <div class="relative">
                        <select id="group-selector" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <option value="">-- Chọn nhóm sách --</option>
                        </select>
                    </div>
                </div>

                <!-- Book Selector (For Posts, Chapters, Books) -->
                <div class="selector-container" id="book-selector-container" style="display:none">
                    <label class="mb-1 text-sm font-medium text-gray-700">Sách</label>
                    <div class="relative">
                        <select id="book-selector" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <option value="">-- Chọn sách --</option>
                        </select>
                    </div>
                </div>

                <!-- Chapter Selector (For Posts, Chapters) -->
                <div class="selector-container" id="chapter-selector-container" style="display:none">
                    <label class="mb-1 text-sm font-medium text-gray-700">Chương Sách</label>
                    <div class="relative">
                        <select id="chapter-selector" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <option value="">-- Chọn chương --</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Additional Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <!-- SEO Status Filter -->
                <div>
                    <label for="seo-status" class="mb-1 text-sm font-medium text-gray-700">Tình Trạng SEO</label>
                    <select id="seo-status" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2 px-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                        <option value="missing">Thiếu Dữ Liệu SEO</option>
                        <option value="all">Tất Cả</option>
                        <option value="complete">Đã Có Dữ Liệu SEO</option>
                    </select>
                </div>

                <!-- Search Filter -->
                <div>
                    <label for="search-input" class="mb-1 text-sm font-medium text-gray-700">Tìm Kiếm</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="search-input"
                            placeholder="Tìm kiếm tên, tiêu đề..."
                            class="form-control w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200"
                        >
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <span class="iconify text-gray-500" data-icon="mdi-magnify"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                <div class="flex items-center text-sm text-gray-600">
                    <span id="filter-status">Chọn các bộ lọc để hiển thị kết quả</span>
                    <span class="mx-2">•</span>
                    <span id="content-count">0 mục</span>
                </div>
                <div class="flex gap-2">
                    <button id="reset-filter-btn" class="btn btn-outline-secondary">
                        <span class="iconify mr-1" data-icon="mdi-refresh"></span>
                        Đặt Lại
                    </button>
                    <button id="apply-filter-btn" class="btn btn-primary">
                        <span class="iconify mr-1" data-icon="mdi-filter"></span>
                        Áp Dụng Bộ Lọc
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loading-indicator" class="flex justify-center py-8 hidden">
            <div class="flex flex-col items-center">
                <div class="spinner-border text-indigo-600" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="mt-2 text-gray-600">Đang tải dữ liệu...</p>
            </div>
        </div>

        <!-- Content Table -->
        <div id="content-table-container" class="rounded-lg border border-gray-200 bg-white p-6 shadow-md">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h4 class="text-xl font-semibold text-gray-800" id="table-title">
                        Nội Dung Thiếu Dữ Liệu SEO
                    </h4>
                    <p class="mt-1 text-sm text-gray-600" id="total-count">Đang tải...</p>
                </div>
                <div>
                    <select id="limit-select" class="form-select w-full rounded-lg border border-gray-300 bg-transparent py-2 px-5 font-medium outline-none transition focus:border-indigo-600">
                        <option value="10">10 mục/trang</option>
                        <option value="25">25 mục/trang</option>
                        <option value="50" selected>50 mục/trang</option>
                        <option value="100">100 mục/trang</option>
                    </select>
                </div>
            </div>

            <!-- Select All Controls -->
            <div class="mb-4 flex items-center space-x-2 bg-blue-50 p-3 rounded-lg">
                <div class="flex h-5 items-center">
                    <input id="select-all" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                </div>
                <label for="select-all" class="ml-2 text-sm font-medium text-gray-700">Chọn Tất Cả</label>
                <span id="selected-count" class="ml-4 text-sm text-indigo-600 font-medium">0 đã chọn</span>
            </div>

            <div class="max-w-full overflow-x-auto">
                <table id="content-table" class="w-full table-auto">
                    <thead>
                    <tr class="bg-indigo-50 text-left">
                        <th class="py-4 px-4 font-medium text-indigo-700">
                            <span class="sr-only">Chọn</span>
                        </th>
                        <th class="py-4 px-4 font-medium text-indigo-700" id="col-title">
                            Tiêu Đề / Tên
                        </th>
                        <th class="py-4 px-4 font-medium text-indigo-700" id="col-parent">
                            Đường Dẫn
                        </th>
                        <th class="py-4 px-4 font-medium text-indigo-700" id="col-status">
                            Trạng Thái
                        </th>
                        <th class="py-4 px-4 font-medium text-indigo-700">Hành Động</th>
                    </tr>
                    </thead>
                    <tbody id="content-tbody">
                    <!-- Content will be loaded here dynamically -->
                    <tr id="empty-row">
                        <td colspan="5" class="border-b border-gray-200 py-5 px-4 text-center">
                            <p class="text-gray-500">Hãy chọn bộ lọc và nhấn "Áp Dụng Bộ Lọc" để xem kết quả</p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="pagination-container" class="mt-6">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be loaded here dynamically -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Generate Single Modal -->
    <div class="modal fade" id="generate-single-modal" tabindex="-1" aria-labelledby="generateSingleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-indigo-50">
                    <h5 class="modal-title text-indigo-800 font-medium" id="generateSingleModalLabel">Tạo Nội Dung</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal-content-id" value="">
                    <input type="hidden" id="modal-content-type" value="">

                    <div class="mb-4">
                        <div class="text-center">
                            <h5 class="mb-1 text-lg font-semibold text-gray-800" id="modal-content-title"></h5>
                            <p class="text-sm text-gray-600">Sử dụng AI để tạo nội dung SEO</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="modal-model" class="mb-2.5 block font-medium text-gray-700">Mô Hình AI</label>
                        <div class="relative bg-white">
                            <select data-plugin-select2 id="modal-model" class="form-select w-full rounded border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
                                <optgroup label="Grok Models">
                                    <option value="grok-2">Grok-2</option>
                                    <option value="grok-2-1212">Grok-2 1212</option>
                                    <option value="grok-2-mini">Grok-2 Mini</option>
                                    <option value="grok-2-vision">Grok-2 Vision</option>
                                </optgroup>
                                <optgroup label="DeepSeek Models">
                                    <option value="deepseek-v3">DeepSeek Chat</option>
                                    <option value="deepseek-r1">DeepSeek R1</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="modal-prompt-source" class="mb-2.5 block font-medium text-gray-700">Lời Nhắc (Prompt)</label>
                        <div class="relative bg-white">
                            <select data-plugin-select2 id="modal-prompt-source" class="form-select w-full rounded border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
                                <option value="default">Lời Nhắc Mặc Định</option>
                                <option value="custom">Lời Nhắc Tùy Chỉnh</option>
                                <option value="saved">Lời Nhắc Đã Lưu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Saved Prompts Dropdown (Hidden by default) -->
                    <div id="modal-saved-prompts-container" class="mb-4 d-none">
                        <label for="modal-saved-prompt" class="mb-2.5 block font-medium text-gray-700">Chọn Lời Nhắc Đã Lưu</label>
                        <div class="relative bg-white">
                            <select data-plugin-select2 id="modal-saved-prompt" class="form-select w-full rounded border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
                                <option value="">Chọn lời nhắc</option>
                                @foreach($prompts as $prompt)
                                    <option value="{{ $prompt->id }}" data-prompt="{{ $prompt->prompt_text }}" data-system-message="{{ $prompt->system_message }}">
                                        {{ $prompt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- System Message for DeepSeek (Hidden by default) -->
                    <div id="modal-system-message-container" class="mb-4 d-none">
                        <label for="modal-system-message" class="mb-2.5 block font-medium text-gray-700">Thông Điệp Hệ Thống (cho DeepSeek)</label>
                        <textarea id="modal-system-message" rows="3" class="form-control w-full rounded-lg border border-gray-300 bg-transparent py-3 px-5 font-medium outline-none transition focus:border-indigo-600"></textarea>
                    </div>

                    <!-- Custom Prompt Editor (Hidden by default) -->
                    <div id="modal-prompt-editor" class="mb-4 d-none">
                        <label for="modal-prompt" class="mb-2.5 block font-medium text-gray-700">Lời Nhắc Tùy Chỉnh</label>
                        <textarea id="modal-prompt" rows="6" class="form-control w-full rounded-lg border border-gray-300 bg-transparent py-3 px-5 font-medium outline-none transition focus:border-indigo-600"></textarea>
                        <div class="mt-2 text-xs text-gray-500">
                            Biến có sẵn: @verbatim{{title}}, {{name}}, {{chapter_name}}, {{book_name}}, {{group_name}}, {{category_name}}@endverbatim
                        </div>
                    </div>

                    <!-- Model Parameters -->
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label for="modal-temperature" class="mb-2.5 block font-medium text-gray-700">Nhiệt Độ</label>
                            <div class="flex items-center gap-3">
                                <input type="range" id="modal-temperature" min="0" max="1" step="0.1" value="0.7" class="form-range w-full cursor-pointer h-6">
                                <span id="modal-temperature-value" class="w-10 text-right text-sm font-medium">0.7</span>
                            </div>
                        </div>
                        <div>
                            <label for="modal-max-tokens" class="mb-2.5 block font-medium text-gray-700">Token Tối Đa</label>
                            <div class="flex items-center gap-3">
                                <input type="range" id="modal-max-tokens" min="100" max="4096" step="100" value="1000" class="form-range w-full cursor-pointer h-6">
                                <span id="modal-max-tokens-value" class="w-14 text-right text-sm font-medium">1000</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="mb-2.5 block font-medium text-gray-700">HTML cho Mô Tả Meta</label>
                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                id="modal-use-html-meta"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <label for="modal-use-html-meta" class="text-sm text-gray-600">
                                Sử dụng định dạng HTML cho mô tả meta
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Phù hợp cho TinyMCE và hiển thị trực tiếp trên frontend</p>
                    </div>

                    <!-- Results Preview (Hidden initially) -->
                    <div id="modal-results-container" class="mb-4 d-none">
                        <div class="mb-2 flex items-center justify-between">
                            <h5 class="text-lg font-semibold text-gray-800">Xem Trước Kết Quả</h5>
                            <div id="modal-loading" class="d-none">
                                <div class="spinner-border text-indigo-600" role="status">
                                    <span class="visually-hidden">Đang tải...</span>
                                </div>
                            </div>
                        </div>
                        <div id="modal-results" class="rounded-lg border border-gray-300 bg-gray-50 p-4"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Hủy
                    </button>
                    <button type="button" id="modal-generate-btn" class="btn btn-indigo bg-indigo-600 text-white hover:bg-indigo-700">
                        Tạo Nội Dung
                    </button>
                    <button type="button" id="modal-apply-btn" class="btn btn-success d-none">
                        Áp Dụng Thay Đổi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Generate Modal -->
    <div class="modal fade" id="bulk-generate-modal" tabindex="-1" aria-labelledby="bulkGenerateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-indigo-50">
                    <h5 class="modal-title text-indigo-800 font-medium" id="bulkGenerateModalLabel">Tạo Nội Dung Hàng Loạt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <span class="iconify me-2" data-icon="mdi-alert"></span>
                            <p id="bulk-selected-count">Chưa chọn mục nào</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bulk-model" class="mb-2.5 block font-medium text-gray-700">Mô Hình AI</label>
                        <div class="relative bg-white">
                            <select data-plugin-select2 id="bulk-model" class="form-select w-full rounded border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
                                <optgroup label="Grok Models">
                                    <option value="grok-2">Grok-2</option>
                                    <option value="grok-2-1212">Grok-2 1212</option>
                                    <option value="grok-2-mini">Grok-2 Mini</option>
                                    <option value="grok-2-vision">Grok-2 Vision</option>
                                </optgroup>
                                <optgroup label="DeepSeek Models">
                                    <option value="deepseek-v3">DeepSeek Chat</option>
                                    <option value="deepseek-r1">DeepSeek R1</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bulk-prompt-source" class="mb-2.5 block font-medium text-gray-700">Prompt</label>
                        <div class="relative bg-white">
                            <select data-plugin-select2 id="bulk-prompt-source" class="form-select w-full rounded border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
                                <option value="default">Prompt Mặc Định</option>
                                <option value="custom">Prompt Tùy Chỉnh</option>
                                <option value="saved">Prompt Đã Lưu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Saved Prompts Dropdown (Hidden by default) -->
                    <div id="bulk-saved-prompts-container" class="mb-4 d-none">
                        <label for="bulk-saved-prompt" class="mb-2.5 block font-medium text-gray-700">Chọn Prompt Đã Lưu</label>
                        <div class="relative bg-white">
                            <select id="bulk-saved-prompt" class="form-select w-full rounded border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
                                <option value="">Chọn Prompt</option>
                                @foreach($prompts as $prompt)
                                    <option value="{{ $prompt->id }}" data-prompt="{{ $prompt->prompt_text }}" data-system-message="{{ $prompt->system_message }}">
                                        {{ $prompt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- System Message for DeepSeek (Hidden by default) -->
                    <div id="bulk-system-message-container" class="mb-4 d-none">
                        <label for="bulk-system-message" class="mb-2.5 block font-medium text-gray-700">System Message (cho DeepSeek)</label>
                        <textarea id="bulk-system-message" rows="3" class="form-control w-full rounded-lg border border-gray-300 bg-transparent py-3 px-5 font-medium outline-none transition focus:border-indigo-600"></textarea>
                    </div>

                    <!-- Custom Prompt Editor (Hidden by default) -->
                    <div id="bulk-prompt-editor" class="mb-4 d-none">
                        <label for="bulk-prompt" class="mb-2.5 block font-medium text-gray-700">Prompt Tùy Chỉnh</label>
                        <textarea id="bulk-prompt" rows="6" class="form-control w-full rounded-lg border border-gray-300 bg-transparent py-3 px-5 font-medium outline-none transition focus:border-indigo-600"></textarea>
                        <div class="mt-2 text-xs text-gray-500">
                            Biến có sẵn: @verbatim{{title}}, {{name}}, {{chapter_name}}, {{book_name}}, {{group_name}}, {{category_name}}@endverbatim
                        </div>
                    </div>

                    <!-- Model Parameters -->
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label for="bulk-temperature" class="mb-2.5 block font-medium text-gray-700">Nhiệt Độ</label>
                            <div class="flex items-center gap-3">
                                <input type="range" id="bulk-temperature" min="0" max="1" step="0.1" value="0.7" class="form-range w-full cursor-pointer h-6">
                                <span id="bulk-temperature-value" class="w-10 text-right text-sm font-medium">0.7</span>
                            </div>
                        </div>
                        <div>
                            <label for="bulk-max-tokens" class="mb-2.5 block font-medium text-gray-700">Token Tối Đa</label>
                            <div class="flex items-center gap-3">
                                <input type="range" id="bulk-max-tokens" min="100" max="4096" step="100" value="1000" class="form-range w-full cursor-pointer h-6">
                                <span id="bulk-max-tokens-value" class="w-14 text-right text-sm font-medium">1000</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="mb-2.5 block font-medium text-gray-700">HTML cho Mô Tả Meta</label>
                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                id="bulk-use-html-meta"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <label for="bulk-use-html-meta" class="text-sm text-gray-600">
                                Sử dụng định dạng HTML cho mô tả meta
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Phù hợp cho TinyMCE và hiển thị trực tiếp trên frontend</p>
                    </div>

                    <!-- Progress (Hidden initially) -->
                    <div id="bulk-progress-container" class="mb-4 d-none">
                        <div class="mb-2 flex items-center justify-between">
                            <h5 class="text-lg font-semibold text-gray-800">Tiến Trình</h5>
                            <div id="bulk-progress-percentage" class="text-sm font-medium">0%</div>
                        </div>
                        <div class="progress">
                            <div id="bulk-progress-bar" class="progress-bar bg-indigo-600" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="mt-2 text-center text-sm text-gray-500">
                            <span id="bulk-processed">0</span> / <span id="bulk-total">0</span> mục đã xử lý
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Hủy
                    </button>
                    <button type="button" id="bulk-generate-start-btn" class="btn btn-indigo bg-indigo-600 text-white hover:bg-indigo-700">
                        Bắt Đầu Tạo
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/ai-dashboard/stats_main.css') }}">
@endpush

@push('scripts')
    <script>
        if (typeof showNotification !== 'function') {
            window.showNotification = function(options) {
                const { title, message, type = 'info', duration = 5000 } = options;
                
                // Create toast container if it doesn't exist
                let toastContainer = document.getElementById('toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toast-container';
                    toastContainer.style.cssText = 'position:fixed; top:20px; right:20px; z-index:9999;';
                    document.body.appendChild(toastContainer);
                }
                
                // Create toast element
                const toast = document.createElement('div');
                toast.style.cssText = 'min-width:250px; margin-bottom:10px; padding:15px; border-radius:4px; box-shadow:0 2px 10px rgba(0,0,0,0.2); animation:fadeIn 0.5s;';
                
                // Set background color based on type
                const colors = {
                    success: { bg: '#4caf50', text: '#fff' },
                    error: { bg: '#f44336', text: '#fff' },
                    warning: { bg: '#ff9800', text: '#fff' },
                    info: { bg: '#2196f3', text: '#fff' }
                };
                
                const color = colors[type] || colors.info;
                toast.style.backgroundColor = color.bg;
                toast.style.color = color.text;
                
                // Set content
                toast.innerHTML = `
                    <div style="font-weight:bold; margin-bottom:5px;">${title}</div>
                    <div>${message}</div>
                `;
                
                // Add to container
                toastContainer.appendChild(toast);
                
                // Remove after duration
                setTimeout(() => {
                    toast.style.animation = 'fadeOut 0.5s';
                    setTimeout(() => {
                        toast.remove();
                    }, 500);
                }, duration);
            };
            
            // Add CSS animations for the toasts
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                @keyframes fadeOut {
                    from { opacity: 1; transform: translateY(0); }
                    to { opacity: 0; transform: translateY(-20px); }
                }
            `;
            document.head.appendChild(style);
        }
    </script>

    @vite('resources/js/admin/ai-dashboard/stats_main.js')
@endpush
