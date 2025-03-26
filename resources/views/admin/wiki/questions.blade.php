@extends('admin_layouts.admin')

@section('title', 'Quản Lý Câu Hỏi Wiki Q&A')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header với gradient background -->
        <div class="rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 p-6 shadow-sm mb-6 border border-indigo-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="iconify text-indigo-500 text-3xl mt-1" data-icon="mdi-help-box-multiple"></span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Quản Lý Câu Hỏi Wiki Q&A</h1>
                        <p class="text-gray-600 mt-1">Quản lý và kiểm soát toàn bộ câu hỏi trong hệ thống wiki</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.wiki.settings') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-indigo-200 bg-white py-2 px-4 text-sm font-medium text-indigo-600 hover:bg-indigo-50 shadow-sm">
                        <span class="iconify" data-icon="mdi-cog"></span>
                        Cài đặt Wiki
                    </a>
                    <a href="{{ route('admin.wiki.moderation') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 py-2 px-4 text-sm font-medium text-white hover:bg-indigo-700 shadow-sm">
                        <span class="iconify" data-icon="mdi-shield-check"></span>
                        Hàng đợi kiểm duyệt
                    </a>
                </div>
            </div>
        </div>

        <!-- Thông báo thành công -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-lg border-l-4 border-green-500 bg-green-50 p-4 mb-6" role="alert">
                <div class="flex items-center">
                    <span class="iconify text-green-500 text-xl mr-2" data-icon="mdi-check-circle"></span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Phần bộ lọc -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm mb-6 overflow-hidden">
            <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 flex items-center">
                <span class="iconify text-indigo-500 mr-2" data-icon="mdi-filter-variant"></span>
                <h2 class="text-lg font-semibold text-gray-800">Bộ lọc tìm kiếm</h2>
            </div>

            <div class="p-5">
                <form action="{{ route('admin.wiki.questions') }}" method="GET" id="filter-form">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select id="status" name="status" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                <option value="">Tất cả trạng thái</option>
                                <option value="published" @if(request('status') == 'published') selected @endif>Đã xuất bản</option>
                                <option value="pending" @if(request('status') == 'pending') selected @endif>Đang chờ</option>
                                <option value="hidden" @if(request('status') == 'hidden') selected @endif>Đã ẩn</option>
                            </select>
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                            <select id="category_id" name="category_id" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                <option value="">Tất cả danh mục</option>
                                @foreach(App\Models\Category::all() as $category)
                                    <option value="{{ $category->id }}" @if(request('category_id') == $category->id) selected @endif>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="iconify text-gray-400" data-icon="mdi-magnify"></span>
                                </div>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                       class="form-control pl-10 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200"
                                       placeholder="Tìm theo tiêu đề hoặc nội dung">
                            </div>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex items-center justify-center gap-2 rounded-lg bg-indigo-600 py-2.5 px-4 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 shadow-sm transition-colors">
                                <span class="iconify" data-icon="mdi-filter"></span>
                                Lọc kết quả
                            </button>

                            @if(request()->anyFilled(['status', 'category_id', 'search']))
                                <a href="{{ route('admin.wiki.questions') }}" class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white py-2.5 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 shadow-sm transition-colors">
                                    <span class="iconify" data-icon="mdi-close"></span>
                                    Xóa bộ lọc
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách câu hỏi -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 flex justify-between items-center">
                <div class="flex items-center">
                    <span class="iconify text-indigo-500 mr-2" data-icon="mdi-format-list-text"></span>
                    <h2 class="text-lg font-semibold text-gray-800">Danh sách câu hỏi</h2>
                </div>
                <div class="bg-indigo-100 text-indigo-800 font-medium py-1 px-3 rounded-full text-sm flex items-center">
                    <span class="iconify mr-1" data-icon="mdi-help-circle"></span>
                    {{ number_format($questions->total()) }} câu hỏi
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase">Câu hỏi</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase">Danh mục</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase">Người hỏi</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase">Trạng thái</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase">Lượt xem</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase">Ngày tạo</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase">Thao tác</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($questions as $question)
                        <tr class="border-b hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 font-medium text-gray-900">
                                <div class="max-w-xs truncate" title="{{ $question->title }}">
                                    {{ $question->title }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-medium text-indigo-800">
                                    {{ $question->category->name }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-700">
                                <div class="flex items-center">
                                    <span class="iconify mr-1 text-gray-400" data-icon="mdi-account"></span>
                                    {{ $question->user->name }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($question->status === 'published')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                                        <span class="iconify mr-1" data-icon="mdi-check-circle"></span>
                                        Đã xuất bản
                                    </span>
                                @elseif($question->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-800">
                                        <span class="iconify mr-1" data-icon="mdi-clock"></span>
                                        Đang chờ
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800">
                                        <span class="iconify mr-1" data-icon="mdi-eye-off"></span>
                                        Đã ẩn
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center text-gray-700">
                                    <span class="iconify mr-1 text-gray-400" data-icon="mdi-eye"></span>
                                    {{ number_format($question->views) }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-700">
                                <div class="flex items-center" title="{{ $question->created_at }}">
                                    <span class="iconify mr-1 text-gray-400" data-icon="mdi-calendar"></span>
                                    {{ $question->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('wiki.show', [$question->category->slug, $question->slug]) }}"
                                       target="_blank"
                                       class="text-blue-500 hover:text-blue-700 transition-colors p-1 rounded-full hover:bg-blue-50"
                                       data-bs-toggle="tooltip"
                                       title="Xem câu hỏi">
                                        <span class="iconify text-xl" data-icon="mdi-eye"></span>
                                    </a>

                                    @if($question->status === 'pending')
                                        <form action="{{ route('admin.wiki.questions.approve', $question->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                    class="text-green-500 hover:text-green-700 transition-colors p-1 rounded-full hover:bg-green-50"
                                                    data-bs-toggle="tooltip"
                                                    title="Phê duyệt câu hỏi">
                                                <span class="iconify text-xl" data-icon="mdi-check-circle"></span>
                                            </button>
                                        </form>
                                    @endif

                                    @if($question->status === 'published' || $question->status === 'pending')
                                        <form action="{{ route('admin.wiki.questions.reject', $question->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                    class="text-red-500 hover:text-red-700 transition-colors p-1 rounded-full hover:bg-red-50"
                                                    data-bs-toggle="tooltip"
                                                    title="Ẩn câu hỏi">
                                                <span class="iconify text-xl" data-icon="mdi-eye-off"></span>
                                            </button>
                                        </form>
                                    @endif

                                    @if($question->status === 'hidden')
                                        <form action="{{ route('admin.wiki.questions.approve', $question->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                    class="text-green-500 hover:text-green-700 transition-colors p-1 rounded-full hover:bg-green-50"
                                                    data-bs-toggle="tooltip"
                                                    title="Khôi phục câu hỏi">
                                                <span class="iconify text-xl" data-icon="mdi-restore"></span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <span class="iconify text-gray-300 text-5xl mb-3" data-icon="mdi-help-circle-outline"></span>
                                    <p class="text-lg font-medium text-gray-600 mb-1">Không tìm thấy câu hỏi nào</p>
                                    <p class="text-gray-500">Không có câu hỏi nào phù hợp với tiêu chí tìm kiếm của bạn</p>
                                    @if(request()->anyFilled(['status', 'category_id', 'search']))
                                        <a href="{{ route('admin.wiki.questions') }}" class="mt-3 inline-flex items-center justify-center gap-2 rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-100">
                                            <span class="iconify" data-icon="mdi-filter-remove"></span>
                                            Xóa bộ lọc và hiển thị tất cả
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            @if($questions->hasPages())
                <div class="px-5 py-4 border-t border-gray-200 bg-white">
                    {{ $questions->appends(request()->except('page'))->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Khởi tạo tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Highlight cho hàng hiện tại khi hover
            $('tbody tr').hover(
                function() { $(this).addClass('bg-gray-50'); },
                function() { $(this).removeClass('bg-gray-50'); }
            );

            // Xác nhận trước khi ẩn hoặc phê duyệt
            $('form').submit(function(e) {
                var buttonIcon = $(this).find('button span.iconify').attr('data-icon');

                if (buttonIcon === 'mdi-eye-off') {
                    if (!confirm('Bạn có chắc chắn muốn ẩn câu hỏi này?')) {
                        e.preventDefault();
                    }
                } else if (buttonIcon === 'mdi-check-circle' || buttonIcon === 'mdi-restore') {
                    if (!confirm('Bạn có chắc chắn muốn phê duyệt/khôi phục câu hỏi này?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
@endpush
