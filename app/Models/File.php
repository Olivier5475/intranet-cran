<?php

namespace App\Models;

use App\Traits\HasHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class File extends Model
{
    use HasFactory, Searchable, HasHistory;

    protected $fillable = [
        'name',
        'user_id',
        'folder_id',
        'storage_path',
        'mimetype',
        'size',
        'departements',
    ];

    public $historyRelations = ['departements'];

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

    // Appelée lors du nettoyage des vieilles versions
    public function purgePhysicalVersion($version)
    {
        $payload = $version->payload;
        if (isset($payload['archived_path'])) {
            Storage::disk('public')->delete($payload['archived_path']);
        }
    }

    public function autoClear() {
        $limit = 3;
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
