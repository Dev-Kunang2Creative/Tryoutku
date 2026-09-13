<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionProgress extends Model
{
    /**
     * Consecutive correct answers required before a question stops being asked.
     */
    public const MASTERY_STREAK = 2;

    protected $table = 'question_progress';

    protected $fillable = [
        'user_id',
        'question_id',
        'correct_streak',
        'times_correct',
        'times_wrong',
        'due_on',
        'last_answered_on',
        'mastered_at',
    ];

    protected function casts(): array
    {
        return [
            'correct_streak' => 'integer',
            'times_correct' => 'integer',
            'times_wrong' => 'integer',
            'due_on' => 'date',
            'last_answered_on' => 'date',
            'mastered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function isMastered(): bool
    {
        return $this->mastered_at !== null;
    }
}
