@props(['label', 'value', 'icon' => null, 'hint' => null])

<div {{ $attributes->merge(['class' => 'card p-4 sm:p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</p>
            <p class="mt-1.5 text-2xl font-semibold tracking-tight text-slate-900 tabular sm:text-[1.75rem]">{{ $value }}</p>
        </div>

        @if($icon)
            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                <x-icon :name="$icon" class="size-[1.125rem]" />
            </span>
        @endif
    </div>

    @if($hint)
        <p class="mt-3 text-xs leading-relaxed text-slate-500">{{ $hint }}</p>
    @endif
</div>
