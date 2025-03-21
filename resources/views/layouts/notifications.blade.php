@auth
    <div id="notifications-container" class="relative">
        <button id="notifications-btn" class="text-gray-500 hover:text-indigo-600 relative" aria-label="Notifications">
            <span class="iconify text-2xl" data-icon="mdi-bell-outline"></span>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 text-xs text-white flex items-center justify-center">
                    {{ auth()->user()->unreadNotifications->count() }}
                </span>
            @endif
        </button>

        <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50 border border-gray-200 max-h-96 overflow-y-auto">
            <div class="p-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-medium text-gray-800">Thông Báo</h3>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <button id="mark-all-read" class="text-xs text-indigo-600 hover:text-indigo-800">
                        Đánh dấu tất cả đã đọc
                    </button>
                @endif
            </div>

            <div id="notifications-list" class="divide-y divide-gray-100">
                @forelse(auth()->user()->notifications()->take(10)->get() as $notification)
                    <div class="notification-item p-3 hover:bg-gray-50 transition {{ $notification->read_at ? '' : 'bg-indigo-50' }}" data-id="{{ $notification->id }}">
                        <div class="flex justify-between items-start">
                            <span class="font-medium text-gray-800">{{ $notification->data['title'] ?? 'Thông báo mới' }}</span>
                            <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                        @if(isset($notification->data['link']))
                            <a href="{{ $notification->data['link'] }}" class="text-xs text-indigo-600 hover:text-indigo-800 mt-2 inline-block">
                                Xem chi tiết
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500">
                        Không có thông báo nào
                    </div>
                @endforelse
            </div>

            @if(auth()->user()->notifications()->count() > 10)
                <div class="p-2 text-center border-t border-gray-100">
                    <a href="/notifications" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Xem tất cả thông báo
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationsBtn = document.getElementById('notifications-btn');
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            const notificationsContainer = document.getElementById('notifications-container');
            const markAllReadBtn = document.getElementById('mark-all-read');
            const notificationItems = document.querySelectorAll('.notification-item');

            // Toggle dropdown
            notificationsBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationsDropdown.classList.toggle('hidden');
            });

            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationsContainer.contains(e.target)) {
                    notificationsDropdown.classList.add('hidden');
                }
            });

            // Mark as read when clicking notification
            notificationItems.forEach(item => {
                item.addEventListener('click', function() {
                    const id = this.dataset.id;
                    fetch(`/notifications/${id}/mark-as-read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    }).then(response => {
                        if (response.ok) {
                            this.classList.remove('bg-indigo-50');
                            // Update counter
                            const counter = document.querySelector('#notifications-btn span.rounded-full');
                            if (counter) {
                                const count = parseInt(counter.textContent) - 1;
                                if (count <= 0) {
                                    counter.remove();
                                } else {
                                    counter.textContent = count;
                                }
                            }
                        }
                    });
                });
            });

            // Mark all as read
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    fetch('/notifications/mark-all-read', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    }).then(response => {
                        if (response.ok) {
                            document.querySelectorAll('.notification-item').forEach(item => {
                                item.classList.remove('bg-indigo-50');
                            });
                            const counter = document.querySelector('#notifications-btn span.rounded-full');
                            if (counter) {
                                counter.remove();
                            }
                        }
                    });
                });
            }

            // Check for new notifications periodically
            let lastNotificationCount = document.querySelectorAll('.notification-item').length;
            let unreadCount = document.querySelector('#notifications-btn span.rounded-full') ? 
                parseInt(document.querySelector('#notifications-btn span.rounded-full').textContent) : 0;
            
            // Function to check for new notifications
            function checkForNewNotifications() {
                fetch('/notifications?check=true', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // If there are new notifications
                    if (data.unread_count > unreadCount) {
                        // Update the badge
                        const counter = document.querySelector('#notifications-btn span.rounded-full');
                        if (counter) {
                            counter.textContent = data.unread_count;
                        } else {
                            const newCounter = document.createElement('span');
                            newCounter.className = 'absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 text-xs text-white flex items-center justify-center';
                            newCounter.textContent = data.unread_count;
                            notificationsBtn.appendChild(newCounter);
                        }
                        
                        // Show toast notifications for new notifications
                        if (typeof showNotification === 'function' && data.new_notifications && data.new_notifications.length > 0) {
                            // Display a toast for each new notification
                            data.new_notifications.forEach(notification => {
                                showNotification({
                                    title: notification.title,
                                    message: notification.message,
                                    type: 'info',
                                    duration: 8000
                                });
                                
                                // If the notification is about a completed AI job, update the notifications list
                                if (notification.title.includes('Bulk SEO Generation')) {
                                    // Refresh the notifications list
                                    updateNotificationsList(data.latest);
                                }
                            });
                        }
                        
                        unreadCount = data.unread_count;
                    }
                })
                .catch(error => console.error('Error checking for notifications:', error));
            }
            
            // Function to update the notifications list with new data
            function updateNotificationsList(notifications) {
                const notificationsList = document.getElementById('notifications-list');
                if (!notificationsList) return;
                
                // Clear the current list
                notificationsList.innerHTML = '';
                
                if (notifications.length === 0) {
                    notificationsList.innerHTML = `
                        <div class="p-4 text-center text-gray-500">
                            Không có thông báo nào
                        </div>
                    `;
                    return;
                }
                
                // Add each notification to the list
                notifications.forEach(notification => {
                    const item = document.createElement('div');
                    item.className = `notification-item p-3 hover:bg-gray-50 transition ${notification.read ? '' : 'bg-indigo-50'}`;
                    item.dataset.id = notification.id;
                    
                    let itemHtml = `
                        <div class="flex justify-between items-start">
                            <span class="font-medium text-gray-800">${notification.title}</span>
                            <span class="text-xs text-gray-500">${notification.time}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                    `;
                    
                    if (notification.link) {
                        itemHtml += `
                            <a href="${notification.link}" class="text-xs text-indigo-600 hover:text-indigo-800 mt-2 inline-block">
                                Xem chi tiết
                            </a>
                        `;
                    }
                    
                    item.innerHTML = itemHtml;
                    
                    // Add click handler for marking as read
                    item.addEventListener('click', function() {
                        const id = this.dataset.id;
                        fetch(`/notifications/${id}/mark-as-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json'
                            }
                        }).then(response => {
                            if (response.ok) {
                                this.classList.remove('bg-indigo-50');
                                // Update counter
                                const counter = document.querySelector('#notifications-btn span.rounded-full');
                                if (counter) {
                                    const count = parseInt(counter.textContent) - 1;
                                    if (count <= 0) {
                                        counter.remove();
                                    } else {
                                        counter.textContent = count;
                                    }
                                }
                            }
                        });
                    });
                    
                    notificationsList.appendChild(item);
                });
            }
            
            // Check every 30 seconds
            setInterval(checkForNewNotifications, 30000);
        });
    </script>
@endauth
