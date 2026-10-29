<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_session_id',
        'meeting_number',
        'topic',
        'notes',
        'submitted_at',
        'submitted_by',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'meeting_number' => 'integer',
        ];
    }

    public function lessonSession()
    {
        return $this->belongsTo(LessonSession::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}

