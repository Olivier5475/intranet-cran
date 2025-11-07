<?php

namespace App\Repositories;

use App\Exception\AttachmentNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Attachment;
use Illuminate\Support\Facades\Log;

class AttachmentRepository implements interfaces\AttachmentRepositoryInterface {

    /**
     * @inheritDoc
     */
    public function create(array $data): Attachment {
        try {
            return Attachment::create($data);
        } catch (\Throwable $e) {
            Log::error('Attachment creation error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message:"Could not create attachment.", previous:$e);
        }
    }

    /**
     * @inheritDoc
     */
    public function read(int $id): Attachment {
        $attachment = Attachment::find($id);
        if (!$attachment) {
            throw new AttachmentNotFoundException("Attachment with ID $id not found.");
        }
        return $attachment;
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Attachment|bool {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            throw new AttachmentNotFoundException("Attachment with ID $id not found.");
        }

        try {
            $attachment->fill($data);
            $attachment->save();

            return $attachment;
        } catch (\Throwable $e) {
            Log::error('Attachment update failed for ID ' . $id, [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new PersistenceException(message : "Could not update attachment with ID $id.", previous:$e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            throw new AttachmentNotFoundException("Attachment with ID $id not found.");
        }

        try {
            $attachment->delete();
            return true;
        } catch (\Throwable $e) {
            Log::error('Attachment delete failed for ID ' . $id, [
                'error' => $e->getMessage(),
            ]);

            throw new PersistenceException(message : "Could not delete attachment with ID $id.", previous:$e);
        }
    }
}
