<script setup lang="ts">
import { ref, watch } from 'vue';
import { Child } from '@/types/child';
import { isGifFile, isImageFile, isVideoFile, isTextFile, isArchiveFile } from '@/Composables/useDocumentsTypeRegex';
import download from '@/routes/download';
import JSZip from 'jszip'; // Import pour les ZIP

const props = defineProps<{
    wasShown: boolean
    showImage: boolean
    child: Child
}>()

const textContent = ref<string>('');
const zipFiles = ref<string[]>([]);
const isLoading = ref(false);

// Détecter si c'est un ZIP

watch(() => props.child.id, async () => {
    if (!props.child.storage_path) return;

    const url = download.file.preview.url(props.child.id);
    const mime = props.child.mimetype || '';

    // Reset
    textContent.value = '';
    zipFiles.value = [];

    // Cas 1 : Fichier texte
    if (isTextFile(mime)) {
        isLoading.value = true;
        try {
            const res = await fetch(url);
            textContent.value = await res.text();
        } finally { isLoading.value = false; }
    }

    // Cas 2 : Archive ZIP
    else if (isArchiveFile(mime)) {
        isLoading.value = true;
        try {
            const res = await fetch(url);
            const blob = await res.blob();
            const zip = await JSZip.loadAsync(blob);
            zipFiles.value = Object.keys(zip.files);
        } catch (e) {
            zipFiles.value = ['Impossible de lire l\'archive'];
            console.log(e)
        } finally { isLoading.value = false; }
    }
}, { immediate: true });
</script>

<template>
    <div v-if="wasShown && child.storage_path" v-show="showImage" class="absolute">
        <div class="fixed pointer-events-none z-[100] shadow-2xl border-4 bg-white text-black rounded-lg overflow-hidden top-20 right-20 min-w-[15rem]">
            <p class="text-center font-extrabold border-b bg-gray-50 py-1">Prévisualisation</p>

            <img v-if="child.mimetype && (isImageFile(child.mimetype) || isGifFile(child.mimetype))"
                 :src="download.file.preview.url(child.id)"
                 class="max-w-[18rem] h-auto object-cover"
                 :alt="child.name"/>

            <video v-else-if="child.mimetype && isVideoFile(child.mimetype)"
                   :src="download.file.preview.url(child.id)"
                   class="max-w-[25rem] h-auto" autoplay loop muted />

            <iframe v-else-if="child.mimetype?.includes('pdf')"
                    :src="download.file.preview.url(child.id)"
                    width="350px" height="500px" />

            <div v-else-if="child.mimetype && isTextFile(child.mimetype)"
                 class="p-2 bg-gray-900 text-green-400 font-mono text-[10px] max-h-[400px] max-w-[20rem]">
                <div v-if="isLoading">Chargement...</div>
                <pre v-else class="whitespace-pre-wrap break-words">{{ textContent }}</pre>
            </div>

            <div v-else-if="child.mimetype && isArchiveFile(child.mimetype)" class="p-3 max-h-[300px] overflow-auto">
                <p class="text-xs font-bold text-gray-500 mb-2">Contenu de l'archive :</p>
                <ul class="text-[10px] space-y-1">
                    <li v-for="file in zipFiles" :key="file" class="truncate">📦 {{ file }}</li>
                </ul>
            </div>

            <div v-else class="p-10 text-center">
                <p>Aperçu non disponible</p>
            </div>
        </div>
    </div>
</template>
