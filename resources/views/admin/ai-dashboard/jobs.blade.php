@extends('admin_layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Công Việc AI</h2>
            <p class="mt-1 text-gray-600">Quản lý và theo dõi các công việc tạo nội dung AI</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai-dashboard.stats') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-stroke py-2 px-4 text-center font-medium text-black hover:bg-gray-50 sm:px-6">
                <span class="iconify" data-icon="mdi-arrow-left"></span>
                Quay Lại Thống Kê
            </a>
        </div>
    </div>
    <!-- Jobs Table -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-md">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-xl font-semibold text-gray-800">Công Việc Đã Xếp Hàng</h3>
        </div>
        @if($jobs->isEmpty())
            <div class="rounded-lg bg-gray-50 p-8 text-center">
                <div class="mb-4">
                    <span class="iconify text-4xl text-gray-400" data-icon="mdi-text-box-outline"></span>
                </div>
                <h4 class="mb-2 text-lg font-medium text-gray-700">Không có công việc nào</h4>
                <p class="text-gray-500">Bạn chưa tạo bất kỳ công việc tạo nội dung hàng loạt nào</p>
            </div>
        @else
            <div class="max-w-full overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="py-4 px-4 font-medium text-gray-700">ID</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Loại Nội Dung</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Tiến Độ</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Trạng Thái</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Ngày Tạo</th>
                            <th class="py-4 px-4 font-medium text-gray-700">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs as $job)
                            <tr class="hover:bg-gray-50">
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <span class="text-xs font-medium">{{ $job->batch_id }}</span>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    @php
                                        $typeLabels = [
                                            'posts' => 'Bài Viết',
                                            'chapters' => 'Chương Sách',
                                            'books' => 'Sách',
                                            'book_groups' => 'Nhóm Sách'
                                        ];
                                    @endphp
                                    <span class="font-medium">{{ $typeLabels[$job->content_type] ?? $job->content_type }}</span>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <div class="flex items-center">
                                        <div class="mr-4 w-full max-w-36">
                                            <div class="h-2 w-full rounded-full bg-gray-200">
                                                <div class="h-full rounded-full bg-indigo-600" style="width: {{ $job->progress_percentage }}%"></div>
                                            </div>
                                        </div>
                                        <span class="text-sm">{{ $job->processed_items }}/{{ $job->total_items }}</span>
                                    </div>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    @if($job->status == 'pending')
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            Đang chờ
                                        </span>
                                    @elseif($job->status == 'processing')
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                            Đang xử lý
                                        </span>
                                    @elseif($job->status == 'completed')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            Hoàn thành
                                        </span>
                                    @elseif($job->status == 'failed')
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                            Thất bại
                                        </span>
                                    @endif
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <span class="text-sm">{{ $job->created_at->format('Y-m-d H:i:s') }}</span>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <button 
                                        type="button" 
                                        class="view-job-details inline-flex items-center text-indigo-600 hover:text-indigo-800"
                                        data-job-id="{{ $job->id }}"
                                    >
                                        <span class="iconify mr-1" data-icon="mdi-eye"></span>
                                        Chi tiết
                                    </button>
                                    
                                    @if($job->status === 'completed' && $job->failed_count > 0)
                                        <form action="{{ route('admin.ai-dashboard.retry-job', $job->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button 
                                                type="submit" 
                                                class="retry-failed-items inline-flex items-center text-orange-600 hover:text-orange-800 ml-3"
                                            >
                                                <span class="iconify mr-1" data-icon="mdi-refresh"></span>
                                                Thử lại
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
    
    <!-- Job Details Modal -->
    <div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-labelledby="jobDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jobDetailsModalLabel">Chi Tiết Công Việc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div id="job-details-content">
                        <div class="text-center">
                            <div class="spinner-border text-indigo-600" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p class="mt-2">Đang tải thông tin...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Job details modal functionality
        const jobDetailsModal = new bootstrap.Modal(document.getElementById('jobDetailsModal'));
        
        // Event handlers for job details modal
        document.querySelectorAll('.view-job-details').forEach(button => {
            button.addEventListener('click', function() {
                const jobId = this.getAttribute('data-job-id');
                
                // Show modal
                jobDetailsModal.show();
                
                // Show loading state
                document.getElementById('job-details-content').innerHTML = `
                    <div class="text-center">
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
                                'failed': 'Thất bại'
                            }[data.status] || data.status;
                            
                            // Format content type
                            const contentTypeText = {
                                'posts': 'Bài Viết',
                                'chapters': 'Chương Sách',
                                'books': 'Sách',
                                'book_groups': 'Nhóm Sách'
                            }[data.content_type] || data.content_type;
                            
                            // Set job details
                            let failedItemsHtml = '';
                            if (data.failed_items && data.failed_items.length > 0) {
                                failedItemsHtml = `
                                    <div class="mt-4">
                                        <h6 class="font-medium">Các mục thất bại (${data.failed_items.length})</h6>
                                        <div class="mt-2 max-h-40 overflow-y-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="bg-gray-50">
                                                        <th class="py-2 px-3 text-left">ID</th>
                                                        <th class="py-2 px-3 text-left">Lỗi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${data.failed_items.map(item => `
                                                        <tr class="border-t border-gray-200">
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
                            
                            document.getElementById('job-details-content').innerHTML = `
                                <div class="space-y-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h5 class="font-medium text-xl">${contentTypeText}</h5>
                                            <p class="text-sm text-gray-600">ID: ${data.batch_id}</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                            data.status === 'completed' ? 'bg-green-100 text-green-800' :
                                            data.status === 'failed' ? 'bg-red-100 text-red-800' :
                                            data.status === 'processing' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-blue-100 text-blue-800'
                                        }">
                                            ${statusText}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
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
                                        <div>
                                            <p class="text-sm text-gray-600">Tổng Kết</p>
                                            <p class="text-sm mt-1">
                                                <span class="text-green-600">${data.success_count}</span> thành công,
                                                <span class="text-red-600">${data.failed_count}</span> thất bại
                                                (${data.processed_items}/${data.total_items})
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">Cài Đặt</p>
                                        <div class="mt-1 rounded bg-gray-50 p-3 text-sm">
                                            <p><span class="font-medium">Mô Hình:</span> ${data.settings?.model || 'N/A'}</p>
                                            <p><span class="font-medium">Nhiệt Độ:</span> ${data.settings?.temperature || 'N/A'}</p>
                                            <p><span class="font-medium">Token Tối Đa:</span> ${data.settings?.max_tokens || 'N/A'}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">Prompt</p>
                                        <div class="mt-1 rounded bg-gray-50 p-3 text-sm">
                                            <pre class="whitespace-pre-wrap">${data.settings?.prompt || 'N/A'}</pre>
                                        </div>
                                    </div>
                                    
                                    ${failedItemsHtml}
                                </div>
                            `;
                        } else {
                            document.getElementById('job-details-content').innerHTML = `
                                <div class="text-center text-red-500">
                                    <p>Lỗi khi tải thông tin: ${data.error || 'Lỗi không xác định'}</p>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        document.getElementById('job-details-content').innerHTML = `
                            <div class="text-center text-red-500">
                                <p>Lỗi khi tải thông tin: ${error.message || 'Lỗi kết nối'}</p>
                            </div>
                        `;
                    });
            });
        });
    });
</script>
@endpush