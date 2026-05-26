<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
