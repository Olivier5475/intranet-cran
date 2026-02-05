<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'color',
        'parent_id',
    ];

    /**
     * Le dossier parent (s'il existe).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Les sous-dossiers
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function allChildren(): HasMany
    {
        // Utilise la même relation children mais est nommé différemment pour le chargement récursif
        return $this->children()->with('allChildren');
    }
    /**
     * Les fichiers simples dans ce dossier
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Les "Documents" (entités complexes) dans ce dossier
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Le propriétaire du dossier.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departements() : BelongsToMany {
        return $this->belongsToMany(Departement::class);
    }
}
