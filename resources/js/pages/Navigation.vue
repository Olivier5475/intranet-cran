<script setup lang="ts">
// 1. Vue & Core (Vue, Inertia, etc.)
import { DeepReadonly, inject, ref, Ref, toRef, watch } from 'vue';
import { Link, useForm } from "@inertiajs/vue3";

// 2. Librairies tierces (Icônes, etc.)
import {
    ArchiveBoxIcon,
    ArrowUturnLeftIcon,
} from "@heroicons/vue/24/solid";

// 3. Composables & Fonctions Utilitaires
import { useDragAndDrop } from "@/Composables/useDragAndDrop";
import { useShortcuts } from "@/Composables/useShortcuts";
import { useFilteredChildren } from "@/Composables/useFiltres";
import { useCanEdit } from '@/Composables/useCanEdit';

// 4. Composants Internes
import CreateFolderCardWidget from "@/Components/Features/Navigation/Creation/CreateFolderCardWidget.vue";
import CreateFolderRowWidget from "@/Components/Features/Navigation/Creation/CreateFolderRowWidget.vue";
import ResourceCard from "@/Components/Features/Navigation/ResourceCard.vue";
import ResourceRow from "@/Components/Features/Navigation/ResourceRow.vue";
import SearchBarWidget from "@/Components/Features/SearchBarWidget.vue";
import NavigationHeader from '@/Components/Features/Navigation/NavigationHeader.vue';

// 5. Routes
import file_route from "@/routes/editor/file";
import navigate_route from "@/routes/navigate";

// 6. Types
import { Child } from "@/types/child";
import { Folder } from "@/types/folder";
import { FilterState } from '@/types/filtres';

const props = defineProps<{
    children: Array<Child>;
    parents: Folder[];
    currentSearch: string;
    isArchived: boolean;
}>();

const lastParent = props.parents.at(-1);
const folder_id = ref(lastParent ? lastParent.id : 0);

const filters = inject<DeepReadonly<Ref<FilterState>>>("activeFilters");

const filteredChildren = useFilteredChildren(
    toRef(props, "children"), // Convertit la prop en Ref réactive
    filters as Ref<FilterState | null>, // On force le type pour le composable
);

// VIEW MOD :
const view_mod = ref(localStorage.getItem('view_mode') || "list");

watch(view_mod, (newValue) => {
    localStorage.setItem('view_mode', newValue);
});

const canEdit = useCanEdit(lastParent?.departements as number[]);
const fastFolderCreation = ref(false);

// Logique raccourci pour le dossier rapide
useShortcuts({
    key: "n",
    isEnabled: canEdit.value,
    action: () => (fastFolderCreation.value = !fastFolderCreation.value),
});

// Logique du Drag & Drop pour les fichiers
const { isDragging } = useDragAndDrop({
    canDrop: canEdit.value,
    onDrop: (file) => {
        useForm({
            files: Array.from(file),
            departements: lastParent?.departements ?? [],
            parent_id: lastParent?.id ?? null,
        }).post(file_route.post.create.url());
    },
});
</script>

<template>
    <!------ ZONE DE DRAGGING ------->
    <div
        v-if="isDragging"
        class="left-0 top-0 bg-sky-400/40 fixed z-50 flex h-full w-full"
    >
        <div
            class="bg-sky-900/30 rounded-2xl border-sky-900 z-10 mx-auto my-auto
            flex h-[92%] w-[92%] border-4 border-dashed"
        >
            <p class="text-sky-900 text-4xl font-black mx-auto my-auto">
                Déposez votre fichier
            </p>
        </div>
    </div>

    <!-- HEADER NAVIGATION (Fil d'Ariane, Mode Affichage, Menu Creation) -->
    <NavigationHeader
        :parents="parents"
        :folder-id="folder_id"
        :can-edit="canEdit"
        v-model:view-mode="view_mod"
        v-model:fast-folder-creation="fastFolderCreation"
    />

    <!------ SEARCH BAR, Mode "Archive" ------->
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

    <!------ AFFICHAGE EN MODE ICON ------->
    <div
        v-show="view_mod == 'icon'"
        class="mt-6 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4 grid grid-cols-2"
    >
        <ResourceCard
            v-for="child in filteredChildren"
            :key="child.name"
            :child="child"
            :folder_id="folder_id"
        />
        <CreateFolderCardWidget
            v-if="fastFolderCreation && canEdit"
            :parent="parents.at(-1)"
            v-model="fastFolderCreation"
            :folder_id="folder_id"
        />
    </div>

    <!------ AFFICHAGE EN MODE LIST ------->
    <div
        v-show="view_mod == 'list'"
        class="mt-6 rounded-xl border-gray-200 dark:border-zinc-800 border"
    >
        <div
            class="bg-gray-50 dark:bg-sky-900/20 py-3 px-4 text-xs font-semibold tracking-wider text-gray-500 dark:text-zinc-400 grid grid-cols-12 uppercase"
        >
            <p class="col-span-6">Nom</p>
            <p class="col-span-1 text-center">Type</p>
            <p class="col-span-2 text-center">Date de création</p>
            <p class="col-span-2 text-center">Départements</p>
            <p class="col-span-1 text-right">Actions</p>
        </div>

        <ResourceRow
            v-for="child in filteredChildren"
            :key="child.name"
            :child="child"
            :folder_id="folder_id"
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
