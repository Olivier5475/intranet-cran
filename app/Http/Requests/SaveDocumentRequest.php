<?php

namespace App\Http\Requests;

use App\Services\Interfaces\DocumentsServiceInterface;
use App\Services\Interfaces\FoldersServiceInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveDocumentRequest extends FormRequest
{
    public function __construct(
        private readonly DocumentsServiceInterface $documentsService,
        private readonly FoldersServiceInterface   $foldersService
    )
    {
        parent::__construct();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $id = $this->route('id');
        $parentId = $this->input('parent_id');

        // Droits en modification
        if ($id && !$this->documentsService->hasEditAccess((int)$id)) return false;

        // Droits en création
        if (!$id && $parentId && !$this->foldersService->hasEditAccess((int)$parentId)) return false;

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'color' => ['nullable', 'string', 'max:16'],
            'existing_attachments' => ['sometimes', 'array'],
            'existing_attachments.*.id' => ['required', 'integer', 'exists:attachments,id'],
            'existing_attachments.*.name' => ['required', 'string', 'max:255'],
            'new_attachments' => ['sometimes', 'array'],
            'new_attachments.*' => ['file', 'max:51200'],
            'departements' => ['sometimes', 'array'],
            'parent_id' => ['integer', 'nullable'],
        ];
    }

    /**
     * Transforme les données validées en format prêt pour le Service.
     */
    public function toServiceData(): array
    {
        $data = $this->validated();

        // On renomme parent_id en folder_id pour le service
        if (isset($data['parent_id'])) {
            $data['folder_id'] = $data['parent_id'];
            unset($data['parent_id']);
        }

        // On s'assure que les nouveaux fichiers sont bien récupérés
        $data['new_attachments'] = $this->file('new_attachments') ?? [];

        return $data;
    }
}
