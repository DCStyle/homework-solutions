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
    
    // Function to load job details
    function loadJobDetails(jobId, container) {
        // Show loading state
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-indigo-600" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
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