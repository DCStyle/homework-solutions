@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-2">
            {{ isset($chapter) ? 'Cập nhật chương sách' : 'Thêm chương mới' }}
        </h2>

        <p class="mb-6">
            @include('layouts.badge-primary', ['content' => $book->group->category->name])
            @include('layouts.badge-secondary', ['content' => $book->group->name])
            <span class="font-medium">{{ $book->name }}</span>
        </p>

        <form class="rounded-sm border bg-white shadow"
              method="POST"
              action="{{ isset($chapter)
                    ? route('admin.bookChapters.update', $chapter->id)
                    : route('admin.books.storeChapter', $book->id)
              }}"
        >
            @csrf
            @if(isset($chapter))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'name', 'label' => 'Tên chương', 'value' => old('name', $chapter->name ?? ''), 'required' => true])

                <!-- Description Field -->
                <div>
                    <label for="description" class="mb-3 block text-sm font-medium text-[#1c2434]">Nội dung</label>
                    <x-form.editor :name="'description'" value="{{ old('description', $chapter->description ?? '') }}" />

                    <input type="hidden" name="uploaded_image_ids" id="uploaded_image_ids" value="{{ isset($chapter) ? json_encode($chapter->images->pluck('id')) : '[]' }}">
                </div>

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => 'Đường dẫn', 'value' => old('slug', $chapter->slug ?? '')])

                <!-- Book Field -->
                <div>
                    <label for="book_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Sách</label>
                    <select name="book_id" id="book_id" data-plugin-select2
                            class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                    >
                        <?php $currentCategory = null ?>

                        @foreach($books as $book)
                            @if($currentCategory == null || $book->group->category->id !== $currentCategory->id)
                                    <?php $currentCategory = $book->group->category ?>
                                <optgroup label="{{ $currentCategory->name }}">
                                    @endif

                                    <option value="{{ $book->id }}" {{ $book->id == old('book_id', $chapter->book_id ?? 0) ? 'selected' : '' }}>{{ $book->name }}</option>

                                    @if($currentCategory == null || $book->group->category->id !== $currentCategory->id)
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>



                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ isset($chapter) ? 'Cập nhật' : 'Thêm' }}
                </button>
            </div>
        </form>
    </div>
@endsection
