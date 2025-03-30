$(document).ready(function() {
    // Initialize all modals
    $('#createApiKeyModal, #editApiKeyModal, #testConnectionModal').each(function() {
        $(this).customModal();
    });

    // Toggle API key visibility
    const toggleButtons = document.querySelectorAll('.toggle-key-visibility');
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const parentCell = this.closest('td');
            const maskedSpan = parentCell.querySelector('.api-key-masked');
            const fullSpan = parentCell.querySelector('.api-key-full');
            const icon = this.querySelector('.iconify');

            if (maskedSpan.classList.contains('hidden')) {
                maskedSpan.classList.remove('hidden');
                fullSpan.classList.add('hidden');
                icon.setAttribute('data-icon', 'mdi-eye');
            } else {
                maskedSpan.classList.add('hidden');
                fullSpan.classList.remove('hidden');
                icon.setAttribute('data-icon', 'mdi-eye-off');
            }
        });
    });

    // Copy API key to clipboard
    const copyButtons = document.querySelectorAll('.copy-key');
    copyButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const key = this.getAttribute('data-key');

            copyToClipboard(key)
                .then(() => {
                    // Show success message using the custom toast function
                    window.createAlert('API key copied to clipboard!', 'success', 'body', true);
                })
                .catch((err) => {
                    // Show error message
                    window.createAlert('Failed to copy: ' + err, 'danger', 'body', true);
                });
        });
    });

    // Edit API Key
    const editButtons = document.querySelectorAll('.edit-api-key');
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const provider = this.getAttribute('data-provider');
            const email = this.getAttribute('data-email');
            const key = this.getAttribute('data-key');
            const isActive = this.getAttribute('data-active') === '1';

            // Set form action URL with the correct route
            document.getElementById('editApiKeyForm').setAttribute('action', `/admin/ai-api-keys/${id}`);

            // Populate form fields
            document.getElementById('edit_provider').value = provider;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_api_key').value = key;
            document.getElementById('edit_is_active').checked = isActive;

            // Trigger change event for provider dropdown to show the appropriate help text
            const event = new Event('change');
            document.getElementById('edit_provider').dispatchEvent(event);

            // Show the modal using jQuery custom modal
            $('#editApiKeyModal').customModal('show');
        });
    });

    // Provider help text toggle in create modal
    const providerSelect = document.getElementById('provider');
    if (providerSelect) {
        providerSelect.addEventListener('change', function() {
            // Hide all help divs first
            document.querySelectorAll('.provider-help > div').forEach(div => div.classList.add('hidden'));

            // Show the selected provider's help
            const provider = this.value;
            if (provider) {
                const helpDiv = document.getElementById('help-' + provider);
                if (helpDiv) helpDiv.classList.remove('hidden');
            }
        });
    }

    // Provider help text toggle in edit modal
    const editProviderSelect = document.getElementById('edit_provider');
    if (editProviderSelect) {
        editProviderSelect.addEventListener('change', function() {
            // Hide all help divs first
            document.querySelectorAll('.provider-help > div').forEach(div => div.classList.add('hidden'));

            // Show the selected provider's help
            const provider = this.value;
            if (provider) {
                const helpDiv = document.getElementById('edit-help-' + provider);
                if (helpDiv) helpDiv.classList.remove('hidden');
            }
        });
    }

    // Delete confirmation
    const deleteForms = document.querySelectorAll('.delete-key-form');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this API key? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Test API key connection
    const testLinks = document.querySelectorAll('.test-connection');
    testLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            // Get the API key ID from the URL
            const href = this.getAttribute('href');

            // Show the modal with loading indicator
            const loadingElement = document.getElementById('test-loading');
            const successElement = document.getElementById('test-success');
            const errorElement = document.getElementById('test-error');

            loadingElement.classList.remove('hidden');
            successElement.classList.add('hidden');
            errorElement.classList.add('hidden');

            // Show modal using jQuery custom modal
            $('#testConnectionModal').customModal('show');

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Make AJAX request to test the connection
            fetch(href, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    // Hide loading indicator
                    loadingElement.classList.add('hidden');

                    if (data.success) {
                        // Show success message
                        successElement.classList.remove('hidden');

                        // Format and display the response
                        let formattedResponse = '';
                        if (typeof data.result === 'object') {
                            formattedResponse = JSON.stringify(data.result, null, 2);
                        } else {
                            formattedResponse = data.result || "Request successful";
                        }

                        const responseElement = document.getElementById('test-response');
                        responseElement.textContent = formattedResponse;
                    } else {
                        // Show error message
                        errorElement.classList.remove('hidden');

                        // Display error details
                        const errorMessageElement = document.getElementById('test-error-message');
                        errorMessageElement.textContent = data.message || 'Connection failed';
                    }
                })
                .catch(error => {
                    // Hide loading indicator
                    loadingElement.classList.add('hidden');

                    // Show error message
                    errorElement.classList.remove('hidden');

                    // Display error details
                    const errorMessageElement = document.getElementById('test-error-message');
                    errorMessageElement.textContent = 'Network error: ' + error.message;
                });
        });
    });
});

// Helper function to copy text to clipboard
function copyToClipboard(text) {
    // Use modern clipboard API if available
    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text);
    }

    // Fallback for older browsers
    return new Promise((resolve, reject) => {
        try {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            // Move element outside viewport to make it invisible
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            const successful = document.execCommand('copy');
            document.body.removeChild(textarea);

            if (successful) {
                resolve();
            } else {
                reject(new Error('Unable to copy'));
            }
        } catch (err) {
            reject(err);
        }
    });
}
