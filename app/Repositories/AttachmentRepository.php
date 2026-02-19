<?php

namespace App\Repositories;

use App\Exception\AttachmentNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttachmentRepository implements Interfaces\AttachmentRepositoryInterface {

    public function create(array $data): Attachment {
        try {
            $attachment = new Attachment();
            $attachment->fill($data);

            if (isset($data['id'])) {
                $attachment->id = $data['id'];
            }
            $attachment->save();
            return $attachment;
        } catch (Throwable $e) {
            Log::error('Erreur SQL : Création attachement impossible', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException("Impossible de créer l'attachement en base de données.", 0, $e);
        }
    }

    public function read(int $id): Attachment {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            throw new AttachmentNotFoundException("L'attachement avec l'ID $id est introuvable.");
        }

        return $attachment;
    }

    public function update(int $id, array $data): Attachment {
        $attachment = $this->read($id); // Réutilise read() pour gérer l'exception 404

        try {
            $attachment->update($data);
            return $attachment->fresh(); // On retourne une instance fraîchement synchronisée
        } catch (Throwable $e) {
            Log::error("Erreur SQL : Mise à jour attachement échouée", [
                'id' => $id,
                'message' => $e->getMessage(),
                'payload' => $data,
            ]);

            throw new PersistenceException("Erreur lors de la mise à jour de l'attachement $id.", 0, $e);
        }
    }

    public function delete(int $id): bool {
        $attachment = $this->read($id);

        try {
            return (bool) $attachment->delete();
        } catch (Throwable $e) {
            Log::error("Erreur SQL : Suppression attachement échouée", [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);

            throw new PersistenceException("Erreur lors de la suppression de l'attachement $id.", 0, $e);
        }
    }
}
