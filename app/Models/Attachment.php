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

    /**
     * Le document auquel ce fichier est attaché.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
