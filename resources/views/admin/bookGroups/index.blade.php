@extends('admin_layouts.admin')

@section('content')
    <div>
        @if (session('success'))
            @include('layouts.message-success', ['message' => session('success')])
        @endif

        <h1 class="text-3xl font-bold mb-4">Danh sách môn học</h1>

        <div class="mb-6 flex justify-end">
            <div class="max-w-xl w-full">
                <x-search-bar
                    model="BookGroup"
                    route-name="admin.bookGroups"
                    :search-fields="['name', 'description']"
                    placeholder="Nhập tên môn học..."
                    :is-admin="true"
                />
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Tên</th>
                <th>Mô tả</th>
                <th>Hành động</th>
            </tr>
            </thead>
            <tbody>
            @if(count($groups))
                <?php $currentCategory = null ?>

                @foreach($groups as $group)
                    @if($currentCategory == null || $group->category->id !== $currentCategory->id)
                        <?php $currentCategory = $group->category ?>

                        <tr>
                            <td colspan="4">
                                <b class="text-primary">{{ $currentCategory->name }}</b>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td>{{ $group->name }}</td>
                        <td>@include('layouts.string-snippet', ['string' => $group->description, 'snippet' => 100])</td>
                        <td>
                            <a href="{{ route('admin.bookGroups.edit', $group->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                            <form action="{{ route('admin.bookGroups.destroy', $group->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xoá?')">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">Hiện chưa có môn học nào</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    {{ $groups->links() }}

    @include('layouts.floating-button-right', ['link' => route('admin.bookGroups.create')])
@endsection
