<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Tryout;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Shared import routine for the two question banks.
 *
 * Subclasses describe one package and its questions; this class writes them,
 * replacing any previous content for that package so re-seeding stays idempotent.
 */
abstract class QuestionBankSeeder extends Seeder
{
    /**
     * Fixed seed so option positions are scrambled but reproducible across runs.
     */
    private const SHUFFLE_SEED = 20260910;

    private const LETTERS = ['A', 'B', 'C', 'D', 'E'];

    /**
     * @return array{title: string, slug: string, subject: string, description: string, duration_minutes: int, questions_per_session: int}
     */
    abstract protected function package(): array;

    /**
     * @return array<int, array{q: string, options: array<string, string>, key: string, explanation: string}>
     */
    abstract protected function questions(): array;

    public function run(): void
    {
        $package = $this->package();
        $manager = User::where('role', 'admin')->first();

        $tryout = Tryout::updateOrCreate(
            ['slug' => $package['slug']],
            [
                'title' => $package['title'],
                'description' => $package['description'],
                'subject' => $package['subject'],
                'session_type' => 'Latihan Harian',
                'duration_minutes' => $package['duration_minutes'],
                'questions_per_session' => $package['questions_per_session'],
                'is_active' => true,
                'created_by' => $manager?->id,
            ]
        );

        $items = $this->questions();
        $randomizer = new Randomizer(new Mt19937(self::SHUFFLE_SEED));
        $targets = $this->balancedKeyPositions(count($items), $randomizer);

        DB::transaction(function () use ($tryout, $items, $targets, $randomizer) {
            // Replace the bank wholesale so the seeder can be run again safely.
            $tryout->questions()->delete();

            foreach ($items as $index => $item) {
                $this->assertValid($item, $index);

                $question = Question::create([
                    'tryout_id' => $tryout->id,
                    'question_text' => $item['q'],
                    'explanation' => $item['explanation'],
                    'score_weight' => 1,
                    'order' => $index + 1,
                ]);

                $target = $targets[$index];

                foreach ($this->repositionOptions($item, $target, $randomizer) as $letter => $text) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_key' => $letter,
                        'option_text' => $text,
                        'is_correct' => $letter === $target,
                    ]);
                }
            }
        });
    }

    /**
     * Which letter should hold the correct answer for each question.
     *
     * Authors naturally bunch correct answers on B and C, which lets a learner
     * guess by position instead of understanding. Spreading them evenly over
     * A-E and then shuffling that list keeps the spread balanced without
     * creating a predictable cycle.
     *
     * @return array<int, string>
     */
    private function balancedKeyPositions(int $count, Randomizer $randomizer): array
    {
        $targets = [];

        for ($index = 0; $index < $count; $index++) {
            $targets[] = self::LETTERS[$index % count(self::LETTERS)];
        }

        return $randomizer->shuffleArray($targets);
    }

    /**
     * Move the correct answer to the target letter and spread the distractors
     * over the remaining letters.
     *
     * @param  array{q: string, options: array<string, string>, key: string, explanation: string}  $item
     * @return array<string, string>
     */
    private function repositionOptions(array $item, string $target, Randomizer $randomizer): array
    {
        $correct = $item['options'][$item['key']];

        $distractors = $randomizer->shuffleArray(
            array_values(array_diff_key($item['options'], [$item['key'] => null]))
        );

        $layout = [];
        $next = 0;

        foreach (self::LETTERS as $letter) {
            $layout[$letter] = $letter === $target
                ? $correct
                : $distractors[$next++];
        }

        return $layout;
    }

    /**
     * Guards against a typo silently producing a question with no correct answer.
     *
     * @param  array{q: string, options: array<string, string>, key: string, explanation: string}  $item
     */
    private function assertValid(array $item, int $index): void
    {
        $number = $index + 1;

        if (count($item['options']) !== count(self::LETTERS)) {
            throw new \RuntimeException(static::class." soal #{$number}: harus tepat 5 opsi.");
        }

        if (! array_key_exists($item['key'], $item['options'])) {
            throw new \RuntimeException(static::class." soal #{$number}: kunci '{$item['key']}' tidak ada di daftar opsi.");
        }

        if (count(array_unique($item['options'])) !== count(self::LETTERS)) {
            throw new \RuntimeException(static::class." soal #{$number}: ada opsi yang sama persis.");
        }

        if (trim($item['explanation']) === '') {
            throw new \RuntimeException(static::class." soal #{$number}: pembahasan tidak boleh kosong.");
        }
    }
}
