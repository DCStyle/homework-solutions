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
        <!-- Comment Form for Authenticated Users - more compact -->
        @auth
            <div id="comment-form-container" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-100">
                <form id="comment-form" action="{{ route('wiki.comments.store', $question->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="parent_id" id="parent_comment_id" value="">

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Safe function to find element (prevents null errors)
            function safeGetElement(id) {
                return document.getElementById(id) || null;
            }

            // Safe function to reset form
            function resetCommentForm() {
                const parentIdElement = safeGetElement('parent_comment_id');
                const replyIndicator = safeGetElement('reply-indicator');

                if (parentIdElement) parentIdElement.value = '';
                if (replyIndicator) replyIndicator.classList.add('hidden');

                // Reset TinyMCE content safely
                if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                    try {
                        tinymce.get('content').setContent('');
                    } catch (e) {
                        console.warn('Error resetting TinyMCE content:', e);
                    }
                }
            }

            // Safe MathJax typeset function
            function safeTypeset() {
                try {
                    if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
                        return window.MathJax.typesetPromise();
                    }
                } catch (e) {
                    console.warn('MathJax typesetting failed:', e);
                }
                return Promise.resolve(); // Return resolved promise if MathJax not available
            }

            // Handle reply buttons
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('reply-button') || e.target.closest('.reply-button')) {
                    const button = e.target.classList.contains('reply-button') ? e.target : e.target.closest('.reply-button');
                    const commentId = button.dataset.commentId;
                    const authorName = button.dataset.authorName;
                    const parentIdElement = safeGetElement('parent_comment_id');
                    const authorElement = safeGetElement('parent-comment-author');
                    const replyIndicator = safeGetElement('reply-indicator');
                    const commentForm = safeGetElement('comment-form-container');

                    // Only proceed if we have all required elements
                    if (parentIdElement && replyIndicator) {
                        parentIdElement.value = commentId;

                        if (authorElement) {
                            authorElement.textContent = authorName;
                        }

                        replyIndicator.classList.remove('hidden');

                        // Scroll to comment form
                        if (commentForm) {
                            commentForm.scrollIntoView({ behavior: 'smooth' });
                        }

                        // Focus the editor if available
                        try {
                            if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                                tinymce.get('content').focus();
                            }
                        } catch (e) {
                            console.warn('Error focusing TinyMCE:', e);
                        }
                    }
                }
            });

            // Handle cancel reply
            const cancelReplyButton = safeGetElement('cancel-reply');
            if (cancelReplyButton) {
                cancelReplyButton.addEventListener('click', function() {
                    resetCommentForm();
                });
            }

            // Handle form submission
            const commentForm = safeGetElement('comment-form');
            if (commentForm) {
                commentForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Get form data
                    const form = this;
                    const formData = new FormData(form);

                    // Safely get content from TinyMCE
                    try {
                        if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                            formData.set('content', tinymce.get('content').getContent());
                        }
                    } catch (e) {
                        console.warn('Error getting TinyMCE content:', e);
                        // Fallback to textarea content if TinyMCE fails
                        const contentTextarea = form.querySelector('[name="content"]');
                        if (contentTextarea) {
                            formData.set('content', contentTextarea.value);
                        }
                    }

                    // Submit comment via AJAX
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Reset the form
                                resetCommentForm();

                                // Add the new comment to the page
                                if (data.comment.parent_id) {
                                    // Add reply to parent comment
                                    const parentComment = document.querySelector(`.comment-item[data-comment-id="${data.comment.parent_id}"]`);
                                    if (parentComment) {
                                        const repliesContainer = parentComment.querySelector('.comment-replies');
                                        if (repliesContainer) {
                                            // Remove "No replies yet" message if it exists
                                            const emptyMessage = repliesContainer.querySelector('.reply-count');
                                            if (emptyMessage) {
                                                emptyMessage.remove();
                                            }

                                            // Insert the new reply at the beginning of the replies container
                                            repliesContainer.insertAdjacentHTML('afterbegin', createCommentHTML(data.comment, true));
                                        }
                                    }
                                } else {
                                    // Add new comment at the top of the list
                                    const commentsContainer = safeGetElement('comments-container');
                                    if (commentsContainer) {
                                        // Check if we need to remove an empty state message
                                        const emptyState = commentsContainer.querySelector('.text-center.py-8');
                                        if (emptyState) {
                                            emptyState.remove();
                                        }

                                        // Insert the new comment at the beginning of the comments container
                                        commentsContainer.insertAdjacentHTML('afterbegin', createCommentHTML(data.comment, false));
                                    }
                                }

                                // Show success message
                                showNotification(data.message, 'success');

                                // Run MathJax typesetting on new content after a small delay
                                setTimeout(() => safeTypeset(), 100);
                            } else {
                                // Show error message
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Đã xảy ra lỗi khi đăng bình luận. Vui lòng thử lại.', 'error');
                        });
                });
            }

            // Handle edit buttons
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('edit-button') || e.target.closest('.edit-button')) {
                    const button = e.target.classList.contains('edit-button') ? e.target : e.target.closest('.edit-button');
                    const commentId = button.dataset.commentId;
                    const commentContent = document.querySelector(`.comment-item[data-comment-id="${commentId}"] .comment-content`).innerHTML;

                    // Create an edit form
                    const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
                    const contentContainer = commentItem.querySelector('.comment-content-container');

                    // Save the original content
                    commentItem.dataset.originalContent = contentContainer.innerHTML;

                    // Replace with edit form
                    contentContainer.innerHTML = `
                    <form class="comment-edit-form bg-gray-50 p-3 rounded-lg border border-gray-100" data-comment-id="${commentId}">
                        <div class="mb-3">
                            <textarea class="edit-tiny-editor w-full border border-gray-200 rounded-lg shadow-sm p-2">${commentContent}</textarea>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" class="cancel-edit-button inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-xs text-gray-700 hover:bg-gray-50">
                                <span class="iconify mr-1" data-icon="mdi-close" data-width="14"></span>
                                Hủy
                            </button>
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-lg shadow-sm text-xs text-white bg-indigo-600 hover:bg-indigo-700">
                                <span class="iconify mr-1" data-icon="mdi-content-save" data-width="14"></span>
                                Lưu
                            </button>
                        </div>
                    </form>
                `;

                    // Initialize TinyMCE for the edit form
                    if (typeof tinymce !== 'undefined') {
                        tinymce.init({
                            selector: '.edit-tiny-editor',
                            plugins: 'autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
                            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                            menubar: false,
                            height: 200,
                            branding: false,
                            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
                        });
                    }
                }
            });

            // Handle cancel edit
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('cancel-edit-button')) {
                    const commentItem = e.target.closest('.comment-item');
                    const contentContainer = commentItem.querySelector('.comment-content-container');

                    // Restore original content
                    contentContainer.innerHTML = commentItem.dataset.originalContent;

                    // Remove stored original content
                    delete commentItem.dataset.originalContent;
                }
            });

            // Handle edit form submission
            document.addEventListener('submit', function(e) {
                if (e.target.classList.contains('comment-edit-form')) {
                    e.preventDefault();

                    const form = e.target;
                    const commentId = form.dataset.commentId;
                    let content = '';

                    // Get content from TinyMCE if available
                    if (typeof tinymce !== 'undefined' && tinymce.get(form.querySelector('.edit-tiny-editor').id)) {
                        content = tinymce.get(form.querySelector('.edit-tiny-editor').id).getContent();
                    } else {
                        content = form.querySelector('.edit-tiny-editor').value;
                    }

                    // Submit edit via AJAX
                    fetch(`/hoi-dap/binh-luan/${commentId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update the comment content
                                const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
                                const contentContainer = commentItem.querySelector('.comment-content-container');

                                contentContainer.innerHTML = `<div class="comment-content prose prose-sm max-w-none">${data.comment.content}</div>`;

                                // Remove stored original content
                                delete commentItem.dataset.originalContent;

                                // Show success message
                                showNotification(data.message, 'success');
                            } else {
                                // Show error message
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Đã xảy ra lỗi khi cập nhật bình luận. Vui lòng thử lại.', 'error');
                        });
                }
            });

            // Handle delete buttons
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-button') || e.target.closest('.delete-button')) {
                    if (!confirm('Bạn có chắc chắn muốn xóa bình luận này không?')) {
                        return;
                    }

                    const button = e.target.classList.contains('delete-button') ? e.target : e.target.closest('.delete-button');
                    const commentId = button.dataset.commentId;

                    // Submit delete via AJAX
                    fetch(`/hoi-dap/binh-luan/${commentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the comment from the page
                                const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
                                commentItem.remove();

                                // Show success message
                                showNotification(data.message, 'success');
                            } else {
                                // Show error message
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Đã xảy ra lỗi khi xóa bình luận. Vui lòng thử lại.', 'error');
                        });
                }
            });

            // Handle load more comments
            document.getElementById('load-more-comments')?.addEventListener('click', function() {
                const button = this;
                const questionId = button.dataset.questionId;
                const lastId = button.dataset.lastId;

                // Disable button while loading
                button.disabled = true;
                button.innerHTML = '<span class="iconify animate-spin mr-1.5" data-icon="mdi-loading" data-width="18"></span> Đang tải...';

                // Fetch more comments
                fetch(`/hoi-dap/cau-hoi/${questionId}/binh-luan?last_id=${lastId}&limit=5`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Add comments to the container
                            const commentsContainer = document.getElementById('comments-container');

                            // Insert comments before the "Load more" button
                            data.comments.forEach(comment => {
                                button.parentNode.insertAdjacentHTML('beforebegin', createCommentHTML(comment));
                            });

                            // Update the last ID
                            if (data.comments.length > 0) {
                                button.dataset.lastId = data.comments[data.comments.length - 1].id;
                            }

                            // Hide the button if no more comments
                            if (!data.has_more) {
                                button.parentNode.remove();
                            }
                        } else {
                            // Show error message
                            showNotification(data.message || 'Không thể tải thêm bình luận', 'error');
                        }

                        // Re-enable button
                        button.disabled = false;
                        button.innerHTML = '<span class="iconify mr-1.5" data-icon="mdi-chevron-down" data-width="18"></span> Tải thêm bình luận';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Đã xảy ra lỗi khi tải thêm bình luận. Vui lòng thử lại.', 'error');

                        // Re-enable button
                        button.disabled = false;
                        button.innerHTML = '<span class="iconify mr-1.5" data-icon="mdi-chevron-down" data-width="18"></span> Tải thêm bình luận';
                    });
            });

            /**
             * Creates HTML for a new comment based on the updated design
             * @param {Object} comment - The comment data
             * @param {boolean} isReply - Whether this is a reply comment
             * @returns {string} HTML string for the comment
             */
            function createCommentHTML(comment, isReply) {
                const date = new Date(comment.created_at);
                const formattedDate = date.toLocaleDateString('vi-VN', { year: 'numeric', month: 'short', day: 'numeric' });
                const userId = {{ Auth::id() ?? 'null' }};
                const isNestedReply = isReply && comment.parent_id;

                // Generate role badge if exists
                let roleBadge = '';
                if (comment.user.role) {
                    const badgeClass = comment.user.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700';
                    const roleText = comment.user.role === 'admin' ? 'Quản trị' : 'Biên tập';
                    roleBadge = `
                        <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">
                            ${roleText}
                        </span>
                    `;
                }

                // Generate reply button (only for level 0 or level 1)
                let replyButton = '';
                if (!isNestedReply) {
                    replyButton = `
                    <button type="button" class="reply-button text-xs text-gray-500 hover:text-indigo-600 transition-colors flex items-center"
                            data-comment-id="${comment.id}"
                            data-author-name="${comment.user.name}">
                        <span class="iconify mr-1" data-icon="mdi-reply" data-width="14"></span>
                        Phản hồi
                    </button>
                `;
                }

                // Generate edit & delete buttons if user is author
                let editDeleteButtons = '';
                if (comment.user_id === userId) {
                    editDeleteButtons = `
                        <button type="button" class="edit-button text-xs text-gray-500 hover:text-blue-600 transition-colors flex items-center"
                                data-comment-id="${comment.id}">
                            <span class="iconify mr-1" data-icon="mdi-pencil" data-width="14"></span>
                            Sửa
                        </button>
                        <button type="button" class="delete-button text-xs text-gray-500 hover:text-red-600 transition-colors flex items-center"
                                data-comment-id="${comment.id}">
                            <span class="iconify mr-1" data-icon="mdi-delete" data-width="14"></span>
                            Xóa
                        </button>
                    `;
                }

                // Generate reply container (only for top-level comments)
                let repliesContainer = '';
                if (!isReply) {
                    repliesContainer = `
                        <div class="comment-replies mt-3 space-y-2">
                            <div class="reply-count">
                                <p class="text-xs text-gray-500 italic">Chưa có phản hồi nào</p>
                            </div>
                        </div>
                    `;
                }

                // Generate reply form container (only if not already at level 2)
                let replyFormContainer = '';
                if (!isNestedReply) {
                    const userAvatar = '{{ Auth::user() ? (Auth::user()->avatar ?? "https://ui-avatars.com/api/?name=" . urlencode(Auth::user()->name) . "&background=6366f1&color=fff") : "" }}';
                    const userName = '{{ Auth::user() ? Auth::user()->name : "" }}';

                    replyFormContainer = `
                        <div class="reply-form-container mt-2 hidden">
                            <form class="reply-form flex items-start space-x-2">
                                <div class="w-7 h-7 flex-shrink-0">
                                    <img class="h-full w-full rounded-full object-cover"
                                         src="${userAvatar}"
                                         alt="${userName}">
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
                    `;
                }

                // Assemble the entire comment HTML
                return `
                    <div class="comment-item ${isReply ? 'pl-4 mt-3 border-l-2 border-gray-200' : 'bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200'}" data-comment-id="${comment.id}">
                        <div class="flex items-start gap-2">
                            <!-- User Avatar -->
                            <div class="flex-shrink-0">
                                <img class="h-8 w-8 rounded-full object-cover border border-gray-100"
                                     src="${comment.user.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(comment.user.name) + '&background=6366f1&color=fff'}"
                                     alt="${comment.user.name}">
                            </div>

                            <!-- Comment Content -->
                            <div class="flex-1 min-w-0">
                                <!-- Header: Author and Date -->
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center text-sm">
                                        <h3 class="font-medium text-gray-900">${comment.user.name}</h3>
                                        ${roleBadge}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <time datetime="${comment.created_at}" title="${new Date(comment.created_at).toLocaleString('vi-VN')}">
                                            ${formattedDate}
                                        </time>
                                    </div>
                                </div>

                                <!-- Comment Content -->
                                <div class="comment-content-container text-sm text-gray-700">
                                    <div class="comment-content prose prose-sm max-w-none">
                                        ${comment.content}
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-2 flex items-center gap-3 ${isReply ? 'justify-end' : ''}">
                                    ${replyButton}
                                    ${editDeleteButtons}
                                    <button type="button" class="like-button text-xs text-gray-500 hover:text-indigo-600 transition-colors flex items-center">
                                        <span class="iconify mr-1" data-icon="mdi-thumb-up-outline" data-width="14"></span>
                                        <span class="like-count">0</span>
                                    </button>
                                </div>

                                <!-- Replies Container -->
                                ${repliesContainer}

                                <!-- Reply Form Container -->
                                ${replyFormContainer}
                            </div>
                        </div>
                    </div>
                `;
            }

            /**
             * Display a notification message to the user
             * @param {string} message - The message to display
             * @param {string} type - The type of notification ('success' or 'error')
             */
            function showNotification(message, type = 'success') {
                // Create notification element
                const notification = document.createElement('div');

                // Set appropriate styling based on type
                if (type === 'success') {
                    notification.className = 'fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg bg-green-500 text-white flex items-center z-50';
                    notification.innerHTML = `
            <span class="iconify mr-2" data-icon="mdi-check-circle" data-width="20"></span>
            <span>${message}</span>
        `;
                } else {
                    notification.className = 'fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg bg-red-500 text-white flex items-center z-50';
                    notification.innerHTML = `
            <span class="iconify mr-2" data-icon="mdi-alert-circle" data-width="20"></span>
            <span>${message}</span>
        `;
                }

                // Add to the document
                document.body.appendChild(notification);

                // Fade-in effect
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s ease';

                setTimeout(() => {
                    notification.style.opacity = '1';
                }, 10);

                // Auto-remove after 3 seconds
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
            }
        });
    </script>
@endpush
