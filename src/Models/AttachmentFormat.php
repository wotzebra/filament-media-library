<?php

namespace Wotz\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $attachment_id
 * @property string $format
 * @property \Carbon\Carbon $updated_at
 */
class AttachmentFormat extends Model
{
    protected $fillable = [
        'attachment_id',
        'format',
        'data',
    ];

    public $casts = [
        'data' => 'array',
    ];

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class);
    }
}
