@extends('admin_layouts.admin')

@section('content')
    <div>
        <div class="flex flex-wrap items-center justify-between mb-4">
            <h2 class="text-3xl font-bold mb-2">
                {{ isset($category) ? 'Cập nhật danh mục' : 'Thêm danh mục mới' }}
            </h2>

            @if(isset($category))
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <a href="{{ route('categories.show', $category->slug) }}"
                       class="px-4 py-2 rounded bg-primary text-white hover:!bg-blue-600"
                       target="_blank"
                    >
                        Xem danh mục
                    </a>

                    <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này không?')"
                        >
                            Xóa
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <form
            class="rounded-sm border bg-white shadow"
            method="post"
            action="{{ isset($category)
                ? route('admin.categories.update', $category->id)
                : route('admin.categories.store')
            }}"
        >
            @csrf
            @if(isset($category))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <!-- Category Name -->
                @include('layouts.form-input', ['name' => 'name', 'label' => "Tên danh mục", 'value' => old('name', $category->name ?? '')])

                <!-- Category Slug -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => 'Đường dẫn', 'value' => old('slug', $category->slug ?? '')])

                <!-- Description Field -->
                <div>
                    <label for="description" class="mb-3 block text-sm font-medium text-[#1c2434]">Mô tả</label>
                    <x-form.editor :name="'description'" value="{{ old('description', $category->description ?? '') }}" />
                </div>

                <!-- Submit Button -->
                <button type="submit" class="flex w-full justify-center rounded bg-primary p-3 font-medium text-white hover:bg-opacity-90">
                    {{ isset($category) ? 'Cập nhật' : 'Thêm mới' }}
                </button>
            </div>
        </form>
    </div>
@endsection
