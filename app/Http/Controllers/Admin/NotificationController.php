<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Fetch all notifications (assuming a Notification model exists)
        $notifications = \App\Models\Notification::all();
        return response()->json($notifications);
    }

    public function store(Request $request)
    {
        // Validate and create a new notification
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            // Add other fields as needed
        ]);

        $notification = \App\Models\Notification::create($validated);

        return response()->json($notification, 201);
    }

    public function show($id)
    {
        $notification = \App\Models\Notification::findOrFail($id);
        return response()->json($notification);
    }

    public function update(Request $request, $id)
    {
        $notification = \App\Models\Notification::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            // Add other fields as needed
        ]);

        $notification->update($validated);

        return response()->json($notification);
    }

    public function destroy($id)
    {
        $notification = \App\Models\Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully.']);
    }
}
