<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionProgress;
use App\Models\Tryout;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Single-user app: progress is always reported for the learner account.
        $learner = User::where('role', 'user')->first() ?? Auth::user();

        $tryouts = Tryout::withCount('questions')->orderBy('id')->get();

        $packages = $tryouts->map(fn (Tryout $tryout) => [
            'tryout' => $tryout,
            'mastery' => $tryout->masteryFor($learner),
        ]);

        $totalQuestions = Question::count();
        $totalMastered = QuestionProgress::where('user_id', $learner->id)
            ->whereNotNull('mastered_at')
            ->count();

        $recentSessions = ExamAttempt::with('tryout')
            ->where('status', 'completed')
            ->latest('completed_at')
            ->take(8)
            ->get();

        return view('admin.dashboard', [
            'learner' => $learner,
            'packages' => $packages,
            'packageCount' => $tryouts->count(),
            'totalQuestions' => $totalQuestions,
            'totalMastered' => $totalMastered,
            'masteryPercentage' => $totalQuestions > 0 ? round(($totalMastered / $totalQuestions) * 100, 1) : 0.0,
            'totalSessions' => ExamAttempt::where('status', 'completed')->count(),
            'sessionsToday' => ExamAttempt::where('status', 'completed')
                ->whereDate('session_date', Carbon::today())
                ->count(),
            'recentSessions' => $recentSessions,
        ]);
    }
}
