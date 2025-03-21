@extends('admin_layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-indigo-50 p-6 rounded-xl">
            <div>
                <h2 class="text-3xl font-bold text-indigo-800">Chi Tiết Quá Trình Tạo AI</h2>
                <p class="mt-1 text-indigo-600">ID: {{ $history->id }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ai-history.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-indigo-300 py-2 px-4 text-center font-medium text-indigo-700 hover:bg-indigo-100 sm:px-6">
                    <span class="iconify" data-icon="mdi-arrow-left"></span>
                    Quay Lại
                </a>
                <a href="{{ route('admin.ai-history.download', $history->id) }}" class="inline-flex items-center justify-center gap-2.5 rounded-md bg-green-600 py-2 px-4 text-center font-medium text-white hover:bg-green-700 sm:px-6">
                    <span class="iconify" data-icon="mdi-download"></span>
                    Tải Xuống CSV
                </a>
            </div>
        </div>

        <!-- History Summary -->
        <div class="bg-white p-6 rounded-xl shadow">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Thời gian tạo</h3>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $history->created_at->format('d/m/Y H:i:s') }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500">Người tạo</h3>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $history->user->name }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500">Loại nội dung</h3>
                    <p class="mt-1 text-lg font-medium text-gray-900">
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
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500">Model AI</h3>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $history->model }}</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="col-span-1 lg:col-span-3">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Kết quả tạo</h3>
                    <div class="flex items-center">
                        <div class="flex items-center gap-1">
                            <span class="text-green-600 font-medium">{{ $history->successful_items }}</span>
                            <span class="text-gray-500">thành công</span>
                        </div>
                        <span class="mx-2 text-gray-400">|</span>
                        <div class="flex items-center gap-1">
                            <span class="text-red-600 font-medium">{{ $history->failed_items }}</span>
                            <span class="text-gray-500">thất bại</span>
                        </div>
                        <span class="mx-2 text-gray-400">|</span>
                        <div class="flex items-center gap-1">
                            <span class="text-gray-600 font-medium">{{ $history->total_items }}</span>
                            <span class="text-gray-500">tổng số</span>
                        </div>

                        <div class="ml-4 flex items-center gap-2">
                            <div class="w-36 bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $history->success_rate }}%"></div>
                            </div>
                            <span class="text-sm text-gray-500">{{ $history->success_rate }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prompt Information -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin Prompt</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Nội dung Prompt</h4>
                        <div class="p-4 rounded-md bg-gray-50 border border-gray-200 whitespace-pre-wrap">
                            {{ $history->prompt_text }}
                        </div>
                    </div>
                    
                    @if(!empty($history->settings['system_message']))
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">System Message</h4>
                        <div class="p-4 rounded-md bg-gray-50 border border-gray-200 whitespace-pre-wrap">
                            {{ $history->settings['system_message'] }}
                        </div>
                    </div>
                    @endif
                </div>
                
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Cài đặt</h4>
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-gray-500">Nhiệt độ:</span>
                            <span class="ml-2 font-medium">{{ $history->settings['temperature'] ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500">Số tokens tối đa:</span>
                            <span class="ml-2 font-medium">{{ $history->settings['max_tokens'] ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500">HTML Meta:</span>
                            <span class="ml-2 font-medium">{{ isset($history->settings['use_html_meta']) && $history->settings['use_html_meta'] ? 'Có' : 'Không' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Processed Items -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Danh sách nội dung đã xử lý</h3>
                <p class="mt-1 text-sm text-gray-500">Chi tiết các nội dung đã được xử lý bởi AI</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ghi chú</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Liên kết</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($history->processed_items ?? [] as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['id'] ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['name'] ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($item['status']))
                                        @if($item['status'] === 'success')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Thành công
                                            </span>
                                        @elseif($item['status'] === 'failed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Thất bại
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $item['status'] }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-500 max-w-xs">
                                    @if(isset($item['error']))
                                        <span class="text-red-500">{{ $item['error'] }}</span>
                                    @elseif(isset($item['note']))
                                        <span>{{ $item['note'] }}</span>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if(isset($relatedItems) && $relatedItems->isNotEmpty())
                                        @php
                                            $relatedItem = $relatedItems->firstWhere('id', $item['id'] ?? 0);
                                        @endphp
                                        
                                        @if($relatedItem)
                                            @switch($history->content_type)
                                                @case('posts')
                                                    <a href="{{ route('admin.posts.edit', $relatedItem->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        <span class="iconify" data-icon="mdi-pencil"></span> Sửa
                                                    </a>
                                                    @break
                                                @case('chapters')
                                                    <a href="{{ route('admin.bookChapters.edit', $relatedItem->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        <span class="iconify" data-icon="mdi-pencil"></span> Sửa
                                                    </a>
                                                    @break
                                                @case('books')
                                                    <a href="{{ route('admin.books.edit', $relatedItem->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        <span class="iconify" data-icon="mdi-pencil"></span> Sửa
                                                    </a>
                                                    @break
                                                @case('book_groups')
                                                    <a href="{{ route('admin.bookGroups.edit', $relatedItem->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        <span class="iconify" data-icon="mdi-pencil"></span> Sửa
                                                    </a>
                                                    @break
                                                @default
                                                    <span>-</span>
                                            @endswitch
                                        @else
                                            <span>-</span>
                                        @endif
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Không có dữ liệu về các nội dung đã xử lý
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Error Messages -->
        @if(!empty($history->error_messages))
            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Thông báo lỗi</h3>
                <div class="p-4 rounded-md bg-red-50 border border-red-200 text-sm text-red-800 whitespace-pre-wrap">
                    {{ $history->error_messages }}
                </div>
            </div>
        @endif
    </div>
@endsection
