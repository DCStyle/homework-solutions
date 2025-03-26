@extends('layouts.app')

@section('title', 'Tạo câu hỏi mới - Hỏi đáp')

@section('meta_description', 'Đặt câu hỏi mới để nhận câu trả lời từ hệ thống trí tuệ nhân tạo và cộng đồng.')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Đặt câu hỏi mới</h1>
            <p class="text-gray-600">
                Hãy đặt câu hỏi của bạn để nhận câu trả lời từ hệ thống trí tuệ nhân tạo.
                Tiêu đề câu hỏi sẽ được tạo tự động dựa trên nội dung bạn cung cấp.
            </p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Vui lòng kiểm tra lại thông tin:</p>
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('wiki.questions.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="content" class="block text-gray-700 font-medium mb-2">Nội dung chi tiết <span class="text-red-500">*</span></label>
                    <x-form.editor :name="'content'" value="{{ old('content') }}" />
                    <input type="hidden" name="uploaded_image_ids" id="uploaded_image_ids" value="[]">
                    @error('content')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-2">
                        Mô tả chi tiết câu hỏi của bạn. Bạn có thể thêm hình ảnh, định dạng và các ví dụ minh họa.
                        Hệ thống sẽ tự động tạo tiêu đề phù hợp dựa trên nội dung bạn cung cấp.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="category_id" class="block text-gray-700 font-medium mb-2">Danh mục <span class="text-red-500">*</span></label>
                        <select
                            name="category_id"
                            id="category_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('category_id') border-red-500 @enderror"
                            required
                        >
                            <option value="">-- Chọn danh mục --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="book_group_id" class="block text-gray-700 font-medium mb-2">Bộ sách (tùy chọn)</label>
                        <select
                            name="book_group_id"
                            id="book_group_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                            <option value="">-- Không chọn bộ sách --</option>
                            @foreach ($bookGroups as $bookGroup)
                                <option value="{{ $bookGroup->id }}" {{ old('book_group_id') == $bookGroup->id ? 'selected' : '' }}>
                                    {{ $bookGroup->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- AI Title Generation Notice -->
                <div class="mb-6 bg-blue-50 p-4 rounded-md border border-blue-100">
                    <div class="flex items-start">
                        <span class="text-blue-500 mr-3 mt-1">
                            <i class="fas fa-robot"></i>
                        </span>
                        <div>
                            <h3 class="text-blue-800 font-medium">Tiêu đề tự động</h3>
                            <p class="text-blue-700 text-sm mt-1">
                                Hệ thống sẽ tự động tạo tiêu đề SEO-friendly cho câu hỏi của bạn dựa trên nội dung bạn cung cấp.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6 mt-6">
                    <div class="flex justify-between items-center">
                        <a href="{{ route('wiki.index') }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-arrow-left mr-1"></i> Quay lại
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Gửi câu hỏi <i class="fas fa-paper-plane ml-1"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Additional question-specific JavaScript can be added here
    </script>
@endpush
