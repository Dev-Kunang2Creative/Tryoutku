<?php

namespace Tests\Feature;

use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionProgress;
use App\Models\Tryout;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyDrillRotationTest extends TestCase
{
    use RefreshDatabase;

    private User $learner;

    private Tryout $ipa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->learner = User::where('role', 'user')->firstOrFail();
        $this->ipa = Tryout::where('slug', 'latihan-harian-ipa')->firstOrFail();
    }

    public function test_the_bank_has_two_packages_of_sixty_questions_each(): void
    {
        $this->assertSame(2, Tryout::count());

        foreach (Tryout::all() as $tryout) {
            $this->assertSame(60, $tryout->questions()->count(), "Paket {$tryout->subject} harus punya 60 soal.");
        }

        // Every question must have exactly five options and exactly one key.
        foreach (Question::with('options')->get() as $question) {
            $this->assertCount(5, $question->options);
            $this->assertSame(1, $question->options->where('is_correct', true)->count());
        }
    }

    public function test_a_session_serves_only_the_configured_number_of_questions(): void
    {
        $this->actingAs($this->learner)->post(route('student.exam.start', $this->ipa));

        $attempt = ExamAttempt::where('tryout_id', $this->ipa->id)->firstOrFail();

        $this->assertSame($this->ipa->questions_per_session, $attempt->answers()->count());
        $this->assertSame(15, $attempt->answers()->count());
    }

    public function test_a_package_can_only_be_worked_through_once_per_day(): void
    {
        $this->actingAs($this->learner)->post(route('student.exam.start', $this->ipa));
        $attempt = ExamAttempt::where('tryout_id', $this->ipa->id)->firstOrFail();

        $this->actingAs($this->learner)->post(route('student.exam.finish', $attempt));

        // A second attempt on the same day is refused and sent back to the dashboard.
        $this->actingAs($this->learner)
            ->post(route('student.exam.start', $this->ipa))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('info');

        $this->assertSame(1, ExamAttempt::where('tryout_id', $this->ipa->id)->count());
    }

    public function test_a_wrong_answer_comes_back_the_next_day_but_a_mastered_one_does_not(): void
    {
        // --- Day 1: answer everything wrong on purpose.
        $day1 = $this->runSession($this->ipa, correct: false);
        $wrongQuestionIds = $day1->answers->pluck('question_id')->all();

        foreach ($wrongQuestionIds as $questionId) {
            $progress = QuestionProgress::where('user_id', $this->learner->id)
                ->where('question_id', $questionId)
                ->firstOrFail();

            $this->assertSame(0, $progress->correct_streak);
            $this->assertNull($progress->mastered_at, 'Jawaban salah tidak boleh langsung tuntas.');
            $this->assertSame(
                Carbon::today()->addDay()->toDateString(),
                $progress->due_on->toDateString(),
                'Soal yang salah harus jatuh tempo besok.'
            );
        }

        // --- Day 2: those same questions must be served again, first.
        Carbon::setTestNow(Carbon::now()->addDay());

        $due = $this->ipa->dueQuestionsFor($this->learner)->pluck('id')->all();
        $this->assertEqualsCanonicalizing(
            $wrongQuestionIds,
            $due,
            'Sesi hari kedua harus mendahulukan soal yang masih salah.'
        );

        // Answer them correctly: streak becomes 1, still not mastered.
        $day2 = $this->runSession($this->ipa, correct: true);

        foreach ($wrongQuestionIds as $questionId) {
            $progress = QuestionProgress::where('user_id', $this->learner->id)
                ->where('question_id', $questionId)
                ->firstOrFail();

            $this->assertSame(1, $progress->correct_streak);
            $this->assertNull($progress->mastered_at, 'Benar 1x belum cukup untuk tuntas.');
        }

        // --- Day 3: correct a second time in a row, so they are mastered.
        Carbon::setTestNow(Carbon::now()->addDay());

        $this->assertEqualsCanonicalizing(
            $wrongQuestionIds,
            $this->ipa->dueQuestionsFor($this->learner)->pluck('id')->all()
        );

        $this->runSession($this->ipa, correct: true);

        foreach ($wrongQuestionIds as $questionId) {
            $progress = QuestionProgress::where('user_id', $this->learner->id)
                ->where('question_id', $questionId)
                ->firstOrFail();

            $this->assertSame(2, $progress->correct_streak);
            $this->assertNotNull($progress->mastered_at, 'Benar 2x berturut-turut harus menuntaskan soal.');
            $this->assertNull($progress->due_on, 'Soal tuntas tidak dijadwalkan lagi.');
        }

        // --- Day 4: mastered questions are gone; fresh ones take their place.
        Carbon::setTestNow(Carbon::now()->addDay());

        $day4Due = $this->ipa->dueQuestionsFor($this->learner)->pluck('id')->all();

        $this->assertEmpty(
            array_intersect($wrongQuestionIds, $day4Due),
            'Soal yang sudah tuntas tidak boleh muncul lagi.'
        );
        $this->assertCount(15, $day4Due, 'Sesi diisi soal baru yang belum pernah dibuka.');
    }

    public function test_a_correct_streak_resets_when_the_answer_is_wrong_again(): void
    {
        $day1 = $this->runSession($this->ipa, correct: true);
        $questionIds = $day1->answers->pluck('question_id')->all();

        Carbon::setTestNow(Carbon::now()->addDay());

        // Second day answered wrong: the streak built on day 1 is lost.
        $this->runSession($this->ipa, correct: false);

        foreach ($questionIds as $questionId) {
            $progress = QuestionProgress::where('user_id', $this->learner->id)
                ->where('question_id', $questionId)
                ->firstOrFail();

            $this->assertSame(0, $progress->correct_streak, 'Jawaban salah harus mengulang hitungan dari nol.');
            $this->assertNull($progress->mastered_at);
        }
    }

    public function test_an_unanswered_question_stays_in_rotation_without_counting_as_a_mistake(): void
    {
        $this->actingAs($this->learner)->post(route('student.exam.start', $this->ipa));
        $attempt = ExamAttempt::where('tryout_id', $this->ipa->id)->firstOrFail();

        // Finish without answering anything at all.
        $this->actingAs($this->learner)->post(route('student.exam.finish', $attempt));

        $progress = QuestionProgress::where('user_id', $this->learner->id)
            ->where('question_id', $attempt->answers()->first()->question_id)
            ->firstOrFail();

        $this->assertSame(0, $progress->times_wrong, 'Soal yang dikosongkan bukan jawaban salah.');
        $this->assertSame(0, $progress->correct_streak);
        $this->assertNotNull($progress->due_on, 'Soal kosong tetap dijadwalkan ulang.');
    }

    public function test_the_review_page_lists_every_question_of_the_session(): void
    {
        $attempt = $this->runSession($this->ipa, correct: true);

        $this->actingAs($this->learner)
            ->get(route('student.exam.review', $attempt))
            ->assertOk()
            ->assertSee('Pembahasan soal')
            ->assertSee('Pilih soal');
    }

    public function test_a_third_package_cannot_be_created(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.tryouts.store'), [
                'title' => 'Paket Ketiga',
                'subject' => 'Matematika',
                'session_type' => 'Latihan Harian',
                'duration_minutes' => 30,
                'questions_per_session' => 15,
            ])
            ->assertRedirect(route('admin.tryouts.index'))
            ->assertSessionHas('error');

        $this->assertSame(2, Tryout::count());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Run one full session, answering every question either right or wrong.
     */
    private function runSession(Tryout $tryout, bool $correct): ExamAttempt
    {
        $this->actingAs($this->learner)->post(route('student.exam.start', $tryout));

        $attempt = ExamAttempt::where('tryout_id', $tryout->id)
            ->whereDate('session_date', Carbon::today())
            ->latest('id')
            ->firstOrFail();

        foreach ($attempt->answers()->with('question.options')->get() as $answer) {
            $option = $correct
                ? $answer->question->options->firstWhere('is_correct', true)
                : $answer->question->options->firstWhere('is_correct', false);

            $this->actingAs($this->learner)->postJson(route('student.exam.answer', $attempt), [
                'question_id' => $answer->question_id,
                'question_option_id' => $option->id,
            ])->assertOk();
        }

        $this->actingAs($this->learner)->post(route('student.exam.finish', $attempt));

        return $attempt->fresh(['answers']);
    }
}
