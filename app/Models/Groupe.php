<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Groupe extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'initials'
    ];

    /**
     * Relation many-to-many avec les fichiers
     */
    public function files(): BelongsToMany {
        return $this->belongsToMany(File::class);
    }

    /**
     * Relation many-to-many avec les documents
     */
    public function documents(): BelongsToMany {
        return $this->belongsToMany(Document::class);
    }

    public function users() : belongsToMany {
        return $this->belongsToMany(User::class);
    }

    // Pour récupérer le parent
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, 'parent_id');
    }

    // Pour récupérer les sous-groupes
    public function children(): Groupe|HasMany
    {
        return $this->hasMany(Groupe::class, 'parent_id');
    }
}
