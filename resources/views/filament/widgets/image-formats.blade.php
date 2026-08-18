@use(Filament\Support\Icons\Heroicon)

<x-filament-widgets::widget>
    <x-filament::section :icon="Heroicon::Photo">
        <x-slot name="heading">
            Image Formats ({{ $this->getFormats()->count() }})
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Description</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Dimensions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($this->getFormats() as $format)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-950 dark:text-white">
                                {{ $format['name'] }}
                            </td>
                            <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                                {{ $format['description'] }}
                            </td>
                            <td class="px-4 py-2 font-mono text-gray-600 dark:text-gray-300">
                                {{ $format['definition'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
