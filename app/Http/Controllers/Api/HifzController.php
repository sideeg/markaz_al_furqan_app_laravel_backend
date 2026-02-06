<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HifzLog;
use App\Models\ReviewLog;
use App\Models\Course;

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
        $progress = HifzLog::where('student_id', auth()->user()->id)
            
            ->groupBy('course_id')
            ->get();
        return response()->json($progress);
    }

    public function myStatistics(Request $request)
{
    $userId = auth()->user()->id;

    $statistics = HifzLog::where('student_id', $userId)
        ->selectRaw('
            count(*) as count,
            avg(CASE 
                WHEN evaluation = "excellent" THEN 5 
                WHEN evaluation = "very_good" THEN 4 
                WHEN evaluation = "good" THEN 3 
                WHEN evaluation = "needs_improvement" THEN 2 
                WHEN evaluation = "poor" THEN 1 
                ELSE 0 
            END) as avg_evaluation
        ')
        ->first();

    // Calculate total ayahs manually if needed, or simply return the raw counts
    // for the frontend to handle specific Sura logic.
    return response()->json([
        'total_sessions' => $statistics->count,
        'average_evaluation' => round($statistics->avg_evaluation, 2),
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
        $log = new HifzLog();
        $log->student_id = $request->student_id;
        $log->group_id = $request->group_id;
        $log->sheikh_id = $request->sheikh_id; // Assuming group_id is provided
        $log->course_id = $request->course_id;
        $log->session_date = Date('Y-m-d' );
        $log->session_time = Date('H:i:s');
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
        $log->session_date = Date('Y-m-d');
        $log->session_time = Date('H:i:s');
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

    public function destroyReview(ReviewLog $log)
    {
        if ($log->sheikh_id != auth()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $log->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
