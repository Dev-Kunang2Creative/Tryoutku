@props(['answers', 'answerLabel' => 'Jawaban kamu'])

@php
    /**
     * Explanation browser: shows one question at a time and lets the reader jump
     * straight to any question through the number palette — the same interaction
     * as the exam screen, but read-only.
     *
     * @var \Illuminate\Support\Collection<int, \App\Models\ExamAnswer> $answers
     */
    $verdictFor = function ($answer): array {
        if (! $answer->question_option_id) {
            return ['key' => 'empty', 'label' => 'Tidak dijawab', 'icon' => 'minus-circle', 'text' => 'text-slate-600', 'dot' => 'border-slate-300 bg-slate-100 text-slate-500'];
        }

        if ($answer->is_correct) {
            return ['key' => 'correct', 'label' => 'Benar', 'icon' => 'check-circle', 'text' => 'text-emerald-700', 'dot' => 'border-emerald-600 bg-emerald-600 text-white'];
        }

        return ['key' => 'wrong', 'label' => 'Salah', 'icon' => 'x-circle', 'text' => 'text-red-700', 'dot' => 'border-red-600 bg-red-600 text-white'];
    };

    $total = $answers->count();
@endphp

<div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_17rem] lg:gap-6" data-explorer>
    {{-- Question stage --}}
    <div class="card min-w-0 p-4 sm:p-6">
        @foreach($answers as $index => $answer)
            @php
                $question = $answer->question;
                $selected = $answer->selectedOption;
                $verdict = $verdictFor($answer);
            @endphp

            <section data-pane="{{ $index }}" class="{{ $index === 0 ? '' : 'hidden' }}"
                aria-label="Pembahasan soal {{ $index + 1 }}">

                <header class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 border-b border-slate-100 pb-3.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm font-medium text-slate-900">
                            Soal <span class="tabular">{{ $index + 1 }}</span>
                            <span class="font-normal text-slate-400">dari <span class="tabular">{{ $total }}</span></span>
                        </span>

                        <span class="inline-flex items-center gap-1.5 text-sm font-medium {{ $verdict['text'] }}">
                            <x-icon :name="$verdict['icon']" class="size-4" />
                            {{ $verdict['label'] }}
                        </span>
                    </div>

                    <span class="text-xs text-slate-500">
                        Bobot <span class="font-medium text-slate-700 tabular">{{ $question->score_weight }}</span> poin
                    </span>
                </header>

                <div class="mt-4 whitespace-pre-line text-[0.9375rem] leading-relaxed text-slate-800 sm:text-base">{{ $question->question_text }}</div>
                @if($question->imageUrl())
                    <figure class="mt-4">
                        <img src="{{ $question->imageUrl() }}" alt="Gambar pendukung soal"
                            class="max-h-80 w-auto max-w-full rounded-lg border border-slate-200 bg-white">
                    </figure>
                @endif

                <ul class="mt-5 space-y-2.5">
                    @foreach($question->options as $option)
                        @php
                            $isSelected = $selected && $selected->id === $option->id;
                            $isKey = (bool) $option->is_correct;

                            $row = match (true) {
                                $isKey => 'border-emerald-300 bg-emerald-50/60',
                                $isSelected => 'border-red-300 bg-red-50/60',
                                default => 'border-slate-200',
                            };

                            $badge = match (true) {
                                $isKey => 'bg-emerald-600 text-white',
                                $isSelected => 'bg-red-600 text-white',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp

                        <li class="flex flex-wrap items-start gap-x-3 gap-y-2 rounded-lg border px-3 py-3 {{ $row }}">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-md text-sm font-semibold {{ $badge }}">
                                {{ $option->option_key }}
                            </span>

                            <span class="min-w-0 flex-1 text-sm leading-relaxed text-slate-700">{{ $option->option_text }}</span>

                            <span class="flex shrink-0 flex-wrap items-center gap-1.5">
                                @if($isSelected)
                                    <span class="badge badge-neutral">{{ $answerLabel }}</span>
                                @endif

                                @if($isKey)
                                    <span class="badge badge-success">
                                        <x-icon name="check" class="size-3" />
                                        Kunci
                                    </span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>

                @if($question->explanation)
                    <div class="mt-5 rounded-lg border border-brand-200 bg-brand-50/60 p-4">
                        <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-brand-700">
                            <x-icon name="lightbulb" class="size-4" />
                            Pembahasan
                        </p>

                        <div class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $question->explanation }}</div>
                    </div>
                @else
                    <p class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                        Belum ada pembahasan untuk soal ini.
                    </p>
                @endif
            </section>
        @endforeach

        {{-- Desktop navigation --}}
        <div class="mt-6 hidden items-center justify-between gap-3 border-t border-slate-100 pt-5 lg:flex">
            <button type="button" data-nav="prev" class="btn btn-secondary">
                <x-icon name="chevron-left" class="size-4" />
                Soal sebelumnya
            </button>

            <button type="button" data-nav="next" class="btn btn-secondary">
                Soal berikutnya
                <x-icon name="chevron-right" class="size-4" />
            </button>
        </div>
    </div>

    {{-- Palette: sidebar on desktop, bottom sheet on phones --}}
    <aside data-sheet
        class="invisible fixed inset-x-0 bottom-0 z-50 max-h-[80vh] translate-y-full overflow-y-auto rounded-t-2xl border-t border-slate-200 bg-white p-4 shadow-[0_-8px_30px_-12px_rgb(15_23_42/0.25)] transition-transform duration-200 ease-out
               lg:visible lg:sticky lg:inset-x-auto lg:bottom-auto lg:top-[5.5rem] lg:z-auto lg:max-h-[calc(100vh-7rem)] lg:translate-y-0 lg:self-start lg:rounded-xl lg:border lg:p-5 lg:shadow-none"
        aria-label="Pilih nomor soal">

        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">Pilih soal</h3>

            <button type="button" data-sheet-close class="btn btn-ghost btn-sm px-1.5 lg:hidden">
                <x-icon name="x" class="size-4" />
                <span class="sr-only">Tutup</span>
            </button>
        </div>

        <div class="grid grid-cols-6 gap-2 sm:grid-cols-8 lg:grid-cols-5">
            @foreach($answers as $index => $answer)
                @php $verdict = $verdictFor($answer); @endphp

                <button type="button" data-jump="{{ $index }}"
                    class="flex aspect-square items-center justify-center rounded-md border text-xs font-medium tabular transition-transform {{ $verdict['dot'] }} {{ $index === 0 ? 'ring-2 ring-brand-500 ring-offset-1' : '' }}"
                    aria-label="Soal {{ $index + 1 }}: {{ $verdict['label'] }}">
                    {{ $index + 1 }}
                </button>
            @endforeach
        </div>

        <dl class="mt-4 space-y-2 border-t border-slate-100 pt-3.5 text-xs">
            <div class="flex items-center gap-2">
                <span class="size-3 shrink-0 rounded bg-emerald-600"></span>
                <dt class="text-slate-600">Benar</dt>
                <dd class="ml-auto font-medium text-slate-900 tabular">{{ $answers->where('is_correct', true)->count() }}</dd>
            </div>
            <div class="flex items-center gap-2">
                <span class="size-3 shrink-0 rounded bg-red-600"></span>
                <dt class="text-slate-600">Salah</dt>
                <dd class="ml-auto font-medium text-slate-900 tabular">{{ $answers->where('is_correct', false)->whereNotNull('question_option_id')->count() }}</dd>
            </div>
            <div class="flex items-center gap-2">
                <span class="size-3 shrink-0 rounded border border-slate-300 bg-slate-100"></span>
                <dt class="text-slate-600">Tidak dijawab</dt>
                <dd class="ml-auto font-medium text-slate-900 tabular">{{ $answers->whereNull('question_option_id')->count() }}</dd>
            </div>
        </dl>
    </aside>
