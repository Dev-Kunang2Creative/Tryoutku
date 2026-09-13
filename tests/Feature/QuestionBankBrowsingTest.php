<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Tryout;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionBankBrowsingTest extends TestCase
{
    use RefreshDatabase;

    private const PER_PAGE = 15;

    private User $manager;

    private Tryout $tryout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->manager = User::where('role', 'admin')->firstOrFail();
        $this->tryout = Tryout::firstOrFail();
    }

    public function test_the_bank_is_split_into_pages(): void
    {
        $total = $this->tryout->questions()->count();
        $this->assertGreaterThan(self::PER_PAGE, $total, 'Seeder harus mengisi lebih dari satu halaman.');

        $response = $this->actingAs($this->manager)->get($this->bankUrl());

        $response->assertOk()->assertSee('page=2', escape: false);

        $questions = $response->viewData('questions');
        $this->assertCount(self::PER_PAGE, $questions->items());
        $this->assertSame($total, $questions->total());
        $this->assertSame($total, $response->viewData('totalQuestions'));
    }

    public function test_the_last_page_holds_the_remaining_questions(): void
    {
        $total = $this->tryout->questions()->count();
        $lastPage = (int) ceil($total / self::PER_PAGE);

        $questions = $this->actingAs($this->manager)
            ->get($this->bankUrl(['page' => $lastPage]))
            ->assertOk()
            ->viewData('questions');

        $this->assertCount($total - (($lastPage - 1) * self::PER_PAGE), $questions->items());
    }

    public function test_search_narrows_the_list_by_question_text(): void
    {
        $needle = 'Fotosintesis kuantum tiga langkah';

        $this->makeQuestion($needle, 'Pilihan benar', 'Pembahasan singkat.');

        $questions = $this->actingAs($this->manager)
            ->get($this->bankUrl(['q' => 'kuantum tiga langkah']))
            ->assertOk()
            ->assertSee('cocok dengan')
            ->viewData('questions');

        $this->assertSame(1, $questions->total());
        $this->assertSame($needle, $questions->items()[0]->question_text);
    }

    public function test_search_also_looks_inside_options_and_explanation(): void
    {
        $this->makeQuestion('Soal dengan pilihan khas', 'Bilangan oksidasi mangan', 'Pembahasan biasa.');
        $this->makeQuestion('Soal dengan pembahasan khas', 'Pilihan biasa', 'Menyangkut entalpi pembentukan.');

        $byOption = $this->actingAs($this->manager)
            ->get($this->bankUrl(['q' => 'oksidasi mangan']))->viewData('questions');
        $this->assertSame(1, $byOption->total());
        $this->assertSame('Soal dengan pilihan khas', $byOption->items()[0]->question_text);

        $byExplanation = $this->actingAs($this->manager)
            ->get($this->bankUrl(['q' => 'entalpi pembentukan']))->viewData('questions');
        $this->assertSame(1, $byExplanation->total());
        $this->assertSame('Soal dengan pembahasan khas', $byExplanation->items()[0]->question_text);
    }

    public function test_a_search_without_matches_keeps_the_bank_total_visible(): void
    {
        $response = $this->actingAs($this->manager)
            ->get($this->bankUrl(['q' => 'kata yang pasti tidak ada di bank soal']));

        $response->assertOk()->assertSee('Tidak ada soal yang cocok');

        $this->assertSame(0, $response->viewData('questions')->total());
        $this->assertGreaterThan(0, $response->viewData('totalQuestions'));
    }

    public function test_the_search_term_survives_paging(): void
    {
        // Kata kunci yang ada di setiap soal seeder memaksa hasilnya ikut terbagi halaman.
        $questions = $this->actingAs($this->manager)
            ->get($this->bankUrl(['q' => 'a', 'page' => 2]))
            ->assertOk()
            ->viewData('questions');

        $this->assertStringContainsString('q=a', $questions->nextPageUrl() ?? $questions->previousPageUrl());
    }

    /**
     * @return string
     */
    private function bankUrl(array $query = [])
    {
        return route('admin.tryouts.questions.index', $this->tryout).($query ? '?'.http_build_query($query) : '');
    }

    private function makeQuestion(string $text, string $firstOption, string $explanation): Question
    {
        $question = $this->tryout->questions()->create([
            'question_text' => $text,
            'explanation' => $explanation,
            'score_weight' => 10,
            'order' => 999,
        ]);

        foreach (['A', 'B', 'C', 'D', 'E'] as $index => $key) {
            $question->options()->create([
                'option_key' => $key,
                'option_text' => $index === 0 ? $firstOption : 'Pilihan '.$key,
                'is_correct' => $index === 0,
            ]);
        }

        return $question;
    }
}
