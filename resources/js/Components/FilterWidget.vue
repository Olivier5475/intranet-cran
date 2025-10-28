<script setup lang="ts">
import { ref } from 'vue';
// Icônes
import { AdjustmentsHorizontalIcon, CalendarIcon, DocumentIcon } from '@heroicons/vue/24/outline';

// --- L'état de nos filtres ---
const departements = ref({
    cid: false,
    iset: false,
    sbs: false,
});
const startDate = ref(null);
const endDate = ref(null);
const fileType = ref('all');
const sortByDate = ref('newest'); // 'newest' ou 'oldest'

// --- Données pour les listes ---
const departementList = [
    { id: 'cid', name: 'Département CID' },
    { id: 'iset', name: 'Département ISET' },
    { id: 'sbs', name: 'Département SBS' },
];

const fileTypeList = [
    { id: 'all', name: 'Tous les types' },
    { id: 'word', name: 'Word' },
    { id: 'excel', name: 'Excel' },
    { id: 'image', name: 'Image' },
    { id: 'video', name: 'Vidéo' },
    { id: 'pdf', name: 'PDF' },
];
</script>

<template>
    <section class="bg-white shadow rounded-lg overflow-hidden">
        <h2 class="font-bold text-lg p-4 border-b flex items-center space-x-2 bg-slate-300 dark:bg-slate-800 dark:text-gray-300">
            <AdjustmentsHorizontalIcon class="h-6 w-6 text-gray-500 dark:text-gray-300" />
            <span>Filtres</span>
        </h2>

        <form class="p-4 space-y-6 dark:bg-zinc-700 dark:text-gray-300">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Départements</label>
                <div class="space-y-2">
                    <div v-for="dep in departementList" :key="dep.id" class="flex items-center">
                        <input
                            :id="dep.id"
                            v-model="departements[dep.id]"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:text-gray-300 dark:bg-gray-900"
                        >
                        <label :for="dep.id" class="ml-3 text-sm text-gray-600 dark:text-gray-300">{{ dep.name }}</label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Filtrer par date</label>
                <div class="space-y-2">
                    <div>
                        <label for="startDate" class="block text-xs font-semibold text-gray-500 dark:text-gray-300">Après le :</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <CalendarIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                type="date"
                                id="startDate"
                                v-model="startDate"
                                class="block w-full rounded-md border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-900"
                            >
                        </div>
                    </div>
                    <div>
                        <label for="endDate" class="block text-xs font-semibold text-gray-500 dark:text-gray-300">Avant le :</label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <CalendarIcon class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="date"
                                    id="endDate"
                                    v-model="endDate"
                                    class="block w-full rounded-md border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-900"
                                >
                            </div>
                    </div>
                </div>
            </div>

            <div>
                <label for="fileType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type de fichier</label>
                <div class="relative mt-1 rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <DocumentIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <select
                        id="fileType"
                        v-model="fileType"
                        class="block w-full rounded-md border-gray-300 pl-10 pr-8 focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-900"
                    >
                        <option v-for="type in fileTypeList" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Trier par date</label>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input
                            id="sortNewest"
                            value="newest"
                            v-model="sortByDate"
                            type="radio"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900"
                        >
                        <label for="sortNewest" class="ml-3 text-sm text-gray-600 dark:text-gray-300">Plus récent d'abord</label>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="sortOldest"
                            value="oldest"
                            v-model="sortByDate"
                            type="radio"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 "
                        >
                        <label for="sortOldest" class="ml-3 text-sm text-gray-600 dark:text-gray-300">Plus ancien d'abord</label>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <button
                    type="submit"
                    @click.prevent=""
                    class="w-full flex justify-center items-center gap-x-2 rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                >
                    <AdjustmentsHorizontalIcon class="h-5 w-5" />
                    Appliquer les filtres
                </button>
            </div>

        </form>
    </section>
</template>
