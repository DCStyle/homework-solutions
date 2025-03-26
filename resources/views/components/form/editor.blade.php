<div class="relative">
    <textarea name="{{ $name }}" id="{{ $name }}"
              class="w-full rounded-lg border-[1.5px] border-primary bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" rows="6">
        {!! $value ?? '' !!}
    </textarea>
</div>

<script>
    // First, load MathJax externally
    function loadMathJax() {
        return new Promise((resolve, reject) => {
            if (window.MathJax) {
                resolve(window.MathJax);
                return;
            }

            // Configure MathJax
            window.MathJax = {
                tex: {
                    inlineMath: [['$', '$'], ['\\(', '\\)']],
                    displayMath: [['$$', '$$'], ['\\[', '\\]']],
                    processEscapes: true
                },
                startup: {
                    ready: function() {
                        MathJax.startup.defaultReady();
                        window.MathJaxReady = true;
                        resolve(window.MathJax);
                    }
                },
                options: {
                    enableMenu: false
                }
            };

            // Load MathJax script
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js';
            script.async = true;
            script.onload = () => {
                // MathJax startup.ready will resolve the promise
            };
            script.onerror = () => {
                console.error('Failed to load MathJax');
                window.MathJaxReady = false;
                // Resolve anyway to not block editor initialization
                resolve(null);
            };
            document.head.appendChild(script);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        let editor = null;

        // Initialize image paste handler
        const imagePasteHandler = new ImagePasteHandler({
            uploadUrl: '{{ route('images.upload') }}'
        });

        // Safe MathJax rendering function
        function safeTypeset() {
            try {
                if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
                    return window.MathJax.typesetPromise();
                }
            } catch (e) {
                console.warn('MathJax typesetting failed:', e);
            }
            return Promise.resolve(); // Return resolved promise if MathJax not available
        }

        // Initialize TinyMCE with MathJax support
        async function initTinyMCE() {
            // Ensure MathJax is loaded first
            await loadMathJax();

            return tinymce.init({
                license_key: 'gpl',
                selector: '#{{ $name }}',
                plugins: 'lists link image table code',
                paste_as_text: false, // Allow HTML pasting
                extended_valid_elements: 'span[*],div[*]', // Preserve LaTeX containers
                entities: '160,nbsp,38,amp,60,lt,62,gt', // Prevent entity encoding
                paste_data_images: true,
                external_plugins: {
                    'mathjax': "{{ env('APP_ENV') === 'public'
                                    ? secure_asset('js/tinymce/plugins/mathjax/plugin.min.js')
                                    : asset('js/tinymce/plugins/mathjax/plugin.min.js')
                            }}"
                },
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table mathjax',
                mathjax: {
                    lib: 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js', // We've already loaded this
                    symbols: {start: '\\(', end: '\\)'},
                    className: 'math-tex',
                    configUrl: "{{ env('APP_ENV') === 'public'
                                    ? secure_asset('js/tinymce/plugins/mathjax/config.js')
                                    : asset('js/tinymce/plugins/mathjax/config.js')
                                }}",
                    // Add this line to prevent initialization issues
                    ignoreUnloadIfMathJaxNotReady: true
                },
                height: {{ $height ?? 300 }},
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

                    // Monkey patch the MathJax plugin methods that might cause errors
                    ed.on('init', function() {
                        if (ed.plugins.mathjax) {
                            const originalRender = ed.plugins.mathjax.originalRender || ed.plugins.mathjax.render;

                            // Override the render function with a safe version
                            ed.plugins.mathjax.render = function() {
                                try {
                                    if (typeof originalRender === 'function') {
                                        return originalRender.apply(this, arguments);
                                    }
                                } catch (e) {
                                    console.warn('MathJax render failed:', e);
                                }
                            };

                            // Store the original for reference
                            ed.plugins.mathjax.originalRender = originalRender;
                        }

                        // Force MathJax typeset after initialization
                        setTimeout(function() {
                            safeTypeset();
                        }, 1000);
                    });

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

                                    // Re-render MathJax content
                                    setTimeout(() => safeTypeset(), 100);
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

                    // When content is set, re-render MathJax
                    ed.on('SetContent', function() {
                        setTimeout(() => safeTypeset(), 100);
                    });
                }
            });
        }

        // Initialize editor with proper MathJax loading
        initTinyMCE().catch(err => {
            console.error('Error initializing TinyMCE:', err);
            // Fallback to basic initialization if there's an error
            tinymce.init({
                selector: '#{{ $name }}',
                plugins: 'lists link image table code',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table',
                height: {{ $height ?? 300 }}
            });
        });
    });
</script>
