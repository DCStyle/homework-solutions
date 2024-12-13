@extends('admin_layouts.admin')

@section('content')
    <div>
        <h1 class="text-3xl font-bold mb-6">
            {{ isset($group) ? "Cập nhật môn học" : "Thêm môn học" }}
        </h1>

        <form
            class="rounded-sm border bg-white shadow"
            method="POST"
            action="{{ isset($group)
                ? route('admin.bookGroups.update', $group->id)
                : route('admin.bookGroups.store')
            }}"
        >
            @csrf
            @if(isset($group))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'name', 'label' => "Tên môn học", 'value' => old('name', $group->name ?? ''), 'required' => true])

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => "Đường dẫn", 'value' => old('slug', $group->slug ?? '')])

                <!-- Description Field -->
                <div>
                    <label for="description" class="mb-3 block text-sm font-medium text-[#1c2434]">Mô tả</label>
                    <textarea name="description" id="description"
                              class="w-full rounded-lg border-[1.5px] border-primary bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" rows="6">
                        {{ old('description', $group->description ?? '') }}
                    </textarea>
                </div>

                <!-- Category Dropdown -->
                <div>
                    <label for="category_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Danh mục</label>
                    <select name="category_id" id="category_id" data-plugin-select2
                            class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                    >
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id', $group->category_id ?? '') == $category->id ? 'selected' : '' }}
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ isset($group) ? "Cập nhật" : "Thêm" }}
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
