<script setup lang="ts">
//REGEX
import { isDocFile, isGifFile, isImageFile, isPresentationFile, isTabFile, isVideoFile } from '@/lib/documentsTypeRegex';

//ICONS
import {
    FolderIcon,
    DocumentIcon,
    DocumentTextIcon,
    TableCellsIcon,
    PresentationChartLineIcon,
    GifIcon,
    PhotoIcon,
    FilmIcon,
    PaperClipIcon,
} from '@heroicons/vue/24/solid';

import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    child: {
        id: number;
        name: string;
        type: string;
        mimetype?: string,
        color?: string;
    };
    folder_id: number;
}>();

const color = computed(() => {
    return props.child.color;
});

let href;
if (props.child.type === 'file') {
    href = `/download/file/${props.child.id}`;
} else if (props.child.type === 'folder') {
    href = `/navigation/${props.child.id}`;
} else {
    href = `/navigation/${props.folder_id}/documents/${props.child.id}`;
}
</script>

<template>
    <div :class="`hover:bg-blue-400 hover:bg-opacity-50 w-[12.5%] transition-all duration-150`">
        <Link v-if="child.type !== 'file'" :href="href">
            <FolderIcon v-if="child.type === 'folder'" class="color mx-auto aspect-square w-10/12" />
            <DocumentIcon v-else-if="child.type === 'document'" class="color mx-auto aspect-square w-10/12" />
            <p class="text-black dark:text-gray-200 overflow-hidden mx-auto w-full text-center">{{ child.name }}</p>
        </Link>
        <a v-else :href="href">
            <PhotoIcon v-if="child.mimetype && isImageFile(child.mimetype)" class="mx-auto aspect-square w-10/12" />
            <FilmIcon v-else-if="child.mimetype && isVideoFile(child.mimetype)" class="mx-auto aspect-square w-10/12" />
            <GifIcon v-else-if="child.mimetype && isGifFile(child.mimetype)" class="mx-auto aspect-square w-10/12" />
            <PresentationChartLineIcon v-else-if="child.mimetype && isPresentationFile(child.mimetype)" class="text-red-500 mx-auto aspect-square w-10/12" />
            <DocumentTextIcon v-else-if="child.mimetype && isDocFile(child.mimetype)" class="text-sky-500 mx-auto aspect-square w-10/12" />
            <DocumentTextIcon v-else-if="child.mimetype && child.mimetype.includes('pdf')" class="text-red-500 mx-auto aspect-square w-10/12" />
            <TableCellsIcon v-else-if="child.mimetype && isTabFile(child.mimetype)" class="text-green-500 mx-auto aspect-square w-10/12" />
            <PaperClipIcon v-else class="mx-auto aspect-square w-10/12" />
            <p class="overflow-hidden mx-auto w-full text-center">{{ child.name }}</p>
        </a>
    </div>
</template>

<style scoped>
.color {
    color: v-bind(color);
}
</style>
