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

    // AI Answer Edit Functionality
    const editBtn = document.getElementById('edit-ai-answer-btn');
    const cancelBtn = document.getElementById('cancel-edit-btn');
    const answerContent = document.getElementById('answer-content');
    const answerEditForm = document.getElementById('answer-edit-form');
    const aiAnswerForm = document.getElementById('ai-answer-form');
    const answerId = document.getElementById('answer_id')?.value;

    if (editBtn) {
        editBtn.addEventListener('click', function() {
            answerContent.classList.add('hidden');
            answerEditForm.classList.remove('hidden');
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            answerContent.classList.remove('hidden');
            answerEditForm.classList.add('hidden');
        });
    }

    if (aiAnswerForm && answerId) {
        aiAnswerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get the TinyMCE content
            const content = tinymce.get('answer_content').getContent();

            // Show loading state
            const submitBtn = aiAnswerForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Đang lưu...';

            // Send AJAX request to update the answer
            fetch(`/api/wiki/questions/{{ $question->id }}/answers/${answerId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    content: content
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        // Update the displayed content
                        answerContent.innerHTML = content;

                        // Show a success message
                        const toast = document.createElement('div');
                        toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg transition-opacity duration-500';
                        toast.textContent = 'Cập nhật thành công!';
                        document.body.appendChild(toast);

                        // Hide the form and show the content
                        answerContent.classList.remove('hidden');
                        answerEditForm.classList.add('hidden');

                        // Remove toast after 3 seconds
                        setTimeout(() => {
                            toast.style.opacity = '0';
                            setTimeout(() => {
                                document.body.removeChild(toast);
                            }, 500);
                        }, 3000);
                    } else {
                        alert('Có lỗi xảy ra: ' + (data.error || 'Không thể cập nhật câu trả lời'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật câu trả lời');
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
        });
    }
});
