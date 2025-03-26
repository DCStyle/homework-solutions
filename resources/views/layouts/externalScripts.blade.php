<script src="//code.iconify.design/1/1.0.6/iconify.min.js"></script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="{{ asset('js/jquery.relativeTime.min.js') }}"></script>

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
    MathJax = {
        tex: {
            inlineMath: [['$', '$'], ['\\(', '\\)']],
            displayMath: [['$$', '$$'], ['\\[', '\\]']]
        }
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" async></script>
