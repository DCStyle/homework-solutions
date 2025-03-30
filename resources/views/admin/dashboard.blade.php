@extends('admin_layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">Dashboard</h2>
                    <p class="mt-1 text-white/90">Tổng quan về website của bạn</p>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 h-64 w-64 rounded-full bg-white/5"></div>
        </div>

        <!-- Statistics Overview -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <span class="iconify mr-2 text-primary" data-icon="mdi-chart-areaspline"></span>
                Thống Kê Tổng Quan
            </h3>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <!-- Users Card -->
                <div class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
                            <span class="iconify text-2xl text-primary" data-icon="mdi-account-outline"></span>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-black">{{ number_format($userCount) }}</h4>
                            <p class="text-sm font-medium text-gray-600 group-hover:text-primary transition-colors">Thành viên</p>
                        </div>
                    </div>
                </div>

                <!-- Categories Card -->
                <div class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-indigo-500/50 transition-all duration-200 transform hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 group-hover:bg-indigo-200 transition-colors">
                            <span class="iconify text-2xl text-indigo-600" data-icon="mdi-folder-outline"></span>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-black">{{ number_format($categoryCount) }}</h4>
                            <p class="text-sm font-medium text-gray-600 group-hover:text-indigo-600 transition-colors">Danh mục</p>
                        </div>
                    </div>
                </div>

                <!-- Groups Card -->
                <div class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-violet-500/50 transition-all duration-200 transform hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-violet-100 group-hover:bg-violet-200 transition-colors">
                            <span class="iconify text-2xl text-violet-600" data-icon="mdi-text-long"></span>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-black">{{ number_format($groupCount) }}</h4>
                            <p class="text-sm font-medium text-gray-600 group-hover:text-violet-600 transition-colors">Môn học</p>
                        </div>
                    </div>
                </div>

                <!-- Books Card -->
                <div class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-blue-500/50 transition-all duration-200 transform hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 group-hover:bg-blue-200 transition-colors">
                            <span class="iconify text-2xl text-blue-600" data-icon="mdi-book-outline"></span>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-black">{{ number_format($bookCount) }}</h4>
                            <p class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition-colors">Sách</p>
                        </div>
                    </div>
                </div>

                <!-- Chapters Card -->
                <div class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-amber-500/50 transition-all duration-200 transform hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 group-hover:bg-amber-200 transition-colors">
                            <span class="iconify text-2xl text-amber-600" data-icon="mdi-bookmark-multiple-outline"></span>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-black">{{ number_format($chapterCount) }}</h4>
                            <p class="text-sm font-medium text-gray-600 group-hover:text-amber-600 transition-colors">Chương</p>
                        </div>
                    </div>
                </div>

                <!-- Posts Card -->
                <div class="stats-card group rounded-lg border border-stroke bg-white p-6 shadow-md hover:shadow-lg hover:border-green-500/50 transition-all duration-200 transform hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 group-hover:bg-green-200 transition-colors">
                            <span class="iconify text-2xl text-green-600" data-icon="mdi-newspaper"></span>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-black">{{ number_format($postCount) }}</h4>
                            <p class="text-sm font-medium text-gray-600 group-hover:text-green-600 transition-colors">Bài viết</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Section -->
        <div>
            <!-- Quick Actions -->
            <div class="rounded-xl border border-stroke bg-white p-6 shadow-md relative overflow-hidden">
                <div class="mb-6 flex items-center justify-between">
                    <h4 class="text-xl font-semibold text-black flex items-center">
                        <span class="iconify mr-2 text-primary" data-icon="mdi-lightning-bolt-outline"></span>
                        Truy Cập Nhanh
                    </h4>
                </div>

                <!-- Decorative background pattern -->
                <div class="absolute top-0 right-0 opacity-5 pointer-events-none">
                    <span class="iconify w-24 h-24 text-primary" data-icon="mdi-lightning-bolt-outline"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 relative z-10">
                    <a href="{{ route('admin.posts.create') }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-primary hover:bg-primary/5 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-all duration-200">
                            <span class="iconify text-2xl text-primary" data-icon="mdi-plus"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-primary transition-colors">Tạo Bài Viết</h5>
                            <p class="text-sm text-gray-500">Thêm bài viết mới</p>
                        </div>
                        <span class="iconify text-gray-400 group-hover:text-primary transition-colors" data-icon="mdi-chevron-right"></span>
                    </a>

                    <a href="{{ route('admin.categories.create') }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-indigo-500 hover:bg-indigo-50/30 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 group-hover:bg-indigo-200 transition-all duration-200">
                            <span class="iconify text-2xl text-indigo-600" data-icon="mdi-folder-plus-outline"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-indigo-600 transition-colors">Tạo Danh Mục</h5>
                            <p class="text-sm text-gray-500">Thêm danh mục mới</p>
                        </div>
                        <span class="iconify text-gray-400 group-hover:text-indigo-600 transition-colors" data-icon="mdi-chevron-right"></span>
                    </a>

                    <a href="{{ route('admin.books.create') }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-blue-500 hover:bg-blue-50/30 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 group-hover:bg-blue-200 transition-all duration-200">
                            <span class="iconify text-2xl text-blue-600" data-icon="mdi-book-plus-outline"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-blue-600 transition-colors">Tạo Sách</h5>
                            <p class="text-sm text-gray-500">Thêm sách mới</p>
                        </div>
                        <span class="iconify text-gray-400 group-hover:text-blue-600 transition-colors" data-icon="mdi-chevron-right"></span>
                    </a>

                    <a href="{{ route('admin.ai-dashboard.playground') }}" class="group flex items-center rounded-xl border border-stroke p-4 hover:border-amber-500 hover:bg-amber-50/30 transition-all duration-200 transform hover:-translate-x-1">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 group-hover:bg-amber-200 transition-all duration-200">
                            <span class="iconify text-2xl text-amber-600" data-icon="mdi-robot"></span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h5 class="text-md font-medium text-black group-hover:text-amber-600 transition-colors">AI Playground</h5>
                            <p class="text-sm text-gray-500">Tạo nội dung với AI</p>
                        </div>
                        <span class="iconify text-gray-400 group-hover:text-amber-600 transition-colors" data-icon="mdi-chevron-right"></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Optional additional styles */
        .stats-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
@endpush
