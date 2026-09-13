<?php

namespace Tests\Feature;

use App\Models\ExamAttempt;
use App\Models\Tryout;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndSessionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke portal latihan');
    }

    public function test_public_registration_is_no_longer_available(): void
    {
        // Single-user app: there must be no way to create another account.
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Orang Lain',
            'username' => 'lain',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();

        $this->assertSame(2, User::count());
    }

    public function test_manager_lands_in_the_question_panel_after_login(): void
    {
        $manager = User::where('role', 'admin')->firstOrFail();

        $this->post('/login', [
            'username' => $manager->username,
            'password' => 'password123',
        ])->assertRedirect('/admin/dashboard');

        $this->actingAs($manager)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Ringkasan Latihan');
    }

    public function test_learner_lands_on_todays_practice_page_after_login(): void
    {
        $learner = User::where('role', 'user')->firstOrFail();

        $this->post('/login', [
            'username' => $learner->username,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->actingAs($learner)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Paket latihan')
            ->assertSee('IPA')
            ->assertSee('Bahasa Inggris');
    }

    public function test_learner_cannot_reach_the_question_panel(): void
    {
        $learner = User::where('role', 'user')->firstOrFail();

        $this->actingAs($learner)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_exam_room_renders_with_the_session_questions(): void
    {
        $learner = User::where('role', 'user')->firstOrFail();
        $tryout = Tryout::firstOrFail();

        $this->actingAs($learner)->post(route('student.exam.start', $tryout))->assertRedirect();

        $attempt = ExamAttempt::where('user_id', $learner->id)->firstOrFail();

        $this->actingAs($learner)
            ->get(route('student.exam.room', $attempt))
            ->assertOk()
            ->assertSee('Nomor soal')
            ->assertSee('Tandai ragu-ragu')
            ->assertSee('Sesi hari ini');
    }

    public function test_autosave_then_finish_produces_a_scored_result(): void
    {
        $learner = User::where('role', 'user')->firstOrFail();
        $tryout = Tryout::firstOrFail();

        $this->actingAs($learner)->post(route('student.exam.start', $tryout));
        $attempt = ExamAttempt::where('user_id', $learner->id)->firstOrFail();

        $answer = $attempt->answers()->with('question.options')->firstOrFail();
        $correctOption = $answer->question->options->firstWhere('is_correct', true);

        $this->actingAs($learner)
            ->postJson(route('student.exam.answer', $attempt), [
                'question_id' => $answer->question_id,
                'question_option_id' => $correctOption->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($learner)
            ->post(route('student.exam.finish', $attempt))
            ->assertRedirect(route('student.exam.result', $attempt));

        $attempt->refresh();

        $this->assertSame('completed', $attempt->status);
        $this->assertEquals(1, $attempt->total_score);
        $this->assertEquals(15, $attempt->max_score);

        $this->actingAs($learner)
            ->get(route('student.exam.result', $attempt))
            ->assertOk()
            ->assertSee('Jadwal pengulangan')
            ->assertSee('Buka pembahasan');
    }

    public function test_a_question_outside_the_session_cannot_be_answered(): void
    {
        $learner = User::where('role', 'user')->firstOrFail();
        $ipa = Tryout::where('slug', 'latihan-harian-ipa')->firstOrFail();
        $english = Tryout::where('slug', 'latihan-harian-bahasa-inggris')->firstOrFail();

        $this->actingAs($learner)->post(route('student.exam.start', $ipa));
        $attempt = ExamAttempt::where('tryout_id', $ipa->id)->firstOrFail();

        $foreignQuestion = $english->questions()->firstOrFail();

        $this->actingAs($learner)
            ->postJson(route('student.exam.answer', $attempt), [
                'question_id' => $foreignQuestion->id,
                'question_option_id' => $foreignQuestion->options()->firstOrFail()->id,
            ])
            ->assertStatus(422);
    }

    public function test_another_users_session_is_not_accessible(): void
    {
        $learner = User::where('role', 'user')->firstOrFail();
        $tryout = Tryout::firstOrFail();

        $this->actingAs($learner)->post(route('student.exam.start', $tryout));
        $attempt = ExamAttempt::where('user_id', $learner->id)->firstOrFail();

        $intruder = User::create([
            'name' => 'Orang Lain',
            'username' => 'orang_lain',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $this->actingAs($intruder)->get(route('student.exam.room', $attempt))->assertForbidden();
    }
}
