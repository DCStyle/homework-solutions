<div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300" id="chat-container">
    <!-- Header Section -->
    <div class="p-6 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-t-xl border-b border-gray-100">
        <div class="flex items-start">
            <span class="mr-4 text-indigo-500">
                <span class="iconify" data-icon="mdi-chat-question-outline" data-width="28"></span>
            </span>
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Đặt câu hỏi mới</h2>
                <p class="mt-1 text-gray-600">
                    Không tìm thấy câu trả lời? Hãy đặt câu hỏi và nhận phản hồi ngay lập tức từ trí tuệ nhân tạo.
                </p>
            </div>
        </div>
    </div>

    <!-- Chat Messages Container -->
    <div id="messages-container" class="p-6 max-h-[500px] overflow-y-auto border-b border-gray-100">
        <!-- Messages will be inserted here -->
        <!-- Empty state message -->
        <div id="empty-state" class="text-center py-12">
            <span class="iconify block mx-auto mb-3 text-gray-300" data-icon="mdi-message-text-outline" data-width="48"></span>
            <p class="text-gray-500">Hãy đặt câu hỏi để bắt đầu cuộc trò chuyện</p>
        </div>
    </div>

    <!-- Form Section -->
    <div class="p-6 border-t border-gray-100" id="form-container">
        <form id="chat-form" class="space-y-5">
            @csrf

            <!-- Question Content -->
            <div>
                <label for="content" class="flex items-center text-sm font-medium text-gray-700 mb-1.5">
                    <span class="iconify mr-1.5" data-icon="mdi-text-box-outline" data-width="18"></span>
                    Nội dung câu hỏi <span class="text-red-500 ml-1">*</span>
                </label>
                <div class="relative">
                    <textarea id="content" name="content" rows="5"
                              class="block w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                              placeholder="Mô tả chi tiết vấn đề hoặc câu hỏi của bạn để nhận được câu trả lời đầy đủ nhất"
                              required></textarea>
                </div>
                <p class="mt-1 text-xs text-gray-500">Thông tin chi tiết giúp AI hiểu rõ hơn về nội dung bạn đang tìm kiếm và tạo tiêu đề phù hợp</p>
            </div>

            <!-- Category and Book Group Selection Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Category Selection -->
                <div>
                    <label for="category_id" class="flex items-center text-sm font-medium text-gray-700 mb-1.5">
                        <span class="iconify mr-1.5" data-icon="mdi-folder-outline" data-width="18"></span>
                        Danh mục <span class="text-red-500 ml-1">*</span>
                    </label>
                    <div class="relative">
                        <select id="category_id" name="category_id"
                                class="block w-full pl-4 pr-10 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors appearance-none"
                                required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <span class="iconify" data-icon="mdi-chevron-down" data-width="20"></span>
                        </div>
                    </div>
                </div>

                <!-- Book Group Selection (Optional) -->
                <div>
                    <label for="book_group_id" class="flex items-center text-sm font-medium text-gray-700 mb-1.5">
                        <span class="iconify mr-1.5" data-icon="mdi-book-multiple-outline" data-width="18"></span>
                        Bộ sách <span class="text-gray-400 ml-1 text-xs">(tùy chọn)</span>
                    </label>
                    <div class="relative">
                        <select id="book_group_id" name="book_group_id"
                                class="block w-full pl-4 pr-10 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors appearance-none">
                            <option value="">-- Không thuộc bộ sách nào --</option>
                            @foreach($bookGroups as $bookGroup)
                                <option value="{{ $bookGroup->id }}">{{ $bookGroup->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <span class="iconify" data-icon="mdi-chevron-down" data-width="20"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Title Generation Notice -->
            <div class="bg-blue-50 rounded-lg p-4 flex items-start">
                <span class="text-blue-500 mr-3 mt-0.5">
                    <span class="iconify" data-icon="mdi-robot-outline" data-width="20"></span>
                </span>
                <p class="text-sm text-blue-700">
                    Hệ thống sẽ tự động tạo tiêu đề phù hợp cho câu hỏi của bạn dựa trên nội dung bạn cung cấp.
                </p>
            </div>

            <!-- Privacy Notice -->
            <div class="bg-gray-50 rounded-lg p-4 flex items-start">
                <span class="text-gray-500 mr-3 mt-0.5">
                    <span class="iconify" data-icon="mdi-information-outline" data-width="20"></span>
                </span>
                <p class="text-sm text-gray-700">
                    Câu hỏi của bạn sẽ được kiểm duyệt trước khi xuất hiện công khai trên hệ thống. Mọi người sẽ có thể xem và tham gia thảo luận.
                </p>
            </div>

            <!-- Submit Button -->
            <div class="pt-3">
                <button type="submit" id="submit-button"
                        class="w-full inline-flex items-center justify-center px-6 py-3.5 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <span class="iconify mr-2" data-icon="mdi-send" data-width="20"></span>
                    Gửi câu hỏi của bạn
                </button>
            </div>
        </form>
    </div>

    <!-- View Question Link (fixed at bottom) -->
    <div id="view-question-container" class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-xl hidden">
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600 flex items-center">
                <span class="iconify mr-1" data-icon="mdi-information-outline" data-width="16"></span>
                Câu hỏi đã được lưu và đang chờ duyệt
            </div>
            <a id="view-question-link" href="#" class="inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-600 bg-white border border-indigo-200 rounded-lg hover:bg-indigo-50">
                <span class="iconify mr-1.5" data-icon="mdi-open-in-new" data-width="18"></span>
                Xem trang câu hỏi đầy đủ
            </a>
        </div>
    </div>
</div>

@push('styles')
    @vite('resources/css/public/wiki/chat.css')
@endpush

@push('scripts')
    @vite('resources/js/public/wiki/chat.js')
@endpush
