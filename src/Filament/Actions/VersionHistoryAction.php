<?php

namespace Wotz\MediaLibrary\Filament\Actions;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Livewire\Component;
use Wotz\MediaLibrary\Models\AttachmentVersion;

class VersionHistoryAction
{
    public static function make(mixed $record): ActionGroup
    {
        $versions = $record->versions;

        if ($versions->isEmpty()) {
            return ActionGroup::make([
                Action::make('no_versions')
                    ->label(__('filament-media-library::versioning.no_versions'))
                    ->disabled(),
            ])
                ->label(__('filament-media-library::versioning.version_history'))
                ->icon('heroicon-o-clock');
        }

        $actions = $versions->map(fn (AttachmentVersion $version) => Action::make("revert_v{$version->version_number}")
            ->label("v{$version->version_number} – {$version->name}.{$version->extension} ({$version->replaced_at->format('d/m/Y H:i')})")
            ->requiresConfirmation()
            ->modalHeading(__('filament-media-library::versioning.revert_confirm_heading'))
            ->action(function (Component $livewire) use ($record, $version): void {
                $record->revertToVersion($version);

                Notification::make()
                    ->title(__('filament-media-library::versioning.version_reverted', ['version' => $version->version_number]))
                    ->success()
                    ->send();

                if (method_exists($livewire, 'refreshFormData')) {
                    $livewire->refreshFormData([
                        'name', 'extension', 'mime_type', 'type', 'size', 'width', 'height', 'version',
                    ]);
                }
            })
        )->values()->all();

        return ActionGroup::make($actions)
            ->label(__('filament-media-library::versioning.version_history'))
            ->icon('heroicon-o-clock')
            ->badge($versions->count());
    }
}
