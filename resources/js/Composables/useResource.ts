import { computed } from 'vue';
import download from '@/routes/download';
import editor from '@/routes/editor';
import navigate from '@/routes/navigate';
import { isGifFile, isImageFile } from '@/Composables/useDocumentsTypeRegex';
import { Child } from '@/types/child';
import { Folder } from '@/types/folder';
import { FileEntry } from '@/types/fileEntry';
import { Document } from '@/types/document';
import { useCanEdit } from '@/Composables/useCanEdit';

export function useResource(child: Child|Document|FileEntry|Folder) {
    // Calcul des liens (href, update, delete)
    const links = computed(() => {
        const { type, id } = child;
        // Si le type est 'file' c'est un fichier
        if (type === 'file') {
            // Donc soit un FileEntry, soit un Child possédant un mimetype
            // On dit mimetype est forcément un string
            const mimetype = ((child as FileEntry|Child).mimetype as string);
            const conditions = isImageFile(mimetype) || isGifFile(mimetype) || mimetype.includes("pdf")
            return {
                href: conditions
                        ? download.file.preview.url(id)
                        : download.file.url(id),
                update: editor.file.update.url(id),
                delete: editor.file.delete.url(id),
                history: editor.model.history.url(["files", id]),
                restore: editor.file.post.restore.url(id),
                download: download.file.url(id)
            };
        } else if (type === 'folder') {
            return {
                href: navigate.folder.url(id),
                update: editor.folder.update(id),
                delete: editor.folder.delete(id),
                restore: editor.folder.post.restore(id),
            };
        } else {
            return {
                href: navigate.document.url(id),
                update: editor.document.update(id),
                delete: editor.document.delete.url(id),
                history: editor.model.history.url(["documents", id]),
                restore: editor.document.post.restore.url(id),
            };
        }
    });

    // Gestion de la couleur
    const itemColor = computed(() => {
        // Si ce n'ai pas un fichier
        if (child.type != "file") {
            // On retourne Color en disant
            // "c'est l'un de ces trois types-là donc tu peux récupérer 'color'".
            return (child as Child|Document|Folder).color;
        }
        // Sinon 'itemColor' est null si c'est un fichier
        return undefined
    });

    // Gestion des permissions
    const canEdit = useCanEdit(child.departements ?? [])

    return {
        links,
        itemColor,
        canEdit,
    };
}
