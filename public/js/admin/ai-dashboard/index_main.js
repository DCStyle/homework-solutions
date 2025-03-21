/**
 * AI Dashboard Prompts Management
 * Handles prompt loading, creation, deletion, and management
 * for the AI Dashboard in the admin panel
 */

(function() {
    // Store prompt templates
    let promptTemplates = {};
    let systemMessageTemplates = {};

    /**
     * Initialize the prompt management functionality
     */
    function init() {
        // Load default prompts from server
        loadDefaultPrompts();

        // Initialize Bootstrap 5 modals
        if (document.getElementById('createPromptModal')) {
            var createPromptModal = new bootstrap.Modal(document.getElementById('createPromptModal'), {
                keyboard: true,
                backdrop: true
            });
        }

        // Set up event handlers
        setupEventHandlers();
    }

    /**
     * Load default prompts from the server
     */
    function loadDefaultPrompts() {
        $.ajax({
            url: '/admin/ai-dashboard/prompts/default',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                // Store the default prompts
                promptTemplates = data;

                // Create matching system messages
                systemMessageTemplates = {
                    posts: "Bạn là chuyên gia SEO giáo dục. Nhiệm vụ của bạn là tạo tiêu đề meta và mô tả được tối ưu hóa SEO cho nội dung giáo dục. Tập trung vào sự rõ ràng, từ khóa liên quan và sự hấp dẫn.",
                    chapters: "Bạn là người viết nội dung giáo dục. Nhiệm vụ của bạn là tạo mô tả rõ ràng, cung cấp thông tin cho các chương sách nhấn mạnh giá trị giáo dục và các khái niệm chính được đề cập.",
                    books: "Bạn là chuyên gia nội dung giáo dục. Nhiệm vụ của bạn là tạo mô tả toàn diện cho sách giáo dục giải thích giá trị của chúng, đối tượng mục tiêu và kết quả học tập.",
                    book_groups: "Bạn là chuyên gia chương trình giảng dạy. Nhiệm vụ của bạn là tạo mô tả cho các lĩnh vực môn học giải thích những gì học sinh sẽ học và các kỹ năng họ sẽ phát triển."
                };

                // Set initial values if elements exist
                if ($('#content_type').length && $('#prompt_text').length) {
                    const contentType = $('#content_type').val();
                    $('#prompt_text').val(promptTemplates[contentType] || '');
                    $('#system_message').val(systemMessageTemplates[contentType] || '');
                }
            },
            error: function(error) {
                console.error('Error loading default prompts:', error);
            }
        });
    }

    /**
     * Set up all event handlers
     */
    function setupEventHandlers() {
        // Handle content type change
        $('#content_type').on('change', function() {
            const contentType = $(this).val();
            $('#prompt_text').val(promptTemplates[contentType] || '');
            $('#system_message').val(systemMessageTemplates[contentType] || '');
        });

        // Handle modal buttons
        $('[data-bs-toggle="modal"]').on('click', function() {
            var targetModal = $(this).data('bs-target');
            $(targetModal).modal('show');
        });

        $('[data-bs-dismiss="modal"]').on('click', function() {
            $(this).closest('.modal').modal('hide');
        });

        // Handle create prompt form submission
        $('#createPromptForm').on('submit', function(e) {
            e.preventDefault();
            submitPromptForm(this);
        });

        // Handle delete prompt
        $('.delete-prompt').on('click', function() {
            const promptId = $(this).data('prompt-id');
            deletePrompt(promptId);
        });
    }

    /**
     * Submit the prompt creation form
     * @param {HTMLFormElement} form - The form element
     */
    function submitPromptForm(form) {
        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    if (document.getElementById('createPromptModal')) {
                        bootstrap.Modal.getInstance(document.getElementById('createPromptModal')).hide();
                    }
                    alert('Mẫu đã được tạo thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.error || 'Không thể tạo mẫu'));
                }
            },
            error: function(error) {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi. Vui lòng thử lại.');
            }
        });
    }

    /**
     * Delete a prompt
     * @param {number} promptId - The ID of the prompt to delete
     */
    function deletePrompt(promptId) {
        if (confirm('Bạn có chắc chắn muốn xóa mẫu này không?')) {
            const baseUrl = window.location.origin;

            $.ajax({
                url: `${baseUrl}/admin/ai-dashboard/prompts/${promptId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        alert('Mẫu đã được xóa thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + (data.error || 'Không thể xóa mẫu'));
                    }
                },
                error: function(error) {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi. Vui lòng thử lại.');
                }
            });
        }
    }

    // Initialize when the document is ready
    $(document).ready(init);

    // Make functions available globally if needed
    window.AIPrompts = {
        loadDefaultPrompts: loadDefaultPrompts,
        deletePrompt: deletePrompt,
        getPromptTemplate: function(type) {
            return promptTemplates[type] || '';
        },
        getSystemMessageTemplate: function(type) {
            return systemMessageTemplates[type] || '';
        }
    };
})();
