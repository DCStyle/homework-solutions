@extends('admin_layouts.admin')

@section('title', isset($post) ? 'Sửa Bài Viết' : 'Tạo Bài Viết')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">
                        {{ isset($post) ? 'Sửa Bài Viết' : 'Tạo Bài Viết' }}
                    </h2>
                    <p class="mt-1 text-white/90">
                        {{ isset($post) ? 'Chỉnh sửa nội dung bài viết' : 'Tạo bài viết mới cho chương ' . $chapter->name }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if(isset($post))
                        <a href="{{ route('posts.show', $post->slug) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-white py-2.5 px-4 text-center font-medium text-primary hover:bg-gray-100 transition-all duration-200 shadow-sm"
                           target="_blank">
                            <span class="iconify" data-icon="mdi-eye"></span>
                            Xem Bài Viết
                        </a>
                        <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 py-2.5 px-4 text-center font-medium text-white hover:bg-red-700 transition-all duration-200 shadow-sm"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                <span class="iconify" data-icon="mdi-trash-can-outline"></span>
                                Xóa Bài Viết
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.bookChapters.posts', $chapter->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/20 py-2.5 px-4 text-center font-medium text-white hover:bg-white/30 transition-all duration-200">
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
                    @include('layouts.badge-primary', ['content' => "<a href='" . route('admin.categories.edit', $chapter->book->group->category->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank' class='hover:underline'>" . $chapter->book->group->category->name  . "</a>"])
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
                    @include('layouts.badge-secondary', ['content' => "<a href='" . route('admin.bookGroups.edit', $chapter->book->group->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank' class='hover:underline'>" . $chapter->book->group->name  . "</a>"])
                </div>
            </div>

            <span class="text-gray-400">
            <span class="iconify" data-icon="mdi-chevron-right"></span>
        </span>

            <div class="flex items-center">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600 mr-2">
                <span class="iconify" data-icon="mdi-book-outline"></span>
            </span>
                <div>
                    @include('layouts.badge-green', ['content' => "<a href='" . route('admin.books.edit', $chapter->book->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank' class='hover:underline'>" . $chapter->book->name  . "</a>"])
                </div>
            </div>

            <span class="text-gray-400">
            <span class="iconify" data-icon="mdi-chevron-right"></span>
        </span>

            <div class="flex items-center">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 mr-2">
                <span class="iconify" data-icon="mdi-bookmark-outline"></span>
            </span>
                <a href="{{ route('admin.bookChapters.edit', $chapter->id) }}"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top"
                   title="Chỉnh sửa"
                   target="_blank"
                   class="font-medium text-gray-700 hover:text-primary hover:underline transition-colors">
                    {{ $chapter->name }}
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
                  action="{{ isset($post) ? route('admin.posts.update', $post->id) : route('admin.bookChapters.storePost', $chapter->id) }}">
                @csrf
                @if(isset($post))
                    @method('PUT')
                @endif

                <div class="p-6 space-y-6">
                    <!-- Post Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Post Title -->
                        <div class="form-group md:col-span-2">
                            <label for="title" class="mb-2.5 block font-medium text-black">
                                Tiêu Đề <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-format-title"></span>
                            </span>
                                <input type="text" name="title" id="title"
                                       value="{{ old('title', $post->title ?? '') }}"
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

                        <!-- Post Slug -->
                        <div class="form-group">
                            <label for="slug" class="mb-2.5 block font-medium text-black">
                                Đường Dẫn <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-link-variant"></span>
                            </span>
                                <input type="text" name="slug" id="slug"
                                       value="{{ old('slug', $post->slug ?? '') }}"
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
                                Đường dẫn sẽ xuất hiện trên URL: example.com/posts/<span class="font-mono text-primary">duong-dan-bai-viet</span>
                            </p>
                        </div>

                        <!-- Meta Title -->
                        <div class="form-group">
                            <label for="meta_title" class="mb-2.5 block font-medium text-black">
                                Tiêu Đề Meta (SEO)
                            </label>
                            <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-search-web"></span>
                            </span>
                                <input type="text" name="meta_title" id="meta_title"
                                       value="{{ old('meta_title', $post->meta_title ?? '') }}"
                                       class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                       placeholder="Tiêu đề hiển thị trên kết quả tìm kiếm">
                            </div>
                            @error('meta_title')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Meta Description Field -->
                    <div class="form-group">
                        <label for="meta_description" class="mb-2.5 block font-medium text-black flex items-center">
                            Mô Tả Meta (SEO)
                            <span class="ml-2 text-xs text-gray-500 font-normal">Hiển thị trên kết quả tìm kiếm</span>
                        </label>
                        <x-form.editor :name="'meta_description'" value="{{ old('meta_description', $post->meta_description ?? '') }}" />
                        @error('meta_description')
                        <p class="mt-1 text-sm text-red-600 flex items-center">
                            <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Content Field -->
                    <div class="form-group">
                        <label for="message" class="mb-2.5 block font-medium text-black">
                            Nội Dung <span class="text-red-500">*</span>
                        </label>
                        <x-form.editor :name="'message'" value="{{ old('message', $post->content ?? '') }}" />
                        <input type="hidden" name="uploaded_image_ids" id="uploaded_image_ids" value="{{ isset($post) ? json_encode($post->images->pluck('id')) : '[]' }}">
                        @error('message')
                        <p class="mt-1 text-sm text-red-600 flex items-center">
                            <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Attachment Field -->
                    <div class="form-group">
                        <label class="mb-2.5 block font-medium text-black flex items-center">
                            Tệp Đính Kèm
                            <span class="ml-2 text-xs text-gray-500 font-normal">PDF, ZIP, RAR tối đa 10MB</span>
                        </label>

                        <!-- Existing Attachments Section -->
                        @if(isset($post) && $post->attachments->count() > 0)
                            <div class="mb-4">
                                <h4 class="text-base font-medium text-gray-900 mb-3 flex items-center">
                                    <span class="iconify mr-2 text-primary" data-icon="mdi-attachment"></span>
                                    Tệp Đính Kèm Hiện Tại
                                </h4>
                                <ul class="divide-y divide-gray-200 border rounded-lg overflow-hidden">
                                    @foreach($post->attachments as $attachment)
                                        <li class="p-4 flex items-center justify-between bg-white hover:bg-gray-50 transition-all duration-200" id="existing-file-{{ $attachment->id }}">
                                            <div class="flex items-center flex-1">
                                                <!-- File Icon -->
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                                    <span class="iconify" data-icon="mdi-file-outline"></span>
                                                </div>

                                                <!-- File Details -->
                                                <div class="ml-4 flex-1">
                                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                                        <p class="text-base font-medium text-gray-900">{{ $attachment->original_filename }}</p>
                                                        <p class="text-sm text-gray-500">
                                                            {{ number_format($attachment->file_size / 1024, 2) }} KB
                                                        </p>
                                                    </div>
                                                    <p class="text-xs text-gray-500 flex items-center">
                                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 mr-2">
                                                        {{ strtoupper($attachment->extension) }}
                                                    </span>
                                                        <span class="inline-flex items-center text-gray-500">
                                                        <span class="iconify mr-1" data-icon="mdi-calendar"></span>
                                                        {{ $attachment->created_at->format('d/m/Y H:i') }}
                                                    </span>
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="ml-4 flex items-center space-x-3">
                                                <a href="{{ route('attachments.download', $attachment->id) }}"
                                                   class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                                    <span class="iconify mr-1" data-icon="mdi-download"></span>
                                                    Tải Xuống
                                                </a>
                                                <button type="button"
                                                        onclick="deleteExistingFile({{ $attachment->id }})"
                                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 transition-colors">
                                                    <span class="iconify mr-1" data-icon="mdi-trash-can-outline"></span>
                                                    Xóa
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- File Upload Area -->
                        <div class="mt-4 flex justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-8 transition-all duration-300 hover:border-primary/70 hover:bg-gray-50">
                            <div class="text-center">
                                <!-- Upload Icon -->
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 mb-4">
                                    <span class="iconify text-primary text-2xl" data-icon="mdi-cloud-upload-outline"></span>
                                </div>

                                <h4 class="mb-2 text-base font-medium text-gray-700">Tải Tệp Lên</h4>
                                <p class="mb-4 text-sm text-gray-500">Kéo và thả tệp vào đây hoặc nhấp để chọn tệp</p>

                                <div class="flex justify-center">
                                    <label for="attachments" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary py-2.5 px-4 text-center font-medium text-white hover:bg-primary/90 transition-all duration-200 cursor-pointer">
                                        <span class="iconify" data-icon="mdi-file-plus-outline"></span>
                                        Chọn Tệp
                                        <input
                                            id="attachments"
                                            name="attachments[]"
                                            type="file"
                                            multiple
                                            class="sr-only"
                                            accept=".pdf,.zip,.rar"
                                        >
                                    </label>
                                </div>
                                <p class="mt-3 text-xs text-gray-500">PDF, ZIP, RAR tối đa 10MB</p>
                            </div>
                        </div>

                        <!-- Selected Files Preview -->
                        <div id="selected-files" class="mt-4 space-y-2 hidden">
                            <h4 class="text-base font-medium text-gray-900 flex items-center">
                                <span class="iconify mr-2 text-primary" data-icon="mdi-file-multiple-outline"></span>
                                Tệp Đã Chọn
                            </h4>
                            <ul id="file-list" class="divide-y divide-gray-200 border rounded-lg overflow-hidden"></ul>
                        </div>

                        <!-- Hidden input to store uploaded file IDs -->
                        <input type="hidden" name="uploaded_attachment_ids" id="uploaded_attachment_ids"
                               value="{{ isset($post) ? json_encode($post->attachments->pluck('id')) : '[]' }}">
                    </div>

                    <!-- Source URL field (Uneditable) -->
                    @if(isset($post) && $post->source_url)
                        <div class="form-group">
                            <label for="source_url" class="mb-2.5 block font-medium text-black">URL Nguồn</label>

                            <div class="flex flex-col sm:flex-row items-center gap-4">
                                <div class="relative flex-1 w-full">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                    <span class="iconify" data-icon="mdi-earth"></span>
                                </span>
                                    <input type="text" id="source_url" name="source_url" value="{{ $post->source_url }}"
                                           class="w-full rounded-lg border border-stroke bg-gray-100 py-3 pl-10 pr-4 outline-none text-gray-700"
                                           readonly>
                                </div>

                                <a href="{{ $post->source_url }}" target="_blank" rel="noopener noreferrer nofollow"
                                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 py-2.5 px-4 text-center font-medium text-white hover:bg-indigo-700 transition-all duration-200">
                                    <span class="iconify" data-icon="mdi-open-in-new"></span>
                                    Mở URL
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.bookChapters.posts', $chapter->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white py-3 px-6 text-center font-medium text-gray-700 hover:bg-gray-100 transition-all duration-200 shadow-sm">
                            <span class="iconify" data-icon="mdi-close"></span>
                            Hủy Bỏ
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary py-3 px-6 text-center font-medium text-white hover:bg-primary/90 transition-all duration-200 shadow-sm">
                            <span class="iconify" data-icon="mdi-{{ isset($post) ? 'content-save' : 'plus' }}"></span>
                            {{ isset($post) ? 'Cập Nhật' : 'Tạo Bài Viết' }}
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
        function deleteExistingFile(attachmentId) {
            if (!confirm('Bạn có chắc chắn muốn xóa tệp này?')) {
                return;
            }

            fetch(`/api/attachments/${attachmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.ok) {
                        const element = document.getElementById(`existing-file-${attachmentId}`);
                        if (element) {
                            element.style.opacity = '0';
                            element.style.transform = 'translateY(-10px)';
                            element.style.transition = 'all 0.3s ease-out';

                            setTimeout(() => {
                                element.remove();

                                // Update the hidden input
                                const uploadedIdsInput = document.getElementById('uploaded_attachment_ids');
                                let currentIds = JSON.parse(uploadedIdsInput.value);
                                currentIds = currentIds.filter(id => id !== attachmentId);
                                uploadedIdsInput.value = JSON.stringify(currentIds);

                                // Check if there are any remaining attachments
                                const attachmentsList = document.querySelector('.divide-y.divide-gray-200');
                                if (attachmentsList && attachmentsList.children.length === 0) {
                                    attachmentsList.closest('.mb-4').remove();
                                }
                            }, 300);
                        }
                    } else {
                        alert('Không thể xóa tệp. Vui lòng thử lại.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi khi xóa tệp.');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('attachments');
            const fileList = document.getElementById('file-list');
            const selectedFiles = document.getElementById('selected-files');
            const uploadedIdsInput = document.getElementById('uploaded_attachment_ids');
            const dropZone = document.querySelector('.border-dashed');
            let uploadedIds = [];

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

            function updateFileList(file, progress = 0, uploadId = null) {
                const fileId = `file-${Date.now()}-${file.name}`;

                // Check if a file with the same name already exists
                const existingFile = Array.from(fileList.children).find(li => {
                    return li.querySelector('.text-gray-900').textContent === file.name;
                });

                // If we found an existing file with the same name
                if (existingFile) {
                    // Just update its progress bar if that's what changed
                    if (progress > 0) {
                        const progressBar = existingFile.querySelector('.bg-indigo-600');
                        if (progressBar) {
                            progressBar.style.width = `${progress}%`;
                        }

                        // Update the upload ID if provided
                        if (uploadId) {
                            const removeButton = existingFile.querySelector('button');
                            if (removeButton) {
                                removeButton.onclick = () => removeFile(existingFile.id, uploadId);
                            }
                        }
                    }
                    return;
                }

                // If no existing file was found, create a new list item
                const li = document.createElement('li');
                li.id = fileId;
                li.className = 'py-4 px-4 flex items-center justify-between opacity-0 transform translate-y-2 bg-white hover:bg-gray-50 transition-all duration-200';

                // Format file size
                const size = file.size < 1024000
                    ? `${(file.size / 1024).toFixed(2)} KB`
                    : `${(file.size / 1024 / 1024).toFixed(2)} MB`;

                li.innerHTML = `
                <div class="flex items-center flex-1">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                        <span class="iconify" data-icon="mdi-file-outline"></span>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-base font-medium text-gray-900">${file.name}</p>
                            <p class="ml-2 text-sm text-gray-500">${size}</p>
                        </div>
                        <div class="mt-1 w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: ${progress}%"></div>
                        </div>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button type="button" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 transition-colors" onclick="removeFile('${fileId}', ${uploadId})">
                        <span class="iconify mr-1" data-icon="mdi-trash-can-outline"></span>
                        Xóa
                    </button>
                </div>
            `;

                fileList.appendChild(li);

                // Animate the entry
                requestAnimationFrame(() => {
                    li.style.transition = 'all 0.3s ease-out';
                    li.style.opacity = '1';
                    li.style.transform = 'translateY(0)';
                    li.classList.remove('opacity-0');
                });
            }

            function uploadFile(file) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                return fetch('/api/attachments/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            }

            async function handleFiles(files) {
                selectedFiles.classList.remove('hidden');

                // Get current file names in the list
                const existingFileNames = new Set(
                    Array.from(fileList.children).map(li =>
                        li.querySelector('.text-gray-900').textContent
                    )
                );

                for (const file of files) {
                    // Skip if file already exists
                    if (existingFileNames.has(file.name)) {
                        continue;
                    }

                    // Add file to preview with 0% progress
                    updateFileList(file, 0);

                    try {
                        const response = await uploadFile(file);
                        const data = await response.json();

                        if (response.ok) {
                            // Update progress to 100% and store upload ID
                            updateFileList(file, 100, data.id);
                            uploadedIds.push(data.id);
                            uploadedIdsInput.value = JSON.stringify(uploadedIds);
                        } else {
                            throw new Error(data.message || 'Upload failed');
                        }
                    } catch (error) {
                        console.error('Upload error:', error);
                        // Show error state
                        const fileElement = document.getElementById(`file-${Date.now()}-${file.name}`);
                        if (fileElement) {
                            fileElement.querySelector('.bg-indigo-600').classList.add('bg-red-600');
                        }
                    }
                }
            }

            // Handle remove file
            window.removeFile = function(fileId, uploadId) {
                const element = document.getElementById(fileId);
                if (element) {
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        element.remove();
                        if (fileList.children.length === 0) {
                            selectedFiles.classList.add('hidden');
                        }
                    }, 300);

                    if (uploadId) {
                        uploadedIds = uploadedIds.filter(id => id !== uploadId);
                        uploadedIdsInput.value = JSON.stringify(uploadedIds);

                        // Optional: Send delete request to server
                        fetch(`/api/attachments/${uploadId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        }).catch(console.error);
                    }
                }
            };

            // File input change handler
            fileInput.addEventListener('change', (e) => {
                handleFiles(Array.from(e.target.files));
            });

            // Drag and drop handlers
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                dropZone.classList.add('border-primary', 'bg-primary/5');
            }

            function unhighlight(e) {
                dropZone.classList.remove('border-primary', 'bg-primary/5');
            }

            dropZone.addEventListener('drop', (e) => {
                const files = Array.from(e.dataTransfer.files);
                handleFiles(files);
            });

            // Try to parse the existing attachment IDs
            try {
                // Only try to parse if the input exists and has a value
                if (uploadedIdsInput && uploadedIdsInput.value) {
                    uploadedIds = JSON.parse(uploadedIdsInput.value) || [];
                }
            } catch (e) {
                console.error('Failed to parse existing attachment IDs:', e);
                uploadedIds = [];
            }
        });
    </script>
@endpush
