<?php

namespace Tests\Feature;

use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Tryout $tryout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->tryout = Tryout::firstOrFail();
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_admin_management_pages_render(): void
    {
        $question = Question::where('tryout_id', $this->tryout->id)->firstOrFail();

        $routes = [
            route('admin.dashboard'),
            route('admin.tryouts.index'),
            route('admin.tryouts.edit', $this->tryout),
            route('admin.tryouts.questions.index', $this->tryout),
            route('admin.tryouts.questions.create', $this->tryout),
            route('admin.tryouts.questions.edit', [$this->tryout, $question]),
            route('admin.results.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_admin_result_detail_renders_for_a_completed_attempt(): void
    {
        $student = User::where('role', 'user')->firstOrFail();

        $this->actingAs($student)->post(route('student.exam.start', $this->tryout));

        $attempt = ExamAttempt::where('user_id', $student->id)
            ->where('tryout_id', $this->tryout->id)
            ->firstOrFail();

        $this->actingAs($student)->post(route('student.exam.finish', $attempt));

        $this->actingAs($this->admin)
            ->get(route('admin.results.show', $attempt->refresh()))
            ->assertOk()
            ->assertSee('Tinjauan butir soal')
            ->assertSee('Pilih soal');
    }

    public function test_results_index_can_be_filtered_by_tryout(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.results.index', ['tryout_id' => $this->tryout->id]))
            ->assertOk()
            ->assertSee('Reset');
    }

    public function test_student_is_blocked_from_the_admin_panel(): void
    {
        $student = User::where('role', 'user')->firstOrFail();

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
    }
}
