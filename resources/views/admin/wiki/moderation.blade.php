@extends('admin_layouts.admin')

@section('title', 'Kiểm Duyệt Wiki Q&A')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header với gradient background -->
        <div class="rounded-xl bg-gradient-to-r from-amber-50 to-yellow-50 p-6 shadow-sm mb-6 border border-amber-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="iconify text-amber-500 text-3xl mt-1" data-icon="mdi-shield-check"></span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Kiểm Duyệt Wiki Q&A</h1>
                        <p class="text-gray-600 mt-1">Xem xét và kiểm duyệt các câu hỏi được gửi bởi người dùng</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.wiki.settings') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-amber-200 bg-white py-2 px-4 text-sm font-medium text-amber-600 hover:bg-amber-50 shadow-sm">
                        <span class="iconify" data-icon="mdi-cog"></span>
                        Cài đặt Wiki
                    </a>
                    <a href="{{ route('admin.wiki.questions') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-amber-200 bg-white py-2 px-4 text-sm font-medium text-amber-600 hover:bg-amber-50 shadow-sm">
                        <span class="iconify" data-icon="mdi-format-list-text"></span>
                        Quản lý câu hỏi
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

        <!-- Danh sách câu hỏi đang chờ -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 flex justify-between items-center">
                <div class="flex items-center">
                    <span class="iconify text-amber-500 mr-2" data-icon="mdi-clock-outline"></span>
                    <h2 class="text-lg font-semibold text-gray-800">Câu hỏi đang chờ duyệt</h2>
                </div>
                <div class="bg-amber-100 text-amber-800 font-medium py-1 px-3 rounded-full text-sm flex items-center">
                    <span class="iconify mr-1" data-icon="mdi-clock-alert"></span>
                    {{ $pendingQuestions->total() }} câu hỏi chờ duyệt
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($pendingQuestions as $question)
                    <div class="p-5 hover:bg-gray-50 transition-colors">
                        <div class="flex flex-col space-y-3">
                            <!-- Tiêu đề và danh mục -->
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-medium text-gray-900">{{ $question->title }}</h3>
                                <span class="ml-3 inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                {{ $question->category->name }}
                            </span>
                            </div>

                            <!-- Nội dung ngắn gọn -->
                            <div class="text-sm text-gray-600 question-content">
                                <div class="question-preview">
                                    {!! Str::limit(strip_tags($question->content), 200) !!}
                                    @if(strlen(strip_tags($question->content)) > 200)
                                        <button type="button" class="text-amber-600 hover:text-amber-800 text-xs font-medium ml-1 focus:outline-none focus:underline"
                                                onclick="toggleContent('question-{{ $question->id }}')">
                                            Xem thêm
                                        </button>
                                    @endif
                                </div>

                                @if(strlen(strip_tags($question->content)) > 200)
                                    <div id="question-{{ $question->id }}" class="question-full-content hidden mt-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                        <div class="prose prose-sm max-w-none">
                                            {!! $question->content !!}
                                        </div>
                                        <button type="button" class="text-amber-600 hover:text-amber-800 text-xs font-medium mt-2 focus:outline-none focus:underline"
                                                onclick="toggleContent('question-{{ $question->id }}')">
                                            Thu gọn
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <!-- Thông tin người gửi -->
                            <div class="flex flex-wrap items-center text-xs text-gray-500 gap-3">
                                <div class="flex items-center">
                                    <span class="iconify text-gray-400 mr-1" data-icon="mdi-account"></span>
                                    <span>{{ $question->user->name }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="iconify text-gray-400 mr-1" data-icon="mdi-calendar"></span>
                                    <span>{{ $question->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($question->bookGroup)
                                    <div class="flex items-center">
                                        <span class="iconify text-gray-400 mr-1" data-icon="mdi-book-multiple"></span>
                                        <span>{{ $question->bookGroup->name }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Nút thao tác -->
                            <div class="flex flex-wrap gap-3 pt-2">
                                <form action="{{ route('admin.wiki.questions.approve', $question->id) }}" method="POST" class="approve-form">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-600 py-2 px-3 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 shadow-sm transition-colors">
                                        <span class="iconify" data-icon="mdi-check-circle"></span>
                                        Phê duyệt
                                    </button>
                                </form>

                                <form action="{{ route('admin.wiki.questions.reject', $question->id) }}" method="POST" class="reject-form">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-red-100 py-2 px-3 text-sm font-medium text-red-700 hover:bg-red-200 focus:outline-none focus:ring-4 focus:ring-red-100 shadow-sm transition-colors">
                                        <span class="iconify" data-icon="mdi-close-circle"></span>
                                        Từ chối
                                    </button>
                                </form>

                                <a href="{{ route('wiki.show', [$question->category->slug, $question->slug]) }}"
                                   target="_blank"
                                   class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white py-2 px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 shadow-sm transition-colors">
                                    <span class="iconify" data-icon="mdi-eye"></span>
                                    Xem trước
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="flex flex-col items-center justify-center text-gray-500">
                            <div class="mb-4 p-3 rounded-full bg-green-50 text-green-500">
                                <span class="iconify text-5xl" data-icon="mdi-check-circle"></span>
                            </div>
                            <h4 class="text-lg font-medium text-gray-600 mb-1">Không có câu hỏi nào đang chờ duyệt</h4>
                            <p class="text-gray-500">Tất cả câu hỏi đã được kiểm duyệt</p>
                            <a href="{{ route('admin.wiki.questions') }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-md bg-amber-50 px-3 py-2 text-sm font-medium text-amber-600 hover:bg-amber-100">
                                <span class="iconify" data-icon="mdi-format-list-text"></span>
                                Xem tất cả câu hỏi
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Phân trang -->
            @if($pendingQuestions->hasPages())
                <div class="px-5 py-4 border-t border-gray-200 bg-white">
                    {{ $pendingQuestions->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function toggleContent(id) {
            const content = document.getElementById(id);
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                // Hiệu ứng hiển thị với animation
                content.style.opacity = '0';
                setTimeout(() => {
                    content.style.opacity = '1';
                    content.style.transition = 'opacity 0.3s ease-in-out';
                }, 10);
            } else {
                // Hiệu ứng ẩn với animation
                content.style.opacity = '0';
                content.style.transition = 'opacity 0.2s ease-in-out';
                setTimeout(() => {
                    content.classList.add('hidden');
                }, 200);
            }
        }

        $(document).ready(function() {
            // Xác nhận trước khi phê duyệt câu hỏi
            $('.approve-form').submit(function(e) {
                if (!confirm('Bạn có chắc chắn muốn phê duyệt câu hỏi này?')) {
                    e.preventDefault();
                }
            });

            // Xác nhận trước khi từ chối câu hỏi
            $('.reject-form').submit(function(e) {
                if (!confirm('Bạn có chắc chắn muốn từ chối câu hỏi này? Câu hỏi sẽ bị ẩn.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush
