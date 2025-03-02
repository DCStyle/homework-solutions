@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-2">Cài đặt trang chủ</h2>

        <form action="{{ route('admin.settings.updateHome') }}"
              method="POST"
              enctype="multipart/form-data"
              class="rounded-sm border bg-white shadow">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-4">
                <h3 class="text-lg font-medium text-[#1c2434]">Banner section</h3>

                <div class="space-y-4">
                    <!-- Homepage Hero Banner -->
                    <div>
                        <label for="home_hero_banner" class="mb-3 block text-sm font-medium text-[#1c2434]">
                            Banner trang chủ
                        </label>
                        <input type="file"
                               name="home_hero_banner"
                               id="home_hero_banner"
                               class="relative w-full rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                               onchange="previewImage(this, 'home_hero_banner_preview')">
                        <div id="home_hero_banner_preview" class="mt-2">
                            @if(setting('home_hero_banner'))
                                <img src="{{ Storage::url(setting('home_hero_banner')) }}" alt="Site Logo" class="w-auto h-24 object-cover rounded">
                            @endif
                        </div>
                        @error('home_hero_banner')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Homepage Hero Banner URL -->
                    <div>
                        <label for="home_hero_banner_url" class="mb-3 block text-sm font-medium text-[#1c2434]">
                            URL banner trang chủ
                            <small class="text-gray-500">(URL khi click vào banner)</small>
                        </label>
                        <input type="text"
                               name="home_hero_banner_url"
                               id="home_hero_banner_url"
                               value="{{ old('home_hero_banner_url', setting('home_hero_banner_url')) }}"
                               placeholder="https://example.com/destination-page"
                               class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none transition focus:border-primary active:border-primary">
                        @error('home_hero_banner_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Home Hero Description with Code Editor -->
                    <div>
                        <label for="home_hero_description" class="mb-3 block text-sm font-medium text-[#1c2434]">
                            Mô tả trang web
                            <small class="text-gray-500">(Có thể dùng HTML)</small>
                        </label>
                        <textarea name="home_hero_description"
                                  id="home_hero_description"
                                  style="display: none;">{{ old('home_hero_description', setting('home_hero_description')) }}</textarea>
                        <div id="editor" class="border rounded"></div>
                        @error('home_hero_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <h3 class="text-lg font-medium text-[#1c2434]">Instruction section</h3>

                <div class="space-y-4">
                    <!-- Homepage Instruction Steps -->
                    <div>
                        <label class="mb-3 block text-sm font-medium text-[#1c2434]">
                            Các bước hướng dẫn
                        </label>
                        <div id="instruction-steps" class="space-y-3">
                            @php
                                $steps = old('instruction_steps', json_decode(setting('home_instruction_steps'), true)) ?? [['title' => '', 'description' => '']];
                            @endphp

                            @foreach($steps as $index => $step)
                                <div class="step-container flex gap-4">
                                    <div class="flex-1 space-y-2">
                                        <input type="text"
                                               name="instruction_steps[{{$index}}][title]"
                                               placeholder="Tiêu đề bước {{$index + 1}}"
                                               value="{{ $step['title'] }}"
                                               class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none transition focus:border-primary active:border-primary">
                                        <textarea name="instruction_steps[{{$index}}][description]"
                                                  id="step_description_{{$index}}"
                                                  style="display: none;"
                                                  placeholder="Mô tả chi tiết bước {{$index + 1}}">{{ $step['description'] }}</textarea>
                                        <div id="editor_{{$index}}" class="border rounded"></div>
                                    </div>
                                    @if($index > 0)
                                        <button type="button"
                                                onclick="removeStep(this)"
                                                class="self-center text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @error('instruction_steps')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('instruction_steps.*.title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('instruction_steps.*.description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit"
                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                        Lưu cài đặt
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script>
        // Initialize CodeMirror
        const initialContent = document.getElementById("home_hero_description").value || '';

        var editor = CodeMirror(document.getElementById("editor"), {
            mode: "htmlmixed",
            theme: "monokai",
            value: initialContent,
            lineNumbers: true,
            autoCloseTags: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 4,
            lineWrapping: true
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

        // Image preview function
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-auto h-24 object-cover rounded">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
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
                    theme: "monokai",
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
                    checkLastStepContent();
                });

                // Store editor instance
                editors.set(index, stepEditor);
            }
        }

        // Dynamic instruction steps
        const stepsContainer = document.getElementById('instruction-steps');

        // Check if last step has content
        function checkLastStepContent() {
            const steps = stepsContainer.querySelectorAll('.step-container');
            const lastStep = steps[steps.length - 1];
            const titleInput = lastStep.querySelector('input[type="text"]');
            const lastIndex = steps.length - 1;
            const lastEditor = editors.get(lastIndex);

            if (titleInput.value.trim() !== '' || (lastEditor && lastEditor.getValue().trim() !== '')) {
                addNewStep();
            }
        }

        // Watch for changes in the last step
        function watchLastStep() {
            const steps = stepsContainer.querySelectorAll('.step-container');
            const lastStep = steps[steps.length - 1];
            const titleInput = lastStep.querySelector('input[type="text"]');

            titleInput.addEventListener('input', checkLastStepContent);
        }

        // Add new step
        function addNewStep() {
            const steps = stepsContainer.querySelectorAll('.step-container');
            const newIndex = steps.length;

            // Create new step HTML
            const stepHtml = `
                <div class="step-container flex gap-4">
                    <div class="flex-1 space-y-2">
                        <input type="text"
                               name="instruction_steps[${newIndex}][title]"
                               placeholder="Tiêu đề bước ${newIndex + 1}"
                               class="w-full rounded border border-stroke bg-transparent py-3 px-5 outline-none transition focus:border-primary active:border-primary">
                        <textarea name="instruction_steps[${newIndex}][description]"
                                  id="step_description_${newIndex}"
                                  style="display: none;"
                                  placeholder="Mô tả chi tiết bước ${newIndex + 1}"></textarea>
                        <div id="editor_${newIndex}" class="border rounded"></div>
                    </div>
                    <button type="button"
                            onclick="removeStep(this)"
                            class="self-center text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            `;

            // Add new step to container
            stepsContainer.insertAdjacentHTML('beforeend', stepHtml);

            // Initialize CodeMirror for new step
            initializeStepEditor(newIndex);

            // Update event listeners
            watchLastStep();
        }

        // Remove step
        function removeStep(button) {
            const stepContainer = button.closest('.step-container');
            const index = Array.from(stepsContainer.children).indexOf(stepContainer);

            // Remove CodeMirror instance
            if (editors.has(index)) {
                editors.delete(index);
            }

            stepContainer.remove();

            // Renumber remaining steps
            const steps = stepsContainer.querySelectorAll('.step-container');
            steps.forEach((step, newIndex) => {
                const inputs = step.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    const newName = input.name.replace(/\[\d+\]/, `[${newIndex}]`);
                    input.name = newName;
                    if (input.id && input.id.startsWith('step_description_')) {
                        input.id = `step_description_${newIndex}`;
                    }
                });

                const editorDiv = step.querySelector(`div[id^="editor_"]`);
                if (editorDiv) {
                    editorDiv.id = `editor_${newIndex}`;
                }

                // Update placeholders
                const titleInput = step.querySelector('input[type="text"]');
                if (titleInput) {
                    titleInput.placeholder = titleInput.placeholder.replace(/\d+/, newIndex + 1);
                }
            });

            // Reinitialize editors with new indices
            editors.clear();
            steps.forEach((step, index) => {
                initializeStepEditor(index);
            });
        }

        // Initialize all existing steps
        document.querySelectorAll('.step-container').forEach((step, index) => {
            initializeStepEditor(index);
        });

        // Initialize watching the last step
        watchLastStep();

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

                // Skip the last step if it's completely empty
                if (index === steps.length - 1 &&
                    !titleInput.value.trim() &&
                    (!stepEditor || !stepEditor.getValue().trim())) {
                    step.remove();
                    return;
                }

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
