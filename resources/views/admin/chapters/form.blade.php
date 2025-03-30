@extends('admin_layouts.admin')

@section('title', isset($chapter) ? 'Cập Nhật Chương Sách' : 'Thêm Chương Mới')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">
                        {{ isset($chapter) ? 'Cập Nhật Chương Sách' : 'Thêm Chương Mới' }}
                    </h2>
                    <p class="mt-1 text-white/90">
                        {{ isset($chapter) ? 'Chỉnh sửa thông tin chi tiết chương' : 'Tạo chương mới cho sách ' . $book->name }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if(isset($chapter))
                        <a href="{{ route('bookChapters.show', $chapter->slug) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-white py-2.5 px-4 text-center font-medium text-primary hover:bg-gray-100 transition-all duration-200 shadow-sm"
                           target="_blank">
                            <span class="iconify" data-icon="mdi-eye"></span>
                            Xem Chương
                        </a>
                        <a href="{{ route('admin.bookChapters.posts', $chapter->id) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 py-2.5 px-4 text-center font-medium text-white hover:bg-amber-600 transition-all duration-200 shadow-sm">
                            <span class="iconify" data-icon="mdi-text-box-multiple-outline"></span>
                            Danh Sách Bài Viết
                        </a>
                        <form method="POST" action="{{ route('admin.bookChapters.destroy', $chapter->id) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 py-2.5 px-4 text-center font-medium text-white hover:bg-red-700 transition-all duration-200 shadow-sm"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa chương sách này không?')">
                                <span class="iconify" data-icon="mdi-trash-can-outline"></span>
                                Xóa Chương
                            </button>
                        </form>
                    @endif
                    <a href="{{ isset($chapter) ? route('admin.books.chapters', $chapter->book_id) : route('admin.books.chapters', $book->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/20 py-2.5 px-4 text-center font-medium text-white hover:bg-white/30 transition-all duration-200">
                        <span class="iconify" data-icon="mdi-arrow-left"></span>
                        Quay Lại Danh Sách
                    </a>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 h-64 w-64 rounded-full bg-white/5"></div>
        </div>

        <!-- Breadcrumb Navigation -->
        <div class="flex flex-wrap items-center gap-2 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
            <div class="flex items-center">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mr-2">
                <span class="iconify" data-icon="mdi-folder-outline"></span>
            </span>
                <div>
                    @include('layouts.badge-primary', ['content' => "<a href='" . route('admin.categories.edit', $book->group->category->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank' class='hover:underline'>" . $book->group->category->name  . "</a>"])
                </div>
            </div>

            <span class="text-gray-400">
            <span class="iconify" data-icon="mdi-chevron-right"></span>
        </span>

            <div class="flex items-center">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-500 mr-2">
                <span class="iconify" data-icon="mdi-bookshelf"></span>
            </span>
                <div>
                    @include('layouts.badge-secondary', ['content' => "<a href='" . route('admin.bookGroups.edit', $book->group->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank' class='hover:underline'>" . $book->group->name  . "</a>"])
                </div>
            </div>

            <span class="text-gray-400">
            <span class="iconify" data-icon="mdi-chevron-right"></span>
        </span>

            <div class="flex items-center">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 mr-2">
                <span class="iconify" data-icon="mdi-book-outline"></span>
            </span>
                <a href="{{ route('admin.books.edit', $book->id) }}"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top"
                   title="Chỉnh sửa"
                   target="_blank"
                   class="font-medium text-gray-700 hover:text-primary hover:underline transition-colors">
                    {{ $book->name }}
                </a>
            </div>
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
                  action="{{ isset($chapter) ? route('admin.bookChapters.update', $chapter->id) : route('admin.books.storeChapter', $book->id) }}">
                @csrf
                @if(isset($chapter))
                    @method('PUT')
                @endif

                <div class="p-6 space-y-6">
                    <!-- Chapter Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Chapter Name -->
                        <div class="form-group">
                            <label for="name" class="mb-2.5 block font-medium text-black">
                                Tên Chương <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-bookmark-outline"></span>
                            </span>
                                <input type="text" name="name" id="name"
                                       value="{{ old('name', $chapter->name ?? '') }}"
                                       class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                       placeholder="Nhập tên chương" required>
                            </div>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Chapter Slug -->
                        <div class="form-group">
                            <label for="slug" class="mb-2.5 block font-medium text-black">
                                Đường Dẫn <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-link-variant"></span>
                            </span>
                                <input type="text" name="slug" id="slug"
                                       value="{{ old('slug', $chapter->slug ?? '') }}"
                                       class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                       placeholder="duong-dan-chuong" required>
                            </div>
                            @error('slug')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                {{ $message }}
                            </p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                Đường dẫn sẽ xuất hiện trên URL: example.com/book-chapters/<span class="font-mono text-primary">duong-dan-chuong</span>
                            </p>
                        </div>
                    </div>

                    <!-- Book Selection -->
                    <div class="form-group">
                        <label for="book_id" class="mb-2.5 block font-medium text-black">
                            Sách <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                            <span class="iconify" data-icon="mdi-book-outline"></span>
                        </span>
                            <select name="book_id" id="book_id" data-plugin-select2
                                    class="w-full appearance-none rounded-lg border border-stroke bg-white py-3 pl-10 pr-10 outline-none focus:border-primary focus-visible:shadow-none">
                                    <?php $currentCategory = null ?>

                                @foreach($books as $bookItem)
                                    @if($currentCategory == null || $bookItem->group->category->id !== $currentCategory->id)
                                            <?php $currentCategory = $bookItem->group->category ?>
                                        <optgroup label="{{ $currentCategory->name }}">
                                            @endif

                                            <option value="{{ $bookItem->id }}" {{ $bookItem->id == old('book_id', $chapter->book_id ?? $book->id) ? 'selected' : '' }}>{{ $bookItem->name }}</option>

                                            @if($currentCategory == null || $bookItem->group->category->id !== $currentCategory->id)
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none">
                            <span class="iconify" data-icon="mdi-chevron-down"></span>
                        </span>
                        </div>
                        @error('book_id')
                        <p class="mt-1 text-sm text-red-600 flex items-center">
                            <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Description Field -->
                    <div class="form-group">
                        <label for="description" class="mb-2.5 block font-medium text-black">
                            Mô Tả Chương
                        </label>
                        <x-form.editor :name="'description'" value="{{ old('description', $chapter->description ?? '') }}" />
                        @error('description')
                        <p class="mt-1 text-sm text-red-600 flex items-center">
                            <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                        <a href="{{ isset($chapter) ? route('admin.books.chapters', $chapter->book_id) : route('admin.books.chapters', $book->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white py-3 px-6 text-center font-medium text-gray-700 hover:bg-gray-100 transition-all duration-200 shadow-sm">
                            <span class="iconify" data-icon="mdi-close"></span>
                            Hủy Bỏ
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary py-3 px-6 text-center font-medium text-white hover:bg-primary/90 transition-all duration-200 shadow-sm">
                            <span class="iconify" data-icon="mdi-{{ isset($chapter) ? 'content-save' : 'plus' }}"></span>
                            {{ isset($chapter) ? 'Cập Nhật' : 'Thêm Mới' }}
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
    </style>
@endpush

@push('scripts')
    <script>
        // Auto-generate slug from name
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            if (nameInput && slugInput) {
                nameInput.addEventListener('input', function() {
                    // Only auto-generate if slug field is empty or hasn't been manually edited
                    if (!slugInput.dataset.manuallyEdited) {
                        const slugValue = nameInput.value
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
        });
    </script>
@endpush
