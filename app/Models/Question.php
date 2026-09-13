<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Question extends Model
{
    public const IMAGE_DIRECTORY = 'questions';

    protected $fillable = [
        'tryout_id',
        'question_text',
        'question_image',
        'explanation',
        'score_weight',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'score_weight' => 'decimal:2',
            'order' => 'integer',
        ];
    }

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('option_key', 'asc');
    }

    /**
     * Berkas gambar ikut terhapus bersama soalnya, supaya penyimpanan tidak
     * menumpuk gambar yang tidak lagi dirujuk siapa pun.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $question): void {
            $question->deleteImageFile();
        });
    }

    /**
     * Simpan berkas gambar baru untuk soal ini, mengganti yang lama bila ada.
     */
    public function replaceImage(UploadedFile $file): void
    {
        $this->deleteImageFile();
        $this->question_image = $file->store(self::IMAGE_DIRECTORY, 'public');
        $this->save();
    }

    /**
     * Simpan gambar yang sudah berupa deretan byte, misalnya hasil ekstraksi
     * dari berkas Excel yang diunggah.
     */
    public static function storeImageContents(string $contents, string $extension): string
    {
        $path = self::IMAGE_DIRECTORY.'/'.Str::random(40).'.'.$extension;

        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    public function removeImage(): void
    {
        $this->deleteImageFile();
        $this->question_image = null;
        $this->save();
    }

    private function deleteImageFile(): void
    {
        if ($this->question_image) {
            Storage::disk('public')->delete($this->question_image);
        }
    }

    /**
     * Alamat publik gambar soal, atau null bila soal ini tanpa gambar.
     */
    public function imageUrl(): ?string
    {
        return $this->question_image ? Storage::disk('public')->url($this->question_image) : null;
    }

    public function correctOption(): ?QuestionOption
    {
        return $this->options()->where('is_correct', true)->first();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(QuestionProgress::class);
    }

    public function progressFor(User $user): ?QuestionProgress
    {
        return $this->progress()->where('user_id', $user->id)->first();
    }
}
