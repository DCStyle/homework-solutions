document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('chat-form');
    const formContainer = document.getElementById('form-container');
    const submitButton = document.getElementById('submit-button');
    const messagesContainer = document.getElementById('messages-container');
    const emptyState = document.getElementById('empty-state');
    const viewQuestionContainer = document.getElementById('view-question-container');
    const viewQuestionLink = document.getElementById('view-question-link');

    let eventSource = null;
    let questionId = null;
    let questionSlug = null;
    let categorySlug = null;
    let contentReceived = false;
    let allChunks = [];
    let responseHTML = '';
    let aiMessageElement = null;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validate form
        if (!form.checkValidity()) {
            return;
        }

        // Disable submit button and show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="iconify animate-spin mr-2" data-icon="mdi-loading" data-width="20"></span> Đang xử lý...';

        // Get form data
        const formData = new FormData(form);

        try {
            // Submit question via AJAX
            const response = await fetch('/hoi-dap/cau-hoi', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                questionId = result.question_id;
                questionSlug = result.question_slug;
                categorySlug = result.category_slug;
                const questionTitle = result.question_title;

                // Remove empty state if present
                if (emptyState) {
                    emptyState.remove();
                }

                // Show the user's question in the messages container
                const questionText = formData.get('content');
                addUserMessage(questionText);

                // Show AI typing indicator
                addAITypingIndicator();

                // Display the view question link
                viewQuestionLink.href = result.question_url;
                viewQuestionContainer.classList.remove('hidden');

                // Start streaming the answer
                startStreaming(questionId);

                // Reset the form for a potential new question
                form.reset();

                // Scroll to the bottom of the messages container
                scrollToBottom();
            } else {
                // Show error message
                showMessage('error', result.message || 'Có lỗi xảy ra khi gửi câu hỏi');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('error', 'Đã xảy ra lỗi khi gửi câu hỏi. Vui lòng thử lại.');
        } finally {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.innerHTML = '<span class="iconify mr-2" data-icon="mdi-send" data-width="20"></span> Gửi câu hỏi của bạn';
        }
    });

    function addUserMessage(content) {
        const userMessage = document.createElement('div');
        userMessage.className = 'message user-message fade-in';
        userMessage.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="iconify text-indigo-600" data-icon="mdi-account" data-width="20"></span>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">Bạn</p>
                        <div class="mt-1 text-sm text-gray-700">
                            ${content}
                        </div>
                    </div>
                </div>
            `;
        messagesContainer.appendChild(userMessage);
    }

    function addAITypingIndicator() {
        aiMessageElement = document.createElement('div');
        aiMessageElement.className = 'message ai-message fade-in';
        aiMessageElement.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="iconify text-blue-600" data-icon="mdi-robot" data-width="20"></span>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">Trợ lý AI</p>
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full flex items-center">
                                <span class="iconify animate-spin mr-1" data-icon="mdi-loading" data-width="14"></span>
                                Đang xử lý
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-gray-700 ai-content">
                            <div class="loader flex">
                                <div class="typing-indicator">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        messagesContainer.appendChild(aiMessageElement);
    }

    function updateAIMessage(content, status = null) {
        if (!aiMessageElement) return;

        const aiContent = aiMessageElement.querySelector('.ai-content');
        if (aiContent) {
            aiContent.innerHTML = content;
        }

        if (status) {
            const statusElement = aiMessageElement.querySelector('.text-xs');
            if (statusElement) {
                if (status === 'complete') {
                    statusElement.innerHTML = '<span class="iconify mr-1" data-icon="mdi-check-circle" data-width="14"></span> Hoàn thành';
                    statusElement.classList.remove('bg-blue-100', 'text-blue-800');
                    statusElement.classList.add('bg-green-100', 'text-green-800');
                } else if (status === 'error') {
                    statusElement.innerHTML = '<span class="iconify mr-1" data-icon="mdi-alert-circle" data-width="14"></span> Lỗi';
                    statusElement.classList.remove('bg-blue-100', 'text-blue-800');
                    statusElement.classList.add('bg-red-100', 'text-red-800');
                }
            }
        }
    }

    function startStreaming(id) {
        // Close any existing event source
        if (eventSource) {
            eventSource.close();
        }

        // Reset tracking variables
        contentReceived = false;
        allChunks = [];
        responseHTML = '';

        // Connect to the streaming endpoint
        eventSource = new EventSource(`/api/wiki/questions/${id}/stream`);

        // Track message count to detect stalled connections
        let messageCount = 0;
        let lastMessageTime = Date.now();

        // Set up a watchdog timer to detect stalled connections
        const connectionWatchdog = setInterval(() => {
            const elapsed = Date.now() - lastMessageTime;
            if (elapsed > 10000 && messageCount > 0) {
                console.log("Connection appears stalled - no messages for 10 seconds");

                // Update AI message to show the connection issue
                if (contentReceived) {
                    updateAIMessage(responseHTML + '<p class="text-yellow-600 mt-4">Có vẻ như kết nối bị gián đoạn. Nếu nội dung không đầy đủ, vui lòng nhấn "Xem trang câu hỏi đầy đủ".</p>');
                }
            }

            // After 20 seconds, assume connection is dead
            if (elapsed > 20000 && messageCount > 0) {
                console.log("Connection timeout - closing EventSource");
                clearInterval(connectionWatchdog);
                eventSource.close();

                // Update AI message
                updateAIMessage(responseHTML + '<p class="text-red-600 mt-4">Kết nối bị đóng. Nội dung có thể không đầy đủ.</p>', 'error');
            }
        }, 5000);

        eventSource.onmessage = function(event) {
            // Update message tracking
            messageCount++;
            lastMessageTime = Date.now();

            // Store for debugging
            allChunks.push(event.data);

            try {
                // Handle the streamed chunks with special attention to UTF-8
                if (event.data.includes("<START_CONTENT>")) {
                    // Process content
                    let processedData = event.data.replace(/<START_CONTENT>/g, '');
                    if (processedData.trim() !== '') {
                        responseHTML += processedData;
                        updateAIMessage(responseHTML);
                        contentReceived = true;
                    }
                }
                else if (event.data.includes("<END_CONTENT>")) {
                    // Process content
                    let processedData = event.data.replace(/<END_CONTENT>/g, '');
                    if (processedData.trim() !== '') {
                        responseHTML += processedData;
                        updateAIMessage(responseHTML);
                    }
                }
                else if (responseHTML === '' && event.data.includes("Đang tìm kiếm thông tin")) {
                    // Loading indicator - already shown
                }
                else {
                    // Regular content
                    responseHTML += event.data;
                    updateAIMessage(responseHTML);
                    contentReceived = true;
                }

                // Scroll to the bottom if near bottom
                if (isNearBottom()) {
                    scrollToBottom();
                }
            } catch (error) {
                console.error("Error processing message:", error);
            }
        };

        eventSource.addEventListener('DONE', function(event) {
            console.log("Streaming completed");

            // Clear the watchdog timer
            clearInterval(connectionWatchdog);

            try {
                // Update AI message status
                updateAIMessage(responseHTML, 'complete');

                // If we didn't receive content, check one more time
                if (!contentReceived || responseHTML.trim() === '') {
                    console.log("No content received, attempting extraction from chunks");

                    // Attempt to extract content from all chunks
                    const allChunksText = allChunks.join('');
                    const contentMatches = allChunksText.match(/<START_CONTENT>([\s\S]*?)<END_CONTENT>/);

                    if (contentMatches && contentMatches[1]) {
                        console.log("Successfully extracted content");
                        updateAIMessage(contentMatches[1], 'complete');
                        contentReceived = true;
                    } else {
                        console.log("Failed to extract content from chunks");
                        updateAIMessage('<p>Không thể hiển thị nội dung. Vui lòng nhấn "Xem trang câu hỏi đầy đủ" để xem câu trả lời.</p>', 'error');

                        // Try to reload the question
                        fetch(`/api/wiki/questions/${questionId}/check-answer`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success' && data.content_length > 0) {
                                    console.log("Content found in database, prompting user to reload");
                                    updateAIMessage('<p>Không thể hiển thị nội dung. Vui lòng nhấn "Xem trang câu hỏi đầy đủ" để xem câu trả lời.</p><p class="mt-3">Có nội dung trong cơ sở dữ liệu, vui lòng tải lại trang.</p>', 'error');
                                }
                            })
                            .catch(error => {
                                console.error("Error checking answer:", error);
                            });
                    }
                }

                // Close the connection
                eventSource.close();
            } catch (error) {
                console.error("Error in DONE handler:", error);
                eventSource.close();
            }
        });

        eventSource.addEventListener('ERROR', function(event) {
            console.log("Streaming error encountered");

            try {
                // Update AI message to error state
                updateAIMessage('<p class="text-red-600">Đã xảy ra lỗi khi tạo câu trả lời. Vui lòng thử lại sau.</p>', 'error');

                // Close the event source
                eventSource.close();
            } catch (error) {
                console.error("Error in ERROR event handler:", error);
            }
        });

        // Better handling of connection errors
        eventSource.onerror = function(e) {
            console.error("EventSource error:", e);
            lastMessageTime = Date.now(); // Update to prevent timeout while in error state

            // If we already have some content, show a warning but keep the connection
            if (contentReceived) {
                updateAIMessage(responseHTML + '<p class="text-red-600 mt-4">Kết nối bị gián đoạn. Nội dung có thể không đầy đủ.</p>');
            } else {
                // If no content received yet, show error message
                updateAIMessage('<p class="text-red-600">Đã xảy ra lỗi kết nối. Vui lòng thử lại hoặc xem trang câu hỏi đầy đủ.</p>', 'error');
            }
        };
    }

    function showMessage(type, message) {
        // Create message element
        const messageElement = document.createElement('div');
        messageElement.className = type === 'error' ? 'error-message' : 'success-message';
        messageElement.innerHTML = message;

        // Insert before the form
        form.parentNode.insertBefore(messageElement, form);

        // Remove after 5 seconds
        setTimeout(() => {
            messageElement.remove();
        }, 5000);
    }

    function isNearBottom() {
        const tolerance = 100; // pixels from bottom
        const scrollPosition = messagesContainer.scrollTop + messagesContainer.clientHeight;
        const scrollHeight = messagesContainer.scrollHeight;

        return scrollHeight - scrollPosition <= tolerance;
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
