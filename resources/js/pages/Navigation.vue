<script setup lang="ts">
import RepertoryWidget from '@/Components/RepertoryWidget.vue';
import SearchBarWidget from '@/Components/SearchBarWidget.vue';
import ResourceCard from '@/Components/ResourceCard.vue';
import CreationWidget from '@/Components/CreationWidget.vue';
import { ListBulletIcon, ViewColumnsIcon } from '@heroicons/vue/20/solid';

import { computed, DeepReadonly, inject, onMounted, onUnmounted, ref, Ref, toRef } from 'vue';
import { useFilteredChildren } from '@/lib/filtres';
import ResourceRow from '@/Components/ResourceRow.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import route from '@/routes/editor/file';
import CreateFolderRowWidget from '@/Components/CreateFolderRowWidget.vue';

const page = usePage();
const props = defineProps<{
    children: Array<{
        id: number;
        name: string;
        type: string;
        color?: string;
        mimetype?: string;
        departements?: number[];
        created_at: string;
    }>;
    parents: Array<{
        id: number;
        name: string;
        departements: number[];
    }>;
    currentSearch: string;
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

const filters = inject<DeepReadonly<Ref<FilterState>>>('activeFilters');

const filteredChildren = useFilteredChildren(
    toRef(props, 'children'), // Convertit la prop en Ref réactive
    filters as Ref<FilterState | null>, // On force le type pour le composable
);

const view_mod = ref('list');

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
const compareParentAndUser = parentDpts.filter((value) =>  userDpts.includes(value));
const canCreate = ref(user.role === "admin" || ( // Si l'utilisateur est un admin, il peut créer.
            // Si c'est un editeur et qu'il a des roles en commun avec la page, il peut créer.
            user.role === "editor" &&  (parentDpts.length === 0 || compareParentAndUser.length > 0)
        )
);

let dragCounter = 0;
const isDragging = ref(false);
const fastFolderCreation = ref(false);

if (canCreate.value) {
    // On retire les actions par défaut de dragover
    // (évite que ça ouvre le fichier au lieu de le traiter dans le site)
    window.addEventListener('dragover', (e) => {
        e.preventDefault();
    });

    window.addEventListener('dragenter', (e) => {
        // On retire les actions par défaut de dragover
        e.preventDefault();

        // on met isDragging à true si un drag arrive sur la fenêtre
        dragCounter++;
        if (dragCounter === 1) isDragging.value = true;
    });

    window.addEventListener('dragleave', (e) => {
        // On retire les actions par défaut de dragover
        e.preventDefault();

        // on met isDragging à false si le drag part de la fenêtre
        dragCounter--;
        if (dragCounter === 0) isDragging.value = false;
    });

    window.addEventListener('drop', (e) => {
        // On retire les actions par défaut de dragover
        e.preventDefault();

        // on met isDragging à false si le fichier est drop sur fenêtre
        dragCounter = 0;
        isDragging.value = false;

        // on lance l'enregistrement du fichier
        catchFile(e);
    });

    /**
     * Methode pour lancer la requete d'enregistrement d'un fichier venant d'être drop en BD
     * @param e
     */
    const catchFile = (e: any) => {
        // on récupère le fichier
        const file = e.dataTransfer.files;

        // on créait un formulaire avec le fichier et des valeurs par défauts
        const form = useForm({
            name: '',
            files: file,
            departements: props.parents.at(-1)?.departements ?? [],
            parent_id: props.parents.at(-1)?.id ?? null,
        });

        // on envoie le formulaire
        form.post(route.post.create.url());

        // on s'assure que isDragging est à false
        dragCounter = 0;
        isDragging.value = false;
    };


    /**
     * Méthode pour setup un raccourci pour l'ouverture de la creation rapide de dossier
     * @param event
     */
    const handleGlobalKeyDown = (event: KeyboardEvent) => {
        // on récupère l'élément actif
        const activeEl = document.activeElement;

        // On vérifie si l'élément focus est un champ de saisie
        const isInput = activeEl instanceof HTMLInputElement ||
            activeEl instanceof HTMLTextAreaElement;

        // On vérifie si l'élément est éditable (en le castant en HTMLElement).
        const isEditable = activeEl instanceof HTMLElement && activeEl.isContentEditable;

        // si l'utilisateur appuie sur N et qu'il n'est pas dans un editable ou une zone de saisie,
        // on ouvre la création rapide de dossier
        if (event.key.toLowerCase() === 'n' && !isInput && !isEditable) {
            fastFolderCreation.value = !fastFolderCreation.value;
        }
    };

    onMounted(() => {
        window.addEventListener('keydown', handleGlobalKeyDown);
    });

    onUnmounted(() => {
        window.removeEventListener('keydown', handleGlobalKeyDown);
    });
}
</script>

<template>
    <div v-if="isDragging" class="left-0 top-0 bg-sky-400/40 absolute z-50 flex h-full w-full">
        <div class="bg-sky-900/30 rounded-2xl border-sky-900 z-10 mx-auto my-auto flex h-[92%] w-[92%] border-4 border-dashed">
            <p class="text-sky-900 text-4xl font-black mx-auto my-auto">Déposez votre fichier</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-2 rounded-xl shadow-sm border-gray-100 dark:border-zinc-800 flex items-center justify-between border">
        <RepertoryWidget :parents="parents" />

        <div class="space-x-4 flex items-center">
            <div class="bg-gray-100 dark:bg-slate-800 p-1 rounded-lg flex">
                <button
                    @click="change_mod('list')"
                    class="p-1.5 rounded-md transition-all"
                    :class="view_mod === 'list' ? 'bg-white dark:bg-slate-700 shadow-sm text-sky-500' : 'text-gray-400 hover:text-gray-600'"
                >
                    <ListBulletIcon class="w-5 h-5" />
                </button>
                <button
                    @click="change_mod('icon')"
                    class="p-1.5 rounded-md transition-all"
                    :class="view_mod === 'icon' ? 'bg-white dark:bg-slate-700 shadow-sm text-sky-500' : 'text-gray-400 hover:text-gray-600'"
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

    <SearchBarWidget class="mt-4" :currentSearch="currentSearch" />

    <div v-show="view_mod == 'icon'" class="mt-6 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4 grid grid-cols-2">
        <ResourceCard v-for="child in filteredChildren" :key="child.name" :child="child" :folder_id="folder_id" />
    </div>

    <div v-show="view_mod == 'list'" class="mt-6 rounded-xl border-gray-200 dark:border-zinc-800 border">
        <div
            class="bg-gray-50 dark:bg-sky-900/20 py-3 px-4 text-xs font-semibold tracking-wider text-gray-500 dark:text-zinc-400 grid grid-cols-12 uppercase"
        >
            <p class="col-span-5">Nom</p>
            <p class="col-span-2 text-center">Type</p>
            <p class="col-span-3 text-center">Date de création</p>
            <p class="col-span-2 text-right">Actions</p>
        </div>
        <ResourceRow v-for="child in filteredChildren" :key="child.name" :child="child" :folder_id="folder_id" />
        <div v-if="fastFolderCreation && canCreate">
            <CreateFolderRowWidget :parent="parents.at(-1)" v-model="fastFolderCreation" :folder_id="folder_id" />
        </div>
    </div>
</template>

<style scoped></style>
