<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from '@inertiajs/vue3';
import { ArrowDownTrayIcon } from '@heroicons/vue/24/solid';
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
</script>

<template>
    <FilePreviewWidget :was-shown="wasShown" :show-image="showImage" :child="child" />

    <div class="group bg-white dark:bg-slate-800/30 hover:border-sky-200 p-4 rounded-2xl relative overflow-hidden border border-transparent transition-all duration-200 hover:overflow-visible"
         @mouseenter="handleMouseEnter" @mouseleave="showImage = false">

        <component v-if="!activeRename" :is="child.type !== 'file' ? Link : 'a'" :href="links.href" class="flex flex-col items-center">
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

        <div class="absolute top-1 left-1 flex flex-wrap gap-1 max-w-[90%] z-50 pointer-events-none">
            <ResourceBadges
                v-if="child.departements"
                :departement-ids="child.departements"
                mode="card"
            />
        </div>

        <div class="top-0 right-0 absolute flex">
            <a v-if="child.type == 'file'" :href="links.download" class="p-1 hover:bg-gray-100 rounded-full"><ArrowDownTrayIcon class="w-5 h-5 text-gray-400"/></a>
            <EditorActionsWidget v-if="canEdit" :links="links" :is_archived="child.is_archived" @active-rename="activeRename = $event" />
        </div>
    </div>
</template>
