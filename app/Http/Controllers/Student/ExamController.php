<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Tryout;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    /**
     * Open today's session for a package. Each package may be worked through
     * once per day; questions are drawn from whatever is due.
     */
    public function start(Tryout $tryout)
    {
        $user = Auth::user();

        if (! $tryout->is_active) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Paket latihan ini sedang tidak aktif.');
        }

        if ($tryout->questions()->count() === 0) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Paket latihan ini belum memiliki soal.');
        }

        $existing = $tryout->sessionOn($user);

        if ($existing) {
            if ($existing->isExpired()) {
                $existing->calculateFinalScore();

                return redirect()->route('student.exam.result', $existing)
                    ->with('warning', 'Waktu sesi sebelumnya sudah habis, jawaban otomatis dikumpulkan.');
            }

            if ($existing->status === 'in_progress') {
                return redirect()->route('student.exam.room', $existing);
            }

            return redirect()->route('student.dashboard')
                ->with('info', 'Paket "'.$tryout->title.'" sudah dikerjakan hari ini. Sesi berikutnya tersedia besok.');
        }

        $questions = $tryout->dueQuestionsFor($user);

        if ($questions->isEmpty()) {
            return redirect()->route('student.dashboard')
                ->with('success', 'Semua soal di paket "'.$tryout->title.'" sudah tuntas. Tidak ada yang perlu diulang.');
        }

        $now = Carbon::now();

        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            'session_date' => $now->toDateString(),
            'started_at' => $now,
            'deadline_at' => $now->copy()->addMinutes($tryout->duration_minutes),
            'status' => 'in_progress',
        ]);

        // Freeze the drawn questions into the session, in serving order.
        foreach ($questions as $question) {
            ExamAnswer::create([
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ]);
        }

        return redirect()->route('student.exam.room', $attempt);
    }

    public function room(ExamAttempt $attempt)
    {
        $this->authorizeOwner($attempt);

        if ($attempt->isExpired()) {
            $attempt->calculateFinalScore();

            return redirect()->route('student.exam.result', $attempt)
                ->with('warning', 'Waktu pengerjaan sudah habis, jawaban otomatis dikumpulkan.');
        }

        if ($attempt->status === 'completed') {
            return redirect()->route('student.exam.result', $attempt);
        }

        $attempt->load(['tryout', 'answers.question.options']);

        return view('student.exam_room', [
            'attempt' => $attempt,
            'remainingSeconds' => $attempt->remainingSeconds(),
        ]);
    }

    public function saveAnswer(Request $request, ExamAttempt $attempt)
    {
        if ($attempt->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($attempt->isExpired() || $attempt->status !== 'in_progress') {
            $attempt->calculateFinalScore();

            return response()->json([
                'expired' => true,
                'redirect_url' => route('student.exam.result', $attempt),
            ], 410);
        }

        $validated = $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'question_option_id' => ['nullable', 'exists:question_options,id'],
            'is_doubt' => ['nullable', 'boolean'],
        ]);

        // Only questions actually drawn for this session may be answered.
        $answer = $attempt->answers()
            ->where('question_id', $validated['question_id'])
            ->first();

        if (! $answer) {
            return response()->json(['error' => 'Soal tidak termasuk dalam sesi ini.'], 422);
        }

        if (array_key_exists('question_option_id', $validated)) {
            $answer->question_option_id = $validated['question_option_id'];
        }

        if (array_key_exists('is_doubt', $validated)) {
            $answer->is_doubt = (bool) $validated['is_doubt'];
        }

        $answer->save();

        return response()->json([
            'success' => true,
            'saved_at' => Carbon::now()->format('H:i:s'),
            'option_id' => $answer->question_option_id,
            'is_doubt' => $answer->is_doubt,
        ]);
    }

    public function finish(ExamAttempt $attempt)
    {
        $this->authorizeOwner($attempt);

        if ($attempt->status !== 'completed') {
            $attempt->calculateFinalScore();
        }

        return redirect()->route('student.exam.result', $attempt)
            ->with('success', 'Sesi selesai. Jawaban sudah dinilai.');
    }

    /**
     * Session summary: score, mastery movement, and a link into the explanations.
     */
    public function result(ExamAttempt $attempt)
    {
        $this->authorizeOwnerOrAdmin($attempt);

        if ($attempt->status !== 'completed') {
            return redirect()->route('student.exam.room', $attempt);
        }

        $attempt->load(['tryout', 'answers.question', 'answers.selectedOption']);

        return view('student.exam_result', [
            'attempt' => $attempt,
            'breakdown' => $attempt->answerBreakdown(),
            'mastery' => $attempt->tryout->masteryFor($attempt->user),
        ]);
    }

    /**
     * The explanation browser: one question at a time, picked from a palette.
     */
    public function review(ExamAttempt $attempt)
    {
        $this->authorizeOwnerOrAdmin($attempt);

        if ($attempt->status !== 'completed') {
            return redirect()->route('student.exam.room', $attempt);
        }

        $attempt->load(['tryout', 'answers.question.options', 'answers.selectedOption']);

        return view('student.exam_review', [
            'attempt' => $attempt,
            'answers' => $attempt->answers,
            'breakdown' => $attempt->answerBreakdown(),
        ]);
    }

    private function authorizeOwner(ExamAttempt $attempt): void
    {
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'Akses sesi latihan tidak valid.');
        }
    }

    private function authorizeOwnerOrAdmin(ExamAttempt $attempt): void
    {
        if ($attempt->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'Akses sesi latihan tidak valid.');
        }
    }
}
