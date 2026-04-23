<?php

namespace Wotz\MediaLibrary\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $attachment_id
 * @property int $version_number
 * @property string $name
 * @property string $extension
 * @property string $mime_type
 * @property string $md5
 * @property string $type
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property string $disk
 * @property array<int, array{format: string, data: mixed}>|null $format_data
 * @property int|null $replaced_by_user_id
 * @property Carbon $replaced_at
 */
class AttachmentVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'attachment_id',
        'version_number',
        'name',
        'extension',
        'mime_type',
        'md5',
        'type',
        'size',
        'width',
        'height',
        'disk',
        'format_data',
        'replaced_by_user_id',
        'replaced_at',
    ];

    protected function casts(): array
    {
        return [
            'format_data' => 'array',
            'replaced_at' => 'datetime',
        ];
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class);
    }

    public function getFilenameAttribute(): string
    {
        return "{$this->name}.{$this->extension}";
    }

    public function getDirectoryAttribute(): string
    {
        return "attachments/{$this->attachment_id}/versions/{$this->version_number}";
    }

    public function getFilePathAttribute(): string
    {
        return "{$this->directory}/{$this->filename}";
    }
}
