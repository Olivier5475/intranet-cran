<script setup lang="ts">
// 1. Vue & Core
import { useForm } from '@inertiajs/vue3';

// 2. Composables & Utilitaires (Logique)
import { useDragAndDrop } from '@/Composables/useDragAndDrop';
import { decodeEntities } from '@/Composables/useDecodeModule';
import { useResource } from '@/Composables/useResource';

// 3. Routes
import route from '@/routes/editor/document';

// 4. Types
import { Document } from '@/types/document';
import DisplayContentWidget from '@/Components/Features/Document/DisplayContentWidget.vue';
import DisplayAttachments from '@/Components/Features/Document/DisplayAttachments.vue';
import EditorActionsWidget from '@/Components/Features/EditorActionsWidget.vue';

const props = defineProps<{
    document: Document;
}>();

// On défini si l'utilisateur à ou non les droits de modification sur le document

const { links, canEdit } = useResource(props.document);

// Logique du Drag & Drop pour les fichiers
const { isDragging } = useDragAndDrop({
    canDrop: canEdit.value,
    onDrop: (file) => {
        // Formulaire à envoyer au contrôleur Laravel
        const form = useForm({
            name: decodeEntities(props.document.name), // On récupère renvoie le meme titre
            content: props.document.content ?? '', // On récupère renvoie le meme contenu
            existing_attachments: props.document?.attachments ?? [], // On renvoie les piece jointe deja existante
            new_attachments: [] as File[], // On prépare l'envoie de la nouvelle pièce jointe
            departements: props.document?.departements ?? [], // On renvoie les meme departements
            // On renvoie la meme couleur et du blanc si on ne trouve pas de couleur
            color: props.document.color ?? '#ffffff',
            parent_id: props.document.folder_id ?? null, // on renvoie le même parent
        });

        // On met le fichier dans une liste pour rendre compatible avec le type attendu par le contrôleur Laravel
        form.new_attachments = Array.from(file);

        // on envoie le formulaire
        form.post(route.post.update.url(props.document.id), { method: 'patch' });
    },
});
</script>

<template>
    <div v-if="isDragging && canEdit" class="left-0 top-0 bg-sky-400/40 absolute z-50 flex h-full w-full">
        <div class="bg-sky-900/30 rounded-2xl border-sky-900 z-10 mx-auto my-auto flex h-[92%] w-[92%] border-4 border-dashed">
            <p class="text-sky-900 text-4xl font-black mx-auto my-auto">Déposez votre fichier</p>
        </div>
    </div>
    <div class="max-w-5xl mb-10 mx-auto overflow-hidden">
        <header class="group bg-slate-50 dark:bg-slate-800/50 p-6 rounded-t-2xl border-slate-200 dark:border-slate-700 relative border-b">
            <h1 class="text-4xl font-black text-slate-800 dark:text-white tracking-tight text-center first-letter:uppercase">
                {{ decodeEntities(document.name) }}
            </h1>

            <div
                class="right-4 p-3 bg-white dark:bg-slate-700 shadow-sm hover:shadow-md rounded-xl text-sky-600 dark:text-sky-400 border-slate-100 dark:border-slate-600 absolute top-1/2 -translate-y-1/2 border transition-all hover:scale-110"
            >
                <EditorActionsWidget
                    :links="links"
                    :is_archived="document.is_archived"
                />
            </div>
        </header>

        <DisplayContentWidget :content="document.content" />
        <DisplayAttachments :attachments="document.attachments" />
    </div>
</template>
