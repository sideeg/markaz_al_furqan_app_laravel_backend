<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HifzLog;
use App\Models\ReviewLog;
use App\Models\Course;
use App\Models\Group;
use Illuminate\Support\Facades\Log;


class HifzController extends Controller
{
    public function myLogs(Request $request)
    {
        $logs = HifzLog::where('student_id', auth()->user()->id)
            ->orderBy('session_date', 'desc')
            ->get();
        return response()->json($logs);
    }

    public function myLogsByCourse(Request $request, Course $course)
    {
        $logs = HifzLog::where('student_id', auth()->user()->id)
            ->where('course_id', $course->id)
            ->orderBy('session_date', 'desc')
            ->get();
        return response()->json($logs);
    }

   public function myProgress(Request $request)
    {
        $userId = auth()->id();

        // تجميع تقدم الطالب في كل دورة بشكل مفصل
        $progress = HifzLog::where('student_id', $userId)
            ->with('course:id,name') // نحضر اسم الدورة مباشرة لتخفيف العبء عن Flutter
            ->selectRaw('
                course_id, 
                COUNT(id) as sessions_count,
                SUM(end_ayah - start_ayah + 1) as total_ayahs_memorized,
                AVG(CASE 
                    WHEN evaluation = "excellent" THEN 100 
                    WHEN evaluation = "very_good" THEN 85 
                    WHEN evaluation = "good" THEN 70 
                    WHEN evaluation = "needs_improvement" THEN 50 
                    WHEN evaluation = "poor" THEN 30 
                    ELSE 0 
                END) as course_evaluation_percent
            ')
            ->groupBy('course_id')
            ->get()
            ->map(function ($item) {
                return [
                    'course_id' => $item->course_id,
                    'course_name' => $item->course ? $item->course->name : 'دورة محذوفة',
                    'sessions_count' => (int) $item->sessions_count,
                    'total_ayahs' => (int) $item->total_ayahs_memorized,
                    'evaluation_percent' => round($item->course_evaluation_percent, 1)
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $progress
        ]);
    }

    public function myStatistics(Request $request)
    {
        $userId = auth()->id();

        $statistics = HifzLog::where('student_id', $userId)
            ->selectRaw('
                COUNT(*) as total_sessions,
                SUM(end_ayah - start_ayah + 1) as total_ayahs_memorized,
                AVG(CASE 
                    WHEN evaluation = "excellent" THEN 5 
                    WHEN evaluation = "very_good" THEN 4 
                    WHEN evaluation = "good" THEN 3 
                    WHEN evaluation = "needs_improvement" THEN 2 
                    WHEN evaluation = "poor" THEN 1 
                    ELSE 0 
                END) as avg_evaluation
            ')
            ->first();
        // عدد الآيات التقريبي في القرآن هو 6236 آية
        $totalQuranAyahs = 6236;
        $memorizedAyahs = (int) $statistics->total_ayahs_memorized;
        $quranCompletionPercentage = ($memorizedAyahs / $totalQuranAyahs) * 100;

        return response()->json([
            'success' => true,
            'data' => [
                'total_sessions' => (int) $statistics->total_sessions,
                'total_ayahs_memorized' => (int)$memorizedAyahs,
                'average_evaluation_out_of_5' =>round($statistics->avg_evaluation, 1),
                'quran_completion_percentage' => round($quranCompletionPercentage, 2),
            ]
        ]);
    }
    public function index(Request $request)
    {
        $logs = HifzLog::where('sheikh_id', auth()->user()->id)
        ->where('course_id', $request->course_id)->where('student_id', $request->student_id)
            ->orderBy('session_date', 'desc')
            ->paginate(10);
        return response()->json($logs);
    }

    public function store(Request $request)
    {
        $course = Course::find($request->course_id);
        if (!$course) {
            return response()->json([
                'error' => 'الدورة المختارة محذوفة أو غير موجودة'
            ], 422);
        }

        // ✅ Validate group belongs to course
        $group = Group::where('id', $request->group_id)
                    ->where('course_id', $request->course_id)
                    ->first();
        if (!$group) {
            return response()->json([
                'error' => 'المجموعة غير مرتبطة بهذه الدورة'
            ], 422);
        }
        $log = new HifzLog();
        $log->student_id = $request->student_id;
        $log->group_id = $request->group_id;
        $log->sheikh_id = $request->sheikh_id; // Assuming group_id is provided
        $log->course_id = $request->course_id;
        $log->session_date = $request->session_date ?? date('Y-m-d');
        $log->session_time = $request->session_time ?? date('H:i:s');
        $log->start_surah = $request->start_surah;
        $log->end_surah = $request->end_surah;
        $log->start_ayah = $request->start_ayah;
        $log->end_ayah = $request->end_ayah;
        $log->evaluation = $request->evaluation;
        $log->notes = $request->notes;
        $log->save();
        return response()->json([
            'success' => true,
            'message' => 'Hifz log created successfully',
            'data' => [
                'id' => $log->id,
                'log' => $log
            ]
        ])->setStatusCode(201, 'Hifz log created successfully');
    }

    public function show(HifzLog $log, int $logId)
    {
        $log = HifzLog::find($logId);
        
        // If the log is not found, return a 404 error
        if (!$log) {
            return response()->json(['error' => 'Log not found'], 404);
        }
    
        // Check if the log belongs to the authenticated user
        if ($log->sheikh_id != auth()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return response()->json($log);
    }

    public function update(Request $request, int $logId)
    {
        $log = HifzLog::findOrFail($logId);
        
        // Check if the log belongs to the authenticated user
        if ($log->sheikh_id != auth()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if ($request->has('session_date')) {
        $log->session_date = $request->session_date;
        }
        if ($request->has('session_time')) {
            $log->session_time = $request->session_time;
        }
        
        $log->start_surah = $request->start_surah;
        $log->end_surah = $request->end_surah;
        $log->start_ayah = $request->start_ayah;
        $log->end_ayah = $request->end_ayah;
        $log->evaluation = $request->evaluation;
        $log->notes = $request->notes;
        $log->save();
        return response()->json([
            'success' => true,
            'message' => 'Hifz log updated successfully',
            'data' => [
                'id' => $log->id,
                'log' => $log
            ]
        ])->setStatusCode(200, 'Hifz log updated successfully');
    }

    public function destroy(HifzLog $log,int $logId)
    {
        $log = HifzLog::find($logId);
        
        
        // If the log is not found, return a 404 error
        if (!$log) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        // Check if the log belongs to the authenticated user
        if ($log->sheikh_id != auth()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Delete the log and return a success response
    
        $log->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function storeReview(Request $request)
    {
        $log = new ReviewLog();
        $log->student_id = $request->student_id;
        $log->group_id = $request->group_id;
        $log->sheikh_id = $request->sheikh_id; // Assuming group_id is provided
        $log->course_id = $request->course_id;
         $log->session_date = $request->session_date ?? date('Y-m-d');  // ✅ FIX
        $log->session_time = $request->session_time ?? date('H:i:s');  // ✅ FIX
        $log->start_surah = $request->start_surah;
        $log->end_surah = $request->end_surah;
        $log->start_ayah = $request->start_ayah;
        $log->end_ayah = $request->end_ayah;
        $log->evaluation = $request->evaluation;
        $log->notes = $request->notes;
        $log->save();
        return response()->json($log);
    }

    public function reviewIndex(Request $request)
    {
        $logs = ReviewLog::where('sheikh_id', auth()->user()->id)
        ->where('course_id', $request->course_id)->where('student_id', $request->student_id)
            ->orderBy('session_date', 'desc')
            ->paginate(10);
        return response()->json($logs);
    }

    public function updateReview(Request $request, int $logId)
    {
        $log = ReviewLog::findOrFail($logId);
        
        // Check if the log belongs to the authenticated user
        if ($log->sheikh_id != auth()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        Log::info($request->all());
        if ($request->has('session_date')) {
        $log->session_date = $request->session_date;
        }
        if ($request->has('session_time')) {
            $log->session_time = $request->session_time;
        }
        
        $log->start_surah = $request->start_surah;
        $log->end_surah = $request->end_surah;
        $log->start_ayah = $request->start_ayah;
        $log->end_ayah = $request->end_ayah;
        $log->evaluation = $request->evaluation;
        $log->notes = $request->notes;
        $log->save();
        return response()->json([
            'success' => true,
            'message' => 'Review log updated successfully',
            'data' => [
                'id' => $log->id,
                'log' => $log
            ]
        ])->setStatusCode(200, 'Review log updated successfully');
    }

    public function destroyReview(HifzLog $log,int $logId)
    {
        
        
        $log = ReviewLog::find($logId);
        
        // If the log is not found, return a 404 error
        if (!$log) {
            return response()->json(['error' => 'Log not found'], 404);
        }
        $log->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
