<?php

namespace Tests\Feature;

use App\Models\ExamAttempt;
use App\Models\Tryout;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionHistoryPaginationTest extends TestCase
{
    use RefreshDatabase;

    private const PER_PAGE = 10;

    private User $learner;

    private Tryout $tryout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->learner = User::where('role', 'user')->firstOrFail();
        $this->tryout = Tryout::firstOrFail();
    }

    public function test_history_is_capped_at_one_page_and_offers_a_next_link(): void
    {
        $this->completedSessionsOnLastDays(14);

        $response = $this->actingAs($this->learner)->get(route('student.dashboard'));

        $response->assertOk()
            ->assertSee($this->historyDate(0))   // sesi terbaru
            ->assertSee($this->historyDate(9))   // sesi terakhir di halaman 1
            ->assertDontSee($this->historyDate(10))
            ->assertSee('page=2', escape: false);

        $this->assertCount(self::PER_PAGE, $response->viewData('recentSessions')->items());
    }

    public function test_the_second_page_shows_the_remaining_sessions(): void
    {
        $this->completedSessionsOnLastDays(14);

        $response = $this->actingAs($this->learner)
            ->get(route('student.dashboard').'?page=2');

        $response->assertOk()
            ->assertSee($this->historyDate(10))
            ->assertSee($this->historyDate(13))  // sesi tertua
            ->assertDontSee($this->historyDate(9));

        $this->assertCount(4, $response->viewData('recentSessions')->items());
    }

    public function test_no_pagination_controls_when_history_fits_on_one_page(): void
    {
        $this->completedSessionsOnLastDays(3);

        $response = $this->actingAs($this->learner)->get(route('student.dashboard'));

        $response->assertOk()->assertDontSee('page=2', escape: false);
        $this->assertFalse($response->viewData('recentSessions')->hasPages());
    }

    public function test_unfinished_sessions_stay_out_of_the_history(): void
    {
        $this->completedSessionsOnLastDays(2);

        ExamAttempt::create([
            'user_id' => $this->learner->id,
            'tryout_id' => $this->tryout->id,
            'session_date' => Carbon::today(),
            'started_at' => Carbon::now(),
            'deadline_at' => Carbon::now()->addMinutes(30),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->learner)->get(route('student.dashboard'));

        $this->assertCount(2, $response->viewData('recentSessions')->items());
    }

    /**
     * Satu sesi selesai per hari, mundur dari hari ini, sehingga tanggal tiap
     * baris riwayat unik dan urutan halaman bisa diperiksa dengan pasti.
     */
    private function completedSessionsOnLastDays(int $count): void
    {
        for ($daysAgo = 0; $daysAgo < $count; $daysAgo++) {
            $finishedAt = Carbon::today()->subDays($daysAgo)->setTime(19, 0);

            ExamAttempt::create([
                'user_id' => $this->learner->id,
                'tryout_id' => $this->tryout->id,
                'session_date' => $finishedAt->copy()->startOfDay(),
                'started_at' => $finishedAt->copy()->subMinutes(20),
                'deadline_at' => $finishedAt->copy()->addMinutes(10),
                'completed_at' => $finishedAt,
                'status' => 'completed',
                'total_score' => 80,
                'max_score' => 100,
                'percentage' => 80,
            ]);
        }
    }

    /**
     * Tanggal sebuah baris riwayat, ditulis persis seperti yang dirender view.
     */
    private function historyDate(int $daysAgo): string
    {
        return Carbon::today()->subDays($daysAgo)->translatedFormat('d M Y');
    }
}
