<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Purifier;
use Parsedown;

class Document extends Model {
    use HasFactory, Searchable;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'folder_id',
        'departements',
        'color',
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

    protected function getRenderedContentAttribute(): string
    {
        // 1. Convertir le Markdown en HTML
        $parsedown = new Parsedown();
        $html = $parsedown->text($this->content);

        // 2. Sanitariser le HTML résultant (pour la sécurité)
        // Ceci est crucial, même si le Markdown est "sûr", la conversion
        // peut parfois être exploitée.
        $cleanHtml = Purifier::clean($html);

        return $cleanHtml;
    }

    // 💡 Astuce : Ajoutez cette propriété à votre tableau 'appends'
    // pour qu'elle soit automatiquement incluse lorsque le modèle est sérialisé en JSON
    // et envoyé à Inertia.
    protected $appends = ['rendered_content'];
}
