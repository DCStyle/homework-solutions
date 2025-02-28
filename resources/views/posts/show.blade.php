@extends('layouts.app')

@section('seo')
    {!! seo($post->getDynamicSEOData()) !!}
@endsection

@section('content')
    <div class="flex justify-between">
        <div class="sidebar-left-content w-[320px] h-auto flex-shrink-0 flex-grow-0 max-xl:w-[280px] max-md:hidden">
            <div class="sticky top-10 bg-white border shadow-md">
                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl text-orange-400 font-bold">
                        {{ $post->chapter->book->name }}
                    </h2>
                </div>

                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl font-bold">Bài học cùng chương</h2>
                    <ul class="list-disc list-inside mt-4">
                        @foreach ($post->chapter->posts as $item)
                            <li class="mb-2">
                                <a title="{{ $item->title }}" href="{{ route('posts.show', $item->slug) }}"
                                   class="text-gray-800 hover:text-orange-400 {{ $item->id == $post->id ? 'text-orange-400' : '' }}">
                                    {{ $item->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-4 text-md">
                    <h2 class="text-xl font-bold">Bài học chương khác</h2>
                    <ul class="mt-4">
                        @foreach ($post->chapter->book->chapters as $chapter)
                            @if($chapter->id != $post->chapter_id)
                                <li class="mb-4">
                                    <h3 class="font-bold text-green-700">{{ $chapter->name }}</h3>
                                    <ul class="list-disc list-inside mt-2">
                                        @foreach ($chapter->posts as $item)
                                            <li class="mb-2">
                                                <a title="{{ $item->title }}" href="{{ route('posts.show', $item->slug) }}"
                                                   class="text-gray-800 hover:text-orange-400">
                                                    {{ $item->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="mx-auto p-6 min-w-0 w-full max-xl:p-4 max-md:p2">
            <h1 class="text-2xl font-bold text-orange-400 py-2 border-b-2 border-b-blue-800">
                <a title="{{ $post->title }}" href="{{ route('posts.show', $post->slug) }}">
                    [{{ $post->chapter->book->name }}]
                    {{ $post->title }}
                </a>
            </h1>

            <nav aria-label="breadcrumb" class="w-full py-4">
                <ol class="w-full flex flex-wrap items-center gap-2 text-sm mx-0 px-0">
                    <li class="flex items-center">
                        <a
                            href="{{ route('home') }}"
                            title="{{ setting('site_name', 'Home') }}"
                            class="text-gray-600 hover:text-blue-500 transition-colors duration-200"
                        >
                            {{ setting('site_name', 'Home') }}
                        </a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mx-2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </li>

                    <li class="flex items-center">
                        <a
                            href="{{ route('categories.show', $post->chapter->book->group->category->slug) }}"
                            title="{{ $post->chapter->book->group->category->name }}"
                            class="text-gray-600 hover:text-blue-500 transition-colors duration-200"
                        >
                            {{ $post->chapter->book->group->category->name }}
                        </a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mx-2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </li>

                    <li class="flex items-center">
                        <a
                            href="{{ route('books.show', $post->chapter->book->slug) }}"
                            title="{{ $post->chapter->book->name }}"
                            class="text-gray-600 hover:text-blue-500 transition-colors duration-200"
                        >
                            {{ $post->chapter->book->group->name . ' ' . $post->chapter->book->group->category->name . ' - ' . $post->chapter->book->name }}
                        </a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mx-2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </li>

                    <li>
                        <span
                            class="text-blue-500 font-medium"
                            title="{{ $post->title }}"
                        >
                            {{ $post->title }}
                        </span>
                    </li>
                </ol>
            </nav>

            <div class="mt-4 bg-white p-4 text-md text-green-700 border shadow-md">
                <h2 class="text-xl">{!! "Hướng dẫn học bài: <b>$post->title - {$post->chapter->book->group->name} {$post->chapter->book->group->category->name}</b>.
                    Đây là sách giáo khoa nằm trong bộ sách <b>'{$post->chapter->book->name} {$post->chapter->book->group->category->name}'</b> được biên soạn theo chương trình đổi mới của Bộ giáo dục.
                    Hi vọng, với cách hướng dẫn cụ thể và giải chi tiết các bé sẽ nắm bài học tốt hơn."
            !!}</h2>
            </div>

            <!-- Main content -->
            <div class="mt-4 text-lg">
                {!! $content !!}
            </div>

            <!-- Attachments Block -->
            @if($post->attachments->count() > 0)
                <div class="mt-8 bg-white rounded-lg border border-gray-200 shadow-md">
                    <div class="p-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            Tài liệu đính kèm
                        </h2>
                    </div>

                    <div class="p-4">
                        <ul class="divide-y divide-gray-200">
                            @foreach($post->attachments as $attachment)
                                <li class="py-3 first:pt-0 last:pb-0">
                                    <div class="flex items-center justify-between group">
                                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                                            <!-- File type icon -->
                                            <div class="flex-shrink-0">
                                                @php
                                                    $iconColor = match(strtolower($attachment->extension)) {
                                                        'pdf' => 'text-red-500',
                                                        'doc', 'docx' => 'text-blue-500',
                                                        'xls', 'xlsx' => 'text-green-500',
                                                        default => 'text-gray-500'
                                                    };
                                                @endphp
                                                <svg class="h-8 w-8 {{ $iconColor }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            </div>

                                            <!-- File info -->
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $attachment->original_filename }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    {{ number_format($attachment->file_size / 1024, 2) }} KB
                                                    • {{ strtoupper($attachment->extension) }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Action buttons -->
                                        <div class="ml-4 flex items-center space-x-2">
                                            @if(strtolower($attachment->extension) === 'pdf')
                                                <button
                                                    onclick="openPdfPreview('{{ route('attachments.preview', $attachment->id) }}', '{{ $attachment->original_filename }}')"
                                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                                                >
                                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Xem trước
                                                </button>
                                            @endif

                                            <a href="{{ route('attachments.download', $attachment->id) }}"
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                Tải xuống
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- PDF Preview Modal -->
            <div id="pdf-preview-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white w-full h-full md:w-4/5 md:h-5/6 flex flex-col rounded-lg shadow-xl">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                        <h3 id="pdf-preview-title" class="text-xl font-semibold text-gray-900 truncate"></h3>
                        <button onclick="closePdfPreview()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="flex-1 p-4 bg-gray-100">
                        <object
                            id="pdf-preview-object"
                            class="w-full h-full rounded-lg"
                            type="application/pdf"
                            data=""
                        >
                            <div class="flex items-center justify-center h-full bg-white rounded-lg">
                                <p class="text-gray-500">Unable to display PDF. Please try downloading instead.</p>
                            </div>
                        </object>
                    </div>
                </div>
            </div>

            <!-- Existing related content sections -->
            <div class="mt-8">
                <h2 class="text-3xl font-medium p-2 border-b-2 border-orange-400 mb-4">
                    Giải bài tập những môn khác
                </h2>

                <div class="p-2 border">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($post->chapter->book->group->category->bookGroups as $group)
                            <div>
                                <h3 class="text-xl font-medium text-orange-400">{{ $group->name }}</h3>

                                <div class="py-4 flex flex-col gap-2 border-dashed border-blue-600">
                                    @foreach($group->books as $book)
                                        <li class="flex items-center gap-2">
                                            <span class="iconify text-xl" data-icon="mdi-chevron-right"></span>

                                            <a href="{{ route('books.show', $book->slug) }}" title="{{ $book->name }}" class="text-md font-medium text-gray-800 hover:underline hover:text-orange-400">
                                                {{ $book->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <x-footer-latest-posts :title="'Lời giải và bài tập ' . $category->name . ' đang được quan tâm'" :posts="$footerLatestPosts" />
            </div>
        </div>

        @include('layouts.sidebar-right')
    </div>
@endsection

@push('scripts')
    <!-- Temporary fix for broken images -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function handleImageFallback(img) {
                return new Promise((resolve) => {
                    const tempImg = new Image();
                    tempImg.onload = () => resolve(false);
                    tempImg.onerror = () => {
                        if (img.src.match(/\.(jpg|png|jpeg)$/i)) {
                            const extension = img.src.match(/\.(jpg|png|jpeg)$/i)[0];
                            if (!img.src.endsWith(extension.toUpperCase())) {
                                const newSrc = img.src.replace(/\.(jpg|png|jpeg)$/i, extension.toUpperCase()) + '?t=' + Date.now();
                                if (newSrc !== img.src) {
                                    img.src = newSrc;
                                }
                            }
                        }
                        resolve(true);
                    };
                    tempImg.src = img.src;
                });
            }

            document.querySelectorAll('img').forEach(img => {
                // Only check images that might be unloaded
                if (!img.complete || img.naturalHeight === 0) {
                    handleImageFallback(img);
                }
            });
        });
    </script>

    <!-- PDF Preview Script -->
    <script>
        function openPdfPreview(url, filename) {
            const modal = document.getElementById('pdf-preview-modal');
            const pdfObject = document.getElementById('pdf-preview-object');
            const title = document.getElementById('pdf-preview-title');

            // Add loading state
            modal.classList.remove('hidden');
            title.textContent = 'Loading ' + filename + '...';

            // First fetch the URL to get the redirect
            fetch(url)
                .then(response => {
                    if (response.ok) {
                        return response.url;
                    }
                    throw new Error('Failed to load PDF');
                })
                .then(finalUrl => {
                    // Create a URL with custom protocol to prevent download
                    const pdfUrl = finalUrl + '#toolbar=0&navpanes=0&scrollbar=1&statusbar=0&messages=0&download=0&view=FitH';
                    pdfObject.setAttribute('data', pdfUrl);
                    title.textContent = filename;

                    // Add event listener to prevent right-click
                    pdfObject.addEventListener('contextmenu', (e) => e.preventDefault());
                })
                .catch(error => {
                    console.error('Error:', error);
                    title.textContent = 'Error loading PDF';
                    setTimeout(closePdfPreview, 2000);
                });

            // Prevent body scrolling
            document.body.style.overflow = 'hidden';

            // Handle click outside modal to close
            const closeOnOutsideClick = function(e) {
                if (e.target === modal) {
                    closePdfPreview();
                }
            };
            modal.addEventListener('click', closeOnOutsideClick);

            // Handle escape key to close modal
            const closeOnEscape = function(e) {
                if (e.key === 'Escape') {
                    closePdfPreview();
                }
            };
            document.addEventListener('keydown', closeOnEscape);

            // Store event listeners for cleanup
            modal._closeHandlers = {
                click: closeOnOutsideClick,
                keydown: closeOnEscape
            };
        }

        function closePdfPreview() {
            const modal = document.getElementById('pdf-preview-modal');
            const pdfObject = document.getElementById('pdf-preview-object');

            // Remove event listeners
            if (modal._closeHandlers) {
                modal.removeEventListener('click', modal._closeHandlers.click);
                document.removeEventListener('keydown', modal._closeHandlers.keydown);
                delete modal._closeHandlers;
            }

            // Hide modal with fade-out effect
            modal.classList.add('animate-fade-out');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('animate-fade-out');
                pdfObject.setAttribute('data', '');
                document.body.style.overflow = '';
            }, 200);
        }
    </script>

    <script>
        MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']]
            },
            svg: {
                fontCache: 'global'
            }
        };
    </script>

    <script
        type="text/javascript"
        id="MathJax-script"
        async
        src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js">
    </script>
@endpush

@push('styles')
    <style>
        .animate-fade-in {
            animation: fadeIn 0.2s ease-in-out;
        }

        .animate-fade-out {
            animation: fadeOut 0.2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        /* Additional styles to prevent PDF selection and download */
        #pdf-preview-object {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            pointer-events: auto; /* Changed from none to auto to allow scrolling */
        }

        #pdf-preview-object::selection {
            background: transparent;
        }
    </style>
@endpush
