<script setup lang="ts">
// 1. Vue & Core
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { EllipsisHorizontalIcon } from "@heroicons/vue/24/solid";

// 3. Composables & Utilitaires
import { decodeEntities } from "@/Composables/useDecodeModule";
import { useResource } from "@/Composables/useResource";
import { isImageFile } from '@/Composables/useDocumentsTypeRegex';

// 4. Composants
import DeleteModal from "@/Components/DeleteModal.vue";
import ResourceIcon from "@/Components/ResourceIcon.vue";

// 5. Types
import { Child } from "@/types/child";
import { Departement } from "@/types/departement";
import { ArrowTurnDownRightIcon } from '@heroicons/vue/24/outline';

// 6. Routes
import folder_route from '@/routes/editor/folder';
import document_route from '@/routes/editor/document';
import file_route from '@/routes/editor/file';

const props = defineProps<{
    child: Child;
    folder_id: number;
}>();

// Utilisation du composable
const { links, itemColor, canEdit } = useResource(props);

// Menu = Menu dropdown d'action d'un dossier (avec bouton modifier et supprimer)
// pour savoir si le menu est étendu. sert pour quand on passe dessus
const isMenuExpend = ref(false);

// pour garder le menu ouvert même quand la souris n'est pas dessus
const toggleMenu = ref(false);

// savoir si le Modal de validation de suppression est ouvert
const isActiveValidation = ref(false);

//
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

const activeRename = ref(false);
let route;
if(props.child.type == "folder") {
    route = folder_route.post.update;
} else if (props.child.type == "document") {
    route = document_route.post.update;
} else {
    route = file_route.post.update;
}

const form = useForm({
    name: props.child.name,
    departements: props.child.departements
});

const submit = () => {
    form.post(route.url(props.child.id));
}

</script>

<template>
    <div v-if="wasShown && child.storage_path" v-show="showImage" class="absolute">
        <div class="fixed pointer-events-none z-[100] shadow-2xl border-4 bg-white text-black rounded-lg overflow-hidden top-20 right-20">
            <p class="text-center font-extrabold">Prévisualisation</p>

            <img
                v-if="child.mimetype && isImageFile(child.mimetype)"
                :src="'/storage/' + child.storage_path"
                class="min-w-[12rem] max-w-[18rem] h-auto object-cover"
                alt=""
            />

            <iframe
                v-else-if="child.mimetype && child.mimetype.includes('pdf')"
                :src="'/storage/' + child.storage_path"
                frameborder="0"
                width="350px" height="600px"
            ></iframe>
        </div>
    </div>
    <div
        class="group py-3 px-4 border-gray-100 dark:border-zinc-800
        hover:bg-sky-50/50 dark:hover:bg-slate-900/10 grid grid-cols-12
        items-center border-t transition-colors duration-150"

        @mouseenter="handleMouseEnter"
        @mouseleave="showImage = false"
    >
        <component
            v-if="activeRename == false"
            :is="child.type !== 'file' ? Link : 'a'"
            :href="links.href"
            class="space-x-3 col-span-6 flex items-center overflow-hidden"
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

        <div
            class="relative col-start-12 flex justify-end"
            v-if="canEdit"
            @mouseenter="isMenuExpend = true"
            @mouseleave="isMenuExpend = false"
        >
            <button
                @click="toggleMenu = !toggleMenu"
                class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-700
                rounded-full transition-all group-hover:opacity-100"
            >
                <EllipsisHorizontalIcon class="w-5 h-5 text-gray-400" />
            </button>

            <div
                v-if="toggleMenu || isMenuExpend"
                class="top-6 -right-7 w-32 bg-white dark:bg-zinc-900 shadow-xl rounded-xl border-gray-100 dark:border-zinc-700 absolute z-50 border"
            >
                <Link
                    v-if="links.history"
                    :href="links.history"
                    class="px-4 py-2 text-xs hover:bg-gray-40 dark:hover:bg-sky-900/50 text-sky-500 block"
                >
                    Historique
                </Link>
                <Link
                    :href="links.update"
                    class="px-4 py-2 text-xs hover:bg-gray-40 dark:hover:bg-yellow-900/50 text-yellow-600 block"
                >
                    Modifier
                </Link>

                <button
                    v-if="!child.is_archived"
                    @click="isActiveValidation = true"
                    class="px-4 py-2 text-xs hover:bg-red-400 text-left dark:hover:bg-red-900/50 text-red-600 block w-full"
                >
                    Archiver
                </button>
                <Link
                    v-if="child.is_archived"
                    :href="links.restore"
                    method="patch"
                    class="px-4 py-2 text-xs hover:bg-emerald-400 w-full text-left block dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-500"
                >
                    Restaurer
                </Link>
                <button
                    class="px-4 py-2 text-xs hover:bg-purple-400 text-left dark:hover:bg-purple-900/50 text-purple-600 block w-full"
                    @click="activeRename = true"
                >
                    Renommer
                </button>
            </div>
        </div>
    </div>
    <DeleteModal
        :show="isActiveValidation"
        :delete-href="links.delete as string"
        @close="isActiveValidation = false"
    />
</template>

<style scoped></style>
