<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HifzLog;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HifzLogController extends Controller
{
    public function index(Request $request)
    {
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
        if ($request->filled('start_date') && !$request->filled('end_date')) {
            $query->whereDate('session_date', $request->start_date);
        }elseif ($request->filled('end_date') && !$request->filled('start_date')) {
            $query->whereDate('session_date', $request->end_date);
        } elseif  ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('session_date', [$request->start_date, $request->end_date]);
        }

        $logs = $query->latest()->paginate(20);

        return view('admin.hifz_logs.index', compact('logs', 'courses', 'sheikhs', 'students'));
    }

    public function show(HifzLog $hifzLog)
    {
        return view('admin.hifz_logs.show', compact('hifzLog'));
    }

    public function destroy(HifzLog $hifzLog)
    {
        $hifzLog->delete();
        return redirect()->route('admin.hifz_logs.index')->with('success', 'تم حذف سجل الحفظ بنجاح');
    }
}
