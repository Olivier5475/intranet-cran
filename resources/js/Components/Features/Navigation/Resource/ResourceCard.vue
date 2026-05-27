<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from '@inertiajs/vue3';
import { ArrowDownTrayIcon } from '@heroicons/vue/24/solid';
import { ClipboardDocumentIcon, ClipboardDocumentCheckIcon } from "@heroicons/vue/24/outline";
import { decodeEntities } from "@/Composables/useDecodeModule";
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

const props = defineProps<{ child: Child; folder_id: number; }>();
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

// --- LOGIQUE DE COPIE ABSOLUE ---
const isCopied = ref(false);

const copyToClipboard = async () => {
    try {
        const absoluteUrl = `${window.location.origin}${links.value.href}`;
        await navigator.clipboard.writeText(absoluteUrl);

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
        class="group bg-white dark:bg-slate-800/30 hover:border-sky-200 p-4 rounded-2xl relative transition-all duration-200 hover:overflow-visible border border-transparent cursor-grab active:cursor-grabbing"
        draggable="true"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
        @mouseenter="handleMouseEnter"
        @mouseleave="showImage = false"
    >
        <component v-if="!activeRename" :is="child.type !== 'file' ? Link : 'a'" :href="links.href" :target="child.type == 'file' ? '_blank' : ''" class="flex flex-col items-center">
            <div class="w-16 h-16 mb-3 transition-transform duration-200 group-hover:scale-110">
                <ResourceIcon :child="child" :color="itemColor" class="h-full w-full" />
            </div>
            <span class="text-xs font-semibold text-gray-700 dark:text-zinc-200 line-clamp-2 min-h-[2rem] text-center break-all">
                {{ decodeEntities(child.name) }}
            </span>
        </component>

        <div v-else class="flex flex-col items-center">
            <div class="w-16 h-16 mb-3"><ResourceIcon :child="child" :color="itemColor" class="w-full h-full" /></div>
            <ResourceRenameForm v-model="child.name" :route-url="updateRoute" @success="activeRename = false" />
        </div>

        <div class="absolute top-2 left-2 flex flex-wrap gap-1 max-w-[65%] z-20 pointer-events-none">
            <ResourceBadges v-if="child.groupes" :groupe-ids="child.groupes" mode="card" />
        </div>

        <div class="top-2 right-2 absolute flex flex-col items-end space-y-1 z-30">
            <div class="flex items-center space-x-1">
                <a v-if="child.type == 'file'" :href="links.download" class="p-1 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-full transition-colors" title="Télécharger">
                    <ArrowDownTrayIcon class="w-4 h-4 text-gray-400 hover:text-sky-500"/>
                </a>

                <button
                    @click.stop="copyToClipboard"
                    class="p-1 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-full transition-colors"
                    :title="isCopied ? 'Lien copié !' : 'Copier le lien'"
                >
                    <component
                        :is="isCopied ? ClipboardDocumentCheckIcon : ClipboardDocumentIcon"
                        class="w-4 h-4"
                        :class="isCopied ? 'text-green-500' : 'text-gray-400 hover:text-sky-500'"
                    />
                </button>
            </div>

            <EditorActionsWidget
                v-if="canEdit"
                :links="links"
                :is_archived="child.is_archived"
                @active-rename="activeRename = $event"
                class="transform translate-x-1"
            />
        </div>
    </div>
</template>
