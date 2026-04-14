<?php

namespace App\Http\Requests;

use App\Services\Interfaces\FoldersServiceInterface;
use Illuminate\Foundation\Http\FormRequest;

class SaveFolderRequest extends FormRequest
{
    public function authorize(FoldersServiceInterface $foldersService): bool
    {
        $folderId = $this->route('id'); // Présent en cas d'update
        $parentId = $this->input('parent_id'); // Nouveau parent potentiel

        // 1. Si modification : check accès au dossier actuel
        if ($folderId && !$foldersService->hasEditAccess((int)$folderId)) {
            return false;
        }

        // 2. Si création ou déplacement (changement de parent)
        if ($parentId) {
            if ($folderId) {
                // Check si le parent a réellement changé pour éviter une requête inutile
                $currentParentId = $foldersService->getParentId((int)$folderId);
                if ($currentParentId !== (int)$parentId && !$foldersService->hasEditAccess((int)$parentId)) {
                    return false;
                }
            } elseif (!$foldersService->hasEditAccess((int)$parentId)) {
                // Check accès création dans le parent
                return false;
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'max:16'],
            'parent_id' => ['integer', 'nullable'],
            'departements' => ['sometimes', 'array'],
        ];
    }
}
