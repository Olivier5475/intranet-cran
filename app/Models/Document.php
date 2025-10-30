<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model {
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'folder_id',
    ];

    /**
     * Le dossier auquel ce document appartient
     */
    public function folder(): BelongsTo {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Les fichiers attachés à ce document
     */
    public function attachments(): HasMany {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Le propriétaire du document
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function departements(): BelongsToMany {
        return $this->belongsToMany(Departement::class);
    }

}
