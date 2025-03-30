@extends('admin_layouts.admin')

@section('title', 'Cài Đặt Trang Chủ')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">Cài Đặt Trang Chủ</h2>
                    <p class="mt-1 text-white/90">Quản lý nội dung hiển thị tại trang chủ website</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.settings.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/20 py-2 px-4 text-center font-medium text-white hover:bg-white/30 transition-all duration-200">
                        <span class="iconify" data-icon="mdi-cog-outline"></span>
                        Cài Đặt Chung
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/20 py-2 px-4 text-center font-medium text-white hover:bg-white/30 transition-all duration-200">
                        <span class="iconify" data-icon="mdi-arrow-left"></span>
                        Quay Lại Dashboard
                    </a>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 h-64 w-64 rounded-full bg-white/5"></div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-100 p-4 text-green-700 flex items-center shadow-md animate-fadeIn">
                <span class="iconify mr-2 text-xl" data-icon="mdi-check-circle"></span>
                <span>{{ session('success') }}</span>
                <button type="button" class="ml-auto" onclick="this.parentElement.remove()">
                    <span class="iconify" data-icon="mdi-close"></span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-lg bg-red-100 p-4 text-red-700 flex items-center shadow-md animate-fadeIn">
                <span class="iconify mr-2 text-xl" data-icon="mdi-alert-circle"></span>
                <span>{{ session('error') }}</span>
                <button type="button" class="ml-auto" onclick="this.parentElement.remove()">
                    <span class="iconify" data-icon="mdi-close"></span>
                </button>
            </div>
        @endif

        <!-- Settings Form -->
        <form action="{{ route('admin.settings.updateHome') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6">
                <!-- Banner Section -->
                <div class="rounded-xl border border-stroke bg-white p-6 shadow-md">
                    <div class="mb-6 flex items-center">
                    <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100">
                        <span class="iconify text-xl text-indigo-600" data-icon="mdi-image-area"></span>
                    </span>
                        <h3 class="text-xl font-semibold text-black">Banner Section</h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Homepage Hero Banner -->
                        <div class="form-group">
                            <label for="home_hero_banner" class="mb-2.5 block font-medium text-black">
                                Banner Trang Chủ
                            </label>
                            <div class="bg-gray-50 p-5 rounded-lg border border-gray-100">
                                <div id="home_hero_banner_preview" class="mb-4 flex justify-center">
                                    @if(setting('home_hero_banner'))
                                        <img src="{{ Storage::url(setting('home_hero_banner')) }}" alt="Banner Trang Chủ" class="max-w-full h-auto max-h-48 rounded-lg shadow-sm">
                                    @else
                                        <div class="flex flex-col items-center justify-center h-32 w-full border-2 border-dashed border-gray-300 rounded-lg text-gray-400">
                                            <span class="iconify text-3xl mb-2" data-icon="mdi-image-outline"></span>
                                            <span class="text-sm">Chưa có banner</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="file-input-wrapper">
                                    <label for="home_hero_banner" class="inline-flex items-center justify-center w-full py-3 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                                        <span class="iconify mr-2" data-icon="mdi-upload"></span>
                                        Tải lên banner mới
                                    </label>
                                    <input type="file" name="home_hero_banner" id="home_hero_banner" class="hidden"
                                           onchange="previewImage(this, 'home_hero_banner_preview', 'max-w-full h-auto max-h-48 rounded-lg shadow-sm')">
                                </div>
                                <p class="mt-2 text-xs text-gray-500 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                    Khuyến nghị kích thước 1920x600px để hiển thị tốt nhất
                                </p>
                            </div>
                            @error('home_hero_banner')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Homepage Hero Banner URL -->
                        <div class="form-group">
                            <label for="home_hero_banner_url" class="mb-2.5 block font-medium text-black">
                                URL Banner
                                <span class="text-sm font-normal text-gray-500 ml-1">(Liên kết khi click vào banner)</span>
                            </label>
                            <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-link-variant"></span>
                            </span>
                                <input type="text" name="home_hero_banner_url" id="home_hero_banner_url"
                                       value="{{ old('home_hero_banner_url', setting('home_hero_banner_url')) }}"
                                       class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                       placeholder="https://example.com/destination-page">
                            </div>
                            @error('home_hero_banner_url')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Home Hero Description with Code Editor -->
                        <div class="form-group">
                            <label for="home_hero_description" class="mb-2.5 block font-medium text-black">
                                Mô Tả Trang Web
                                <span class="text-sm font-normal text-gray-500 ml-1">(Hỗ trợ HTML)</span>
                            </label>
                            <div class="rounded-lg overflow-hidden border border-gray-300">
                                <div class="bg-gray-800 px-4 py-2 flex items-center justify-between">
                                    <span class="text-white text-sm font-medium">HTML Editor</span>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" id="editor-fullscreen" class="text-gray-300 hover:text-white">
                                            <span class="iconify" data-icon="mdi-fullscreen"></span>
                                        </button>
                                    </div>
                                </div>
                                <textarea name="home_hero_description" id="home_hero_description" style="display: none;">{{ old('home_hero_description', setting('home_hero_description')) }}</textarea>
                                <div id="editor" class="border-t"></div>
                            </div>
                            @error('home_hero_description')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Instruction Section -->
                <div class="rounded-xl border border-stroke bg-white p-6 shadow-md">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center">
                        <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                            <span class="iconify text-xl text-amber-600" data-icon="mdi-lightbulb-outline"></span>
                        </span>
                            <h3 class="text-xl font-semibold text-black">Instruction Section</h3>
                        </div>
                        <button type="button" id="add-step-button"
                                class="inline-flex items-center justify-center rounded-lg border border-primary py-2 px-4 text-sm font-medium text-primary hover:bg-primary hover:text-white transition-all duration-200">
                            <span class="iconify mr-1" data-icon="mdi-plus"></span>
                            Thêm Bước
                        </button>
                    </div>

                    <div id="instruction-steps" class="space-y-6">
                        @php
                            $steps = old('instruction_steps', json_decode(setting('home_instruction_steps'), true)) ?? [['title' => '', 'description' => '']];
                        @endphp

                        @foreach($steps as $index => $step)
                            <div class="step-container p-5 rounded-lg border border-gray-100 bg-gray-50/80 hover:bg-gray-50 transition-all duration-200">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-medium mr-3">
                                            {{ $index + 1 }}
                                        </div>
                                        <h4 class="font-medium text-gray-800">Bước {{ $index + 1 }}</h4>
                                    </div>
                                    @if($index > 0)
                                        <button type="button" onclick="removeStep(this)"
                                                class="inline-flex items-center justify-center h-8 w-8 rounded-full text-red-500 hover:bg-red-50 transition-all duration-200">
                                            <span class="iconify" data-icon="mdi-trash-can-outline"></span>
                                        </button>
                                    @endif
                                </div>

                                <div class="space-y-4">
                                    <!-- Step Title -->
                                    <div class="form-group">
                                        <label class="mb-2 block text-sm font-medium text-gray-700">
                                            Tiêu Đề Bước {{ $index + 1 }}
                                        </label>
                                        <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                        <span class="iconify" data-icon="mdi-format-title"></span>
                                    </span>
                                            <input type="text" name="instruction_steps[{{$index}}][title]"
                                                   placeholder="Tiêu đề bước {{$index + 1}}"
                                                   value="{{ $step['title'] }}"
                                                   class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none">
                                        </div>
                                    </div>

                                    <!-- Step Description Editor -->
                                    <div class="form-group">
                                        <label class="mb-2 block text-sm font-medium text-gray-700">
                                            Mô Tả Chi Tiết
                                            <span class="text-sm font-normal text-gray-500 ml-1">(Hỗ trợ HTML)</span>
                                        </label>
                                        <div class="rounded-lg overflow-hidden border border-gray-300">
                                            <div class="bg-gray-800 px-4 py-2 flex items-center">
                                                <span class="text-white text-sm font-medium">Bước {{ $index + 1 }} - Editor</span>
                                            </div>
                                            <textarea name="instruction_steps[{{$index}}][description]"
                                                      id="step_description_{{$index}}"
                                                      style="display: none;"
                                                      placeholder="Mô tả chi tiết bước {{$index + 1}}">{{ $step['description'] }}</textarea>
                                            <div id="editor_{{$index}}" class="border-t"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('instruction_steps')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                        {{ $message }}
                    </p>
                    @enderror
                    @error('instruction_steps.*.title')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                        {{ $message }}
                    </p>
                    @enderror
                    @error('instruction_steps.*.description')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="sticky bottom-6 z-10">
                    <div class="rounded-xl border border-stroke bg-white p-6 shadow-md">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h4 class="text-lg font-semibold text-black">Lưu Thay Đổi</h4>
                                <p class="text-sm text-gray-500">Cập nhật cài đặt trang chủ</p>
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary py-3 px-6 font-medium text-white hover:bg-primary/90 hover:shadow-lg focus:ring-4 focus:ring-primary/30 transition-all duration-200 transform hover:-translate-y-1">
                                <span class="iconify" data-icon="mdi-content-save"></span>
                                Lưu Cài Đặt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">

    <style>
        .animate-fadeIn {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        input:focus, textarea:focus, select:focus {
            border-color: rgb(99 102 241);
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.2);
        }

        .form-group:hover label {
            color: rgb(99 102 241);
            transition: all 0.2s;
        }

        /* CodeMirror custom styles */
        .CodeMirror {
            height: auto;
            min-height: 150px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            line-height: 1.6;
        }

        .CodeMirror-focused {
            border-color: rgb(99 102 241);
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.2);
        }

        .CodeMirror-fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            height: auto;
            z-index: 9999;
        }

        /* Step container hover effect */
        .step-container:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Sticky submit button - make sure it's always visible */
        @media (min-width: 768px) {
            .sticky {
                position: sticky;
                bottom: 20px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closetag.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/display/fullscreen.min.js"></script>

    <script>
        // Initialize CodeMirror
        const initialContent = document.getElementById("home_hero_description").value || '';

        var editor = CodeMirror(document.getElementById("editor"), {
            mode: "htmlmixed",
            theme: "dracula",
            value: initialContent,
            lineNumbers: true,
            autoCloseTags: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 4,
            lineWrapping: true,
            extraKeys: {
                "F11": function(cm) {
                    cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                },
                "Esc": function(cm) {
                    if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                }
            }
        });

        // Update hidden textarea before form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            // Get the editor content
            const content = editor.getValue();

            // Update the hidden textarea
            document.getElementById("home_hero_description").value = content;

            // Validate content
            if (!content.trim()) {
                e.preventDefault();
                alert('Mô tả trang web không được để trống');
                editor.focus();
            }
        });

        // Initialize editor with content if exists
        editor.on('change', function() {
            document.getElementById("home_hero_description").value = editor.getValue();
        });

        // Fullscreen toggle button
        document.getElementById('editor-fullscreen').addEventListener('click', function() {
            editor.setOption("fullScreen", !editor.getOption("fullScreen"));
        });

        // Enhanced Image preview function
        function previewImage(input, previewId, classNames) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}" class="${classNames}" alt="Preview">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Store CodeMirror instances
        const editors = new Map();

        // Initialize CodeMirror for a step
        function initializeStepEditor(index) {
            const textarea = document.getElementById(`step_description_${index}`);
            const editorContainer = document.getElementById(`editor_${index}`);

            if (textarea && editorContainer) {
                const stepEditor = CodeMirror(editorContainer, {
                    mode: "htmlmixed",
                    theme: "dracula",
                    value: textarea.value || '',
                    lineNumbers: true,
                    autoCloseTags: true,
                    autoCloseBrackets: true,
                    matchBrackets: true,
                    indentUnit: 4,
                    lineWrapping: true
                });

                // Update textarea on change
                stepEditor.on('change', function() {
                    textarea.value = stepEditor.getValue();
                });

                // Store editor instance
                editors.set(index, stepEditor);
            }
        }

        // Enhanced functionality for the instruction steps
        const stepsContainer = document.getElementById('instruction-steps');
        const addStepButton = document.getElementById('add-step-button');

        // Add new step when clicking the button
        addStepButton.addEventListener('click', function() {
            addNewStep();
        });

        // Add new step
        function addNewStep() {
            const steps = stepsContainer.querySelectorAll('.step-container');
            const newIndex = steps.length;

            // Create new step HTML
            const stepHtml = `
            <div class="step-container p-5 rounded-lg border border-gray-100 bg-gray-50/80 hover:bg-gray-50 transition-all duration-200 animate-fadeIn">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-medium mr-3">
                            ${newIndex + 1}
                        </div>
                        <h4 class="font-medium text-gray-800">Bước ${newIndex + 1}</h4>
                    </div>
                    <button type="button" onclick="removeStep(this)"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-full text-red-500 hover:bg-red-50 transition-all duration-200">
                        <span class="iconify" data-icon="mdi-trash-can-outline"></span>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="form-group">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                        Tiêu Đề Bước ${newIndex + 1}
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                <span class="iconify" data-icon="mdi-format-title"></span>
                            </span>
                            <input type="text" name="instruction_steps[${newIndex}][title]"
                            placeholder="Tiêu đề bước ${newIndex + 1}"
                            class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none">
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Mô Tả Chi Tiết
                            <span class="text-sm font-normal text-gray-500 ml-1">(Hỗ trợ HTML)</span>
                        </label>
                        <div class="rounded-lg overflow-hidden border border-gray-300">
                            <div class="bg-gray-800 px-4 py-2 flex items-center">
                                <span class="text-white text-sm font-medium">Bước ${newIndex + 1} - Editor</span>
                            </div>
                            <textarea name="instruction_steps[${newIndex}][description]"
                            id="step_description_${newIndex}"
                            style="display: none;"
                            placeholder="Mô tả chi tiết bước ${newIndex + 1}"></textarea>
                            <div id="editor_${newIndex}" class="border-t"></div>
                        </div>
                    </div>
                </div>
            </div>
            `;

        // Add new step to container
        stepsContainer.insertAdjacentHTML('beforeend', stepHtml);

        // Initialize CodeMirror for new step
        initializeStepEditor(newIndex);
    }

    // Enhanced remove step
    function removeStep(button) {
        const stepContainer = button.closest('.step-container');

        // Add fade-out animation
        stepContainer.style.transition = 'all 0.3s';
        stepContainer.style.opacity = '0';
        stepContainer.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            const index = Array.from(stepsContainer.children).indexOf(stepContainer);

            // Remove CodeMirror instance
            if (editors.has(index)) {
                editors.delete(index);
            }

            stepContainer.remove();

            // Renumber remaining steps
            const steps = stepsContainer.querySelectorAll('.step-container');
            steps.forEach((step, newIndex) => {
                // Update step number in UI
                const stepNumber = step.querySelector('.flex.items-center.justify-center');
                if (stepNumber) stepNumber.textContent = newIndex + 1;

                const stepTitle = step.querySelector('h4');
                if (stepTitle) stepTitle.textContent = `Bước ${newIndex + 1}`;

                // Update step number in labels
                const labels = step.querySelectorAll('label');
                if (labels[0]) labels[0].textContent = `Tiêu Đề Bước ${newIndex + 1}`;

                // Update editor header
                const editorHeader = step.querySelector('.bg-gray-800 span');
                if (editorHeader) editorHeader.textContent = `Bước ${newIndex + 1} - Editor`;

                // Update input names
                const inputs = step.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    const newName = input.name.replace(/\[\d+\]/, `[${newIndex}]`);
                    input.name = newName;
                    if (input.placeholder) {
                        input.placeholder = input.placeholder.replace(/\d+/, newIndex + 1);
                    }
                    if (input.id && input.id.startsWith('step_description_')) {
                        input.id = `step_description_${newIndex}`;
                    }
                });

                const editorDiv = step.querySelector(`div[id^="editor_"]`);
                if (editorDiv) {
                    editorDiv.id = `editor_${newIndex}`;
                }
            });

            // Reinitialize editors with new indices
            editors.clear();
            steps.forEach((step, index) => {
                initializeStepEditor(index);
            });
        }, 300);
    }

    // Initialize all existing steps
    document.querySelectorAll('.step-container').forEach((step, index) => {
        initializeStepEditor(index);
    });

    // Update form submission to include all editor contents
    document.querySelector('form').addEventListener('submit', function(e) {
        // Handle main description editor
        const content = editor.getValue();
        document.getElementById("home_hero_description").value = content;

        if (!content.trim()) {
            e.preventDefault();
            alert('Mô tả trang web không được để trống');
            editor.focus();
            return;
        }

        // Handle all step editors
        const steps = stepsContainer.querySelectorAll('.step-container');
        let hasError = false;

        steps.forEach((step, index) => {
            const titleInput = step.querySelector('input[type="text"]');
            const stepEditor = editors.get(index);
            const textarea = document.getElementById(`step_description_${index}`);

            // Update textarea with editor content
            if (textarea && stepEditor) {
                textarea.value = stepEditor.getValue();
            }

            // Validate non-empty steps
            if (!titleInput.value.trim()) {
                titleInput.classList.add('border-red-500');
                hasError = true;
            } else {
                titleInput.classList.remove('border-red-500');
            }

            if (!stepEditor || !stepEditor.getValue().trim()) {
                step.querySelector('.CodeMirror').classList.add('border-red-500');
                hasError = true;
            } else {
                step.querySelector('.CodeMirror').classList.remove('border-red-500');
            }
        });

        if (hasError) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin cho tất cả các bước');
        }
    });
</script>
@endpush
