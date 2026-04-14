import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import download from '@/routes/download';
import editor from '@/routes/editor';
import navigate from '@/routes/navigate';
import { isGifFile, isImageFile } from '@/Composables/useDocumentsTypeRegex';

export function useResource(props: any) {
    const page = usePage();

    // Calcul des liens (href, update, delete)
    const links = computed(() => {
        const { type, id } = props.child;
        if (type === 'file') {
            const mimetype = props.child.mimetype;
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
    const itemColor = computed(() => props.child.color);

    // Gestion des permissions
    const canEdit = computed(() => {
        // On récupère les départements de la page
        const parentDpts = props.child.departements as number[];

        // On récupère l'utilisateur
        const user = page.props.auth.user;

        // On récupère les départements de l'utilisateur
        const userDpts = user.departements as number[];

        // On récupère les départements en commun
        const compareParentAndUser = parentDpts.filter((value) =>
            userDpts.includes(value),
        );
        return (
            user.role === "admin" || // Si l'utilisateur est un admin, il peut créer.
            // Si c'est un editeur et qu'il a des roles en commun avec la page, il peut créer.
            (
                user.role === "editeur" &&
                (
                    parentDpts.length === 0 ||
                    compareParentAndUser.length > 0
                )
            )
        );
    });

    return {
        links,
        itemColor,
        canEdit,
    };
}
