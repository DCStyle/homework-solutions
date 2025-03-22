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

        <!-- SEO Status Overview Cards - With Loading States -->
        <div id="stats-overview" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Posts Stats Card (With Skeleton Loading) -->
            <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'posts']) }}" class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-black skeleton-text" id="posts-count">--</h3>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">Bài viết thiếu meta</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
                        <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M14,17H7V15H14M17,13H7V11H17M17,9H7V7H17M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z" /></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full skeleton-text" id="posts-percentage">--%</span>
                </div>
            </a>

            <!-- Chapters Stats Card -->
            <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'chapters']) }}" class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-black skeleton-text" id="chapters-count">--</h3>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">Chương thiếu mô tả</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
                        <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M19 2L14 6.5V17.5L19 13V2M6.5 5C4.55 5 2.45 5.4 1 6.5V21.16C1 21.41 1.25 21.66 1.5 21.66C1.6 21.66 1.65 21.59 1.75 21.59C3.1 20.94 5.05 20.5 6.5 20.5C8.45 20.5 10.55 20.9 12 22C13.35 21.15 15.8 20.5 17.5 20.5C19.15 20.5 20.85 20.81 22.25 21.56C22.35 21.61 22.4 21.59 22.5 21.59C22.75 21.59 23 21.34 23 21.09V6.5C22.4 6.05 21.75 5.75 21 5.5V19C19.9 18.65 18.7 18.5 17.5 18.5C15.8 18.5 13.35 19.15 12 20V6.5C10.55 5.4 8.45 5 6.5 5Z" /></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full skeleton-text" id="chapters-percentage">--%</span>
                </div>
            </a>

            <!-- Books Stats Card -->
            <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'books']) }}" class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-black skeleton-text" id="books-count">--</h3>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">Sách thiếu mô tả</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
                        <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M18,22A2,2 0 0,0 20,20V4C20,2.89 19.1,2 18,2H12V9L9.5,7.5L7,9V2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18Z" /></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full skeleton-text" id="books-percentage">--%</span>
                </div>
            </a>

            <!-- Book Groups Stats Card -->
            <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'book_groups']) }}" class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-black skeleton-text" id="groups-count">--</h3>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">Nhóm thiếu mô tả</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
                        <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M4 4C2.89 4 2 4.89 2 6V18A2 2 0 0 0 4 20H20A2 2 0 0 0 22 18V8C22 6.89 21.1 6 20 6H12L10 4H4Z" /></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full skeleton-text" id="groups-percentage">--%</span>
                </div>
            </a>
        </div>

        <!-- SEO Progress -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- SEO Progress Chart -->
            <div class="rounded-xl border border-stroke bg-white p-6 shadow-md relative overflow-hidden">
                <div class="mb-6 flex items-center justify-between">
                    <h4 class="text-xl font-semibold text-black">Tiến Độ Tối Ưu Hóa SEO</h4>
                    <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M16,11.78L20.24,4.45L21.97,5.45L16.74,14.5L10.23,10.75L5.46,19H22V21H2V3H4V17.54L9.5,8L16,11.78Z" /></svg>
                </div>

                <!-- Decorative background pattern -->
                <div class="absolute top-0 right-0 opacity-5 pointer-events-none">
                    <svg class="w-24 h-24 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M16,11.78L20.24,4.45L21.97,5.45L16.74,14.5L10.23,10.75L5.46,19H22V21H2V3H4V17.54L9.5,8L16,11.78Z" /></svg>
                </div>

                <div class="mt-4 space-y-6 relative z-10" id="progress-bars">
                    <!-- Posts Progress (Skeleton Loading) -->
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-2 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M14,17H7V15H14M17,13H7V11H17M17,9H7V7H17M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z" /></svg>
                                Bài Viết
                            </p>
                            <p class="text-sm font-medium skeleton-text" id="posts-progress-percentage">--%</p>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-primary transition-all duration-1000 skeleton-progress" id="posts-progress-bar" style="width: 0%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 skeleton-text" id="posts-progress-numbers">-- / -- đã tối ưu hóa</p>
                    </div>

                    <!-- Chapters Progress -->
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" viewBox="0 0 24 24"><path fill="currentColor" d="M19 2L14 6.5V17.5L19 13V2M6.5 5C4.55 5 2.45 5.4 1 6.5V21.16C1 21.41 1.25 21.66 1.5 21.66C1.6 21.66 1.65 21.59 1.75 21.59C3.1 20.94 5.05 20.5 6.5 20.5C8.45 20.5 10.55 20.9 12 22C13.35 21.15 15.8 20.5 17.5 20.5C19.15 20.5 20.85 20.81 22.25 21.56C22.35 21.61 22.4 21.59 22.5 21.59C22.75 21.59 23 21.34 23 21.09V6.5C22.4 6.05 21.75 5.75 21 5.5V19C19.9 18.65 18.7 18.5 17.5 18.5C15.8 18.5 13.35 19.15 12 20V6.5C10.55 5.4 8.45 5 6.5 5Z" /></svg>
                                Chương Sách
                            </p>
                            <p class="text-sm font-medium skeleton-text" id="chapters-progress-percentage">--%</p>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-indigo-500 transition-all duration-1000 skeleton-progress" id="chapters-progress-bar" style="width: 0%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 skeleton-text" id="chapters-progress-numbers">-- / -- đã tối ưu hóa</p>
                    </div>

                    <!-- Books Progress -->
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" viewBox="0 0 24 24"><path fill="currentColor" d="M18,22A2,2 0 0,0 20,20V4C20,2.89 19.1,2 18,2H12V9L9.5,7.5L7,9V2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18Z" /></svg>
                                Sách
                            </p>
                            <p class="text-sm font-medium skeleton-text" id="books-progress-percentage">--%</p>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-green-500 transition-all duration-1000 skeleton-progress" id="books-progress-bar" style="width: 0%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 skeleton-text" id="books-progress-numbers">-- / -- đã tối ưu hóa</p>
                    </div>

                    <!-- Book Groups Progress -->
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 mr-2 text-amber-500" viewBox="0 0 24 24"><path fill="currentColor" d="M4 4C2.89 4 2 4.89 2 6V18A2 2 0 0 0 4 20H20A2 2 0 0 0 22 18V8C22 6.89 21.1 6 20 6H12L10 4H4Z" /></svg>
                                Nhóm Sách
                            </p>
                            <p class="text-sm font-medium skeleton-text" id="groups-progress-percentage">--%</p>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-amber-500 transition-all duration-1000 skeleton-progress" id="groups-progress-bar" style="width: 0%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 skeleton-text" id="groups-progress-numbers">-- / -- đã tối ưu hóa</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-xl border border-stroke bg-white p-6 shadow-md relative overflow-hidden">
                <div class="mb-6 flex items-center justify-between">
                    <h4 class="text-xl font-semibold text-black">Hành Động Nhanh</h4>
                    <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M11 15H6L13 1V9H18L11 23V15Z" /></svg>
                </div>

                <!-- Decorative background pattern -->
                <div class="absolute top-0 right-0 opacity-5 pointer-events-none">
                    <svg class="w-24 h-24 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M11 15H6L13 1V9H18L11 23V15Z" /></svg>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 relative z-10">
                    <a href="{{ route('admin.ai-dashboard.playground') }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-primary hover:bg-primary/5 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-all duration-200">
                            <span class="iconify" data-icon="mdi-code-json"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-primary transition-colors">Khu vực thử nghiệm</h5>
                            <p class="text-sm text-gray-500">Thử nghiệm với nội dung tạo bởi AI</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors" viewBox="0 0 24 24"><path fill="currentColor" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" /></svg>
                    </a>

                    <a href="{{ route('admin.ai-dashboard.stats', ['type' => 'posts']) }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-primary hover:bg-primary/5 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 group-hover:bg-green-200 transition-all duration-200">
                            <span class="iconify text-2xl text-green-600" data-icon="mdi-file-document-edit-outline"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-primary transition-colors">Tạo Meta cho Bài Viết</h5>
                            <p class="text-sm text-gray-500">Tối ưu hóa SEO cho bài viết</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors" viewBox="0 0 24 24"><path fill="currentColor" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" /></svg>
                    </a>

                    <a href="{{ route('admin.ai-dashboard.vision') }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-primary hover:bg-primary/5 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 group-hover:bg-amber-200 transition-all duration-200">
                            <span class="iconify text-2xl text-amber-600" data-icon="mdi-eye-outline"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-primary transition-colors">Phân Tích Hình Ảnh</h5>
                            <p class="text-sm text-gray-500">Phân tích hình ảnh bằng AI</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors" viewBox="0 0 24 24"><path fill="currentColor" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" /></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Custom Prompts Section - Lazy Loaded -->
        <div class="rounded-xl border border-stroke bg-white p-6 shadow-md relative overflow-hidden" id="custom-prompts-container">
            <div class="mb-6 flex items-center justify-between relative z-10">
                <h4 class="text-xl font-semibold text-black flex items-center">
                    <svg class="w-5 h-5 text-primary mr-2" viewBox="0 0 24 24"><path fill="currentColor" d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3M19 19H5V5H19V19M17 17H7V7H17V17M15 15H9V9H15V15" /></svg>
                    Mẫu Đã Lưu
                </h4>
                <button
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#createPromptModal"
                    class="flex items-center justify-center rounded-lg border border-primary py-2 px-5 text-center font-medium text-primary hover:bg-primary hover:text-white transition-all duration-200 focus:ring-2 focus:ring-primary/30 sm:px-4 md:px-6"
                >
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24"><path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" /></svg>
                    Mẫu Mới
                </button>
            </div>

            <!-- Decorative background pattern -->
            <div class="absolute top-0 right-0 opacity-5 pointer-events-none">
                <svg class="w-24 h-24 text-primary" viewBox="0 0 24 24"><path fill="currentColor" d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3M19 19H5V5H19V19M17 17H7V7H17V17M15 15H9V9H15V15" /></svg>
            </div>

            <!-- Lazy loading container for prompts -->
            <div id="prompts-list-container" class="relative z-10">
                <!-- Loading state -->
                <div class="loading-state flex flex-col items-center justify-center py-8">
                    <div class="w-12 h-12 rounded-full border-4 border-primary border-t-transparent animate-spin mb-4"></div>
                    <p class="text-gray-500">Đang tải mẫu đã lưu...</p>
                </div>

                <!-- Prompts will be loaded here -->
                <div id="prompts-list" class="hidden">
                    @if($prompts->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="mb-4 rounded-full bg-gray-100 p-4">
                                <svg class="w-10 h-10 text-gray-500" viewBox="0 0 24 24"><path fill="currentColor" d="M19,20H5V4H7V7H17V4H19M12,2A1,1 0 0,1 13,3A1,1 0 0,1 12,4A1,1 0 0,1 11,3A1,1 0 0,1 12,2M19,2H14.82C14.4,0.84 13.3,0 12,0C10.7,0 9.6,0.84 9.18,2H5A2,2 0 0,0 3,4V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V4A2,2 0 0,0 19,2Z" /></svg>
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
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3">
                            @foreach($prompts as $prompt)
                                <div class="group rounded-xl border border-stroke bg-white p-5 shadow-sm hover:shadow-md hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                            {{ $prompt->content_type_label }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.ai-dashboard.playground', ['prompt_id' => $prompt->id]) }}" class="text-gray-500 hover:text-primary transition-colors">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M8,5.14V19.14L19,12.14L8,5.14Z" /></svg>
                                            </a>
                                            <button
                                                type="button"
                                                class="delete-prompt text-gray-500 hover:text-red-500 transition-colors"
                                                data-prompt-id="{{ $prompt->id }}"
                                            >
                                                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <h4 class="mt-3 mb-2 text-lg font-semibold text-black group-hover:text-primary transition-colors">{{ $prompt->name }}</h4>
                                    <p class="text-sm text-gray-500 line-clamp-2 h-10">{{ $prompt->prompt_excerpt }}</p>
                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="text-xs text-gray-500 flex items-center">
                                            <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24"><path fill="currentColor" d="M12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22C6.47,22 2,17.5 2,12A10,10 0 0,1 12,2M12.5,7V12.25L17,14.92L16.25,16.15L11,13V7H12.5Z" /></svg>
                                            {{ $prompt->formatted_created_at }}
                                        </span>
                                        <span class="text-xs font-medium text-black flex items-center">
                                            <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24"><path fill="currentColor" d="M12,2A2,2 0 0,1 14,4C14,4.74 13.6,5.39 13,5.73V7H14A7,7 0 0,1 21,14H22A1,1 0 0,1 23,15V18A1,1 0 0,1 22,19H21V20A2,2 0 0,1 19,22H5A2,2 0 0,1 3,20V19H2A1,1 0 0,1 1,18V15A1,1 0 0,1 2,14H3A7,7 0 0,1 10,7H11V5.73C10.4,5.39 10,4.74 10,4A2,2 0 0,1 12,2M7.5,13A2.5,2.5 0 0,0 5,15.5A2.5,2.5 0 0,0 7.5,18A2.5,2.5 0 0,0 10,15.5A2.5,2.5 0 0,0 7.5,13M16.5,13A2.5,2.5 0 0,0 14,15.5A2.5,2.5 0 0,0 16.5,18A2.5,2.5 0 0,0 19,15.5A2.5,2.5 0 0,0 16.5,13Z" /></svg>
                                            {{ $prompt->ai_model ?? 'Any model' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 text-center">
                            <a href="{{ route('admin.ai-dashboard.playground') }}" class="inline-flex items-center text-sm font-medium text-primary hover:underline">
                                Xem tất cả các mẫu trong "Khu vực Thử Nghiệm"
                                <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24"><path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" /></svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Prompt Modal -->
    <div class="modal fade" id="createPromptModal" tabindex="-1" aria-labelledby="createPromptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-xl">
                <div class="modal-header bg-primary text-white rounded-top">
                    <h5 class="modal-title font-medium flex items-center" id="createPromptModalLabel">
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24"><path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" /></svg>
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
                                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M5,4V7H10.5V19H13.5V7H19V4H5Z" /></svg>
                            </span>
                                <input type="text" id="name" name="name" class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-4 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30" required>
                            </div>
                        </div>

                        <div>
                            <label for="content_type" class="mb-2 block text-sm font-medium text-gray-700">Loại Nội Dung</label>
                            <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M7,5H21V7H7V5M7,13V11H21V13H7M4,4.5A1.5,1.5 0 0,1 5.5,6A1.5,1.5 0 0,1 4,7.5A1.5,1.5 0 0,1 2.5,6A1.5,1.5 0 0,1 4,4.5M4,10.5A1.5,1.5 0 0,1 5.5,12A1.5,1.5 0 0,1 4,13.5A1.5,1.5 0 0,1 2.5,12A1.5,1.5 0 0,1 4,10.5M7,19V17H21V19H7M4,16.5A1.5,1.5 0 0,1 5.5,18A1.5,1.5 0 0,1 4,19.5A1.5,1.5 0 0,1 2.5,18A1.5,1.5 0 0,1 4,16.5Z" /></svg>
                            </span>
                                <select data-plugin-select2 id="content_type" name="content_type" class="w-full appearance-none rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-10 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30">
                                    <option value="posts">Bài Viết</option>
                                    <option value="chapters">Chương Sách</option>
                                    <option value="books">Sách</option>
                                    <option value="book_groups">Nhóm Sách</option>
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z" /></svg>
                            </span>
                            </div>
                        </div>

                        <div>
                            <label for="ai_model" class="mb-2 block text-sm font-medium text-gray-700">Mô Hình AI (Tùy Chọn)</label>
                            <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M12,2A2,2 0 0,1 14,4C14,4.74 13.6,5.39 13,5.73V7H14A7,7 0 0,1 21,14H22A1,1 0 0,1 23,15V18A1,1 0 0,1 22,19H21V20A2,2 0 0,1 19,22H5A2,2 0 0,1 3,20V19H2A1,1 0 0,1 1,18V15A1,1 0 0,1 2,14H3A7,7 0 0,1 10,7H11V5.73C10.4,5.39 10,4.74 10,4A2,2 0 0,1 12,2M7.5,13A2.5,2.5 0 0,0 5,15.5A2.5,2.5 0 0,0 7.5,18A2.5,2.5 0 0,0 10,15.5A2.5,2.5 0 0,0 7.5,13M16.5,13A2.5,2.5 0 0,0 14,15.5A2.5,2.5 0 0,0 16.5,18A2.5,2.5 0 0,0 19,15.5A2.5,2.5 0 0,0 16.5,13Z" /></svg>
                            </span>
                                <select data-plugin-select2 id="ai_model" name="ai_model" class="w-full appearance-none rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-10 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30">
                                    @include('admin.ai-dashboard.partials.model-options')
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z" /></svg>
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
                                    <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24"><path fill="currentColor" d="M13,9H11V7H13M13,17H11V11H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" /></svg>
                                    Biến có sẵn: @verbatim@{{title}}, @{{name}}, @{{chapter_name}}, @{{book_name}}, @{{group_name}}, @{{category_name}}@endverbatim
                                </small>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="mb-2 block text-sm font-medium text-gray-700">Mô Tả (Tùy Chọn)</label>
                            <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M14,17H7V15H14M17,13H7V11H17M17,9H7V7H17M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z" /></svg>
                            </span>
                                <textarea id="description" name="description" rows="2" class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-4 font-medium outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-t border-gray-200">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <svg class="w-5 h-5 align-middle mr-1" viewBox="0 0 24 24"><path fill="currentColor" d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" /></svg>
                            Hủy
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-5 h-5 align-middle mr-1" viewBox="0 0 24 24"><path fill="currentColor" d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" /></svg>
                            Tạo Mẫu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/ai-dashboard/index_main.css') }}">
@endpush

@push('scripts')
    <script defer src="{{ asset('js/admin/ai-dashboard/index_main.js') }}"></script>
@endpush
