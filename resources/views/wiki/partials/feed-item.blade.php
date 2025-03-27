<div class="bg-white rounded-lg shadow-md border border-gray-200 feed-item mb-6" data-question-id="{{ $question->id }}">
    {{-- Question Header --}}
    <div class="p-4 flex items-center space-x-3">
        <img src="{{ $question->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($question->user->name[0] ?? 'A') . '&background=e5e7eb&color=6b7280&size=40' }}"
             alt="{{ $question->user->name ?? 'Anonymous' }}"
             class="w-10 h-10 rounded-full flex-shrink-0">
        <div class="flex-grow">
            <p class="font-semibold text-gray-900 leading-tight">{{ $question->user->name ?? 'Anonymous' }}</p>
            <p class="text-xs text-gray-500">
                {{ $question->created_at->diffForHumans() }}
                @if($question->category)
                    · <a href="{{ route('wiki.search', ['category_id' => $question->category->id]) }}" class="text-blue-600 hover:underline">{{ $question->category->name }}</a>
                @endif
            </p>
        </div>

        {{-- Optional: More options button --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
            </button>
            <div x-show="open"
                 @click.away="open = false"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10"
                 style="display: none;">
                <a href="{{ route('wiki.show', [$question->category ? $question->category->slug : 'uncategorized', $question->slug]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Xem chi tiết</a>
                <button @click="navigator.clipboard.writeText('{{ route('wiki.show', [$question->category ? $question->category->slug : 'uncategorized', $question->slug]) }}'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Copy URL</button>
            </div>
        </div>
    </div>

    {{-- Question Content --}}
    <div class="px-4 pb-3">
        <h2 class="text-lg font-semibold mb-2 leading-snug">
            <a href="{{ route('wiki.show', [$question->category ? $question->category->slug : 'uncategorized', $question->slug]) }}" class="text-gray-800 hover:text-blue-700 transition duration-150 ease-in-out">
                {{ $question->title }}
            </a>
        </h2>
        <div class="relative" x-data="{ expanded: false }">
            <div x-show="!expanded" class="text-gray-700 text-sm prose prose-sm max-w-none line-clamp-3">
                {!! nl2br(e(strip_tags($question->content))) !!}
            </div>
            <div x-show="expanded" class="text-gray-700 text-sm prose prose-sm max-w-none" style="display: none;">
                {!! nl2br(e(strip_tags($question->content))) !!}
            </div>
            <button x-show="!expanded"
                    @click="expanded = true"
                    class="absolute bottom-0 right-0 text-xs font-semibold text-blue-600 hover:underline bg-gradient-to-r from-transparent via-white to-white pl-4">
                ... Xem thêm
            </button>
            <button x-show="expanded"
                    @click="expanded = false"
                    class="text-xs font-semibold text-blue-600 hover:underline mt-1"
                    style="display: none;">
                Ẩn bớt
            </button>
        </div>
    </div>

    {{-- Action Bar --}}
    <div class="px-4 py-2 border-t border-gray-100 flex justify-around items-center text-sm text-gray-600">
        <a href="{{ route('wiki.show', [$question->category ? $question->category->slug : 'uncategorized', $question->slug]) }}#like"
           class="flex items-center space-x-1 hover:text-blue-600 hover:bg-blue-50 rounded-md px-2 py-1 transition-colors duration-150">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
            <span>Thích</span>
        </a>
        <div class="relative" x-data="{ shareOpen: false }">
            <button @click="shareOpen = !shareOpen" class="flex items-center space-x-1 hover:text-blue-600 hover:bg-blue-50 rounded-md px-2 py-1 transition-colors duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path></svg>
                <span>Chia sẻ</span>
            </button>
            <div x-show="shareOpen"
                 @click.away="shareOpen = false"
                 class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10"
                 style="display: none;">
                <button @click="navigator.clipboard.writeText('{{ route('wiki.show', [$question->category ? $question->category->slug : 'uncategorized', $question->slug]) }}'); shareOpen = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Copy URL</button>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('wiki.show', [$question->category ? $question->category->slug : 'uncategorized', $question->slug])) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Chia sẻ lên Facebook</a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('wiki.show', [$question->category ? $question->category->slug : 'uncategorized', $question->slug])) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Chia sẻ lên Twitter</a>
            </div>
        </div>
    </div>

    {{-- Answers & Comments Section --}}
    <div class="bg-gray-50 px-4 py-4 border-t border-gray-100">
        {{-- AI Answer (if available) --}}
        @if($aiAnswer = $question->answers->firstWhere('is_ai', true))
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-4">
                <div class="flex items-start space-x-2 w-full">
                    <div class="bg-blue-500 text-white p-1 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div class="flex-grow-0 flex-shrink-0" style="width: calc(100% - 28px)">
                        <p class="font-semibold text-gray-800 text-sm mb-1">Câu trả lời từ AI</p>
                        <div x-data="{ aiExpanded: false }" class="relative">
                            <div x-show="!aiExpanded" class="prose prose-sm max-w-none text-gray-700 line-clamp-3">
                                {!! $aiAnswer->content !!}
                            </div>
                            <div x-show="aiExpanded" class="prose prose-sm max-w-none text-gray-700" style="display: none;">
                                {!! $aiAnswer->content !!}
                            </div>
                            <button x-show="!aiExpanded"
                                    @click="aiExpanded = true"
                                    class="absolute bottom-0 right-0 text-xs font-semibold text-blue-600 hover:underline bg-gradient-to-r from-transparent via-blue-50 to-blue-50 pl-4">
                                ... Xem thêm
                            </button>
                            <button x-show="aiExpanded"
                                    @click="aiExpanded = false"
                                    class="text-xs font-semibold text-blue-600 hover:underline mt-1"
                                    style="display: none;">
                                Ẩn bớt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Comments --}}
        @if($question->comments->isNotEmpty())
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-600 mb-2">Bình luận</h3>
                <div class="comments-container">
                    @foreach($question->comments->take(3) as $comment)
                        <div class="flex items-start space-x-2 text-sm comment-item pt-2 pb-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                            <img src="{{ $comment->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name[0] ?? 'A') . '&background=e5e7eb&color=6b7280&size=28' }}"
                                 alt="{{ $comment->user->name ?? 'Anonymous' }}"
                                 class="w-7 h-7 rounded-full flex-shrink-0 mt-0.5">
                            <div class="flex-grow">
                                <p class="font-semibold text-gray-700 leading-tight text-xs">{{ $comment->user->name ?? 'Anonymous' }}</p>
                                <div x-data="{ commentExpanded: false }" class="relative mt-1">
                                    <div x-show="!commentExpanded" class="text-gray-600 prose prose-sm max-w-none line-clamp-2 text-xs">
                                        {!! $comment->content !!}
                                    </div>
                                    <div x-show="commentExpanded" class="text-gray-600 prose prose-sm max-w-none text-xs" style="display: none;">
                                        {!! $comment->content !!}
                                    </div>
                                    <button x-show="!commentExpanded && ($el.previousElementSibling.scrollHeight > $el.previousElementSibling.clientHeight)"
                                            @click="commentExpanded = true"
                                            class="absolute bottom-0 right-0 text-xs font-semibold text-blue-600 hover:underline bg-gradient-to-r from-transparent via-gray-50 to-gray-50 pl-4">
                                        ... more
                                    </button>
                                    <button x-show="commentExpanded"
                                            @click="commentExpanded = false"
                                            class="text-xs font-semibold text-blue-600 hover:underline mt-1"
                                            style="display: none;">
                                        less
                                    </button>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $comment->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($question->comments->count() > 3)
                    <button type="button"
                            class="text-xs text-blue-600 hover:underline block mt-2 load-more-comments-btn"
                            data-question-id="{{ $question->id }}"
                            data-last-comment-id="{{ $question->comments->take(3)->last()->id }}"
                            data-total-comments="{{ $question->comments->count() }}"
                            data-loaded-count="3">
                        Xêm thêm bình luận
                    </button>
                    <div class="more-comments-container mt-2" style="display: none;"></div>
                @endif
            </div>
        @endif

        {{-- Add Comment Form --}}
        @auth
            <form class="js-comment-form mt-2" data-question-id="{{ $question->id }}">
                @csrf
                <input type="hidden" name="parent_id" value="">
                <div class="flex items-start space-x-2">
                    <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name[0] ?? 'U') . '&background=e5e7eb&color=6b7280&size=28' }}"
                         alt="{{ auth()->user()->name ?? 'User' }}"
                         class="w-7 h-7 rounded-full flex-shrink-0 mt-1">
                    <div class="flex-grow">
                        <textarea name="content"
                                  class="w-full px-3 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                  rows="1"
                                  placeholder="Write a comment..."></textarea>
                        <div class="validation-error text-xs text-red-500 mt-1" style="display: none;"></div>
                        <div class="mt-1.5 flex justify-end">
                            <button type="submit" class="px-2.5 py-1 text-xs bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Bình luận
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-3 bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-gray-600">
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-700">Bạn muốn bình luận chi tiết hơn hoặc muốn thêm lời giải đầy đủ cho câu hỏi này?</p>
                        <p class="mt-1">
                            Đến trang <a href="{{ route('wiki.show', [$question->category ? $question->category->slug : 'uncategorized', $question->slug]) }}#answer" class="text-blue-600 font-medium hover:underline">chi tiết câu hỏi</a>
                            để sử dụng trình soạn thảo đầy đủ chức năng
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-4 p-3 bg-gray-100 rounded-lg text-center">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Đăng nhập</a> hoặc
                <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Đăng ký</a>
                để tham gia trả lời câu hỏi
            </div>
        @endauth
    </div>
</div>
