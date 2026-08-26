<?php

namespace Wotz\MediaLibrary\Support;

use Illuminate\Database\Eloquent\Builder;
use Wotz\MediaLibrary\Exceptions\InvalidConfiguration;
use Wotz\MediaLibrary\Models\Attachment;

/**
 * Resolves the configured attachment model.
 *
 * An application can point `filament-media-library.model` at its own subclass
 * to change how attachments behave — most usefully the root directory, so a
 * project migrating from another media library can keep its existing storage
 * layout instead of moving every object. Every place in this package that
 * builds a query or creates a record goes through here, so a subclass applies
 * to uploads and conversions too, not only to what the application reads back.
 */
class Config
{
    /**
     * @return class-string<Attachment>
     */
    public static function attachmentModel(): string
    {
        $model = config('filament-media-library.model') ?? Attachment::class;

        if (! is_a($model, Attachment::class, true)) {
            throw InvalidConfiguration::modelIsNotValid($model);
        }

        return $model;
    }

    public static function attachmentModelInstance(): Attachment
    {
        $model = static::attachmentModel();

        return new $model;
    }

    public static function attachmentQuery(): Builder
    {
        return static::attachmentModel()::query();
    }
}
