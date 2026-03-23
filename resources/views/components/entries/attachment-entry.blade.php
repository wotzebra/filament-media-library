@php
    $attachment = $getState();
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @if ($attachment)
        <x-filament-media-library::attachment
            :$attachment
            :show-title="false"
            :show-tooltip="false"
            container-class="h-80"
        />
    @endif
</x-dynamic-component>
