document.addEventListener('DOMContentLoaded', function() {
    // Toggle job details
    document.querySelectorAll('.view-job-details').forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-job-id');
            const detailsRow = document.getElementById(`job-details-${jobId}`);
            const jobRow = document.getElementById(`job-row-${jobId}`);
            const detailsContent = detailsRow.querySelector('.job-details-content');
            const icon = this.querySelector('.iconify');

            // Toggle the details row
            if (detailsRow.classList.contains('hidden')) {
                // Show details row
                detailsRow.classList.remove('hidden');
                jobRow.classList.add('active');
                icon.setAttribute('data-icon', 'mdi-chevron-up');

                // Load details if not already loaded
                if (!detailsRow.getAttribute('data-loaded')) {
                    loadJobDetails(jobId, detailsContent);
                    detailsRow.setAttribute('data-loaded', 'true');
                }
            } else {
                // Hide details row
                detailsRow.classList.add('hidden');
                jobRow.classList.remove('active');
                icon.setAttribute('data-icon', 'mdi-chevron-down');
            }
        });
    });

    // Initialize custom modal for rerun job
    let currentJobId = null;

    // Initialize providers/models selectors
    initProviderModelSelectors();

    // Handle Rerun Job button clicks
    document.querySelectorAll('.rerun-job').forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-job-id');
            currentJobId = jobId;

            // Set the form action
            document.getElementById('rerunJobForm').setAttribute('action', `/admin/ai-dashboard/jobs/${jobId}/rerun`);

            // Reset form values
            document.getElementById('rerun-temperature').value = 0.7;
            document.getElementById('rerun-temperature-value').textContent = '0.7';

            // Load providers if needed
            fetchProviders();

            // Show the modal
            $('#rerunJobModal').customModal('show');
        });
    });

    // Temperature slider input handler
    document.getElementById('rerun-temperature').addEventListener('input', function() {
        document.getElementById('rerun-temperature-value').textContent = this.value;
    });

    // Provider change event - load appropriate models
    document.getElementById('rerun-provider').addEventListener('change', function() {
        const provider = this.value;
        if (provider) {
            fetchModelsForProvider(provider);
        } else {
            // Clear and disable model selector if no provider is selected
            const modelSelect = document.getElementById('rerun-model');
            modelSelect.innerHTML = '<option value="">Chọn nhà cung cấp trước</option>';
            modelSelect.disabled = true;
        }
    });

    /**
     * Initialize provider and model selectors
     */
    function initProviderModelSelectors() {
        // Check for saved preferences in localStorage
        const savedProvider = localStorage.getItem('selectedProvider');
        const savedModel = localStorage.getItem('selectedModel');

        // Fetch initial providers list
        fetchProviders(savedProvider, savedModel);
    }

    /**
     * Fetch available AI providers
     */
    function fetchProviders(selectedProvider = null, selectedModel = null) {
        const providerSelect = document.getElementById('rerun-provider');

        // Show loading state
        providerSelect.innerHTML = '<option value="">Đang tải nhà cung cấp...</option>';

        // Fetch providers from API
        fetch('/admin/ai-dashboard/providers')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear loading option
                    providerSelect.innerHTML = '<option value="">Chọn nhà cung cấp AI</option>';

                    // Add provider options
                    Object.entries(data.providers).forEach(([code, name]) => {
                        const option = document.createElement('option');
                        option.value = code;
                        option.textContent = name;
                        providerSelect.appendChild(option);
                    });

                    // Select saved provider if available
                    if (selectedProvider && providerSelect.querySelector(`option[value="${selectedProvider}"]`)) {
                        providerSelect.value = selectedProvider;

                        // Trigger change event to load models
                        const event = new Event('change');
                        providerSelect.dispatchEvent(event);

                        // Load models for this provider
                        fetchModelsForProvider(selectedProvider, selectedModel);
                    }
                } else {
                    providerSelect.innerHTML = '<option value="">Không thể tải nhà cung cấp</option>';
                }
            })
            .catch(error => {
                console.error('Error loading providers:', error);
                providerSelect.innerHTML = '<option value="">Lỗi: Không thể tải nhà cung cấp</option>';
            });
    }

    /**
     * Fetch models for selected provider
     */
    function fetchModelsForProvider(provider, selectedModel = null) {
        const modelSelect = document.getElementById('rerun-model');

        // Show loading state
        modelSelect.innerHTML = '<option value="">Đang tải mô hình...</option>';
        modelSelect.disabled = true;

        // Fetch models for the selected provider
        fetch(`/admin/ai-dashboard/providers/${provider}/models`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.models) {
                    // Clear loading option
                    modelSelect.innerHTML = '<option value="">Chọn mô hình AI</option>';

                    // Add model options
                    Object.entries(data.models).forEach(([id, name]) => {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = name;
                        modelSelect.appendChild(option);
                    });

                    // Enable selection
                    modelSelect.disabled = false;

                    // Select saved model if available
                    if (selectedModel && modelSelect.querySelector(`option[value="${selectedModel}"]`)) {
                        modelSelect.value = selectedModel;
                    }
                } else {
                    modelSelect.innerHTML = '<option value="">Không có mô hình nào</option>';
                }
            })
            .catch(error => {
                console.error('Error loading models:', error);
                modelSelect.innerHTML = '<option value="">Lỗi: Không thể tải mô hình</option>';
            })
            .finally(() => {
                modelSelect.disabled = false;
            });
    }

    // Handle form submission - save preferences
    const rerunJobForm = document.getElementById('rerunJobForm');
    if (rerunJobForm) {
        rerunJobForm.addEventListener('submit', function(e) {
            // Store selected provider and model in localStorage
            const provider = document.getElementById('rerun-provider').value;
            const model = document.getElementById('rerun-model').value;

            if (provider) {
                localStorage.setItem('selectedProvider', provider);
            }

            if (model) {
                localStorage.setItem('selectedModel', model);
            }
        });
    }

    // Function to load job details
    function loadJobDetails(jobId, container) {
        // Show loading state
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="w-10 h-10 mx-auto border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="mt-2">Đang tải thông tin...</p>
            </div>
        `;

        // Load job details
        fetch(`/admin/ai-dashboard/jobs/${jobId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Format status text
                    const statusText = {
                        'pending': 'Đang chờ',
                        'processing': 'Đang xử lý',
                        'completed': 'Hoàn thành',
                        'failed': 'Thất bại',
                        'replaced': 'Đã thay thế'
                    }[data.status] || data.status;

                    // Format content type
                    const contentTypeText = {
                        'posts': 'Bài Viết',
                        'chapters': 'Chương Sách',
                        'books': 'Sách',
                        'book_groups': 'Nhóm Sách'
                    }[data.content_type] || data.content_type;

                    // Build HTML for failed items section
                    let failedItemsHtml = '';
                    if (data.failed_items && data.failed_items.length > 0) {
                        failedItemsHtml = `
                            <div class="mt-4 bg-red-50 p-4 rounded-lg border border-red-200">
                                <h6 class="font-medium text-red-700 mb-2">Các mục thất bại (${data.failed_items.length})</h6>
                                <div class="mt-2 max-h-40 overflow-y-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-red-100">
                                                <th class="py-2 px-3 text-left">ID</th>
                                                <th class="py-2 px-3 text-left">Lỗi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${data.failed_items.map(item => `
                                                <tr class="border-t border-red-200">
                                                    <td class="py-2 px-3">${item.id}</td>
                                                    <td class="py-2 px-3">${item.error}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;
                    }

                    // Build HTML for affected items section
                    let affectedItemsHtml = '';
                    if (data.item_ids && data.item_ids.length > 0) {
                        // Prepare table headers based on content type
                        let tableHeaders = '';
                        switch (data.content_type) {
                            case 'posts':
                                tableHeaders = `
                                    <th class="py-2 px-3 text-left">ID</th>
                                    <th class="py-2 px-3 text-left">Tiêu đề</th>
                                    <th class="py-2 px-3 text-left">Đường dẫn</th>
                                    <th class="py-2 px-3 text-left">Trạng thái</th>
                                    <th class="py-2 px-3 text-left">Hành động</th>
                                `;
                                break;
                            case 'chapters':
                            case 'books':
                            case 'book_groups':
                                tableHeaders = `
                                    <th class="py-2 px-3 text-left">ID</th>
                                    <th class="py-2 px-3 text-left">Tên</th>
                                    <th class="py-2 px-3 text-left">Đường dẫn</th>
                                    <th class="py-2 px-3 text-left">Trạng thái</th>
                                    <th class="py-2 px-3 text-left">Hành động</th>
                                `;
                                break;
                            default:
                                tableHeaders = `
                                    <th class="py-2 px-3 text-left">ID</th>
                                    <th class="py-2 px-3 text-left">Trạng thái</th>
                                    <th class="py-2 px-3 text-left">Hành động</th>
                                `;
                        }

                        affectedItemsHtml = `
                            <div class="mt-6">
                                <h6 class="font-medium text-gray-700 mb-2">Danh sách nội dung đã xử lý (${data.item_ids.length})</h6>
                                <div class="mt-2 max-h-80 overflow-y-auto bg-white rounded-lg border border-gray-200">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-gray-100 sticky top-0">
                                                ${tableHeaders}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${data.item_ids.map((id, index) => {
                            // Check if this ID is in the failed items list
                            const failedItem = data.failed_items ? data.failed_items.find(item => item.id == id) : null;
                            const isSuccess = !failedItem;
                            const status = isSuccess ?
                                `<span class="text-green-600">Thành công</span>` :
                                `<span class="text-red-600">Thất bại</span>`;

                            // Get content details if available
                            const details = data.content_details && data.content_details[id] ? data.content_details[id] : null;

                            if (details) {
                                // Prepare content path based on content type
                                let contentPath = '';
                                switch (data.content_type) {
                                    case 'posts':
                                        contentPath = [
                                            details.chapter_name,
                                            details.book_name,
                                            details.group_name
                                        ].filter(Boolean).join(' / ');
                                        break;
                                    case 'chapters':
                                        contentPath = [
                                            details.book_name,
                                            details.group_name
                                        ].filter(Boolean).join(' / ');
                                        break;
                                    case 'books':
                                        contentPath = details.group_name || '';
                                        break;
                                    case 'book_groups':
                                        contentPath = details.category_name || '';
                                        break;
                                }

                                // Build action buttons
                                const editButton = `
                                                        <a href="${details.edit_url}" target="_blank"
                                                           class="content-action-btn edit-btn" title="Chỉnh sửa">
                                                            <span class="iconify" data-icon="mdi-pencil"></span>
                                                        </a>
                                                    `;

                                const playgroundButton = `
                                                        <a href="${details.playground_url}" target="_blank"
                                                           class="content-action-btn playground-btn" title="Mở trong Playground">
                                                            <span class="iconify" data-icon="mdi-flask-outline"></span>
                                                        </a>
                                                    `;

                                return `
                                                        <tr class="border-t border-gray-200">
                                                            <td class="py-2 px-3">${id}</td>
                                                            <td class="py-2 px-3 font-medium">
                                                                ${details.title || 'N/A'}
                                                            </td>
                                                            <td class="py-2 px-3">
                                                                <div class="content-path tooltip" data-tooltip="${contentPath}">
                                                                    ${contentPath || 'N/A'}
                                                                </div>
                                                            </td>
                                                            <td class="py-2 px-3">${status}</td>
                                                            <td class="py-2 px-3">
                                                                ${editButton}
                                                                ${playgroundButton}
                                                            </td>
                                                        </tr>
                                                    `;
                            } else {
                                // Fallback for items without details
                                return `
                                                        <tr class="border-t border-gray-200">
                                                            <td class="py-2 px-3">${id}</td>
                                                            <td class="py-2 px-3">N/A</td>
                                                            <td class="py-2 px-3">N/A</td>
                                                            <td class="py-2 px-3">${status}</td>
                                                            <td class="py-2 px-3">N/A</td>
                                                        </tr>
                                                    `;
                            }
                        }).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;
                    }

                    // Set job details HTML
                    container.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h5 class="font-medium text-xl text-indigo-700">${contentTypeText}</h5>
                                        <p class="text-sm text-gray-600">ID: ${data.batch_id}</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                        data.status === 'completed' ? 'bg-green-100 text-green-800' :
                            data.status === 'failed' ? 'bg-red-100 text-red-800' :
                                data.status === 'processing' ? 'bg-yellow-100 text-yellow-800' :
                                    data.status === 'replaced' ? 'bg-gray-100 text-gray-800' :
                                        'bg-blue-100 text-blue-800'
                    }">
                                        ${statusText}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <p class="text-sm text-gray-600">Tiến độ</p>
                                    <div class="mt-1 flex items-center">
                                        <div class="mr-3 w-full">
                                            <div class="h-2 w-full rounded-full bg-gray-200">
                                                <div class="h-full rounded-full bg-indigo-600" style="width: ${data.progress_percentage}%"></div>
                                            </div>
                                        </div>
                                        <span class="text-sm">${data.progress_percentage}%</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <p class="text-sm text-gray-600">Tổng Kết</p>
                                    <p class="text-sm mt-1">
                                        <span class="text-green-600">${data.success_count}</span> thành công,
                                        <span class="text-red-600">${data.failed_count}</span> thất bại
                                        (${data.processed_items}/${data.total_items})
                                    </p>
                                </div>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600">Cài Đặt</p>
                                    <div class="mt-1 rounded bg-white p-3 text-sm border border-gray-200">
                                        <p><span class="font-medium">Mô Hình:</span> ${data.settings?.model || 'N/A'}</p>
                                        <p><span class="font-medium">Nhiệt Độ:</span> ${data.settings?.temperature || 'N/A'}</p>
                                        <p><span class="font-medium">Token Tối Đa:</span> ${data.settings?.max_tokens || 'N/A'}</p>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-600">Prompt</p>
                                    <div class="mt-1 rounded bg-white p-3 text-sm border border-gray-200 max-h-28 overflow-y-auto">
                                        <pre class="whitespace-pre-wrap">${data.settings?.prompt || 'N/A'}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        ${failedItemsHtml}
                        ${affectedItemsHtml}
                    `;
                } else {
                    container.innerHTML = `
                        <div class="text-center text-red-500">
                            <p>Lỗi khi tải thông tin: ${data.error || 'Lỗi không xác định'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                container.innerHTML = `
                    <div class="text-center text-red-500">
                        <p>Lỗi khi tải thông tin: ${error.message || 'Lỗi kết nối'}</p>
                    </div>
                `;
            });
    }
});
