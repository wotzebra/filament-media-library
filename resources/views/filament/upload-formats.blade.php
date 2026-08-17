@php
    /** @var \Wotz\MediaLibrary\Support\FormatSummary|null $summary */
    $upscaled = collect($results)->where('isUpscaled', true);

    $state = match (true) {
        filled($fileErrors) => 'error',
        $sourceWidth === null => 'empty',
        $upscaled->isNotEmpty() => 'upscaled',
        default => 'covered',
    };

    // Nothing was generated from the file, so the formats are listed without a verdict.
    $isUnresolved = in_array($state, ['empty', 'error'], true);
@endphp

@if ($summary)
    <div
        @class([
            'flex flex-col gap-3 rounded-lg border p-3',
            'border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5' => $state === 'empty',
            'border-success-200 bg-success-50 dark:border-success-500/30 dark:bg-success-500/10' => $state === 'covered',
            'border-warning-200 bg-warning-50 dark:border-warning-500/30 dark:bg-warning-500/10' => $state === 'upscaled',
            'border-danger-200 bg-danger-50 dark:border-danger-500/30 dark:bg-danger-500/10' => $state === 'error',
        ])
    >
        <div class="flex items-baseline justify-between gap-2">
            <span class="text-sm font-medium text-gray-950 dark:text-white">
                {{ __('filament-media-library::upload.formats title') }}
            </span>

            <span class="text-xs text-gray-500 dark:text-gray-400">
                @switch ($state)
                    @case ('covered')
                        {{ trans_choice('filament-media-library::upload.formats all covered', $summary->count(), ['count' => $summary->count()]) }}
                        @break
                    @case ('upscaled')
                        {{ __('filament-media-library::upload.formats will be upscaled', ['count' => $upscaled->count()]) }}
                        @break
                    @default
                        {{ $summary->countLabel() }}
                @endswitch
            </span>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach ($results as $result)
                <x-filament::badge
                    :color="$isUnresolved ? 'gray' : ($result['isUpscaled'] ? 'warning' : 'success')"
                    :icon="$isUnresolved ? null : ($result['isUpscaled'] ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check')"
                >
                    {{ $result['name'] }}

                    <span class="font-normal opacity-75">
                        {{ $result['isUpscaled'] ? __('filament-media-library::upload.formats upscaled') : $result['dimensions'] }}
                    </span>
                </x-filament::badge>
            @endforeach
        </div>

        @if ($state === 'error')
            {{-- FileRule's own wording, so the block says exactly what the step will say. --}}
            @foreach ($fileErrors as $fileError)
                <p class="text-danger-700 dark:text-danger-400 text-xs">{{ $fileError }}</p>
            @endforeach
        @else
            <p
                @class([
                    'text-xs',
                    'text-gray-500 dark:text-gray-400' => $state === 'empty',
                    'text-success-700 dark:text-success-400' => $state === 'covered',
                    'text-warning-700 dark:text-warning-400' => $state === 'upscaled',
                ])
            >
                @switch ($state)
                    @case ('covered')
                        {{ trans_choice('filament-media-library::upload.formats covered note', $summary->count(), ['count' => $summary->count()]) }}
                        @break
                    @case ('upscaled')
                        {{ trans_choice('filament-media-library::upload.formats upscaled note', $upscaled->count(), ['count' => $upscaled->count(), 'width' => $sourceWidth]) }}
                        @break
                    @default
                        {{ __('filament-media-library::upload.formats empty note', ['dimensions' => $summary->requiredDimensions()]) }}
                @endswitch
            </p>
        @endif
    </div>
@endif
