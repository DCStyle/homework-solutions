@extends('admin_layouts.admin')

@section('content')
    <div>
        <div class="flex flex-wrap items-center justify-between mb-4">
            <h2 class="text-3xl font-bold mb-2">
                {{ isset($book) ? 'Cập nhật thông tin sách' : 'Thêm sách mới' }}
            </h2>

            @if(isset($book))
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <a href="{{ route('books.show', $book->slug) }}"
                       class="px-4 py-2 rounded bg-primary text-white hover:!bg-blue-600"
                       target="_blank"
                    >
                        Xem sách
                    </a>

                    <a href="{{ route('admin.books.chapters', $book->id) }}"
                       class="px-4 py-2 rounded bg-orange-400 text-white hover:!bg-orange-500"
                       target="_blank"
                    >
                        Danh sách chương
                    </a>

                    <form method="POST" action="{{ route('admin.books.destroy', $book->id) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa sách này không?')"
                        >
                            Xóa
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if(isset($book))
            <p class="mb-6">
                @include('layouts.badge-primary', ['content' => "<a href='" . route('admin.categories.edit', $book->group->category->id) . "' data-bs-toggle='tooltip' data-bs-placement='top' title='Chỉnh sửa' target='_blank'>" . $book->group->category->name  . "</a>"])
                <span class="font-medium">
                <a href="{{ route('admin.bookGroups.edit', $book->group->id) }}"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top"
                   title="Chỉnh sửa"
                   target="_blank"
                   class="hover:underline"
                >
                    {{ $book->group->name }}
                </a>
            </span>
            </p>
        @endif

        <form class="rounded-sm border bg-white shadow"
              method="POST"
              action="{{ isset($book) ? route('admin.books.update', $book->id) : route('admin.books.store') }}"
        >
            @csrf
            @if(isset($book))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'name', 'label' => 'Tên sách', 'value' => old('name', $book->name ?? ''), 'required' => true])

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => 'Đường dẫn', 'value' => old('slug', $book->slug ?? '')])

                <!-- Description Field -->
                <div>
                    <label for="description" class="mb-3 block text-sm font-medium text-[#1c2434]">Mô tả</label>
                    <x-form.editor :name="'description'" value="{{ old('description', $book->description ?? '') }}" />
                </div>

                <!-- Book Group Dropdown -->
                <div>
                    <label for="book_group_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Môn học</label>
                    <select name="book_group_id" id="group_id" data-plugin-select2
                            class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                    >
                        <?php $currentCategory = null ?>

                        @foreach($groups as $group)
                            @if($currentCategory == null || $group->category->id !== $currentCategory->id)
                                <?php $currentCategory = $group->category ?>
                                <optgroup label="{{ $currentCategory->name }}">
                            @endif

                            <option value="{{ $group->id }}" {{ $group->id == old('group_id', $book->book_group_id ?? 0) ? 'selected' : '' }}>{{ $group->name }}</option>

                            @if($currentCategory == null || $group->category->id !== $currentCategory->id)
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ isset($book) ? 'Cập nhật' : 'Thêm' }}
                </button>
            </div>
        </form>
    </div>
@endsection
