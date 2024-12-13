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

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => 'Đường dẫn', 'value' => old('slug', $chapter->slug ?? '')])

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ isset($chapter) ? 'Cập nhật' : 'Thêm' }}
                </button>
            </div>
        </form>
    </div>
@endsection
