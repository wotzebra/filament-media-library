<x-filament::modal
    id="filament-media-library::formatter-attachment-modal"
    width="w-6xl"
    :close-by-clicking-away="false"
>
    @if ($attachment)
        <x-slot name="header">
            <x-filament::modal.heading>
                {{ __('filament-media-library::formatter.formatter modal heading :name', [
                    'name' => $attachment->name,
                ]) }}
            </x-filament::modal.heading>
        </x-slot>

        <div
            class="py-8 w-full flex-col gap-2"
            wire:loading.remove
            wire:key="filament-media-library::formatter-attachment-modal-{{ $attachment->id }}"
            x-data="{
                formats: @entangle('formats'),
                previousFormats: @js($previousFormats),
                currentFormat: @entangle('currentFormat'),
                init () {
                    this.loadFormatter()
                },
                loadFormatter () {
                    if (! this.currentFormat) {
                        return
                    }

                    if (typeof window.cropper === 'object') {
                        window.cropper.destroy()
                    }

                    let previousData = this.previousFormats[this.currentFormat.key] || {}

                    window.cropper = new Cropper(document.getElementById('filament-media-library::formatter'), {
                        viewMode: 1,
                        dragMode: 'move',
                        aspectRatio: this.currentFormat.aspectRatio,
                        ready() {
                            window.cropper.setData(previousData || {})
                        },
                        checkCrossOrigin: false,
                    })
                },
                submit () {
                    this.previousFormats[this.currentFormat.key] = window.cropper.getData()
                    window.currentFormat = this.currentFormat

                    $wire.saveCrop({
                        format: this.currentFormat,
                        data: window.cropper.getData(),
                        crop: window.cropper
                            .getCroppedCanvas()
                            .toDataURL('image/png'),
                    })
                },
                setFormat (key) {
                    this.currentFormat = this.formats[key] || null

                    if (this.currentFormat) {
                        window.cropper.setAspectRatio(this.currentFormat.aspectRatio)
                        window.cropper.setData(this.previousFormats[this.currentFormat.key] || {})
                    }
                }
            }"
            x-on:filament-media-library::load-formatter.window="loadFormatter()"
            x-on:filament-media-library::submit-formatter.window="submit()"
        >
            {{-- Actual formatter --}}
            <div class="w-full flex flex-col lg:flex-row gap-6">
                <div class="lg:w-4/6 order-last lg:order-first">
                    <div class="w-full h-[68vh]">
                        <img
                            src="{{ $attachment->url }}"
                            id="filament-media-library::formatter"
                            wire:key="filament-media-library::formatter-{{ $attachment->id }}"
                            style="max-width: 100%; max-height: 68vh"
                            crossorigin="anonymous"
                        >
                    </div>

                    <div class="flex gap-4 flex-wrap justify-center mt-2">
                        <div class="flex gap-1">
                            <x-filament::button
                                x-on:click.prevent="window.cropper.zoom(0.1)"
                                title="{{ __('filament-media-library::formatter.zoom in') }}"
                            >
                                <x-heroicon-o-magnifying-glass-plus class="h-5" />
                            </x-filament::button>

                            <x-filament::button
                                x-on:click.prevent="window.cropper.zoom(-0.1)"
                                title="{{ __('filament-media-library::formatter.zoom out') }}"
                            >
                                <x-heroicon-o-magnifying-glass-minus class="h-5" />
                            </x-filament::button>
                        </div>

                        <div class="flex gap-1">
                            <x-filament::button
                                x-on:click.prevent="window.cropper.rotate(45)"
                                title="{{ __('filament-media-library::formatter.rotate 45 degrees clockwise') }}"
                            >
                                <x-fas-rotate-right class="h-4" />
                            </x-filament::button>

                            <x-filament::button
                                x-on:click.prevent="window.cropper.rotate(-45)"
                                title="{{ __('filament-media-library::formatter.rotate 45 degrees counterclockwise') }}"
                            >
                                <x-fas-rotate-left class="h-4" />
                            </x-filament::button>
                        </div>

                        <div class="flex gap-1">
                            <x-filament::button x-on:click.prevent="window.cropper.scaleX(
                                window.cropper.imageData.scaleX === -1 ? 1 : -1
                            )" title="{{ __('filament-media-library::formatter.flip horizontally') }}">
                                <x-heroicon-o-arrows-right-left class="h-4" />
                            </x-filament::button>

                            <x-filament::button x-on:click.prevent="window.cropper.scaleY(
                                window.cropper.imageData.scaleY === -1 ? 1 : -1
                            )" title="{{ __('filament-media-library::formatter.flip vertically') }}">
                                <x-heroicon-o-arrows-up-down class="h-4" />
                            </x-filament::button>
                        </div>

                        <x-filament::button color="gray" x-on:click.prevent="window.cropper.reset()">
                            {{ __('filament-media-library::formatter.reset format') }}
                        </x-filament::button>
                    </div>
                </div>

                {{-- Formats --}}
                <div class="lg:w-2/6 gallery-container lg:h-[70vh] flex flex-col">
                    <h3 class="text-lg font-bold mb-2 truncate">{{ $attachment->filename }}</h3>

                    <p class="mb-1">
                        <span x-text="currentFormat.name" class="font-bold"></span> -
                        <span x-text="currentFormat.width || '...'"></span><span x-show="currentFormat.height">px</span> x
                        <span x-text="currentFormat.height || '...'"></span><span x-show="currentFormat.height">px</span>
                    </p>

                    <p x-text="currentFormat.description"></p>

                    <hr class="my-6 dark:border-gray-600">

                    <div class="max-lg:flex max-md:h-40 max-lg:h-52 gallery overflow-x-auto lg:overflow-y-auto">
                        <template x-for="(format, key) in formats" :key="key">
                            <div
                                class="shrink-0 max-lg:aspect-3/4 flex flex-col gap-2 justify-between rounded-lg bg-gray-200 dark:bg-gray-800 p-2"
                                :class="{'ring-2 ring-primary-500 ring-inset': currentFormat.key === format.key}"
                                x-on:click="setFormat(key)"
                            >
                                <div class="w-full text-center">
                                    <p x-html="format.name"></p>
                                    <p>
                                        <span x-text="format.width || '...'"></span> x
                                        <span x-text="format.height || '...'"></span>
                                    </p>
                                </div>

                                <div class="flex aspect-square items-center justify-center">
                                    <div
                                        x-data="{
                                            aspectRatio: !format.width ? 0.67 : !format.height ? 1.5 : format.aspectRatio
                                        }"
                                        class="bg-gray-400 dark:bg-gray-600"
                                        :class="{
                                            'w-full': aspectRatio > 1,
                                            'h-full': aspectRatio <= 1,
                                            'relative format-preview--variable-width': !format.width,
                                            'relative format-preview--variable-height': !format.height,
                                        }"
                                        x-bind:style="'aspect-ratio: ' + aspectRatio"
                                    ></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <x-filament::button x-on:click.prevent="window.dispatchEvent(new Event('filament-media-library::submit-formatter'))">
                    {{ __('filament-media-library::formatter.save format') }}
                </x-filament::button>

                <x-filament::button color="gray" x-on:click.prevent="close() && $wire.set('attachment', null)">
                    {{ __('filament-media-library::formatter.close modal') }}
                </x-filament::button>
            </x-slot>
        </div>

        <div class="w-full justify-center py-8" wire:loading.flex>
            <x-filament::loading-indicator class="w-10 h-10" />
        </div>
    @else
        <div class="w-full flex justify-center py-8">
            <x-filament::loading-indicator class="w-10 h-10" />
        </div>
    @endif
</x-filament::modal>
