@extends('admin_layouts.admin')

@section('title', 'Cài Đặt Wiki Q&A')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header với gradient background -->
        <div class="rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 p-6 shadow-sm mb-6 border border-blue-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="iconify text-blue-500 text-3xl mt-1" data-icon="mdi-comment-question"></span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Cài Đặt Hệ Thống Wiki Q&A</h1>
                        <p class="text-gray-600 mt-1">Quản lý và cấu hình hệ thống câu hỏi và trả lời Wiki</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.wiki.moderation') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-blue-200 bg-white py-2 px-4 text-sm font-medium text-blue-600 hover:bg-blue-50 shadow-sm">
                        <span class="iconify" data-icon="mdi-shield-check"></span>
                        Kiểm duyệt câu hỏi
                    </a>
                    <a href="{{ route('admin.wiki.questions') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-blue-200 bg-white py-2 px-4 text-sm font-medium text-blue-600 hover:bg-blue-50 shadow-sm">
                        <span class="iconify" data-icon="mdi-format-list-bulleted"></span>
                        Quản lý câu hỏi
                    </a>
                </div>
            </div>
        </div>

        <!-- Thông báo -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-lg border-l-4 border-green-500 bg-green-50 p-4 mb-6" role="alert">
                <div class="flex items-center">
                    <span class="iconify text-green-500 text-xl mr-2" data-icon="mdi-check-circle"></span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-lg border-l-4 border-red-500 bg-red-50 p-4 mb-6" role="alert">
                <div class="flex items-center">
                    <span class="iconify text-red-500 text-xl mr-2" data-icon="mdi-alert-circle"></span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Cài đặt -->
            <div class="lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <span class="iconify text-blue-500 mr-2" data-icon="mdi-cog"></span>
                            Cấu hình hệ thống
                        </h2>
                    </div>

                    <form action="{{ route('admin.wiki.settings.update') }}" method="POST" class="p-5">
                        @csrf
                        @method('PUT')

                        <!-- AI Provider Settings -->
                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-700 flex items-center mb-4 pb-2 border-b border-gray-100">
                                <span class="iconify text-blue-500 mr-2" data-icon="mdi-robot"></span>
                                Nhà cung cấp AI
                            </h3>

                            <div class="mb-4">
                                <label for="default_ai_provider" class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp AI mặc định</label>
                                <select id="default_ai_provider" name="default_ai_provider" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    @foreach($aiProviders as $value => $label)
                                        <option value="{{ $value }}" @if(($settings['default_ai_provider'] ?? '') === $value) selected @endif>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 flex items-center">
                                    <span class="iconify text-gray-400 mr-1" data-icon="mdi-information-outline"></span>
                                    Hệ thống sẽ sử dụng nhà cung cấp này để tạo câu trả lời tự động
                                </p>
                            </div>

                            <div class="mb-4">
                                <label for="default_api_key" class="block text-sm font-medium text-gray-700 mb-1">Khóa API mặc định (Tùy chọn)</label>
                                <input type="password" id="default_api_key" name="default_api_key"
                                       value="{{ $settings['default_api_key'] ?? '' }}"
                                       class="form-control w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <p class="mt-1 text-xs text-gray-500 flex items-center">
                                    <span class="iconify text-gray-400 mr-1" data-icon="mdi-information-outline"></span>
                                    Nếu để trống, hệ thống sẽ sử dụng khóa API mặc định từ cấu hình ứng dụng
                                </p>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 mb-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="auto_generate_answers" value="0">
                                    <input class="form-check-input" type="checkbox" id="auto_generate_answers" name="auto_generate_answers" value="1"
                                           @if(($settings['auto_generate_answers'] ?? '0') == '1') checked @endif>
                                    <label class="form-check-label font-medium" for="auto_generate_answers">Tự động tạo câu trả lời</label>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Tự động tạo câu trả lời AI cho các câu hỏi mới
                                </p>
                            </div>
                        </div>

                        <!-- Embedding Settings -->
                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-700 flex items-center mb-4 pb-2 border-b border-gray-100">
                                <span class="iconify text-purple-500 mr-2" data-icon="mdi-vector-triangle"></span>
                                Cài đặt Embedding
                            </h3>

                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="embedding_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="embedding_enabled" name="embedding_enabled" value="1"
                                           @if(($settings['embedding_enabled'] ?? '0') == '1') checked @endif>
                                    <label class="form-check-label font-medium" for="embedding_enabled">Kích hoạt Embedding</label>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Tạo các embedding cho câu hỏi để hỗ trợ tìm kiếm vector
                                </p>
                            </div>
                        </div>

                        <!-- Moderation Settings -->
                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-700 flex items-center mb-4 pb-2 border-b border-gray-100">
                                <span class="iconify text-amber-500 mr-2" data-icon="mdi-shield"></span>
                                Kiểm duyệt nội dung
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="moderation_enabled" value="0">
                                        <input class="form-check-input" type="checkbox" id="moderation_enabled" name="moderation_enabled" value="1"
                                               @if(($settings['moderation_enabled'] ?? '0') == '1') checked @endif>
                                        <label class="form-check-label font-medium" for="moderation_enabled">Bật kiểm duyệt</label>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">
                                        Kiểm duyệt câu hỏi và bình luận trước khi hiển thị công khai
                                    </p>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100" id="auto_approve_container">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="approve_questions_automatic" value="0">
                                        <input class="form-check-input" type="checkbox" id="approve_questions_automatic" name="approve_questions_automatic" value="1"
                                               @if(($settings['approve_questions_automatic'] ?? '0') == '1') checked @endif>
                                        <label class="form-check-label font-medium" for="approve_questions_automatic">Tự động phê duyệt</label>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">
                                        Tự động phê duyệt câu hỏi mới mà không cần kiểm duyệt thủ công
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Rate Limits -->
                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-700 flex items-center mb-4 pb-2 border-b border-gray-100">
                                <span class="iconify text-green-500 mr-2" data-icon="mdi-speedometer"></span>
                                Giới hạn tốc độ
                            </h3>

                            <div>
                                <label for="max_comments_per_day" class="block text-sm font-medium text-gray-700 mb-1">
                                    Số bình luận tối đa/ngày/người dùng
                                </label>
                                <div class="flex items-center">
                                    <input type="number" id="max_comments_per_day" name="max_comments_per_day"
                                           value="{{ $settings['max_comments_per_day'] ?? '30' }}"
                                           min="0" step="1"
                                           class="form-control rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <span class="ml-2 text-gray-500 text-sm">bình luận</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Nhập 0 để không giới hạn
                                </p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end mt-8 pt-4 border-t border-gray-100">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 py-2.5 px-6 text-center font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 shadow-sm transition-colors">
                                <span class="iconify" data-icon="mdi-content-save"></span>
                                Lưu cài đặt
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="lg:col-span-1">
                <!-- Thống kê nhanh -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm mb-6 overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <span class="iconify text-blue-500 mr-2" data-icon="mdi-chart-box"></span>
                            Thống kê Wiki
                        </h2>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <span class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</span>
                                <p class="text-sm text-gray-600 mt-1">Câu hỏi</p>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <span class="text-2xl font-bold text-green-600">{{ $stats['total_answers'] }}</span>
                                <p class="text-sm text-gray-600 mt-1">Câu trả lời</p>
                            </div>
                            <div class="text-center p-3 bg-purple-50 rounded-lg">
                                <span class="text-2xl font-bold text-purple-600">{{ $stats['total_comments'] }}</span>
                                <p class="text-sm text-gray-600 mt-1">Bình luận</p>
                            </div>
                            <div class="text-center p-3 bg-amber-50 rounded-lg">
                                <span class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</span>
                                <p class="text-sm text-gray-600 mt-1">Chờ duyệt</p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3">
                            <div class="text-center p-3 bg-indigo-50 rounded-lg">
                                <span class="text-xl font-bold text-indigo-600">{{ $stats['with_ai_answers'] }}</span>
                                <p class="text-sm text-gray-600 mt-1">Câu trả lời AI</p>
                            </div>
                            <div class="text-center p-3 bg-teal-50 rounded-lg">
                                <span class="text-xl font-bold text-teal-600">{{ $stats['with_user_answers'] }}</span>
                                <p class="text-sm text-gray-600 mt-1">Đã có người trả lời</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hướng dẫn -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <span class="iconify text-blue-500 mr-2" data-icon="mdi-lightbulb"></span>
                            Mẹo hữu ích
                        </h2>
                    </div>
                    <div class="p-5">
                        <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                            <h3 class="font-medium text-blue-800 flex items-center mb-2">
                                <span class="iconify mr-1" data-icon="mdi-robot"></span>
                                Nhà cung cấp AI
                            </h3>
                            <p class="text-sm text-gray-700">Chọn nhà cung cấp AI phù hợp với nhu cầu về ngôn ngữ và độ chính xác của dự án của bạn.</p>
                        </div>

                        <div class="mb-4 p-4 bg-purple-50 rounded-lg border border-purple-100">
                            <h3 class="font-medium text-purple-800 flex items-center mb-2">
                                <span class="iconify mr-1" data-icon="mdi-vector-triangle"></span>
                                Embedding
                            </h3>
                            <p class="text-sm text-gray-700">Bật tính năng embedding để tạo ra trải nghiệm tìm kiếm tốt hơn cho người dùng với khả năng hiểu ngữ nghĩa.</p>
                        </div>

                        <div class="p-4 bg-green-50 rounded-lg border border-green-100">
                            <h3 class="font-medium text-green-800 flex items-center mb-2">
                                <span class="iconify mr-1" data-icon="mdi-shield-account"></span>
                                Kiểm duyệt
                            </h3>
                            <p class="text-sm text-gray-700">Bật kiểm duyệt nội dung để đảm bảo chất lượng câu hỏi và câu trả lời trên hệ thống.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Hiển thị/ẩn tùy chọn tự động phê duyệt dựa trên trạng thái kiểm duyệt
            function toggleAutoApprove() {
                if($('#moderation_enabled').is(':checked')) {
                    $('#auto_approve_container').removeClass('opacity-50 pointer-events-none');
                } else {
                    $('#auto_approve_container').addClass('opacity-50 pointer-events-none');
                    $('#approve_questions_automatic').prop('checked', false);
                }
            }

            // Chạy khi trang tải và khi thay đổi
            toggleAutoApprove();
            $('#moderation_enabled').change(toggleAutoApprove);

            // Hiệu ứng khi lưu cài đặt
            $('form').submit(function() {
                $('button[type="submit"]').html('<span class="iconify animate-spin mr-2" data-icon="mdi-loading"></span> Đang lưu...');
                $('button[type="submit"]').prop('disabled', true);
            });
        });
    </script>
@endpush
