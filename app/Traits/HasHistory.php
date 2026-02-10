<?php
namespace App\Traits;

use App\Models\Version;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasHistory
{
    public function versions()
    {
        return $this->morphMany(Version::class, 'versionable')->latest();
    }

    public static function bootHasHistory()
    {
        static::updating(function ($model) {
            // 1. Snapshot des données classiques
            $originalData = $model->getOriginal();
            unset($originalData['updated_at'], $originalData['created_at']);

            // 2. Snapshot des Relations (IMPORTANT pour les droits d'accès)
            // On vérifie si le modèle définit une propriété $historyRelations
            $relationsData = [];
            if (property_exists($model, 'historyRelations')) {
                foreach ($model->historyRelations as $relationName) {
                    // On charge la relation pour avoir les IDs actuels
                    $model->load($relationName);
                    // On ne stocke que les IDs
                    $relationsData[$relationName] = $model->$relationName->pluck('id')->toArray();
                }
            }
            // On fusionne tout ça
            $payload = array_merge($originalData, ['_relations' => $relationsData]);

            // 3. Création de la version en BDD
            $version = $model->versions()->create([
                'user_id' => Auth::id(),
                'payload' => $payload,
                'reason'  => request()->input('reason', null),
            ]);

            $version->save();
            // 4. Hook pour archivage physique (Fichiers)
            if (method_exists($model, 'archivePhysicalFile')) {
                $model->archivePhysicalFile($version);
            }

            if (method_exists($model, 'autoClear')) {
                $model->autoClear($version);
            }
        });
    }
}
