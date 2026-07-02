import { computed } from 'vue';
import type { Ref } from 'vue';
import { FilterState } from '@/types/filtres';
import { Child } from '@/types/child'
import { filtre } from '@/Composables/useDocumentsTypeRegex';

const getTypePriority = (type: string): number => {
    switch (type) {
        case 'folder': return 1;
        case 'document': return 2;
        case 'file': return 3;
        default: return 99;
    }
};

const getExtension = (name: string): string => {
    const lastDot = name.lastIndexOf('.');
    if (lastDot <= 0) return '';
    return name.substring(lastDot + 1).toLowerCase();
};

export function useFilteredChildren(
    children: Ref<Child[]>,
    filters: Ref<FilterState | null>,
    date_mode: Ref<string>,
    sortColumn: Ref<'name' | 'type' | 'date'>, // 🚀 Nouveaux arguments
    sortDirection: Ref<'asc' | 'desc'>
) {
    return computed(() => {
        const currentFilters = filters.value;
        let items = [...children.value];

        if (currentFilters) {
            const { selectedGroupes, fileType, startDate, endDate } = currentFilters;

            if (selectedGroupes && selectedGroupes.length > 0) {
                items = items.filter(child =>
                    child.groupes &&
                    child.groupes.some(grp => selectedGroupes.includes(grp))
                );
            }
            if (fileType && fileType !== 'all') {
                items = items.filter((child) => {
                    if (child.type === "file" && child.mimetype) {
                        return filtre(child.mimetype, fileType);
                    }
                    return child.type === fileType;
                });
            }
            if (startDate) {
                items = items.filter(child => new Date(child.created_at) >= new Date(startDate));
            }
            if (endDate) {
                items = items.filter(child => new Date(child.created_at) <= new Date(endDate));
            }
        }

        // --- 🚀 TRI DYNAMIQUE (EXPLORATEUR DE FICHIERS) ---
        const dir = sortDirection.value === 'asc' ? 1 : -1;

        items.sort((a, b) => {
            // Règle d'or : Les dossiers restent toujours en haut (comme sur Windows/Mac)
            // Sauf si on trie spécifiquement par "Type"
            const priorityA = getTypePriority(a.type);
            const priorityB = getTypePriority(b.type);

            if (sortColumn.value === 'name') {
                if (priorityA !== priorityB) return priorityA - priorityB; // Garde les dossiers en haut

                // Tri par extension pour les fichiers
                if (a.type === 'file' && b.type === 'file') {
                    const extCompare = getExtension(a.name).localeCompare(getExtension(b.name));
                    if (extCompare !== 0) return extCompare * dir;
                }
                return a.name.localeCompare(b.name) * dir;
            }

            if (sortColumn.value === 'type') {
                // Ici, on trie strictement par type, puis on ordonne par nom
                if (priorityA !== priorityB) return (priorityA - priorityB) * dir;
                return a.name.localeCompare(b.name);
            }

            if (sortColumn.value === 'date') {
                let dateA = 0;
                let dateB = 0;

                if (date_mode.value === 'create') {
                    dateA = new Date(a.created_at).getTime();
                    dateB = new Date(b.created_at).getTime();
                } else if (date_mode.value === 'update') {
                    dateA = new Date(a.updated_at || a.created_at).getTime();
                    dateB = new Date(b.updated_at || b.created_at).getTime();
                } else if (date_mode.value === 'deadline') {
                    // Pour les deadlines, ceux qui n'en ont pas vont à la fin
                    if (!a.deadline && !b.deadline) return 0;
                    if (!a.deadline) return 1;
                    if (!b.deadline) return -1;
                    dateA = new Date(a.deadline).getTime();
                    dateB = new Date(b.deadline).getTime();
                }

                return (dateA - dateB) * dir;
            }

            return 0;
        });

        return items;
    });
}
