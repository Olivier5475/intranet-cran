<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { useDragAndDrop } from '@/Composables/useDragAndDrop';
import { decodeEntities } from '@/Composables/useDecodeModule';

import { Folder } from '@/types/folder';
import { Document } from '@/types/document';

import document_route from '@/routes/editor/document';
import file_route from "@/routes/editor/file";

const props = defineProps<{
    navigation?: {
        lastParent?: Folder
    }
    document? : Document;
    canEdit: boolean;
}>();

// Logique du Drag & Drop pour les fichiers
const { isDragging } = useDragAndDrop({
    canDrop: props.canEdit,
    onDrop: (file) => {
        if(props.navigation) {
            useForm({
                files: Array.from(file),
                departements: props.navigation.lastParent?.departements ?? [],
                parent_id: props.navigation.lastParent?.id ?? null
            }).post(file_route.post.create.url());
        } else if(props.document) {
            useForm({
                name: decodeEntities(props.document.name), // On récupère renvoie le meme titre
                content: props.document.content ?? '', // On récupère renvoie le meme contenu
                existing_attachments: props.document?.attachments ?? [], // On renvoie les piece jointe deja existante
                new_attachments: Array.from(file) as File[], // On prépare l'envoie de la nouvelle pièce jointe
                departements: props.document?.departements ?? [], // On renvoie les meme departements
                // On renvoie la meme couleur et du blanc si on ne trouve pas de couleur
                color: props.document.color ?? '#ffffff',
                parent_id: props.document.folder_id ?? null, // on renvoie le même parent
            }).post(document_route.post.update.url(props.document.id),
                { method: 'patch' }
            );
        }
    },
});
</script>

<template>
    <div
        v-if="isDragging"
        class="left-0 top-0 bg-sky-400/40 fixed z-50 flex h-full w-full"
    >
        <div
            class="bg-sky-900/30 rounded-2xl border-sky-900 z-10 mx-auto my-auto
            flex h-[92%] w-[92%] border-4 border-dashed"
        >
            <p class="text-sky-900 text-4xl font-black mx-auto my-auto">
                Déposez votre fichier
            </p>
        </div>
    </div>

</template>

<style scoped>

</style>
