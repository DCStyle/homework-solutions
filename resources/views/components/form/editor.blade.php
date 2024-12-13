<textarea name="{{ $name }}" id="{{ $name }}"
          class="w-full rounded-lg border-[1.5px] border-primary bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" rows="6">
    {!! $value ?? '' !!}
</textarea>

<!-- Include TinyMCE from the public folder -->
<script src="{{ asset('js/image-upload.js') }}"></script>
<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
<script>
    tinymce.init({
        selector: '#{{ $name }}',
        plugins: 'lists link image table',
        smart_paste: true,
        external_plugins: {
            'mathjax': "{{ asset('js/tinymce/plugins/mathjax/plugin.min.js') }}"
        },
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table mathjax',
        mathjax: {
            lib: 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js',
            configUrl: "{{ asset('js/tinymce/plugins/mathjax/config.js') }}"
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
    });
</script>
