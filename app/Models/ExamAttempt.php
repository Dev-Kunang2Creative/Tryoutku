<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'tryout_id',
        'session_date',
        'started_at',
        'deadline_at',
        'completed_at',
        'status',
        'total_score',
        'max_score',
        'percentage',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'started_at' => 'datetime',
            'deadline_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    /**
     * The questions drawn for this session, in the order they were served.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class)->orderBy('id');
    }

    public function remainingSeconds(): int
    {
        if ($this->status !== 'in_progress') {
            return 0;
        }

        $now = Carbon::now();

        if ($now->greaterThanOrEqualTo($this->deadline_at)) {
            return 0;
        }

        return $now->diffInSeconds($this->deadline_at, false);
    }

    public function isExpired(): bool
    {
        return $this->status === 'in_progress' && Carbon::now()->greaterThan($this->deadline_at);
    }

    /**
     * Sesi yang belum disentuh sama sekali. Menekan tombol mulai lalu tidak
     * menjawab apa pun tidak boleh menghanguskan jatah latihan hari itu, jadi
     * sesi semacam ini diperlakukan seolah tidak pernah ada.
     */
    public function isUntouched(): bool
    {
        return $this->answers()->whereNotNull('question_option_id')->doesntExist();
    }

    /**
     * Score only the questions served in this session, then roll each one
     * forward or back in the mastery schedule.
     */
    public function calculateFinalScore(): void
    {
        $this->load(['answers.question', 'answers.selectedOption']);

        $totalEarned = 0.0;
        $maxPossible = 0.0;

        foreach ($this->answers as $answer) {
            $weight = (float) $answer->question->score_weight;
            $maxPossible += $weight;

            $isCorrect = $answer->selectedOption && $answer->selectedOption->is_correct;
            $earned = $isCorrect ? $weight : 0.0;

            $answer->update([
                'is_correct' => $isCorrect,
                'score_earned' => $earned,
            ]);

            $totalEarned += $earned;
        }

        $percentage = $maxPossible > 0 ? ($totalEarned / $maxPossible) * 100 : 0;

        $this->update([
            'completed_at' => Carbon::now(),
            'status' => 'completed',
            'total_score' => $totalEarned,
            'max_score' => $maxPossible,
            'percentage' => round($percentage, 2),
        ]);

        $this->applyMasteryProgress();
    }

    /**
     * A correct answer advances the streak; anything else resets it. Whatever is
     * not mastered yet becomes due again tomorrow.
     */
    private function applyMasteryProgress(): void
    {
        $today = ($this->completed_at ?? Carbon::now())->copy();

        foreach ($this->answers as $answer) {
            $progress = QuestionProgress::firstOrNew(
                [
                    'user_id' => $this->user_id,
                    'question_id' => $answer->question_id,
                ],
                [
                    'correct_streak' => 0,
                    'times_correct' => 0,
                    'times_wrong' => 0,
                ]
            );

            if ($answer->is_correct) {
                $progress->correct_streak++;
                $progress->times_correct++;
            } else {
                $progress->correct_streak = 0;

                // An unanswered question was never really attempted, so it is not
                // counted as a mistake — it just stays in the rotation.
                if ($answer->question_option_id) {
                    $progress->times_wrong++;
                }
            }

            $progress->last_answered_on = $today->toDateString();

            if ($progress->correct_streak >= QuestionProgress::MASTERY_STREAK) {
                $progress->mastered_at = $today;
                $progress->due_on = null;
            } else {
                $progress->mastered_at = null;
                $progress->due_on = $today->copy()->addDay()->toDateString();
            }

            $progress->save();
        }
    }

    /**
     * @return array{correct: int, wrong: int, empty: int, total: int}
     */
    public function answerBreakdown(): array
    {
        $total = $this->answers->count();
        $correct = $this->answers->where('is_correct', true)->count();
        $empty = $this->answers->whereNull('question_option_id')->count();

        return [
            'correct' => $correct,
            'wrong' => $total - $correct - $empty,
            'empty' => $empty,
            'total' => $total,
        ];
    }
}
