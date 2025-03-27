<div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300">
    <div class="p-4 border-b border-gray-100">
        <!-- Comments Header - more compact -->
        <div class="flex items-center justify-between mb-0">
            <div class="flex items-center">
                <span class="iconify text-indigo-500 mr-2" data-icon="mdi-comment-text-multiple-outline" data-width="20"></span>
                <h2 class="text-lg font-semibold text-gray-900">Bình luận ({{ $question->comments->count() }})</h2>
            </div>

            <div class="flex items-center">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    <span class="iconify mr-1" data-icon="mdi-account-group-outline" data-width="14"></span>
                    {{ $question->comments->groupBy('user_id')->count() }} người tham gia
                </span>
            </div>
        </div>
    </div>

    <div class="p-4">
        <!-- Hidden fields for JavaScript -->
        <input type="hidden" id="question-id" value="{{ $question->id }}">
        <input type="hidden" id="user-id" value="{{ Auth::id() ?? '' }}">
        <input type="hidden" id="user-name" value="{{ Auth::user() ? Auth::user()->name : '' }}">
        <input type="hidden" id="user-avatar" value="{{ Auth::user() ? (Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=6366f1&color=fff') : '' }}">

        <!-- Comment Form for Authenticated Users - more compact -->
        @auth
            <div id="comment-form-container" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-100">
                <form id="comment-form" action="/api/wiki/comments" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="parent_id" id="parent_comment_id" value="">
                    <input type="hidden" name="question_id" value="{{ $question->id }}">

                    <!-- Reply Indicator - cleaner design -->
                    <div id="reply-indicator" class="hidden mb-3 p-2 bg-blue-50 rounded-lg border border-blue-100 flex justify-between items-center">
                        <span class="text-sm text-blue-700 inline-flex items-center">
                            <span class="iconify mr-1.5" data-icon="mdi-reply" data-width="16"></span>
                            Đang trả lời: <span id="parent-comment-author" class="font-medium ml-1"></span>
                        </span>
                        <button type="button" id="cancel-reply" class="text-xs text-gray-500 hover:text-gray-700 inline-flex items-center">
                            <span class="iconify mr-1" data-icon="mdi-close-circle" data-width="14"></span>
                            Hủy phản hồi
                        </button>
                    </div>

                    <!-- Editor Component - simplified -->
                    <div class="relative">
                        <label for="comment-editor" class="block text-sm font-medium text-gray-700 mb-1.5">Viết bình luận của bạn</label>
                        <x-form.editor :name="'content'" value="{{ old('content') }}" :height="350" />

                        @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button - cleaner design -->
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <span class="iconify mr-1.5" data-icon="mdi-send" data-width="16"></span>
                            Đăng bình luận
                        </button>
                    </div>
                </form>
            </div>
        @else
            <!-- Login Prompt for Guests - more compact -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4 rounded-lg mb-6 border border-indigo-100 flex items-start">
                <span class="iconify text-indigo-500 mr-3 flex-shrink-0" data-icon="mdi-account-lock-outline" data-width="20"></span>
                <div>
                    <h3 class="font-medium text-gray-800 mb-1">Tham gia thảo luận</h3>
                    <p class="text-gray-600 text-sm">
                        Vui lòng <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">đăng nhập</a> để tham gia thảo luận và đăng bình luận.
                    </p>
                </div>
            </div>
        @endauth

        <!-- Comments List - new design with 2-level nesting -->
        <div id="comments-container" class="space-y-4">
            @if($question->comments->where('parent_id', null)->count() > 0)
                @foreach($question->comments->where('parent_id', null)->sortByDesc('created_at') as $comment)
                    @include('wiki.partials.comment-item', ['comment' => $comment])
                @endforeach

                <!-- Load More Comments Button - cleaner design -->
                @if($question->comments->where('parent_id', null)->count() > 5)
                    <div class="text-center pt-4">
                        <button id="load-more-comments"
                                data-question-id="{{ $question->id }}"
                                data-last-id="{{ $question->comments->where('parent_id', null)->sortBy('id')->last()->id }}"
                                class="inline-flex items-center px-4 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 transition-colors">
                            <span class="iconify mr-1" data-icon="mdi-chevron-down" data-width="16"></span>
                            Tải thêm bình luận
                        </button>
                    </div>
                @endif
            @else
                <!-- Empty State - cleaner design -->
                <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                    <span class="iconify block mx-auto mb-2 text-gray-300" data-icon="mdi-comment-outline" data-width="36"></span>
                    <p class="text-gray-500">Chưa có bình luận nào. Hãy là người đầu tiên chia sẻ ý kiến của bạn!</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/public/wiki/comments.js')
@endpush
