<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable; // ... (traits existants)

    // ... ($fillable, $hidden, $casts)
    protected $fillable = [
        "email",
        "nom",
        "prenom",
        "role",
        "verified_member_role",
        "newsletter_role",
        "department_id",
    ];

    /**
     * Récupère tous les dossiers racines de l'utilisateur
     */
    public function folders(): HasMany
    {
        // On ne retourne que les dossiers à la racine par défaut
        return $this->hasMany(Folder::class)->whereNull('parent_id');
    }

    /**
     * Récupère TOUS les dossiers (utile pour l'admin)
     */
    public function allFolders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * Récupère tous les fichiers (simples) de l'utilisateur
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Récupère tous les "Documents" de l'utilisateur
     */
    public function documents(): HasMany {
        return $this->hasMany(Document::class);
    }

    public function departement(): belongsTo {
        return $this->belongsTo(Departement::class);
    }
}
