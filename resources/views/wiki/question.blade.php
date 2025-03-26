@extends('layouts.app')

@section('seo')
    {!! seo($question->getDynamicSEOData()) !!}
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Main Content -->
            <div class="md:col-span-9">
                <!-- Question Details -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h1 class="text-xl font-semibold text-gray-900">{{ $question->title }}</h1>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
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
                            <span class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Câu trả lời từ AI
                            </span>
                        </h2>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div id="answer-content" class="prose max-w-none">
                            @if($question->answers->count() > 0)
                                {!! $question->answers->first()->content !!}
                            @else
                                <div class="p-4 border border-yellow-300 bg-yellow-50 rounded-md">
                                    <p class="text-yellow-700">Đang tạo câu trả lời...</p>
                                    <div class="loading-indicator mt-2 h-1 w-full bg-gray-200 rounded">
                                        <div class="h-1 bg-yellow-500 rounded w-1/3 animate-pulse"></div>
                                    </div>
                                </div>
                                <div id="streaming-content" class="mt-4 hidden"></div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const streamingContent = document.getElementById('streaming-content');
                                        const loadingIndicator = document.querySelector('.loading-indicator');

                                        // Function to start streaming the answer
                                        function streamAnswer() {
                                            // Show the streaming content div
                                            streamingContent.classList.remove('hidden');

                                            // Create a new EventSource connection
                                            const eventSource = new EventSource("{{ route('api.wiki.questions.stream', $question) }}");

                                            let contentBuffer = '';

                                            // Process incoming data chunks
                                            eventSource.onmessage = function(event) {
                                                // Add the new text to our buffer
                                                contentBuffer += event.data;

                                                // Update the content div
                                                streamingContent.innerHTML = contentBuffer;

                                                // Scroll to the bottom of the div
                                                streamingContent.scrollTop = streamingContent.scrollHeight;
                                            };

                                            // Handle completed streaming
                                            eventSource.addEventListener('DONE', function(event) {
                                                // Close the connection
                                                eventSource.close();

                                                // Hide the loading indicator
                                                if (loadingIndicator) {
                                                    loadingIndicator.parentNode.classList.add('hidden');
                                                }
                                            });

                                            // Handle errors
                                            eventSource.onerror = function(event) {
                                                console.error("Error in event stream:", event);
                                                eventSource.close();

                                                // If no content was streamed, show an error message
                                                if (contentBuffer === '') {
                                                    streamingContent.innerHTML = '<p class="text-red-600">Không thể tạo câu trả lời. Vui lòng thử lại sau.</p>';
                                                }

                                                // Hide the loading indicator
                                                if (loadingIndicator) {
                                                    loadingIndicator.parentNode.classList.add('hidden');
                                                }
                                            };
                                        }

                                        // Start streaming the answer
                                        streamAnswer();
                                    });
                                </script>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                @include('wiki.partials.comments', ['question' => $question])
            </div>

            <div class="md:col-span-12 lg:col-span-3 hidden md:block">
                <!-- Related Questions -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-base font-medium text-gray-900">Câu hỏi liên quan</h2>
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
                </div>

                <!-- Category Info -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-base font-medium text-gray-900">Thông tin danh mục</h2>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-sm font-medium text-gray-900">{{ $question->category->name }}</h3>
                        <div class="mt-3 text-sm text-gray-600">
                            <p>Số câu hỏi: {{ $question->category->wikiQuestions()->count() }}</p>
                        </div>
                    </div>
                </div>

                @include('wiki.partials.sidebar')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle reply buttons
        const replyButtons = document.querySelectorAll('.reply-button');
        const replyInfo = document.getElementById('reply-to-info');
        const parentIdInput = document.getElementById('parent_id');
        const cancelReply = document.getElementById('cancel-reply');

        replyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const parentId = this.getAttribute('data-parent-id');
                parentIdInput.value = parentId;
                replyInfo.classList.remove('hidden');
                document.getElementById('comment-content').focus();
            });
        });

        cancelReply.addEventListener('click', function() {
            parentIdInput.value = '';
            replyInfo.classList.add('hidden');
        });
    });
</script>
@endpush
@endsection
