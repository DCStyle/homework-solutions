@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-4">{{ isset($book) ? 'Cập nhật thông tin sách' : 'Tạo sách mới' }}</h2>

        @isset($book)
            <div class="mb-6 flex items-center space-x-2 justify-end">
                <a href="{{ route('admin.books.chapters', $book->id) }}" class="btn btn-info btn-sm">
                    Xem chương
                </a>

                <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xoá?')">Xoá</button>
                </form>
            </div>
        @endisset

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
                    <textarea name="description" id="description"
                              class="w-full rounded-lg border-[1.5px] border-primary bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter" rows="6">
                        {{ old('description', $book->description ?? '') }}
                    </textarea>
                </div>

                <!-- Book Group Dropdown -->
                <div>
                    <label for="group_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Môn học</label>
                    <select name="group_id" id="group_id" data-plugin-select2
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
