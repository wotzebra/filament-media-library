@php
    $attachment = $getState();
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @if ($attachment)
        <div class="h-80 aspect-square">
            <x-filament-media-library::attachment
                :$attachment
                :show-title="false"
                :show-tooltip="false"
                container-class="w-80"
            />
        </div>
    @endif
</x-dynamic-component>
