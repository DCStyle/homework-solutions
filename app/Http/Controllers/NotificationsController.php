<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * Display all notifications for the user
     */
    public function index(Request $request)
    {
        // If this is an AJAX request checking for new notifications
        if ($request->ajax() && $request->has('check')) {
            $user = Auth::user();
            $unreadCount = $user->unreadNotifications->count();
            $latestNotifications = $user->notifications->take(5);
            
            // Get the latest unread notifications separately for toast display
            $newNotifications = $user->unreadNotifications
                ->filter(function ($notification) {
                    return $notification->created_at->gt(now()->subMinutes(1));
                });
            
            return response()->json([
                'unread_count' => $unreadCount,
                'has_new' => $newNotifications->count() > 0,
                'latest' => $latestNotifications->map(function ($notification) {
                    $data = [
                        'id' => $notification->id,
                        'title' => $notification->data['title'] ?? 'Thông báo mới',
                        'message' => $notification->data['message'] ?? '',
                        'read' => !is_null($notification->read_at),
                        'time' => $notification->created_at->diffForHumans(),
                        'link' => $notification->data['link'] ?? null
                    ];
                    
                    // Add job-specific details for bulk generation notifications
                    if (isset($notification->data['processed'])) {
                        $data['processed'] = $notification->data['processed'];
                        $data['failed'] = $notification->data['failed'];
                        $data['total'] = $notification->data['total'];
                        $data['history_id'] = $notification->data['history_id'] ?? null;
                    }
                    
                    return $data;
                }),
                // Provide the newest notifications for automatic toast display
                'new_notifications' => $newNotifications->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->data['title'] ?? 'Thông báo mới',
                        'message' => $notification->data['message'] ?? '',
                        'link' => $notification->data['link'] ?? null
                    ];
                })
            ]);
        }
        
        // Regular view for notifications page
        $notifications = Auth::user()->notifications;
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
} 