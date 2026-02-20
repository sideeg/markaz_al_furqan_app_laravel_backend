<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReviewLog;
use App\Models\Course;
use App\Models\User;
use App\Models\HifzLog;
use Illuminate\Support\Carbon;

class ReviewLogController extends Controller
{
    public function index(Request $request)
    {
        // $logs = ReviewLog::with(['student', 'sheikh'])
        //     ->when($request->student, fn($q) => $q->whereHas('student', fn($q2) => $q2->where('name', 'like', "%{$request->student}%")))
        //     ->when($request->sheikh, fn($q) => $q->whereHas('sheikh', fn($q2) => $q2->where('name', 'like', "%{$request->sheikh}%")))
        //     ->when($request->from, fn($q) => $q->whereDate('date', '>=', $request->from))
        //     ->when($request->to, fn($q) => $q->whereDate('date', '<=', $request->to))
        //     ->latest()
        //     ->paginate(15);

        $courses = Course::all();
        $sheikhs = User::withRole('sheikh')->get();
        $students = User::withRole('student')->get();

        $query = HifzLog::query()->with(['student', 'sheikh', 'course']);

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('sheikh_id')) {
            $query->where('sheikh_id', $request->sheikh_id);
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('surah')) {
            $query->where('start_surah', 'like', "%{$request->surah}%")
                  ->orWhere('end_surah', 'like', "%{$request->surah}%");
        }
        if ($request->filled('to') && !$request->filled('from')) {
            $query->whereDate('session_date', $request->to);
        }elseif ($request->filled('from') && !$request->filled('to')) {
            $query->whereDate('session_date', $request->from);
        } elseif ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('session_date', [$request->from, $request->to]);
        }
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('session_date', [$request->from, $request->to]);
        }

        $logs = $query->latest()->paginate(20);


        return view('admin.review_logs.index', compact('logs', 'courses', 'sheikhs', 'students'));
    }

    public function show(ReviewLog $review_log)
    {
        $log = $review_log->load(['student', 'sheikh']);
        return view('admin.review_logs.show', compact('log'));
    }

    public function destroy(ReviewLog $review_log)
    {
        $review_log->delete();
        return redirect()->route('admin.review_logs.index')->with('success', 'تم حذف سجل المراجعة بنجاح.');
    }
}
