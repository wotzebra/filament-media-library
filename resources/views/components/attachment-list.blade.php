@php
    use Illuminate\Support\Str;

    $attachment = $getRecord();
    $isImage = $attachment->type === 'image';

    // Alt text is the accessible name; fall back to the filename so the tile is never
    // announced as an unnamed image.
    $altText = filled($attachment->alt) ? $attachment->alt : $attachment->filename;
@endphp

<div
    @if ($isImage) x-data="{ failed: false }" @endif
    class="
        attachment-visual flex relative aspect-square rounded-lg
        overflow-hidden bg-gray-100 dark:bg-white/5 media mt-4
        h-32 w-32 items-center justify-center my-2
    "
>
    @if (! $isImage)
        <div class="flex h-full w-full flex-col items-center justify-center gap-y-1 text-gray-400 dark:text-gray-500">
            @if ($attachment->type === 'document')
                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedDocumentText" class="h-10 w-10" />
            @elseif ($attachment->type === 'video')
                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedVideoCamera" class="h-10 w-10" />
            @else
                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedQuestionMarkCircle" class="h-10 w-10" />
            @endif

            <span class="px-2 text-center text-[0.625rem] font-medium uppercase tracking-wide">
                {{ Str::upper($attachment->extension) }}
            </span>
        </div>
    @else
        {{--
            A missing file otherwise renders the browser's broken-image glyph. Swap in a
            styled placeholder instead.
        --}}
        <div
            x-show="failed"
            x-cloak
            class="flex h-full w-full flex-col items-center justify-center gap-y-1 text-gray-400 dark:text-gray-500"
        >
            <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedPhoto" class="h-10 w-10" />

            <span class="px-2 text-center text-[0.625rem] font-medium uppercase tracking-wide">
                {{ __('filament-media-library::admin.preview unavailable') }}
            </span>
        </div>

        <img
            src="{{ $attachment->getFormatOrOriginal('thumbnail') }}"
            alt="{{ $altText }}"
            loading="lazy"
            decoding="async"
            @if ($attachment->width && $attachment->height)
                width="{{ $attachment->width }}"
                height="{{ $attachment->height }}"
            @endif
            x-show="! failed"
            x-on:error="failed = true"
            class="h-full w-full object-cover"
        />
    @endif

    <abbr class="absolute inset-0 z-1" title="{{ $attachment->filename }}">
        <span class="invisible">{{ $attachment->filename }}</span>
    </abbr>
</div>
