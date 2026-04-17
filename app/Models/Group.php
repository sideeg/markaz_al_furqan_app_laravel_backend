<?php
// ═══════════════════════════════════════════════════════════════════
// Path: app/Models/Group.php (Updated)
// ═══════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'course_id',
        'sheikh_id',
        'name',
        'description',
        'max_students',
        'status',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    /**
     * The course this group belongs to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The sheikh/teacher who leads this group
     */
    public function sheikh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sheikh_id');
    }

    /**
     * Students in this group
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'group_students',
            'group_id',
            'student_id'
        )
        ->withTimestamps()
        ->withPivot('assigned_at');
    }

    // ─────────────────────────────────────────────────────────────
    // Helper Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Attach a student to this group
     * This triggers the GroupStudentObserver!
     */
    public function addStudent(Student $student): void
    {
        // ✅ This will trigger GroupStudentObserver::attaching()
        $this->students()->attach($student->id, [
            'assigned_at' => now(),
        ]);
    }

    /**
     * Remove a student from this group
     * This triggers the GroupStudentObserver!
     */
    public function removeStudent(Student $student): void
    {
        // ✅ This will trigger GroupStudentObserver::detaching()
        $this->students()->detach($student->id);
    }

    /**
     * Sync multiple students
     * This triggers GroupStudentObserver for attach/detach!
     */
    public function syncStudents(array $studentIds): void
    {
        // ✅ This will trigger GroupStudentObserver::attaching() for new students
        // ✅ This will trigger GroupStudentObserver::detaching() for removed students
        $this->students()->sync($studentIds);
    }
}




