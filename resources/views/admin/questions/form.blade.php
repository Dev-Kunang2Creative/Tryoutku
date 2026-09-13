@php
    /** @var \App\Models\Question|null $question */
    $question = $question ?? null;

    $optionsByKey = $question ? $question->options->keyBy('option_key') : collect();
    $correctKey = $question ? (optional($question->correctOption())->option_key ?? 'A') : 'A';
@endphp

<div class="space-y-5">
    <div>
        <label for="question_text" class="form-label">Teks pertanyaan <span class="text-red-500">*</span></label>
        <textarea id="question_text" name="question_text" rows="5" required class="form-input resize-y"
            placeholder="Tuliskan butir soal olimpiade secara jelas&hellip;">{{ old('question_text', $question?->question_text) }}</textarea>
        <x-input-error for="question_text" />
    </div>

    <div>
        <label for="image" class="form-label">Gambar soal <span class="font-normal text-slate-400">(opsional)</span></label>

        @if($question?->imageUrl())
            <div class="mb-3 flex flex-wrap items-start gap-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
                <img src="{{ $question->imageUrl() }}" alt="Gambar soal saat ini"
                    class="max-h-40 w-auto max-w-full rounded border border-slate-200 bg-white">

                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remove_image" value="1" class="form-checkbox">
                    Hapus gambar ini
                </label>
            </div>
        @endif

        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp"
            class="block w-full cursor-pointer rounded-lg border border-slate-300 text-sm text-slate-600 transition-colors file:mr-4 file:cursor-pointer file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-slate-700 hover:border-slate-400 hover:file:bg-slate-200">
        <p class="form-hint">
            JPG, PNG, GIF, atau WEBP; maksimal 2 MB.
            @if($question?->imageUrl())
                Memilih berkas baru akan menggantikan gambar di atas.
            @endif
        </p>
        <x-input-error for="image" />
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="score_weight" class="form-label">Bobot nilai <span class="text-red-500">*</span></label>
            <div class="relative">
                <input type="number" id="score_weight" name="score_weight"
                    value="{{ old('score_weight', $question?->score_weight ?? 10) }}"
                    step="0.1" min="0.1" required class="form-input pr-12">
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-slate-400">poin</span>
            </div>
            <p class="form-hint">Poin jika peserta menjawab benar.</p>
            <x-input-error for="score_weight" />
        </div>

        <div>
            <label for="order" class="form-label">Nomor urut</label>
            <input type="number" id="order" name="order"
                value="{{ old('order', $question?->order ?? ($nextOrder ?? 1)) }}" min="1" class="form-input">
            <p class="form-hint">Menentukan posisi soal dalam paket.</p>
            <x-input-error for="order" />
        </div>
    </div>

    {{-- Options A–E --}}
    <fieldset>
        <legend class="form-label">Opsi jawaban &amp; kunci <span class="text-red-500">*</span></legend>

        <p class="mb-3 -mt-0.5 text-xs leading-relaxed text-slate-500">
            Isi kelima opsi, lalu pilih tombol radio pada opsi yang menjadi kunci jawaban.
        </p>

        <div class="space-y-2">
            @foreach(['A', 'B', 'C', 'D', 'E'] as $key)
                @php
                    $optionValue = old("options.{$key}", isset($optionsByKey[$key]) ? $optionsByKey[$key]->option_text : '');
                    $isCorrect = old('correct_option', $correctKey) === $key;
                @endphp

                <div class="flex items-center gap-2.5 rounded-lg border p-2 transition-colors {{ $isCorrect ? 'border-emerald-300 bg-emerald-50/60' : 'border-slate-200' }}">
                    <label class="flex shrink-0 cursor-pointer items-center gap-2 px-1"
                        title="Tandai opsi {{ $key }} sebagai kunci jawaban">
                        <input type="radio" name="correct_option" value="{{ $key }}" class="form-radio" @checked($isCorrect)>
                        <span class="w-4 text-sm font-semibold text-slate-700">{{ $key }}</span>
                        <span class="sr-only">Jadikan opsi {{ $key }} kunci jawaban</span>
                    </label>

                    <input type="text" name="options[{{ $key }}]" value="{{ $optionValue }}" required
                        class="form-input border-slate-200" placeholder="Teks opsi {{ $key }}">
                </div>
            @endforeach
        </div>

        <x-input-error for="correct_option" />
        <x-input-error for="options" />
    </fieldset>

    <div>
        <label for="explanation" class="form-label">Pembahasan</label>
        <textarea id="explanation" name="explanation" rows="4" class="form-input resize-y"
            placeholder="Jelaskan cara pengerjaan atau teorema yang digunakan&hellip;">{{ old('explanation', $question?->explanation) }}</textarea>
        <p class="form-hint">Ditampilkan kepada peserta setelah sesi ujian selesai.</p>
        <x-input-error for="explanation" />
    </div>
</div>

<script>
    // Keep the highlighted row in sync with the selected answer key.
    document.querySelectorAll('input[name="correct_option"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('input[name="correct_option"]').forEach((sibling) => {
                const row = sibling.closest('div');
                const isChecked = sibling.checked;

                row.classList.toggle('border-emerald-300', isChecked);
                row.classList.toggle('bg-emerald-50/60', isChecked);
                row.classList.toggle('border-slate-200', !isChecked);
            });
        });
    });
</script>
