<?php

namespace Wotz\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Wotz\MediaLibrary\Database\Factories\AttachmentTagFactory;

/**
 * @property string $title
 */
class AttachmentTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'is_hidden',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    protected static function newFactory()
    {
        return AttachmentTagFactory::new();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AttachmentTag::class, 'parent_attachment_tag_id');
    }
}
