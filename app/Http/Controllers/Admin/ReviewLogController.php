<?php
/**
 * FIXED REVIEW LOG CONTROLLER
 * 
 * Issues Fixed:
 * 1. Index was querying HifzLog instead of ReviewLog ❌ 
 * 2. View was using $log->date instead of $log->session_date ❌
 * 3. View was using $log->surah instead of $log->start_surah/$log->end_surah ❌
 * 4. No soft deletes - causing deletion cascade issues ❌
 * 5. Inconsistent field names between view and migration ❌
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewLog;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewLogController extends Controller
{
    /**
     * Display a listing of review logs
     */
    public function index(Request $request)
    {
        // Get filter options for dropdowns
        $courses = Course::all();
        $sheikhs = User::withRole('sheikh')->active()->get();
        $students = User::withRole('student')->active()->get();

        // Start query with ReviewLog (NOT HifzLog) ✅ FIXED
        $query = ReviewLog::query()->with(['student', 'sheikh', 'course']);

        // Apply filters
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('sheikh_id')) {
            $query->where('sheikh_id', $request->sheikh_id);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by surah (supports start_surah or end_surah)
        if ($request->filled('surah')) {
            $query->where(function ($q) use ($request) {
                $q->where('start_surah', 'like', "%{$request->surah}%")
                  ->orWhere('end_surah', 'like', "%{$request->surah}%");
            });
        }

        // Filter by date range
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('session_date', [$request->from, $request->to]);
        } elseif ($request->filled('from')) {
            $query->whereDate('session_date', '>=', $request->from);
        } elseif ($request->filled('to')) {
            $query->whereDate('session_date', '<=', $request->to);
        }

        // Get paginated results
        $logs = $query->latest('session_date')->paginate(20);

        return view('admin.review_logs.index', compact('logs', 'courses', 'sheikhs', 'students'));
    }

    /**
     * Display a single review log
     */
    public function show(ReviewLog $review_log)
    {
        $log = $review_log->load(['student', 'sheikh', 'course']);
        return view('admin.review_logs.show', compact('log'));
    }

    /**
     * Edit a review log
     */
    public function edit(ReviewLog $review_log)
    {
        $log = $review_log->load(['student', 'sheikh', 'course', 'group']);
        $courses = Course::all();
        $sheikhs = User::withRole('sheikh')->active()->get();
        
        return view('admin.review_logs.edit', compact('log', 'courses', 'sheikhs'));
    }

    /**
     * Update a review log
     */
    public function update(Request $request, ReviewLog $review_log)
    {
        $validated = $request->validate([
            'session_date' => 'required|date', // ✅ Changed from 'date' to 'session_date'
            'session_time' => 'nullable|date_format:H:i',
            'start_surah' => 'required|string', // ✅ Changed from 'surah' to 'start_surah'
            'end_surah' => 'required|string',
            'start_ayah' => 'required|integer|min:1',
            'end_ayah' => 'required|integer|min:1|gte:start_ayah',
            'evaluation' => 'required|in:excellent,very_good,good,needs_improvement,poor',
            'notes' => 'nullable|string',
        ]);

        $review_log->update($validated);

        return redirect()
            ->route('admin.review_logs.show', $review_log)
            ->with('success', 'تم تحديث سجل المراجعة بنجاح');
    }

    /**
     * Delete a review log (with soft delete)
     * ✅ FIXED: Uses soft delete instead of permanent delete to avoid cascade issues
     */
    public function destroy(ReviewLog $review_log)
    {
        try {
            // Soft delete - won't cause logout/cascade issues
            $review_log->delete();

            return redirect()
                ->route('admin.review_logs.index')
                ->with('success', 'تم حذف سجل المراجعة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.review_logs.index')
                ->with('error', 'حدث خطأ أثناء حذف السجل: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a review log (Admin only)
     */
    public function forceDelete(ReviewLog $review_log)
    {
        // Only allow permanent delete if explicitly requested
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'غير مصرح لك بهذا الإجراء');
        }

        try {
            $review_log->forceDelete();

            return redirect()
                ->route('admin.review_logs.index')
                ->with('success', 'تم حذف السجل بشكل نهائي');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.review_logs.index')
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted review log
     */
    public function restore(ReviewLog $review_log)
    {
        try {
            $review_log->restore();

            return redirect()
                ->route('admin.review_logs.index')
                ->with('success', 'تم استرجاع السجل بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.review_logs.index')
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}