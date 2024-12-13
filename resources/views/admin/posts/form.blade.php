@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-2">
            {{ isset($post) ? 'Sửa bài viết' : 'Tạo bài viết' }}
        </h2>

        <p class="mb-6">
            @include('layouts.badge-primary', ['content' => $chapter->book->group->category->name])
            @include('layouts.badge-secondary', ['content' => $chapter->book->group->name])
            @include('layouts.badge-green', ['content' => $chapter->book->name])
            <span class="font-medium">{{ $chapter->name }}</span>
        </p>

        <form class="rounded-sm border bg-white shadow"
              method="POST"
              action="{{ isset($post)
                ? route('admin.posts.update', $post->id)
                : route('admin.bookChapters.storePost', $chapter->id)
              }}"
        >
            @csrf
            @if(isset($post))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'title', 'label' => 'Tiêu đề', 'value' => old('title', $post->title ?? ''), 'required' => true])

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => 'Đường dẫn', 'value' => old('slug', $post->slug ?? '')])

                <!-- Content Field -->
                <div>
                    <label for="message" class="mb-3 block text-sm font-medium text-[#1c2434]">Nội dung</label>
                    <x-form.editor :name="'message'" value="{{ old('message', $post->content ?? '') }}" />

                    <input type="hidden" name="uploaded_image_ids" id="uploaded_image_ids" value="{{ isset($post) ? json_encode($post->images->pluck('id')) : '[]' }}">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ isset($post) ? 'Cập nhật' : 'Tạo' }}
                </button>
            </div>
        </form>
    </div>
@endsection
