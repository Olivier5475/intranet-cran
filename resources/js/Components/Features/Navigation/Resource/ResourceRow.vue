<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowDownTrayIcon } from "@heroicons/vue/24/solid";
import { ClipboardDocumentIcon, ClipboardDocumentCheckIcon } from "@heroicons/vue/24/outline";
import { decodeEntities, formatForDisplay } from '@/Composables/useDecodeModule';
import { useResource } from "@/Composables/useResource";
import ResourceIcon from "@/Components/Features/Navigation/Resource/ResourceIcon.vue";
import EditorActionsWidget from '@/Components/Features/EditorActionsWidget.vue';
import FilePreviewWidget from '@/Components/Features/Navigation/FilePreviewWidget.vue';
import ResourceRenameForm from '@/Components/Features/Navigation/Resource/ResourceRenameForm.vue';
import { Child } from "@/types/child";
import folder_route from '@/routes/editor/folder';
import document_route from '@/routes/editor/document';
import file_route from '@/routes/editor/file';
import ResourceBadges from '@/Components/Features/Navigation/Resource/ResourceBadges.vue';

const props = defineProps<{
    child: Child;
    folder_id: number;
    date_mode: 'create' | 'update' | 'deadline'
}>();
const { links, itemColor, canEdit } = useResource(props.child);

const showImage = ref(false);
const wasShown = ref(false);
const activeRename = ref(false);

const updateRoute = computed(() => {
    const routes = { folder: folder_route, document: document_route, file: file_route };
    return routes[props.child.type as keyof typeof routes].post.update.url(props.child.id);
});

const handleMouseEnter = () => { showImage.value = wasShown.value = true; };

// --- LOGIQUE DE DRAG ---
const handleDragStart = (e: DragEvent) => {
    if (e.dataTransfer) {
        e.dataTransfer.setData('resource_id', props.child.id.toString());
        e.dataTransfer.setData('resource_type', props.child.type);
        e.dataTransfer.effectAllowed = 'move';
        if (e.target instanceof HTMLElement) e.target.classList.add('opacity-40');
    }
};

const handleDragEnd = (e: DragEvent) => {
    if (e.target instanceof HTMLElement) e.target.classList.remove('opacity-40');
};

const isCopied = ref(false);

const copyToClipboard = async () => {
    try {
        // Utilisation de l'API native du navigateur
        await navigator.clipboard.writeText(`${window.location.origin}${links.value.href}`);

        // Petit feedback visuel de 2 secondes
        isCopied.value = true;
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Erreur lors de la copie : ', err);
    }
};

</script>

<template>
    <FilePreviewWidget :was-shown="wasShown" :show-image="showImage" :child="child" />
    <div
        class="group py-3 px-4 border-gray-100 dark:border-zinc-800 hover:bg-sky-50/50 dark:hover:bg-slate-900/10 grid grid-cols-12 items-center border-t transition-colors duration-150 cursor-grab active:cursor-grabbing"
        draggable="true"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
    >
        <component v-if="!activeRename" :is="child.type !== 'file' ? Link : 'a'" :href="links.href" :target="child.type == 'file' ? '_blank' : ''"
                   class="space-x-3 col-span-5 flex items-center overflow-hidden" @mouseenter="handleMouseEnter" @mouseleave="showImage = false">
            <div class="w-9 h-9 flex-shrink-0">
                <ResourceIcon
                    :child="child"
                    :color="itemColor"
                    :solid="true"
                    class="h-full w-full transform transition-transform group-hover:scale-110"
                />
            </div>
            <p class="text-sm font-medium text-gray-700 dark:text-zinc-200 break-words">{{ decodeEntities(child.name) }}</p>
        </component>

        <div v-else class="space-x-3 col-span-6 flex items-center">
            <div class="w-9 h-9 flex-shrink-0"><ResourceIcon :child="child" :color="itemColor" class="w-full h-full" /></div>
            <ResourceRenameForm v-model="child.name" :route-url="updateRoute" is-row @success="activeRename = false" />
        </div>

        <div class="text-xs text-gray-500 dark:text-zinc-400 col-span-1 text-center">
            <span class="px-2 py-1 bg-gray-100 dark:bg-slate-800 rounded-full">
                {{ child.type === "folder" ? "Dossier" : child.type === "document" ? "Document" : "Fichier" }}
            </span>
        </div>

        <p v-if="date_mode == 'create'" class="text-xs text-gray-400 col-span-3 text-center">{{ child.created_at }}</p>

        <!--        AFFICHAGE DATE DERNIÈRE MODIFICATION          -->
        <p v-else-if="date_mode == 'update'" class="text-xs text-gray-400 col-span-3 text-center">
            <span v-if="child.updated_at">
                {{ child.updated_at }}
            </span>
            <span v-else>
                -
            </span>
        </p>

        <!--        AFFICHAGE DEADLINE          -->
        <p v-else-if="date_mode == 'deadline'" class="text-xs text-gray-400 col-span-3 text-center">
            <span v-if="child.deadline">
                {{ formatForDisplay(child.deadline) }}
            </span>
            <span v-else>
                -
            </span>
        </p>

        <ResourceBadges v-if="child.groupes" :groupe-ids="child.groupes" mode="row" class="col-span-2" />

        <div class="col-start-12 flex justify-center">
            <span class="p-1 hover:bg-gray-100 rounded-full" title="Copier le lien">
            <component
                :is="isCopied ? ClipboardDocumentCheckIcon : ClipboardDocumentIcon"
                @click="copyToClipboard"
                class="w-5 h-5"
                :class="isCopied ? 'text-green-500' : 'text-gray-400 hover:text-sky-500'"
            />
            </span>
            <a v-if="child.type == 'file'" :href="links.download" class="p-1 hover:bg-gray-100 rounded-full" title="Télécharger">
                <ArrowDownTrayIcon class="w-5 h-5 text-gray-400"/>
            </a>
            <EditorActionsWidget v-if="canEdit" :links="links" :is_archived="child.is_archived" @active-rename="activeRename = $event" />
        </div>
    </div>
</template>
