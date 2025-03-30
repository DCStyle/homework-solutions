/**
 * Optimized AI Dashboard Main Script without Bootstrap dependency
 */
document.addEventListener('DOMContentLoaded', function() {
    // Load dashboard stats asynchronously
    loadDashboardStats();

    // Lazy load the prompts list
    lazyLoadPrompts();

    // Set up event handlers with efficient delegation
    setupEventHandlers();
});

/**
 * Load dashboard stats from the API
 */
function loadDashboardStats() {
    fetch('/admin/ai-dashboard/stats-api')
        .then(response => response.json())
        .then(data => {
            updateStatsUI(data);
        })
        .catch(error => {
            console.error('Error loading dashboard stats:', error);
        });
}

/**
 * Update the UI with stats data
 */
function updateStatsUI(data) {
    // Update overview cards
    document.getElementById('posts-count').textContent = data.missingData.posts_no_meta;
    document.getElementById('chapters-count').textContent = data.missingData.chapters_no_desc;
    document.getElementById('books-count').textContent = data.missingData.books_no_desc;
    document.getElementById('groups-count').textContent = data.missingData.book_groups_no_desc;

    // Update percentage badges
    document.getElementById('posts-percentage').textContent = data.seoProgress.posts.percentage + '%';
    document.getElementById('chapters-percentage').textContent = data.seoProgress.chapters.percentage + '%';
    document.getElementById('books-percentage').textContent = data.seoProgress.books.percentage + '%';
    document.getElementById('groups-percentage').textContent = data.seoProgress.book_groups.percentage + '%';

    // Update progress bars
    document.getElementById('posts-progress-bar').style.width = data.seoProgress.posts.percentage + '%';
    document.getElementById('chapters-progress-bar').style.width = data.seoProgress.chapters.percentage + '%';
    document.getElementById('books-progress-bar').style.width = data.seoProgress.books.percentage + '%';
    document.getElementById('groups-progress-bar').style.width = data.seoProgress.book_groups.percentage + '%';

    // Update progress percentages
    document.getElementById('posts-progress-percentage').textContent = data.seoProgress.posts.percentage + '%';
    document.getElementById('chapters-progress-percentage').textContent = data.seoProgress.chapters.percentage + '%';
    document.getElementById('books-progress-percentage').textContent = data.seoProgress.books.percentage + '%';
    document.getElementById('groups-progress-percentage').textContent = data.seoProgress.book_groups.percentage + '%';

    // Update progress numbers
    document.getElementById('posts-progress-numbers').textContent =
        data.seoProgress.posts.completed + ' / ' + data.seoProgress.posts.total + ' đã tối ưu hóa';
    document.getElementById('chapters-progress-numbers').textContent =
        data.seoProgress.chapters.completed + ' / ' + data.seoProgress.chapters.total + ' đã tối ưu hóa';
    document.getElementById('books-progress-numbers').textContent =
        data.seoProgress.books.completed + ' / ' + data.seoProgress.books.total + ' đã tối ưu hóa';
    document.getElementById('groups-progress-numbers').textContent =
        data.seoProgress.book_groups.completed + ' / ' + data.seoProgress.book_groups.total + ' đã tối ưu hóa';

    // Remove skeleton loading classes
    document.querySelectorAll('.skeleton-text').forEach(el => {
        el.classList.remove('skeleton-text');
    });

    document.querySelectorAll('.skeleton-progress').forEach(el => {
        el.classList.remove('skeleton-progress');
    });
}

/**
 * Lazy load the prompts list
 */
function lazyLoadPrompts() {
    // Use IntersectionObserver for better performance
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Load prompts only when near viewport
                fetch('/admin/ai-dashboard/lazy-load-prompts')
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('.loading-state').classList.add('hidden');
                        const promptsListContainer = document.getElementById('prompts-list');
                        promptsListContainer.innerHTML = html;
                        promptsListContainer.classList.remove('hidden');

                        // Initialize delete prompt handlers
                        setupDeletePromptHandlers();

                        // Disconnect observer after loading
                        observer.disconnect();
                    })
                    .catch(error => {
                        console.error('Error loading prompts:', error);
                        document.querySelector('.loading-state').innerHTML =
                            '<p class="text-red-500">Error loading prompts. Please try refreshing.</p>';
                    });
            }
        });
    }, {
        rootMargin: '100px' // Load when 100px from viewport
    });

    // Start observing the prompts container
    observer.observe(document.getElementById('custom-prompts-container'));
}

