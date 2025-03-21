<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.externalStylesheets')

    @vite('resources/css/app.css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ setting('site_name', 'Homework Solutions') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Roboto" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    <!-- Styles -->
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div id="app">
        <div class="flex h-screen overflow-hidden">
            @include('admin_layouts.sidebar')

            <!-- ===== Content Area Start ===== -->
            <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
                @include('admin_layouts.header')

                <!-- ===== Main Content Start ===== -->
                <main>
                    <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                        @yield('content')
                    </div>
                </main>
                <!-- ===== Main Content End ===== -->
            </div>
            <!-- ===== Content Area End ===== -->
        </div>
    </div>

    <!-- Include external scripts -->
    @include('layouts.externalScripts');
    @stack('scripts')
    
    <script>
        /**
         * Display a toast notification
         * @param {Object} options - The notification options
         * @param {string} options.title - The notification title
         * @param {string} options.message - The notification message
         * @param {string} options.type - The notification type (success, warning, error, info)
         * @param {number} options.duration - The duration in milliseconds
         */
        function showNotification(options = {}) {
            const { 
                title = 'Thông báo', 
                message = '', 
                type = 'success', 
                duration = 5000 
            } = options;
            
            // Create toast container if it doesn't exist
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'fixed top-4 right-4 z-50 flex flex-col gap-3 max-w-md';
                document.body.appendChild(toastContainer);
            }
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = 'rounded-lg shadow-lg p-4 flex items-start gap-3 transform translate-x-full transition-transform duration-300 ease-out';
            
            // Set background color based on type
            switch (type) {
                case 'success':
                    toast.classList.add('bg-green-50', 'border-l-4', 'border-green-500', 'text-green-800');
                    break;
                case 'warning':
                    toast.classList.add('bg-yellow-50', 'border-l-4', 'border-yellow-500', 'text-yellow-800');
                    break;
                case 'error':
                    toast.classList.add('bg-red-50', 'border-l-4', 'border-red-500', 'text-red-800');
                    break;
                case 'info':
                default:
                    toast.classList.add('bg-indigo-50', 'border-l-4', 'border-indigo-500', 'text-indigo-800');
                    break;
            }
            
            // Set icon based on type
            let icon = 'mdi-information';
            switch (type) {
                case 'success': icon = 'mdi-check-circle'; break;
                case 'warning': icon = 'mdi-alert'; break;
                case 'error': icon = 'mdi-alert-circle'; break;
            }
            
            // Add toast content
            toast.innerHTML = `
                <div class="flex-shrink-0">
                    <span class="iconify text-2xl" data-icon="${icon}"></span>
                </div>
                <div class="flex-1">
                    <div class="font-medium">${title}</div>
                    <div class="text-sm mt-1">${message}</div>
                </div>
                <button class="text-gray-400 hover:text-gray-600">
                    <span class="iconify" data-icon="mdi-close"></span>
                </button>
            `;
            
            // Add to container
            toastContainer.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 10);
            
            // Set up close button
            const closeBtn = toast.querySelector('button');
            closeBtn.addEventListener('click', () => {
                removeToast(toast);
            });
            
            // Auto remove after duration
            if (duration > 0) {
                setTimeout(() => {
                    removeToast(toast);
                }, duration);
            }
            
            // Function to remove toast with animation
            function removeToast(toastElement) {
                toastElement.classList.add('translate-x-full');
                setTimeout(() => {
                    toastElement.remove();
                    
                    // Remove container if empty
                    if (toastContainer.children.length === 0) {
                        toastContainer.remove();
                    }
                }, 300);
            }
            
            return toast;
        }
    </script>
</body>
</html>
