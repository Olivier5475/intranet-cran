<script setup lang="ts">
// 1. Vue & Core
import { ref, computed, watch } from "vue";
import { Link, useForm, usePage } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import {
    EllipsisHorizontalIcon,
    ChevronRightIcon,
    ChevronDownIcon,
} from "@heroicons/vue/24/solid";

// 3. Composables, Routes & Utilitaires
import { decodeEntities } from "@/Composables/useDecodeModule";
import folder_route from "@/routes/editor/folder";
import document_route from "@/routes/editor/document";
import file_route from "@/routes/editor/file";
import navigate from "@/routes/navigate";

// 4. Composants
import DeleteModal from "@/Components/UI/DeleteModal.vue";
import TreeViewItem from "./TreeViewItem.vue";

// 5. Types
import { Folder } from "@/types/folder";

const props = defineProps<{
    child: Folder;
}>();

const page = usePage();

/**
 * Extraire le folder_id de l'URL (/navigation/f/{id})
 */
const currentFolderId = computed(() => {
    const folder = page.props.parents?.at(-1);
    return folder?.id ?? page.props.document?.folder_id ?? null;
});

/**
 * Fonction récursive pour vérifier si ce nœud ou un descendant est actif
 * @param item
 * @param targetId
 */
const checkShouldExpand = (item: Folder, targetId: number | null): boolean => {
    if (!targetId) return false;
    if (item.id === targetId) return true;
    if (item.children) {
        return item.children.some((sub) => checkShouldExpand(sub, targetId));
    }
    return false;
};

// État initial : true si le dossier actuel (ou un enfant) est détecté
const isExpanded = ref(checkShouldExpand(props.child, currentFolderId.value));

watch(currentFolderId, (newId) => {
    isExpanded.value = checkShouldExpand(props.child, newId);
});

// Menu = Menu dropdown d'action d'un dossier (avec bouton modifier et supprimer)
// pour savoir si le menu est étendu. sert pour quand on passe dessus
const isMenuExpend = ref(false);

// pour garder le menu ouvert même quand la souris n'est pas dessus
const toggleMenu = ref(false);

// savoir si le Modal de validation de suppression est ouvert
const isActiveValidation = ref(false);

// On récupère les départements de la page
const parentDpts = props.child.departements as number[];

// On récupère l'utilisateur
const user = page.props.auth.user;

// On récupère les départements de l'utilisateur
const userDpts = user.departements as number[];

// On récupère les départements en commun
const compareParentAndUser = parentDpts.filter((value) =>
    userDpts.includes(value),
);
const canEdit = ref(
    user.role === "admin" || // Si l'utilisateur est un admin, il peut créer dans le dossier ou le modifier.
        // Si c'est un editeur et qu'il a des roles en commun avec la page, il peut créer dans le dossier ou le modifier.
        (user.role === "editeur" &&
            (parentDpts.length === 0 || compareParentAndUser.length > 0)),
);

const handleDrop = (e: DragEvent) => {
    e.preventDefault();

    // On récupère les infos stockées lors du dragstart (étape 2)
    const resourceId = e.dataTransfer?.getData('resource_id');
    const resourceType = e.dataTransfer?.getData('resource_type');

    if (!resourceId || !resourceType) return;

    // On évite de déplacer un dossier dans lui-même
    if (resourceType === 'folder' && resourceId === props.child.id.toString()) {
        return;
    }

    // Envoi de la requête Inertia
    const form = useForm({
        parent_id: props.child.id, // Le nouvel ID parent est celui du dossier sur lequel on drop
    });

    form.post(
        (resourceType == "folder")
            ? folder_route.post.update.url(resourceId)
            : (resourceType == "document")
                ? document_route.post.update.url(resourceId)
                : file_route.post.update.url(resourceId)
        , {
        preserveScroll: true,
        onSuccess: () => {
            // Optionnel : ouvrir le dossier pour montrer que l'élément est dedans
            isExpanded.value = true;
        }
    });
};

