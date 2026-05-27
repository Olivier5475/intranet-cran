<?php

namespace App\Repositories;

use App\Exception\{AlreadyExistsException, DocumentNotFoundException, PersistenceException};
use App\Models\{Document, File, Version};
use App\Repositories\Interfaces\DocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class DocumentRepository implements DocumentRepositoryInterface
{
    // On crée une variable statique pour stocker le résultat en mémoire
    // Et éviter de faire 2 fois la requete
    private static ?Document $racineDocCache = null;

    // --- LECTURE ---

    /**
     * @inheritDoc
     */
    public function read(int $id): Document
    {
        $document = Document::with("groupes")
            ->with("attachments")
            ->find($id);

        if (!$document) {
            throw new DocumentNotFoundException("Le document avec l'ID $id est introuvable.");
        }
        return $document;
    }

    /**
     * @inheritDoc
     */
    public function readRacineDoc(): ?Document
    {
        // Si on l'a déjà récupéré durant cette requête, on le renvoie direct
        if (self::$racineDocCache !== null) {
            return self::$racineDocCache;
        }

        try {
            self::$racineDocCache = Document::with('groupes')
                ->whereNull("folder_id")
                ->orderBy("created_at")
                ->with('attachments')
                ->first();

            return self::$racineDocCache;
        } catch (Throwable $e) {
            Log::error('Erreur SQL : lecture du document racine', ["message" => $e->getMessage()]);
            return null;
        }
    }

    // --- ÉCRITURE (CRUD) ---

    /**
     * @inheritDoc
     */
    public function create(array $data): Document
    {
        if ($this->checkName($data['folder_id'] ?? null, $data['name'])) {
            throw new AlreadyExistsException("Un document ou un fichier porte déjà ce nom dans ce dossier.");
        }

        try {
            $document = new Document();
            $document->name = e($data['name']);
            $document->content = $data['content'];
            $document->color = $data['color'] ?? '#ffffff';
            $document->folder_id = $data['folder_id'] ?? null;
            $document->type = $data['type'] ?? "document";
            if ($document->type == "appelprojet") {
                $document->deadline = $data['deadline'] ?? null;
                if(is_null($document->deadline)) {
                    $document->type = "document";
                }
            }
            $document->user_id = $data['user_id'];
            $document->save();

            if (!empty($data['groupes'])) {
                $document->groupes()->attach($data['groupes']);
            }

            return $document->load('groupes');
        } catch (Throwable $e) {
            Log::error('Échec SQL : Création du document', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
            throw new PersistenceException("Impossible de créer le document.", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Document
    {
        $document = $this->read($id);

        if (isset($data["name"]) && $this->checkName($document->folder_id, $data['name'], $id)) {
            throw new AlreadyExistsException("Le nouveau titre est déjà utilisé par un autre élément.");
        }

        try {
            if (isset($data['name'])) $document->name = e($data['name']);
            if (isset($data["content"])) $document->content = $data['content'];
            if (isset($data["color"])) $document->color = $data['color'];
            if (isset($data["folder_id"])) $document->folder_id = $data['folder_id'];

            // --- GESTION DU TYPE ET DE LA DEADLINE ---
            if (isset($data['type'])) {
                $document->type = $data['type'];

                if ($document->type === "appelprojet") {
                    // On met à jour la deadline si elle est fournie, sinon on garde l'existante ou null
                    $document->deadline = $data['deadline'] ?? $document->deadline;

                    // Sécurité : Si après traitement la deadline est absente, on rétrograde en document classique
                    if (is_null($document->deadline)) {
                        $document->type = "document";
                    }
                } else {
                    // Si le type repasse en "document", on nettoie la deadline en BDD
                    $document->deadline = null;
                }
            }
            // Si le type n'est pas renvoyé mais qu'on modifie directement la deadline d'un appelprojet existant
            elseif (array_key_exists('deadline', $data) && $document->type === "appelprojet") {
                $document->deadline = $data['deadline'];
                if (is_null($document->deadline)) {
                    $document->type = "document";
                }
            }

            $document->save();

            if (isset($data['groupes'])) {
                $document->groupes()->sync($data['groupes']);
            }

            return $document->fresh('groupes');
        } catch (Throwable $e) {
            Log::error("Échec SQL : Mise à jour du document $id", [
                'message' => $e->getMessage(),
                'payload' => $data,
            ]);
            throw new PersistenceException("Erreur lors de la mise à jour du document.", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $document = $this->read($id);
        $document->delete();
        return true;
    }

    /**
     * @inheritDoc
     */
    public function archive(int $document_id): bool
    {
        return $this->setIsArchived($document_id, true);
    }

    /**
     * @inheritDoc
     */
    public function restore(int $document_id): bool
    {
        return $this->setIsArchived($document_id, false);
    }

    // --- RECHERCHE ---

    /**
     * @inheritDoc
     */
    public function performSearch(string $query, array $folderIds, bool $fromArchived = false, bool $searchInContent = false): Collection
    {
        $documents = Document::search($query)
            ->whereIn('folder_id', $folderIds)
            ->where('is_archived', (int) $fromArchived);

        if (!$searchInContent) {
            $documents->options(['attributesToSearchOn' => ['name']]);
        }
        return $documents->get();
    }

    // --- VERSIONS ---

    /**
     * @inheritDoc
     */
    public function findVersionsFromParent(int $parent_id): Collection
    {
        return Version::where('versionable_id', $parent_id)
            ->where("versionable_type", Document::class)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findVersionWithParent(int $versionId): Version
    {
        return Version::with('versionable')->findOrFail($versionId);
    }

    // --- PRIVÉ / HELPERS ---

    private function setIsArchived(int $document_id, bool $is_archived): bool
    {
        $document = $this->read($document_id);

        try {
            $document->is_archived = $is_archived;
            return $document->save();
        } catch (Throwable $e) {
            Log::error("Échec SQL : Changement état archivage document $document_id", [
                'message' => $e->getMessage(),
            ]);
            throw new PersistenceException("Erreur technique lors de la modification de l'état.", 0, $e);
        }
    }

    private function checkName(?int $folderId, string $name, ?int $excludeId = null): bool
    {
        $fileQuery = File::where('folder_id', $folderId)->where('name', $name);

        $docQuery = Document::where('folder_id', $folderId)
            ->where('name', $name)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId));

        return $fileQuery->exists() || $docQuery->exists();
    }
}