</div>

{{-- Mobile bar --}}
<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)] lg:hidden" data-explorer-bar>
    <div class="flex items-center gap-2 px-3 py-2.5">
        <button type="button" data-nav="prev" class="btn btn-secondary px-3" aria-label="Soal sebelumnya">
            <x-icon name="chevron-left" class="size-5" />
        </button>

        <button type="button" data-sheet-open class="btn btn-ghost min-w-0 flex-1 border border-slate-200">
            <x-icon name="grid" class="size-4 shrink-0" />
            <span class="truncate">
                Soal <span class="tabular" data-current>1</span>/<span class="tabular">{{ $total }}</span>
            </span>
            <x-icon name="chevron-up" class="size-4 shrink-0 text-slate-400" />
        </button>

        <button type="button" data-nav="next" class="btn btn-primary px-3" aria-label="Soal berikutnya">
            <x-icon name="chevron-right" class="size-5" />
        </button>
    </div>
</nav>

<div data-sheet-backdrop class="fixed inset-0 z-40 hidden bg-slate-900/40 lg:hidden" aria-hidden="true"></div>

<script>
    (() => {
        const TOTAL = {{ $total }};
        const RING = ['ring-2', 'ring-brand-500', 'ring-offset-1'];

        const panes = Array.from(document.querySelectorAll('[data-pane]'));
        const dots = Array.from(document.querySelectorAll('[data-jump]'));
        const sheet = document.querySelector('[data-sheet]');
        const backdrop = document.querySelector('[data-sheet-backdrop]');

        let current = 0;

        const setSheet = (isOpen) => {
            sheet.classList.toggle('translate-y-full', !isOpen);
            sheet.classList.toggle('invisible', !isOpen);
            backdrop.classList.toggle('hidden', !isOpen);
            document.body.classList.toggle('overflow-hidden', isOpen);
        };

        const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

        const goTo = (index) => {
            if (index < 0 || index >= TOTAL || index === current) {
                return;
            }

            panes[current].classList.add('hidden');
            dots[current].classList.remove(...RING);

            current = index;

            panes[current].classList.remove('hidden');
            dots[current].classList.add(...RING);

            document.querySelectorAll('[data-current]').forEach((el) => {
                el.textContent = current + 1;
            });

            document.querySelectorAll('[data-nav="prev"]').forEach((el) => {
                el.disabled = current === 0;
            });

            document.querySelectorAll('[data-nav="next"]').forEach((el) => {
                el.disabled = current === TOTAL - 1;
            });

            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-jump], [data-nav], [data-sheet-open], [data-sheet-close]');

            if (!trigger) {
                return;
            }

            if (trigger.hasAttribute('data-sheet-open')) {
                setSheet(true);
                return;
            }

            if (trigger.hasAttribute('data-sheet-close')) {
                setSheet(false);
                return;
            }

            if (trigger.dataset.jump !== undefined) {
                goTo(Number(trigger.dataset.jump));

                if (!isDesktop()) {
                    setSheet(false);
                }

                return;
            }

            goTo(trigger.dataset.nav === 'next' ? current + 1 : current - 1);
        });

        backdrop.addEventListener('click', () => setSheet(false));

        document.addEventListener('keydown', (event) => {
            if (event.metaKey || event.ctrlKey || event.altKey) {
                return;
            }

            if (event.key === 'Escape') {
                setSheet(false);
            } else if (event.key === 'ArrowRight') {
                goTo(current + 1);
            } else if (event.key === 'ArrowLeft') {
                goTo(current - 1);
            }
        });

        document.querySelectorAll('[data-nav="prev"]').forEach((el) => {
            el.disabled = true;
        });

        if (TOTAL <= 1) {
            document.querySelectorAll('[data-nav="next"]').forEach((el) => {
                el.disabled = true;
            });
        }
    })();
</script>
