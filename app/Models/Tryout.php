<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tryout extends Model
{
    /**
     * The app is deliberately limited to two practice packages.
     */
    public const MAX_PACKAGES = 2;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'subject',
        'session_type',
        'duration_minutes',
        'questions_per_session',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'questions_per_session' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tryout) {
            if (empty($tryout->slug)) {
                $tryout->slug = Str::slug($tryout->title).'-'.Str::random(5);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order', 'asc');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /**
     * Questions to serve in today's session: everything already seen but not yet
     * mastered and scheduled for today or earlier, topped up with questions the
     * user has never met. Weakest questions come first so they get consolidated.
     */
    public function dueQuestionsFor(User $user, ?Carbon $date = null): Collection
    {
        $date = ($date ?? Carbon::today())->toDateString();
        $limit = max(1, (int) $this->questions_per_session);

        $review = $this->questions()
            ->reorder()
            ->join('question_progress', function ($join) use ($user) {
                $join->on('question_progress.question_id', '=', 'questions.id')
                    ->where('question_progress.user_id', '=', $user->id);
            })
            ->whereNull('question_progress.mastered_at')
            ->whereDate('question_progress.due_on', '<=', $date)
            ->orderBy('question_progress.correct_streak')
            ->orderByDesc('question_progress.times_wrong')
            ->orderBy('question_progress.due_on')
            ->orderBy('questions.order')
            ->select('questions.*')
            ->limit($limit)
            ->get();

        if ($review->count() >= $limit) {
            return $review;
        }

        $fresh = $this->questions()
            ->whereDoesntHave('progress', fn ($query) => $query->where('user_id', $user->id))
            ->limit($limit - $review->count())
            ->get();

        return $review->concat($fresh);
    }

    /**
     * Mastery snapshot for the progress bars.
     *
     * @return array{total: int, mastered: int, learning: int, untouched: int, percentage: float}
     */
    public function masteryFor(User $user): array
    {
        $total = $this->questions()->count();

        $progress = QuestionProgress::where('question_progress.user_id', $user->id)
            ->join('questions', 'questions.id', '=', 'question_progress.question_id')
            ->where('questions.tryout_id', $this->id)
            ->selectRaw('COUNT(*) as seen, SUM(CASE WHEN question_progress.mastered_at IS NOT NULL THEN 1 ELSE 0 END) as mastered')
            ->first();

        $seen = (int) ($progress->seen ?? 0);
        $mastered = (int) ($progress->mastered ?? 0);

        return [
            'total' => $total,
            'mastered' => $mastered,
            'learning' => $seen - $mastered,
            'untouched' => max(0, $total - $seen),
            'percentage' => $total > 0 ? round(($mastered / $total) * 100, 1) : 0.0,
        ];
    }

    /**
     * Today's session for this package, if one was already created.
     */
    public function sessionOn(User $user, ?Carbon $date = null): ?ExamAttempt
    {
        return $this->examAttempts()
            ->where('user_id', $user->id)
            ->whereDate('session_date', ($date ?? Carbon::today())->toDateString())
            ->latest('id')
            ->first();
    }

    public function totalScorePossible(): float
    {
        return (float) $this->questions()->sum('score_weight');
    }
}
