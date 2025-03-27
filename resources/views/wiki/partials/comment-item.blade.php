<div class="comment-item {{ isset($isReply) && $isReply ? 'pl-4 mt-3 border-l-2 border-gray-200' : 'bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200' }}" data-comment-id="{{ $comment->id }}">
    <div class="flex items-start gap-2">
        <!-- User Avatar - more compact -->
        <div class="flex-shrink-0">
            <img class="h-8 w-8 rounded-full object-cover border border-gray-100"
                 src="{{ $comment->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name) . '&background=6366f1&color=fff' }}"
                 alt="{{ $comment->user->name }}">
        </div>

        <!-- Comment Content - streamlined -->
        <div class="flex-1 min-w-0">
            <!-- Header: Author and Date - more compact -->
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center text-sm">
                    <h3 class="font-medium text-gray-900">{{ $comment->user->name }}</h3>
                    @if($comment->user->role ?? false)
                        <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium {{ $comment->user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $comment->user->role === 'admin' ? 'Quản trị' : 'Biên tập' }}
                        </span>
                    @endif
                </div>
                <div class="text-xs text-gray-500">
                    <time datetime="{{ $comment->created_at->toISOString() }}" title="{{ $comment->created_at->format('H:i d/m/Y') }}">
                        {{ $comment->created_at->diffForHumans() }}
                    </time>
                    @if($comment->created_at != $comment->updated_at)
                        <span class="ml-1 text-xs text-gray-400" title="{{ $comment->updated_at->format('H:i d/m/Y') }}">
                            (đã sửa)
                        </span>
                    @endif
                </div>
            </div>

            <!-- Comment Content - clean presentation -->
            <div class="comment-content-container text-sm text-gray-700">
                <div class="comment-content prose prose-sm max-w-none">
                    {!! $comment->content !!}
                </div>
            </div>

            <!-- Action Buttons - more compact and modern -->
            <div class="mt-2 flex items-center gap-3 {{ isset($isReply) && $isReply ? 'justify-end' : '' }}">
                @auth
                    <!-- Only show reply button if not at level 2 -->
                    @if(!isset($isReply) || (isset($isReply) && !$isReply))
                        <button type="button" class="reply-button text-xs text-gray-500 hover:text-indigo-600 transition-colors flex items-center"
                                data-comment-id="{{ $comment->id }}"
                                data-author-name="{{ $comment->user->name }}">
                            <span class="iconify mr-1" data-icon="mdi-reply" data-width="14"></span>
                            Phản hồi
                        </button>
                    @endif

                    @if(Auth::id() === $comment->user_id)
                        <button type="button" class="edit-button text-xs text-gray-500 hover:text-blue-600 transition-colors flex items-center"
                                data-comment-id="{{ $comment->id }}">
                            <span class="iconify mr-1" data-icon="mdi-pencil" data-width="14"></span>
                            Sửa
                        </button>

                        <button type="button" class="delete-button text-xs text-gray-500 hover:text-red-600 transition-colors flex items-center"
                                data-comment-id="{{ $comment->id }}">
                            <span class="iconify mr-1" data-icon="mdi-delete" data-width="14"></span>
                            Xóa
                        </button>
                    @endif

                    <button type="button" class="like-button text-xs text-gray-500 hover:text-indigo-600 transition-colors flex items-center">
                        <span class="iconify mr-1" data-icon="mdi-thumb-up-outline" data-width="14"></span>
                        <span class="like-count">{{ $comment->likes_count ?? 0 }}</span>
                    </button>
                @endauth
            </div>

            <!-- Replies Container - only show if this is a top-level comment -->
            @if(!isset($isReply))
                @if($comment->replies->count() > 0)
                    <div class="comment-replies mt-3 space-y-2">
                        @foreach($comment->replies as $reply)
                            @include('wiki.partials.comment-item', ['comment' => $reply, 'isReply' => true])
                        @endforeach
                    </div>
                @else
                    <div class="reply-count mt-2">
                        <p class="text-xs text-gray-500 italic">Chưa có phản hồi nào</p>
                    </div>
                @endif
            @endif

            <!-- Reply Form Container (initially hidden) - only for level 1 -->
            @if(!isset($isReply) || (isset($isReply) && !$isReply))
                <div class="reply-form-container mt-2 hidden">
                    <form class="reply-form flex items-start space-x-2">
                        <div class="w-7 h-7 flex-shrink-0">
                            <img class="h-full w-full rounded-full object-cover"
                                 src="{{ Auth::user()?->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()?->name ?? 'Unknown') . '&background=6366f1&color=fff' }}"
                                 alt="{{ Auth::user()?->name ?? 'Ảnh đại diện' }}">
                        </div>
                        <div class="flex-1">
                            <textarea class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                      rows="2"
                                      placeholder="Viết phản hồi của bạn..."></textarea>
                            <div class="mt-2 flex justify-end space-x-2">
                                <button type="button" class="cancel-reply-button px-3 py-1 text-xs border border-gray-200 rounded-md hover:bg-gray-50">
                                    Hủy
                                </button>
                                <button type="submit" class="px-3 py-1 text-xs bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                    Gửi phản hồi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
