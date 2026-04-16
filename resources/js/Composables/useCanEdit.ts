import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useCanEdit = (departements: number[]) => {
    const page = usePage();

    // On récupère l'utilisateur
    const user = page.props.auth.user;

    // On récupère les départements de l'utilisateur
    const userDpts = user.departements as number[];

    // On récupère les départements en commun
    const compareParentAndUser = departements.filter((value) => userDpts.includes(value));
    return  ref(
        user.role === 'admin' || // Si l'utilisateur est un admin, il peut créer.
        // Si c'est un editeur et qu'il a des roles en commun avec la page, il peut créer.
        (user.role === 'editor' && (departements.length === 0 || compareParentAndUser.length > 0)),
    );
}
