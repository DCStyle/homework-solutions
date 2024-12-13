@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-2">
            {{ isset($article) ? 'Sửa bài viết' : 'Thêm bài viết' }}
        </h2>

        @if(isset($article))
            <p class="mb-6">
                <span class="font-medium">{{ $article->name }}</span>
            </p>
        @endif

        <form
            class="rounded-sm border bg-white shadow"
            method="POST"
            action="{{ isset($article)
                ? route('admin.articles.update', $article->id)
                : route('admin.articles.store') }}"
        >
            @csrf
            @if(isset($article))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'title', 'label' => 'Tiêu đề', 'value' => old('name', $article->title ?? ''), 'required' => true])

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => 'Đường dẫn', 'value' => old('slug', $article->slug ?? '')])

                <!-- Content Field -->
                <div>
                    <label for="message" class="mb-3 block text-sm font-medium text-[#1c2434]">Nội dung</label>
                    <x-form.editor :name="'content'" value="{{ old('content', $article->content ?? '') }}" />

                    <input type="hidden" name="uploaded_image_ids" id="uploaded_image_ids" value="{{ isset($article) ? json_encode($article->images->pluck('id')) : '[]' }}">
                </div>

                <!-- Category Field -->
                <div>
                    <label for="article_category_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Danh mục</label>
                    <select name="article_category_id" id="article_category_id" data-plugin-select2
                            class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                    >
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('article_category_id', $article->article_category_id ?? 0) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tag Field -->
                <div>
                    <label for="tags" class="mb-3 block text-sm font-medium text-[#1c2434]">Tags</label>
                    <select name="tags[]"
                            id="tags"
                            multiple
                            class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary">
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}"
                                {{ isset($article) && $article->tags->contains($tag->id) ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ isset($article) ? 'Cập nhật bài viết' : 'Thêm bài viết' }}
                </button>
            </div>
        </form>
    </div>


@endsection
