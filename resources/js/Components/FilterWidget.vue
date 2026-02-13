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
    <section class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-slate-800 dark:bg-slate-900 transition-all duration-300">
        <div
            @click="toggle"
            class="h-14 cursor-pointer font-bold text-gray-700 dark:text-zinc-300 p-4 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors border-b border-gray-100 dark:border-slate-800"
        >
            <div class="flex items-center space-x-3">
                <AdjustmentsHorizontalIcon class="h-5 w-5 text-sky-500" />
                <span class="text-sm uppercase tracking-wider">Filtres</span>
            </div>
            <div :class="['transition-transform duration-300', isActive ? 'rotate-180' : '']">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-[1000px] opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="max-h-[1000px] opacity-100"
            leave-to-class="max-h-0 opacity-0"
        >
            <form v-if="isActive" class="p-5 space-y-8 dark:bg-sky-900/5 overflow-hidden">

                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-zinc-500 uppercase tracking-[0.2em] mb-3 ml-1">Départements</label>
                    <div class="grid grid-cols-1 gap-2">
                        <label
                            v-for="dep in departements"
                            :key="dep.id"
                            class="group flex items-center p-2 rounded-xl cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-slate-800/40"
                        >
                            <div class="relative flex items-center">
                                <input
                                    :id="dep.id.toString()"
                                    type="checkbox"
                                    v-model="filters.selectedDepartments"
                                    :value="dep.id"
                                    class="h-5 w-5 rounded-lg border-gray-300 dark:border-slate-700 text-sky-500 focus:ring-sky-500/20 dark:bg-slate-900 transition-all cursor-pointer"
                                >
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-600 dark:text-zinc-300 group-hover:text-sky-500 transition-colors">
                                {{ dep.name }} <span class="text-[10px] opacity-50 ml-1">({{ dep.initials }})</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">Période</label>
                    <div class="grid grid-cols-1 gap-3">
                        <div v-for="(label, key) in {startDate: 'Après le', endDate: 'Avant le'}" :key="key">
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <CalendarIcon class="h-4 w-4 text-gray-400 group-focus-within:text-sky-500 transition-colors" />
                                </div>
                                <input
                                    type="date"
                                    v-model="filters[key]"
                                    class="block w-full bg-transparent dark:bg-slate-900/50 border-gray-200 dark:border-slate-800 rounded-xl pl-10 py-2.5 text-sm dark:text-zinc-200 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all"
                                >
                                <span class="absolute -top-2 left-3 bg-white dark:bg-slate-900 px-1 text-[9px] font-bold text-zinc-400 uppercase tracking-tighter">{{ label }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="fileType" class="block text-[10px] font-black text-gray-400 dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">Format</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <DocumentIcon class="h-4 w-4" />
                        </div>
                        <select
                            id="fileType"
                            v-model="filters.fileType"
                            class="block w-full bg-transparent dark:bg-slate-900/50 border-gray-200 dark:border-slate-800 rounded-xl pl-10 py-2.5 text-sm dark:text-zinc-200 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all appearance-none cursor-pointer"
                        >
                            <option v-for="type in fileTypeList" :key="type.id" :value="type.id" class="dark:bg-slate-900">
                                {{ type.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-zinc-500 uppercase tracking-[0.2em] mb-3 ml-1">Trier par</label>
                    <div class="space-y-1">
                        <label
                            v-for="(label, value) in sortPossibilities"
                            :key="value"
                            :class="['flex items-center p-2 rounded-xl cursor-pointer transition-all border border-transparent',
                                    filters.sortBy === value ? 'bg-sky-50 dark:bg-sky-900/20 border-sky-100 dark:border-sky-900/30 text-sky-600 dark:text-sky-400' : 'hover:bg-gray-50 dark:hover:bg-slate-800 text-gray-600 dark:text-zinc-400']"
                        >
                            <input
                                :id="value"
                                :value="value"
                                v-model="filters.sortBy"
                                type="radio"
                                class="h-4 w-4 border-gray-300 text-sky-500 focus:ring-sky-500/20 dark:bg-slate-900 transition-all"
                            >
                            <span class="ml-3 text-sm font-semibold tracking-tight">{{ label }}</span>
                        </label>
                    </div>
                </div>

            </form>
        </Transition>
    </section>
</template>
