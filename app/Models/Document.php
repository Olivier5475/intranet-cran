<?php

namespace App\Models;

use App\Traits\HasHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;
use Purifier;
use Parsedown;

/**
 * @method static where(string $param1, string $symbol, string $param2)
 */
class Document extends Model {
    use HasFactory, Searchable, HasHistory;

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

    public $historyRelations = ['departements', "attachments"];

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

    public function archivePhysicalFile($version)
    {
        // Si le chemin change (donc nouvel upload)
        if ($this->isDirty('storage_path')) {
            $oldPath = $this->getOriginal('storage_path');

            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                // On crée un dossier 'history'
                $archivePath = 'history/file_' . $this->id . '_' . time() . '.' . $extension;

                // COPY (pas move) car le Service va supprimer l'original juste après
                Storage::disk('public')->copy($oldPath, $archivePath);

                // On met à jour le payload de la version
                $currentPayload = $version->payload;
                $currentPayload['archived_path'] = $archivePath;
                $version->update(['payload' => $currentPayload]);
            }
        }
    }

    public function autoClear() {
        $limit = 50;
        $historyCount = $this->versions()->count();

        if ($historyCount > $limit) {
            // On récupère les plus vieilles
            $versionsToDelete = $this->versions()
                ->reorder()
                ->orderBy('created_at', 'asc')
                ->take($historyCount - $limit)
                ->get();

            Log::alert("Version To Delete : ");
            Log::alert($versionsToDelete);
            foreach ($versionsToDelete as $v) {
                $this->purgePhysicalVersion($v);
                $v->delete();
            }
        }
    }
}
