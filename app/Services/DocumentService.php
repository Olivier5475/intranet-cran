<?php

namespace App\Services;

use App\DTO\AttachmentDTO;
use App\DTO\DocumentViewDTO;
use App\Interface\DocumentsServiceInterface;
use App\Models\Document;

class DocumentService implements DocumentsServiceInterface {
    public function getDocumentView($id) : DocumentViewDTO {
        $document = Document::find($id);
        $attachments = [];
        foreach ($document->attachments as $attachment) {
            $attachments[] = new AttachmentDTO(
                id           : $attachment->id,
                name         : $attachment->name,
                storage_path : $attachment->storage_path,
                mime_type    : $attachment->mimetype,
                size         : $attachment->size,
            );
        }
        return new DocumentViewDTO(
            id          : $document->id,
            title       : $document->title,
            content     : $document->content,
            attachments : $attachments,
        );
    }
}
