import { apiClient, domUtils } from './wiki-utils';

document.addEventListener('DOMContentLoaded', function() {
    // DOM elements
    const commentsContainer = document.getElementById('comments-container');
    const commentForm = document.getElementById('comment-form');
    const parentCommentIdInput = document.getElementById('parent_comment_id');
    const replyIndicator = document.getElementById('reply-indicator');
    const parentCommentAuthorElem = document.getElementById('parent-comment-author');
    const cancelReplyButton = document.getElementById('cancel-reply');
    const loadMoreButton = document.getElementById('load-more-comments');

    // Get question ID from the DOM
    const questionId = document.getElementById('question-id').value;
    const currentUserId = document.getElementById('user-id').value;

    // Initialize event handlers
    initializeEventHandlers();

    function initializeEventHandlers() {
        // Handle reply buttons
        document.addEventListener('click', handleReplyButtonClick);

        // Handle cancel reply
        if (cancelReplyButton) {
            cancelReplyButton.addEventListener('click', resetCommentForm);
        }

        // Handle form submission
        if (commentForm) {
            commentForm.addEventListener('submit', handleCommentSubmission);
        }

        // Handle edit buttons
        document.addEventListener('click', handleEditButtonClick);

        // Handle cancel edit
        document.addEventListener('click', handleCancelEditClick);

        // Handle edit form submission
        document.addEventListener('submit', handleEditFormSubmission);

        // Handle delete buttons
        document.addEventListener('click', handleDeleteButtonClick);

        // Handle load more comments
        if (loadMoreButton) {
            loadMoreButton.addEventListener('click', handleLoadMoreComments);
        }
    }

    function handleReplyButtonClick(e) {
        const button = e.target.closest('.reply-button');
        if (!button) return;

        const commentId = button.dataset.commentId;
        const authorName = button.dataset.authorName;

        if (parentCommentIdInput && replyIndicator) {
            // Set parent comment ID
            parentCommentIdInput.value = commentId;

            // Show reply indicator with author name
            if (parentCommentAuthorElem) {
                parentCommentAuthorElem.textContent = authorName;
            }
            replyIndicator.classList.remove('hidden');

            // Scroll to comment form
            const commentFormContainer = document.getElementById('comment-form-container');
            if (commentFormContainer) {
                commentFormContainer.scrollIntoView({ behavior: 'smooth' });
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

    function resetCommentForm() {
        // Reset parent comment ID
        if (parentCommentIdInput) parentCommentIdInput.value = '';

        // Hide reply indicator
        if (replyIndicator) replyIndicator.classList.add('hidden');

        // Reset TinyMCE content safely
        try {
            if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                tinymce.get('content').setContent('');
            }
        } catch (e) {
            console.warn('Error resetting TinyMCE content:', e);
        }
    }

    async function handleCommentSubmission(e) {
        e.preventDefault();

        // Show loading indicator
        const submitButton = e.target.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="iconify animate-spin mr-1.5" data-icon="mdi-loading" data-width="18"></span> Đang gửi...';

        try {
            // Get form data
            let formData = new FormData(this);

            // Make sure question_id is included
            formData.append('question_id', questionId);

            // Get content from TinyMCE if available
            try {
                if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                    formData.set('content', tinymce.get('content').getContent());
                }
            } catch (e) {
                console.warn('Error getting TinyMCE content:', e);
            }

            // Convert FormData to JSON object
            const formObject = {};
            formData.forEach((value, key) => {
                formObject[key] = value;
            });

            // Submit comment via API
            const response = await apiClient.post('/api/wiki/comments', formObject);

            if (response.success) {
                // Reset the form
                resetCommentForm();

                // Add the new comment to the page
                const comment = response.data;
                if (comment.parent_id) {
                    // Add reply to parent comment
                    const parentComment = document.querySelector(`.comment-item[data-comment-id="${comment.parent_id}"]`);
                    if (parentComment) {
                        const repliesContainer = parentComment.querySelector('.comment-replies');
                        if (repliesContainer) {
                            // Remove "No replies yet" message if it exists
                            const emptyMessage = repliesContainer.querySelector('.reply-count');
                            if (emptyMessage) {
                                emptyMessage.remove();
                            }

                            // Insert the new reply at the beginning of the replies container
                            repliesContainer.insertAdjacentHTML('afterbegin', createCommentHTML(comment, true));
                        }
                    }
                } else {
                    // Add new comment at the top of the list
                    if (commentsContainer) {
                        // Check if we need to remove an empty state message
                        const emptyState = commentsContainer.querySelector('.text-center.py-8');
                        if (emptyState) {
                            emptyState.remove();
                        }

                        // Insert the new comment at the beginning of the comments container
                        commentsContainer.insertAdjacentHTML('afterbegin', createCommentHTML(comment, false));
                    }
                }

                // Show success message
                domUtils.showNotification(response.message || 'Bình luận đã được đăng thành công', 'success');

                // Run MathJax typesetting on new content after a small delay
                setTimeout(runMathJaxTypesetting, 100);
            } else {
                // Show error message
                domUtils.showNotification(response.message || 'Đã xảy ra lỗi khi đăng bình luận', 'error');
            }
        } catch (error) {
            console.error('Error posting comment:', error);
            domUtils.showNotification('Đã xảy ra lỗi khi đăng bình luận. Vui lòng thử lại.', 'error');
        } finally {
            // Restore submit button
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    }

    function handleEditButtonClick(e) {
        const button = e.target.closest('.edit-button');
        if (!button) return;

        const commentId = button.dataset.commentId;
        const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
        if (!commentItem) return;

        const contentContainer = commentItem.querySelector('.comment-content-container');
        const commentContent = commentItem.querySelector('.comment-content').innerHTML;

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

    function handleCancelEditClick(e) {
        const button = e.target.closest('.cancel-edit-button');
        if (!button) return;

        const commentItem = button.closest('.comment-item');
        if (!commentItem) return;

        const contentContainer = commentItem.querySelector('.comment-content-container');

        // Restore original content
        contentContainer.innerHTML = commentItem.dataset.originalContent;

        // Clean up
        delete commentItem.dataset.originalContent;
    }

    async function handleEditFormSubmission(e) {
        const form = e.target.closest('.comment-edit-form');
        if (!form) return;

        e.preventDefault();

        const commentId = form.dataset.commentId;
        let content = '';

        // Get content from TinyMCE if available
        try {
            if (typeof tinymce !== 'undefined') {
                const editorId = form.querySelector('.edit-tiny-editor').id;
                if (tinymce.get(editorId)) {
                    content = tinymce.get(editorId).getContent();
                } else {
                    content = form.querySelector('.edit-tiny-editor').value;
                }
            } else {
                content = form.querySelector('.edit-tiny-editor').value;
            }
        } catch (e) {
            console.warn('Error getting editor content:', e);
            content = form.querySelector('.edit-tiny-editor').value;
        }

        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="iconify animate-spin mr-1" data-icon="mdi-loading" data-width="14"></span> Đang lưu...';

        try {
            // Submit update via API
            const response = await apiClient.put(`/api/wiki/comments/${commentId}`, { content });

            if (response.success) {
                // Update the comment content
                const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
                const contentContainer = commentItem.querySelector('.comment-content-container');

                contentContainer.innerHTML = `<div class="comment-content prose prose-sm max-w-none">${response.data.content}</div>`;

                // Clean up
                delete commentItem.dataset.originalContent;

                // Show success message
                domUtils.showNotification(response.message || 'Bình luận đã được cập nhật', 'success');

                // Run MathJax typesetting if needed
                runMathJaxTypesetting();
            } else {
                // Show error message
                domUtils.showNotification(response.message || 'Đã xảy ra lỗi khi cập nhật bình luận', 'error');
            }
        } catch (error) {
            console.error('Error updating comment:', error);
            domUtils.showNotification('Đã xảy ra lỗi khi cập nhật bình luận. Vui lòng thử lại.', 'error');
        }
    }

    async function handleDeleteButtonClick(e) {
        const button = e.target.closest('.delete-button');
        if (!button) return;

        if (!confirm('Bạn có chắc chắn muốn xóa bình luận này không?')) {
            return;
        }

        const commentId = button.dataset.commentId;

        try {
            // Submit delete via API
            const response = await apiClient.delete(`/api/wiki/comments/${commentId}`);

            if (response.success) {
                // Remove the comment from the page
                const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
                commentItem.remove();

                // Show success message
                domUtils.showNotification(response.message || 'Bình luận đã được xóa', 'success');
            } else {
                // Show error message
                domUtils.showNotification(response.message || 'Đã xảy ra lỗi khi xóa bình luận', 'error');
            }
        } catch (error) {
            console.error('Error deleting comment:', error);
            domUtils.showNotification('Đã xảy ra lỗi khi xóa bình luận. Vui lòng thử lại.', 'error');
        }
    }

    async function handleLoadMoreComments() {
        if (!loadMoreButton) return;

        const lastId = loadMoreButton.dataset.lastId;
        const limit = 5; // Number of comments to load

        // Show loading state
        const originalButtonText = loadMoreButton.innerHTML;
        loadMoreButton.disabled = true;
        loadMoreButton.innerHTML = '<span class="iconify animate-spin mr-1.5" data-icon="mdi-loading" data-width="18"></span> Đang tải...';

        try {
            // Fetch more comments via API
            const response = await apiClient.get('/api/wiki/comments', {
                question_id: questionId,
                last_id: lastId,
                limit: limit
            });

            if (response.success) {
                // Process the comments
                const comments = response.data.comments;

                // Insert comments before the "Load more" button
                if (comments && comments.length > 0) {
                    comments.forEach(comment => {
                        loadMoreButton.parentNode.insertAdjacentHTML('beforebegin', createCommentHTML(comment));
                    });

                    // Update last ID for next load
                    loadMoreButton.dataset.lastId = comments[comments.length - 1].id;
                }

                // Hide the button if no more comments
                if (!response.data.has_more) {
                    loadMoreButton.parentNode.remove();
                } else {
                    // Restore button
                    loadMoreButton.disabled = false;
                    loadMoreButton.innerHTML = originalButtonText;
                }

                // Run MathJax typesetting if needed
                runMathJaxTypesetting();
            } else {
                // Show error and restore button
                domUtils.showNotification(response.message || 'Không thể tải thêm bình luận', 'error');
                loadMoreButton.disabled = false;
                loadMoreButton.innerHTML = originalButtonText;
            }
        } catch (error) {
            console.error('Error loading more comments:', error);
            domUtils.showNotification('Đã xảy ra lỗi khi tải thêm bình luận. Vui lòng thử lại.', 'error');
            loadMoreButton.disabled = false;
            loadMoreButton.innerHTML = originalButtonText;
        }
    }

    function runMathJaxTypesetting() {
        try {
            if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
                return window.MathJax.typesetPromise();
            }
        } catch (e) {
            console.warn('MathJax typesetting failed:', e);
        }
        return Promise.resolve();
    }

    function createCommentHTML(comment, isReply = false) {
        const date = new Date(comment.created_at);
        const formattedDate = date.toLocaleDateString('vi-VN', { year: 'numeric', month: 'short', day: 'numeric' });
        const isNestedReply = isReply && comment.parent_id;

        // Generate role badge if exists
        let roleBadge = '';
        if (comment.user && comment.user.role) {
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
                        data-author-name="${comment.user ? comment.user.name : 'Unknown'}">
                    <span class="iconify mr-1" data-icon="mdi-reply" data-width="14"></span>
                    Phản hồi
                </button>
            `;
        }

        // Generate edit & delete buttons if user is author
        let editDeleteButtons = '';
        if (comment.user && comment.user.id === parseInt(currentUserId)) {
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
            const userAvatar = document.getElementById('user-avatar').value;
            const userName = document.getElementById('user-name').value;

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

        // Put it all together
        return `
            <div class="comment-item ${isReply ? 'pl-4 mt-3 border-l-2 border-gray-200' : 'bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200'}" data-comment-id="${comment.id}">
                <div class="flex items-start gap-2">
                    <!-- User Avatar -->
                    <div class="flex-shrink-0">
                        <img class="h-8 w-8 rounded-full object-cover border border-gray-100"
                             src="${comment.user && comment.user.avatar ? comment.user.avatar : 'https://ui-avatars.com/api/?name=' + encodeURIComponent((comment.user ? comment.user.name[0] : 'A')) + '&background=6366f1&color=fff'}"
                             alt="${comment.user ? comment.user.name : 'Anonymous'}">
                    </div>

                    <!-- Comment Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Header: Author and Date -->
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center text-sm">
                                <h3 class="font-medium text-gray-900">${comment.user ? comment.user.name : 'Anonymous'}</h3>
                                ${roleBadge}
                            </div>
                            <div class="text-xs text-gray-500">
                                <time datetime="${comment.created_at}" title="${new Date(comment.created_at).toLocaleString('vi-VN')}">
                                    ${formattedDate}
                                </time>
                                ${comment.created_at !== comment.updated_at ?
            `<span class="ml-1 text-xs text-gray-400" title="${new Date(comment.updated_at).toLocaleString('vi-VN')}">
                                        (đã sửa)
                                    </span>` : ''}
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
                                <span class="like-count">${comment.likes_count || 0}</span>
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
});
