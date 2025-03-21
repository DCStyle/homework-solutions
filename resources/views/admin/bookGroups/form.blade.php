@extends('admin_layouts.admin')

@section('content')
    <div>
        <div class="flex flex-wrap items-center justify-between mb-4">
            <h2 class="text-3xl font-bold mb-2">
                {{ isset($group) ? 'Cập nhật môn học' : 'Thêm môn học mới' }}
            </h2>

            @if(isset($group))
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <a href="{{ route('bookGroups.show', $group->slug) }}"
                       class="px-4 py-2 rounded bg-primary text-white hover:!bg-blue-600"
                       target="_blank"
                    >
                        Xem môn học
                    </a>

                    <form method="POST" action="{{ route('admin.bookGroups.destroy', $group->id) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa môn học này không?')"
                        >
                            Xóa
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if(isset($group))
            <p class="mb-6">
                @include('layouts.badge-primary', ['content' => "<a href='" . route('admin.categories.edit', $group->category->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank'>" . $group->category->name  . "</a>"])
            </p>
        @endif

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
                    <x-form.editor :name="'description'" value="{{ old('description', $group->description ?? '') }}" />
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
@endsection
