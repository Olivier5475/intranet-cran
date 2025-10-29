<?php

namespace App\repositories\interfaces;

interface FolderRepositoryInterface {
    public function getDescendantFolderIds(int $rootFolderId): array;

}
