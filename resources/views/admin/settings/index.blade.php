@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-2">Cài đặt chung</h2>

        <form action="{{ route('admin.settings.update') }}"
              method="POST"
              enctype="multipart/form-data"
              class="rounded-sm border bg-white shadow">
            @csrf
            @method('PUT')

            <div class="p-6">
                <h3 class="text-lg font-medium text-[#1c2434] mb-4">Thông tin trang web</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <!-- Site Name -->
                        <div>
                            <label for="site_name" class="mb-3 block text-sm font-medium text-[#1c2434]">
                                Tên trang web
                            </label>
                            <input type="text"
                                   name="site_name"
                                   id="site_name"
                                   value="{{ old('site_name', setting('site_name')) }}"
                                   class="relative w-full rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                                   required>
                            @error('site_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Site Description -->
                        <div>
                            <label for="site_description" class="mb-3 block text-sm font-medium text-[#1c2434]">
                                Mô tả trang web
                            </label>
                            <textarea name="site_description"
                                      id="site_description"
                                      rows="4"
                                      class="relative w-full rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary">{{ old('site_description', setting('site_description')) }}</textarea>
                            @error('site_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Site Meta Keywords -->
                        <div>
                            <label for="site_meta_keywords" class="mb-3 block text-sm font-medium text-[#1c2434]">
                                Từ khóa meta
                            </label>
                            <input type="text"
                                   name="site_meta_keywords"
                                   id="site_meta_keywords"
                                   value="{{ old('site_meta_keywords', setting('site_meta_keywords')) }}"
                                   class="relative w-full rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary">
                            @error('site_meta_keywords')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cache Settings Section -->
                        <div class="space-y-4">
                            <!-- Cache Lifetime -->
                            <div>
                                <label for="cache_lifetime" class="mb-3 block text-sm font-medium text-[#1c2434]">
                                    Thời gian lưu cache
                                </label>
                                <div class="relative">
                                    <input type="number"
                                           name="cache_lifetime"
                                           id="cache_lifetime"
                                           value="{{ old('cache_lifetime', setting('cache_lifetime')) }}"
                                           class="relative w-full rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                                           required>
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">phút</span>
                                </div>
                                @error('cache_lifetime')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <!-- Site Logo -->
                        <div>
                            <label for="site_logo" class="mb-3 block text-sm font-medium text-[#1c2434]">
                                Logo trang web
                            </label>
                            <input type="file"
                                   name="site_logo"
                                   id="site_logo"
                                   class="relative w-full rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                                   onchange="previewImage(this, 'site_logo_preview')">
                            <div id="site_logo_preview" class="mt-2">
                                @if(setting('site_logo'))
                                    <img src="{{ Storage::url(setting('site_logo')) }}" alt="Site Logo" class="w-auto h-24 object-cover rounded">
                                @endif
                            </div>
                            @error('site_logo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Site Favicon -->
                        <div>
                            <label for="site_favicon" class="mb-3 block text-sm font-medium text-[#1c2434]">
                                Favicon
                            </label>
                            <input type="file"
                                   name="site_favicon"
                                   id="site_favicon"
                                   class="relative w-full rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                                   onchange="previewImage(this, 'site_favicon_preview')">
                            <div id="site_favicon_preview" class="mt-2">
                                @if(setting('site_favicon'))
                                    <img src="{{ Storage::url(setting('site_favicon')) }}" alt="Site Favicon" class="w-8 h-8 object-cover rounded">
                                @endif
                            </div>
                            @error('site_favicon')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Site OG Image -->
                        <div>
                            <label for="site_og_image" class="mb-3 block text-sm font-medium text-[#1c2434]">
                                Ảnh OG
                            </label>
                            <input type="file"
                                   name="site_og_image"
                                   id="site_og_image"
                                   class="relative w-full rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                                   onchange="previewImage(this, 'site_og_image_preview')">
                            <div id="site_og_image_preview" class="mt-2">
                                @if(setting('site_og_image'))
                                    <img src="{{ Storage::url(setting('site_og_image')) }}" alt="Site OG Image" class="w-48 h-48 object-cover rounded">
                                @endif
                            </div>
                            @error('site_og_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Cache Toggle -->
                <div class="flex items-center gap-4 bg-gray-50 rounded-sm p-4 mt-4">
                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <label for="cache_enabled" class="text-base font-medium text-[#1c2434]">
                                Kích hoạt cache
                            </label>
                            <div class="relative inline-block w-12 align-middle select-none">
                                <input type="checkbox"
                                       name="cache_enabled"
                                       id="cache_enabled"
                                       class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200 ease-in-out translate-x-0 checked:translate-x-6"
                                    {{ old('cache_enabled', setting('cache_enabled')) ? 'checked' : '' }}>
                                <label for="cache_enabled"
                                       class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer">
                                </label>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            Khuyến nghị bật để cải thiện hiệu suất
                        </p>
                    </div>
                    @error('cache_enabled')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit"
                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                        Lưu cài đặt
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-auto h-24 object-cover rounded">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        .toggle-checkbox:checked {
            border-color: rgb(99 102 241);
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: rgb(99 102 241);
        }
        .toggle-checkbox {
            position: absolute !important;
            top: auto !important;
            z-index: 1;
        }
        .toggle-label {
            transition: background-color 0.2s ease-in-out;
        }
    </style>
@endpush
