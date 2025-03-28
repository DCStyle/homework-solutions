@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Left Side - Illustration -->
        <div class="hidden md:flex md:w-1/2 bg-indigo-50 items-center justify-center p-10">
            <div class="max-w-lg">
                <h1 class="text-3xl md:text-4xl font-bold text-indigo-900 mb-6">
                    Chào mừng bạn quay trở lại với <span class="text-primary">{{ setting('site_name') }}</span>
                </h1>
                <p class="text-indigo-700 mb-8">
                    Tham gia thảo luận và tải xuống vô vàn tài liệu học tập miễn phí.
                    Chúng tôi cung cấp một nền tảng an toàn và thân thiện cho học sinh và giáo viên để kết nối, chia sẻ và học hỏi lẫn nhau.
                </p>

                <!-- Educational Illustration -->
                <div class="w-full flex justify-center">
                    <svg class="w-80 h-80" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                        <!-- Learning/Education Illustration -->
                        <rect x="100" y="300" width="300" height="40" rx="10" fill="#e0e7ff" />
                        <rect x="150" y="150" width="200" height="150" rx="10" fill="#c7d2fe" />
                        <rect x="175" y="175" width="150" height="100" rx="5" fill="#4f46e5" />
                        <circle cx="250" cy="350" r="15" fill="#4f46e5" />
                        <path d="M180 80 L320 80 L280 150 L220 150 Z" fill="#818cf8" />
                        <circle cx="200" cy="225" r="10" fill="#eef2ff" />
                        <circle cx="300" cy="225" r="10" fill="#eef2ff" />
                        <path d="M230 250 C250 265, 270 265, 290 250" stroke="#eef2ff" stroke-width="4" fill="none" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-6 md:p-10 bg-white">
            <div class="w-full max-w-md">
                <!-- Logo/Site Name -->
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-extrabold text-indigo-600">{{ setting('site_name') }}</h2>
                    <p class="mt-2 text-gray-600">Đăng nhập vào hệ thống</p>
                </div>

                <!-- Social Login -->
                <div class="space-y-3 mb-6">
                    <!-- Google Login -->
                    <a href="{{ route('login.google') }}" class="flex items-center justify-center w-full py-3 px-4 bg-white rounded-lg shadow-md border border-gray-200 transition-all hover:shadow-lg">
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z" fill="#4285F4"/>
                        </svg>
                        <span class="text-gray-800 font-medium">Tiếp tục với Google</span>
                    </a>

                    <!-- Facebook Login -->
                    <a href="{{ route('login.facebook') }}" class="flex items-center justify-center w-full py-3 px-4 bg-white rounded-lg shadow-md border border-gray-200 transition-all hover:shadow-lg">
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="#1877F2"/>
                        </svg>
                        <span class="text-gray-800 font-medium">Tiếp tục với Facebook</span>
                    </a>

                    <!-- Twitter (X) Login -->
                    <a href="{{ route('login.twitter') }}" class="flex items-center justify-center w-full py-3 px-4 bg-white rounded-lg shadow-md border border-gray-200 transition-all hover:shadow-lg">
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.543 7.104c.015.211.015.423.015.636 0 6.507-4.954 14.01-14.01 14.01v-.003A13.94 13.94 0 0 1 0 19.539a9.88 9.88 0 0 0 7.287-2.041 4.93 4.93 0 0 1-4.6-3.42 4.916 4.916 0 0 0 2.223-.084A4.926 4.926 0 0 1 .96 9.167v-.062a4.887 4.887 0 0 0 2.235.616A4.928 4.928 0 0 1 1.67 3.148a13.98 13.98 0 0 0 10.15 5.144 4.929 4.929 0 0 1 8.39-4.49 9.868 9.868 0 0 0 3.128-1.196 4.941 4.941 0 0 1-2.165 2.724A9.828 9.828 0 0 0 24 4.555a10.019 10.019 0 0 1-2.457 2.549z" fill="#000000"/>
                        </svg>
                        <span class="text-gray-800 font-medium">Tiếp tục với X (Twitter)</span>
                    </a>
                </div>

                <!-- Divider -->
                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-white text-gray-500">hoặc đăng nhập sử dụng email của bạn</span>
                    </div>
                </div>

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Địa chỉ email</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-md border-gray-300 shadow-sm" placeholder="you@example.com">
                        </div>
                        @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" required autocomplete="current-password" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-md border-gray-300 shadow-sm" placeholder="••••••••">
                        </div>
                        @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">Lưu phiên đăng nhập</label>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                Bạn quên mật khẩu?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                            Đăng nhập
                        </button>
                    </div>
                </form>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Chưa có tài khoản?
                        <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Tạo tài khoản ngay, chỉ mất vài giây!
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
