<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { EllipsisHorizontalIcon, ChevronRightIcon, ChevronDownIcon } from "@heroicons/vue/24/solid";
import { decodeEntities } from "@/Composables/useDecodeModule";
import folder_route from "@/routes/editor/folder";
import document_route from "@/routes/editor/document";
import file_route from "@/routes/editor/file";
import navigate from "@/routes/navigate";
import DeleteModal from "@/Components/UI/DeleteModal.vue";
import TreeViewItem from "./TreeViewItem.vue";
import { Folder } from "@/types/folder";

const props = defineProps<{ child: Folder; }>();
const page = usePage();

// --- ETAT DU SURVOL (UPDATE VISUEL) ---
const isDragOver = ref(false);

const currentFolderId = computed(() => {
    const folder = page.props.parents?.at(-1);
    return folder?.id ?? page.props.document?.folder_id ?? null;
});

const checkShouldExpand = (item: Folder, targetId: number | null): boolean => {
    if (!targetId) return false;
    if (item.id === targetId) return true;
    if (item.children) return item.children.some((sub) => checkShouldExpand(sub, targetId));
    return false;
};

const isExpanded = ref(checkShouldExpand(props.child, currentFolderId.value));
watch(currentFolderId, (newId) => { isExpanded.value = checkShouldExpand(props.child, newId); });

const isMenuExpend = ref(false);
const toggleMenu = ref(false);
const isActiveValidation = ref(false);

// PERMISSIONS
const user = page.props.auth.user;
const parentDpts = props.child.departements as number[];
const userDpts = user.departements as number[];
const compareDpts = parentDpts.filter((v) => userDpts.includes(v));
const canEdit = ref(user.role === "admin" || (user.role === "editeur" && (parentDpts.length === 0 || compareDpts.length > 0)));

// --- LOGIQUE DE DROP ---
const handleDragEnter = (e: DragEvent) => {
    e.preventDefault();
    if (e.dataTransfer?.types.includes('resource_id')) isDragOver.value = true;
};

const handleDragLeave = () => { isDragOver.value = false; };

const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    isDragOver.value = false;

    const resourceId = e.dataTransfer?.getData('resource_id');
    const resourceType = e.dataTransfer?.getData('resource_type');

    if (!resourceId || !resourceType) return;
    if (resourceType === 'folder' && resourceId === props.child.id.toString()) return;

    const form = useForm({ parent_id: props.child.id }); // On envoie le nouvel ID parent

    let url = "";
    if (resourceType == "folder") url = folder_route.post.update.url(resourceId);
    else if (resourceType == "document") url = document_route.post.update.url(resourceId);
    else url = file_route.post.update.url(resourceId);

    form.post(url, {
        preserveScroll: true,
        onSuccess: () => { isExpanded.value = true; }
    });
};
</script>

<template>
    <li :id="child.id.toString()" class="select-none">
        <div
            class="group p-1 rounded-md flex items-center transition-all duration-150"
            :class="{
                'bg-sky-50 dark:bg-sky-900/20': child.id === currentFolderId,
                'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/40 scale-[1.02] shadow-lg z-10': isDragOver,
                'hover:bg-slate-100 dark:hover:bg-sky-900/50': !isDragOver
            }"
            @dragover="handleDragOver"
            @dragenter="handleDragEnter"
            @dragleave="handleDragLeave"
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
                    :class="child.id === currentFolderId ? 'text-sky-600 dark:text-sky-400 font-bold' : 'text-gray-700 dark:text-gray-300'"
                >
                    {{ decodeEntities(child.name) }}
                </Link>
                <div class="relative flex items-center" @mouseover="isMenuExpend = true" @mouseleave="isMenuExpend = false">
                    <button v-if="canEdit" @click="toggleMenu = !toggleMenu" class="p-1 hover:bg-gray-200 dark:hover:bg-zinc-700 rounded-full">
                        <EllipsisHorizontalIcon class="h-5 w-5 text-gray-500" />
                    </button>
                    <div v-if="(isMenuExpend || toggleMenu) && canEdit" class="right-0 top-5 w-32 rounded-lg shadow-xl bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 py-1 absolute z-20">
                        <Link :href="folder_route.update.url(child.id)" class="px-4 py-2 text-xs hover:bg-gray-100 text-yellow-600 block">Modifier</Link>
                        <button @click="isActiveValidation = true" class="px-4 py-2 text-xs hover:bg-red-50 text-red-500 block w-full text-left">Archiver</button>
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
            <ul v-if="isExpanded" class="pl-4 ml-3 border-gray-200 dark:border-zinc-700 mt-1 space-y-1 border-l">
                <TreeViewItem v-for="subChild in child.children" :key="subChild.id" :child="subChild" />
                <li>
                    <Link v-if="canEdit" class="text-xs text-gray-400 hover:text-yellow-600 py-1 flex items-center transition-colors" :href="folder_route.create.url(child.id)">
                        <span class="mr-2 text-lg">+</span> Nouveau dossier
                    </Link>
                </li>
            </ul>
        </Transition>
    </li>

    <DeleteModal v-if="canEdit" :show="isActiveValidation" :delete-href="folder_route.archive.url(child.id)" :is-archived="false" @close="isActiveValidation = false" />
</template>
