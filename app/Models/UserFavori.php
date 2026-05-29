<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserFavori extends Model
{
    protected $table = 'user_favoris';
    protected $fillable = ['user_id', 'ressource_id', 'ressource_type'];

    // Récupère la ressource associée (Document, Folder, ou File)
    public function ressource(): MorphTo
    {
        return $this->morphTo();
    }

    // Récupère l'utilisateur qui a mis le favori
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
