@extends('admin_layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">
                        {{ isset($user) ? 'Chỉnh Sửa: ' . $user->name : 'Tạo Người Dùng Mới' }}
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

        @php
            $routeName = isset($user) ? 'admin.users.update' : 'admin.users.store';
            $routeParams = isset($user) ? ['user' => $user->id] : [];
            $adminRole = $roles->where('name', 'Administrator')->first();
            $currentUserIsAdmin = isset($user) && $user->id === auth()->id() && $user->isAdmin();
        @endphp

        <form action="{{ route($routeName, $routeParams) }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="user-form">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <!-- Basic Information Section -->
            <div class="bg-white shadow overflow-hidden rounded-lg" id="basic-info">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                        <span class="iconify text-primary mr-2" data-icon="mdi-account"></span>
                        Thông Tin Cơ Bản
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Thông tin chi tiết và liên hệ của người dùng
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Name -->
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Họ Tên <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="iconify text-gray-400" data-icon="mdi-account"></span>
                                </div>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name', isset($user) ? $user->name : '') }}"
                                    class="focus:ring-primary focus:border-primary block w-full pl-10 sm:text-sm border-gray-300 rounded-md @error('name') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="Nguyễn Văn A"
                                    required
                                >
                                @error('name')
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="iconify text-red-500" data-icon="mdi-alert-circle"></span>
                                </div>
                                @enderror
                            </div>
                            @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="sm:col-span-3">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Địa Chỉ Email <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="iconify text-gray-400" data-icon="mdi-email"></span>
                                </div>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email', isset($user) ? $user->email : '') }}"
                                    class="focus:ring-primary focus:border-primary block w-full pl-10 sm:text-sm border-gray-300 rounded-md @error('email') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="email@example.com"
                                    required
                                >
                                @error('email')
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="iconify text-red-500" data-icon="mdi-alert-circle"></span>
                                </div>
                                @enderror
                            </div>
                            @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="sm:col-span-3">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Mật Khẩu {{ isset($user) ? '(Để trống nếu không thay đổi)' : '<span class="text-red-500">*</span>' }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="iconify text-gray-400" data-icon="mdi-lock"></span>
                                </div>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="focus:ring-primary focus:border-primary block w-full pl-10 sm:text-sm border-gray-300 rounded-md @error('password') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="••••••••"
                                    {{ isset($user) ? '' : 'required' }}
                                    autocomplete="new-password"
                                >
                                <div id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                                    <span class="iconify text-gray-400" data-icon="mdi-eye"></span>
                                </div>
                                @error('password')
                                <div class="absolute inset-y-0 right-0 pr-10 flex items-center pointer-events-none">
                                    <span class="iconify text-red-500" data-icon="mdi-alert-circle"></span>
                                </div>
                                @enderror
                            </div>
                            @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="mt-1 text-xs text-gray-500" id="password-strength"></div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="sm:col-span-3">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Xác Nhận Mật Khẩu {{ isset($user) ? '' : '<span class="text-red-500">*</span>' }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="iconify text-gray-400" data-icon="mdi-lock-check"></span>
                                </div>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="focus:ring-primary focus:border-primary block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                                    placeholder="••••••••"
                                    {{ isset($user) ? '' : 'required' }}
                                    autocomplete="new-password"
                                >
                                <div id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                                    <span class="iconify text-gray-400" data-icon="mdi-eye"></span>
                                </div>
                            </div>
                            <div id="password-match" class="mt-1 hidden text-xs text-green-500">
                                <span class="iconify inline-block" data-icon="mdi-check-circle"></span>
                                Mật khẩu khớp
                            </div>
                        </div>

                        <!-- User Type -->
                        <div class="sm:col-span-3">
                            <label for="user_type" class="block text-sm font-medium text-gray-700">
                                Loại Người Dùng <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select
                                    id="user_type"
                                    name="user_type"
                                    class="focus:ring-primary focus:border-primary block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none sm:text-sm rounded-md @error('user_type') border-red-300 text-red-900 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                    required
                                >
                                    <option value="" disabled {{ old('user_type', isset($user) ? $user->user_type : '') ? '' : 'selected' }}>Chọn loại người dùng</option>
                                    <option value="student" {{ old('user_type', isset($user) ? $user->user_type : '') == 'student' ? 'selected' : '' }}>Học Sinh</option>
                                    <option value="teacher" {{ old('user_type', isset($user) ? $user->user_type : '') == 'teacher' ? 'selected' : '' }}>Giáo Viên</option>
                                    <option value="parent" {{ old('user_type', isset($user) ? $user->user_type : '') == 'parent' ? 'selected' : '' }}>Phụ Huynh</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <span class="iconify text-gray-400" data-icon="mdi-chevron-down"></span>
                                </div>
                            </div>
                            @error('user_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles & Permissions Section -->
            <div class="bg-white shadow overflow-hidden rounded-lg" id="roles-section">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                        <span class="iconify text-primary mr-2" data-icon="mdi-shield"></span>
                        Vai Trò & Quyền Hạn
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Gán vai trò và cấp quyền truy cập
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Roles Selection -->
                        <div class="sm:col-span-6">
                            <fieldset>
                                <legend class="text-base font-medium text-gray-900">Vai Trò Người Dùng</legend>
                                <p class="text-sm text-gray-500">Chọn vai trò để gán cho người dùng này</p>
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($roles as $role)
                                        <div class="relative flex items-start p-4 rounded-lg border border-gray-200 hover:border-primary hover:bg-primary-lighter transition-colors
                                        {{ (isset($user) && $user->roles->contains($role->id)) ? 'bg-primary-lighter border-primary' : '' }}
                                        {{ ($currentUserIsAdmin && $role->id === $adminRole->id) ? 'opacity-75' : '' }}"
                                        >
                                            <div class="flex items-center h-5">
                                                <input
                                                    id="role_{{ $role->id }}"
                                                    name="roles[]"
                                                    type="checkbox"
                                                    value="{{ $role->id }}"
                                                    class="focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded"
                                                    {{ (isset($user) && $user->roles->contains($role->id)) ? 'checked' : '' }}
                                                    {{ ($currentUserIsAdmin && $role->id === $adminRole->id) ? 'disabled' : '' }}
                                                >
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <label for="role_{{ $role->id }}" class="font-medium text-gray-700">{{ $role->name }}</label>
                                                <p class="text-sm text-gray-500">{{ $role->permissions->count() }} quyền hạn bao gồm</p>

                                                @if($role->permissions->count() > 0)
                                                    <div class="mt-2 flex flex-wrap gap-1">
                                                        @foreach($role->permissions->take(3) as $permission)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-light text-primary">
                                                            {{ $permission->name }}
                                                        </span>
                                                        @endforeach
                                                        @if($role->permissions->count() > 3)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                            +{{ $role->permissions->count() - 3 }} quyền khác
                                                        </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if($currentUserIsAdmin)
                                    <input type="hidden" name="roles[]" value="{{ $adminRole->id }}">
                                    <div class="mt-4 rounded-md bg-yellow-50 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <span class="iconify text-yellow-400" data-icon="mdi-alert-circle"></span>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-yellow-800">Quyền quản trị viên bị khóa</h3>
                                                <div class="mt-2 text-sm text-yellow-700">
                                                    <p>
                                                        Bạn không thể xóa quyền quản trị viên của chính mình. Điều này là vì lý do bảo mật.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </fieldset>

                            @error('roles')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avatar Section -->
            <div class="bg-white shadow overflow-hidden rounded-lg" id="avatar-section">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                        <span class="iconify text-primary mr-2" data-icon="mdi-image-outline"></span>
                        Ảnh Đại Diện
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Tải lên ảnh đại diện cho người dùng này
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <!-- Current Avatar Display -->
                        <div class="md:col-span-4 flex flex-col items-center justify-center">
                            @if(isset($user) && $user->avatar)
                                <div class="relative mb-4 w-40 h-40 rounded-full overflow-hidden border-4 border-primary-light shadow-lg image-container">
                                    <img
                                        src="{{ asset('storage/avatars/' . $user->avatar) }}"
                                        alt="{{ $user->name }}"
                                        class="h-full w-full object-cover"
                                        id="current-avatar"
                                    />
                                </div>
                                <p class="text-sm text-gray-500">Ảnh đại diện hiện tại</p>
                            @else
                                <div class="mb-4 flex w-40 h-40 rounded-full overflow-hidden border-4 border-primary-light shadow-lg bg-primary-lighter text-primary items-center justify-center image-container">
                                    <span class="iconify text-6xl" data-icon="mdi-account"></span>
                                </div>
                                <p class="text-sm text-gray-500">Chưa có ảnh đại diện</p>
                            @endif
                        </div>

                        <!-- Avatar Upload -->
                        <div class="md:col-span-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ảnh Đại Diện</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md" id="dropzone">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="avatar" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                                    <span>Tải tệp lên</span>
                                                    <input
                                                        id="avatar"
                                                        name="avatar"
                                                        type="file"
                                                        class="sr-only"
                                                        accept="image/*"
                                                    >
                                                </label>
                                                <p class="pl-1">hoặc kéo thả</p>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                PNG, JPG, GIF tối đa 2MB
                                            </p>
                                        </div>
                                    </div>
                                    @error('avatar')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Avatar Preview -->
                                <div id="avatar-preview" class="hidden">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-primary-light mr-4">
                                                <img id="preview-image" src="#" alt="Preview" class="h-full w-full object-cover">
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900">Xem trước</h4>
                                                <p class="text-sm text-gray-500" id="file-details">Tên tệp: <span id="filename"></span>, Kích thước: <span id="filesize"></span></p>
                                                <div class="mt-2 flex">
                                                    <button
                                                        type="button"
                                                        id="remove-avatar"
                                                        class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150"
                                                    >
                                                        <span class="iconify mr-1" data-icon="mdi-close"></span>
                                                        Xóa
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="md:col-span-12 flex flex-wrap justify-between">
                            <div></div>
                            <div class="flex flex-col md:flex-row md:space-x-3 mt-3 md:mt-0">
                                <a
                                    href="{{ route('admin.users.index') }}"
                                    class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150"
                                >
                                    <span class="iconify mr-2" data-icon="mdi-cancel"></span>
                                    Hủy
                                </a>

                                <button
                                    type="submit"
                                    class="mt-3 md:mt-0 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150"
                                >
                                    <span class="iconify mr-2" data-icon="{{ isset($user) ? 'mdi-content-save' : 'mdi-account-plus' }}"></span>
                                    {{ isset($user) ? 'Cập Nhật' : 'Tạo Người Dùng' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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

            // Password visibility toggle
            $('#togglePassword').on('click', function() {
                const passwordInput = $('#password');
                const icon = $(this).find('.iconify');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.attr('data-icon', 'mdi-eye-off');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.attr('data-icon', 'mdi-eye');
                }
            });

            $('#toggleConfirmPassword').on('click', function() {
                const passwordInput = $('#password_confirmation');
                const icon = $(this).find('.iconify');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.attr('data-icon', 'mdi-eye-off');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.attr('data-icon', 'mdi-eye');
                }
            });

            // Password match validation
            $('#password, #password_confirmation').on('keyup', function() {
                const password = $('#password').val();
                const confirmPassword = $('#password_confirmation').val();

                if (password && confirmPassword) {
                    if (password === confirmPassword) {
                        $('#password-match').removeClass('hidden text-red-500').addClass('text-green-500');
                        $('#password-match').html('<span class="iconify inline-block" data-icon="mdi-check-circle"></span> Mật khẩu khớp');
                    } else {
                        $('#password-match').removeClass('hidden text-green-500').addClass('text-red-500');
                        $('#password-match').html('<span class="iconify inline-block" data-icon="mdi-alert-circle"></span> Mật khẩu không khớp');
                    }
                } else {
                    $('#password-match').addClass('hidden');
                }
            });

            // Password strength indicator
            $('#password').on('keyup', function() {
                const password = $(this).val();
                let strength = 0;

                if (!password) {
                    $('#password-strength').empty();
                    return;
                }

                // Length check
                if (password.length >= 8) strength += 1;

                // Complexity checks
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[a-z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;

                let message, color;
                switch(strength) {
                    case 0:
                    case 1:
                        message = 'Rất yếu';
                        color = 'text-red-500';
                        break;
                    case 2:
                        message = 'Yếu';
                        color = 'text-orange-500';
                        break;
                    case 3:
                        message = 'Trung bình';
                        color = 'text-yellow-500';
                        break;
                    case 4:
                        message = 'Mạnh';
                        color = 'text-green-500';
                        break;
                    case 5:
                        message = 'Rất mạnh';
                        color = 'text-green-600';
                        break;
                }

                $('#password-strength').html(`<span class="iconify inline-block mr-1" data-icon="mdi-shield-check"></span> Độ mạnh mật khẩu: <span class="${color}">${message}</span>`);
            });

            // Avatar upload preview
            function readURL(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        $('#preview-image').attr('src', e.target.result);
                        $('#avatar-preview').removeClass('hidden');

                        // Update file details
                        $('#filename').text(file.name);
                        $('#filesize').text(formatBytes(file.size));
                    }

                    reader.readAsDataURL(file);
                }
            }

            function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';

                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];

                const i = Math.floor(Math.log(bytes) / Math.log(k));

                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            $('#avatar').on('change', function() {
                readURL(this);
            });

            // Remove avatar preview
            $('#remove-avatar').on('click', function() {
                $('#avatar').val('');
                $('#avatar-preview').addClass('hidden');
            });

            // Drag and drop functionality
            const dropzone = document.getElementById('dropzone');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropzone.classList.add('border-primary', 'bg-primary-lighter');
                dropzone.classList.remove('border-gray-300');
            }

            function unhighlight() {
                dropzone.classList.remove('border-primary', 'bg-primary-lighter');
                dropzone.classList.add('border-gray-300');
            }

            dropzone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                if (files.length) {
                    const fileInput = document.getElementById('avatar');
                    fileInput.files = files;

                    // Trigger change event
                    $(fileInput).trigger('change');
                }
            }

            // Additional animations
            $('.image-container').hover(
                function() { $(this).addClass('ring-4 ring-primary-light transform scale-105').css('transition', 'all 0.3s'); },
                function() { $(this).removeClass('ring-4 ring-primary-light transform scale-105'); }
            );
        });
    </script>
@endpush
