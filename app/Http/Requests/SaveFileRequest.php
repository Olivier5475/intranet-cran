<?php

namespace App\Http\Requests;

use App\Services\Interfaces\{FilesServiceInterface, FoldersServiceInterface};
use Illuminate\Foundation\Http\FormRequest;

class SaveFileRequest extends FormRequest
{
    public function authorize(
        FilesServiceInterface $filesService,
        FoldersServiceInterface $foldersService
    ): bool
    {
        $fileId = $this->route('id');
        $parentId = $this->input('parent_id');

        // Modification : a-t-on accès au fichier ?
        if ($fileId && !$filesService->hasEditAccess((int)$fileId)) return false;

        // Création ou Déplacement : a-t-on accès au dossier de destination ?
        if ($parentId) {
            // Si c'est une update, on vérifie si le parent change avant de check les droits
            if ($fileId) {
                $currentParentId = $foldersService->getParentId((int)$fileId);
                if ($currentParentId !== (int)$parentId && !$foldersService->hasEditAccess((int)$parentId)) {
                    return false;
                }
            } elseif (!$foldersService->hasEditAccess((int)$parentId)) {
                return false;
            }
        }

        return true;
    }

    public function rules(): array
    {
        $isUpdate = !empty($this->route('id'));

        return [
            'name' => [
                $isUpdate ? 'sometimes' : ($this->hasFile('files') ? 'nullable' : 'required'),
                'string',
                'max:255'
            ],
            'files' => [$isUpdate ? 'sometimes' : 'required', 'array'],
            'files.*' => ['file', 'max:102400'],
            'departements' => ['sometimes', 'array'],
            'parent_id' => ['required', 'integer'],
        ];
    }

    public function toServiceData(): array
    {
        $data = $this->validated();

        // On mappe parent_id vers folder_id pour le service
        $data['folder_id'] = $data['parent_id'];
        unset($data['parent_id']);

        // On extrait le premier fichier du tableau 'files' (ton service attend un seul fichier)
        if ($this->hasFile('files')) {
            $data['file'] = $this->file('files')[0];
        }
        unset($data['files']);

        return $data;
    }
}
