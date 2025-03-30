@extends('admin_layouts.admin')

@section('title', 'Cài Đặt Chung')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">Cài Đặt Chung</h2>
                    <p class="mt-1 text-white/90">Quản lý cấu hình cơ bản của trang web</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/20 py-2 px-4 text-center font-medium text-white hover:bg-white/30 transition-all duration-200">
                        <span class="iconify" data-icon="mdi-arrow-left"></span>
                        Quay Lại Dashboard
                    </a>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 h-64 w-64 rounded-full bg-white/5"></div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-100 p-4 text-green-700 flex items-center shadow-md animate-fadeIn">
                <span class="iconify mr-2 text-xl" data-icon="mdi-check-circle"></span>
                <span>{{ session('success') }}</span>
                <button type="button" class="ml-auto" onclick="this.parentElement.remove()">
                    <span class="iconify" data-icon="mdi-close"></span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-lg bg-red-100 p-4 text-red-700 flex items-center shadow-md animate-fadeIn">
                <span class="iconify mr-2 text-xl" data-icon="mdi-alert-circle"></span>
                <span>{{ session('error') }}</span>
                <button type="button" class="ml-auto" onclick="this.parentElement.remove()">
                    <span class="iconify" data-icon="mdi-close"></span>
                </button>
            </div>
        @endif

        <!-- Settings Form -->
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <!-- Main Settings Column -->
                <div class="xl:col-span-2 space-y-6">
                    <!-- Website Information Card -->
                    <div class="rounded-xl border border-stroke bg-white p-6 shadow-md">
                        <div class="mb-6 flex items-center">
                        <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                            <span class="iconify text-xl text-primary" data-icon="mdi-web"></span>
                        </span>
                            <h3 class="text-xl font-semibold text-black">Thông Tin Trang Web</h3>
                        </div>

                        <div class="space-y-5">
                            <!-- Site URL -->
                            <div class="form-group">
                                <label for="site_url" class="mb-2.5 block font-medium text-black">
                                    URL Trang Web
                                </label>
                                <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                    <span class="iconify" data-icon="mdi-link-variant"></span>
                                </span>
                                    <input type="url" name="site_url" id="site_url"
                                           value="{{ old('site_url', setting('site_url')) }}"
                                           class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                           placeholder="https://example.com" required>
                                </div>
                                @error('site_url')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Site Name -->
                            <div class="form-group">
                                <label for="site_name" class="mb-2.5 block font-medium text-black">
                                    Tên Trang Web
                                </label>
                                <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                    <span class="iconify" data-icon="mdi-domain"></span>
                                </span>
                                    <input type="text" name="site_name" id="site_name"
                                           value="{{ old('site_name', setting('site_name')) }}"
                                           class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                           placeholder="Tên trang web của bạn" required>
                                </div>
                                @error('site_name')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Site Description -->
                            <div class="form-group">
                                <label for="site_description" class="mb-2.5 block font-medium text-black">
                                    Mô Tả Trang Web
                                </label>
                                <div class="relative">
                                <span class="absolute left-4 top-6 text-gray-500">
                                    <span class="iconify" data-icon="mdi-text-box-outline"></span>
                                </span>
                                    <textarea name="site_description" id="site_description" rows="4"
                                              class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                              placeholder="Mô tả ngắn gọn về trang web của bạn">{{ old('site_description', setting('site_description')) }}</textarea>
                                </div>
                                @error('site_description')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Site Meta Keywords -->
                            <div class="form-group">
                                <label for="site_meta_keywords" class="mb-2.5 block font-medium text-black">
                                    Từ Khóa Meta
                                </label>
                                <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                    <span class="iconify" data-icon="mdi-tag-multiple-outline"></span>
                                </span>
                                    <input type="text" name="site_meta_keywords" id="site_meta_keywords"
                                           value="{{ old('site_meta_keywords', setting('site_meta_keywords')) }}"
                                           class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-4 outline-none focus:border-primary focus-visible:shadow-none"
                                           placeholder="từ khóa, được phân tách, bằng dấu phẩy">
                                </div>
                                @error('site_meta_keywords')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Cache Settings Card -->
                    <div class="rounded-xl border border-stroke bg-white p-6 shadow-md">
                        <div class="mb-6 flex items-center">
                        <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                            <span class="iconify text-xl text-blue-600" data-icon="mdi-cached"></span>
                        </span>
                            <h3 class="text-xl font-semibold text-black">Cài Đặt Cache</h3>
                        </div>

                        <div class="space-y-5">
                            <!-- Cache Toggle -->
                            <div class="flex items-center gap-4 p-4 rounded-lg border border-gray-100 bg-gray-50/80 hover:bg-gray-50 transition-all duration-200">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                                    <span class="iconify text-blue-600 text-xl" data-icon="mdi-refresh"></span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <label for="cache_enabled" class="text-base font-medium text-black">
                                            Kích Hoạt Cache
                                        </label>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="cache_enabled" id="cache_enabled"
                                                   class="sr-only peer" {{ old('cache_enabled', setting('cache_enabled')) ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-primary/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                        </label>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Khuyến nghị bật để cải thiện hiệu suất trang web
                                    </p>
                                </div>
                                @error('cache_enabled')
                                <p class="text-sm text-red-600 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Cache Lifetime -->
                            <div class="form-group">
                                <label for="cache_lifetime" class="mb-2.5 block font-medium text-black">
                                    Thời Gian Lưu Cache
                                </label>
                                <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                    <span class="iconify" data-icon="mdi-clock-outline"></span>
                                </span>
                                    <input type="number" name="cache_lifetime" id="cache_lifetime"
                                           value="{{ old('cache_lifetime', setting('cache_lifetime')) }}"
                                           class="w-full rounded-lg border border-stroke bg-white py-3 pl-10 pr-16 outline-none focus:border-primary focus-visible:shadow-none"
                                           required>
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">phút</span>
                                </div>
                                @error('cache_lifetime')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Column -->
                <div class="space-y-6">
                    <!-- Media Settings Card -->
                    <div class="rounded-xl border border-stroke bg-white p-6 shadow-md">
                        <div class="mb-6 flex items-center">
                        <span class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                            <span class="iconify text-xl text-amber-600" data-icon="mdi-image-outline"></span>
                        </span>
                            <h3 class="text-xl font-semibold text-black">Media & Hình Ảnh</h3>
                        </div>

                        <div class="space-y-5">
                            <!-- Site Logo -->
                            <div class="form-group">
                                <label for="site_logo" class="mb-2.5 block font-medium text-black">
                                    Logo Trang Web
                                </label>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div id="site_logo_preview" class="mb-3 flex justify-center">
                                        @if(setting('site_logo'))
                                            <img src="{{ Storage::url(setting('site_logo')) }}" alt="Site Logo" class="h-24 object-contain rounded">
                                        @else
                                            <div class="h-24 w-48 flex items-center justify-center border-2 border-dashed border-gray-300 rounded">
                                                <span class="text-gray-400 text-sm">Chưa có logo</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="file-input-wrapper">
                                        <label for="site_logo" class="inline-flex items-center justify-center w-full py-2.5 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                                            <span class="iconify mr-2" data-icon="mdi-upload"></span>
                                            Tải lên logo mới
                                        </label>
                                        <input type="file" name="site_logo" id="site_logo" class="hidden"
                                               onchange="previewImage(this, 'site_logo_preview', 'h-24 object-contain rounded')">
                                    </div>
                                </div>
                                @error('site_logo')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Site Favicon -->
                            <div class="form-group">
                                <label for="site_favicon" class="mb-2.5 block font-medium text-black">
                                    Favicon
                                </label>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div id="site_favicon_preview" class="mb-3 flex justify-center">
                                        @if(setting('site_favicon'))
                                            <img src="{{ Storage::url(setting('site_favicon')) }}" alt="Site Favicon" class="w-10 h-10 object-contain rounded">
                                        @else
                                            <div class="h-10 w-10 flex items-center justify-center border-2 border-dashed border-gray-300 rounded">
                                                <span class="text-gray-400 text-xs">Favicon</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="file-input-wrapper">
                                        <label for="site_favicon" class="inline-flex items-center justify-center w-full py-2.5 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                                            <span class="iconify mr-2" data-icon="mdi-upload"></span>
                                            Tải lên favicon mới
                                        </label>
                                        <input type="file" name="site_favicon" id="site_favicon" class="hidden"
                                               onchange="previewImage(this, 'site_favicon_preview', 'w-10 h-10 object-contain rounded')">
                                    </div>
                                </div>
                                @error('site_favicon')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Site OG Image -->
                            <div class="form-group">
                                <label for="site_og_image" class="mb-2.5 block font-medium text-black">
                                    Ảnh OG <span class="text-xs text-gray-500 font-normal">(Hiển thị khi chia sẻ)</span>
                                </label>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div id="site_og_image_preview" class="mb-3 flex justify-center">
                                        @if(setting('site_og_image'))
                                            <img src="{{ Storage::url(setting('site_og_image')) }}" alt="Site OG Image" class="max-w-full max-h-48 object-contain rounded">
                                        @else
                                            <div class="h-32 w-full flex items-center justify-center border-2 border-dashed border-gray-300 rounded">
                                                <span class="text-gray-400 text-sm">Chưa có ảnh OG</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="file-input-wrapper">
                                        <label for="site_og_image" class="inline-flex items-center justify-center w-full py-2.5 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                                            <span class="iconify mr-2" data-icon="mdi-upload"></span>
                                            Tải lên ảnh OG mới
                                        </label>
                                        <input type="file" name="site_og_image" id="site_og_image" class="hidden"
                                               onchange="previewImage(this, 'site_og_image_preview', 'max-w-full max-h-48 object-contain rounded')">
                                    </div>
                                </div>
                                @error('site_og_image')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi-alert-circle"></span>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Card -->
                    <div class="sticky top-6 rounded-xl border border-stroke bg-white p-6 shadow-md">
                        <div class="mb-4">
                            <h4 class="text-lg font-semibold text-black">Lưu Thay Đổi</h4>
                            <p class="text-sm text-gray-500 mt-1">Cập nhật các cài đặt website của bạn</p>
                        </div>

                        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-lg bg-primary py-3 px-6 font-medium text-white hover:bg-primary/90 hover:shadow-lg focus:ring-4 focus:ring-primary/30 transition-all duration-200 transform hover:-translate-y-1">
                            <span class="iconify" data-icon="mdi-content-save"></span>
                            Lưu Cài Đặt
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(input, previewId, classNames) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}" class="${classNames}" alt="Preview">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        .animate-fadeIn {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        input:focus, textarea:focus, select:focus {
            border-color: rgb(99 102 241);
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.2);
        }

        .form-group:hover label {
            color: rgb(99 102 241);
            transition: all 0.2s;
        }
    </style>
@endpush
