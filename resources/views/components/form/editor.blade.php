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

<!-- Load MathJax first -->
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

<!-- Include TinyMCE from the public folder -->
<script src="{{ env('APP_ENV') === 'public'
                    ? secure_asset('js/image-upload.js')
                    : asset('js/image-upload.js')
              }}"></script>
<script src="{{ env('APP_ENV') === 'public'
                    ? secure_asset('js/image-paste-handler.js')
                    : asset('js/image-paste-handler.js')
              }}"></script>
<script src="{{ env('APP_ENV') === 'public'
                ? secure_asset('js/tinymce/tinymce.min.js')
                : asset('js/tinymce/tinymce.min.js')
              }}"></script>

<script>
    // Ensure MathJax is loaded and initialized
    window.MathJax = {
        tex: {
            inlineMath: [['$', '$'], ['\\(', '\\)']],
            displayMath: [['$$', '$$'], ['\\[', '\\]']],
            processEscapes: true
        },
        startup: {
            ready: function() {
                MathJax.startup.defaultReady();
                // Signal TinyMCE that MathJax is ready
                window.MathJaxReady = true;
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        let isCodeView = false;
        const toggleButton = document.getElementById('toggle-{{ $name }}');
        const textarea = document.getElementById('{{ $name }}');
        let editor = null;
        
        // Initialize image paste handler
        const imagePasteHandler = new ImagePasteHandler({
            uploadUrl: '{{ route('images.upload') }}'
        });

        function initTinyMCE() {
            return tinymce.init({
                selector: '#{{ $name }}',
                plugins: 'lists link image table code',
                paste_data_images: true,
                external_plugins: {
                    'mathjax': "{{ env('APP_ENV') === 'public'
                                    ? secure_asset('js/tinymce/plugins/mathjax/plugin.min.js')
                                    : asset('js/tinymce/plugins/mathjax/plugin.min.js')
                            }}"
                },
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table mathjax',
                mathjax: {
                    lib: 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js',
                    symbols: {start: '\\(', end: '\\)'},
                    className: 'math-tex',
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
                                    if (imageIdsInput) {
                                        const currentIds = imageIdsInput.value ? JSON.parse(imageIdsInput.value) : [];
                                        currentIds.push(result.image_id);
                                        imageIdsInput.value = JSON.stringify(currentIds);
                                    }

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
                    
                    // Process content after paste to handle any remaining base64 images
                    ed.on('PastePostProcess', function(e) {
                        // Only process if content exists and has base64 images
                        if (e && e.content && typeof e.content === 'string' && e.content.indexOf('data:image') !== -1) {
                            // Show a loading indicator
                            const loadingId = 'loading-' + Date.now();
                            ed.insertContent('<p id="' + loadingId + '">Processing pasted images... Please wait.</p>');
                            
                            // Process the content asynchronously
                            imagePasteHandler.processContent(e.content)
                                .then(processedContent => {
                                    // Remove the loading indicator
                                    const loadingElement = ed.dom.get(loadingId);
                                    if (loadingElement) {
                                        ed.dom.remove(loadingElement);
                                    }
                                    
                                    // Insert the processed content
                                    ed.insertContent(processedContent);
                                })
                                .catch(err => {
                                    console.error('Error processing pasted images:', err);
                                    // Remove the loading indicator
                                    const loadingElement = ed.dom.get(loadingId);
                                    if (loadingElement) {
                                        ed.dom.remove(loadingElement);
                                    }
                                    // Insert the original content
                                    ed.insertContent(e.content);
                                });
                                
                            // Prevent the default paste behavior
                            e.preventDefault();
                        }
                    });
                    
                    // Process content before save to ensure all base64 images are uploaded
                    ed.on('SaveContent', function(e) {
                        // Only process if content exists and has base64 images
                        if (e && e.content && typeof e.content === 'string' && e.content.indexOf('data:image') !== -1) {
                            // Don't save immediately, process images first
                            e.preventDefault();
                            
                            // Process the content
                            imagePasteHandler.processContent(e.content)
                                .then(processedContent => {
                                    // Update the editor content
                                    ed.setContent(processedContent);
                                    // Now trigger the save again
                                    ed.save();
                                })
                                .catch(err => {
                                    console.error('Error processing images before save:', err);
                                    // Continue with save anyway
                                    e.content = e.content;
                                });
                        }
                    });

                    // Fix for MathJax not being ready
                    ed.on('init', function() {
                        // Force MathJax to be re-initialized if needed
                        if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                            // Ensure MathJax is ready
                            setTimeout(function() {
                                try {
                                    MathJax.typesetPromise();
                                } catch (e) {
                                    console.error('Error initializing MathJax:', e);
                                }
                            }, 1000);
                        }
                    });
                }
            });
        }

        // Wait a bit to make sure MathJax is properly initialized
        setTimeout(function() {
            // Initialize TinyMCE
            initTinyMCE().then(() => {
                // Check if initial content has base64 images and process them
                try {
                    if (editor) {
                        const initialContent = editor.getContent();
                        if (initialContent && typeof initialContent === 'string' && initialContent.indexOf('data:image') !== -1) {
                            // Show loading message
                            editor.setProgressState(true);
                            editor.setContent(initialContent + '<p id="initial-loading">Processing embedded images... Please wait.</p>');
                            
                            // Process the initial content to upload any base64 images
                            imagePasteHandler.processContent(initialContent)
                                .then(processedContent => {
                                    // Remove loading message and update content
                                    editor.setProgressState(false);
                                    const loadingElement = editor.dom.get('initial-loading');
                                    if (loadingElement) {
                                        editor.dom.remove(loadingElement);
                                    }
                                    editor.setContent(processedContent);
                                })
                                .catch(err => {
                                    console.error('Error processing initial images:', err);
                                    editor.setProgressState(false);
                                    const loadingElement = editor.dom.get('initial-loading');
                                    if (loadingElement) {
                                        editor.dom.remove(loadingElement);
                                    }
                                });
                        }
                    }
                } catch (error) {
                    console.error('Error checking initial content:', error);
                }
            }).catch(err => {
                console.error('Error initializing TinyMCE:', err);
            });
        }, 500);

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
