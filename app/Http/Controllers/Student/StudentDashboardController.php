<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\Tryout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $tryouts = Tryout::where('is_active', true)
            ->withCount('questions')
            ->orderBy('id')
            ->get();

        // Per-package state for today: mastery so far, today's session, and what is due.
        $packages = $tryouts->map(function (Tryout $tryout) use ($user, $today) {
            $session = $tryout->sessionOn($user, $today);
            $mastery = $tryout->masteryFor($user);

            return [
                'tryout' => $tryout,
                'mastery' => $mastery,
                'session' => $session,
                'status' => match (true) {
                    $session && $session->status === 'completed' => 'done_today',
                    $session !== null => 'in_progress',
                    $mastery['total'] > 0 && $mastery['mastered'] >= $mastery['total'] => 'all_mastered',
                    $mastery['total'] === 0 => 'empty',
                    default => 'available',
                },
                'due_count' => $tryout->dueQuestionsFor($user, $today)->count(),
            ];
        });

        $recentSessions = ExamAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('tryout')
            ->withCount('answers')
            ->latest('completed_at')
            ->paginate(10)
            ->fragment('riwayat');

        $doneToday = $packages->where('status', 'done_today')->count();

        return view('student.dashboard', [
            'packages' => $packages,
            'recentSessions' => $recentSessions,
            'doneToday' => $doneToday,
            'packageCount' => $packages->count(),
            'streakDays' => $this->currentStreak($user),
        ]);
    }

    /**
     * Consecutive days, counting back from today, with at least one finished session.
     */
    private function currentStreak($user): int
    {
        $days = ExamAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('session_date')
            ->orderByDesc('session_date')
            ->pluck('session_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values();

        if ($days->isEmpty()) {
            return 0;
        }

        $cursor = Carbon::today();

        // Allow the streak to still count if today has not been worked on yet.
        if ($days->first() !== $cursor->toDateString()) {
            $cursor = $cursor->subDay();

            if ($days->first() !== $cursor->toDateString()) {
                return 0;
            }
        }

        $streak = 0;

        foreach ($days as $day) {
            if ($day === $cursor->toDateString()) {
                $streak++;
                $cursor = $cursor->subDay();

                continue;
            }

            break;
        }

        return $streak;
    }
}
