@extends('admin_layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Header Section -->
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
                            <tr class="hover:bg-gray-50 job-row
                                    {{ $job->status === \App\Models\AIContentJob::$JOB_STATUS_COMPLETED ? 'bg-green-50/30' :
                                        ($job->status === \App\Models\AIContentJob::$JOB_STATUS_FAILED ? 'bg-red-50/30' :
                                        ($job->status === \App\Models\AIContentJob::$JOB_STATUS_REPLACED ? 'bg-gray-50/50' :
                                        ($job->status === \App\Models\AIContentJob::$JOB_STATUS_CANCELLED ? 'bg-yellow-500' : ''))) }}"
                                id="job-row-{{ $job->id }}">
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
                                                <div class="h-full rounded-full
                                                        {{ $job->status === \App\Models\AIContentJob::$JOB_STATUS_COMPLETED ? 'bg-green-500' :
                                                            ($job->status === \App\Models\AIContentJob::$JOB_STATUS_FAILED ? 'bg-red-500' :
                                                            ($job->status === \App\Models\AIContentJob::$JOB_STATUS_REPLACED ? 'bg-gray-500' : 'bg-indigo-600')) }}"
                                                    style="width: {{ $job->progress_percentage }}%"></div>
                                            </div>
                                        </div>
                                        <span class="text-sm">{{ $job->processed_items }}/{{ $job->total_items }}</span>
                                    </div>

                                    <!-- Success/Failure counts for completed jobs -->
                                    @if($job->status === \App\Models\AIContentJob::$JOB_STATUS_COMPLETED || $job->status === \App\Models\AIContentJob::$JOB_STATUS_FAILED)
                                        <div class="mt-1 flex text-xs">
                                            <span class="font-medium text-green-600 mr-3">
                                                <span class="iconify" data-icon="mdi-check-circle"></span> {{ $job->success_count }}
                                            </span>

                                            <span class="font-medium text-red-600">
                                                <span class="iconify" data-icon="mdi-alert-circle"></span> {{ $job->failed_count }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    @if($job->status == \App\Models\AIContentJob::$JOB_STATUS_PENDING)
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800">
                                            <span class="iconify mr-1" data-icon="mdi-clock-outline"></span>
                                            Đang chờ
                                        </span>
                                    @elseif($job->status == \App\Models\AIContentJob::$JOB_STATUS_PROCESSING)
                                        @php
                                            // Check if job has been processing for more than 10 minutes
                                            $processingTime = \Carbon\Carbon::now()->diffInMinutes($job->updated_at);
                                            $isStuck = $processingTime > 10;
                                        @endphp

                                        <span class="inline-flex items-center rounded-full {{ $isStuck ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800' }} px-2.5 py-1 text-xs font-medium"
                                            title="{{ $isStuck ? 'Có thể bị treo (đang xử lý hơn ' . $processingTime . ' phút)' : 'Đang xử lý' }}">
                                            <span class="iconify mr-1" data-icon="{{ $isStuck ? 'mdi-progress-alert' : 'mdi-progress-clock' }}"></span>
                                            {{ $isStuck ? 'Có thể bị treo' : 'Đang xử lý' }}
                                        </span>
                                    @elseif($job->status == \App\Models\AIContentJob::$JOB_STATUS_COMPLETED)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-green-100 text-green-800">
                                            <span class="iconify mr-1" data-icon="mdi-check-circle"></span>
                                            Hoàn thành
                                        </span>
                                    @elseif($job->status == \App\Models\AIContentJob::$JOB_STATUS_FAILED)
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800">
                                            <span class="iconify mr-1" data-icon="mdi-close-circle"></span>
                                            Thất bại
                                        </span>
                                    @elseif($job->status == \App\Models\AIContentJob::$JOB_STATUS_REPLACED)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-800">
                                            <span class="iconify mr-1" data-icon="mdi-swap-horizontal"></span>
                                            Đã thay thế
                                        </span>
                                    @elseif($job->status == \App\Models\AIContentJob::$JOB_STATUS_CANCELLED)
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-800">
                                            <span class="iconify mr-1" data-icon="mdi-close-circle"></span>
                                            Đã hủy
                                        </span>
                                    @endif

                                    <!-- Time information -->
                                    <div class="mt-1 text-xs text-gray-500">
                                        @if($job->status === \App\Models\AIContentJob::$JOB_STATUS_COMPLETED
                                            || $job->status === \App\Models\AIContentJob::$JOB_STATUS_FAILED
                                            || $job->status === \App\Models\AIContentJob::$JOB_STATUS_REPLACED
                                            || $job->status === \App\Models\AIContentJob::$JOB_STATUS_CANCELLED
                                        )
                                            {{ $job->updated_at->diffForHumans() }}
                                        @elseif($job->status === \App\Models\AIContentJob::$JOB_STATUS_PROCESSING)
                                            Đang xử lý {{ $job->updated_at->diffForHumans() }}
                                        @elseif($job->status === \App\Models\AIContentJob::$JOB_STATUS_PENDING)
                                            Đã tạo {{ $job->created_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <span class="text-sm">{{ $job->created_at->format('Y-m-d H:i:s') }}</span>
                                </td>
                                <td class="border-b border-gray-200 py-4 px-4">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            type="button"
                                            class="view-job-details inline-flex items-center text-indigo-600 hover:text-indigo-800 px-2 py-1 rounded hover:bg-indigo-50"
                                            data-job-id="{{ $job->id }}"
                                        >
                                            <span class="iconify mr-1" data-icon="mdi-chevron-down"></span>
                                            Chi tiết
                                        </button>

                                        @if($job->status === \App\Models\AIContentJob::$JOB_STATUS_PENDING)
                                            <form action="{{ route('admin.ai-dashboard.cancel-job', $job->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="retry-failed-items inline-flex items-center text-red-600 hover:text-red-700 px-2 py-1 rounded hover:bg-orange-50"
                                                    title="Huỷ công việc"
                                                >
                                                    <span class="iconify mr-1" data-icon="mdi-close-circle"></span>
                                                    Huỷ
                                                </button>
                                            </form>
                                        @endif

                                        @if($job->status !== \App\Models\AIContentJob::$JOB_STATUS_PENDING && $job->status !== \App\Models\AIContentJob::$JOB_STATUS_REPLACED)
                                            <button
                                                type="button"
                                                class="rerun-job inline-flex items-center text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                                title="{{ $job->status === 'processing' ? 'Công việc có thể bị treo, tạo bản sao để chạy lại' : 'Tạo bản sao và chạy lại công việc này' }}"
                                                data-job-id="{{ $job->id }}"
                                            >
                                                <span class="iconify mr-1" data-icon="mdi-play-circle-outline"></span>
                                                Chạy lại
                                            </button>
                                        @endif

                                        @if($job->status === \App\Models\AIContentJob::$JOB_STATUS_COMPLETED && $job->failed_count > 0)
                                            <form action="{{ route('admin.ai-dashboard.retry-job', $job->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="retry-failed-items inline-flex items-center text-orange-600 hover:text-orange-800 px-2 py-1 rounded hover:bg-orange-50"
                                                    title="Thử lại {{ $job->failed_count }} mục thất bại"
                                                >
                                                    <span class="iconify mr-1" data-icon="mdi-refresh"></span>
                                                    Thử lại
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <!-- Details row (initially hidden) -->
                            <tr class="job-details-row hidden" id="job-details-{{ $job->id }}">
                                <td colspan="6" class="py-0">
                                    <div class="job-details-content bg-gray-50 p-6 border-t border-b border-gray-200">
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-indigo-600" role="status">
                                                <span class="visually-hidden">Đang tải...</span>
                                            </div>
                                            <p class="mt-2">Đang tải thông tin...</p>
                                        </div>
                                    </div>
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
</div>

<!-- Rerun Job Modal -->
<div class="modal fade" id="rerunJobModal" tabindex="-1" aria-labelledby="rerunJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rerunJobModalLabel">Chạy lại công việc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rerunJobForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-gray-600 mb-4">Bạn có thể chọn nhà cung cấp AI và mô hình khác để tạo nội dung.</p>

                    <div class="mb-3">
                        <label for="rerun-provider" class="form-label font-medium text-gray-700">Nhà Cung Cấp AI</label>
                        <select id="rerun-provider" name="provider" class="form-select w-full rounded border border-gray-300 py-2 px-3">
                            <option value="">Đang tải...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="rerun-model" class="form-label font-medium text-gray-700">Mô Hình AI</label>
                        <select id="rerun-model" name="model" class="form-select w-full rounded border border-gray-300 py-2 px-3" disabled>
                            <option value="">Chọn nhà cung cấp trước</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="rerun-temperature" class="form-label font-medium text-gray-700">
                            Độ sáng tạo (Temperature): <span id="rerun-temperature-value">0.7</span>
                        </label>
                        <input type="range" class="form-range w-full" id="rerun-temperature" name="temperature"
                               min="0" max="1" step="0.1" value="0.7">
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Chính xác</span>
                            <span>Sáng tạo</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Chạy lại</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
    @vite('resources/css/admin/ai-dashboard/jobs_main.css')
@endpush

@push('scripts')
    @vite('resources/js/admin/ai-dashboard/jobs_main.js')
@endpush