const handleDragOver = (e: DragEvent) => {
    e.preventDefault(); // Indispensable pour autoriser le drop
    if (e.dataTransfer) {
        e.dataTransfer.dropEffect = 'move';
    }
};
</script>

<template>
    <li :id="child.id.toString()" class="select-none">
        <div
            class="group p-1 rounded-md hover:bg-slate-100 dark:hover:bg-sky-900/50 flex items-center transition-colors duration-150"
            :class="{
                'bg-sky-50 dark:bg-sky-900/20': child.id === currentFolderId,
            }"

            @dragover="handleDragOver"
            @drop="handleDrop"
        >
            <div @click="isExpanded = !isExpanded" class="p-1 cursor-pointer">
                <component
                    v-if="child.children.length > 0 || canEdit"
                    :is="isExpanded ? ChevronDownIcon : ChevronRightIcon"
                    class="w-4 h-4 text-gray-500"
                />
            </div>

            <div class="ml-1 flex flex-1 items-center justify-between">
                <Link
                    :href="navigate.folder.url(child.id)"
                    class="text-sm truncate"
                    :class="
                        child.id === currentFolderId
                            ? 'text-sky-600 dark:text-sky-400 font-bold'
                            : 'text-gray-700 dark:text-gray-300'
                    "
                >
                    {{ decodeEntities(child.name) }}
                </Link>
                <div
                    class="relative flex items-center transition-opacity group-hover:opacity-100"
                    @mouseover="isMenuExpend = true"
                    @mouseleave="isMenuExpend = false"
                >
                    <button
                        v-if="canEdit"
                        @click="toggleMenu = !toggleMenu"
                        class="p-1 hover:bg-gray-200 dark:hover:bg-zinc-700 rounded-full"
                    >
                        <EllipsisHorizontalIcon class="h-5 w-5 text-gray-500" />
                    </button>

                    <div
                        v-if="(isMenuExpend || toggleMenu) && canEdit"
                        class="right-0 top-5 w-32 rounded-lg shadow-xl bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-700 py-1 absolute z-20 border opacity-25 group-hover:opacity-100"
                    >
                        <Link
                            :href="folder_route.update.url(child.id)"
                            class="px-4 py-2 text-xs hover:bg-gray-100 dark:hover:bg-yellow-900/50 text-yellow-600 block"
                        >
                            Modifier
                        </Link>
                        <button
                            @click="isActiveValidation = true"
                            class="px-4 py-2 text-xs hover:bg-red-50 dark:hover:bg-red-900/50 text-red-500 block w-full text-left"
                        >
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Transition
            enter-active-class="transition-all duration-300 ease-in-out"
            enter-from-class="max-h-0 opacity-0 overflow-hidden"
            enter-to-class="max-h-[1000px] opacity-100"
            leave-active-class="transition-all duration-200 ease-in-out"
            leave-from-class="max-h-[1000px] opacity-100"
            leave-to-class="max-h-0 opacity-0 overflow-hidden"
        >
            <ul
                v-if="isExpanded"
                class="pl-4 ml-3 border-gray-200 dark:border-zinc-700 mt-1 space-y-1 border-l"
            >
                <TreeViewItem
                    v-for="subChild in child.children"
                    :key="subChild.id"
                    :child="subChild"
                />

                <li>
                    <Link
                        v-if="canEdit"
                        class="text-xs text-gray-400 hover:text-yellow-600 py-1 flex items-center transition-colors"
                        :href="folder_route.create.url(child.id)"
                    >
                        <span class="mr-2 text-lg">+</span>
                        Nouveau dossier
                    </Link>
                </li>
            </ul>
        </Transition>
    </li>

    <DeleteModal
        v-if="canEdit"
        :show="isActiveValidation"
        :delete-href="folder_route.archive.url(child.id)"
        @close="isActiveValidation = false"
    />
</template>
