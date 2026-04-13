<?php

namespace App\Repositories;

use App\Exception\AlreadyExistsException;
use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Document;
use App\Models\File;
use App\Models\Version;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class DocumentRepository implements Interfaces\DocumentRepositoryInterface {

    public function create(array $data): Document {
        // Vérification de l'unicité du nom dans le dossier
        if($this->checkName($data['folder_id'] ?? null, $data['name'])) {
            throw new AlreadyExistsException("Un document ou un fichier porte déjà ce nom dans ce dossier.");
        }

        try {
            $document = new Document();
            $document->name = e($data['name']); // Protection basique XSS sur le titre
            $document->content = $data['content']; // Le nettoyage est fait dans le Service via Purifier
            $document->color = $data['color'] ?? '#ffffff';
            $document->folder_id = $data['folder_id'] ?? null;
            $document->user_id = $data['user_id'];
            $document->save();

            if (!empty($data['departements'])) {
                $document->departements()->attach($data['departements']);
            }

            return $document->load('departements');
        } catch (Throwable $e) {
            Log::error('Échec SQL : Création du document', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
            throw new PersistenceException("Impossible de créer le document.", 0, $e);
        }
    }

    public function read(int $id) : Document {
        $document = Document::with("departements")->find($id);

        if (!$document) {
            throw new DocumentNotFoundException("Le document avec l'ID $id est introuvable.");
        }
        return $document;
    }

    public function update(int $id, array $data): Document {
        $document = $this->read($id);

        if(isset($data["name"]) && $this->checkName($document->folder_id, $data['name'], $id)) {
            throw new AlreadyExistsException("Le nouveau titre est déjà utilisé par un autre élément.");
        }

        try {
            if (isset($data['name'])) {
                $document->name = e($data['name']);
            }
            if (isset($data["content"])) {
                $document->content = $data['content'];
            }
            if (isset($data["color"])) {
                $document->color = $data['color'];
            }
            if (isset($data["folder_id"])) {
                $document->folder_id = $data['folder_id'];
            }
            $document->save();

            if (isset($data['departements'])) {
                $document->departements()->sync($data['departements']);
            }

            return $document->fresh('departements');
        } catch (Throwable $e) {
            Log::error("Échec SQL : Mise à jour du document $id", [
                'message' => $e->getMessage(),
                'payload' => $data,
            ]);
            throw new PersistenceException("Erreur lors de la mise à jour du document.", 0, $e);
        }
    }

    /**
     * @throws DocumentNotFoundException
     * @throws PersistenceException
     */
    private function setIsArchived(int $document_id, $is_archived): bool {
        $document = $this->read($document_id);

        try {
            $document->is_archived = $is_archived;
            return $document->save();
        } catch (Throwable $e) {
            Log::error("Échec SQL : Suppression du document $document_id", [
                'message' => $e->getMessage(),
            ]);
            throw new PersistenceException("Erreur technique lors de la suppression.", 0, $e);
        }
    }
    public function delete(int $id) : bool
    {
        return $this->setIsArchived($id, true);
    }

    public function restore(int $document_id): bool
    {
        return $this->setIsArchived($document_id, false);
    }

    public function readRacineDoc(): ?Document {
        try {
            return Document::with('departements')
                ->whereNull("folder_id")
                ->orderBy("created_at")
                ->first();
        } catch (Throwable $e) {
            Log::error('Erreur SQL : lecture du document racine', ["message" => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Vérifie si le nom existe déjà (soit en Fichier, soit en Document) dans le même dossier.
     */
    private function checkName(?int $folderId, string $name, ?int $excludeId = null): bool {
        // Un document à la racine a un folder_id null
        $fileQuery = File::where('folder_id', $folderId)->where('name', $name);

        $docQuery = Document::where('folder_id', $folderId)
            ->where('name', $name)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId));

        return $fileQuery->exists() || $docQuery->exists();
    }

    public function findVersionWithParent(int $versionId): Version {
        return Version::with('versionable')->findOrFail($versionId);
    }

    public function findVersionsFromParent(int $parent_id): Collection
    {
        return Version::where('versionable_id', $parent_id)
            ->where("versionable_type", Document::class)
            ->orderByDesc('created_at')
            ->get();
    }
}
