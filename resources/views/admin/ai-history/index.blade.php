@extends('admin_layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-indigo-50 p-6 rounded-xl">
            <div>
                <h2 class="text-3xl font-bold text-indigo-800">Lịch Sử Tạo AI</h2>
                <p class="mt-1 text-indigo-600">Xem lại tất cả các lần tạo nội dung bằng AI</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ai-dashboard.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-indigo-300 py-2 px-4 text-center font-medium text-indigo-700 hover:bg-indigo-100 sm:px-6">
                    <span class="iconify" data-icon="mdi-arrow-left"></span>
                    Quay Lại Trang Chính
                </a>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white p-6 rounded-xl shadow">
            <form action="{{ route('admin.ai-history.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                        <input type="text" name="search" id="search" value="{{ $search ?? '' }}" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Nhập từ khóa...">
                    </div>
                    
                    <div>
                        <label for="content_type" class="block text-sm font-medium text-gray-700 mb-1">Loại nội dung</label>
                        <select name="content_type" id="content_type" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tất cả</option>
                            @foreach($contentTypes as $type)
                                <option value="{{ $type }}" {{ isset($contentType) && $contentType == $type ? 'selected' : '' }}>
                                    @switch($type)
                                        @case('posts')
                                            Bài viết
                                            @break
                                        @case('chapters')
                                            Chương sách
                                            @break
                                        @case('books')
                                            Sách
                                            @break
                                        @case('book_groups')
                                            Nhóm sách
                                            @break
                                        @default
                                            {{ $type }}
                                    @endswitch
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select name="status" id="status" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tất cả</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption }}" {{ isset($status) && $status == $statusOption ? 'selected' : '' }}>
                                    @switch($statusOption)
                                        @case('processing')
                                            Đang xử lý
                                            @break
                                        @case('completed')
                                            Hoàn thành
                                            @break
                                        @case('failed')
                                            Thất bại
                                            @break
                                        @default
                                            {{ $statusOption }}
                                    @endswitch
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 py-2 px-4 text-center font-medium text-white hover:bg-indigo-700">
                            <span class="iconify" data-icon="mdi-filter"></span>
                            Lọc
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại nội dung</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model AI</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kết quả</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($histories as $history)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $history->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @switch($history->content_type)
                                        @case('posts')
                                            Bài viết
                                            @break
                                        @case('chapters')
                                            Chương sách
                                            @break
                                        @case('books')
                                            Sách
                                            @break
                                        @case('book_groups')
                                            Nhóm sách
                                            @break
                                        @default
                                            {{ $history->content_type }}
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $history->model }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($history->status)
                                        @case('processing')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Đang xử lý
                                            </span>
                                            @break
                                        @case('completed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Hoàn thành
                                            </span>
                                            @break
                                        @case('failed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Thất bại
                                            </span>
                                            @break
                                        @default
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $history->status }}
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <span class="mr-2 text-green-600">{{ $history->successful_items }}</span>/<span class="mx-2 text-gray-600">{{ $history->total_items }}</span>
                                        @if($history->total_items > 0)
                                            <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-2 overflow-hidden">
                                                <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $history->success_rate }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $history->success_rate }}%</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.ai-history.show', $history->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                            <span class="iconify" data-icon="mdi-eye"></span>
                                        </a>
                                        <a href="{{ route('admin.ai-history.download', $history->id) }}" class="text-green-600 hover:text-green-900">
                                            <span class="iconify" data-icon="mdi-download"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Không tìm thấy dữ liệu
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 bg-white border-t border-gray-200">
                {{ $histories->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
