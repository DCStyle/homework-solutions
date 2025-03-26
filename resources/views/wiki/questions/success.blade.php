@extends('layouts.app')

@section('title', 'Câu hỏi đã được gửi thành công')
@section('description', 'Câu hỏi của bạn đã được gửi và đang được xử lý bởi hệ thống trí tuệ nhân tạo')

@section('content')
    <div class="py-12 bg-gray-50">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300">
                <!-- Success Header with Gradient Background -->
                <div class="p-8 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-100 text-center">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-green-100 text-green-500 mb-4">
                        <span class="iconify text-3xl" data-icon="mdi-check-circle" data-width="40"></span>
                    </div>

                    <h2 class="text-2xl font-semibold text-gray-900">Câu hỏi của bạn đã được gửi thành công!</h2>

                    <div class="mt-3 max-w-lg mx-auto">
                        <p class="text-gray-600">
                            Câu hỏi của bạn đang được xử lý bởi hệ thống AI của chúng tôi. Bạn sẽ nhận được câu trả lời trong thời gian ngắn.
                        </p>
                    </div>
                </div>

                <!-- Question Information Card -->
                <div class="p-6">
                    <div class="bg-gradient-to-r from-gray-50 to-indigo-50 p-5 rounded-xl border border-gray-200">
                        <div class="flex items-center mb-4">
                            <span class="iconify text-indigo-500 mr-3" data-icon="mdi-information-outline" data-width="24"></span>
                            <h3 class="text-lg font-medium text-gray-800">Thông tin câu hỏi</h3>
                        </div>

                        <div class="space-y-3 pl-2">
                            <div class="flex items-start">
                                <span class="iconify text-gray-400 mr-2 mt-0.5" data-icon="mdi-format-title" data-width="18"></span>
                                <div>
                                    <p class="text-sm text-gray-500">Tiêu đề</p>
                                    <p class="font-medium text-gray-800">{{ $question->title }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <span class="iconify text-gray-400 mr-2 mt-0.5" data-icon="mdi-folder-outline" data-width="18"></span>
                                <div>
                                    <p class="text-sm text-gray-500">Danh mục</p>
                                    <p class="font-medium text-gray-800">{{ $question->category->name }}</p>
                                </div>
                            </div>

                            @if($question->bookGroup)
                                <div class="flex items-start">
                                    <span class="iconify text-gray-400 mr-2 mt-0.5" data-icon="mdi-book-multiple-outline" data-width="18"></span>
                                    <div>
                                        <p class="text-sm text-gray-500">Bộ sách</p>
                                        <p class="font-medium text-gray-800">{{ $question->bookGroup->name }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-start">
                                <span class="iconify text-gray-400 mr-2 mt-0.5" data-icon="mdi-clock-outline" data-width="18"></span>
                                <div>
                                    <p class="text-sm text-gray-500">Thời gian</p>
                                    <p class="font-medium text-gray-800">{{ $question->created_at->format('H:i, d/m/Y') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <span class="iconify text-gray-400 mr-2 mt-0.5" data-icon="mdi-tag-outline" data-width="18"></span>
                                <div>
                                    <p class="text-sm text-gray-500">Trạng thái</p>
                                    @if($question->status === 'pending')
                                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <span class="iconify mr-1" data-icon="mdi-timer-sand" data-width="14"></span>
                                            Đang chờ duyệt
                                        </div>
                                    @elseif($question->status === 'published')
                                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="iconify mr-1" data-icon="mdi-check-circle" data-width="14"></span>
                                            Đã đăng
                                        </div>
                                    @else
                                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <span class="iconify mr-1" data-icon="mdi-information-outline" data-width="14"></span>
                                            {{ $question->status }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps Information -->
                    <div class="mt-6 bg-blue-50 rounded-xl p-4 border border-blue-100">
                        <div class="flex items-start">
                            <span class="iconify text-blue-500 mr-3 flex-shrink-0" data-icon="mdi-lightbulb-outline" data-width="24"></span>
                            <div>
                                <h4 class="font-medium text-gray-800 mb-1">Bước tiếp theo</h4>
                                <p class="text-sm text-gray-600">
                                    Câu hỏi của bạn sẽ được trả lời tự động bởi AI. Sau khi được duyệt, câu hỏi sẽ hiển thị công khai trên hệ thống và người dùng khác có thể xem và thảo luận.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 flex flex-col sm:flex-row sm:justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="{{ route('wiki.show', [$question->category->slug, $question->slug]) }}"
                           class="inline-flex items-center justify-center px-5 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <span class="iconify mr-2" data-icon="mdi-eye-outline" data-width="20"></span>
                            Xem câu hỏi
                        </a>
                        <a href="{{ route('wiki.index') }}"
                           class="inline-flex items-center justify-center px-5 py-3 border border-gray-300 rounded-lg shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <span class="iconify mr-2" data-icon="mdi-home-outline" data-width="20"></span>
                            Quay lại trang chủ
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Help Section -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Cần trợ giúp thêm?
                    <a href="#" class="font-medium text-indigo-600 hover:text-indigo-800 inline-flex items-center">
                        Liên hệ hỗ trợ
                        <span class="iconify ml-1" data-icon="mdi-arrow-right" data-width="16"></span>
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
