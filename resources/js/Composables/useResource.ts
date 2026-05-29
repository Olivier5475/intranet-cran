import { computed } from 'vue';

import download from '@/routes/download';
import editor from '@/routes/editor';
import admin from '@/routes/admin';
import navigate from '@/routes/navigate';

import {
    isDocFile,
    isGifFile,
    isImageFile,
    isPresentationFile, isTabFile, isTextFile,
    isVideoFile
} from '@/Composables/useDocumentsTypeRegex';
import { useCanEdit } from '@/Composables/useCanEdit';

import { Child } from '@/types/child';
import { Folder } from '@/types/folder';
import { FileEntry } from '@/types/fileEntry';
import { Document } from '@/types/document';

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
                archive: editor.file.archive.url(id),
                delete: admin.file.delete.url(id),
                history: editor.model.history.url(["files", id]),
                restore: editor.file.post.restore.url(id),
                download: download.file.url(id)
            };
        } else if (type === 'folder') {
            return {
                href: navigate.folder.url(id),
                update: editor.folder.update.url(id),
                archive: editor.folder.archive.url(id),
                delete: admin.folder.delete.url(id),
                restore: editor.folder.post.restore.url(id),
            };
        } else {
            return {
                href: navigate.document.url(id),
                update: editor.document.update.url(id),
                archive: editor.document.archive.url(id),
                delete: admin.document.delete.url(id),
                history: editor.model.history.url(["documents", id]),
                restore: editor.document.post.restore.url(id),
            };
        }
    });

    // Gestion de la couleur
    const itemColor = computed(() => {
        // 1. Logique pour les Appels à Projet (Prioritaire)
        if (child.type === 'appelprojet') {
            const projectChild = child as { deadline?: string };
            if (projectChild.deadline) {
                const now = new Date();
                const deadline = new Date(projectChild.deadline);

                const diffInMs = deadline.getTime() - now.getTime();
                const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

                if (diffInDays <= 2) {
                    return '#ef4444'; // text-red-500
                }
                if (diffInDays <= 7) {
                    return '#f97316'; // text-orange-500
                }
                if (diffInDays <= 14) {
                    return '#eab308'; // text-yellow-500
                }
                return '#22c55e'; // text-green-500
            }
        }

        // 2. Logique pour les Fichiers (Détection par Mimetype)
        if (child.type === "file") {
            const mime = (child as FileEntry|Child).mimetype || "";

            if (isImageFile(mime)) return '#ec4899';       // text-pink-500
            if (isVideoFile(mime)) return '#a855f7';       // text-purple-500
            if (isGifFile(mime)) return '#6366f1';         // text-indigo-500
            if (isPresentationFile(mime)) return '#f97316';// text-orange-500
            if (isDocFile(mime)) return '#2563eb';         // text-blue-600
            if (mime.includes("pdf")) return '#dc2626';    // text-red-600
            if (isTabFile(mime)) return '#059669';         // text-emerald-600
            if (isTextFile(mime)) return '#000000';        // text-black

            return '#94a3b8'; // text-slate-400 (par défaut pour PaperClipIcon)
        }

        // 3. Logique pour les Dossiers et Documents (Couleur personnalisée de la BDD)
        return (child as Child|Document|Folder).color;
    });

    // Gestion des permissions
    const canEdit = useCanEdit(child.groupes ?? [])

    return {
        links,
        itemColor,
        canEdit,
    };
}
