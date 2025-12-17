<?php

namespace App\Repositories;
;

use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class DocumentRepository implements Interfaces\DocumentRepositoryInterface {

    /**
     * Créer un document.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return Document Retourne le Document créé
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function create(array $data): Document {
        try {
          $document = new Document();
          $document->title = $data['title'];
          $document->content = $data['content'];
          $document->color = $data['color'];
          $document->folder_id = $data['folder_id'];
          $document->user_id = $data['user_id'];
          $document->save();
          return $document;
        } catch (\Throwable $e) {
            Log::error('Document creation error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message:"Could not create document.", previous:$e);
        }
    }

    /**
     * Lit un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @return Document Retourne le Document avec l'ID $id
     * @throws DocumentNotFoundException Si le document n'est pas trouvé.
     */
    public function read(int $id) : Document {
        $document = Document::find($id);

        if (!$document) {
            throw new DocumentNotFoundException("Document with ID $id not found.");
        }
        return $document;
    }

    /**
     * Met à jour un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @param array $data Les champs et leurs nouvelles valeurs (doivent être "fillable").
     * @return Document|bool Retourne le Document mis à jour, ou false si la mise à jour échoue.
     * @throws DocumentNotFoundException Si le document n'est pas trouvé.
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function update(int $id, array $data): Document|bool {
        $document = Document::find($id);

        if (!$document) {
            throw new DocumentNotFoundException("Document with ID $id not found.");
        }

        try {
            $document->fill($data);
            $result = $document->save();

            return $result ? $document : false;
        } catch (\Throwable $e) {
            Log::error('Document update failed for ID ' . $id, [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message : "Could not update document with ID $id.", previous:$e);
        }
    }

    /**
     * Supprime un document existant.
     * @param int $id L'ID du document à mettre à jour.
     * @return bool retourne true si la suppression a réussi
     * @throws DocumentNotFoundException Si le document n'est pas trouvé.
     * @throws PersistenceException En cas d'erreur de base de données.
     */
    public function delete(int $id) : bool {
        $document = Document::find($id);

        if (!$document) {
            throw new DocumentNotFoundException("Document with ID $id not found.");
        }

        try {
            $document->delete();
            return true;
        } catch (\Throwable $e) {
            Log::error('Document delete failed for ID ' . $id, [
                'error' => $e->getMessage(),
            ]);

            throw new PersistenceException(message : "Could not delete document with ID $id.", previous:$e);
        }
    }
}
