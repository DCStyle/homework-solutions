@extends('admin_layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">
                        Xem chi tiết thành viên
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/20 py-2.5 px-4 text-center font-medium text-white hover:bg-white/30 transition-all duration-200">
                        <span class="iconify" data-icon="mdi-arrow-left"></span>
                        Quay Lại Danh Sách
                    </a>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 h-64 w-64 rounded-full bg-white/5"></div>
        </div>

        <!-- User Header Card -->
        <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
            <div class="px-4 py-5 sm:px-6 flex flex-col md:flex-row md:items-center justify-between relative border-b border-gray-200">
                <div class="flex items-center">
                    <!-- User Avatar -->
                    <div class="mr-4 flex-shrink-0">
                        @if($user->avatar)
                            <img class="h-16 w-16 rounded-full object-cover border-2 border-primary-light" src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}">
                        @else
                            <div class="h-16 w-16 rounded-full flex items-center justify-center bg-gradient-to-br from-primary to-primary-light text-white text-3xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <!-- User Basic Info -->
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 leading-7 sm:truncate">
                            {{ $user->name }}
                        </h1>
                        <div class="mt-1 flex items-center">
                            <span class="iconify mr-2 text-gray-500" data-icon="mdi-email"></span>
                            <a href="mailto:{{ $user->email }}" class="text-gray-500 hover:text-primary">{{ $user->email }}</a>

                            @if($user->email_verified_at)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="iconify mr-1" data-icon="mdi-check-circle"></span>
                                Đã xác thực
                            </span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                <span class="iconify mr-1" data-icon="mdi-alert-circle-outline"></span>
                                Chưa xác thực
                            </span>
                            @endif
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $user->user_type == 'student' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $user->user_type == 'teacher' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $user->user_type == 'parent' ? 'bg-amber-100 text-amber-800' : '' }}
                        ">
                            <span class="w-1.5 h-1.5 rounded-full mr-1
                                {{ $user->user_type == 'student' ? 'bg-green-400' : '' }}
                                {{ $user->user_type == 'teacher' ? 'bg-blue-400' : '' }}
                                {{ $user->user_type == 'parent' ? 'bg-amber-400' : '' }}
                            "></span>
                            {{ $user->user_type == 'student' ? 'Học Sinh' : '' }}
                            {{ $user->user_type == 'teacher' ? 'Giáo Viên' : '' }}
                            {{ $user->user_type == 'parent' ? 'Phụ Huynh' : '' }}
                        </span>

                            @foreach($user->roles as $role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-light text-primary">
                                <span class="iconify mr-1" data-icon="mdi-shield-account"></span>
                                {{ $role->name }}
                            </span>
                            @endforeach

                            @if($user->roles->isEmpty())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Người Dùng Cơ Bản
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 md:mt-0 space-x-3 flex flex-wrap">
                    <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150">
                        <span class="iconify mr-2" data-icon="mdi-pencil"></span>
                        Chỉnh Sửa
                    </a>

                    @if(auth()->id() !== $user->id)
                        <button type="button" id="deleteUserBtn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150">
                            <span class="iconify mr-2" data-icon="mdi-delete"></span>
                            Xóa Người Dùng
                        </button>

                        @php
                            $adminRole = \App\Models\Role::where('name', 'Administrator')->first();
                        @endphp

                        @if($adminRole)
                            <form action="{{ route('admin.users.toggle_admin', $user) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $user->isAdmin() ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $user->isAdmin() ? 'orange' : 'green' }}-500 transition duration-150"
                                >
                                    <span class="iconify mr-2" data-icon="{{ $user->isAdmin() ? 'mdi-shield-off' : 'mdi-shield' }}"></span>
                                    {{ $user->isAdmin() ? 'Hủy Quyền Admin' : 'Cấp Quyền Admin' }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="md:col-span-1 space-y-6">
                <!-- Account Information Card -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                            <span class="iconify text-primary mr-2" data-icon="mdi-account-details"></span>
                            Thông Tin Tài Khoản
                        </h3>
                    </div>
                    <div class="bg-white px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-identifier"></span>
                                    ID Người Dùng
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    #{{ $user->id }}
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-calendar"></span>
                                    Tham Gia
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $user->created_at->format('d/m/Y') }}
                                    <span class="text-xs text-gray-500 block">{{ $user->created_at->diffForHumans() }}</span>
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-update"></span>
                                    Cập Nhật Lần Cuối
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $user->updated_at->format('d/m/Y') }}
                                    <span class="text-xs text-gray-500 block">{{ $user->updated_at->diffForHumans() }}</span>
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-check-decagram"></span>
                                    Trạng Thái Xác Thực
                                </dt>
                                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="iconify mr-1" data-icon="mdi-check-circle"></span>
                                        Đã xác thực vào {{ $user->email_verified_at->format('d/m/Y') }}
                                    </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        <span class="iconify mr-1" data-icon="mdi-alert-circle-outline"></span>
                                        Chưa xác thực
                                    </span>
                                    @endif
                                </dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-login"></span>
                                    Đăng Nhập Gần Đây
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <!-- Placeholder data -->
                                    {{ \Carbon\Carbon::now()->subDays(rand(0, 10))->format('d/m/Y - H:i') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Roles & Permissions Card -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                            <span class="iconify text-primary mr-2" data-icon="mdi-shield"></span>
                            Vai Trò & Quyền Hạn
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-base font-medium text-gray-900 mb-3">Vai Trò Đã Gán</h4>
                        <div class="space-y-3 mb-6">
                            @forelse($user->roles as $role)
                                <div class="p-3 rounded-lg bg-primary-light border border-primary-lighter">
                                    <div class="flex items-center">
                                        <div class="p-2 rounded-full bg-primary-lighter text-primary mr-3">
                                            <span class="iconify" data-icon="mdi-shield-account"></span>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-gray-900">{{ $role->name }}</h5>
                                            <p class="text-xs text-gray-500">{{ $role->permissions->count() }} quyền hạn</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100 text-gray-500 text-sm">
                                    Người dùng này chưa được gán vai trò nào.
                                </div>
                            @endforelse
                        </div>

                        <h4 class="text-base font-medium text-gray-900 mb-3">Quyền Hạn</h4>
                        <div class="space-y-2">
                            @php
                                $permissions = [];
                                foreach ($user->roles as $role) {
                                    foreach ($role->permissions as $permission) {
                                        $permissions[$permission->name] = true;
                                    }
                                }
                            @endphp

                            @if(count($permissions) > 0)
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach($permissions as $name => $value)
                                        <div class="flex items-center p-2 rounded-md bg-green-50 border border-green-100">
                                            <span class="iconify text-green-500 mr-2" data-icon="mdi-check-circle"></span>
                                            <span class="text-sm text-gray-800">{{ $name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100 text-gray-500 text-sm">
                                    Người dùng này không có quyền hạn cụ thể nào.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="md:col-span-2 space-y-6">
                <!-- Activity Stats Card -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                            <span class="iconify text-primary mr-2" data-icon="mdi-chart-timeline-variant"></span>
                            Tổng Quan Hoạt Động
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Login Count Card -->
                            <div class="bg-gradient-to-br from-primary-lighter to-blue-50 overflow-hidden rounded-lg shadow-sm border border-primary-lighter">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-primary rounded-md p-3">
                                            <span class="iconify text-white text-xl" data-icon="mdi-login-variant"></span>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">
                                                    Tổng Số Đăng Nhập
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-bold text-gray-900">
                                                        {{ rand(20, 100) }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-primary-lighter px-5 py-3">
                                    <div class="text-sm">
                                        <span class="font-medium text-primary hover:text-primary-dark cursor-pointer">Đăng nhập gần đây: {{ \Carbon\Carbon::now()->subDays(rand(0, 5))->format('d/m, H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Creation Card -->
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 overflow-hidden rounded-lg shadow-sm border border-green-100">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                            <span class="iconify text-white text-xl" data-icon="mdi-file-document-edit"></span>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">
                                                    Nội Dung Đã Tạo
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-bold text-gray-900">
                                                        {{ rand(5, 30) }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-green-50 px-5 py-3">
                                    <div class="text-sm">
                                        <span class="font-medium text-green-600 hover:text-green-700 cursor-pointer">Xem nội dung</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Comments Card -->
                            <div class="bg-gradient-to-br from-purple-50 to-violet-50 overflow-hidden rounded-lg shadow-sm border border-purple-100">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                            <span class="iconify text-white text-xl" data-icon="mdi-comment-text-multiple"></span>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 truncate">
                                                    Bình Luận
                                                </dt>
                                                <dd>
                                                    <div class="text-lg font-bold text-gray-900">
                                                        {{ rand(0, 50) }}
                                                    </div>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-purple-50 px-5 py-3">
                                    <div class="text-sm">
                                        <span class="font-medium text-purple-600 hover:text-purple-700 cursor-pointer">Xem bình luận</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Timeline -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                            <span class="iconify text-primary mr-2" data-icon="mdi-history"></span>
                            Hoạt Động Gần Đây
                        </h3>
                    </div>
                    <div class="bg-white overflow-hidden">
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                <!-- Sample activity items - replace with real data -->
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex items-start space-x-3">
                                            <div class="relative">
                                                <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center ring-8 ring-white">
                                                    <span class="iconify text-white" data-icon="mdi-login"></span>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div>
                                                    <div class="text-sm">
                                                        <span class="font-medium text-gray-900">Đăng nhập</span>
                                                    </div>
                                                    <p class="mt-0.5 text-sm text-gray-500">
                                                        {{ \Carbon\Carbon::now()->subDays(1)->format('d/m/Y') }} lúc {{ \Carbon\Carbon::now()->subDays(1)->format('H:i') }}
                                                    </p>
                                                </div>
                                                <div class="mt-2 text-sm text-gray-700">
                                                    <p>
                                                        Đăng nhập từ <span class="font-medium">192.168.1.1</span> bằng <span class="font-medium">Chrome trên Windows</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex items-start space-x-3">
                                            <div class="relative">
                                                <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                    <span class="iconify text-white" data-icon="mdi-pencil"></span>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div>
                                                    <div class="text-sm">
                                                        <span class="font-medium text-gray-900">Cập nhật hồ sơ</span>
                                                    </div>
                                                    <p class="mt-0.5 text-sm text-gray-500">
                                                        {{ \Carbon\Carbon::now()->subDays(5)->format('d/m/Y') }} lúc {{ \Carbon\Carbon::now()->subDays(5)->format('H:i') }}
                                                    </p>
                                                </div>
                                                <div class="mt-2 text-sm text-gray-700">
                                                    <p>
                                                        Thay đổi thông tin hồ sơ và thêm ảnh đại diện
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex items-start space-x-3">
                                            <div class="relative">
                                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <span class="iconify text-white" data-icon="mdi-account-plus"></span>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div>
                                                    <div class="text-sm">
                                                        <span class="font-medium text-gray-900">Tạo tài khoản</span>
                                                    </div>
                                                    <p class="mt-0.5 text-sm text-gray-500">
                                                        {{ $user->created_at->format('d/m/Y') }} lúc {{ $user->created_at->format('H:i') }}
                                                    </p>
                                                </div>
                                                <div class="mt-2 text-sm text-gray-700">
                                                    <p>
                                                        Người dùng đăng ký với email {{ $user->email }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-center">
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150">
                                    <span class="iconify mr-2" data-icon="mdi-history"></span>
                                    Xem Toàn Bộ Lịch Sử
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal - Hidden by default -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="iconify text-red-600 text-xl" data-icon="mdi-alert"></span>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Xóa Người Dùng
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Bạn có chắc chắn muốn xóa <span class="font-medium text-gray-700">{{ $user->name }}</span>? Hành động này không thể hoàn tác và tất cả dữ liệu liên quan đến người dùng này sẽ bị xóa vĩnh viễn.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Xóa
                        </button>
                    </form>
                    <button type="button" id="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification System -->
    <div id="toast-container" class="fixed bottom-0 right-0 p-4 space-y-3 z-50"></div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Show toast notification
            function showToast(message, type = 'success') {
                const id = 'toast-' + Date.now();
                const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
                const icon = type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle';

                const toast = `
            <div id="${id}" class="${bgColor} text-white rounded-lg shadow-lg p-4 mb-3 flex items-center transform transition-all duration-300 translate-x-full opacity-0">
                <span class="iconify mr-2 text-xl" data-icon="${icon}"></span>
                <div class="flex-1">${message}</div>
                <button class="ml-4 text-white focus:outline-none hover:text-gray-100 toast-close">
                    <span class="iconify" data-icon="mdi-close"></span>
                </button>
            </div>
        `;

                $('#toast-container').append(toast);

                // Animate in
                setTimeout(() => {
                    $(`#${id}`).removeClass('translate-x-full opacity-0');
                }, 10);

                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    dismissToast(id);
                }, 5000);

                // Close button event
                $(`#${id} .toast-close`).on('click', function() {
                    dismissToast(id);
                });
            }

            function dismissToast(id) {
                $(`#${id}`).addClass('translate-x-full opacity-0');
                setTimeout(() => {
                    $(`#${id}`).remove();
                }, 300);
            }

            // Session flash messages
            @if(session('success'))
            showToast("{{ session('success') }}", 'success');
            @endif

            @if(session('error'))
            showToast("{{ session('error') }}", 'error');
            @endif

            // Delete user modal
            $('#deleteUserBtn').on('click', function() {
                $('#deleteModal').removeClass('hidden');
            });

            // Hide delete confirmation modal
            $('#cancelDelete').on('click', function() {
                $('#deleteModal').addClass('hidden');
            });

            // Close modal when clicking background
            $('#deleteModal').on('click', function(e) {
                if ($(e.target).hasClass('fixed')) {
                    $('#deleteModal').addClass('hidden');
                }
            });

            // Add hover effects to stats cards
            $('.bg-gradient-to-br').hover(
                function() { $(this).addClass('transform scale-105 shadow-md').css('transition', 'all 0.3s'); },
                function() { $(this).removeClass('transform scale-105 shadow-md'); }
            );
        });
    </script>
@endpush
