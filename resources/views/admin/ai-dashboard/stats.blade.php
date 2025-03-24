@extends('admin_layouts.admin')

@section('content')
    <div class="space-y-6">
        <!-- Fixed Header with Gradient Background -->
        <div class="sticky top-[84px] z-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-indigo-50 to-indigo-100 p-6 rounded-xl shadow-sm">
            <div>
                <h2 class="text-3xl font-bold text-indigo-800">Trình Phân Tích Nội Dung SEO</h2>
                <p class="mt-1 text-indigo-600">Tìm và tối ưu hóa nội dung thiếu thông tin hoặc chưa đủ yêu cầu SEO</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ai-dashboard.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-indigo-300 py-2 px-4 text-center font-medium text-indigo-700 hover:bg-indigo-100 sm:px-6 transition duration-150">
                    <span class="iconify" data-icon="mdi-arrow-left"></span>
                    Quay Lại Trang Chính
                </a>
                <button id="bulk-generate-btn" class="inline-flex items-center justify-center gap-2.5 rounded-md bg-indigo-600 py-2 px-4 text-center font-medium text-white hover:bg-indigo-700 sm:px-6 transition duration-150 shadow-sm">
                    <span class="iconify" data-icon="mdi-plus"></span>
                    Tạo Hàng Loạt
                </button>
            </div>
        </div>

        <!-- Content Filter Panel with Card Design -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-4 border-b border-gray-100 pb-4">
                <h4 class="text-xl font-semibold text-gray-800">Bộ Lọc Nội Dung</h4>
                <p class="mt-1 text-sm text-gray-600">Chọn nội dung để tối ưu hóa SEO</p>
            </div>

            <!-- Content Type Selector with Visual Buttons -->
            <div class="mb-6">
                <label class="mb-3 block text-sm font-medium text-gray-700">Loại Nội Dung</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <button type="button" class="content-type-btn flex flex-col items-center justify-center rounded-xl border-2 border-transparent bg-gray-50 px-3 py-4 transition hover:bg-indigo-50 hover:border-indigo-200 hover:shadow-sm" data-type="posts">
                        <span class="iconify text-2xl mb-2 text-indigo-600" data-icon="mdi-file-document-outline"></span>
                        <span class="text-sm font-medium">Bài Viết</span>
                    </button>
                    <button type="button" class="content-type-btn flex flex-col items-center justify-center rounded-xl border-2 border-transparent bg-gray-50 px-3 py-4 transition hover:bg-indigo-50 hover:border-indigo-200 hover:shadow-sm" data-type="chapters">
                        <span class="iconify text-2xl mb-2 text-indigo-600" data-icon="mdi-book-open-variant"></span>
                        <span class="text-sm font-medium">Chương</span>
                    </button>
                    <button type="button" class="content-type-btn flex flex-col items-center justify-center rounded-xl border-2 border-transparent bg-gray-50 px-3 py-4 transition hover:bg-indigo-50 hover:border-indigo-200 hover:shadow-sm" data-type="books">
                        <span class="iconify text-2xl mb-2 text-indigo-600" data-icon="mdi-book-outline"></span>
                        <span class="text-sm font-medium">Sách</span>
                    </button>
                    <button type="button" class="content-type-btn flex flex-col items-center justify-center rounded-xl border-2 border-transparent bg-gray-50 px-3 py-4 transition hover:bg-indigo-50 hover:border-indigo-200 hover:shadow-sm" data-type="book_groups">
                        <span class="iconify text-2xl mb-2 text-indigo-600" data-icon="mdi-bookshelf"></span>
                        <span class="text-sm font-medium">Nhóm Sách</span>
                    </button>
                </div>
                <!-- Hidden input to store current content type -->
                <input type="hidden" id="content-type" name="content_type" value="{{ $type }}">
            </div>

            <!-- Hierarchical Selectors with Improved Design -->
            <div id="hierarchical-selectors" class="space-y-4 mb-6">
                <!-- Category Selector (Always visible) -->
                <div class="selector-container" id="category-selector-container">
                    <label class="mb-1.5 text-sm font-medium text-gray-700">Danh Mục</label>
                    <div class="relative">
                        <select id="category-selector" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2.5 px-4 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                            <option value="">-- Chọn Danh Mục --</option>
                        </select>
                    </div>
                </div>

                <!-- Book Group Selector (Visible for all) -->
                <div class="selector-container" id="group-selector-container" style="display:none">
                    <label class="mb-1.5 text-sm font-medium text-gray-700">Nhóm Sách</label>
                    <div class="relative">
                        <select id="group-selector" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2.5 px-4 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                            <option value="">-- Chọn Nhóm Sách --</option>
                        </select>
                    </div>
                </div>

                <!-- Book Selector (For Posts, Chapters, Books) -->
                <div class="selector-container" id="book-selector-container" style="display:none">
                    <label class="mb-1.5 text-sm font-medium text-gray-700">Sách</label>
                    <div class="relative">
                        <select id="book-selector" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2.5 px-4 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                            <option value="">-- Chọn Sách --</option>
                        </select>
                    </div>
                </div>

                <!-- Chapter Selector (For Posts, Chapters) -->
                <div class="selector-container" id="chapter-selector-container" style="display:none">
                    <label class="mb-1.5 text-sm font-medium text-gray-700">Chương</label>
                    <div class="relative">
                        <select id="chapter-selector" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2.5 px-4 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                            <option value="">-- Chọn Chương --</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Additional Filters with Improved Design -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- SEO Status Filter -->
                <div>
                    <label for="seo-status" class="mb-1.5 text-sm font-medium text-gray-700">Trạng Thái SEO</label>
                    <select id="seo-status" class="form-select w-full rounded-lg border border-gray-300 bg-white py-2.5 px-4 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150">
                        <option value="missing">Thiếu hoặc Không Đủ Dữ Liệu SEO</option>
                        <option value="all">Tất Cả Nội Dung</option>
                        <option value="complete">Dữ Liệu SEO Đầy Đủ</option>
                    </select>
                </div>

                <!-- Search Filter -->
                <div>
                    <label for="search-input" class="mb-1.5 text-sm font-medium text-gray-700">Tìm Kiếm</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="search-input"
                            placeholder="Tìm theo tiêu đề, tên..."
                            class="form-control w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150"
                        >
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <span class="iconify text-gray-500" data-icon="mdi-magnify"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:justify-between gap-3 pt-2 border-t border-gray-100">
                <div class="flex items-center text-sm text-gray-600">
                    <span id="filter-status">Chọn bộ lọc để hiển thị kết quả</span>
                    <span class="mx-2">•</span>
                    <span id="content-count">0 mục</span>
                </div>
                <div class="flex gap-2">
                    <button id="reset-filter-btn" class="btn btn-outline-secondary flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition duration-150">
                        <span class="iconify" data-icon="mdi-refresh"></span>
                        Đặt Lại
                    </button>
                    <button id="apply-filter-btn" class="btn btn-primary flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition duration-150 shadow-sm">
                        <span class="iconify" data-icon="mdi-filter"></span>
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

        <!-- Content Table with Improved Design -->
        <div id="content-table-container" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h4 class="text-xl font-semibold text-gray-800" id="table-title">
                        Nội Dung Thiếu Dữ Liệu SEO
                    </h4>
                    <p class="mt-1 text-sm text-gray-600" id="total-count">Đang tải...</p>
                </div>
                <div>
                    <select id="limit-select" class="form-select rounded-lg border border-gray-300 bg-transparent py-2 px-5 font-medium outline-none transition focus:border-indigo-600">
                        <option value="10">10 mục/trang</option>
                        <option value="25">25 mục/trang</option>
                        <option value="50" selected>50 mục/trang</option>
                        <option value="100">100 mục/trang</option>
                    </select>
                </div>
            </div>

            <!-- Select All Controls -->
            <div class="mb-4 flex items-center space-x-2 bg-blue-50 p-3.5 rounded-lg border border-blue-100">
                <div class="flex h-5 items-center">
                    <input id="select-all" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                </div>
                <label for="select-all" class="ml-2 text-sm font-medium text-gray-700">Chọn Tất Cả</label>
                <span id="selected-count" class="ml-4 text-sm text-indigo-600 font-medium">0 đã chọn</span>
            </div>

            <!-- SEO Legend -->
            <div class="mb-4 p-3.5 bg-gray-50 rounded-lg border border-gray-100">
                <h5 class="text-sm font-medium text-gray-700 mb-2">Chú Thích Trạng Thái SEO:</h5>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="flex items-center">
                        <span class="mr-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-red-600">
                            <span class="iconify" data-icon="mdi-close-circle" data-width="14"></span>
                        </span>
                        <span>Thiếu Dữ Liệu</span>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
                            <span class="iconify" data-icon="mdi-alert" data-width="14"></span>
                        </span>
                        <span>Quá Ngắn (< yêu cầu độ dài)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-green-100 text-green-600">
                            <span class="iconify" data-icon="mdi-check-circle" data-width="14"></span>
                        </span>
                        <span>Hoàn Thành</span>
                    </div>
                    <div class="flex items-center">
                        <span class="ml-1 text-gray-500">
                            <span class="font-medium">Yêu cầu:</span> Tiêu đề ≥ 60 ký tự, Mô tả ≥ 350 ký tự
                        </span>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto rounded-lg border border-gray-200">
                <table id="content-table" class="w-full table-auto">
                    <thead>
                    <tr class="bg-gray-50 text-left border-b border-gray-200">
                        <th class="py-3 px-4 font-medium text-gray-700">
                            <span class="sr-only">Chọn</span>
                        </th>
                        <th class="py-3 px-4 font-medium text-gray-700" id="col-title">
                            Tiêu Đề / Tên
                        </th>
                        <th class="py-3 px-4 font-medium text-gray-700" id="col-parent">
                            Đường Dẫn
                        </th>
                        <th class="py-3 px-4 font-medium text-gray-700" id="col-status">
                            Trạng Thái SEO
                        </th>
                        <th class="py-3 px-4 font-medium text-gray-700">Thao Tác</th>
                    </tr>
                    </thead>
                    <tbody id="content-tbody">
                    <!-- Content will be loaded here dynamically -->
                    <tr id="empty-row">
                        <td colspan="5" class="border-b border-gray-200 py-6 px-4 text-center">
                            <p class="text-gray-500">Chọn bộ lọc và nhấn "Áp Dụng Bộ Lọc" để xem kết quả</p>
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

    <!-- Bulk Generate Modal - Updated design -->
    <div class="modal fade" id="bulk-generate-modal" tabindex="-1" aria-labelledby="bulkGenerateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-to-r from-indigo-50 to-indigo-100 border-b">
                    <h5 class="modal-title text-indigo-800 font-medium" id="bulkGenerateModalLabel">Tạo Nội Dung Hàng Loạt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <div class="alert alert-warning d-flex align-items-center rounded-lg" role="alert">
                            <span class="iconify me-2" data-icon="mdi-alert"></span>
                            <p id="bulk-selected-count">Chưa chọn mục nào</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bulk-provider" class="mb-2.5 block font-medium text-gray-700">Nhà Cung Cấp AI</label>
                        <div class="relative bg-white">
                            <select data-plugin-select2 id="bulk-provider" class="form-select w-full rounded-lg border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
                                <option value="">Chọn nhà cung cấp AI</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bulk-model" class="mb-2.5 block font-medium text-gray-700">Mô Hình AI</label>
                        <div class="relative bg-white">
                            <select data-plugin-select2 id="bulk-model" class="form-select w-full rounded-lg border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
                                @include('admin.ai-dashboard.partials.model-options')
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bulk-prompt-source" class="mb-2.5 block font-medium text-gray-700">Prompt</label>
                        <div class="relative bg-white">
                            <select data-plugin-select2 id="bulk-prompt-source" class="form-select w-full rounded-lg border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
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
                            <select id="bulk-saved-prompt" class="form-select w-full rounded-lg border border-gray-300 py-3 px-5 outline-none transition focus:border-indigo-600">
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
                        <label for="bulk-system-message" class="mb-2.5 block font-medium text-gray-700">Thông Điệp Hệ Thống (cho DeepSeek)</label>
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
                        <div class="flex items-center">
                            <p class="text-sm text-gray-500">Luôn sử dụng giới hạn token tối đa cho mô hình</p>
                        </div>
                    </div>

{{--                    <div class="mb-4">--}}
{{--                        <label class="mb-2.5 block font-medium text-gray-700">HTML cho Mô Tả Meta</label>--}}
{{--                        <div class="flex items-center space-x-2">--}}
{{--                            <input--}}
{{--                                type="checkbox"--}}
{{--                                id="bulk-use-html-meta"--}}
{{--                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"--}}
{{--                            >--}}
{{--                            <label for="bulk-use-html-meta" class="text-sm text-gray-600">--}}
{{--                                Sử dụng định dạng HTML cho mô tả meta--}}
{{--                            </label>--}}
{{--                        </div>--}}
{{--                        <p class="mt-1 text-xs text-gray-500">Phù hợp cho TinyMCE và hiển thị trực tiếp trên frontend</p>--}}
{{--                    </div>--}}

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
                <div class="modal-footer border-t">
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
    @vite('resources/css/admin/ai-dashboard/stats_main.css')
@endpush

@push('scripts')
    @vite('resources/js/admin/ai-dashboard/stats_main.js')
@endpush
