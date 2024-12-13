@extends('admin_layouts.admin')

@section('content')
    <div>
        <h1 class="text-3xl font-bold mb-6">
            {{ isset($category) ? 'Cập nhật danh mục' : 'Thêm danh mục' }}
        </h1>

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

                <!-- Category Description -->
                <div>
                    <label for="description" class="mb-3 block text-sm font-medium text-[#1c2434]">Mô tả</label>
                    <textarea name="description" id="description"
                              class="w-full rounded-lg border-[1.5px] border-primary bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" rows="6">
                        {{ old('description', $category->description ?? '') }}
                    </textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="flex w-full justify-center rounded bg-primary p-3 font-medium text-white hover:bg-opacity-90">
                    {{ isset($category) ? 'Cập nhật' : 'Thêm mới' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Include TinyMCE from the public folder -->
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        tinymce.init({
            selector: '#description',
            plugins: 'lists link image table',
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table',
            height: 300,
            license_key: 'gpl'
        });
    </script>
@endsection
