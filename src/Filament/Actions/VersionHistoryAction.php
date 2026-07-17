<?php

namespace Wotz\MediaLibrary\Filament\Actions;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\Livewire;
use Wotz\MediaLibrary\Exceptions\VersionFilesMissing;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Models\AttachmentVersion;

class VersionHistoryAction
{
    public static function make(Attachment $record): ActionGroup
    {
        $versions = $record->versions;

        if ($versions->isEmpty()) {
            return static::group([
                Action::make('no_versions')
                    ->label(__('filament-media-library::versioning.no_versions'))
                    ->disabled(),
            ]);
        }

        $actions = $versions->map(fn (AttachmentVersion $version) => Action::make("revert_v{$version->version_number}")
            ->label("v{$version->version_number} – {$version->name}.{$version->extension} ({$version->replaced_at->format('d/m/Y H:i')})")
            ->requiresConfirmation()
            ->modalHeading(__('filament-media-library::versioning.revert_confirm_heading'))
            ->action(function (Component $livewire) use ($record, $version): void {
                try {
                    $record->revertToVersion($version);
                } catch (VersionFilesMissing) {
                    Notification::make()
                        ->title(__('filament-media-library::versioning.version_files_missing'))
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title(__('filament-media-library::versioning.version_reverted', ['version' => $version->version_number]))
                    ->success()
                    ->send();

                // Filament caches the header actions at boot, so this group still holds the
                // versions from before the revert. Only a new request rebuilds the list.
                $livewire->redirect(Livewire::originalUrl());
            })
        )->values()->all();

        return static::group($actions)->badge($versions->count());
    }

    /**
     * @param  array<int, Action>  $actions
     */
    protected static function group(array $actions): ActionGroup
    {
        return ActionGroup::make($actions)
            ->label(__('filament-media-library::versioning.version_history'))
            ->icon('heroicon-o-clock')
            ->button();
    }
}
