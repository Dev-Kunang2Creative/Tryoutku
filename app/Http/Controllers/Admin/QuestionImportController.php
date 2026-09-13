<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Tryout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Pengisian bank soal secara massal lewat berkas Excel.
 *
 * Impor bersifat menambah: soal yang sudah ada di paket tidak pernah disentuh,
 * sehingga riwayat penguasaan (mastery) pelajar tetap utuh.
 */
class QuestionImportController extends Controller
{
    /**
     * Urutan dan nama kolom template. Kunci larik adalah nama header yang
     * dicari di baris pertama berkas.
     *
     * @var array<string, string>
     */
    private const COLUMNS = [
        'question_text' => 'Pertanyaan',
        'option_a' => 'Pilihan A',
        'option_b' => 'Pilihan B',
        'option_c' => 'Pilihan C',
        'option_d' => 'Pilihan D',
        'option_e' => 'Pilihan E',
        'correct_option' => 'Kunci (A-E)',
        'explanation' => 'Pembahasan',
        'score_weight' => 'Bobot',
        'order' => 'Urutan',
        'image' => 'Gambar',
    ];

    /**
     * Kolom yang wajib terisi pada setiap baris soal.
     *
     * @var list<string>
     */
    private const REQUIRED_COLUMNS = [
        'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_option',
    ];

    private const OPTION_KEYS = ['A', 'B', 'C', 'D', 'E'];

    /**
     * Batas wajar untuk satu berkas, supaya unggahan keliru tidak menggantung
     * permintaan sampai kehabisan waktu.
     */
    private const MAX_ROWS = 500;

    /**
     * Gambar yang ditempel di berkas Excel mengikuti batas yang sama dengan
     * unggahan gambar satuan lewat form soal.
     */
    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024;

    /**
     * @var list<string>
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function create(Tryout $tryout)
    {
        return view('admin.questions.import', [
            'tryout' => $tryout,
            'columns' => self::COLUMNS,
            'requiredColumns' => self::REQUIRED_COLUMNS,
            'maxRows' => self::MAX_ROWS,
        ]);
    }

    /**
     * Unduh template kosong yang sudah berisi header, contoh pengisian, dan
     * daftar pilihan untuk kolom kunci jawaban.
     */
    public function template(Tryout $tryout): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $this->buildQuestionSheet($spreadsheet->getActiveSheet());
        $this->buildGuideSheet($spreadsheet->createSheet(), $tryout);
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'template-soal-'.$tryout->slug.'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function store(Request $request, Tryout $tryout): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [], ['file' => 'berkas Excel']);

        try {
            [$rows, $images] = $this->readSheet($request->file('file')->getRealPath());
        } catch (ReaderException $exception) {
            return back()->withErrors([
                'file' => 'Berkas tidak bisa dibaca. Pastikan yang diunggah benar-benar berkas Excel (.xlsx).',
            ]);
        }

        if ($rows === []) {
            return back()->withErrors([
                'file' => 'Tidak ada baris soal yang terisi di berkas ini.',
            ]);
        }

        [$questions, $rowErrors] = $this->parseRows($rows, $images);

        if ($rowErrors !== []) {
            return back()
                ->withErrors(['file' => 'Impor dibatalkan, tidak ada soal yang tersimpan. Perbaiki dulu baris berikut.'])
                ->with('import_errors', $rowErrors);
        }

        $withImages = $this->saveQuestions($tryout, $questions);

