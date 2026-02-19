<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import editor from '@/routes/editor';
import download from '@/routes/download';
interface File {
    id: number;
    name: string;
    storage_path: string;
    mimetype: string;
    _relations: {
        departements: number[];
    };
}
interface Version {
    id: number;
    versionable_id: number;
    payload: File;
}
defineProps<{
    versions: Version[];
}>();

const restore = (versionId: number) => {
    if (confirm('Êtes-vous sûr de vouloir restaurer cette version ? La version actuelle sera archivée.')) {
        router.post(
            editor.model.post.restore.url(["files", versionId]),
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
                            {{ version.payload.name }}
                        </td>
                        <td class="p-4 text-sm uppercase">
                            {{ version.payload.mimetype.split('/')[1] }}
                        </td>
                        <td class="p-4">
                            <div class="gap-1 flex">
                                <Link
                                    :href="download.file.version.url(version.id)"
                                    download
                                    class="px-3 py-1 mx-auto dark:bg-slate-700 rounded-md dark:hover:bg-slate-500"
                                >
                                    Télécharger
                                </Link>
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
