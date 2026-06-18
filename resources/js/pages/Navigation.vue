<script setup lang="ts">
import { DeepReadonly, inject, ref, Ref, toRef, watch, computed, useModel } from 'vue';
import { Link } from "@inertiajs/vue3";

import {
    ArchiveBoxIcon,
    ArrowUturnLeftIcon,
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

// INITIALISATION DES VARIABLES
const lastParent = props.parents.at(-1);
const folder_id = ref(lastParent ? lastParent.id : 0);

const navigation = { lastParent : lastParent };
const view_mod = ref(localStorage.getItem('view_mode') || "list");

const filters = inject<DeepReadonly<Ref<FilterState>>>("activeFilters");

// Récupération réactive de l'état des barres latérales du Layout principal
const sidebarActive = inject<Ref<boolean>>('sidebarActive', ref(true));
const filterActive = inject<Ref<boolean>>('filterActive', ref(true));

const canEdit = useCanEdit(lastParent?.groupes as number[]);
const fastFolderCreation = ref(false);

// RÉCUPÉRATION DES ELEMENTS FILTRER
const filteredChildren = useFilteredChildren(
    toRef(props, "children"),
    filters as Ref<FilterState | null>,
);

// Calcul dynamique strict du nombre de colonnes de cartes
const gridColsClass = computed(() => {
    if (sidebarActive.value && filterActive.value) {
        return 'lg:grid-cols-5'; // 6 colonnes si tout est déplié
    }
    if (!sidebarActive.value && !filterActive.value) {
        return 'lg:grid-cols-8'; // 8 colonnes si tout est fermé
    }
    return 'lg:grid-cols-7'; // 7 colonnes si un seul des deux est fermé
});

// VIEW MOD :
watch(view_mod, (newValue) => {
    localStorage.setItem('view_mode', newValue);
});

// Logique raccourci pour le dossier rapide
useShortcuts({
    key: "n",
    isEnabled: canEdit.value,
    action: () => (fastFolderCreation.value = !fastFolderCreation.value),
});

const date_mode = ref("create");
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
        <SearchBarWidget
            class="mt-4"
            :currentSearch="currentSearch"
            placeholder="Rechercher un fichier, un document..."
        />
        <Link
            :href="isArchived
                    ? navigate_route.folder(folder_id)
                    : navigate_route.archived(folder_id)"
            :title="isArchived
                    ? 'Retourner au dossier'
                    : 'Voir les archives'"
            class="mx-auto mt-4 text-sky-600"
        >
            <component
                :is="isArchived
                        ? ArrowUturnLeftIcon
                        : ArchiveBoxIcon"
                class="w-10"
            />
        </Link>
    </div>

    <div
        v-show="view_mod == 'icon'"
        class="mt-6 gap-3 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 transition-all duration-300"
        :class="gridColsClass"
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
        <div
            class="bg-gray-50 dark:bg-sky-900/20 py-3 px-4 text-xs font-semibold tracking-wider text-gray-500 dark:text-zinc-400 grid grid-cols-12 uppercase"
        >
            <p class="col-span-6">Nom</p>
            <p class="col-span-1 text-center">Type</p>
            <select
                class="col-span-2 text-center p-0 pr-4 bg-transparent border-transparent
                text-xs font-semibold uppercase"
                v-model="date_mode"
            >
                <option value="create">Date de création</option>
                <option value="update">Dernière modification</option>
                <option value="deadline">Deadline</option>
            </select>
            <p class="col-span-2 text-center">Groupes</p>
            <p class="col-span-1 text-right">Actions</p>
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

<style scoped></style>
