@php
    /** @var \Wotz\MediaLibrary\Support\FormatSummary $summary */
@endphp

<table class="w-full text-sm">
    <thead>
        <tr class="border-b border-gray-200 dark:border-white/10">
            <th class="py-2 pe-3 text-start font-medium text-gray-950 dark:text-white">
                {{ __('filament-media-library::upload.formats column name') }}
            </th>

            <th class="py-2 pe-3 text-start font-medium text-gray-950 dark:text-white">
                {{ __('filament-media-library::upload.formats column usage') }}
            </th>

            <th class="py-2 text-end font-medium text-gray-950 dark:text-white">
                {{ __('filament-media-library::upload.formats column dimensions') }}
            </th>
        </tr>
    </thead>

    <tbody>
        @foreach ($summary->formats() as $format)
            <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                <td class="py-2 pe-3 align-top font-medium text-gray-950 dark:text-white">
                    {{ $format['name'] }}
                </td>

                <td class="py-2 pe-3 align-top text-gray-600 dark:text-gray-400">
                    {{ $format['description'] }}
                </td>

                <td class="py-2 text-end align-top whitespace-nowrap text-gray-600 dark:text-gray-400">
                    {{ $format['dimensions'] }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