/**
 * Set up event handlers
 */
function setupEventHandlers() {
    // Handle prompt creation form submission with more efficient event handling
    const createPromptForm = document.getElementById('createPromptForm');
    if (createPromptForm) {
        createPromptForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitPromptForm(this);
        });
    }

    // Set up content type change handler
    const contentTypeSelect = document.getElementById('content_type');
    if (contentTypeSelect) {
        contentTypeSelect.addEventListener('change', function() {
            updatePromptTemplate(this.value);
        });
    }

    // Handle modal buttons with event delegation
    document.addEventListener('click', function(e) {
        // Modal toggle buttons
        if (e.target.matches('[data-toggle="modal"]') || e.target.closest('[data-toggle="modal"]')) {
            const button = e.target.matches('[data-toggle="modal"]') ?
                e.target : e.target.closest('[data-toggle="modal"]');
            const targetModal = button.getAttribute('data-target');

            // Use our custom modal implementation
            $(targetModal).customModal('show');
        }
    });
}

/**
 * Set up delete prompt handlers
 */
function setupDeletePromptHandlers() {
    document.querySelectorAll('.delete-prompt').forEach(button => {
        button.addEventListener('click', function() {
            const promptId = this.getAttribute('data-prompt-id');
            deletePrompt(promptId);
        });
    });
}

/**
 * Submit the prompt creation form
 */
function submitPromptForm(form) {
    const formData = new FormData(form);

    // Show loading state on button
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.innerHTML = '<svg class="w-5 h-5 mr-2 animate-spin" viewBox="0 0 24 24"><path fill="currentColor" d="M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z" /></svg> Processing...';
    submitButton.disabled = true;

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide modal
                $('#createPromptModal').customModal('hide');

                // Show success message
                createAlert('Mẫu đã được tạo thành công!', 'success', 'body', true);

                // Reload the page
                window.location.reload();
            } else {
                createAlert('Lỗi: ' + (data.error || 'Không thể tạo mẫu'), 'danger', 'body', true);

                // Reset button
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            createAlert('Đã xảy ra lỗi. Vui lòng thử lại.', 'danger', 'body', true);

            // Reset button
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
}

/**
 * Delete a prompt
 */
function deletePrompt(promptId) {
    if (confirm('Bạn có chắc chắn muốn xóa mẫu này không?')) {
        const baseUrl = window.location.origin;

        fetch(`${baseUrl}/admin/ai-dashboard/prompts/${promptId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    createAlert('Mẫu đã được xóa thành công!', 'success', 'body', true);
                    window.location.reload();
                } else {
                    createAlert('Lỗi: ' + (data.error || 'Không thể xóa mẫu'), 'danger', 'body', true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                createAlert('Đã xảy ra lỗi. Vui lòng thử lại.', 'danger', 'body', true);
            });
    }
}

/**
 * Update prompt template based on content type
 */
function updatePromptTemplate(contentType) {
    // Fetch default prompts if not already loaded
    fetch('/admin/ai-dashboard/prompts/default')
        .then(response => response.json())
        .then(templates => {
            const promptText = document.getElementById('prompt_text');
            const systemMessage = document.getElementById('system_message');

            if (promptText && templates[contentType]) {
                promptText.value = templates[contentType];
            }

            // Also update system message
            if (systemMessage) {
                fetch('/admin/ai-dashboard/system-message/' + contentType)
                    .then(response => response.text())
                    .then(message => {
                        systemMessage.value = message;
                    });
            }
        })
        .catch(error => {
            console.error('Error fetching prompt templates:', error);
        });
}
