<?php
// Path: app/Http/Controllers/Api/NotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * GET /api/notifications
     *
     * THE FIX: Transform each notification to include this user's
     * read status from the pivot table. Without this, Flutter always
     * receives read_at: null on every refresh, making everything appear
     * unread — even after successfully marking as read on the server.
     */
    public function index(Request $request)
    {
        $userId  = auth()->id();
        $perPage = $request->get('per_page', 15);

        $notifications = $this->notificationService->getUserNotifications($userId, $perPage);

        // Map each notification to include per-user is_read / read_at
        // recipients are already eager-loaded in getUserNotifications()
        // so this is zero extra queries
        $items = collect($notifications->items())->map(function ($notification) {
            $data      = $notification->toArray();
            $recipient = $notification->recipients->first(); // eager-loaded

            $data['is_read'] = $recipient ? (bool) $recipient->pivot->is_read : false;
            $data['read_at'] = $recipient ? $recipient->pivot->read_at       : null;

            // Remove the loaded recipients array — Flutter doesn't need it
            unset($data['recipients']);

            return $data;
        });

        return response()->json([
            'success'    => true,
            'data'       => $items,
            'pagination' => [
                'total'        => $notifications->total(),
                'per_page'     => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/notifications/unread/count
     */
    public function unreadCount()
    {
        $userId = auth()->id();
        $count  = $this->notificationService->getUnreadCount($userId);

        return response()->json([
            'success'      => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * POST /api/notifications/{id}/read
     */
    public function markAsRead($notificationId)
    {
        try {
            $userId = auth()->id();
            $this->notificationService->markAsRead((int) $notificationId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'تم وضع علامة على الإشعار كمقروء',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'حدث خطأ: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/notifications/read-all
     */
    public function markAllAsRead()
    {
        try {
            $userId = auth()->id();
            $this->notificationService->markAllAsRead($userId);

            return response()->json([
                'success' => true,
                'message' => 'تم وضع علامة على جميع الإشعارات كمقروءة',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'حدث خطأ: ' . $e->getMessage(),
            ], 400);
        }
    }
}