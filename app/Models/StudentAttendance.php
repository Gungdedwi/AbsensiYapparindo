<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $table = 'student_attendance';

    protected $fillable = [
        'lesson_session_id',
        'student_id',
        'status',
        'notes',
    ];

    public function lessonSession()
    {
        return $this->belongsTo(LessonSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

