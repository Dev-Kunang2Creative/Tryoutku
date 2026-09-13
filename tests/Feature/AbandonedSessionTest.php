<?php

namespace Tests\Feature;

use App\Models\ExamAttempt;
use App\Models\Tryout;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menekan tombol mulai tanpa menjawab apa pun tidak boleh menghabiskan jatah
 * latihan hari itu.
 */
class AbandonedSessionTest extends TestCase
{
    use RefreshDatabase;

    private User $learner;

    private Tryout $tryout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->learner = User::where('role', 'user')->firstOrFail();
        $this->tryout = Tryout::firstOrFail();
    }

    public function test_the_dashboard_shows_a_confirmation_before_the_timer_starts(): void
    {
        $this->actingAs($this->learner)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Mulai '.$this->tryout->title.'?')
            ->assertSee('Penghitung waktu mulai berjalan begitu tombol Mulai ditekan.');
    }

    public function test_visiting_the_dashboard_discards_a_session_nothing_was_answered_in(): void
    {
        $sesi = $this->mulaiSesi();

        $this->actingAs($this->learner)->get(route('student.dashboard'))->assertOk();

        $this->assertModelMissing($sesi);
        $this->assertSame(0, ExamAttempt::count());
    }

    public function test_a_session_with_an_answer_survives_a_visit_to_the_dashboard(): void
    {
        $sesi = $this->mulaiSesi();
        $this->jawabSoalPertama($sesi);

        $this->actingAs($this->learner)->get(route('student.dashboard'))->assertOk();

        $this->assertModelExists($sesi);
    }

    public function test_the_day_can_be_started_again_after_an_untouched_session(): void
    {
        $pertama = $this->mulaiSesi();

        $this->actingAs($this->learner)->get(route('student.dashboard'));

        $this->actingAs($this->learner)
            ->post(route('student.exam.start', $this->tryout))
            ->assertRedirect();

        $kedua = ExamAttempt::latest('id')->firstOrFail();

        $this->assertNotSame($pertama->id, $kedua->id);
        $this->assertSame('in_progress', $kedua->status);
    }

    public function test_leaving_an_untouched_session_removes_it(): void
    {
        $sesi = $this->mulaiSesi();

        $this->actingAs($this->learner)
            ->post(route('student.exam.abandon', $sesi))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('info');

        $this->assertModelMissing($sesi);
    }

    public function test_leaving_a_session_that_has_an_answer_keeps_it(): void
    {
        $sesi = $this->mulaiSesi();
        $this->jawabSoalPertama($sesi);

        $this->actingAs($this->learner)
            ->post(route('student.exam.abandon', $sesi))
            ->assertRedirect(route('student.dashboard'));

        $this->assertModelExists($sesi);
        $this->assertSame('in_progress', $sesi->refresh()->status);
    }

    public function test_an_expired_untouched_session_is_discarded_instead_of_scored_zero(): void
    {
        $sesi = $this->mulaiSesi();
        $sesi->update(['deadline_at' => Carbon::now()->subMinute()]);

        $this->actingAs($this->learner)
            ->post(route('student.exam.start', $this->tryout))
            ->assertRedirect();

        $this->assertModelMissing($sesi);

        $baru = ExamAttempt::latest('id')->firstOrFail();
        $this->assertSame('in_progress', $baru->status);
        $this->assertNotSame($sesi->id, $baru->id);
    }

    public function test_an_expired_session_with_answers_is_still_scored(): void
    {
        $sesi = $this->mulaiSesi();
        $this->jawabSoalPertama($sesi);
        $sesi->update(['deadline_at' => Carbon::now()->subMinute()]);

        $this->actingAs($this->learner)
            ->post(route('student.exam.start', $this->tryout))
            ->assertRedirect(route('student.exam.result', $sesi));

        $this->assertSame('completed', $sesi->refresh()->status);
    }

    public function test_another_learners_session_cannot_be_abandoned(): void
    {
        $sesi = $this->mulaiSesi();

        $penyusup = User::create([
            'name' => 'Orang Lain',
            'username' => 'penyusup',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $this->actingAs($penyusup)
            ->post(route('student.exam.abandon', $sesi))
            ->assertForbidden();

        $this->assertModelExists($sesi);
    }

    private function mulaiSesi(): ExamAttempt
    {
        $this->actingAs($this->learner)->post(route('student.exam.start', $this->tryout));

        return ExamAttempt::latest('id')->firstOrFail();
    }

    private function jawabSoalPertama(ExamAttempt $sesi): void
    {
        $jawaban = $sesi->answers()->with('question.options')->firstOrFail();

        $jawaban->update([
            'question_option_id' => $jawaban->question->options->first()->id,
        ]);
    }
}
