@props([
    'attachment',
    'containerClass' => null,
    'formats' => [],
    'isDisabled' => false,
    'deleteAction' => null,
    'editAction' => null,
    'formatAction' => null,
    'extendedTooltip' => false,
    'showTitle' => true,
    'showTooltip' => true
])

<div @class(['flex flex-col h-full', $containerClass])>
    <div class="grow flex justify-between gap-2">
        {{-- Title --}}
        @if ($showTitle)
            <p class="font-bold text-xs mb-2 line-clamp-1 border-primary-600" title="{{ $attachment->filename }}">
                {{ $attachment->filename }}
            </p>
        @endif

        {{-- Format tooltip --}}
        @if ($showTooltip && ($attachment->type === 'image' || $extendedTooltip))
            <div>
                <template x-ref="attachment-tooltip-{{ $attachment->id }}">
                    @if ($attachment->type === 'image' && ! $extendedTooltip)
                        <div>
                            <ul class="text-sm">
                                <li>{{ __('filament-media-library::attachment.original format') }}: {{ $attachment->width }}px x {{ $attachment->height }}px</li>

                                @foreach ($formats as $format)
                                    <li>{{ $format->name }}: {{ $format->width }}px x {{ $format->height }}px</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div>
                            <dl>
                                <dt class="text-sm font-bold">{{ __('filament-media-library::attachment.filename') }}</dt>
                                <dd class="mb-2 text-sm">{{ $attachment->filename }}</dd>

                                <dt class="text-sm font-bold">{{ __('filament-media-library::attachment.type') }}</dt>
                                <dd class="mb-2 text-sm">{{ $attachment->type }}</dd>

                                <dt class="text-sm font-bold">{{ __('filament-media-library::attachment.tags') }}</dt>
                                <dd class="mb-2 text-sm">{{
                                    $attachment->tags->count()
                                        ? $attachment->tags->implode('title', ', ')
                                        : __('filament-media-library::attachment.no tags for this attachment')
                                }}</dd>

                                <dt class="text-sm font-bold">{{ __('filament-media-library::attachment.size') }}</dt>
                                <dd class="mb-2 text-sm">{{ $attachment->formatted_in_mb_size }} MB</dd>

                                <dt class="text-sm font-bold">{{ __('filament-media-library::attachment.created at') }}</dt>
                                <dd class="mb-2 text-sm">{{ $attachment->created_at->format('d-m-Y') }}</dd>

                                <dt class="text-sm font-bold">{{ __('filament-media-library::attachment.original format') }}</dt>
                                <dd class="mb-2 text-sm">{{ $attachment->width }}px x {{ $attachment->height }}px</dd>
                            </dl>
                        </div>
                    @endif
                </template>

                <div
                    x-tooltip="{
                        content: () => $refs['attachment-tooltip-{{ $attachment->id }}'].innerHTML,
                        allowHTML: true,
                        appendTo: $root
                    }"
                    class="inline-block"
                >
                    <x-heroicon-o-information-circle class="h-[1em] text-gray-300 attachment-tooltip"/>
                </div>
            </div>
        @endif
    </div>

    {{-- Media --}}
    <div
        @if ($attachment->type === 'image')
            style="background-image: url('{{ $attachment->getFormatOrOriginal('thumbnail') }}')"
        @endif

        {{ $attributes->except(['slot', 'attachment'])->merge(['class' => '
            attachment-visual flex relative w-full aspect-square rounded-lg
            overflow-hidden bg-center bg-contain bg-no-repeat bg-gray-200 dark:bg-gray-800 media
        ']) }}
    >
        <abbr class="absolute inset-0 z-1" title="{{ $attachment->filename }}">
            <span class="invisible">{{ $attachment->filename }}</span>
        </abbr>
        @if($attachment->type !== 'image')
            <div class="w-full aspect-square flex items-center justify-center rounded-lg">
                @if($attachment->type === 'document')
                    <x-heroicon-o-document-text class="w-16 h-16 opacity-50"/>
                @elseif($attachment->type === 'video')
                    <x-heroicon-o-video-camera class="w-16 h-16 opacity-50"/>
                @else
                    <x-heroicon-o-question-mark-circle class="w-16 h-16 opacity-50"/>
                @endif
            </div>
        @endif

        {{-- Buttons --}}
        @unless($isDisabled)
            <div class="absolute right-1 bottom-1 left-1 z-10 flex justify-between gap-3 attachment__btn-block">
                @if ($deleteAction && $deleteAction->isVisible())
                    {{ ($deleteAction)(['attachmentId' => $attachment->id]) }}
                @endif

                <div class=" flex justify-end gap-1">
                    {{ $slot }}

                    @if ($formatAction && $formatAction->isVisible() && is_convertible_image($attachment->extension))
                        {{ ($formatAction)(['attachmentId' => $attachment->id]) }}
                    @endif

                    @if ($editAction && $editAction->isVisible())
                        {{ ($editAction)(['attachmentId' => $attachment->id]) }}
                    @endif
                </div>
            </div>
        @endunless
    </div>
</div>
