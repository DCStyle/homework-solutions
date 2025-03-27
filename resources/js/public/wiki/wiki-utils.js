/**
 * Shared utilities for Wiki Q&A system JavaScript components
 * This file centralizes common functionality used across different Wiki pages
 */

// API Client for common AJAX operations
export const apiClient = {
    /**
     * Get CSRF token from meta tag
     * @returns {string} CSRF token
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    },

    /**
     * Get a cookie value by name
     * @param {string} name Cookie name
     * @returns {string|null} Cookie value
     */
    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    },

    /**
     * Make a GET request to the specified URL
     * @param {string} url - The URL to fetch
     * @param {Object} params - Query parameters
     * @returns {Promise} - Promise that resolves to the response data
     */
    async get(url, params = {}) {
        try {
            // Add parameters to URL if present
            const queryString = Object.keys(params).length
                ? '?' + new URLSearchParams(params).toString()
                : '';

            const response = await fetch(`${url}${queryString}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('GET request failed:', error);
            throw error;
        }
    },

    /**
     * Make a POST request to the specified URL
     * @param {string} url - The URL to post to
     * @param {Object} data - The data to send
     * @returns {Promise} - Promise that resolves to the response data
     */
    async post(url, data = {}) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken() || '',
                    'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN') || '',
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw {
                    status: response.status,
                    message: errorData.message || 'An error occurred',
                    errors: errorData.errors || {},
                    response: errorData
                };
            }

            return await response.json();
        } catch (error) {
            console.error('POST request failed:', error);
            throw error;
        }
    },

    /**
     * Make a PUT request to update an existing resource
     * @param {string} url - The URL to send the request to
     * @param {Object} data - The data to send
     * @returns {Promise} - Promise that resolves to the response data
     */
    async put(url, data = {}) {
        try {
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken() || '',
                    'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN') || '',
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw {
                    status: response.status,
                    message: errorData.message || 'An error occurred',
                    errors: errorData.errors || {},
                    response: errorData
                };
            }

            return await response.json();
        } catch (error) {
            console.error('PUT request failed:', error);
            throw error;
        }
    },

    /**
     * Make a DELETE request to remove a resource
     * @param {string} url - The URL to send the request to
     * @returns {Promise} - Promise that resolves to the response data
     */
    async delete(url) {
        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken() || '',
                    'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN') || '',
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw {
                    status: response.status,
                    message: errorData.message || 'An error occurred',
                    errors: errorData.errors || {},
                    response: errorData
                };
            }

            return await response.json();
        } catch (error) {
            console.error('DELETE request failed:', error);
            throw error;
        }
    }
};

// DOM utilities for common UI operations
export const domUtils = {
    /**
     * Show a loading indicator in the given element
     * @param {HTMLElement} element - The element to show loading in
     * @param {string} message - Optional loading message
     * @returns {HTMLElement} The created loading element
     */
    showLoading(element, message = 'Đang tải...') {
        const loadingEl = document.createElement('div');
        loadingEl.className = 'loading-indicator flex items-center justify-center p-4';
        loadingEl.innerHTML = `
            <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600 mr-2"></div>
            <span class="text-gray-500">${message}</span>
        `;

        // Clear the element and append the loading indicator
        if (element.classList.contains('relative')) {
            loadingEl.classList.add('absolute', 'inset-0', 'bg-white', 'bg-opacity-80', 'z-10');
        }
        element.appendChild(loadingEl);

        return loadingEl;
    },

    /**
     * Hide the loading indicator
     * @param {HTMLElement} loadingEl - The loading element to hide
     */
    hideLoading(loadingEl) {
        if (loadingEl && loadingEl.parentNode) {
            loadingEl.parentNode.removeChild(loadingEl);
        }
    },

    /**
     * Display a notification message
     * @param {string} message - The message to display
     * @param {string} type - The message type ('success', 'error', 'info', 'warning')
     * @param {number} duration - How long to show the notification (ms)
     */
    showNotification(message, type = 'success', duration = 3000) {
        // Create notification element
        const notification = document.createElement('div');

        // Set appropriate styling based on type
        const bgColor = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'info': 'bg-blue-500',
            'warning': 'bg-yellow-500'
        }[type] || 'bg-gray-700';

        const icon = {
            'success': 'mdi-check-circle',
            'error': 'mdi-alert-circle',
            'info': 'mdi-information',
            'warning': 'mdi-alert'
        }[type] || 'mdi-bell';

        notification.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg ${bgColor} text-white flex items-center z-50`;
        notification.innerHTML = `
            <span class="iconify mr-2" data-icon="${icon}" data-width="20"></span>
            <span>${message}</span>
        `;

        // Add to the document
        document.body.appendChild(notification);

        // Fade-in effect
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s ease';

        setTimeout(() => {
            notification.style.opacity = '1';
        }, 10);

        // Auto-remove after duration
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, duration);
    },

    /**
     * Format a date as a relative time (e.g., "2 hours ago")
     * @param {string} dateString - The date string to format
     * @returns {string} Formatted relative time
     */
    formatRelativeTime(dateString) {
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
    },

    /**
     * Strip HTML tags from a string
     * @param {string} html - The HTML string to strip
     * @returns {string} The plain text content
     */
    stripHtml(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        return temp.textContent || temp.innerText || '';
    },

    /**
     * Check if element is near the bottom (for infinite scrolling)
     * @param {HTMLElement} element - The scrollable element
     * @param {number} threshold - How close to the bottom (in pixels)
     * @returns {boolean} Whether the element is near bottom
     */
    isNearBottom(element, threshold = 200) {
        const scrollPosition = element.scrollTop + element.clientHeight;
        const scrollHeight = element.scrollHeight;
        return scrollHeight - scrollPosition <= threshold;
    },

    /**
     * Scroll an element to the bottom
     * @param {HTMLElement} element - The element to scroll
     * @param {boolean} smooth - Whether to use smooth scrolling
     */
    scrollToBottom(element, smooth = false) {
        element.scrollTo({
            top: element.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto'
        });
    }
};

