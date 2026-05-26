import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useCanEdit = (groupes: number[]) => {
    const page = usePage();

    // On récupère l'utilisateur
    const user = page.props.auth.user;

    // On récupère les groupes de l'utilisateur
    const userGrps = user.groupes as number[];

    // On récupère les groupes en commun
    const compareParentAndUser = groupes.filter((value) => userGrps.includes(value));
    return  ref(
        user.role === 'admin' || // Si l'utilisateur est un admin, il peut créer.
        // Si c'est un editeur et qu'il a des roles en commun avec la page, il peut créer.
        (user.role === 'editor' && (groupes.length === 0 || compareParentAndUser.length > 0)),
    );
}
