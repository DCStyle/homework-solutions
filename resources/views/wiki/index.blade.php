@extends('layouts.app')

@section('title', 'Hệ Thống Hỏi Đáp Thông Minh')
@section('description', 'Nền tảng hỏi đáp thông minh được hỗ trợ bởi trí tuệ nhân tạo')

@section('content')
<div class="py-8 bg-gradient-to-r from-blue-200 to-cyan-200">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 mb-3">Hỏi Đáp Thông Minh</h1>
            <p class="max-w-2xl mx-auto text-gray-600">
                Khám phá kiến thức, tìm kiếm câu trả lời hoặc đặt câu hỏi mới về bất kỳ chủ đề nào bạn quan tâm.
            </p>
        </div>

        <!-- Search Form - Prominent placement -->
        @include('wiki.partials.search')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Sidebar -->
            <div class="lg:col-span-3">
                <div class="sticky top-8 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                        <div class="p-5 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                                <span class="iconify mr-2" data-icon="mdi-folder-multiple-outline" data-width="22"></span>
                                Danh Mục
                            </h2>
                        </div>
                        <div class="p-4">
                            @include('wiki.partials.sidebar')
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-6">
                <!-- Chat Interface with improved styling -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 mb-6">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <span class="iconify mr-2" data-icon="mdi-message-text-outline" data-width="22"></span>
                            Trợ Lý AI
                        </h2>
                        <div class="flex items-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="iconify mr-1" data-icon="mdi-circle" data-width="8"></span>
                            Đang hoạt động
                        </span>
                        </div>
                    </div>
                    <div class="p-5">
                        @include('wiki.partials.chat')
                    </div>
                </div>

                <!-- Featured Content Section -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="p-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <span class="iconify mr-2" data-icon="mdi-lightbulb-outline" data-width="22"></span>
                            Khám Phá Kiến Thức
                        </h2>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Featured Category Cards -->
                            @forelse($categories->take(4) as $category)
                                <a href="{{ route('categories.show', $category->slug) }}" class="block rounded-lg border border-gray-100 p-5 hover:bg-gray-50 hover:border-gray-200 transition-all">
                                    <h3 class="font-medium text-indigo-600 mb-1">{{ $category->name }}</h3>
                                    <p class="text-sm text-gray-500 line-clamp-2">{!! $category->description ?? 'Khám phá các câu hỏi và kiến thức trong danh mục này' !!}</p>
                                </a>
                            @empty
                                <div class="col-span-2 text-center py-4 text-gray-500">
                                    <span class="iconify block mx-auto mb-2" data-icon="mdi-folder-outline" data-width="30"></span>
                                    Chưa có danh mục nào được tạo.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="lg:col-span-3">
                <!-- Latest Questions -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 mb-6">
                    <div class="p-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <span class="iconify mr-2" data-icon="mdi-clock-outline" data-width="22"></span>
                            Câu Hỏi Mới Nhất
                        </h2>
                    </div>

                    <div>
                        <ul class="divide-y divide-gray-100">
                            @forelse($latestQuestions as $question)
                                <li>
                                    <a href="{{ route('wiki.show', [$question->category->slug, $question->slug]) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                                        <p class="font-medium text-indigo-600 text-sm line-clamp-1">{{ $question->title }}</p>
                                        <div class="flex items-center mt-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center">
                                            <span class="iconify mr-1" data-icon="mdi-account" data-width="14"></span>
                                            {{ $question->user->name }}
                                        </span>
                                            <span class="mx-2">•</span>
                                            <span class="inline-flex items-center">
                                            <span class="iconify mr-1" data-icon="mdi-calendar" data-width="14"></span>
                                            {{ $question->created_at->diffForHumans() }}
                                        </span>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="p-4 text-center text-gray-500">
                                    <span class="iconify block mx-auto mb-2" data-icon="mdi-help-circle-outline" data-width="24"></span>
                                    Chưa có câu hỏi nào.
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="p-4 bg-gray-50 border-t border-gray-100 text-center">
                        <a href="{{ route('wiki.feed') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                            Xem tất cả câu hỏi
                            <span class="iconify ml-1" data-icon="mdi-arrow-right" data-width="16"></span>
                        </a>
                    </div>
                </div>

                <!-- Trending Questions -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="p-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <span class="iconify mr-2" data-icon="mdi-trending-up" data-width="22"></span>
                            Câu Hỏi Nổi Bật
                        </h2>
                    </div>

                    <div>
                        <ul class="divide-y divide-gray-100">
                            @forelse($trendingQuestions as $question)
                                <li>
                                    <a href="{{ route('wiki.show', [$question->category->slug, $question->slug]) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                                        <p class="font-medium text-indigo-600 text-sm line-clamp-1">{{ $question->title }}</p>
                                        <div class="flex items-center mt-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center">
                                            <span class="iconify mr-1" data-icon="mdi-eye-outline" data-width="14"></span>
                                            {{ $question->views }} lượt xem
                                        </span>
                                            <span class="mx-2">•</span>
                                            <span class="inline-flex items-center">
                                            <span class="iconify mr-1" data-icon="mdi-comment-outline" data-width="14"></span>
                                            {{ $question->comments->count() }} bình luận
                                        </span>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li class="p-4 text-center text-gray-500">
                                    <span class="iconify block mx-auto mb-2" data-icon="mdi-chart-line" data-width="24"></span>
                                    Chưa có câu hỏi nổi bật.
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="p-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-t border-gray-100">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-2">Bạn có câu hỏi chưa được giải đáp?</p>
                            <a href="{{ route('wiki.feed') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                Xem tất cả câu hỏi
                                <span class="iconify ml-1" data-icon="mdi-arrow-right" data-width="16"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
