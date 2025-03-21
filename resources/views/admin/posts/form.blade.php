@extends('admin_layouts.admin')

@section('content')
    <div>
        <div class="flex flex-wrap items-center justify-between mb-4">
            <h2 class="text-3xl font-bold mb-2">
                {{ isset($post) ? 'Sửa bài viết' : 'Tạo bài viết' }}
            </h2>

            @if(isset($post))
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <a href="{{ route('posts.show', $post->slug) }}"
                       class="px-4 py-2 rounded bg-primary text-white hover:!bg-blue-600"
                       target="_blank"
                    >
                        Xem bài viết
                    </a>

                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 rounded bg-red-500 text-white hover:bg-red-600"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')"
                        >
                            Xóa bài viết
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <p class="mb-6">
            @include('layouts.badge-primary', ['content' => "<a href='" . route('admin.categories.edit', $chapter->book->group->category->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank'>" . $chapter->book->group->category->name  . "</a>"])
            @include('layouts.badge-secondary', ['content' => "<a href='" . route('admin.bookGroups.edit', $chapter->book->group->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank'>" . $chapter->book->group->name  . "</a>"])
            @include('layouts.badge-green', ['content' => "<a href='" . route('admin.books.edit', $chapter->book->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank'>" . $chapter->book->name  . "</a>"])
            <span class="font-medium">
                <a href="{{ route('admin.bookChapters.edit', $chapter->id) }}"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top"
                   title="Chỉnh sửa"
                   target="_blank"
                   class="hover:underline"
                >
                    {{ $chapter->name }}
                </a>
            </span>
        </p>

        <form class="rounded-sm border bg-white shadow"
              method="POST"
              action="{{ isset($post)
                ? route('admin.posts.update', $post->id)
                : route('admin.bookChapters.storePost', $chapter->id)
              }}"
        >
            @csrf
            @if(isset($post))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'title', 'label' => 'Tiêu đề', 'value' => old('title', $post->title ?? ''), 'required' => true])

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => 'Đường dẫn', 'value' => old('slug', $post->slug ?? '')])

                <!-- Meta Title Field -->
                @include('layouts.form-input', ['name' => 'meta_title', 'label' => 'Tiêu đề meta (SEO)', 'value' => old('meta_title', $post->meta_title ?? '')])

                <!-- Meta Description Field -->
                <div class="mb-4">
                    <label for="meta_description" class="mb-3 block text-sm font-medium text-[#1c2434]">Mô tả meta (SEO)</label>
                    <x-form.editor
                        :name="'meta_description'"
                        value="{{ old('meta_description', $post->meta_description ?? '') }}"
                    />
                </div>

                <!-- Content Field -->
                <div>
                    <label for="message" class="mb-3 block text-sm font-medium text-[#1c2434]">Nội dung</label>
                    <x-form.editor :name="'message'" value="{{ old('message', $post->content ?? '') }}" />

                    <input type="hidden" name="uploaded_image_ids" id="uploaded_image_ids" value="{{ isset($post) ? json_encode($post->images->pluck('id')) : '[]' }}">
                </div>

                <!-- Attachment Field -->
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-[#1c2434]">Tệp đính kèm</label>

                    <!-- Existing Attachments Section -->
                    @if(isset($post) && $post->attachments->count() > 0)
                        <div class="mb-4">
                            <h4 class="text-base font-medium text-gray-900 mb-3">Tệp đính kèm hiện tại</h4>
                            <ul class="divide-y divide-gray-200 border rounded-lg">
                                @foreach($post->attachments as $attachment)
                                    <li class="p-4 flex items-center justify-between" id="existing-file-{{ $attachment->id }}">
                                        <div class="flex items-center flex-1">
                                            <!-- File Icon -->
                                            <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                            </svg>

                                            <!-- File Details -->
                                            <div class="ml-4 flex-1">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-base font-medium text-gray-900">{{ $attachment->original_filename }}</p>
                                                    <p class="ml-2 text-sm text-gray-500">
                                                        {{ number_format($attachment->file_size / 1024, 2) }} KB
                                                    </p>
                                                </div>
                                                <p class="text-sm text-gray-500">
                                                    {{ strtoupper($attachment->extension) }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="ml-4 flex items-center space-x-3">
                                            <a href="{{ route('attachments.download', $attachment->id) }}"
                                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                Tải xuống
                                            </a>
                                            <button type="button"
                                                    onclick="deleteExistingFile({{ $attachment->id }})"
                                                    class="text-red-500 hover:text-red-700 text-sm font-medium">
                                                Xóa
                                            </button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- File Upload Area -->
                    <div class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                        <div class="text-center">
                            <!-- Upload Icon -->
                            <span class="iconify text-gray-300 inline-block mx-auto text-3xl"
                                  data-icon="mdi-tray-arrow-up">
                            </span>

                            <div class="mt-4 flex text-sm leading-6 text-gray-600">
                                <label for="attachments" class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                                    <span class="text-base">Tải tệp lên</span>
                                    <input
                                        id="attachments"
                                        name="attachments[]"
                                        type="file"
                                        multiple
                                        class="sr-only"
                                        accept=".pdf,.zip,.rar"
                                    >
                                </label>
                                <p class="pl-1 text-base">hoặc kéo và thả</p>
                            </div>

                            <p class="text-sm leading-5 text-gray-600">PDF, ZIP, RAR tối đa 10MB</p>
                        </div>
                    </div>

                    <!-- Selected Files Preview -->
                    <div id="selected-files" class="mt-4 space-y-2 hidden">
                        <h4 class="text-base font-medium text-gray-900">Tệp đã chọn</h4>
                        <ul id="file-list" class="divide-y divide-gray-200"></ul>
                    </div>

                    <!-- Hidden input to store uploaded file IDs -->
                    <input type="hidden" name="uploaded_attachment_ids" id="uploaded_attachment_ids"
                           value="{{ isset($post) ? json_encode($post->attachments->pluck('id')) : '[]' }}">
                </div>

                <!-- Source URL field (Uneditable) -->
                @if(isset($post) && $post->source_url)
                    <div class="mb-4">
                        <label for="source_url" class="mb-3 block text-sm font-medium text-[#1c2434]">URL nguồn</label>

                        <div class="flex items-center gap-4">
                            <input type="text" id="source_url" name="source_url" value="{{ $post->source_url }}"
                                   class="flex-1 px-3 py-2 border rounded-md text-gray-900 bg-gray-100"
                                   readonly>

                            <a href="{{ $post->source_url }}" target="_blank" rel="noopener noreferrer nofollow"
                               class="whitespace-nowrap bg-indigo-600 hover:bg-indigo-900 p-2 rounded text-white text-sm font-medium">
                                Mở URL
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ isset($post) ? 'Cập nhật' : 'Tạo' }}
                </button>
            </div>
        </form>
    </div>
@endsection

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
            let uploadedIds = [];

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
                li.className = 'py-4 flex items-center justify-between opacity-0 transform translate-y-2';

                // Format file size
                const size = file.size < 1024000
                    ? `${(file.size / 1024).toFixed(2)} KB`
                    : `${(file.size / 1024 / 1024).toFixed(2)} MB`;

                li.innerHTML = `
                    <div class="flex items-center flex-1">
                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                        </svg>
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
                        <button type="button" class="text-red-500 hover:text-red-700 text-sm font-medium" onclick="removeFile('${fileId}', ${uploadId})">
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
            const dropZone = document.querySelector('.border-dashed');

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
                dropZone.classList.add('border-indigo-600', 'bg-indigo-50', 'transition-colors', 'duration-300');
            }

            function unhighlight(e) {
                dropZone.classList.remove('border-indigo-600', 'bg-indigo-50');
            }

            dropZone.addEventListener('drop', (e) => {
                const files = Array.from(e.dataTransfer.files);
                handleFiles(files);
            });
        });
    </script>
@endpush
