import { computed } from 'vue';
import type { Ref } from 'vue';
import { FilterState, NavigationChild } from '@/types/filtres';
import { filtre } from '@/lib/documentsTypeRegex';

// --- HELPER 1 : Priorité des types ---
// (Défini ici pour ne pas être recréé à chaque rendu)
const getTypePriority = (type: string): number => {
    switch (type) {
        case 'folder': return 1;   // Vient en premier
        case 'document': return 2; // Vient en second
        case 'file': return 3;     // Vient en troisième
        default: return 99;        // Tout le reste à la fin
    }
};

// --- HELPER 2 : Extension de fichier ---
const getExtension = (name: string): string => {
    const lastDot = name.lastIndexOf('.');
    // S'il n'y a pas de point, ou si c'est le premier caractère (ex: .env)
    if (lastDot <= 0) {
        return ''; // Pas d'extension
    }
    return name.substring(lastDot + 1).toLowerCase();
};

export function useFilteredChildren(
    children: Ref<NavigationChild[]>,
    filters: Ref<FilterState | null>
) {
    return computed(() => {
        const currentFilters = filters.value;
        let items = [...children.value];

        if (!currentFilters) {
            return items;
        }

        const { selectedDepartments, fileType, startDate, endDate, sortBy } = currentFilters;

        // ... (votre logique de FILTRAGE existante reste ici) ...
        // a) Filtre Départements
        if (selectedDepartments && selectedDepartments.length > 0) {
            items = items.filter(child =>
                child.departements &&
                child.departements.some(dep => selectedDepartments.includes(dep))
            );
        }
        // b) Filtre Type de fichier
        if (fileType && fileType !== 'all') {
            items = items.filter((child) => {
                if(child.type === "file" && child.mimetype) {
                    return filtre(child.mimetype, fileType);
                }
                return child.type === fileType;
            });
        }
        // c) Filtre Date de début
        if (startDate) {
            items = items.filter(child => new Date(child.created_at) >= new Date(startDate));
        }
        // d) Filtre Date de fin
        if (endDate) {
            items = items.filter(child => new Date(child.created_at) <= new Date(endDate));
        }


        // --- e) TRI (LOGIQUE MISE À JOUR) ---
        if (sortBy) {
            if (sortBy === 'newest') {
                items.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
                items.forEach(item => {new Date(item.created_at).getTime()});
            }
            else if (sortBy === 'oldest') {
                items.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());
                items.forEach(item => {new Date(item.created_at).getTime()});
            }

            // --- NOUVELLE LOGIQUE POUR 'name' ---
            else if (sortBy === 'name') {
                console.log('sort by: ', sortBy);
                items.sort((a, b) => {
                    // NIVEAU 1 : Trier par type (folder > document > file)
                    const priorityA = getTypePriority(a.type);
                    const priorityB = getTypePriority(b.type);
                    if (priorityA !== priorityB) {
                        return priorityA - priorityB; // Tri numérique simple
                    }

                    // NIVEAU 2 : Les types sont identiques
                    // Si ce sont des 'file', on trie par extension d'abord
                    if (a.type === 'file') {
                        const extA = getExtension(a.name);
                        const extB = getExtension(b.name);
                        const extCompare = extA.localeCompare(extB);

                        if (extCompare !== 0) {
                            return extCompare; // Les extensions sont différentes
                        }
                    }

                    // NIVEAU 3 : Tri par nom
                    // (Pour 'folder', 'document', ou 'file' avec la même extension)
                    return a.name.localeCompare(b.name);
                });
            }
        }
        return items;
    });
}
