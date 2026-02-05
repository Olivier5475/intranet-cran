<script setup lang="ts">
import { computed } from 'vue';
import { isDocFile, isGifFile, isImageFile, isPresentationFile, isTabFile, isVideoFile } from '@/lib/documentsTypeRegex';
import {
    FolderIcon, DocumentIcon, DocumentTextIcon, TableCellsIcon,
    PresentationChartLineIcon, GifIcon, PhotoIcon, FilmIcon, PaperClipIcon
} from '@heroicons/vue/24/solid';

const props = defineProps<{
    child: { type: string; mimetype?: string; };
    color?: string;
}>();

// On applique la couleur dynamiquement via style
const styleObject = computed(() => props.color ? { color: props.color } : {});
</script>

<template>
    <FolderIcon v-if="child.type === 'folder'" :style="styleObject" />

    <DocumentIcon v-else-if="child.type === 'document'" :style="styleObject" />

    <template v-else>
        <PhotoIcon v-if="child.mimetype && isImageFile(child.mimetype)" />
        <FilmIcon v-else-if="child.mimetype && isVideoFile(child.mimetype)" />
        <GifIcon v-else-if="child.mimetype && isGifFile(child.mimetype)" />
        <PresentationChartLineIcon v-else-if="child.mimetype && isPresentationFile(child.mimetype)" class="text-red-500" />
        <DocumentTextIcon v-else-if="child.mimetype && isDocFile(child.mimetype)" class="text-sky-500" />
        <DocumentTextIcon v-else-if="child.mimetype && child.mimetype.includes('pdf')" class="text-red-500" />
        <TableCellsIcon v-else-if="child.mimetype && isTabFile(child.mimetype)" class="text-green-500" />
        <PaperClipIcon v-else />
    </template>
</template>
