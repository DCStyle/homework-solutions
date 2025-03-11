@extends('admin_layouts.admin')

@section('content')
    <div>
        <h1 class="text-3xl font-bold mb-6">{{ isset($category) ? 'Cập nhật danh mục' : 'Thêm danh mục' }}</h1>

        <form
            class="rounded-sm border bg-white shadow"
            action="{{ isset($category)
                ? route('admin.articleCategories.update', $category->id)
                : route('admin.articleCategories.store') }}"
            method="POST"
        >
            @csrf
            @if(isset($category))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <div class="flex flex-col gap-4 p-6">
                    <!-- Category Name -->
                    @include('layouts.form-input', ['name' => 'name', 'label' => 'Tên danh mục', 'value' => old('name', $category->name ?? ''), 'required' => true])

                    <!-- Slug Field -->
                    @include('layouts.form-input', ['name' => 'slug', 'label' => 'Đường dẫn', 'value' => old('slug', $category->slug ?? '')])

                    <!-- Description Field -->
                    <div>
                        <label for="description" class="mb-3 block text-sm font-medium text-[#1c2434]">Nội dung</label>
                        <x-form.editor :name="'description'" value="{{ old('description', $category->description ?? '') }}" />

                        <input type="hidden" name="uploaded_image_ids" id="uploaded_image_ids" value="{{ isset($category) ? json_encode($category->images->pluck('id')) : '[]' }}">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="flex w-full justify-center rounded bg-primary p-3 font-medium text-white hover:bg-opacity-90">
                        {{ isset($category) ? 'Cập nhật' : 'Thêm' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
