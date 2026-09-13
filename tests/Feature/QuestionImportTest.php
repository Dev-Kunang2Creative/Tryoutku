<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Tryout;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class QuestionImportTest extends TestCase
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

    public function test_learner_cannot_reach_the_import_page(): void
    {
        $learner = User::where('role', 'user')->firstOrFail();

        $this->actingAs($learner)
            ->get(route('admin.tryouts.questions.import.create', $this->tryout))
            ->assertForbidden();
    }

    public function test_manager_can_download_the_template(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('admin.tryouts.questions.import.template', $this->tryout));

        $response->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertStringContainsString(
            'template-soal-'.$this->tryout->slug.'.xlsx',
            $response->headers->get('content-disposition')
        );
    }

    public function test_a_valid_file_appends_questions_without_touching_existing_ones(): void
    {
        $existing = $this->tryout->questions()->count();
        $this->assertGreaterThan(0, $existing);

        $file = $this->spreadsheet([
            ['Apa satuan SI untuk gaya?', 'Joule', 'Newton', 'Watt', 'Pascal', 'Ohm', 'B', 'Gaya diukur dalam newton.', 5, ''],
            ['Air membeku pada suhu berapa?', '0 °C', '10 °C', '100 °C', '-10 °C', '50 °C', 'a', '', '', ''],
        ]);

        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.import.store', $this->tryout), ['file' => $file])
            ->assertRedirect(route('admin.tryouts.questions.index', $this->tryout))
            ->assertSessionHas('success', '2 soal berhasil ditambahkan ke bank soal.');

        $this->assertSame($existing + 2, $this->tryout->questions()->count());

        $imported = Question::where('question_text', 'Apa satuan SI untuk gaya?')->firstOrFail();
        $this->assertSame('5.00', $imported->score_weight);
        $this->assertSame('Gaya diukur dalam newton.', $imported->explanation);
        $this->assertCount(5, $imported->options);
        $this->assertSame('B', $imported->correctOption()->option_key);
        $this->assertSame('Newton', $imported->correctOption()->option_text);

        // Kunci huruf kecil tetap diterima, dan bobot kosong memakai nilai bawaan.
        $lowercase = Question::where('question_text', 'Air membeku pada suhu berapa?')->firstOrFail();
        $this->assertSame('A', $lowercase->correctOption()->option_key);
        $this->assertSame('10.00', $lowercase->score_weight);
        $this->assertNull($lowercase->explanation);
    }

    public function test_one_bad_row_cancels_the_whole_file(): void
    {
        $before = $this->tryout->questions()->count();

        $file = $this->spreadsheet([
            ['Soal yang benar', 'A', 'B', 'C', 'D', 'E', 'C', '', '', ''],
            ['Kunci di luar A-E', 'A', 'B', 'C', 'D', 'E', 'Z', '', '', ''],
            ['', 'A', 'B', 'C', 'D', 'E', 'A', '', '', ''],
        ]);

        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.import.store', $this->tryout), ['file' => $file])
            ->assertRedirect()
            ->assertSessionHasErrors('file');

        $this->assertSame($before, $this->tryout->questions()->count());
        $this->assertDatabaseMissing('questions', ['question_text' => 'Soal yang benar']);

        $errors = session('import_errors');
        $this->assertCount(2, $errors);
        $this->assertStringContainsString('Baris 3', $errors[0]);
        $this->assertStringContainsString('bukan salah satu dari A-E', $errors[0]);
        $this->assertStringContainsString('Baris 4', $errors[1]);
        $this->assertStringContainsString('Pertanyaan', $errors[1]);
    }

    public function test_blank_rows_are_skipped_and_an_empty_file_is_rejected(): void
    {
        $file = $this->spreadsheet([
            ['', '', '', '', '', '', '', '', '', ''],
        ]);

        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.import.store', $this->tryout), ['file' => $file])
            ->assertRedirect()
            ->assertSessionHasErrors('file');
    }

    public function test_a_non_spreadsheet_upload_is_rejected(): void
    {
        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.import.store', $this->tryout), [
                'file' => UploadedFile::fake()->create('soal.pdf', 8, 'application/pdf'),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('file');
    }

    public function test_an_image_pasted_on_a_question_row_is_saved_with_that_question(): void
    {
        $file = $this->spreadsheet(
            [
                ['Soal tanpa gambar', 'A', 'B', 'C', 'D', 'E', 'A', '', '', ''],
                ['Soal dengan grafik', 'A', 'B', 'C', 'D', 'E', 'C', '', '', ''],
            ],
            ['K3' => $this->pngFile()]   // baris 3 = soal kedua
        );

        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.import.store', $this->tryout), ['file' => $file])
            ->assertRedirect(route('admin.tryouts.questions.index', $this->tryout))
            ->assertSessionHas('success', '2 soal berhasil ditambahkan ke bank soal, 1 di antaranya bergambar.');

        $withImage = Question::where('question_text', 'Soal dengan grafik')->firstOrFail();
        $withoutImage = Question::where('question_text', 'Soal tanpa gambar')->firstOrFail();

        $this->assertNull($withoutImage->question_image);
        $this->assertNotNull($withImage->question_image);
        $this->assertStringStartsWith(Question::IMAGE_DIRECTORY.'/', $withImage->question_image);
        Storage::disk('public')->assertExists($withImage->question_image);
        $this->assertNotNull($withImage->imageUrl());
    }

    public function test_an_image_anchored_to_a_row_without_a_question_is_reported(): void
    {
        $file = $this->spreadsheet(
            [['Satu-satunya soal', 'A', 'B', 'C', 'D', 'E', 'A', '', '', '']],
            ['K7' => $this->pngFile()]   // baris 7 kosong: gambarnya tergeser
        );

        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.import.store', $this->tryout), ['file' => $file])
            ->assertRedirect()
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('questions', ['question_text' => 'Satu-satunya soal']);

        $errors = session('import_errors');
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Baris 7', $errors[0]);
        $this->assertStringContainsString('tidak berisi soal', $errors[0]);
    }

    public function test_a_rejected_file_leaves_no_orphaned_image_behind(): void
    {
        $before = Storage::disk('public')->allFiles(Question::IMAGE_DIRECTORY);

        $file = $this->spreadsheet(
            [
                ['Soal bergambar yang sah', 'A', 'B', 'C', 'D', 'E', 'A', '', '', ''],
                ['Soal dengan kunci salah', 'A', 'B', 'C', 'D', 'E', 'Z', '', '', ''],
            ],
            ['K2' => $this->pngFile()]
        );

        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.import.store', $this->tryout), ['file' => $file])
            ->assertSessionHasErrors('file');

        $this->assertSame($before, Storage::disk('public')->allFiles(Question::IMAGE_DIRECTORY));
    }

    public function test_two_images_on_one_row_are_refused(): void
    {
        $file = $this->spreadsheet(
            [['Soal dengan dua gambar', 'A', 'B', 'C', 'D', 'E', 'A', '', '', '']],
            ['K2' => $this->pngFile(), 'L2' => $this->pngFile(40, 20)]
        );

        $this->actingAs($this->manager)
            ->post(route('admin.tryouts.questions.import.store', $this->tryout), ['file' => $file])
            ->assertSessionHasErrors('file');

        $this->assertStringContainsString('lebih dari satu gambar', session('import_errors')[0]);
    }

    public function test_the_template_carries_the_image_column(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('admin.tryouts.questions.import.template', $this->tryout));

        $path = tempnam(sys_get_temp_dir(), 'template').'.xlsx';
        file_put_contents($path, $response->streamedContent());

        $sheet = IOFactory::load($path)->getSheet(0);

        $this->assertSame('Gambar', $sheet->getCell('K1')->getValue());
    }

    /**
     * Bangun berkas .xlsx sungguhan dengan header template, supaya jalur
     * pembacaan yang diuji sama dengan yang dipakai pengguna.
     *
     * @param  list<list<mixed>>  $rows
     * @param  array<string, string>  $images  koordinat sel => path berkas gambar
     */
    private function spreadsheet(array $rows, array $images = []): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'Pertanyaan', 'Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Pilihan E',
            'Kunci (A-E)', 'Pembahasan', 'Bobot', 'Urutan', 'Gambar',
        ], null, 'A1');

        $sheet->fromArray($rows, null, 'A2');

        foreach ($images as $cell => $imagePath) {
            $drawing = new Drawing;
            $drawing->setPath($imagePath);
            $drawing->setCoordinates($cell);
            $drawing->setWorksheet($sheet);
        }

        $path = tempnam(sys_get_temp_dir(), 'import').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'soal.xlsx', null, null, true);
    }

    /**
     * Tulis sebuah PNG sungguhan ke berkas sementara, supaya yang diuji adalah
     * jalur gambar yang sebenarnya dan bukan data tiruan.
     */
    private function pngFile(int $width = 60, int $height = 30): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 20, 110, 200));

        $path = tempnam(sys_get_temp_dir(), 'gambar').'.png';
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }
}
