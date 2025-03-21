@extends('admin_layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-3xl font-bold text-indigo-700">Phân Tích Hình Ảnh AI</h2>
                <p class="mt-1 text-gray-600">Phân tích chi tiết hình ảnh bằng công nghệ trí tuệ nhân tạo</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ai-dashboard.index') }}" class="inline-flex items-center justify-center gap-2.5 rounded-lg border border-indigo-600 py-2.5 px-6 text-center font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors">
                    <span class="iconify text-lg" data-icon="mdi-arrow-left"></span>
                    Quay Lại Bảng Điều Khiển
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-12">
            <!-- Form Tải Lên -->
            <div class="md:col-span-5 lg:col-span-4">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-md h-full">
                    <h3 class="mb-5 text-xl font-semibold text-indigo-700">Tải Lên & Phân Tích</h3>

                    <form action="{{ route('admin.ai-dashboard.vision.analyze') }}" method="POST" enctype="multipart/form-data" id="visionForm">
                        @csrf

                        @if($errors->any())
                            <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-600">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Tải Lên Hình Ảnh -->
                        <div class="mb-6">
                            <label class="mb-2.5 block font-medium text-gray-700">Tải Lên Hình Ảnh</label>
                            <div class="relative">
                                <div id="imagePreviewContainer" class="hidden mb-3 relative">
                                    <img id="imagePreview" class="max-h-48 rounded-lg object-contain mx-auto border border-indigo-200 p-1" src="" alt="Xem trước">
                                    <button type="button" id="removeImage" class="absolute top-2 right-2 rounded-full bg-red-500 p-1 hover:bg-red-600 transition-colors">
                                        <span class="iconify text-white" data-icon="mdi-close"></span>
                                    </button>
                                </div>

                                <div id="dropZone" class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-indigo-300 bg-indigo-50 py-8 px-4 cursor-pointer hover:bg-indigo-100 transition-colors">
                                    <span class="iconify text-indigo-500 text-5xl" data-icon="mdi-image-outline"></span>
                                    <p class="mt-3 text-sm text-gray-600">Kéo và thả hình ảnh, hoặc <span class="text-indigo-600 font-medium">chọn tệp</span></p>
                                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF tối đa 10MB</p>
                                </div>
                                <input id="image" name="image" type="file" accept="image/*" class="hidden" required>
                            </div>
                        </div>

                        <!-- Lựa Chọn Mô Hình AI -->
                        <div class="mb-4">
                            <label for="model" class="mb-2.5 block font-medium text-gray-700">Mô Hình AI</label>
                            <div class="relative bg-white">
                                <select id="model" name="model" class="form-select w-full appearance-none rounded-lg border border-gray-300 bg-transparent py-3 px-5 outline-none transition focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <optgroup label="Mô Hình Grok">
                                        <option value="grok-2-vision">Grok Vision</option>
                                        <option value="grok-2-vision-latest">Grok Vision Mới Nhất</option>
                                    </optgroup>
                                    <optgroup label="Mô Hình DeepSeek">
                                        <option value="deepseek-vision">DeepSeek Vision</option>
                                    </optgroup>
                                </select>
                                <span class="absolute top-1/2 right-4 -translate-y-1/2 pointer-events-none">
                                    <span class="iconify text-gray-500" data-icon="mdi-chevron-down"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Tùy Chọn Mô Hình -->
                        <div id="modelOptions" class="mb-6 space-y-4">
                            <!-- Nhiệt Độ -->
                            <div>
                                <label for="temperature" class="mb-2.5 block font-medium text-gray-700">Nhiệt Độ</label>
                                <div class="relative">
                                    <input type="range" id="temperature" name="temperature" min="0" max="1" step="0.1" value="0.7" class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer">
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>Chính Xác</span>
                                        <span id="temperatureValue">0.7</span>
                                        <span>Sáng Tạo</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tùy Chọn DeepSeek -->
                            <div id="deepseekOptions" class="space-y-4 hidden">
                                <div>
                                    <label for="system_message" class="mb-2.5 block font-medium text-gray-700">Tin Nhắn Hệ Thống</label>
                                    <textarea id="system_message" name="system_message" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent py-3 px-5 font-medium outline-none transition focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">Bạn là trợ lý phân tích hình ảnh. Mô tả hình ảnh một cách chính xác và cung cấp thông tin chi tiết dựa trên những gì bạn thấy.</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Câu Hỏi -->
                        <div class="mb-6">
                            <label for="prompt" class="mb-2.5 block font-medium text-gray-700">Câu Hỏi</label>
                            <textarea id="prompt" name="prompt" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent py-3 px-5 font-medium outline-none transition focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Mô tả chi tiết hình ảnh này..." required>Mô tả chi tiết những gì bạn thấy trong hình ảnh này.</textarea>
                        </div>

                        <!-- Nút Gửi -->
                        <button type="submit" class="flex w-full justify-center rounded-lg bg-indigo-600 p-3 font-medium text-white hover:bg-indigo-700 transition-colors">
                            <span class="iconify mr-2 text-xl" data-icon="mdi-brain"></span>
                            Phân Tích Hình Ảnh
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tính Năng & Ví Dụ -->
            <div class="md:col-span-7 lg:col-span-8">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-md h-full">
                    <h3 class="mb-5 text-xl font-semibold text-indigo-700">Tính Năng Phân Tích Hình Ảnh</h3>

                    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 hover:border-indigo-300 transition-colors">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-200">
                                <span class="iconify text-indigo-600 text-2xl" data-icon="mdi-eye-outline"></span>
                            </div>
                            <h4 class="mb-2 text-lg font-semibold text-gray-800">Mô Tả Hình Ảnh</h4>
                            <p class="text-sm text-gray-600">Nhận mô tả chi tiết về hình ảnh, xác định đối tượng, cảnh, hành động và nhiều hơn nữa.</p>
                        </div>

                        <div class="rounded-lg border border-teal-100 bg-teal-50 p-4 hover:border-teal-300 transition-colors">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-teal-200">
                                <span class="iconify text-teal-600 text-2xl" data-icon="mdi-file-document-outline"></span>
                            </div>
                            <h4 class="mb-2 text-lg font-semibold text-gray-800">Trích Xuất Nội Dung</h4>
                            <p class="text-sm text-gray-600">Trích xuất chi tiết từ biểu đồ, sơ đồ, ảnh chụp màn hình và tài liệu trong hình ảnh.</p>
                        </div>

                        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 hover:border-blue-300 transition-colors">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-200">
                                <span class="iconify text-blue-600 text-2xl" data-icon="mdi-view-grid"></span>
                            </div>
                            <h4 class="mb-2 text-lg font-semibold text-gray-800">Nhiều Mô Hình</h4>
                            <p class="text-sm text-gray-600">Lựa chọn giữa các mô hình AI khác nhau (Grok Vision hoặc DeepSeek) cho các cách tiếp cận phân tích khác nhau.</p>
                        </div>

                        <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 hover:border-amber-300 transition-colors">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-amber-200">
                                <span class="iconify text-amber-600 text-2xl" data-icon="mdi-lightbulb-outline"></span>
                            </div>
                            <h4 class="mb-2 text-lg font-semibold text-gray-800">Tùy Chỉnh Câu Hỏi</h4>
                            <p class="text-sm text-gray-600">Điều chỉnh phân tích của bạn với các câu hỏi tùy chỉnh để tập trung vào các khía cạnh cụ thể của hình ảnh.</p>
                        </div>
                    </div>

                    <h4 class="mb-4 text-lg font-semibold text-indigo-700">Ví Dụ Câu Hỏi</h4>

                    <div class="space-y-3">
                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors example-prompt">
                            <h5 class="mb-1 font-medium text-gray-800 flex items-center">
                                <span class="iconify text-indigo-500 mr-2" data-icon="mdi-image-search"></span>
                                Mô Tả Chi Tiết
                            </h5>
                            <p class="text-sm text-gray-600">Mô tả hình ảnh này một cách chi tiết, bao gồm tất cả đối tượng, người, hành động và bối cảnh.</p>
                        </div>

                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors example-prompt">
                            <h5 class="mb-1 font-medium text-gray-800 flex items-center">
                                <span class="iconify text-indigo-500 mr-2" data-icon="mdi-text-recognition"></span>
                                Trích Xuất Văn Bản
                            </h5>
                            <p class="text-sm text-gray-600">Trích xuất và đọc tất cả văn bản hiển thị trong hình ảnh này, giữ nguyên định dạng ban đầu nếu có thể.</p>
                        </div>

                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors example-prompt">
                            <h5 class="mb-1 font-medium text-gray-800 flex items-center">
                                <span class="iconify text-indigo-500 mr-2" data-icon="mdi-chart-bar"></span>
                                Phân Tích Dữ Liệu
                            </h5>
                            <p class="text-sm text-gray-600">Hình ảnh này chứa biểu đồ/đồ thị. Phân tích dữ liệu và cung cấp những hiểu biết và xu hướng chính được hiển thị.</p>
                        </div>

                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors example-prompt">
                            <h5 class="mb-1 font-medium text-gray-800 flex items-center">
                                <span class="iconify text-indigo-500 mr-2" data-icon="mdi-book-open-variant"></span>
                                Nội Dung Giáo Dục
                            </h5>
                            <p class="text-sm text-gray-600">Phân tích sơ đồ/bảng tính giáo dục này và giải thích các khái niệm mà nó đang dạy.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/ai-dashboard/vision_analysis_main.js') }}"></script>
@endpush