// Form utilities for form handling
export const formUtils = {
    /**
     * Get form values as an object
     * @param {HTMLFormElement} form - The form element
     * @returns {Object} Form values as key-value pairs
     */
    getFormData(form) {
        const formData = new FormData(form);
        const data = {};

        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }

        return data;
    },

    /**
     * Display form validation errors
     * @param {Object} errors - Error object with field names as keys
     * @param {HTMLFormElement} form - The form element
     */
    showValidationErrors(errors, form) {
        // Clear existing error messages
        const existingErrors = form.querySelectorAll('.validation-error');
        existingErrors.forEach(el => el.remove());

        // Add new error messages
        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                // Add error class to input
                input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');

                // Create error message element
                const errorEl = document.createElement('p');
                errorEl.className = 'validation-error text-red-500 text-xs mt-1';
                errorEl.textContent = errors[field][0]; // First error message

                // Insert after the input
                input.parentNode.insertBefore(errorEl, input.nextSibling);
            }
        });
    },

    /**
     * Clear validation errors from a form
     * @param {HTMLFormElement} form - The form element
     */
    clearValidationErrors(form) {
        // Remove error classes from inputs
        const inputs = form.querySelectorAll('.border-red-500');
        inputs.forEach(input => {
            input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        });

        // Remove error messages
        const errors = form.querySelectorAll('.validation-error');
        errors.forEach(el => el.remove());
    },

    /**
     * Reset a form to its initial state
     * @param {HTMLFormElement} form - The form element
     */
    resetForm(form) {
        // Reset the form
        form.reset();

        // Clear validation errors
        this.clearValidationErrors(form);

        // Reset TinyMCE editors if present
        if (typeof tinymce !== 'undefined') {
            const editorIds = Array.from(form.querySelectorAll('textarea[id]'))
                .map(el => el.id)
                .filter(id => tinymce.get(id));

            editorIds.forEach(id => {
                try {
                    tinymce.get(id).setContent('');
                } catch (e) {
                    console.warn('Error resetting TinyMCE:', e);
                }
            });
        }
    }
};
