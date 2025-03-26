document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const searchForm = document.querySelector('.search-form') || document.querySelector('form[action*="tim-kiem"]');
    const resultsContainer = document.getElementById('search-results');

    // Skip if we're not on the search page or elements don't exist
    if (!searchInput || !searchForm) {
        console.log('Search form elements not found, exiting search script');
        return;
    }

    // Create search results container if it doesn't exist
    if (!resultsContainer) {
        const searchBox = searchForm.closest('.bg-white');
        const newResultsContainer = document.createElement('div');
        newResultsContainer.id = 'search-results';
        newResultsContainer.className = 'absolute z-10 w-full bg-white rounded-lg shadow-lg mt-1 overflow-hidden hidden';
        searchBox.style.position = 'relative';
        searchBox.appendChild(newResultsContainer);
    }

    // Reference to results container (either existing or newly created)
    const searchResults = document.getElementById('search-results');

    // Debounce function to prevent too many requests while typing
    let debounceTimer;
    const debounce = (callback, time) => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(callback, time);
    };

    // Function to perform search
    function performSearch(query) {
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        console.log(`Performing search for query: "${query}"`);

        // Show loading state
        searchResults.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600 mr-2"></div>
                Đang tìm kiếm...
            </div>
        `;
        searchResults.classList.remove('hidden');

        // Get any category or book group filters from the form
        const params = new URLSearchParams();
        params.append('q', query);

        // Add category filter if present
        const categorySelect = document.getElementById('category_id');
        if (categorySelect && categorySelect.value) {
            params.append('category_id', categorySelect.value);
        }

        // Add book group filter if present
        const bookGroupSelect = document.getElementById('book_group_id');
        if (bookGroupSelect && bookGroupSelect.value) {
            params.append('book_group_id', bookGroupSelect.value);
        }

        // Lower the threshold for more results
        params.append('threshold', '0.3');

        // Use basic search instead of vector search for better performance
        params.append('use_basic_search', '1');

        // Add the ajax parameter to indicate this is an AJAX request
        params.append('ajax', '1');

        // Log the full URL for debugging
        const searchUrl = `/hoi-dap/tim-kiem?${params.toString()}`;
        console.log(`Sending search request to: ${searchUrl}`);

        // Fetch results
        const startTime = performance.now();
        fetch(searchUrl)
            .then(response => {
                const endTime = performance.now();
                console.log(`Search request took ${endTime - startTime}ms`);

                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Search response:', data);

                if (data.success) {
                    renderResults(data.results);
                } else {
                    showError('Không thể tìm kiếm. Vui lòng thử lại.');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                showError('Đã xảy ra lỗi khi tìm kiếm.');
            });
    }

    // Function to render search results
    function renderResults(results) {
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div class="p-6 text-center text-gray-500">
                    <span class="iconify block mx-auto mb-2" data-icon="mdi-search-off" data-width="28"></span>
                    Không tìm thấy kết quả phù hợp
                </div>
            `;
            return;
        }

        let html = `<div class="divide-y divide-gray-100">`;

        results.forEach(question => {
            // Highlight the matching text in title and content
            let title = question.title;
            let content = stripHtml(question.content);

            // Truncate content to a reasonable length
            if (content.length > 120) {
                content = content.substring(0, 120) + '...';
            }

            html += `
                <a href="/hoi-dap/${question.category_slug}/${question.slug}" class="block p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 line-clamp-1">${title}</h4>
                            <p class="mt-1 text-xs text-gray-500 line-clamp-2">${content}</p>
                            <div class="mt-2 flex items-center text-xs text-gray-400">
                                <span class="inline-flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-account" data-width="14"></span>
                                    ${question.user ? question.user.name : 'Ẩn danh'}
                                </span>
                                <span class="mx-2">•</span>
                                <span class="inline-flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-folder-outline" data-width="14"></span>
                                    ${question.category ? question.category.name : 'Chưa phân loại'}
                                </span>
                                <span class="mx-2">•</span>
                                <span class="inline-flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-eye-outline" data-width="14"></span>
                                    ${question.views || 0} lượt xem
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            `;
        });

        html += `</div>`;

        // Show a link to view all results
        html += `
            <div class="bg-gray-50 p-3 text-center">
                <a href="/hoi-dap/tim-kiem?q=${encodeURIComponent(searchInput.value)}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 inline-flex items-center justify-center">
                    Xem tất cả kết quả
                    <span class="iconify ml-1" data-icon="mdi-arrow-right" data-width="14"></span>
                </a>
            </div>
        `;

        searchResults.innerHTML = html;
    }

    // Function to show error message
    function showError(message) {
        searchResults.innerHTML = `
            <div class="p-6 text-center text-red-500">
                <span class="iconify block mx-auto mb-2" data-icon="mdi-alert-circle" data-width="28"></span>
                ${message}
            </div>
        `;
    }

    // Function to strip HTML tags
    function stripHtml(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        return temp.textContent || temp.innerText || '';
    }

    // Handle input event to trigger search
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        // Clear previous timer and set a new one
        debounce(() => {
            performSearch(query);
        }, 300);

        // Clear results if input is empty
        if (query.length < 2) {
            searchResults.classList.add('hidden');
        }
    });

    // Handle form submission
    searchForm.addEventListener('submit', function(e) {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            e.preventDefault();

            // Show a message that search term is too short
            searchResults.innerHTML = `
                <div class="p-6 text-center text-yellow-500">
                    <span class="iconify block mx-auto mb-2" data-icon="mdi-information" data-width="28"></span>
                    Vui lòng nhập ít nhất 2 ký tự để tìm kiếm
                </div>
            `;
            searchResults.classList.remove('hidden');
        }
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // Keep search results open when clicking inside
    searchResults.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') {
            // Allow normal link behavior
            return;
        }
        e.stopPropagation();
    });

    // Focus searchInput again after clicking in search results (better UX)
    searchResults.addEventListener('click', function() {
        searchInput.focus();
    });

    // Add some debug logging to help troubleshoot
    console.log('Live search initialized with elements:', {
        searchInput: searchInput ? true : false,
        searchForm: searchForm ? true : false,
        resultsContainer: searchResults ? true : false
    });
});
