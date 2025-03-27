@extends('layouts.app')

@section('title', 'Wiki Feed')

@section('content')
    <div class="py-8 bg-gradient-to-r from-blue-100 to-cyan-100">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-900 mb-3">Câu hỏi mới nhất</h1>
                <p class="max-w-2xl mx-auto text-gray-600">
                    Khám phá những câu hỏi mới nhất từ cộng đồng.
                </p>
            </div>

            @include('wiki.partials.search')


            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 sticky top-8">
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

                {{-- Main Content (Feed Container) --}}
                <div class="lg:col-span-6">
                    {{-- Feed Questions List --}}
                    <div id="wiki-feed-container">
                        @forelse($feedQuestions as $question)
                            @include('wiki.partials.feed-item', ['question' => $question])
                        @empty
                            <div class="text-center py-10 bg-white rounded-xl shadow-sm border border-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-gray-500">Chưa có câu hỏi nào</p>
                                <a href="{{ route('wiki.index') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Hãy là người đầu tiên đặt câu hỏi
                                </a>
                            </div>
                        @endforelse
                    </div>

                    {{-- Loading indicator for infinite scroll - make sure it's outside the feed container --}}
                    <div id="feed-loading-indicator" class="py-6 flex justify-center items-center mt-6">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-gray-600">Đang tải...</span>
                    </div>

                    {{-- End of feed indicator - initially hidden --}}
                    <div id="feed-end-indicator" class="py-6 text-center hidden mt-6">
                        <p class="text-sm text-gray-500 hidden">No more questions to load</p>
                    </div>

                    {{-- Hidden pagination data for JavaScript to use --}}
                    <div id="feed-pagination-data"
                        data-current-page="{{ $feedQuestions->currentPage() }}"
                        data-last-page="{{ $feedQuestions->lastPage() }}"
                        data-total="{{ $feedQuestions->total() }}"
                        data-has-more="{{ $feedQuestions->hasMorePages() ? 'true' : 'false' }}"
                        class="hidden">
                    </div>
                </div>

                {{-- Right Sidebar (Copied from wiki.index) --}}
                <div class="lg:col-span-3">
                    <div class="space-y-6 sticky top-8"> {{-- Added sticky top and spacing --}}
                        {{-- Latest Questions --}}
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
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
                                            <a href="{{ route('wiki.show', [$question->category->slug ?? 'uncategorized', $question->slug]) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                                                <p class="font-medium text-indigo-600 text-sm line-clamp-1">{{ $question->title }}</p>
                                                <div class="flex items-center mt-2 text-xs text-gray-500">
                                                <span class="inline-flex items-center">
                                                    <span class="iconify mr-1" data-icon="mdi-account" data-width="14"></span>
                                                    {{ $question->user->name ?? 'Anonymous' }}
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

                        {{-- Trending Questions --}}
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
                                            <a href="{{ route('wiki.show', [$question->category->slug ?? 'uncategorized', $question->slug]) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                                                <p class="font-medium text-indigo-600 text-sm line-clamp-1">{{ $question->title }}</p>
                                                {{-- Add view/comment count if needed --}}
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

                            <div class="p-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-t border-gray-100 text-center">
                                <a href="{{ route('wiki.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                    Đặt câu hỏi mới
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

@push('scripts')
    @vite(['resources/js/public/wiki/feed.js'])

    {{-- Include Alpine.js for interactive elements --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
