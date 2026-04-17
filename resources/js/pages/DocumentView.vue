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
import PageDragDropWidget from '@/Components/Features/PageDragDropWidget.vue';

const props = defineProps<{
    document: Document;
}>();

// On défini si l'utilisateur à ou non les droits de modification sur le document

const { links, canEdit } = useResource(props.document);

</script>

<template>
    <PageDragDropWidget :can-edit="canEdit" :document="document" />
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
