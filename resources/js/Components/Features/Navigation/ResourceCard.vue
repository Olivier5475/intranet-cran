<script setup lang="ts">
// 1. Vue & Core
import { ref } from "vue";
import { Link, useForm, usePage } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { ArrowDownTrayIcon } from '@heroicons/vue/24/solid';

// 3. Composables & Utilitaires
import { decodeEntities } from "@/Composables/useDecodeModule";
import { useResource } from "@/Composables/useResource";

// 4. Composants
import DeleteModal from "@/Components/UI/DeleteModal.vue";
import ResourceIcon from "@/Components/Features/Navigation/ResourceIcon.vue";
import EditorActionsWidget from '@/Components/Features/EditorActionsWidget.vue';

// 5. Types
import { Child } from "@/types/child";
import { Departement } from '@/types/departement';

// 6. Routes
import folder_route from '@/routes/editor/folder';
import document_route from '@/routes/editor/document';
import file_route from '@/routes/editor/file';
import FilePreviewWidget from '@/Components/Features/Navigation/FilePreviewWidget.vue';

const props = defineProps<{
    child: Child;
    folder_id: number;
}>();

// Utilisation du composable
const { links, itemColor, canEdit } = useResource(props.child);

// savoir si le Modal de validation de suppression est ouvert
const isActiveValidation = ref(false);

//
const page = usePage();
// Récupère les informations d'un département grâce à son id
const getDep = (id: number) => {
    return page.props.departements.find((d: Departement) => d.id === id);
};

const hoveredDepId = ref<number | null>(null);

const showImage = ref(false);
const wasShown = ref(false); // Garde en mémoire si on a déjà survolé

const handleMouseEnter = () => {
    showImage.value = true;
    wasShown.value = true; // On active le rendu définitif
};

const activeRename = ref(false);
const handleRename = (value: boolean) => {
    activeRename.value = value;
}
const route = props.child.type == "folder" // Si type = "folder"
    ? folder_route.post.update // route correspondante aux folder
    : props.child.type == "document" // Sinon, si route = "document"
        ? document_route.post.update // route correspondante aux documents
        : file_route.post.update; // Sinon, route correspondante aux "files"

const form = useForm({
    name: props.child.name,
});

const submit = () => {
    console.log(route.url(props.child.id))
    form.post(route.url(props.child.id));
}
</script>

<template>
    <FilePreviewWidget
        :was-shown="wasShown"
        :show-image="showImage"
        :child="child"
    />
    <div
        class="group bg-white dark:bg-slate-800/30 hover:border-sky-200
        dark:hover:border-sky-900 hover:shadow-md p-4 rounded-2xl relative
         overflow-hidden border border-transparent transition-all duration-200
         hover:overflow-visible"

        @mouseenter="handleMouseEnter"
        @mouseleave="showImage = false"
    >
        <component
            v-if="activeRename == false"
            :is="child.type !== 'file' ? Link : 'a'"
            :href="links.href"
            class="flex flex-col items-center"
        >

            <div
                class="w-16 h-16 mb-3 transition-transform
                duration-200 group-hover:scale-110"
            >
                <ResourceIcon
                    :child="child"
                    :color="itemColor"
                    class="h-full w-full"
                />
            </div>
            <span
                class="text-xs font-semibold text-gray-700 dark:text-zinc-200
                line-clamp-2 min-h-[2rem] max-w-full text-center break-words"
            >
                {{ decodeEntities(child.name) }}
            </span>
        </component>

        <div
            v-else
            class="flex flex-col items-center"
        >
            <div
                class="w-16 h-16 mb-3 transition-transform
                duration-200 group-hover:scale-110"
            >
                <ResourceIcon
                    :child="child"
                    :color="itemColor"
                    class="h-full w-full"
                />
            </div>
            <form @submit.prevent="submit" class="flex flex-col gap-1">
                <input
                    v-model="form.name"
                    class="text-xs font-medium dark:bg-slate-700 w-[130%] rounded-lg
                        focus:ring-0 hover:border-violet-300 hover:border-1
                        focus:border-violet-500 focus:border-2 text-gray-700
                        dark:text-zinc-200 truncate mx-auto -translate-x-[11%] break-words"
                >
                <button
                    @click="submit"
                    class="group flex items-center justify-center
                       bg-purple-600 hover:bg-purple-700 active:scale-95
                       text-white rounded-md shadow-sm hover:shadow-md text-xs
                       transition-all duration-200 ease-in-out focus:outline-none
                       focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 w-full"
                    aria-label="Envoyer"
                >
                    <span>Send</span>
                </button>
            </form>
        </div>
        <div
            class="absolute top-1 left-1 flex flex-wrap gap-1
            max-w-[90%] z-[50] pointer-events-none"
        >
            <div
                v-for="depId in child.departements"
                :key="depId"
                class="pointer-events-auto relative h-3 transition-all duration-300"
                :style="{
                    // Si moins de 2 departements, largueur de base en auto,
                    // sinon fixé à 0.75rem
                    width: (child.departements as number[]).length <= 2
                                                                    ? 'auto'
                                                                    : '0.75rem'
                }"
                @mouseenter="hoveredDepId = depId"
                @mouseleave="hoveredDepId = null"
            >
                <div
                    :style="{
                        backgroundColor: getDep(depId)?.color,
                        zIndex: hoveredDepId === depId ? 10 : 1
                    }"
                :class="[
                    // Si le nombre de departement est supérieur à 2,
                    (child.departements as number[]).length > 2
                        // on fixe les pastilles à un endroit
                        ? 'absolute top-0 left-0'
                        // Sinon, on les laisse se placer en ligne.
                        : 'relative',

                    // S\'il y a 2 departements, ou moins par-dessus le departement
                    ((child.departements as number[]).length <= 2 || hoveredDepId === depId)
                        // On fix la largeur à 'fit' et le padding à 1
                        ? 'w-fit px-1'
                        // Sinon, on fix largeur à 3
                        : 'w-3'
                ]"
                    class="h-3 rounded-full flex items-center justify-center
                    transition-all duration-300 shadow-sm whitespace-nowrap"
                >
                    <span
                        v-if="(child.departements as number[]).length <= 3
                                || hoveredDepId === depId"
                        class="text-[0.5rem] font-black uppercase text-white p-1"
                        :class="getDep(depId)?.color === '#ffffff' ? '!text-black' : ''"
                    >
                        {{ getDep(depId)?.initials }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-start-12 flex justify-center top-0 right-0 absolute">
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
    <DeleteModal
        :show="isActiveValidation"
        :delete-href="links.delete as string"
        @close="isActiveValidation = false"
    />
</template>
<style scoped></style>
