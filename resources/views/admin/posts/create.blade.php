@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-2">Create New Post</h2>

        <p class="mb-6">
            @include('layouts.badge-primary', ['content' => $chapter->book->group->category->name])
            @include('layouts.badge-secondary', ['content' => $chapter->book->group->name])
            @include('layouts.badge-green', ['content' => $chapter->book->name])
            <span class="font-medium">{{ $chapter->name }}</span>
        </p>

        <form class="rounded-sm border bg-white shadow"  method="POST" action="{{ route('admin.bookChapters.storePost', $chapter->slug) }}">
            @csrf

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'title', 'label' => __('Post title'), 'value' => '', 'required' => true])

                <!-- Content Field -->
                <div>
                    <label for="message" class="mb-3 block text-sm font-medium text-[#1c2434]">{{ __('Content') }}</label>
                    <textarea name="message" id="message"
                              class="w-full rounded-lg border-[1.5px] border-primary bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" rows="6">
                    </textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ __('Create Post') }}
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
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table',
            height: 300,
            license_key: 'gpl'
        });
    </script>
@endsection
