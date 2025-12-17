<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
// Icônes
import { AdjustmentsHorizontalIcon, CalendarIcon, DocumentIcon } from '@heroicons/vue/24/outline';

const emit = defineEmits(['filters-updated']);

// --- L'état de nos filtres ---
defineProps<{
    departements : Array<{
        id: number;
        name: string;
        initials: string,
    }> | null
}>()

const filters = reactive({
    startDate: null,
    endDate: null,
    fileType: 'all',
    sortBy: 'newest',
    selectedDepartments: [] as number[] // La variable qui manquait pour vos checkboxes
});

const sortPossibilities = ref({
    pertinence : "Pertinence",
    newest :  "Le plus recent",
    oldest : "Le plus ancien",
    name : "Par nom",
});

const fileTypeList = [
    { id: 'all', name: 'Tous les types' },
    { id: 'word', name: 'Word' },
    { id: 'excel', name: 'Excel' },
    { id: 'image', name: 'Image' },
    { id: 'video', name: 'Vidéo' },
    { id: 'pdf', name: 'PDF' },
];

watch(filters, (newFilterValues) => {
    // Émet l'événement vers le parent avec les nouvelles valeurs
    emit('filters-updated', newFilterValues);
}, { deep: true }); // surveil l'intérieur de l'array departement

const isActive = ref(!window.matchMedia('(orientation: portrait)').matches);

const toggle = function() {
  isActive.value = !isActive.value;
};
</script>

<template>
    <section class="bg-white shadow rounded-lg overflow-hidden">
        <h2 @click=toggle class="h-[6vh] hover:cursor-pointer font-bold text-lg p-4 border-b flex items-center space-x-2 bg-slate-300 dark:bg-slate-800 dark:text-gray-300">
            <AdjustmentsHorizontalIcon class="h-6 w-6 text-gray-500 dark:text-gray-300" />
            <span>Filtres</span>
        </h2>

        <form v-if=isActive class="p-4 space-y-6 dark:bg-zinc-700 dark:text-gray-300">
<!--            DEPARTEMENTS FILTRE INPUT            -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Départements</label>
                <div class="space-y-2">
                    <div v-for="dep in departements" :key="dep.id" class="flex items-center">
                        <input
                            :id=dep.id.toString()
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:text-gray-300 dark:bg-gray-900"
                        >
                        <label :for=dep.id.toString() class="ml-3 text-sm text-gray-600 dark:text-gray-300">{{ dep.initials }}</label>
                    </div>
                </div>
            </div>
<!--            DEPARTEMENTS FILTRE INPUT            -->

<!--            DATE FILTRE INPUT            -->
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
                                v-model=filters.startDate
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
                                    v-model=filters.endDate
                                    class="block w-full rounded-md border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-900"
                                >
                            </div>
                    </div>
                </div>
            </div>
<!--            DATE FILTRE INPUT            -->

<!--            FILE TYPE FILTRE INPUT            -->
            <div>
                <label for="fileType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type de fichier</label>
                <div class="relative mt-1 rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <DocumentIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <select
                        id="fileType"
                        v-model=filters.fileType
                        class="block w-full rounded-md border-gray-300 pl-10 pr-8 focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-900"
                    >
                        <option v-for="type in fileTypeList" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                </div>
            </div>
<!--            FILE TYPE INPUT            -->

<!--            SORT INPUT            -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Trier par date</label>
                <div class="space-y-2">
                    <div v-for="(key, value) in sortPossibilities" :key=key class="flex items-center">
                        <input
                            :id=value
                            :value=value
                            v-model=filters.sortBy
                            type="radio"
                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900"
                        >
                        <label :for=value class="ml-3 text-sm text-gray-600 dark:text-gray-300">{{key}}</label>
                    </div>
                </div>
            </div>
<!--            SORT INPUT            -->

        </form>
    </section>
</template>
