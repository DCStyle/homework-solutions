<div class="relative">
    <textarea name="{{ $name }}" id="{{ $name }}"
              class="w-full rounded-lg border-[1.5px] border-primary bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" rows="6">
        {!! $value ?? '' !!}
    </textarea>

    <!-- Toggle Button -->
    <button type="button" id="toggle-{{ $name }}"
            class="absolute top-2 right-2 z-50 rounded bg-primary px-3 py-1 text-sm text-white hover:bg-primary/80">
        Switch to HTML
    </button>
</div>

<!-- Include TinyMCE from the public folder -->
<script src="{{ env('APP_ENV') === 'public'
                    ? secure_asset('js/image-upload.js')
                    : asset('js/image-upload.js')
              }}"></script>
<script src="{{ env('APP_ENV') === 'public'
                ? secure_asset('js/tinymce/tinymce.min.js')
                : asset('js/tinymce/tinymce.min.js')
              }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let isCodeView = false;
        const toggleButton = document.getElementById('toggle-{{ $name }}');
        const textarea = document.getElementById('{{ $name }}');
        let editor = null;

        function initTinyMCE() {
            return tinymce.init({
                selector: '#{{ $name }}',
                plugins: 'lists link image table code',
                smart_paste: true,
                external_plugins: {
                    'mathjax': "{{ env('APP_ENV') === 'public'
                                    ? secure_asset('js/tinymce/plugins/mathjax/plugin.min.js')
                                    : asset('js/tinymce/plugins/mathjax/plugin.min.js')
                            }}"
                },
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table mathjax',
                mathjax: {
                    lib: 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js',
                    configUrl: "{{ env('APP_ENV') === 'public'
                                    ? secure_asset('js/tinymce/plugins/mathjax/config.js')
                                    : asset('js/tinymce/plugins/mathjax/config.js')
                                }}"
                },
                height: 900,
                license_key: 'gpl',
                images_upload_url: '{{ route('images.upload') }}',
                images_upload_handler: function (blobInfo, progress) {
                    return new Promise((resolve, reject) => {
                        const formData = new FormData();
                        formData.append('image', blobInfo.blob(), blobInfo.filename());

                        fetch('{{ route('images.upload') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: formData
                        })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    // Store the image ID in a hidden input
                                    const imageIdsInput = document.getElementById('uploaded_image_ids');
                                    const currentIds = imageIdsInput.value ? JSON.parse(imageIdsInput.value) : [];
                                    currentIds.push(result.image_id);
                                    imageIdsInput.value = JSON.stringify(currentIds);

                                    resolve(result.url);
                                } else {
                                    reject(result.message);
                                }
                            })
                            .catch(error => reject(error));
                    });
                },
                setup: function(ed) {
                    editor = ed;
                }
            });
        }

        // Initialize TinyMCE
        initTinyMCE();

        // Toggle button click handler
        toggleButton.addEventListener('click', function() {
            isCodeView = !isCodeView;

            if (isCodeView) {
                // Switch to HTML view
                const content = editor.getContent();
                editor.destroy();

                // Show the original textarea and set its value
                textarea.style.display = 'block';
                textarea.value = content;

                // Style the textarea for code view
                textarea.style.fontFamily = 'monospace';
                textarea.style.whiteSpace = 'pre-wrap';

                toggleButton.textContent = 'Switch to Rich Text';
            } else {
                // Switch back to rich text editor
                textarea.style.fontFamily = '';
                textarea.style.whiteSpace = '';

                initTinyMCE().then(() => {
                    editor.setContent(textarea.value);
                });

                toggleButton.textContent = 'Switch to HTML';
            }
        });
    });
</script>
