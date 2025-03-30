@extends('admin_layouts.admin')

@section('content')
    <div class="container px-6 mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                <span class="iconify mr-1" data-icon="mdi-arrow-left"></span>
                Back to Users
            </a>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">
                {{ isset($user) ? 'Edit User: ' . $user->name : 'Create New User' }}
            </h1>
        </div>

        @php
            $routeName = isset($user) ? 'admin.users.update' : 'admin.users.store';
            $routeParams = isset($user) ? ['user' => $user->id] : [];
            $adminRole = $roles->where('name', 'Administrator')->first();
            $currentUserIsAdmin = isset($user) && $user->id === auth()->id() && $user->isAdmin();
        @endphp

        <div class="mb-6">
            <nav class="flex" aria-label="Progress">
                <ol role="list" class="space-y-4 md:flex md:space-y-0">
                    <li class="md:flex-1">
                        <a href="#basic-info" class="group md:flex pl-4 py-2 flex items-center text-sm font-medium">
                        <span class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 text-indigo-600 step-active">
                            <span class="iconify" data-icon="mdi-account"></span>
                        </span>
                            <span class="ml-3 text-sm font-medium text-indigo-600">Basic Information</span>
                        </a>
                    </li>

                    <li class="md:flex-1">
                        <a href="#roles-section" class="group md:flex pl-4 py-2 flex items-center text-sm font-medium">
                        <span class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 text-gray-500 step">
                            <span class="iconify" data-icon="mdi-shield"></span>
                        </span>
                            <span class="ml-3 text-sm font-medium text-gray-500">Roles & Permissions</span>
                        </a>
                    </li>

                    <li class="md:flex-1">
                        <a href="#avatar-section" class="group md:flex pl-4 py-2 flex items-center text-sm font-medium">
                        <span class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 text-gray-500 step">
                            <span class="iconify" data-icon="mdi-image-outline"></span>
                        </span>
                            <span class="ml-3 text-sm font-medium text-gray-500">Profile Picture</span>
                        </a>
                    </li>
                </ol>
            </nav>
        </div>

        <form action="{{ route($routeName, $routeParams) }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="user-form">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <!-- Basic Information Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg" id="basic-info">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                        <span class="iconify text-indigo-500 mr-2" data-icon="mdi-account"></span>
                        Basic Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        User details and contact information
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Name -->
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Full Name <span class="text-red-500">*</span>
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
                                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md @error('name') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="John Doe"
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
                                Email Address <span class="text-red-500">*</span>
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
                                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md @error('email') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="john.doe@example.com"
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
                                Password {{ isset($user) ? '(Leave blank to keep current)' : '<span class="text-red-500">*</span>' }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="iconify text-gray-400" data-icon="mdi-lock"></span>
                                </div>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md @error('password') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
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
                            @endif
                            <div class="mt-1 text-xs text-gray-500" id="password-strength"></div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="sm:col-span-3">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Confirm Password {{ isset($user) ? '' : '<span class="text-red-500">*</span>' }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="iconify text-gray-400" data-icon="mdi-lock-check"></span>
                                </div>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
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
                                Passwords match
                            </div>
                        </div>

                        <!-- User Type -->
                        <div class="sm:col-span-3">
                            <label for="user_type" class="block text-sm font-medium text-gray-700">
                                User Type <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <select
                                    id="user_type"
                                    name="user_type"
                                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none sm:text-sm rounded-md @error('user_type') border-red-300 text-red-900 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                    required
                                >
                                    <option value="" disabled {{ old('user_type', isset($user) ? $user->user_type : '') ? '' : 'selected' }}>Select user type</option>
                                    <option value="student" {{ old('user_type', isset($user) ? $user->user_type : '') == 'student' ? 'selected' : '' }}>Student</option>
                                    <option value="teacher" {{ old('user_type', isset($user) ? $user->user_type : '') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                                    <option value="parent" {{ old('user_type', isset($user) ? $user->user_type : '') == 'parent' ? 'selected' : '' }}>Parent</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <span class="iconify text-gray-400" data-icon="mdi-chevron-down"></span>
                                </div>
                            </div>
                            @error('user_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Next Step Button -->
                        <div class="sm:col-span-6 flex justify-end">
                            <button type="button" class="next-step inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Next: Roles & Permissions
                                <span class="iconify ml-2" data-icon="mdi-arrow-right"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles & Permissions Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg" id="roles-section">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                        <span class="iconify text-indigo-500 mr-2" data-icon="mdi-shield"></span>
                        Roles & Permissions
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Assign roles and access levels
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Roles Selection -->
                        <div class="sm:col-span-6">
                            <fieldset>
                                <legend class="text-base font-medium text-gray-900">User Roles</legend>
                                <p class="text-sm text-gray-500">Select roles to assign to this user</p>
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($roles as $role)
                                        <div class="relative flex items-start p-4 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition-colors
                                        {{ (isset($user) && $user->roles->contains($role->id)) ? 'bg-indigo-50 border-indigo-500' : '' }}
                                        {{ ($currentUserIsAdmin && $role->id === $adminRole->id) ? 'opacity-75' : '' }}"
                                        >
                                            <div class="flex items-center h-5">
                                                <input
                                                    id="role_{{ $role->id }}"
                                                    name="roles[]"
                                                    type="checkbox"
                                                    value="{{ $role->id }}"
                                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                    {{ (isset($user) && $user->roles->contains($role->id)) ? 'checked' : '' }}
                                                    {{ ($currentUserIsAdmin && $role->id === $adminRole->id) ? 'disabled' : '' }}
                                                >
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <label for="role_{{ $role->id }}" class="font-medium text-gray-700">{{ $role->name }}</label>
                                                <p class="text-sm text-gray-500">{{ $role->permissions->count() }} permissions included</p>

                                                @if($role->permissions->count() > 0)
                                                    <div class="mt-2 flex flex-wrap gap-1">
                                                        @foreach($role->permissions->take(3) as $permission)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                            {{ $permission->name }}
                                                        </span>
                                                        @endforeach
                                                        @if($role->permissions->count() > 3)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                            +{{ $role->permissions->count() - 3 }} more
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
                                                <h3 class="text-sm font-medium text-yellow-800">Administrator Access Locked</h3>
                                                <div class="mt-2 text-sm text-yellow-700">
                                                    <p>
                                                        You cannot remove your own administrator privileges. This is for security reasons.
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

                        <!-- Next/Prev Buttons -->
                        <div class="sm:col-span-6 flex justify-between">
                            <button type="button" class="prev-step inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="iconify mr-2" data-icon="mdi-arrow-left"></span>
                                Back to Basic Info
                            </button>
                            <button type="button" class="next-step inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Next: Profile Picture
                                <span class="iconify ml-2" data-icon="mdi-arrow-right"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avatar Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg" id="avatar-section">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                        <span class="iconify text-indigo-500 mr-2" data-icon="mdi-image-outline"></span>
                        Profile Picture
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Upload a profile image for this user
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <!-- Current Avatar Display -->
                        <div class="md:col-span-4 flex flex-col items-center justify-center">
                            @if(isset($user) && $user->avatar)
                                <div class="relative mb-4 w-40 h-40 rounded-full overflow-hidden border-4 border-indigo-200 shadow-lg image-container">
                                    <img
                                        src="{{ asset('storage/avatars/' . $user->avatar) }}"
                                        alt="{{ $user->name }}"
                                        class="h-full w-full object-cover"
                                        id="current-avatar"
                                    />
                                </div>
                                <p class="text-sm text-gray-500">Current profile picture</p>
                            @else
                                <div class="mb-4 flex w-40 h-40 rounded-full overflow-hidden border-4 border-indigo-200 shadow-lg bg-indigo-50 text-indigo-500 items-center justify-center image-container">
                                    <span class="iconify text-6xl" data-icon="mdi-account"></span>
                                </div>
                                <p class="text-sm text-gray-500">No current profile picture</p>
                            @endif
                        </div>

                        <!-- Avatar Upload -->
                        <div class="md:col-span-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md" id="dropzone">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="avatar" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload a file</span>
                                                    <input
                                                        id="avatar"
                                                        name="avatar"
                                                        type="file"
                                                        class="sr-only"
                                                        accept="image/*"
                                                    >
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                PNG, JPG, GIF up to 2MB
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
                                            <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-indigo-300 mr-4">
                                                <img id="preview-image" src="#" alt="Preview" class="h-full w-full object-cover">
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900">Preview</h4>
                                                <p class="text-sm text-gray-500" id="file-details">Filename: <span id="filename"></span>, Size: <span id="filesize"></span></p>
                                                <div class="mt-2 flex">
                                                    <button
                                                        type="button"
                                                        id="remove-avatar"
                                                        class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    >
                                                        <span class="iconify mr-1" data-icon="mdi-close"></span>
                                                        Remove
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
                            <button
                                type="button"
                                class="prev-step inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <span class="iconify mr-2" data-icon="mdi-arrow-left"></span>
                                Back to Roles
                            </button>

                            <div class="flex flex-col md:flex-row md:space-x-3 mt-3 md:mt-0">
                                <a
                                    href="{{ route('admin.users.index') }}"
                                    class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <span class="iconify mr-2" data-icon="mdi-cancel"></span>
                                    Cancel
                                </a>

                                <button
                                    type="submit"
                                    class="mt-3 md:mt-0 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <span class="iconify mr-2" data-icon="{{ isset($user) ? 'mdi-content-save' : 'mdi-account-plus' }}"></span>
                                    {{ isset($user) ? 'Update User' : 'Create User' }}
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
                        $('#password-match').html('<span class="iconify inline-block" data-icon="mdi-check-circle"></span> Passwords match');
                    } else {
                        $('#password-match').removeClass('hidden text-green-500').addClass('text-red-500');
                        $('#password-match').html('<span class="iconify inline-block" data-icon="mdi-alert-circle"></span> Passwords do not match');
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
                        message = 'Very Weak';
                        color = 'text-red-500';
                        break;
                    case 2:
                        message = 'Weak';
                        color = 'text-orange-500';
                        break;
                    case 3:
                        message = 'Medium';
                        color = 'text-yellow-500';
                        break;
                    case 4:
                        message = 'Strong';
                        color = 'text-green-500';
                        break;
                    case 5:
                        message = 'Very Strong';
                        color = 'text-green-600';
                        break;
                }

                $('#password-strength').html(`<span class="iconify inline-block mr-1" data-icon="mdi-shield-check"></span> Password strength: <span class="${color}">${message}</span>`);
            });

            // Multi-step navigation
            $('.next-step').on('click', function() {
                const currentSection = $(this).closest('div[id$="-section"], div[id$="-info"]');
                const currentSectionId = currentSection.attr('id');
                let nextSectionId;

                if (currentSectionId === 'basic-info') {
                    nextSectionId = 'roles-section';
                } else if (currentSectionId === 'roles-section') {
                    nextSectionId = 'avatar-section';
                }

                if (nextSectionId) {
                    $('html, body').animate({
                        scrollTop: $('#' + nextSectionId).offset().top - 20
                    }, 500);

                    // Update step indicators
                    $('.step').each(function(index) {
                        if (index <= $('.step').index($('#' + nextSectionId).find('.step'))) {
                            $(this).removeClass('border-gray-300 text-gray-500').addClass('border-indigo-600 text-indigo-600');
                        }
                    });
                }
            });

            $('.prev-step').on('click', function() {
                const currentSection = $(this).closest('div[id$="-section"]');
                const currentSectionId = currentSection.attr('id');
                let prevSectionId;

                if (currentSectionId === 'roles-section') {
                    prevSectionId = 'basic-info';
                } else if (currentSectionId === 'avatar-section') {
                    prevSectionId = 'roles-section';
                }

                if (prevSectionId) {
                    $('html, body').animate({
                        scrollTop: $('#' + prevSectionId).offset().top - 20
                    }, 500);
                }
            });

            // Navigation links in steps
            $('nav a').on('click', function(e) {
                e.preventDefault();
                const targetId = $(this).attr('href');

                $('html, body').animate({
                    scrollTop: $(targetId).offset().top - 20
                }, 500);
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
                dropzone.classList.add('border-indigo-500', 'bg-indigo-50');
                dropzone.classList.remove('border-gray-300');
            }

            function unhighlight() {
                dropzone.classList.remove('border-indigo-500', 'bg-indigo-50');
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
                function() { $(this).addClass('ring-4 ring-indigo-300 transform scale-105').css('transition', 'all 0.3s'); },
                function() { $(this).removeClass('ring-4 ring-indigo-300 transform scale-105'); }
            );
        });
    </script>
@endpush
