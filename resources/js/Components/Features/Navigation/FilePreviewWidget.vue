<script setup lang="ts">
import { Child } from '@/types/child';
import { isImageFile } from '@/Composables/useDocumentsTypeRegex';
import download from '@/routes/download';

defineProps<{
    wasShown: boolean
    showImage: boolean
    child: Child
}>()
</script>

<template>
    <div v-if="wasShown && child.storage_path" v-show="showImage" class="absolute">
        <div
            class="fixed pointer-events-none z-[100] shadow-2xl border-4 bg-white
            text-black rounded-lg overflow-hidden top-20 right-20"
        >
            <p class="text-center font-extrabold">Prévisualisation</p>

            <img
                v-if="child.mimetype && isImageFile(child.mimetype)"
                :src="download.file.preview.url(child.id)"
                class="min-w-[12rem] max-w-[18rem] h-auto object-cover"
                :alt="child.name"
            />

            <iframe
                v-else-if="child.mimetype && child.mimetype.includes('pdf')"
                :src="download.file.preview.url(child.id)"
                frameborder="0"
                width="350px" height="600px"
            ></iframe>
        </div>
    </div>
</template>

<style scoped>

</style>
