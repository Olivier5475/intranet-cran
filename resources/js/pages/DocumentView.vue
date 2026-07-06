<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

import { decodeEntities } from '@/Composables/useDecodeModule';
import { useResource } from '@/Composables/useResource';

import { CalendarDaysIcon, ArrowUturnLeftIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

import { Document } from '@/types/document';
import { Folder } from '@/types/folder';

import DisplayContentWidget from '@/Components/Features/Document/DisplayContentWidget.vue';
import DisplayAttachments from '@/Components/Features/Document/DisplayAttachments.vue';
import EditorActionsWidget from '@/Components/Features/EditorActionsWidget.vue';
import PageDragDropWidget from '@/Components/Features/PageDragDropWidget.vue';
import RepertoryWidget from '@/Components/Features/Navigation/RepertoryWidget.vue';

import editor from '@/routes/editor';

const props = defineProps<{
    document: Document;
    parents: Folder[];
    versionId: number
}>();

const { links, canEdit } = useResource(props.document);

// --- LOGIQUE DE STYLE DU BADGE DE DEADLINE ---
const deadlineStyle = computed(() => {
    // Si ce n'est pas un appel à projet ou qu'il n'y a pas de deadline, on ne renvoie rien
    if (props.document.type !== 'appelprojet' || !props.document.deadline) {
        return null;
    }

    const now = new Date();
    const deadline = new Date(props.document.deadline);

    const diffInMs = deadline.getTime() - now.getTime();
    const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

    // Retourne les classes de texte, de bordure et de fond selon l'urgence
    if (diffInDays <= 2) {
        return {
            text: 'text-red-600 dark:text-red-400',
            bg: 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-900/50',
            label: 'Urgent'
        };
    }
    if (diffInDays <= 7) {
        return {
            text: 'text-orange-600 dark:text-orange-400',
            bg: 'bg-orange-50 dark:bg-orange-950/30 border-orange-200 dark:border-orange-900/50',
            label: 'Moins d\'une semaine'
        };
    }
    if (diffInDays <= 14) {
        return {
            text: 'text-yellow-600 dark:text-yellow-400',
            bg: 'bg-yellow-50 dark:bg-yellow-950/30 border-yellow-200 dark:border-yellow-900/50',
            label: 'Moins de 2 semaines'
        };
    }
    return {
        text: 'text-green-600 dark:text-green-400',
        bg: 'bg-green-50 dark:bg-green-950/30 border-green-200 dark:border-green-900/50',
        label: 'En cours'
    };
});

// Formatage rapide de la date pour l'affichage (exemple : 31 mai 2026 à 23:59)
const formattedDeadline = computed(() => {
    if (!props.document.deadline) return '';
    return new Date(props.document.deadline).toLocaleString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
});

const restore = () => {
    if (confirm('Êtes-vous sûr de vouloir restaurer cette version ? La version actuelle sera archivée.')) {
        router.post(
            editor.model.post.restore.url(["documents", props.versionId]),
            {},
            {
                preserveState: false,
                onSuccess: () => alert('Fichier restauré avec succès !'),
            },
        );
    }
};
</script>

<template>
    <PageDragDropWidget :can-edit="canEdit" :document="document" />
    <div class="max-w-5xl mb-10 mx-auto overflow-hidden">
        <div class="bg-white dark:bg-slate-900 p-2 rounded-xl shadow-sm border-gray-100 dark:border-zinc-800 flex items-center justify-between border">
            <RepertoryWidget :parents="parents" />
        </div>
        <header class="group bg-slate-50 dark:bg-slate-800/50 p-6 rounded-t-2xl border-slate-200 dark:border-slate-700 relative border-b flex flex-col items-center justify-center">

            <h1 class="text-xl font-black text-slate-800 dark:text-white tracking-tight text-center first-letter:uppercase max-w-[80%]">
                {{ decodeEntities(document.name) }}
            </h1>

            <div
                v-if="deadlineStyle"
                class="mt-4 flex items-center space-x-2 px-3 py-1.5 rounded-full border text-xs font-semibold shadow-sm transition-all"
                :class="deadlineStyle.bg"
            >
                <CalendarDaysIcon class="w-4 h-4" :class="deadlineStyle.text" />
                <span :class="deadlineStyle.text">
                    Clôture le {{ formattedDeadline }} ({{ deadlineStyle.label }})
                </span>
            </div>

            <div
                v-if="versionId"
                class="absolute top-1/2 -translate-y-1/2 left-4 flex gap-4"
            >
                <Link
                    title="Retour à la liste des version"
                    :href="links.history"
                    class="block p-3 bg-white dark:bg-slate-700 shadow-sm hover:shadow-md
                rounded-xl text-sky-600 dark:text-sky-400 border-slate-100 dark:border-slate-600
                border transition-all hover:scale-110"
                >
                    <ArrowUturnLeftIcon class="w-5 h-5" />
                </Link>
                <button
                    title="Restauré cette version"
                    @click="restore"
                    class="p-3 bg-white dark:bg-slate-700 shadow-sm hover:shadow-md
                rounded-xl text-sky-600 dark:text-sky-400 border-slate-100 dark:border-slate-600
                border transition-all hover:scale-110"
                >
                    <ArrowPathIcon class="w-5 h-5" />
                </button>
            </div>
            <div
                v-else
                class="right-4 p-3 bg-white dark:bg-slate-700 shadow-sm hover:shadow-md
                rounded-xl text-sky-600 dark:text-sky-400 border-slate-100 dark:border-slate-600
                absolute top-1/2 -translate-y-1/2 border transition-all hover:scale-110"
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
