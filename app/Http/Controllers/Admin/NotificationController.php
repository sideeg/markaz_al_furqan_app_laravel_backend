<?php
// ═══════════════════════════════════════════════════════════════════
// Path: app/Http/Controllers/Admin/NotificationController.php
// ═══════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Models\Notification;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * GET /admin/notifications
     * Show paginated notifications for admin dashboard
     */
    public function index()
    {
        // ✅ FIXED: Use method that exists
        $notifications = $this->notificationService->getAdminNotifications(20);
        
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    /**
     * POST /admin/notifications
     * Store a new broadcast notification
     */
    public function store(Request $request)
    {
        // ✅ Validate
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target' => 'required|in:students,teachers,both',
        ]);

        try {
           
            
            $notification = $this->notificationService->createAndSendNotification(
                auth()->user()->id,
                $validated['title'],
                $validated['message'],

                'custom_broadcast',
                $validated['target']  // 'students' | 'teachers' | 'both'
            );

            return back()->with('success', 'تم إرسال الإشعار بنجاح! ✓');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification broadcast error: ' . $e->getMessage());
            
            return back()
                ->with('error', 'حدث خطأ أثناء إرسال الإشعار: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id){
        $notification = Notification::findOrFail($id);
        return view('admin.notifications.show', compact('notification'));
    }

    public function edit($id){
        $notification = Notification::findOrFail($id);
        return view('admin.notifications.edit', compact('notification'));
    }

    public function update(Request $request, $id){
        $notification = Notification::findOrFail($id);
        $notification->update($request->all());
        return back()->with('success', 'تم تحديث الإشعار بنجاح');
    }

    /**
     * DELETE /admin/notifications/{id}
     * Delete a notification (from history)
     */
    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return back()->with('success', 'تم حذف الإشعار بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ في الحذف: ' . $e->getMessage());
        }
    }
}


