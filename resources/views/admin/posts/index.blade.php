@extends('admin_layouts.admin')

@section('content')
    <div>
        @if (session('success'))
            @include('layouts.message-success', ['message' => session('success')])
        @endif

        <h1 class="text-3xl font-bold mb-2">Danh sách bài viết</h1>

        <p class="mb-2">
            @include('layouts.badge-primary', ['content' => $chapter->book->group->category->name])
            @include('layouts.badge-secondary', ['content' => $chapter->book->group->name])
            @include('layouts.badge-green', ['content' => $chapter->book->name])
            <span class="font-medium">{{ $chapter->name }}</span>
        </p>

        <div class="mb-4">
            <div class="max-w-xl w-full">
                <x-search-bar
                    model="Post"
                    route-name="admin.posts"
                    :search-fields="['title', 'content']"
                    placeholder="Nhập tên bài viết..."
                    :is-admin="true"
                />
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Tên</th>
                <th>Ngày cập nhật cuối</th>
                <th class="text-center">Hành động</th>
            </tr>
            </thead>
            <tbody>
            @if(count($posts) > 0)
                @foreach($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td>{!! format_time($post->updated_at) !!}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-warning btn-sm">Sửa</a>

                            <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xoá?')">
                                    Xoá
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">Hiện chưa có bài viết nào</td>
                </tr>
            @endif
            </tbody>
        </table>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    </div>

    @include('layouts.floating-button-right', ['link' => route('admin.bookChapters.createPost', $chapter->id)])
@endsection
