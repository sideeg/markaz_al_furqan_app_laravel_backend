<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications;

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function delete(Request $request, Notification $notification)
    {
        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }
}

