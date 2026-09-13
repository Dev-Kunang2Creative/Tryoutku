<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Tryout;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionImageTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private Tryout $tryout;

    protected function setUp(): void
    {
        parent::setUp();

        // Gambar hasil uji tidak boleh mengotori storage asli aplikasi.
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);
        $this->manager = User::where('role', 'admin')->firstOrFail();
        $this->tryout = Tryout::firstOrFail();
    }

    public function test_a_new_question_can_carry_an_image(): void
    {
        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.store', $this->tryout), $this->payload([
                'image' => UploadedFile::fake()->image('grafik.png', 400, 300),
            ]))
            ->assertRedirect(route('admin.tryouts.questions.index', $this->tryout));

        $question = Question::where('question_text', 'Soal baru bergambar')->firstOrFail();

        $this->assertNotNull($question->question_image);
        Storage::disk('public')->assertExists($question->question_image);
        $this->assertStringContainsString($question->question_image, $question->imageUrl());
    }

    public function test_a_question_without_an_image_stays_without_one(): void
    {
        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.store', $this->tryout), $this->payload())
            ->assertRedirect();

        $question = Question::where('question_text', 'Soal baru bergambar')->firstOrFail();

        $this->assertNull($question->question_image);
        $this->assertNull($question->imageUrl());
    }

    public function test_uploading_a_replacement_removes_the_previous_file(): void
    {
        $question = $this->questionWithImage();
        $firstPath = $question->question_image;

        $this->actingAs($this->manager)
            ->put(route('admin.tryouts.questions.update', [$this->tryout, $question]), $this->payload([
                'image' => UploadedFile::fake()->image('pengganti.png', 200, 150),
            ]))
            ->assertRedirect();

        $question->refresh();

        $this->assertNotSame($firstPath, $question->question_image);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($question->question_image);
    }

    public function test_the_remove_checkbox_clears_the_image(): void
    {
        $question = $this->questionWithImage();
        $path = $question->question_image;

        $this->actingAs($this->manager)
            ->put(route('admin.tryouts.questions.update', [$this->tryout, $question]), $this->payload([
                'remove_image' => '1',
            ]))
            ->assertRedirect();

        $this->assertNull($question->refresh()->question_image);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_editing_without_touching_the_field_keeps_the_image(): void
    {
        $question = $this->questionWithImage();
        $path = $question->question_image;

        $this->actingAs($this->manager)
            ->put(route('admin.tryouts.questions.update', [$this->tryout, $question]), $this->payload())
            ->assertRedirect();

        $this->assertSame($path, $question->refresh()->question_image);
        Storage::disk('public')->assertExists($path);
    }

    public function test_deleting_a_question_also_deletes_its_image(): void
    {
        $question = $this->questionWithImage();
        $path = $question->question_image;

        $this->actingAs($this->manager)
            ->delete(route('admin.tryouts.questions.destroy', [$this->tryout, $question]))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_a_non_image_upload_is_refused(): void
    {
        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.store', $this->tryout), $this->payload([
                'image' => UploadedFile::fake()->create('catatan.pdf', 40, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('questions', ['question_text' => 'Soal baru bergambar']);
    }

    public function test_the_image_is_shown_to_the_learner_and_in_the_question_bank(): void
    {
        $question = $this->questionWithImage();

        $this->actingAs($this->manager)
            ->get(route('admin.tryouts.questions.index', $this->tryout).'?q=Soal baru bergambar')
            ->assertOk()
            ->assertSee($question->imageUrl(), escape: false)
            ->assertSee('Bergambar');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'question_text' => 'Soal baru bergambar',
            'explanation' => 'Pembahasan singkat.',
            'score_weight' => 10,
            'order' => 900,
            'options' => ['A' => 'Satu', 'B' => 'Dua', 'C' => 'Tiga', 'D' => 'Empat', 'E' => 'Lima'],
            'correct_option' => 'A',
        ], $overrides);
    }

    private function questionWithImage(): Question
    {
        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.store', $this->tryout), $this->payload([
                'image' => UploadedFile::fake()->image('awal.png', 300, 200),
            ]));

        return Question::where('question_text', 'Soal baru bergambar')->firstOrFail();
    }
}
