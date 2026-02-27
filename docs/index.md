# Media Library for Filament

- [Installation](#installation)
- [Configuration](#configuration)
    * [Extensions](#extensions)
- [Formats](#formats)
    * [Register models](#register-models)
    * [Creating formats](#creating-formats)
        + [Format definition](#format-definition)
    * [Registering formats](#registering-formats)
        + [Preparing your model](#preparing-your-model)
- [Attachment model methods and attributes](#attachment-model-methods-and-attributes)
    * [Methods](#methods)
        + [getStorage](#getstorage)
        + [getFormatOrOriginal](#getformatororiginal)
        + [getFormat](#getformat)
    * [Attributes](#attributes)
        + [url](#url)
        + [filename](#filename)
        + [root_directory](#root-directory)
        + [directory](#directory)
        + [file_path](#file-path)
        + [absolute_directory_path](#absolute-directory-path)
        + [absolute_file_path](#absolute-file-path)
- [Usage in Blade](#usage-in-blade)
- [Usage in Filament](#usage-in-filament)
    * [AttachmentInput](#attachmentinput)
      * [Multiple attachments](#multiple-attachments)
      * [allowedFormats](#allowedformats)
    * [AttachmentColumn](#attachmentcolumn)
    * [AttachmentEntry](#attachmententry)
- [UploadedFile Mixin]('#uploaded-file-mixin)
    * [Save Attachment]('#save-attachment)
    * [Create from URL]('#create-from-url)
- [Versioning](#versioning)
    * [Configuration](#versioning-configuration)
    * [Replace a file](#replace-a-file)
    * [Revert to a previous version](#revert-to-a-previous-version)
    * [Events](#versioning-events)
    * [Custom resource pages](#versioning-in-custom-resource-pages)

## Installation

First, install this package via composer:

```bash
composer require wotz/filament-media-library
```

Then publish the assets with

```bash
php artisan vendor:publish --provider "Wotz\MediaLibrary\Providers\MediaLibraryServiceProvider"
```

and lastly, run the migrations:

```bash
php artisan migrate
```

Follow the [Formats](##formats) section to create and use formats.

## Configuration

The basic config file consists of the following contents:

```php
return [
    'conversion' => \Wotz\MediaLibrary\Conversions\LocalConversion::class,
    'enable-format-generate-action' => true,
    'force-format-extension' => [
        'extension' => 'webp',
        'mime-type' => 'image/webp',
    ],
    'format-queue' => 'default',
    'extensions' => [
        'image' => [
            'jpg',
            'jpeg',
            'svg',
            'png',
            'webp',
            'gif',
        ],
        'document' => [
            'txt',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'zip',
            'odf',
        ],
    ],
    'temporary_directory_path' => storage_path('filament-media-library/tmp'),
];
```

If you want to be able to upload video's just add this to the extensions in the config file:

```php
'video' => [
    'mp4',
    'm4v',
    'webm',
    'ogg',
],
```

### WebP by default

WebP is the default, if there is a new media type you want to use, this can be adjusted in the `force-format-extension` key.

### Extensions

This package divides files into 3 different types. An image, document, video and other. The config can define which file
extensions belong to which type.

This configuration also decides which files can be uploaded. An extension that is not defined is not going to be able to
be uploaded.

This configuration can be adjusted as desired.

### Showing the format generation action

The format generation action is a button that will generate all the formats for the given attachment.
This can be used on the Media Library as a bulk action. This action can be disabled by setting the `enable-format-generate-action` to false.

### S3 support

To use S3 as a storage, you can use the `Wotz\MediaLibrary\Conversions\S3Conversion` class.

```php
return [
    'conversion' => \Wotz\MediaLibrary\Conversions\S3Conversion::class,
    // ...
];
```

Then switch your filesystem to S3 in the `config/filesystems.php` file.

```php
return [
    'disks' => [
        'public' => [
            'visibility' => 'public',
            'driver' => 's3',
            'endpoint' => env('AWS_ENDPOINT', 'http://127.0.0.1:9000'),
            'use_path_style_endpoint' => true,
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'root' => 'public',
        ],
    ],
];
```

## Formats

### Register models

Any model that contains formats should be registered and implement the `Wotz\MediaLibrary\Interfaces\HasFormats`
interface.

Formats must be registered via the `Wotz\MediaLibrary\Facades\Formats` facade to be visible in the formatter.

```php
use App\Formats;
use Wotz\MediaLibrary\Facades\Formats;

public function boot()
{
    ...

    Formats::register([
        Formats\Thumbnail::class,
        Formats\FullWidth::class,
    ]);
}
```

### Creating formats

Create a new PHP class that extends the `Wotz\MediaLibrary\Formats\Format` class.

```php
<?php

namespace App\Formats;

use Spatie\Image\Manipulations;

class Hero extends Format
{
    protected string $description = 'This format is used for hero images';

    public function definition(): Manipulations
    {
        return $this->manipulations()
            ->blur(3)
            ...;
    }
}
```

#### Format definition

The manipulations object has predefined methods that will perform manipulations on an image.

These manipulations are based of Glide. A reference can be found in
the [Glide docs](https://glide.thephpleague.com/2.0/api/quick-reference/)

### Registering formats

Formats are tightly coupled with models. This way, specific formats can be fetched per model to prevent overhead.

#### Preparing your model

A model should implement the `Wotz\MediaLibrary\Interfaces\HasFormats` interface which contains the `getFormats`
method.

```php
public static function getFormats(Collection $formats): Collection
{
    return $formats->add(Hero::make('attachment_id'))
        ->add(...);
}
```

## Attachment model methods and attributes

### Methods

#### getStorage

Retrieve the Filesystem of the attachment

```php
use Wotz\MediaLibrary\Models\Attachment;

/** @var Illuminate\Contracts\Filesystem $filesystem */
$filesystem = Attachment::first()->getStorage();
```

#### getFormatOrOriginal

Retrieve the url for the file of the given format. Returns the original file if the given format is not found.

```php
use Wotz\MediaLibrary\Models\Attachment;

/** @var string $url */
$url = Attachment::first()->getFormatOrOriginal('hero');

// https://example.com/storage/{root_directory}/{attachment_id}/{snaked_format_name}__{filename}
// or
// https://example.com/storage/{root_directory}/{attachment_id}/{filename}
```

#### getFormat

Retrieve the url for the file of the given format. Returns null if the given format is not found.

```php
use Wotz\MediaLibrary\Models\Attachment;

/** @var string|null $url */
$url = Attachment::first()->getFormat('hero');

// https://example.com/storage/{root_directory}/{attachment_id}/{snaked_format_name}__{filename}
```

### Attributes

#### url

Retrieve the url to the original file

```php
use Wotz\MediaLibrary\Models\Attachment;

/** @var string $url */
$url = Attachment::first()->url;

// https://example.com/storage/{root_directory}/{attachment_id}/{filename}
```

#### filename

Retrieve the filename with extension.

```php
use Wotz\MediaLibrary\Models\Attachment;

/** @var string $url */
$url = Attachment::first()->filename;

// image.jpeg
```

#### root_directory

Retrieve the root directory in which the folder structure and files should be saved. This defaults to `attachments`

#### directory

Retrieve the relative path to the directory where all the formats and the original file is located.

`{root_directory}/{attachment_id}`

#### file_path

Retrieve the relative path to the original file.

`{root_directory}/{attachment_id}/{file_name}`

#### absolute_directory_path

Retrieve the full path to the attachment directory.

`{path_to_storage_folder}/{root_directory}/{attachment_id}`

#### absolute_file_path

Retrieve the full path to the original file.

`{path_to_storage_folder}/{root_directory}/{attachment_id}/{file_name}`

## Usage in Blade

This package provides a `<x-filament-media-library::picture />` component which will render the provided attachment with the given format. If no format is defined, the original attachment will be rendered.

```php
<x-filament-media-library::picture
    :image="$attachment"
    format="thumb"
    alt="alt text"
    class="img"
>
    <p>Filament Media Library package!</p>
</x-filament-media-library::picture>
```

Will return

```html
<picture class="img">
    <p>Filament Media Library package!</p>
    <source type="{MIME_TYPE}" srcset="{{IMAGE_SRC}}" />
    <img alt="alt text" src="{{IMAGE_SRC}}" />
</picture>
```

## Usage in Filament

### AttachmentInput

This field will give the option to upload or select an already existing image.
The ID will be stored in de column provided in the `make` method.

```php
use Wotz\MediaLibrary\Components\Fields\AttachmentInput;

AttachmentInput::make('profile_image_id')
    ->label('Profile Image')
```

This field inherits the `Filament\Forms\Components\Field` class which means that this field can do all the things other fields can do too.

#### Multiple attachments

```php
use Wotz\MediaLibrary\Components\Fields\AttachmentInput;

AttachmentInput::make('profile_image_id')
    ->multiple()
```

#### allowedFormats

The allowed formats in the cropper are based on the `getFormats` method in the model.
If you want to override this, you can use the `allowedFormats` method.

```php
use App\Formats\Hero;
use Wotz\MediaLibrary\Components\Fields\AttachmentInput;

AttachmentInput::make('profile_image_id')
    ->allowedFormats([
        Hero::make()
    ])
```

### AttachmentColumn

This column for a table will render the image with the thumbnail format or an icon if attachment is not an image.

```php
\Wotz\MediaLibrary\Tables\Columns\AttachmentColumn::make('image_id'),
```

### AttachmentEntry

This entry for an info list will render the image with the thumbnail format or an icon if attachment is not an image.

```php
\Wotz\MediaLibrary\Filament\Entries\AttachmentEntry::make('image'),
```


## UploadedFile Mixin

We add some methods to the UploadedFile class to make it easier to work with our attachments.

### Save Attachment

To convert an uploaded file to an attachment, you simply call the `save()` method on the UploadedFile.

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;

class UploadPhoto extends Component
{
    use WithFileUploads;

    #[Rule('image|max:1024')] // 1MB Max
    public $photo;

    public function save()
    {
        $this->photo->save();
    }
}
```

### Create from URL

To convert an url to an attachment, you simply call the `createFromUrl()` method on the UploadedFile.

```php
$uploadedFile = \Illuminate\Http\UploadedFile::createFromUrl('https://example.com/image.jpg');

$attachment = $uploadedFile->save();
```

## Versioning

The package supports file versioning out of the box. When a file is replaced, the previous version is archived and can be restored at any time.

### Versioning Configuration

The versioning behaviour can be configured in `config/filament-media-library.php`:

```php
'versioning' => [
    'keep_versions' => 5, // Number of previous versions to keep per attachment
],
```

Old versions beyond the `keep_versions` limit are automatically pruned (both the database record and the stored files).

### Replace a file

The `Attachment` model uses the `HasVersions` trait, which provides a `replaceFile` method:

```php
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Wotz\MediaLibrary\Models\Attachment;

/** @var TemporaryUploadedFile $file */
Attachment::find($id)->replaceFile($file);
```

Calling `replaceFile` will:
1. Archive the current file and its metadata as a new `AttachmentVersion` snapshot.
2. Move the existing files to a versioned subdirectory (`attachments/{id}/versions/{version_number}`).
3. Store the new file, increment the `version` counter, and regenerate all image formats.
4. Fire an `AttachmentReplaced` event.

In the Filament admin panel, the **Replace file** button is available on the attachment edit page.

### Revert to a previous version

```php
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Models\AttachmentVersion;

$attachment = Attachment::find($id);

/** @var AttachmentVersion $version */
$version = $attachment->versions->first();

$attachment->revertToVersion($version);
```

Calling `revertToVersion` will:
1. Archive the current state as a new version snapshot.
2. Restore the files from the selected version directory.
3. Restore the format data that was saved with that version.
4. Delete the reverted version record and prune old versions.
5. Fire an `AttachmentReverted` event.

In the Filament admin panel, the **Version history** dropdown on the attachment edit page lists all available previous versions. Each entry shows the filename and the date/time it was replaced.

### Listing versions

```php
$attachment->versions; // Collection of AttachmentVersion, ordered by version_number descending
```

Each `AttachmentVersion` has the following attributes:

| Attribute | Description |
|---|---|
| `version_number` | The version counter at the time of archiving |
| `name` | File name (without extension) |
| `extension` | File extension |
| `mime_type` | MIME type |
| `size` | File size in bytes |
| `width` / `height` | Image dimensions (nullable) |
| `disk` | Storage disk |
| `format_data` | Serialised format crop data |
| `replaced_by_user_id` | ID of the user who replaced the file (nullable) |
| `replaced_at` | Timestamp when the file was replaced |

### Versioning Events

Two events are dispatched during versioning:

#### `AttachmentReplaced`

Fired after a file has been successfully replaced.

```php
use Wotz\MediaLibrary\Events\AttachmentReplaced;

Event::listen(AttachmentReplaced::class, function (AttachmentReplaced $event) {
    $event->attachment;       // The updated Attachment model
    $event->previousVersion;  // The AttachmentVersion snapshot that was created
});
```

#### `AttachmentReverted`

Fired after an attachment has been reverted to a previous version.

```php
use Wotz\MediaLibrary\Events\AttachmentReverted;

Event::listen(AttachmentReverted::class, function (AttachmentReverted $event) {
    $event->attachment; // The updated Attachment model
    $event->version;    // The AttachmentVersion that was restored
});
```

### Versioning in custom resource pages

If you create a custom Filament resource page for attachments, you can add the versioning actions by using the `HasVersionHistory` concern:

```php
use Filament\Resources\Pages\EditRecord;
use Wotz\MediaLibrary\Resources\Concerns\HasVersionHistory;

class EditMyAttachment extends EditRecord
{
    use HasVersionHistory;

    protected function getHeaderActions(): array
    {
        return [
            $this->getReplaceFileAction(),
            $this->getVersionHistoryAction(),
        ];
    }
}
```

You can also use the actions independently:

```php
use Wotz\MediaLibrary\Filament\Actions\ReplaceAttachmentAction;
use Wotz\MediaLibrary\Filament\Actions\VersionHistoryAction;

// A standalone action for the page header
ReplaceAttachmentAction::make('replaceFile');

// An action group listing all previous versions
VersionHistoryAction::make($this->getRecord());
```

## Generate new media format

To generate a new media format, you can use the `media:generate-format` command.

```bash
php artisan media:generate-format {--attachment-id=} {--format=} {--force}
```

If you do not pass an `attachment-id`, the formats will be generated for all attachments.
If you do not pass a `format`, all formats will be generated for the attachments.
With `force` you can force the generation of the formats, even if they already exist. 

> [!WARNING]
> Using force will also overwrite cropped images!
