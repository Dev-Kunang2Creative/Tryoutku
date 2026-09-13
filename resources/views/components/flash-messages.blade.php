@php
    /** @var array<string, array{icon: string, classes: string, iconClasses: string}> $styles */
    $styles = [
        'success' => ['icon' => 'check-circle', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-900', 'iconClasses' => 'text-emerald-600'],
        'error' => ['icon' => 'x-circle', 'classes' => 'border-red-200 bg-red-50 text-red-900', 'iconClasses' => 'text-red-600'],
        'warning' => ['icon' => 'alert-triangle', 'classes' => 'border-amber-200 bg-amber-50 text-amber-900', 'iconClasses' => 'text-amber-600'],
        'info' => ['icon' => 'info', 'classes' => 'border-brand-200 bg-brand-50 text-brand-900', 'iconClasses' => 'text-brand-600'],
    ];
@endphp

@foreach($styles as $type => $style)
    @if(session($type))
        <div role="alert" class="mb-4 flex items-start gap-3 rounded-lg border px-4 py-3 {{ $style['classes'] }}">
            <x-icon :name="$style['icon']" class="mt-px size-[1.125rem] shrink-0 {{ $style['iconClasses'] }}" />

            <p class="min-w-0 flex-1 text-sm leading-relaxed">{{ session($type) }}</p>

            <button type="button" onclick="this.closest('[role=alert]').remove()"
                class="-my-1 -mr-1 shrink-0 rounded p-1 opacity-60 transition-opacity hover:opacity-100"
                aria-label="Tutup pesan">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    @endif
@endforeach
