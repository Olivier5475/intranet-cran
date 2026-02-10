import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import download from '@/routes/download';
import editor from '@/routes/editor';
import navigate from '@/routes/navigate';

export function useResource(props: any) {
    const page = usePage();

    // Calcul des liens (href, update, delete)
    const links = computed(() => {
        const { type, id } = props.child;
        if (type === 'file') {
            return {
                href: download.file.url(id),
                update: editor.file.update.url(id),
                delete: editor.file.delete.url(id),
            };
        } else if (type === 'folder') {
            return {
                href: navigate.folder.url(id),
                update: editor.folder.update(id),
                delete: editor.folder.delete(id), // Pas de suppression directe ici selon ton code
            };
        } else {
            return {
                href: navigate.document.url(id),
                update: editor.document.update(id),
                delete: editor.document.delete.url(id),
            };
        }
    });

    // Gestion de la couleur
    const itemColor = computed(() => props.child.color);

    // Gestion des permissions
    const canEdit = computed(() => {
        const userDeps = page.props.auth.user.departements_ids as number[];
        // On vérifie si l'utilisateur a un département en commun OU si l'objet n'a pas de département (public/global)
        return (
            props.child.departements.length === 0 ||
            props.child.departements.some((depId: number) => userDeps.includes(depId))
        );
    });

    return {
        links,
        itemColor,
        canEdit,
    };
}
