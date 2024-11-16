@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-2">Edit Post</h2>

        <p class="mb-6">
            @include('layouts.badge-primary', ['content' => $chapter->book->group->category->name])
            @include('layouts.badge-secondary', ['content' => $chapter->book->group->name])
            @include('layouts.badge-green', ['content' => $chapter->book->name])
            <span class="font-medium">{{ $chapter->name }}</span>
        </p>

        <form class="rounded-sm border bg-white shadow"  method="POST" action="{{ route('admin.posts.update', $post->slug) }}">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'title', 'label' => __('Post title'), 'value' => $post->title, 'required' => true])

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => __('Post slug'), 'value' => $post->slug, 'required' => true])

                <!-- Content Field -->
                <div>
                    <label for="message" class="mb-3 block text-sm font-medium text-[#1c2434]">Content</label>
                    <textarea name="message" id="message"
                              class="w-full rounded-lg border-[1.5px] border-primary bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" rows="6">
                        {{ $post->content }}
                    </textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ __('Update post') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Include TinyMCE from the public folder -->
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        tinymce.init({
            selector: '#message',
            plugins: 'lists link image table',
            external_plugins: {'mathjax': "{{ asset('js/tinymce/plugins/mathjax/plugin.min.js') }}"},
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table mathjax',
            mathjax: {
                lib: 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js', //required path to mathjax
                //symbols: {start: '\\(', end: '\\)'}, //optional: mathjax symbols
                //className: "math-tex", //optional: mathjax element class
                configUrl: "{{ asset('js/tinymce/plugins/mathjax/config.js') }}" //optional: mathjax config js
            },
            height: 300,
            license_key: 'gpl'
        });
    </script>
@endsection
