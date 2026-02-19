<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import editor from '@/routes/editor';
import download from '@/routes/download';

interface Attachment {
    id: string;
    name: string;
    storage_path: string;
}
interface DocumentPayload {
    id: number;
    title: string;
    content: string;
    color: string;
    _relations: {
        departements: number[];
        attachments: Array<Attachment>;
    };
}

interface Version {
    id: number;
    versionable_id: number;
    versionable_type: string;
    payload: DocumentPayload;
}
defineProps<{
    versions: Version[];
}>();

const restore = (versionId: number) => {
    if (confirm('Êtes-vous sûr de vouloir restaurer cette version ? La version actuelle sera archivée.')) {
        router.post(
            editor.model.post.restore.url(["documents", versionId]),
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
    <div class="p-6 rounded-lg shadow">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Historique des versions</h2>
            <span class="px-3 py-1 text-sm bg-blue-100 dark:bg-slate-800 text-blue-700 rounded-full">
                {{ versions.length }} version(s) archivée(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                <tr class="font-medium border-b">
                    <th class="p-4">Nom du fichier au moment de l'archive</th>
                    <th class="p-4">Type</th>
                    <th class="p-4 text-center">Fichier</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="version in versions" :key="version.id" class="border-b transition">
                    <td class="p-4 font-medium">
                        {{ version.payload.title }}
                    </td>
                    <td class="p-4 text-sm uppercase">
                        DOC
                    </td>
                    <td class="p-4">
                        <div class="gap-1 flex">
                            <div v-for="attachment in version.payload._relations.attachments" :key="attachment.id">
                                <a
                                    :href="download.attachment.url(attachment.id)"
                                    download
                                    class="px-3 py-1 mx-auto max-w-[10rem] max-h-8 overflow-hidden block dark:bg-slate-700 rounded-md hover:max-h-24 dark:hover:bg-slate-500"
                                >
                                    {{ attachment.name}}
                                </a>
                            </div>
                        </div>
                    </td>
                    <td class="p-4 text-right">
                        <button
                            @click="restore(version.id)"
                            class="px-4 py-2 text-white text-sm font-medium bg-indigo-600 hover:bg-indigo-700 dark:bg-slate-700 dark:hover:bg-slate-500 rounded-md inline-flex items-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"
                                />
                            </svg>
                            Restaurer
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div v-if="versions.length === 0" class="py-12 text-gray-500 text-center italic">Aucune version archivée pour ce fichier.</div>
    </div>
</template>
