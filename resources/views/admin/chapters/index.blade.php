@extends('admin_layouts.admin')

@section('content')
    <div>
        @if (session('success'))
            @include('layouts.message-success', ['message' => session('success')])
        @endif

        <h1 class="text-3xl font-bold mb-2">Danh sách chương</h1>

        <p class="mb-6">
            @include('layouts.badge-primary', ['content' => $book->group->category->name])
            @include('layouts.badge-secondary', ['content' => $book->group->name])
            <span class="font-medium">{{ $book->name }}</span>
        </p>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Tên</th>
                <th class="text-center">Bài viết</th>
                <th class="text-center">Hành động</th>
            </tr>
            </thead>
            <tbody>
            @if(count($chapters) > 0)
                @foreach($chapters as $chapter)
                    <tr>
                        <td>{{ $chapter->name }}</td>
                        <td class="text-center">
                            {{ $chapter->posts->count() }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.bookChapters.posts', $chapter->id) }}" class="btn btn-info btn-sm">Xem bài viết</a>

                            <a href="{{ route('admin.bookChapters.edit', $chapter->id) }}" class="btn btn-warning btn-sm">Sửa</a>

                            <form action="{{ route('admin.bookChapters.destroy', $chapter->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xoá?')">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">Hiện chưa có chương nào</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    @include('layouts.floating-button-right', ['link' => route('admin.books.createChapter', $book->id)])
@endsection
