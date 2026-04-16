<script setup lang="ts">
// 1. Vue & Core
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { ArrowDownTrayIcon, ArrowTurnDownRightIcon } from "@heroicons/vue/24/solid";

// 3. Composables & Utilitaires
import { decodeEntities } from "@/Composables/useDecodeModule";
import { useResource } from "@/Composables/useResource";

// 4. Composants
import ResourceIcon from "@/Components/Features/Navigation/ResourceIcon.vue";

// 5. Types
import { Child } from "@/types/child";
import { Departement } from "@/types/departement";

// 6. Routes
import folder_route from '@/routes/editor/folder';
import document_route from '@/routes/editor/document';
import file_route from '@/routes/editor/file';
import EditorActionsWidget from '@/Components/Features/EditorActionsWidget.vue';
import FilePreviewWidget from '@/Components/Features/Navigation/FilePreviewWidget.vue';

const props = defineProps<{
    child: Child;
    folder_id: number;
}>();

// Utilisation du composable
const { links, itemColor, canEdit } = useResource(props.child);


const page = usePage();

// Récupère les informations d'un département grâce à son id
const getDep = (id: number) => {
    return page.props.departements.find((d: Departement) => d.id === id);
};

const showImage = ref(false);
const wasShown = ref(false); // Garde en mémoire si on a déjà survolé

const handleMouseEnter = () => {
    showImage.value = true;

    // On active le rendu définitif, permet de ne pas avoir à charger plusieurs fois une image
    wasShown.value = true;
};

const route = props.child.type == "folder"
    ? folder_route.post.update
    : props.child.type == "document"
        ? document_route.post.update
        : file_route.post.update;


const form = useForm({
    name: props.child.name,
});

const submit = () => {
    console.log(route.url(props.child.id))
    form.post(route.url(props.child.id));
}

const handleDragStart = (e: DragEvent) => {
    if (e.dataTransfer) {
        // On passe l'ID et le type pour savoir quoi déplacer côté serveur ou app
        e.dataTransfer.setData('resource_id', props.child.id.toString());
        e.dataTransfer.setData('resource_type', props.child.type);

        // Optionnel : change l'effet visuel du curseur
        e.dataTransfer.effectAllowed = 'move';
    }
};

const activeRename = ref(false);
const handleRename = (value: boolean) => {
    activeRename.value = value;
}
</script>

<template>
    <FilePreviewWidget
        :was-shown="wasShown"
        :show-image="showImage"
        :child="child"
    />

    <div
        class="group py-3 px-4 border-gray-100 dark:border-zinc-800
        hover:bg-sky-50/50 dark:hover:bg-slate-900/10 grid grid-cols-12
        items-center border-t transition-colors duration-150"

        draggable="true"
        @dragstart="handleDragStart"
    >
        <component
            v-if="activeRename == false"
            :is="child.type !== 'file' ? Link : 'a'"
            :href="links.href"
            :target="child.type == 'file' ? 'blank' : ''"
            class="space-x-3 col-span-6 flex items-center overflow-hidden"

            @mouseenter="handleMouseEnter"
            @mouseleave="showImage = false"
        >
            <div class="w-9 h-9 flex-shrink-0">
                <ResourceIcon
                    :child="child"
                    :color="itemColor"
                    class="h-full w-full transform transition-transform group-hover:scale-110"
                />
            </div>
            <p
                class="text-sm font-medium text-gray-700 dark:text-zinc-200 truncate"
            >
                {{ decodeEntities(child.name) }}
            </p>
        </component>

        <div
            v-else
            class="space-x-3 col-span-6 flex items-center overflow-hidden"
        >
            <div class="w-9 h-9 flex-shrink-0">
                <ResourceIcon
                    :child="child"
                    :color="itemColor"
                    class="h-full w-full transform transition-transform group-hover:scale-110"
                />
            </div>
            <form @submit.prevent="submit" class="flex gap-2">
                <input
                    v-model="form.name"
                    class="text-sm font-medium dark:bg-slate-700 w-full rounded-lg focus:ring-0
        hover:border-violet-300 hover:border-1 focus:border-violet-500 focus:border-2
        text-gray-700 dark:text-zinc-200 truncate"
                >
                <button
                    @click="submit"
                    class="group flex items-center justify-center p-2.5
           bg-purple-600 hover:bg-purple-700 active:scale-95
           text-white rounded-full shadow-sm hover:shadow-md
           transition-all duration-200 ease-in-out
           focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                    aria-label="Envoyer"
                >
                    <ArrowTurnDownRightIcon
                        class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-0.5
                        group-hover:-translate-y-0.5"
                    />
                </button>
            </form>
        </div>

        <div
            class="text-xs text-gray-500 dark:text-zinc-400 col-span-1 text-center"
        >
            <span class="px-2 py-1 bg-gray-100 dark:bg-slate-800 rounded-full">
                {{
                    child.type === "folder"
                        ? "Dossier"
                        : child.type === "document"
                          ? "Document"
                          : "Fichier"
                }}
            </span>
        </div>

        <p class="text-xs text-gray-400 col-span-2 text-center">
            {{ child.created_at }}
        </p>

        <div
            class="text-xs col-span-2 flex flex-wrap justify-center gap-y-1 mx-auto max-w-full"
        >
            <span
                v-for="departement in child.departements"
                :key="departement"
                :style="{ backgroundColor: getDep(departement)?.color }"
                class="px-2 mx-0.5 rounded-xl font-semibold whitespace-nowrap"
                :class="
                    getDep(departement)?.color === '#ffffff'
                        ? 'text-black border border-gray-200'
                        : 'text-white'
                "
            >
                {{ getDep(departement)?.initials }}
            </span>
        </div>

        <div class="col-start-12 flex justify-center">
            <!--               DOWNLOAD               -->
            <a
                v-if="child.type == 'file'"
                :href="links.download"
                class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-700
                rounded-full transition-all group-hover:opacity-100"
                title="Télécharger"
            >
                <ArrowDownTrayIcon class="w-5 h-5 text-gray-400"/>
            </a>

            <!--            EDITOR ACTIONS            -->
            <EditorActionsWidget
                v-if="canEdit"
                :links="links"
                :is_archived="child.is_archived"
                @active-rename="handleRename"
            />
        </div>
    </div>
</template>

<style scoped></style>
