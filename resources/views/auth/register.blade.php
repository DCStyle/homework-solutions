@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Left Side - Registration Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-6 md:p-10 bg-white">
            <div class="w-full max-w-md">
                <!-- Logo/Site Name -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-extrabold text-indigo-600">{{ setting('site_name') }}</h2>
                    <p class="mt-2 text-gray-600">Tạo tài khoản mới, tham gia thảo luận</p>
                </div>

                <!-- Social Registration -->
                <div class="space-y-3 mb-6">
                    <!-- Google Registration -->
                    <a href="{{ route('login.google') }}" class="flex items-center justify-center w-full py-3 px-4 bg-white rounded-lg shadow-md border border-gray-200 transition-all hover:shadow-lg">
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z" fill="#4285F4"/>
                        </svg>
                        <span class="text-gray-800 font-medium">Đăng ký bằng Google</span>
                    </a>

                    <!-- Facebook Registration -->
                    <a href="{{ route('login.facebook') }}" class="flex items-center justify-center w-full py-3 px-4 bg-white rounded-lg shadow-md border border-gray-200 transition-all hover:shadow-lg">
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="#1877F2"/>
                        </svg>
                        <span class="text-gray-800 font-medium">Đăng ký bằng Facebook</span>
                    </a>

                    <!-- Twitter (X) Registration -->
                    <a href="{{ route('login.twitter') }}" class="flex items-center justify-center w-full py-3 px-4 bg-white rounded-lg shadow-md border border-gray-200 transition-all hover:shadow-lg">
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.543 7.104c.015.211.015.423.015.636 0 6.507-4.954 14.01-14.01 14.01v-.003A13.94 13.94 0 0 1 0 19.539a9.88 9.88 0 0 0 7.287-2.041 4.93 4.93 0 0 1-4.6-3.42 4.916 4.916 0 0 0 2.223-.084A4.926 4.926 0 0 1 .96 9.167v-.062a4.887 4.887 0 0 0 2.235.616A4.928 4.928 0 0 1 1.67 3.148a13.98 13.98 0 0 0 10.15 5.144 4.929 4.929 0 0 1 8.39-4.49 9.868 9.868 0 0 0 3.128-1.196 4.941 4.941 0 0 1-2.165 2.724A9.828 9.828 0 0 0 24 4.555a10.019 10.019 0 0 1-2.457 2.549z" fill="#000000"/>
                        </svg>
                        <span class="text-gray-800 font-medium">Đăng ký bằng X (Twitter)</span>
                    </a>
                </div>

                <!-- Divider -->
                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-white text-gray-500">hoặc tiếp tục với email của baạn</span>
                    </div>
                </div>

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Tên của bạn</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Nguyễn Văn A">
                        </div>
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Địa chỉ email</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-md border-gray-300 shadow-sm" placeholder="email@gmail.com">
                        </div>
                        @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                            <input id="password" name="password" type="password" required autocomplete="new-password" class="pl-10 pr-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-md border-gray-300 shadow-sm" placeholder="••••••••">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <button type="button" id="togglePassword" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="eyeIcon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Password Strength Meter -->
                        <div class="mt-2">
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div id="passwordStrengthBar" class="h-full bg-red-500 transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <p id="passwordStrengthText" class="mt-1 text-xs text-gray-500">Đánh giá: Mật khẩu dễ đoán</p>
                        </div>

                        <!-- Password Suggestions -->
                        <div id="passwordSuggestions" class="mt-2 text-xs text-gray-500 p-2 bg-gray-50 rounded border border-gray-200 hidden">
                            <p class="font-medium mb-1">Đảm bảo mật khẩu của bạn có chứa các yếu tố:</p>
                            <ul class="space-y-1 list-disc pl-5">
                                <li id="lengthCriterion" class="text-gray-500">Ít nhất 8 ký tự</li>
                                <li id="uppercaseCriterion" class="text-gray-500">Gồm ký tự in hoa (A-Z)</li>
                                <li id="lowercaseCriterion" class="text-gray-500">Gồm ký tự in thường (a-z)</li>
                                <li id="numberCriterion" class="text-gray-500">Số (0-9)</li>
                                <li id="specialCriterion" class="text-gray-500">Ký tự đặc biệt (!@#$%^&*)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Xác nhận lại mật khẩu</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="pl-10 pr-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-md border-gray-300 shadow-sm" placeholder="••••••••">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <button type="button" id="toggleConfirmPassword" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="eyeIconConfirm">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p id="passwordMatchText" class="mt-1 text-xs hidden"></p>
                        @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- User Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tôi là</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="flex p-3 w-full h-full border border-gray-200 rounded-lg cursor-pointer focus-within:ring-2 focus-within:ring-indigo-500 hover:bg-gray-50">
                                    <input type="radio" name="user_type" value="student" class="sr-only" checked>
                                    <div class="flex items-center w-full">
                                        <svg class="w-6 h-6 text-indigo-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Học sinh</span>
                                    </div>
                                    <span class="absolute inset-0 rounded-lg ring-2 ring-indigo-500 hidden peer-checked:block"></span>
                                </label>
                            </div>
                            <div>
                                <label class="flex p-3 w-full h-full border border-gray-200 rounded-lg cursor-pointer focus-within:ring-2 focus-within:ring-indigo-500 hover:bg-gray-50">
                                    <input type="radio" name="user_type" value="teacher" class="sr-only">
                                    <div class="flex items-center w-full">
                                        <svg class="w-6 h-6 text-indigo-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Giáo viên</span>
                                    </div>
                                    <span class="absolute inset-0 rounded-lg ring-2 ring-indigo-500 hidden peer-checked:block"></span>
                                </label>
                            </div>
                        </div>
                        @error('user_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="font-medium text-gray-700">I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-500">Terms of Service</a> and <a href="#" class="text-indigo-600 hover:text-indigo-500">Privacy Policy</a></label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                            Tạo tài khoản
                        </button>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Bạn đã có tài khoản?
                        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Đăng nhập ngay
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Illustration -->
        <div class="hidden md:flex md:w-1/2 bg-indigo-50 items-center justify-center p-10">
            <div class="max-w-lg">
                <h1 class="text-3xl md:text-4xl font-bold text-indigo-900 mb-6">
                    Tham gia vào cộng đồng <span class="text-primary">{{ setting('site_name') }}</span>
                </h1>
                <p class="text-indigo-700 mb-8">
                    Tạo tài khoản, tham gia thảo luận và tải xuống vô vàn tài liệu học tập miễn phí.
                    Chúng tôi cung cấp một nền tảng an toàn và thân thiện cho học sinh và giáo viên để kết nối, chia sẻ và học hỏi lẫn nhau.
                </p>

                <!-- Educational Illustration -->
                <div class="w-full flex justify-center">
                    <svg class="w-80 h-80" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                        <!-- Classroom/Collaboration Illustration -->
                        <circle cx="250" cy="250" r="120" fill="#e0e7ff" />
                        <circle cx="200" cy="200" r="30" fill="#4f46e5" />
                        <circle cx="320" cy="220" r="25" fill="#818cf8" />
                        <circle cx="270" cy="270" r="35" fill="#6366f1" />
                        <circle cx="180" cy="290" r="20" fill="#c7d2fe" />

                        <!-- Connection lines between circles (representing connectivity) -->
                        <line x1="200" y1="200" x2="270" y2="270" stroke="#4f46e5" stroke-width="3" />
                        <line x1="320" y1="220" x2="270" y2="270" stroke="#4f46e5" stroke-width="3" />
                        <line x1="180" y1="290" x2="270" y2="270" stroke="#4f46e5" stroke-width="3" />

                        <!-- Book/Document elements -->
                        <rect x="150" y="350" width="80" height="10" rx="2" fill="#4f46e5" />
                        <rect x="150" y="370" width="60" height="10" rx="2" fill="#818cf8" />
                        <rect x="270" y="350" width="80" height="10" rx="2" fill="#4f46e5" />
                        <rect x="270" y="370" width="60" height="10" rx="2" fill="#818cf8" />

                        <!-- Graduation cap -->
                        <path d="M250 100 L300 130 L250 160 L200 130 Z" fill="#4f46e5" />
                        <rect x="245" y="160" width="10" height="30" fill="#4f46e5" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const passwordField = document.getElementById('password');
            const togglePasswordButton = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');

            togglePasswordButton.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);

                // Toggle eye icon
                if (type === 'text') {
                    eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
                } else {
                    eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
                }
            });

            // Password strength meter
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');
            const passwordSuggestions = document.getElementById('passwordSuggestions');

            // Criteria elements
            const lengthCriterion = document.getElementById('lengthCriterion');
            const uppercaseCriterion = document.getElementById('uppercaseCriterion');
            const lowercaseCriterion = document.getElementById('lowercaseCriterion');
            const numberCriterion = document.getElementById('numberCriterion');
            const specialCriterion = document.getElementById('specialCriterion');

            passwordField.addEventListener('input', function() {
                const password = passwordField.value;
                const strength = calculatePasswordStrength(password);

                // Update strength bar
                strengthBar.style.width = strength.percentage + '%';

                // Update strength text and bar color
                strengthText.textContent = 'Tình trạng: ' + strength.label;

                if (strength.score === 0) {
                    strengthBar.className = 'h-full bg-red-500 transition-all duration-300';
                } else if (strength.score === 1) {
                    strengthBar.className = 'h-full bg-orange-500 transition-all duration-300';
                } else if (strength.score === 2) {
                    strengthBar.className = 'h-full bg-yellow-500 transition-all duration-300';
                } else if (strength.score === 3) {
                    strengthBar.className = 'h-full bg-lime-500 transition-all duration-300';
                } else {
                    strengthBar.className = 'h-full bg-green-600 transition-all duration-300';
                }

                // Show/hide suggestions based on password strength
                if (strength.score < 3 && password.length > 0) {
                    passwordSuggestions.classList.remove('hidden');
                } else {
                    passwordSuggestions.classList.add('hidden');
                }

                // Update criteria status
                updateCriterionStatus(lengthCriterion, password.length >= 8);
                updateCriterionStatus(uppercaseCriterion, /[A-Z]/.test(password));
                updateCriterionStatus(lowercaseCriterion, /[a-z]/.test(password));
                updateCriterionStatus(numberCriterion, /[0-9]/.test(password));
                updateCriterionStatus(specialCriterion, /[^A-Za-z0-9]/.test(password));
            });

            function updateCriterionStatus(element, isMet) {
                if (isMet) {
                    element.classList.remove('text-gray-500');
                    element.classList.add('text-green-600');
                } else {
                    element.classList.remove('text-green-600');
                    element.classList.add('text-gray-500');
                }
            }

            function calculatePasswordStrength(password) {
                // Initialize score and criteria count
                let score = 0;
                let criteriaCount = 0;

                // Check password length (at least 8 characters)
                if (password.length >= 8) {
                    criteriaCount++;
                }

                // Check for uppercase letters
                if (/[A-Z]/.test(password)) {
                    criteriaCount++;
                }

                // Check for lowercase letters
                if (/[a-z]/.test(password)) {
                    criteriaCount++;
                }

                // Check for numbers
                if (/[0-9]/.test(password)) {
                    criteriaCount++;
                }

                // Check for special characters
                if (/[^A-Za-z0-9]/.test(password)) {
                    criteriaCount++;
                }

                // Determine score based on criteria count and password length
                if (password.length === 0) {
                    score = 0;
                } else if (criteriaCount === 1) {
                    score = 1;
                } else if (criteriaCount === 2) {
                    score = 2;
                } else if (criteriaCount === 3 || criteriaCount === 4) {
                    score = 3;
                } else if (criteriaCount === 5) {
                    if (password.length >= 12) {
                        score = 4;
                    } else {
                        score = 3;
                    }
                }

                // Calculate percentage based on score
                const percentage = (score * 25) > 0 ? score * 25 : 5;

                // Determine label based on score
                let label;
                switch (score) {
                    case 0:
                        label = 'Quá dễ đoán';
                        break;
                    case 1:
                        label = 'Dễ đoán';
                        break;
                    case 2:
                        label = 'Tạm được';
                        break;
                    case 3:
                        label = 'Tương đối tốt';
                        break;
                    case 4:
                        label = 'Mạnh';
                        break;
                    default:
                        label = 'Quá dễ đoán';
                }

                return {
                    score: score,
                    percentage: percentage,
                    label: label
                };
            }
        });
    </script>
@endpush
