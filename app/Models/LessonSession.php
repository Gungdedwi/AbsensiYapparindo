<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'teaching_assignment_id',
        'date',
        'time_start',
        'time_end',
        'period_start',
        'period_end',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function journal()
    {
        return $this->hasOne(LessonJournal::class);
    }

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function getAttendanceSummaryAttribute(): array
    {
        $counts = [
            'total' => $this->attendances->count(),
            'H' => 0,
            'S' => 0,
            'I' => 0,
            'A' => 0,
            'T' => 0,
            'DW' => 0,
        ];

        foreach ($this->attendances as $attendance) {
            if (isset($counts[$attendance->status])) {
                $counts[$attendance->status]++;
            }
        }

        return $counts;
    }
}

