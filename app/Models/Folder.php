<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        // Récupère uniquement les sous-dossiers non archivés de manière récursive
        return $this->children()
            ->where('is_archived', false)
            ->with('allChildren');
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

    public function groupes() : BelongsToMany {
        return $this->belongsToMany(Groupe::class);
    }

    public function favoris(): MorphMany
    {
        // Laravel va automatiquement chercher 'ressource_type' et 'ressource_id'
        return $this->morphMany(UserFavori::class, 'ressource');
    }
}