        return redirect()->route('admin.tryouts.questions.index', $tryout)
            ->with('success', $this->importSummary(count($questions), $withImages));
    }

    /**
     * Baca berkas menjadi baris teks dan gambar yang tertempel padanya.
     *
     * Gambar di Excel mengambang di atas lembar, bukan berada di dalam sel,
     * sehingga yang dipakai adalah baris tempat sudut kiri atasnya menambat.
     *
     * @return array{0: list<array{row: int, values: array<string, string>}>, 1: array<int, array{contents: string, extension: string, problem: string|null}>}
     */
    private function readSheet(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        // Gambar hanya ikut terbaca kalau pembacaan tidak dibatasi ke data saja.
        $reader->setReadDataOnly(false);
        $sheet = $reader->load($path)->getSheet(0);

        $images = $this->collectImages($sheet);

        if ($sheet->getHighestDataRow() < 2) {
            return [[], $images];
        }

        $headerMap = $this->mapHeaderColumns($sheet);
        $rows = [];

        foreach ($sheet->getRowIterator(2) as $row) {
            $values = [];

            foreach ($headerMap as $field => $columnIndex) {
                $values[$field] = trim($this->cellText($sheet, $columnIndex, $row->getRowIndex()));
            }

            // Baris kosong yang hanya ditempeli gambar sengaja dilewati di sini
            // agar dilaporkan sebagai gambar yang tergeser, bukan sebagai tujuh
            // kolom wajib yang kosong.
            if (implode('', $values) === '') {
                continue;
            }

            $rows[] = ['row' => $row->getRowIndex(), 'values' => $values];

            if (count($rows) >= self::MAX_ROWS) {
                break;
            }
        }

        return [$rows, $images];
    }

    /**
     * Kumpulkan gambar per baris. Keluhan disimpan bersama gambarnya agar bisa
     * dilaporkan dengan nomor baris, bukan digagalkan diam-diam.
     *
     * @return array<int, array{contents: string, extension: string, problem: string|null}>
     */
    private function collectImages(Worksheet $sheet): array
    {
        $images = [];

        foreach ($sheet->getDrawingCollection() as $drawing) {
            $row = (int) preg_replace('/[^0-9]/', '', $drawing->getCoordinates());

            if ($row < 1) {
                continue;
            }

            if (isset($images[$row])) {
                $images[$row]['problem'] = 'ada lebih dari satu gambar menempel di baris ini';

                continue;
            }

            $images[$row] = $this->readDrawing($drawing);
        }

        return $images;
    }

    /**
     * @return array{contents: string, extension: string, problem: string|null}
     */
    private function readDrawing(BaseDrawing $drawing): array
    {
        $image = ['contents' => '', 'extension' => '', 'problem' => null];

        if (! $drawing instanceof Drawing) {
            $image['problem'] = 'jenis gambar ini tidak didukung, tempel ulang sebagai gambar biasa';

            return $image;
        }

        $extension = strtolower($drawing->getExtension());

        if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            $image['problem'] = 'format gambar "'.$extension.'" tidak didukung';

            return $image;
        }

        $contents = @file_get_contents($drawing->getPath());

        if ($contents === false || $contents === '') {
            $image['problem'] = 'gambarnya tidak bisa dibaca dari berkas';

            return $image;
        }

        if (strlen($contents) > self::MAX_IMAGE_BYTES) {
            $image['problem'] = 'ukuran gambar melebihi 2 MB';

            return $image;
        }

        if (getimagesizefromstring($contents) === false) {
            $image['problem'] = 'isinya bukan gambar yang sah';

            return $image;
        }

        return ['contents' => $contents, 'extension' => $extension === 'jpeg' ? 'jpg' : $extension, 'problem' => null];
    }

    /**
     * Cocokkan header di baris pertama dengan kolom template. Pencocokan
     * memakai nama teknis (question_text) maupun judul bahasa Indonesia,
     * dan jatuh kembali ke urutan kolom bila header tidak dikenali.
     *
     * @return array<string, int>
     */
    private function mapHeaderColumns(Worksheet $sheet): array
    {
        $headings = [];

        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $headings[$cell->getColumn()] = $this->normalise($this->plainText($cell->getValue()));
            }
        }

        $map = [];
        $position = 1;

        foreach (self::COLUMNS as $field => $label) {
            $wanted = [$this->normalise($field), $this->normalise($label)];
            $columnIndex = null;

            foreach ($headings as $column => $heading) {
                if ($heading !== '' && in_array($heading, $wanted, true)) {
                    $columnIndex = Coordinate::columnIndexFromString($column);
                    break;
                }
            }

            $map[$field] = $columnIndex ?? $position;
            $position++;
        }

        return $map;
    }

    /**
     * Ubah tiap baris menjadi data soal siap simpan, sambil mengumpulkan
     * keluhan per baris. Impor bersifat semua-atau-tidak sama sekali.
     *
     * @param  list<array{row: int, values: array<string, string>}>  $rows
     * @param  array<int, array{contents: string, extension: string, problem: string|null}>  $images
     * @return array{0: list<array<string, mixed>>, 1: list<string>}
     */
    private function parseRows(array $rows, array $images): array
    {
        $questions = [];
        $errors = [];
        $claimedImageRows = [];

        foreach ($rows as $entry) {
            $values = $entry['values'];
            $problems = [];
            $image = $images[$entry['row']] ?? null;

            if ($image !== null) {
                $claimedImageRows[] = $entry['row'];

                if ($image['problem'] !== null) {
                    $problems[] = $image['problem'];
                }
            }

            foreach (self::REQUIRED_COLUMNS as $field) {
                if ($values[$field] === '') {
                    $problems[] = 'kolom "'.self::COLUMNS[$field].'" kosong';
                }
            }

            $correct = strtoupper($values['correct_option']);

            if ($values['correct_option'] !== '' && ! in_array($correct, self::OPTION_KEYS, true)) {
                $problems[] = 'kunci jawaban "'.$values['correct_option'].'" bukan salah satu dari A-E';
            }

            $weight = $values['score_weight'];

            if ($weight !== '' && (! is_numeric($weight) || (float) $weight <= 0)) {
                $problems[] = 'bobot "'.$weight.'" bukan angka lebih dari nol';
            }

            $order = $values['order'];

            if ($order !== '' && ! ctype_digit($order)) {
                $problems[] = 'urutan "'.$order.'" bukan bilangan bulat';
            }

            if ($problems !== []) {
                $errors[$entry['row']] = 'Baris '.$entry['row'].': '.implode('; ', $problems).'.';

                continue;
            }

            $questions[] = [
                'question_text' => $values['question_text'],
                'explanation' => $values['explanation'] !== '' ? $values['explanation'] : null,
                'score_weight' => $weight !== '' ? (float) $weight : 10.00,
                'order' => $order !== '' ? (int) $order : null,
                'options' => [
                    'A' => $values['option_a'],
                    'B' => $values['option_b'],
                    'C' => $values['option_c'],
                    'D' => $values['option_d'],
                    'E' => $values['option_e'],
                ],
                'correct_option' => $correct,
                'image' => $image,
            ];
        }

        // Gambar yang menambat di baris tanpa soal hampir selalu berarti gambar
        // itu tergeser; menolaknya lebih baik daripada membuangnya diam-diam.
        foreach (array_diff(array_keys($images), $claimedImageRows) as $strayRow) {
            $errors[$strayRow] = 'Baris '.$strayRow.': ada gambar menempel di baris yang tidak berisi soal. '
                .'Geser gambarnya ke baris soal yang benar.';
        }

        // Dikunci per nomor baris supaya keluhan tampil urut seperti di Excel.
        ksort($errors);

        return [$questions, array_values($errors)];
    }

    /**
     * Simpan seluruh soal beserta gambarnya, lalu laporkan berapa yang bergambar.
     *
     * Berkas gambar hidup di luar transaksi basis data, jadi kalau penyimpanan
     * gagal di tengah jalan berkas yang terlanjur ditulis dihapus sendiri agar
     * tidak menjadi sampah yang tidak dirujuk soal mana pun.
     *
     * @param  list<array<string, mixed>>  $questions
     */
    private function saveQuestions(Tryout $tryout, array $questions): int
    {
        $storedImages = [];

        try {
            return DB::transaction(function () use ($tryout, $questions, &$storedImages): int {
                $nextOrder = (int) $tryout->questions()->max('order') + 1;

                foreach ($questions as $data) {
                    $imagePath = null;

                    if ($data['image'] !== null) {
                        $imagePath = Question::storeImageContents($data['image']['contents'], $data['image']['extension']);
                        $storedImages[] = $imagePath;
                    }

                    $question = $tryout->questions()->create([
                        'question_text' => $data['question_text'],
                        'question_image' => $imagePath,
                        'explanation' => $data['explanation'],
                        'score_weight' => $data['score_weight'],
                        'order' => $data['order'] ?? $nextOrder,
                    ]);

                    $nextOrder++;

                    foreach ($data['options'] as $key => $text) {
                        $question->options()->create([
                            'option_key' => $key,
                            'option_text' => $text,
                            'is_correct' => $key === $data['correct_option'],
                        ]);
                    }
                }

                return count($storedImages);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedImages);

            throw $exception;
        }
    }

    private function importSummary(int $total, int $withImages): string
    {
        $summary = $total.' soal berhasil ditambahkan ke bank soal';

        return $withImages > 0
            ? $summary.', '.$withImages.' di antaranya bergambar.'
            : $summary.'.';
    }

    private function buildQuestionSheet(Worksheet $sheet): void
    {
        $sheet->setTitle('Soal');

        $widths = [58, 22, 22, 22, 22, 22, 12, 48, 9, 9, 30];
        $column = 1;

        foreach (self::COLUMNS as $field => $label) {
            $sheet->setCellValue([$column, 1], $label);
            $sheet->getColumnDimensionByColumn($column)->setWidth($widths[$column - 1]);
            $column++;
        }

        $lastColumn = count(self::COLUMNS);
        $header = $sheet->getStyle([1, 1, $lastColumn, 1]);
        $header->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $header->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
        $header->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->freezePane('A2');

        $sheet->fromArray([
            'Air murni pada tekanan 1 atm mendidih pada suhu berapa?',
            '90 °C', '100 °C', '110 °C', '120 °C', '80 °C',
            'B',
            'Pada tekanan 1 atm titik didih air murni adalah 100 °C.',
            10, 1,
            '',
        ], null, 'A2');

        // Baris dibuat tinggi supaya gambar yang ditempel muat di dalam satu baris.
        for ($row = 2; $row <= self::MAX_ROWS + 1; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(90);
        }

        $sheet->getStyle([1, 2, $lastColumn, self::MAX_ROWS + 1])
            ->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $this->applyAnswerKeyDropdown($sheet);
    }

    /**
     * Kolom kunci jawaban dibatasi menjadi daftar pilihan A-E agar salah ketik
     * ketahuan sebelum berkas diunggah.
     */
    private function applyAnswerKeyDropdown(Worksheet $sheet): void
    {
        $validation = $sheet->getCell('G2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowDropDown(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Kunci jawaban tidak valid');
        $validation->setError('Pilih salah satu: A, B, C, D, atau E.');
        $validation->setFormula1('"A,B,C,D,E"');

        $sheet->setDataValidation('G2:G'.(self::MAX_ROWS + 1), clone $validation);
    }

    private function buildGuideSheet(Worksheet $sheet, Tryout $tryout): void
    {
        $sheet->setTitle('Petunjuk');
        $sheet->getColumnDimension('A')->setWidth(100);

        $lines = [
            'Cara mengisi template soal',
            '',
            'Paket tujuan: '.$tryout->title.' ('.$tryout->subject.')',
            '',
            '1. Isi mulai baris ke-2 pada lembar "Soal". Jangan mengubah atau menghapus baris header.',
            '2. Satu baris berisi satu soal lengkap dengan lima pilihan jawaban.',
            '3. Kolom wajib: Pertanyaan, Pilihan A sampai Pilihan E, dan Kunci.',
            '4. Kolom Kunci hanya boleh berisi huruf A, B, C, D, atau E.',
            '5. Kolom Pembahasan boleh dikosongkan.',
            '6. Kolom Bobot boleh dikosongkan; jika kosong akan dianggap 10 poin.',
            '7. Kolom Urutan boleh dikosongkan; jika kosong soal ditaruh di urutan berikutnya.',
            '8. Hapus baris contoh sebelum mengunggah, kecuali memang ingin dipakai.',
            '9. Maksimal '.self::MAX_ROWS.' baris soal per berkas.',
            '',
            'Menambahkan gambar pada soal',
            '',
            'Tempel gambar ke kolom "Gambar" pada baris soal yang bersangkutan.',
            'Gambar boleh JPG, PNG, GIF, atau WEBP, dan maksimal 2 MB per gambar.',
            'Satu baris soal hanya boleh memiliki satu gambar.',
            '',
            'PENTING: di Excel gambar mengambang di atas lembar, bukan berada di dalam sel.',
            'Yang dipakai adalah baris tempat sudut KIRI ATAS gambar berada. Setelah menempel,',
            'seret gambar sampai sudut kiri atasnya benar-benar berada di dalam baris soal itu,',
            'lalu perkecil ukurannya agar tidak menutupi baris lain.',
            'Gambar yang menempel di baris tanpa soal akan menggagalkan seluruh berkas,',
            'lengkap dengan nomor barisnya, supaya tidak ada gambar yang tertukar diam-diam.',
            '',
            'Jika ada satu baris yang salah, seluruh berkas ditolak dan tidak ada soal yang tersimpan.',
            'Pesan kesalahan akan menyebutkan nomor barisnya, sehingga bisa langsung diperbaiki.',
            '',
            'Mengunggah berkas tidak menghapus soal yang sudah ada. Soal baru selalu ditambahkan.',
        ];

        foreach ($lines as $index => $line) {
            $sheet->setCellValue([1, $index + 1], $line);
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getFont()->setBold(true);
    }

    private function cellText(Worksheet $sheet, int $columnIndex, int $rowIndex): string
    {
        return $this->plainText($sheet->getCell([$columnIndex, $rowIndex])->getValue());
    }

    private function plainText(mixed $value): string
    {
        if ($value instanceof RichText) {
            return $value->getPlainText();
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        return $value === null ? '' : (string) $value;
    }

    private function normalise(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($value)) ?? '';
    }
}
