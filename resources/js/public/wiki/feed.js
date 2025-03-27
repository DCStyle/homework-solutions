/**
 * Wiki Feed Page JavaScript - Server-rendered version
 * This script handles interactions for the server-rendered feed page
 */

document.addEventListener('DOMContentLoaded', () => {
    // Helper function to get CSRF token from meta tag
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    // Helper function to get a cookie value by name
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    // Function to format relative time
    function formatRelativeTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return 'just now';
        }

        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) {
            return `${diffInMinutes}m ago`;
        }

        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) {
            return `${diffInHours}h ago`;
        }

        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 30) {
            return `${diffInDays}d ago`;
        }

        const diffInMonths = Math.floor(diffInDays / 30);
        if (diffInMonths < 12) {
            return `${diffInMonths}mo ago`;
        }

        return `${Math.floor(diffInMonths / 12)}y ago`;
    }

    // Function to handle "View more" buttons for truncated content
    function setupExpandableContent() {
        document.querySelectorAll('[data-expandable]').forEach(expandableEl => {
            const content = expandableEl.querySelector('.expandable-content');
            const viewMoreBtn = expandableEl.querySelector('.view-more-btn');
            const viewLessBtn = expandableEl.querySelector('.view-less-btn');

            if (content && viewMoreBtn && viewLessBtn) {
                // Check if content is overflowing
                if (content.scrollHeight > content.clientHeight) {
                    viewMoreBtn.classList.remove('hidden');

                    viewMoreBtn.addEventListener('click', () => {
                        content.classList.remove('line-clamp-3', 'line-clamp-2');
                        viewMoreBtn.classList.add('hidden');
                        viewLessBtn.classList.remove('hidden');
                    });

                    viewLessBtn.addEventListener('click', () => {
                        content.classList.add(content.dataset.clamp === '2' ? 'line-clamp-2' : 'line-clamp-3');
                        viewMoreBtn.classList.remove('hidden');
                        viewLessBtn.classList.add('hidden');
                    });
                }
            }
        });
    }

    // Function to handle answer form submissions
    function setupAnswerForms() {
        document.querySelectorAll('.js-answer-form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitButton = form.querySelector('button[type="submit"]');
                const textarea = form.querySelector('textarea[name="content"]');
                const errorContainer = form.querySelector('.validation-error');

                // Validate input
                if (!textarea.value.trim()) {
                    errorContainer.textContent = 'Answer cannot be empty';
                    errorContainer.style.display = 'block';
                    return;
                }

                // Disable button and hide previous errors
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
                errorContainer.style.display = 'none';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken() || '',
                            'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            content: textarea.value
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Success - reload the page to show the new answer
                        window.location.reload();
                    } else {
                        // Show error message
                        const errorMsg = data.message ||
                            (data.errors && Object.values(data.errors).flat().join(', ')) ||
                            'Error submitting answer';
                        errorContainer.textContent = errorMsg;
                        errorContainer.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error submitting answer:', error);
                    errorContainer.textContent = 'Network error. Please try again.';
                    errorContainer.style.display = 'block';
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Answer';
                }
            });
        });
    }

    // Function to handle loading more comments dynamically
    function setupLoadMoreComments() {
        document.querySelectorAll('.load-more-comments-btn').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                
                const questionId = this.dataset.questionId;
                const lastCommentId = this.dataset.lastCommentId;
                const totalComments = parseInt(this.dataset.totalComments);
                const loadedCount = parseInt(this.dataset.loadedCount || '3');
                const commentsContainer = this.nextElementSibling;
                
                if (!questionId || !lastCommentId) {
                    console.error('Missing question ID or last comment ID');
                    return;
                }
                
                try {
                    // Show loading state
                    this.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Loading...</span>';
                    
                    // Determine how many comments to load
                    const loadLimit = 3; // Load 3 comments at a time
                    
                    // Make API request to load more comments
                    let url = new URL(`${window.location.origin}/api/wiki/comments`);
                    url.searchParams.append('question_id', questionId);
                    url.searchParams.append('last_id', lastCommentId);
                    url.searchParams.append('limit', loadLimit);
                    
                    console.log('Request URL for comments:', url.toString());
                    
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    // Check response status and log details
                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('Error response:', {
                            status: response.status,
                            statusText: response.statusText,
                            responseText: errorText
                        });
                        throw new Error(`Request failed with status ${response.status}: ${response.statusText}`);
                    }
                    
                    // Parse JSON response
                    let data;
                    try {
                        data = await response.json();
                        console.log('Response data:', data);
                    } catch (jsonError) {
                        console.error('JSON parse error:', jsonError);
                        throw new Error('Failed to parse response as JSON');
                    }
                    
                    if (data.success && data.comments && data.comments.length > 0) {
                        // Show the comments container if it's hidden
                        if (commentsContainer.style.display === 'none') {
                            commentsContainer.style.display = 'block';
                        }
                        
                        // Build HTML for new comments
                        let commentHTML = '';
                        let newLastCommentId = lastCommentId;
                        
                        data.comments.forEach(comment => {
                            // Update last comment ID for next request
                            newLastCommentId = comment.id;
                            
                            // Format the comment creation date
                            const createdAt = new Date(comment.created_at);
                            const timeAgo = timeSince(createdAt);
                            
                            commentHTML += `
                                <div class="flex items-start space-x-2 text-sm comment-item pt-2 pb-3 border-b border-gray-100">
                                    <img src="${comment.user.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user.name[0] || 'A')}&background=e5e7eb&color=6b7280&size=28`}" 
                                         alt="${comment.user.name || 'Anonymous'}" 
                                         class="w-7 h-7 rounded-full flex-shrink-0 mt-0.5">
                                    <div class="flex-grow">
                                        <p class="font-semibold text-gray-700 leading-tight text-xs">${comment.user.name || 'Anonymous'}</p>
                                        <div x-data="{ commentExpanded: false }" class="relative mt-1">
                                            <div x-show="!commentExpanded" class="text-gray-600 prose prose-sm max-w-none line-clamp-2 text-xs">
                                                ${comment.content}
                                            </div>
                                            <div x-show="commentExpanded" class="text-gray-600 prose prose-sm max-w-none text-xs" style="display: none;">
                                                ${comment.content}
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
                                        <p class="text-xs text-gray-400 mt-0.5">${timeAgo}</p>
                                    </div>
                                </div>
                            `;
                        });
                        
                        // Append new comments to container
                        commentsContainer.innerHTML += commentHTML;
                        
                        // Update the total loaded count
                        const newLoadedCount = loadedCount + data.comments.length;
                        
                        // Update button data for next load
                        this.dataset.lastCommentId = newLastCommentId;
                        this.dataset.loadedCount = newLoadedCount.toString();
                        
                        // Update button text or hide if all comments loaded
                        if (newLoadedCount >= totalComments || !data.has_more) {
                            this.style.display = 'none'; // Hide the button
                        } else {
                            const remainingComments = totalComments - newLoadedCount;
                            this.innerHTML = `View ${remainingComments} more ${remainingComments === 1 ? 'comment' : 'comments'}`;
                        }
                        
                        // Initialize Alpine.js components for the new comments
                        initAlpineComponents();
                    } else {
                        // No more comments or error
                        this.innerHTML = 'No more comments';
                        setTimeout(() => {
                            this.style.display = 'none';
                        }, 2000);
                    }
                } catch (error) {
                    console.error('Error loading comments:', error);
                    this.innerHTML = 'Error loading comments';
                }
            });
        });
    }

    // Function to handle copy link buttons
    function setupCopyLinkButtons() {
        document.querySelectorAll('[data-copy-link]').forEach(button => {
            button.addEventListener('click', () => {
                const link = button.dataset.copyLink;
                navigator.clipboard.writeText(link)
                    .then(() => {
                        // Show success feedback
                        const originalText = button.textContent;
                        button.textContent = 'Copied!';
                        setTimeout(() => {
                            button.textContent = originalText;
                        }, 2000);
                    })
                    .catch(err => {
                        console.error('Failed to copy:', err);
                    });
            });
        });
    }

    // Auto-resize textareas as user types
    function setupTextareaAutoResize() {
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // Initial height
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        });
    }

    // Initialize Alpine.js components after dynamic content is loaded
    function initAlpineComponents() {
        if (window.Alpine) {
            // Trigger Alpine to detect new components
            document.querySelectorAll('[x-data]').forEach(el => {
                if (el._x_dataStack) return; // Skip if already initialized
                window.Alpine.initTree(el);
            });
        }
    }

    // Function to handle infinite scrolling for the feed
    function setupInfiniteScroll() {
        const feedContainer = document.getElementById('wiki-feed-container');
        const loadingIndicator = document.getElementById('feed-loading-indicator');
        const endIndicator = document.getElementById('feed-end-indicator');
        const paginationData = document.getElementById('feed-pagination-data');
        
        if (!feedContainer || !loadingIndicator || !paginationData) {
            console.error('Missing required elements for infinite scroll:', {
                feedContainer: !!feedContainer,
                loadingIndicator: !!loadingIndicator,
                paginationData: !!paginationData
            });
            return; // Required elements not found
        }

        // Parse pagination data
        let currentPage = parseInt(paginationData.dataset.currentPage) || 1;
        const lastPage = parseInt(paginationData.dataset.lastPage) || 1;
        let hasMorePages = paginationData.dataset.hasMore === 'true';
        let isLoading = false;

        console.log('Infinite scroll initialized:', {
            currentPage,
            lastPage,
            hasMorePages
        });

        // If we're on the last page already, show the end indicator and hide loading
        if (!hasMorePages) {
            loadingIndicator.classList.add('hidden');
            endIndicator.classList.remove('hidden');
            return;
        }

        // Get current URL parameters (preserve existing filters like category_id if present)
        const urlParams = new URLSearchParams(window.location.search);
        
        // Function to load more questions
        async function loadMoreQuestions() {
            if (isLoading || !hasMorePages) return;
            
            console.log('Loading more questions, page:', currentPage + 1);
            
            isLoading = true;
            
            try {
                // Set the next page number in URL parameters
                urlParams.set('page', currentPage + 1);
                
                // Add ajax parameter to get just the questions without layout
                urlParams.set('ajax', '1');
                
                const requestUrl = `${window.location.pathname}?${urlParams.toString()}`;
                console.log('Request URL:', requestUrl);
                
                // Make the request to get more questions
                const response = await fetch(requestUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`Failed to load more questions: ${response.status} ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success && data.html && data.html.trim() !== '') {
                    // Create a temporary container
                    const tempContainer = document.createElement('div');
                    tempContainer.innerHTML = data.html;
                    
                    // Get all the top-level nodes (question items)
                    const questionNodes = tempContainer.children;
                    console.log(`Found ${questionNodes.length} question items to append`);
                    
                    // Convert HTMLCollection to Array and append each question
                    Array.from(questionNodes).forEach(node => {
                        feedContainer.appendChild(node);
                    });
                    
                    // Update pagination info
                    currentPage++;
                    hasMorePages = data.has_more;
                    
                    // Initialize interactive elements in the new content
                    setupExpandableContent();
                    setupLoadMoreComments();
                    setupCopyLinkButtons();
                    setupTextareaAutoResize();
                    initAlpineComponents();
                    
                    // Show end indicator if we've reached the last page
                    if (!hasMorePages) {
                        loadingIndicator.classList.add('hidden');
                        endIndicator.classList.remove('hidden');
                    } else {
                        // Reset loading state for next load
                        isLoading = false;
                    }
                } else {
                    // No more questions or error occurred
                    console.log('No more questions or empty HTML returned');
                    console.log('Response data:', data);
                    hasMorePages = false;
                    loadingIndicator.classList.add('hidden');
                    endIndicator.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading more questions:', error);
                loadingIndicator.classList.add('hidden');
                
                endIndicator.textContent = 'Error loading more questions. Please refresh the page.';
                endIndicator.classList.remove('hidden');
            } finally {
                isLoading = false;
            }
        }
        
        // Set up intersection observer for infinite scrolling
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && hasMorePages && !isLoading) {
                    console.log('Loading indicator is visible, triggering load');
                    loadMoreQuestions();
                }
            });
        }, {
            rootMargin: '0px 0px 100px 0px', // Start loading when indicator is 100px from viewport
            threshold: 0.1 // Fire when at least 10% of the target is visible
        });
        
        // Observe the loading indicator
        observer.observe(loadingIndicator);
        console.log('Observer attached to loading indicator');
        
        // Trigger immediate load if the indicator is already visible
        if (isElementInViewport(loadingIndicator) && hasMorePages) {
            console.log('Loading indicator is in viewport on page load, triggering immediate load');
            setTimeout(loadMoreQuestions, 500); // Small delay to ensure everything is initialized
        }
    }
    
    // Helper function to check if an element is in the viewport
    function isElementInViewport(el) {
        if (!el) return false;
        
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    // Function to format time string for relative time (e.g., "2 hours ago")
    function timeSince(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        let interval = seconds / 31536000; // years
        
        if (interval > 1) {
            return Math.floor(interval) + " years ago";
        }
        interval = seconds / 2592000; // months
        if (interval > 1) {
            return Math.floor(interval) + " months ago";
        }
        interval = seconds / 86400; // days
        if (interval > 1) {
            return Math.floor(interval) + " days ago";
        }
        interval = seconds / 3600; // hours
        if (interval > 1) {
            return Math.floor(interval) + " hours ago";
        }
        interval = seconds / 60; // minutes
        if (interval > 1) {
            return Math.floor(interval) + " minutes ago";
        }
        return Math.floor(seconds) + " seconds ago";
    }

    // Function to handle loading more answers dynamically
    function setupLoadMoreAnswers() {
        document.querySelectorAll('.load-more-answers-btn').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                
                const questionId = this.dataset.questionId;
                const lastAnswerId = this.dataset.lastAnswerId;
                const totalAnswers = parseInt(this.dataset.totalAnswers);
                const loadedCount = parseInt(this.dataset.loadedCount || '2');
                const answersContainer = this.nextElementSibling;
                
                if (!questionId || !lastAnswerId) {
                    console.error('Missing question ID or last answer ID');
                    return;
                }
                
                try {
                    // Show loading state
                    this.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Loading...</span>';
                    
                    // Determine how many answers to load
                    const loadLimit = 3; // Load 3 answers at a time
                    
                    // Make API request to load more answers
                    const response = await fetch(`/api/wiki/questions/${questionId}/answers?last_id=${lastAnswerId}&limit=${loadLimit}&skip_ai=1`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.answers && data.answers.length > 0) {
                        // Show the answers container if it's hidden
                        if (answersContainer.style.display === 'none') {
                            answersContainer.style.display = 'block';
                        }
                        
                        // Build HTML for new answers
                        let answerHTML = '';
                        let newLastAnswerId = lastAnswerId;
                        
                        data.answers.forEach(answer => {
                            // Update last answer ID for next request
                            newLastAnswerId = answer.id;
                            
                            // Format the answer creation date
                            const createdAt = new Date(answer.created_at);
                            const timeAgo = timeSince(createdAt);
                            
                            answerHTML += `
                                <div class="flex items-start space-x-2 text-sm answer-item pt-2 pb-3 border-b border-gray-100">
                                    <img src="${answer.user.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(answer.user.name[0] || 'A')}&background=e5e7eb&color=6b7280&size=32`}" 
                                         alt="${answer.user.name || 'Anonymous'}" 
                                         class="w-8 h-8 rounded-full flex-shrink-0 mt-0.5">
                                    <div class="flex-grow">
                                        <p class="font-semibold text-gray-800 leading-tight">${answer.user.name || 'Anonymous'}</p>
                                        <div x-data="{ answerExpanded: false }" class="relative mt-1">
                                            <div x-show="!answerExpanded" class="text-gray-700 prose prose-sm max-w-none line-clamp-2">
                                                ${answer.content}
                                            </div>
                                            <div x-show="answerExpanded" class="text-gray-700 prose prose-sm max-w-none" style="display: none;">
                                                ${answer.content}
                                            </div>
                                            <button x-show="!answerExpanded && ($el.previousElementSibling.scrollHeight > $el.previousElementSibling.clientHeight)"
                                                    @click="answerExpanded = true"
                                                    class="absolute bottom-0 right-0 text-xs font-semibold text-blue-600 hover:underline bg-gradient-to-r from-transparent via-white to-white pl-4">
                                                ... View more
                                            </button>
                                            <button x-show="answerExpanded"
                                                    @click="answerExpanded = false"
                                                    class="text-xs font-semibold text-blue-600 hover:underline mt-1"
                                                    style="display: none;">
                                                Show less
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                                    </div>
                                </div>
                            `;
                        });
                        
                        // Append new answers to container
                        answersContainer.innerHTML += answerHTML;
                        
                        // Update the total loaded count
                        const newLoadedCount = loadedCount + data.answers.length;
                        
                        // Update button data for next load
                        this.dataset.lastAnswerId = newLastAnswerId;
                        this.dataset.loadedCount = newLoadedCount.toString();
                        
                        // Update button text or hide if all answers loaded
                        if (newLoadedCount >= totalAnswers || !data.has_more) {
                            this.style.display = 'none'; // Hide the button
                        } else {
                            const remainingAnswers = totalAnswers - newLoadedCount;
                            this.innerHTML = `View ${remainingAnswers} more ${remainingAnswers === 1 ? 'answer' : 'answers'}`;
                        }
                        
                        // Initialize Alpine.js components for the new answers
                        initAlpineComponents();
                    } else {
                        // No more answers or error
                        this.innerHTML = 'No more answers';
                        setTimeout(() => {
                            this.style.display = 'none';
                        }, 2000);
                    }
                } catch (error) {
                    console.error('Error loading answers:', error);
                    this.innerHTML = 'Error loading answers';
                }
            });
        });
    }

    // Initialize all interactive elements
    function init() {
        setupExpandableContent();
        setupAnswerForms();
        setupLoadMoreComments();
        setupLoadMoreAnswers();
        setupCopyLinkButtons();
        setupTextareaAutoResize();
        
        // Initialize infinite scroll after a short delay to ensure page is fully loaded
        setTimeout(setupInfiniteScroll, 100);
    }

    // Run initialization
    init();
});
