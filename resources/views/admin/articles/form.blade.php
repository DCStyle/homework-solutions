@extends('admin_layouts.admin')

@section('title', isset($article) ? 'Sửa Bài Viết' : 'Thêm Bài Viết')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">
                        {{ isset($article) ? 'Sửa Bài Viết' : 'Thêm Bài Viết' }}
                    </h2>
                    <p class="mt-1 text-white/90">
                        {{ isset($article) ? 'Chỉnh sửa thông tin và nội dung bài viết' : 'Tạo bài viết tin tức mới cho website' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if(isset($article))
                        <a href="{{ route('articles.show', $article->slug) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-white py-2.5 px-4 text-center font-medium text-primary hover:bg-gray-100 transition-all duration-200 shadow-sm"
                           target="_blank">
                            <span class="iconify" data-icon="mdi-eye"></span>
                            Xem Bài Viết
                        </a>
                    @endif
                    <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/20 py-2.5 px-4 text-center font-medium text-white hover:bg-white/30 transition-all duration-200">
                        <span class="iconify" data-icon="mdi-arrow-left"></span>
                        Quay Lại Danh Sách
                    </a>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 h-64 w-64 rounded-full bg-white/5"></div>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-lg bg-red-100 p-4 text-red-700 shadow-md animate-fadeIn">
                <div class="flex items-center mb-2">
                    <span class="iconify mr-2 text-xl" data-icon="mdi-alert-circle"></span>
                    <span class="font-medium">Vui lòng kiểm tra lại thông tin nhập liệu:</span>
                </div>
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="rounded-xl border border-stroke bg-white shadow-md">
            <form method="POST"
                  action="{{ isset($article) ? route('admin.articles.update', $article->id) : route('admin.articles.store') }}">
                @csrf
                @if(isset($article))
                    @method('PUT')
                @endif

                <div class="p-6 space-y-6">
                    <!-- Article Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Article Title -->
                        <div class="form-group md:col-span-2">
                            <label for="title" class="mb-2.5 block font-medium text-black">
                                Tiêu Đề <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-format-title"></span>
                            </span>
                                <input type="text" name="title" id="title"
                                       value="{{ old('name', $article->title ?? '') }}"
                                       class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                       placeholder="Nhập tiêu đề bài viết" required>
                            </div>
                            @error('title')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Slug Field -->
                        <div class="form-group">
                            <label for="slug" class="mb-2.5 block font-medium text-black">
                                Đường Dẫn <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-link-variant"></span>
                            </span>
                                <input type="text" name="slug" id="slug"
                                       value="{{ old('slug', $article->slug ?? '') }}"
                                       class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                       placeholder="duong-dan-bai-viet" required>
                            </div>
                            @error('slug')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                {{ $message }}
                            </p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                Đường dẫn sẽ xuất hiện trên URL: example.com/articles/<span class="font-mono text-primary">duong-dan</span>
                            </p>
                        </div>

                        <!-- Category Field -->
                        <div class="form-group">
                            <label for="article_category_id" class="mb-2.5 block font-medium text-black">
                                Danh Mục <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-folder-outline"></span>
                            </span>
                                <select name="article_category_id" id="article_category_id"
                                        class="w-full appearance-none rounded-lg border border-stroke bg-white py-3 pl-10 pr-10 outline-none focus:border-primary focus-visible:shadow-none"
                                        data-plugin-select2>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('article_category_id', $article->article_category_id ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none">
                                <span class="iconify" data-icon="mdi-chevron-down"></span>
                            </span>
                            </div>
                            @error('article_category_id')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tags Field -->
                    <div class="form-group">
                        <label for="tags" class="mb-2.5 block font-medium text-black flex items-center">
                            Tags
                            <span class="ml-2 text-xs text-gray-500 font-normal">Thêm nhiều tags cho bài viết</span>
                        </label>
                        <div class="relative">
                        <span class="absolute left-4 top-4 text-gray-500">
                            <span class="iconify" data-icon="mdi-tag-multiple-outline"></span>
                        </span>
                            <select name="tags[]" id="article-tags" multiple
                                    class="w-full rounded-lg border border-stroke bg-white py-2 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none">
                                @if(isset($article))
                                    @foreach($article->tags as $tag)
                                        <option value="{{ $tag->id }}" selected>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @error('tags')
                        <p class="mt-1 text-sm text-red-600 flex items-center">
                            <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                            {{ $message }}
                        </p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 flex items-center">
                            <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                            Gõ để tìm kiếm hoặc tạo tags mới, phân cách nhiều tags bằng dấu phẩy
                        </p>
                    </div>

                    <!-- Content Field -->
                    <div class="form-group">
                        <label for="content" class="mb-2.5 block font-medium text-black">
                            Nội Dung <span class="text-red-500">*</span>
                        </label>
                        <x-form.editor :name="'content'" value="{{ old('content', $article->content ?? '') }}" />
                        <input type="hidden" name="uploaded_image_ids" id="uploaded_image_ids"
                               value="{{ isset($article) ? json_encode($article->images->pluck('id')) : '[]' }}">
                        @error('content')
                        <p class="mt-1 text-sm text-red-600 flex items-center">
                            <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white py-3 px-6 text-center font-medium text-gray-700 hover:bg-gray-100 transition-all duration-200 shadow-sm">
                            <span class="iconify" data-icon="mdi-close"></span>
                            Hủy Bỏ
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary py-3 px-6 text-center font-medium text-white hover:bg-primary/90 transition-all duration-200 shadow-sm">
                            <span class="iconify" data-icon="mdi-{{ isset($article) ? 'content-save' : 'plus' }}"></span>
                            {{ isset($article) ? 'Cập Nhật' : 'Thêm Bài Viết' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .animate-fadeIn {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group:hover label {
            color: rgb(99 102 241);
            transition: all 0.2s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: rgb(99 102 241);
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.2);
        }

        /* Style the Select2 dropdown */
        .select2-container--default .select2-selection--multiple {
            border-color: #e5e7eb;
            border-radius: 0.5rem;
            min-height: 42px;
            padding-left: 2.5rem;
        }

        .select2-container--default .select2-selection--multiple:focus {
            border-color: rgb(99 102 241);
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.2);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #EEF2FF;
            border: 1px solid #C7D2FE;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
            margin-right: 0.5rem;
            margin-top: 0.25rem;
            font-size: 0.875rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #6366F1;
            margin-right: 0.25rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-generate slug from title
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');

            if (titleInput && slugInput) {
                titleInput.addEventListener('input', function() {
                    // Only auto-generate if slug field is empty or hasn't been manually edited
                    if (!slugInput.dataset.manuallyEdited) {
                        const slugValue = titleInput.value
                            .toLowerCase()
                            .replace(/đ/g, 'd')
                            .replace(/[^\w\s-]/g, '')
                            .replace(/[\s_-]+/g, '-')
                            .replace(/^-+|-+$/g, '');

                        slugInput.value = slugValue;
                    }
                });

                // Mark slug as manually edited when user types in it
                slugInput.addEventListener('input', function() {
                    slugInput.dataset.manuallyEdited = 'true';
                });
            }

            // Initialize Select2 for tags
            $('#article-tags').select2({
                placeholder: 'Chọn hoặc tạo tags mới...',
                tags: true, // Allows creating new tags
                tokenSeparators: [',', ' '], // Allows creating multiple tags by typing these separators
                ajax: {
                    url: '{{ route('admin.article-tags.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: {
                                more: false
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1,
                createTag: function(params) {
                    const term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
            });
        });
    </script>
@endpush
