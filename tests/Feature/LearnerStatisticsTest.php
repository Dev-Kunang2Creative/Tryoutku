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
use Illuminate\Support\Str;
use Tests\TestCase;

class LearnerStatisticsTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private User $learner;

    private Tryout $tryout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->manager = User::where('role', 'admin')->firstOrFail();
        $this->learner = User::where('role', 'user')->firstOrFail();
        $this->tryout = Tryout::firstOrFail();
    }

    public function test_learner_cannot_open_the_statistics_page(): void
    {
        $this->actingAs($this->learner)
            ->get(route('admin.statistics.index'))
            ->assertForbidden();
    }

    public function test_a_quiet_history_is_reported_as_too_little_data(): void
    {
        $response = $this->actingAs($this->manager)->get(route('admin.statistics.index'));

        $response->assertOk()->assertSee('Belum cukup data');
        $this->assertSame('belum_cukup', $response->viewData('vonis')['kunci']);
    }

    public function test_mastering_questions_across_the_week_reads_as_growth(): void
    {
        $this->sessionsOnDays(range(0, 5));
        $this->masterQuestions(6, Carbon::today()->subDays(2));

        $response = $this->actingAs($this->manager)->get(route('admin.statistics.index'));

        $response->assertOk()->assertSee('Bertumbuh');

        $vonis = $response->viewData('vonis');
        $this->assertSame('tumbuh', $vonis['kunci']);
        $this->assertStringContainsString('6 soal', $vonis['alasan']);

        $this->assertSame(6, $response->viewData('ringkasan')['dikuasai']);
    }

    public function test_practising_without_mastering_anything_reads_as_stuck(): void
    {
        $this->sessionsOnDays(range(0, 5));

        $response = $this->actingAs($this->manager)->get(route('admin.statistics.index'));

        $response->assertOk()->assertSee('Jalan di tempat');
        $this->assertSame('mandek', $response->viewData('vonis')['kunci']);
    }

    public function test_working_only_rarely_is_called_out_before_anything_else(): void
    {
        // Cukup sesi untuk dinilai, tetapi menumpuk di dua hari saja.
        $this->sessionsOnDays([0, 0, 1, 1]);
        $this->masterQuestions(4, Carbon::today());

        $response = $this->actingAs($this->manager)->get(route('admin.statistics.index'));

        $response->assertOk()->assertSee('Jarang berlatih');
        $this->assertSame('jarang_latihan', $response->viewData('vonis')['kunci']);
    }

    public function test_a_slower_week_than_the_last_is_reported_as_slowing_down(): void
    {
        $this->sessionsOnDays(range(0, 5));
        $this->masterQuestions(2, Carbon::today()->subDays(1));
        $this->masterQuestions(5, Carbon::today()->subDays(9), 10);

        $response = $this->actingAs($this->manager)->get(route('admin.statistics.index'));

        $response->assertOk()->assertSee('Tumbuh, tapi melambat');
        $this->assertSame('melambat', $response->viewData('vonis')['kunci']);
    }

    public function test_repeatedly_wrong_questions_are_surfaced(): void
    {
        $soal = Question::where('tryout_id', $this->tryout->id)->firstOrFail();

        QuestionProgress::create([
            'user_id' => $this->learner->id,
            'question_id' => $soal->id,
            'correct_streak' => 0,
            'times_correct' => 1,
            'times_wrong' => 4,
            'due_on' => Carbon::today(),
            'last_answered_on' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->manager)->get(route('admin.statistics.index'));

        $menyangkut = $response->viewData('soalMenyangkut');
        $this->assertCount(1, $menyangkut);
        $this->assertSame($soal->id, $menyangkut->first()->question_id);
        $response->assertSee(Str::limit($soal->question_text, 110));
    }

    public function test_the_mastery_curve_only_ever_climbs(): void
    {
        $this->masterQuestions(2, Carbon::today()->subDays(4));
        $this->masterQuestions(3, Carbon::today()->subDays(1), 50);

        $kurva = $this->actingAs($this->manager)
            ->get(route('admin.statistics.index'))
            ->viewData('kurvaPenguasaan');

        $titik = collect($kurva)->firstWhere('tryout.id', $this->tryout->id)['titik'];
        $nilai = array_column($titik, 'nilai');

        $this->assertSame([2, 5], $nilai, 'Kurva kumulatif harus menaik, bukan jumlah per hari.');
    }

    public function test_average_duration_comes_from_the_time_actually_spent(): void
    {
        $this->sessionsOnDays([0], durasiDetik: 600);
        $this->sessionsOnDays([1], durasiDetik: 300);

        $ringkasan = $this->actingAs($this->manager)
            ->get(route('admin.statistics.index'))
            ->viewData('ringkasan');

        $this->assertSame(450, $ringkasan['durasi']);
    }

    /**
     * @param  list<int>  $hariLalu
     */
    private function sessionsOnDays(array $hariLalu, int $durasiDetik = 480): void
    {
        foreach ($hariLalu as $lalu) {
            $selesai = Carbon::today()->subDays($lalu)->setTime(19, 0);

            ExamAttempt::create([
                'user_id' => $this->learner->id,
                'tryout_id' => $this->tryout->id,
                'session_date' => $selesai->copy()->startOfDay(),
                'started_at' => $selesai->copy()->subSeconds($durasiDetik),
                'deadline_at' => $selesai->copy()->addMinutes(10),
                'completed_at' => $selesai,
                'status' => 'completed',
                'total_score' => 70,
                'max_score' => 100,
                'percentage' => 70,
            ]);
        }
    }

    private function masterQuestions(int $jumlah, Carbon $pada, int $lewati = 0): void
    {
        $soal = Question::where('tryout_id', $this->tryout->id)
            ->orderBy('id')->skip($lewati)->take($jumlah)->get();

        foreach ($soal as $butir) {
            QuestionProgress::create([
                'user_id' => $this->learner->id,
                'question_id' => $butir->id,
                'correct_streak' => 2,
                'times_correct' => 2,
                'times_wrong' => 0,
                'due_on' => $pada,
                'last_answered_on' => $pada,
                'mastered_at' => $pada->copy()->setTime(19, 30),
            ]);
        }
    }
}
