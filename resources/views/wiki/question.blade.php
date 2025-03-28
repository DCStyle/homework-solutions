@extends('layouts.app')

@section('seo')
    {!! seo($question->getDynamicSEOData()) !!}
@endsection

@section('content')
<div class="py-8 bg-gradient-to-r from-blue-100 to-cyan-100">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Sidenav Content -->
            <div class="lg:col-span-3 hidden lg:block">
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

            <!-- Main Content -->
            <div class="lg:col-span-6">
                @include('wiki.partials.search')

                <!-- Question Details -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h1 class="text-xl font-semibold text-gray-900">{{ $question->title }}</h1>
                            <span class="whitespace-nowrap px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                                {{ $question->category->name }}
                            </span>
                        </div>
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <span>{{ $question->user->name }}</span>
                            <span class="mx-2">&bull;</span>
                            <span>{{ $question->created_at->format('d/m/Y H:i') }}</span>
                            <span class="mx-2">&bull;</span>
                            <span>{{ $question->views }} lượt xem</span>
                        </div>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="prose max-w-none">
                            {!! $question->content !!}
                        </div>
                    </div>
                </div>

                <!-- AI-Generated Answer -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="px-4 py-5 sm:px-6 bg-green-50 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">
                            <span class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Câu trả lời từ AI
                                </div>
                                @if(Auth::user()?->isAdmin())
                                    @if(isset($aiAnswer))
                                    <button id="edit-ai-answer-btn" type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Sửa
                                    </button>
                                    @endif
                                @endif
                            </span>
                        </h2>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div id="answer-content" class="prose max-w-none tiny-mce-content">
                            @if(isset($aiAnswer))
                                <div id="ai-content-container"></div>
                            @else
                                <p class="text-gray-500 italic">Chưa có câu trả lời AI cho câu hỏi này.</p>
                            @endif
                        </div>

                        @if(Auth::user()?->isAdmin() && isset($aiAnswer))
                            <div id="answer-edit-form" class="mt-4 hidden">
                                <form id="ai-answer-form">
                                    @csrf
                                    <input type="hidden" id="answer_id" value="{{ $aiAnswer->id }}">
                                    <div class="mb-4">
                                        <x-form.editor name="answer_content" :value="$aiAnswer->content" :height="400" />
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <button type="button" id="cancel-edit-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Hủy
                                        </button>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Lưu
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Comments Section -->
                @include('wiki.partials.comments', ['question' => $question])
            </div>

            <div class="lg:col-span-3">
                <div class="sticky top-8">
                    <!-- Related Questions -->
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900 inline-flex items-center">
                                <span class="iconify mr-2" data-icon="mdi-link-variant" data-width="22"></span>
                                Câu hỏi liên quan
                            </h2>
                        </div>

                        <div class="px-4 py-3">
                            <ul class="space-y-3">
                                @forelse($relatedQuestions as $relatedQuestion)
                                    <li>
                                        <a href="{{ route('wiki.show', [$relatedQuestion->category->slug, $relatedQuestion->slug]) }}" class="block text-sm text-indigo-600 hover:text-indigo-900 hover:underline">
                                            {{ $relatedQuestion->title }}
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-sm text-gray-500">Không có câu hỏi liên quan.</li>
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

                    <!-- Category Info -->
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900 inline-flex items-center">
                                <span class="iconify mr-2" data-icon="mdi-folder-multiple-outline" data-width="22"></span>
                                Thông tin danh mục
                            </h2>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-sm font-medium text-gray-900">{{ $question->category->name }}</h3>
                            <div class="mt-3 text-sm text-gray-600">
                                <p>Số câu hỏi: {{ $question->category->wikiQuestions()->count() }}</p>
                            </div>
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
                        {{-- Optional: Ask Question CTA --}}
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
    @vite('resources/js/public/wiki/question.js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js" integrity="sha512-H+vwHI+KwQl7usF9JYBfv8m+JRIp7gJZv5NQ2xCp5YpS2A/YpwGk3PAFKgvrXGiCMV9SL67rMn7cVJEyG1hSQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('ai-content-container');
            // Use DOMPurify as an additional safety net
            const sanitizedContent = DOMPurify.sanitize('{!! addslashes($aiAnswer->content) !!}', {
                USE_PROFILES: { html: true },
                ADD_ATTR: ['target']
            });
            container.innerHTML = sanitizedContent;
        });
    </script>
@endpush
