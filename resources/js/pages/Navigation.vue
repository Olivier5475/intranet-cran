<script setup lang="ts">
import { DeepReadonly, inject, ref, Ref, toRef, watch, computed } from 'vue';
import { Link } from "@inertiajs/vue3";

import {
    ArchiveBoxIcon,
    ArrowUturnLeftIcon,
    ChevronUpIcon,     // 🚀 Nouveaux imports
    ChevronDownIcon,
    ChevronUpDownIcon
} from "@heroicons/vue/24/solid";

import { useShortcuts } from "@/Composables/useShortcuts";
import { useFilteredChildren } from "@/Composables/useFiltres";
import { useCanEdit } from '@/Composables/useCanEdit';

import CreateFolderCardWidget from "@/Components/Features/Navigation/Creation/CreateFolderCardWidget.vue";
import CreateFolderRowWidget from "@/Components/Features/Navigation/Creation/CreateFolderRowWidget.vue";
import ResourceCard from "@/Components/Features/Navigation/Resource/ResourceCard.vue";
import ResourceRow from "@/Components/Features/Navigation/Resource/ResourceRow.vue";
import SearchBarWidget from "@/Components/Features/SearchBarWidget.vue";
import NavigationHeader from '@/Components/Features/Navigation/NavigationHeader.vue';

import navigate_route from "@/routes/navigate";

import { Child } from "@/types/child";
import { Folder } from "@/types/folder";
import { FilterState } from '@/types/filtres';
import PageDragDropWidget from '@/Components/Features/PageDragDropWidget.vue';

const props = defineProps<{
    children: Array<Child>;
    parents: Folder[];
    currentSearch?: string;
    isArchived: boolean;
    favorites: Child[]
}>();

const lastParent = props.parents.at(-1);
const folder_id = ref(lastParent ? lastParent.id : 0);

const navigation = { lastParent : lastParent };
const view_mod = ref(localStorage.getItem('view_mode') || "list");

const filters = inject<DeepReadonly<Ref<FilterState>>>("activeFilters");

const canEdit = useCanEdit(lastParent?.groupes as number[]);
const fastFolderCreation = ref(false);
const date_mode = ref<'create' | 'update' | 'deadline'>("create");

// 🚀 NOUVEAUX ÉTATS POUR LE TRI
const sortColumn = ref<'name' | 'type' | 'date'>('name');
const sortDirection = ref<'asc' | 'desc'>('asc');

// 🚀 GESTION DU CLIC SUR LES EN-TÊTES
const toggleSort = (column: 'name' | 'type' | 'date') => {
    if (sortColumn.value === column) {
        // Inverse la direction si on clique sur la même colonne
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        // Change de colonne (par défaut ascendant pour texte, descendant pour date)
        sortColumn.value = column;
        sortDirection.value = column === 'date' ? 'desc' : 'asc';
    }
};

// 🚀 GESTION DU CHANGEMENT DE SÉLECTEUR DE DATE
const handleDateChange = () => {
    if (sortColumn.value !== 'date') {
        sortColumn.value = 'date';
        sortDirection.value = 'desc'; // Plus récent en premier par défaut
    }
};

// RÉCUPÉRATION DES ELEMENTS FILTRÉS
const filteredChildren = useFilteredChildren(
    toRef(props, "children"),
    filters as Ref<FilterState | null>,
    date_mode,
    sortColumn,
    sortDirection
);

watch(view_mod, (newValue) => {
    localStorage.setItem('view_mode', newValue);
});

useShortcuts({
    key: "n",
    isEnabled: canEdit.value,
    action: () => (fastFolderCreation.value = !fastFolderCreation.value),
});

const hasAppelProjet = computed(() => filteredChildren.value.some(child => child.type === "appelprojet"));
</script>

