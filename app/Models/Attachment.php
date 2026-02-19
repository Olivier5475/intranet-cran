<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'name',
        'storage_path',
        'mimetype',
        'size',
    ];
    protected $touches = ['document'];

    protected static function booted()
    {
        $triggerHistory = function ($attachment) {
            if ($attachment->document) {
                $attachment->document->touch();
            }
        };

        static::creating($triggerHistory);
        static::deleting($triggerHistory);
    }

    /**
     * Le document auquel ce fichier est attaché.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
