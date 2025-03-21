@extends('admin_layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-lg shadow">
            <div>
                <h2 class="text-3xl font-bold text-indigo-700">Kết Quả Phân Tích Hình Ảnh</h2>
                <p class="mt-1 text-gray-600">Phân tích được tạo tự động bởi AI</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ai-dashboard.vision') }}" class="inline-flex items-center justify-center gap-2.5 rounded-lg border border-indigo-600 py-2 px-5 text-center font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors">
                    <span class="iconify" data-icon="mdi-arrow-left"></span>
                    Phân Tích Hình Ảnh Khác
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Image Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-md">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">Hình Ảnh Đã Phân Tích</h3>
                    <span class="rounded-full bg-indigo-100 py-1 px-3 text-sm font-medium text-indigo-600">
                        {{ $model }}
                    </span>
                </div>

                <div class="mb-4 aspect-auto rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                    <img src="{{ $imageUrl }}" alt="Hình Ảnh Đã Phân Tích" class="h-full w-full object-contain">
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <h4 class="mb-2 font-medium text-gray-800">Yêu Cầu Được Sử Dụng</h4>
                    <p class="text-sm text-gray-600">{{ $prompt }}</p>
                </div>
            </div>

            <!-- Results Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-md">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">Phân Tích Của AI</h3>
                    <div class="flex items-center gap-2">
                        <button id="copyBtn" class="rounded-lg border border-gray-200 p-2 hover:bg-gray-50 transition-colors" title="Sao chép vào clipboard">
                            <span class="iconify" data-icon="mdi-content-copy"></span>
                        </button>
                        <button id="downloadBtn" class="rounded-lg border border-gray-200 p-2 hover:bg-gray-50 transition-colors" title="Tải xuống kết quả">
                            <span class="iconify" data-icon="mdi-download"></span>
                        </button>
                    </div>
                </div>

                <div class="h-[400px] overflow-y-auto rounded-lg border border-gray-200 p-4 bg-gray-50">
                    <div id="analysisResult" class="prose prose-sm max-w-none">
                        {!! nl2br(e($result)) !!}
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="mb-2 font-medium text-gray-800">Điều Chỉnh Phân Tích</h4>
                    <form action="{{ route('admin.ai-dashboard.vision.analyze') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="hidden" name="image" value="{{ $imageUrl }}">
                        <input type="hidden" name="model" value="{{ $model }}">
                        <input type="hidden" name="temperature" value="0.7">

                        <div class="flex flex-col gap-4 w-full">
                            <textarea name="prompt"
                                      class="w-full rounded-lg border-[1.5px] border-gray-300 bg-transparent py-2 px-5 font-medium outline-none transition focus:border-indigo-600 active:border-indigo-600"
                                      placeholder="Thử yêu cầu khác..."
                                      rows="3" required
                            >{{ $prompt }}</textarea>

                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 py-2 px-5 text-center font-medium text-white hover:bg-indigo-700 transition-colors">
                                <span class="iconify me-2" data-icon="mdi-eye-refresh"></span>
                                Phân Tích Lại
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Related Features -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-md">
            <h3 class="mb-5 text-xl font-semibold text-gray-800">Thử Các Tính Năng AI Khác</h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                <a href="{{ route('admin.ai-dashboard.playground') }}" class="group flex items-center rounded-lg border border-gray-200 p-4 hover:border-indigo-600 hover:bg-gray-50 transition-colors">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100">
                        <span class="iconify text-2xl text-indigo-600" data-icon="mdi-atom"></span>
                    </div>
                    <div class="ml-4 flex-1">
                        <h5 class="text-md font-medium text-gray-800 group-hover:text-indigo-600">Khu Vực Thử Nghiệm AI</h5>
                        <p class="text-sm text-gray-500">Tạo nội dung SEO cho các tài liệu giáo dục của bạn</p>
                    </div>
                    <span class="iconify text-gray-400 group-hover:text-indigo-600" data-icon="mdi-chevron-right"></span>
                </a>

                <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'posts']) }}" class="group flex items-center rounded-lg border border-gray-200 p-4 hover:border-indigo-600 hover:bg-gray-50 transition-colors">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                        <span class="iconify text-2xl text-green-600" data-icon="mdi-calendar-text"></span>
                    </div>
                    <div class="ml-4 flex-1">
                        <h5 class="text-md font-medium text-gray-800 group-hover:text-indigo-600">Tối Ưu Hóa Bài Viết</h5>
                        <p class="text-sm text-gray-500">Tạo tiêu đề và mô tả meta để SEO tốt hơn</p>
                    </div>
                    <span class="iconify text-gray-400 group-hover:text-indigo-600" data-icon="mdi-chevron-right"></span>
                </a>

                <a href="{{ route('admin.ai-dashboard.index') }}" class="group flex items-center rounded-lg border border-gray-200 p-4 hover:border-indigo-600 hover:bg-gray-50 transition-colors">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100">
                        <span class="iconify text-2xl text-amber-600" data-icon="mdi-message-text"></span>
                    </div>
                    <div class="ml-4 flex-1">
                        <h5 class="text-md font-medium text-gray-800 group-hover:text-indigo-600">Bảng Điều Khiển</h5>
                        <p class="text-sm text-gray-500">Xem và quản lý tất cả các tính năng AI</p>
                    </div>
                    <span class="iconify text-gray-400 group-hover:text-indigo-600" data-icon="mdi-chevron-right"></span>
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/ai-dashboard/vision_results_main.js') }}"></script>
@endpush