<template>
    <PageDragDropWidget
        :can-edit="canEdit"
        :navigation="navigation"
    />

    <NavigationHeader
        :parents="parents"
        :folder-id="folder_id"
        :can-edit="canEdit"
        v-model:view-mode="view_mod"
        v-model:fast-folder-creation="fastFolderCreation"
    />

    <div class="flex">
        <SearchBarWidget class="mt-4" :currentSearch="currentSearch" placeholder="Rechercher un fichier, un document..." />
        <Link
            :href="isArchived ? navigate_route.folder(folder_id) : navigate_route.archived(folder_id)"
            :title="isArchived ? 'Retourner au dossier' : 'Voir les archives'"
            class="mx-auto mt-4 text-sky-600"
        >
            <component :is="isArchived ? ArrowUturnLeftIcon : ArchiveBoxIcon" class="w-10" />
        </Link>
    </div>

    <div
        v-show="view_mod == 'icon'"
        class="mt-6 gap-4 grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] transition-all duration-300"
    >
        <ResourceCard
            v-for="child in filteredChildren"
            :key="child.name"
            :child="child"
            :folder_id="folder_id"
            :favorites="favorites"
        />

        <CreateFolderCardWidget
            v-if="fastFolderCreation && canEdit"
            :parent="parents.at(-1)"
            v-model="fastFolderCreation"
            :folder_id="folder_id"
        />
    </div>

    <div
        v-show="view_mod == 'list'"
        class="mt-6 rounded-xl border-gray-200 dark:border-zinc-800 border"
    >
        <div class="bg-gray-50 dark:bg-sky-900/20 py-3 px-4 text-xs font-semibold tracking-wider text-gray-500 dark:text-zinc-400 grid grid-cols-12 uppercase select-none">

            <button @click="toggleSort('name')" class="col-span-5 flex items-center justify-start gap-1 hover:text-sky-500 transition-colors uppercase font-semibold text-left">
                Nom
                <ChevronUpIcon v-if="sortColumn === 'name' && sortDirection === 'asc'" class="w-4 h-4" />
                <ChevronDownIcon v-else-if="sortColumn === 'name' && sortDirection === 'desc'" class="w-4 h-4" />
                <ChevronUpDownIcon v-else class="w-4 h-4 opacity-30" />
            </button>

            <button @click="toggleSort('type')" class="col-span-1 flex items-center justify-center gap-1 hover:text-sky-500 transition-colors uppercase font-semibold">
                Type
                <ChevronUpIcon v-if="sortColumn === 'type' && sortDirection === 'asc'" class="w-4 h-4" />
                <ChevronDownIcon v-else-if="sortColumn === 'type' && sortDirection === 'desc'" class="w-4 h-4" />
                <ChevronUpDownIcon v-else class="w-4 h-4 opacity-30" />
            </button>

            <div class="col-span-3 flex items-center justify-center gap-1">
                <select
                    class="dark:bg-sky-900 w-2/3 text-center p-0 pr-1 bg-transparent border-transparent text-xs font-bold uppercase hover:text-sky-300 focus:ring-0 cursor-pointer transition-colors"
                    v-model="date_mode"
                    @change="handleDateChange"
                >
                    <option value="create">Créé le</option>
                    <option value="update">Modifié le</option>
                    <option v-if="hasAppelProjet" value="deadline">Deadline</option>
                </select>
                <button @click="toggleSort('date')" class="hover:text-sky-500 hover:bg-gray-200 dark:hover:bg-slate-700 p-1 rounded-full transition-colors">
                    <ChevronUpIcon v-if="sortColumn === 'date' && sortDirection === 'asc'" class="w-4 h-4" />
                    <ChevronDownIcon v-else-if="sortColumn === 'date' && sortDirection === 'desc'" class="w-4 h-4" />
                    <ChevronUpDownIcon v-else class="w-4 h-4 opacity-30" />
                </button>
            </div>

            <p class="col-span-2 text-center flex items-center justify-center">Groupes</p>
            <p class="col-span-1 text-right flex items-center justify-end">Actions</p>
        </div>

        <ResourceRow
            v-for="child in filteredChildren"
            :key="child.name"
            :child="child"
            :folder_id="folder_id"
            :date_mode="date_mode"
        />
        <div v-if="fastFolderCreation && canEdit">
            <CreateFolderRowWidget
                :parent="parents.at(-1)"
                v-model="fastFolderCreation"
                :folder_id="folder_id"
            />
        </div>
    </div>
</template>
