<script setup lang="ts">
// 1. Vue & Core (Vue, Inertia, etc.)
import { computed, DeepReadonly, inject, ref, Ref, toRef } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";

// 2. Librairies tierces (Icônes, etc.)
import {
    ListBulletIcon,
    ViewColumnsIcon,
    ArchiveBoxIcon,
    ArrowUturnLeftIcon
} from "@heroicons/vue/20/solid";

// 3. Composables & Fonctions Utilitaires
import { useDragAndDrop } from "@/Composables/useDragAndDrop";
import { useShortcuts } from "@/Composables/useShortcuts";
import { useFilteredChildren } from "@/Composables/useFiltres";

// 4. Composants Internes
import CreateFolderCardWidget from "@/Components/CreateFolderCardWidget.vue";
import CreateFolderRowWidget from "@/Components/CreateFolderRowWidget.vue";
import CreationWidget from "@/Components/CreationWidget.vue";
import RepertoryWidget from "@/Components/RepertoryWidget.vue";
import ResourceCard from "@/Components/ResourceCard.vue";
import ResourceRow from "@/Components/ResourceRow.vue";
import SearchBarWidget from "@/Components/SearchBarWidget.vue";

// 5. Routes
import file_route from "@/routes/editor/file";
import navigate_route from "@/routes/navigate";

// 6. Types
import { Child } from "@/types/child";
import { Folder } from "@/types/folder";

const page = usePage();
const props = defineProps<{
    children: Array<Child>;
    parents: Folder[];
    currentSearch: string;
    isArchived: boolean
}>();

const folder_id = computed(() => {
    const lastParent = props.parents.at(-1);

    return lastParent ? lastParent.id : 0;
});
interface FilterState {
    startDate?: string | null;
    endDate?: string | null;
    fileType?: string;
    sortBy?: string;
    selectedDepartments?: number[];
}

const filters = inject<DeepReadonly<Ref<FilterState>>>("activeFilters");

const filteredChildren = useFilteredChildren(
    toRef(props, "children"), // Convertit la prop en Ref réactive
    filters as Ref<FilterState | null>, // On force le type pour le composable
);

const view_mod = ref("list");

const change_mod = (value: string) => {
    view_mod.value = value;
};

// On récupère les départements de la page
const parentDpts = props.parents.at(-1)?.departements as number[];

// On récupère l'utilisateur
const user = page.props.auth.user;

// On récupère les départements de l'utilisateur
const userDpts = user.departements_ids as number[];

// On récupère les départements en commun
const compareParentAndUser = parentDpts.filter((value) =>
    userDpts.includes(value),
);
const canCreate = ref(
    user.role === "admin" || // Si l'utilisateur est un admin, il peut créer.
        // Si c'est un editeur et qu'il a des roles en commun avec la page, il peut créer.
        (user.role === "editor" &&
            (parentDpts.length === 0 || compareParentAndUser.length > 0)),
);

const fastFolderCreation = ref(false);

// Logique raccourci pour le dossier rapide
useShortcuts({
    key: "n",
    isEnabled: canCreate.value,
    action: () => (fastFolderCreation.value = !fastFolderCreation.value),
});

// Logique du Drag & Drop pour les fichiers
const { isDragging } = useDragAndDrop({
    canDrop: canCreate.value,
    onDrop: (file) => {
        const filesArray = Array.from(file);
        const form = useForm({
            name: "",
            files: filesArray,
            departements: props.parents.at(-1)?.departements ?? [],
            parent_id: props.parents.at(-1)?.id ?? null,
        });

        // on envoie le formulaire
        form.post(file_route.post.create.url());
    },
});
</script>

<template>
    <div
        v-if="isDragging"
        class="left-0 top-0 bg-sky-400/40 absolute z-50 flex h-full w-full"
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

    <div
        class="bg-white dark:bg-slate-900 p-2 rounded-xl shadow-sm border-gray-100
        dark:border-zinc-800 flex items-center justify-between border"
    >
        <RepertoryWidget :parents="parents" />

        <div class="space-x-4 flex items-center">
            <div class="bg-gray-100 dark:bg-slate-800 p-1 rounded-lg flex">
                <button
                    @click="change_mod('list')"
                    class="p-1.5 rounded-md transition-all"
                    :class="
                        view_mod === 'list'
                            ? 'bg-white dark:bg-slate-700 shadow-sm text-sky-500'
                            : 'text-gray-400 hover:text-gray-600'
                    "
                >
                    <ListBulletIcon class="w-5 h-5" />
                </button>
                <button
                    @click="change_mod('icon')"
                    class="p-1.5 rounded-md transition-all"
                    :class="
                        view_mod === 'icon'
                            ? 'bg-white dark:bg-slate-700 shadow-sm text-sky-500'
                            : 'text-gray-400 hover:text-gray-600'
                    "
                >
                    <ViewColumnsIcon class="w-5 h-5" />
                </button>
            </div>

            <CreationWidget
                v-if="canCreate"
                v-model="fastFolderCreation"
                :folder_id="folder_id"
                :is-active-fast-creation="fastFolderCreation"
            />
        </div>
    </div>

    <div class="flex">
        <SearchBarWidget
            class="mt-4"
            :currentSearch="currentSearch"
            placeholder="Rechercher un fichier, un document..."
        />
        <Link
            v-if="isArchived"
            :href="navigate_route.folder(folder_id)"
            class="mx-auto mt-4"
            title="Retourner au dossier"
        >
            <ArrowUturnLeftIcon class="w-10"></ArrowUturnLeftIcon>
        </Link>
        <Link
            v-else
            :href="navigate_route.archived(folder_id)"
            class="mx-auto mt-4"
            title="Voir les archives"
        >
            <ArchiveBoxIcon class="w-10"></ArchiveBoxIcon>
        </Link>
    </div>

    <!--    AFFICHAGE EN MODE ICON    -->
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
            v-if="fastFolderCreation && canCreate"
            :parent="parents.at(-1)"
            v-model="fastFolderCreation"
            :folder_id="folder_id"
        />
    </div>

    <!--    AFFICHAGE EN MODE LIST    -->
    <div
        v-show="view_mod == 'list'"
        class="mt-6 rounded-xl border-gray-200 dark:border-zinc-800 border"
    >
        <div
            class="bg-gray-50 dark:bg-sky-900/20 py-3 px-4 text-xs font-semibold
            tracking-wider text-gray-500 dark:text-zinc-400 grid grid-cols-12 uppercase"
        >
            <p class="col-span-5">Nom</p>
            <p class="col-span-2 text-center">Type</p>
            <p class="col-span-3 text-center">Date de création</p>
            <p class="col-span-2 text-right">Actions</p>
        </div>

        <ResourceRow
            v-for="child in filteredChildren"
            :key="child.name"
            :child="child"
            :folder_id="folder_id"
        />
        <div v-if="fastFolderCreation && canCreate">
            <CreateFolderRowWidget
                :parent="parents.at(-1)"
                v-model="fastFolderCreation"
                :folder_id="folder_id"
            />
        </div>
    </div>
</template>

<style scoped></style>
