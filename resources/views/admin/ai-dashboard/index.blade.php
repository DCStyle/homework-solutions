@extends('admin_layouts.admin')

@section('content')
    <div class="space-y-6">
        <!-- Header Section with Solid Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">Bảng Điều Khiển AI</h2>
                    <p class="mt-1 text-white/90">Tạo và tối ưu hóa nội dung SEO bằng AI</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.ai-dashboard.playground') }}" class="inline-flex items-center justify-center gap-2.5 rounded-lg bg-white py-3 px-5 text-center font-medium text-primary hover:bg-gray-100 transition-all duration-200 transform hover:scale-105 shadow-md lg:px-6">
                        <span class="iconify" data-icon="mdi-code-json"></span>
                        Khu vực thử nghiệm
                    </a>

                    <a href="{{ route('admin.ai-dashboard.stats') }}" class="inline-flex items-center justify-center gap-2.5 rounded-lg bg-white py-3 px-5 text-center font-medium text-primary hover:bg-gray-100 transition-all duration-200 transform hover:scale-105 shadow-md lg:px-6">
                        <span class="iconify" data-icon="mdi-thermostat"></span>
                        Thống kê chi tiết
                    </a>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 h-64 w-64 rounded-full bg-white/5"></div>
        </div>

        <!-- SEO Status Overview Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'posts']) }}" class="group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-black">{{ number_format($missingData['posts_no_meta']) }}</h3>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">Bài viết thiếu meta</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
                        <span class="iconify text-primary text-2xl" data-icon="mdi-file-document-outline"></span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">{{ $seoProgress['posts']['percentage'] }}% Hoàn thành</span>
                </div>
            </a>

            <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'chapters']) }}" class="group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-black">{{ number_format($missingData['chapters_no_desc']) }}</h3>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">Chương thiếu mô tả</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
                        <span class="iconify text-primary text-2xl" data-icon="mdi-book-open-variant"></span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">{{ $seoProgress['chapters']['percentage'] }}% Hoàn thành</span>
                </div>
            </a>

            <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'books']) }}" class="group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-black">{{ number_format($missingData['books_no_desc']) }}</h3>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">Sách thiếu mô tả</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
                        <span class="iconify text-primary text-2xl" data-icon="mdi-book-outline"></span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">{{ $seoProgress['books']['percentage'] }}% Hoàn thành</span>
                </div>
            </a>

            <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'book_groups']) }}" class="group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-black">{{ number_format($missingData['book_groups_no_desc']) }}</h3>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">Nhóm thiếu mô tả</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
                        <span class="iconify text-primary text-2xl" data-icon="mdi-folder-multiple-outline"></span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">{{ $seoProgress['book_groups']['percentage'] }}% Hoàn thành</span>
                </div>
            </a>
        </div>

        <!-- SEO Progress -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- SEO Progress Chart -->
            <div class="rounded-xl border border-stroke bg-white p-6 shadow-md relative overflow-hidden">
                <div class="mb-6 flex items-center justify-between">
                    <h4 class="text-xl font-semibold text-black">Tiến Độ Tối Ưu Hóa SEO</h4>
                    <span class="iconify text-primary text-2xl" data-icon="mdi-chart-line"></span>
                </div>

                <!-- Decorative background pattern -->
                <div class="absolute top-0 right-0 opacity-5 pointer-events-none">
                    <span class="iconify text-9xl text-primary" data-icon="mdi-chart-box-outline"></span>
                </div>

                <div class="mt-4 space-y-6 relative z-10">
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-sm font-medium flex items-center">
                                <span class="iconify mr-2 text-primary" data-icon="mdi-file-document-outline"></span>
                                Bài Viết
                            </p>
                            <p class="text-sm font-medium">{{ $seoProgress['posts']['percentage'] }}%</p>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-primary transition-all duration-1000" style="width: {{ $seoProgress['posts']['percentage'] }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $seoProgress['posts']['completed'] }} / {{ $seoProgress['posts']['total'] }} đã tối ưu hóa</p>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-sm font-medium flex items-center">
                                <span class="iconify mr-2 text-indigo-500" data-icon="mdi-book-open-variant"></span>
                                Chương Sách
                            </p>
                            <p class="text-sm font-medium">{{ $seoProgress['chapters']['percentage'] }}%</p>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-indigo-500 transition-all duration-1000" style="width: {{ $seoProgress['chapters']['percentage'] }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $seoProgress['chapters']['completed'] }} / {{ $seoProgress['chapters']['total'] }} đã tối ưu hóa</p>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-sm font-medium flex items-center">
                                <span class="iconify mr-2 text-green-500" data-icon="mdi-book-outline"></span>
                                Sách
                            </p>
                            <p class="text-sm font-medium">{{ $seoProgress['books']['percentage'] }}%</p>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-green-500 transition-all duration-1000" style="width: {{ $seoProgress['books']['percentage'] }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $seoProgress['books']['completed'] }} / {{ $seoProgress['books']['total'] }} đã tối ưu hóa</p>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-sm font-medium flex items-center">
                                <span class="iconify mr-2 text-amber-500" data-icon="mdi-folder-multiple-outline"></span>
                                Nhóm Sách
                            </p>
                            <p class="text-sm font-medium">{{ $seoProgress['book_groups']['percentage'] }}%</p>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-amber-500 transition-all duration-1000" style="width: {{ $seoProgress['book_groups']['percentage'] }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $seoProgress['book_groups']['completed'] }} / {{ $seoProgress['book_groups']['total'] }} đã tối ưu hóa</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-xl border border-stroke bg-white p-6 shadow-md relative overflow-hidden">
                <div class="mb-6 flex items-center justify-between">
                    <h4 class="text-xl font-semibold text-black">Hành Động Nhanh</h4>
                    <span class="iconify text-primary text-2xl" data-icon="mdi-lightning-bolt-outline"></span>
                </div>

                <!-- Decorative background pattern -->
                <div class="absolute top-0 right-0 opacity-5 pointer-events-none">
                    <span class="iconify text-9xl text-primary" data-icon="mdi-lightning-bolt"></span>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 relative z-10">
                    <a href="{{ route('admin.ai-dashboard.playground') }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-primary hover:bg-primary/5 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-all duration-200">
                            <span class="iconify text-2xl text-primary" data-icon="mdi-code-json"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-primary transition-colors">Khu vực thử nghiệm</h5>
                            <p class="text-sm text-gray-500">Thử nghiệm với nội dung tạo bởi AI</p>
                        </div>
                        <span class="iconify text-gray-400 group-hover:text-primary transition-colors" data-icon="mdi-chevron-right"></span>
                    </a>

                    <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'posts']) }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-primary hover:bg-primary/5 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 group-hover:bg-green-200 transition-all duration-200">
                            <span class="iconify text-2xl text-green-600" data-icon="mdi-file-document-edit-outline"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-primary transition-colors">Tạo Meta cho Bài Viết</h5>
                            <p class="text-sm text-gray-500">Tối ưu hóa SEO cho bài viết</p>
                        </div>
                        <span class="iconify text-gray-400 group-hover:text-primary transition-colors" data-icon="mdi-chevron-right"></span>
                    </a>

                    <a href="{{ route('admin.ai-dashboard.vision') }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-primary hover:bg-primary/5 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 group-hover:bg-amber-200 transition-all duration-200">
                            <span class="iconify text-2xl text-amber-600" data-icon="mdi-eye-outline"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-primary transition-colors">Phân Tích Hình Ảnh</h5>
                            <p class="text-sm text-gray-500">Phân tích hình ảnh bằng AI</p>
                        </div>
                        <span class="iconify text-gray-400 group-hover:text-primary transition-colors" data-icon="mdi-chevron-right"></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Custom Prompts -->
        <div class="rounded-xl border border-stroke bg-white p-6 shadow-md relative overflow-hidden">
            <div class="mb-6 flex items-center justify-between relative z-10">
                <h4 class="text-xl font-semibold text-black flex items-center">
                    <span class="iconify text-primary mr-2" data-icon="mdi-text-box-check-outline"></span>
                    Mẫu Đã Lưu
                </h4>
                <button
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#createPromptModal"
                    class="flex items-center justify-center rounded-lg border border-primary py-2 px-5 text-center font-medium text-primary hover:bg-primary hover:text-white transition-all duration-200 focus:ring-2 focus:ring-primary/30 sm:px-4 md:px-6"
                >
                    <span class="iconify mr-2" data-icon="mdi-plus"></span>
                    Mẫu Mới
                </button>
            </div>

            <!-- Decorative background pattern -->
            <div class="absolute top-0 right-0 opacity-5 pointer-events-none">
                <span class="iconify text-9xl text-primary" data-icon="mdi-text-box-multiple-outline"></span>
            </div>

            @if($prompts->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 relative z-10">
                    <div class="mb-4 rounded-full bg-gray-100 p-4">
                        <span class="iconify text-4xl text-gray-500" data-icon="mdi-text-box-plus-outline"></span>
                    </div>
                    <p class="text-gray-500 mb-4">No custom prompts have been created yet.</p>
                    <button
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#createPromptModal"
                        class="inline-flex items-center justify-center rounded-lg border border-primary py-2 px-5 text-center font-medium text-primary hover:bg-primary hover:text-white transition-all duration-200 focus:ring-2 focus:ring-primary/30"
                    >
                        Tạo mẫu đầu tiên của bạn
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 relative z-10">
                    @foreach($prompts as $prompt)
                        <div class="group rounded-xl border border-stroke bg-white p-5 shadow-sm hover:shadow-md hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                    {{ $prompt->content_type_label }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.ai-dashboard.playground', ['prompt_id' => $prompt->id]) }}" class="text-gray-500 hover:text-primary transition-colors">
                                        <span class="iconify text-lg" data-icon="mdi-play-circle-outline"></span>
                                    </a>
                                    <button
                                        type="button"
                                        class="delete-prompt text-gray-500 hover:text-red-500 transition-colors"
                                        data-prompt-id="{{ $prompt->id }}"
                                    >
                                        <span class="iconify text-lg" data-icon="mdi-delete-outline"></span>
                                    </button>
                                </div>
                            </div>
                            <h4 class="mt-3 mb-2 text-lg font-semibold text-black group-hover:text-primary transition-colors">{{ $prompt->name }}</h4>
                            <p class="text-sm text-gray-500 line-clamp-2 h-10">{{ $prompt->prompt_excerpt }}</p>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-xs text-gray-500 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-clock-outline"></span>
                                    {{ $prompt->formatted_created_at }}
                                </span>
                                <span class="text-xs font-medium text-black flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-robot-outline"></span>
                                    {{ $prompt->ai_model ?? 'Any model' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('admin.ai-dashboard.playground') }}" class="inline-flex items-center text-sm font-medium text-primary hover:underline">
                        Xem tất cả các mẫu trong "Khu vực Thử Nghiệm"
                        <span class="iconify ml-1" data-icon="mdi-arrow-right"></span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Create Prompt Modal -->
    <div class="modal fade" id="createPromptModal" tabindex="-1" aria-labelledby="createPromptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-xl">
                <div class="modal-header bg-primary text-white rounded-top">
                    <h5 class="modal-title font-medium flex items-center" id="createPromptModalLabel">
                        <span class="iconify mr-2" data-icon="mdi-plus-circle-outline"></span>
                        Tạo Mẫu Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createPromptForm" action="{{ route('admin.ai-dashboard.save-prompt') }}" method="POST" class="p-2">
                    @csrf
                    <div class="px-4 py-4 space-y-4">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-gray-700">Tên Mẫu</label>
                            <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <span class="iconify" data-icon="mdi-format-title"></span>
                            </span>
                                <input type="text" id="name" name="name" class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-4 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30" required>
                            </div>
                        </div>

                        <div>
                            <label for="content_type" class="mb-2 block text-sm font-medium text-gray-700">Loại Nội Dung</label>
                            <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <span class="iconify" data-icon="mdi-format-list-bulleted-type"></span>
                            </span>
                                <select data-plugin-select2 id="content_type" name="content_type" class="w-full appearance-none rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-10 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30">
                                    <option value="posts">Bài Viết</option>
                                    <option value="chapters">Chương Sách</option>
                                    <option value="books">Sách</option>
                                    <option value="book_groups">Nhóm Sách</option>
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <span class="iconify" data-icon="mdi-chevron-down"></span>
                            </span>
                            </div>
                        </div>

                        <div>
                            <label for="ai_model" class="mb-2 block text-sm font-medium text-gray-700">Mô Hình AI (Tùy Chọn)</label>
                            <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <span class="iconify" data-icon="mdi-robot-outline"></span>
                            </span>
                                <select data-plugin-select2 id="ai_model" name="ai_model" class="w-full appearance-none rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-10 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30">
                                    <option value="">Bất Kỳ Mô Hình</option>
                                    <option value="grok-2">Grok-2</option>
                                    <option value="grok-2-latest">Grok-2 Latest</option>
                                    <option value="deepseek-v3">DeepSeek</option>
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <span class="iconify" data-icon="mdi-chevron-down"></span>
                            </span>
                            </div>
                        </div>

                        <div>
                            <label for="system_message" class="mb-2 block text-sm font-medium text-gray-700">Thông Báo Hệ Thống (Tùy Chọn, cho DeepSeek)</label>
                            <div class="relative">
                                <textarea id="system_message" name="system_message" rows="3" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30"></textarea>
                            </div>
                        </div>

                        <div>
                            <label for="prompt_text" class="mb-2 block text-sm font-medium text-gray-700">Mẫu Prompt</label>
                            <div class="relative">
                                <textarea id="prompt_text" name="prompt_text" rows="6" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30" required></textarea>
                            </div>
                            <div class="mt-2">
                                <small class="text-xs text-gray-500 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                    Biến có sẵn: @verbatim@{{title}}, @{{name}}, @{{chapter_name}}, @{{book_name}}, @{{group_name}}, @{{category_name}}@endverbatim
                                </small>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="mb-2 block text-sm font-medium text-gray-700">Mô Tả (Tùy Chọn)</label>
                            <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <span class="iconify" data-icon="mdi-text-box-outline"></span>
                            </span>
                                <textarea id="description" name="description" rows="2" class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-4 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-t border-gray-200">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <span class="iconify align-middle" data-icon="mdi-close"></span>
                            Hủy
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span class="iconify align-middle" data-icon="mdi-check"></span>
                            Tạo Mẫu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/ai-dashboard/index_main.js') }}"></script>
@endpush
