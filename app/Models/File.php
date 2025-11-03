<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class File extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'name',
        'user_id',
        'folder_id',
        'storage_path',
        'mimetype',
        'size',
        'departements',
    ];

    /**
     * Le dossier auquel ce fichier appartient
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Le propriétaire du fichier
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departements(): BelongsToMany {
        return $this->belongsToMany(Departement::class);
    }

}
