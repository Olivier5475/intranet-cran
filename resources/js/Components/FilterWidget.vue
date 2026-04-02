<script setup lang="ts">
// 1. Vue & Core
import { ref, reactive, watch } from 'vue';

// 2. Librairie Tierce (Icônes)
import { AdjustmentsHorizontalIcon, CalendarIcon, DocumentIcon } from '@heroicons/vue/24/outline';

// 3. Types
import { Departement } from '@/types/departement';

const emit = defineEmits(['filters-updated']);

// --- L'état de nos filtres ---
defineProps<{
    departements: Departement[];
}>();

const filters = reactive({
    startDate: null,
    endDate: null,
    fileType: 'all',
    sortBy: 'newest',
    selectedDepartments: [] as number[], // La variable qui manquait pour vos checkboxes
});

const sortPossibilities = ref({
    pertinence: 'Pertinence',
    newest: 'Le plus recent',
    oldest: 'Le plus ancien',
    name: 'Par nom',
});

const fileTypeList = [
    { id: 'all', name: 'Tous les types' },
    { id: 'word', name: 'Word' },
    { id: 'tableur', name: 'Tableur' },
    { id: 'presentation', name: 'Presentation' },
    { id: 'pdf', name: 'PDF' },
    { id: 'image', name: 'Image' },
    { id: 'video', name: 'Vidéo' },
    { id: 'gif', name: 'GIF' },
    { id: 'audio', name: 'Audio' },
    { id: 'archive', name: 'Archive' },
    { id: 'document', name: 'Document' },
    { id: 'folder', name: 'Dossier' },
];

watch(
    filters,
    (newFilterValues) => {
        // Émet l'événement vers le parent avec les nouvelles valeurs
        emit('filters-updated', newFilterValues);
    },
    { deep: true },
); // surveil l'intérieur de l'array departement

const isActive = ref(!window.matchMedia('(orientation: portrait)').matches);

const toggle = function () {
    isActive.value = !isActive.value;
};
</script>

<template>
    <section
        class="bg-white shadow-xl rounded-2xl border-gray-100 dark:border-slate-800
        dark:bg-slate-900 overflow-hidden border transition-all duration-300"
    >
        <div
            @click="toggle"
            class="h-14 font-bold text-gray-700 dark:text-zinc-300 p-4 bg-slate-50
            dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 border-gray-100
            dark:border-slate-800 flex cursor-pointer items-center justify-between border-b transition-colors"
        >
            <div class="space-x-3 flex items-center">
                <AdjustmentsHorizontalIcon class="h-5 w-5 text-sky-500" />
                <span class="text-sm tracking-wider uppercase">Filtres</span>
            </div>
            <div :class="['transition-transform duration-300', isActive ? 'rotate-180' : '']">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4" fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2" d="M19 9l-7 7-7-7"
                    />
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
                    <label class="font-black text-gray-400 dark:text-zinc-500 mb-3 ml-1 block text-[10px]
                    tracking-[0.2em] uppercase"
                        >Départements</label
                    >
                    <div class="gap-2 grid grid-cols-1">
                        <label
                            v-for="dep in departements"
                            :key="dep.id"
                            class="group p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40
                            flex cursor-pointer items-center transition-all"
                        >
                            <div class="relative flex items-center">
                                <input
                                    :id="dep.id.toString()"
                                    type="checkbox"
                                    v-model="filters.selectedDepartments"
                                    :value="dep.id"
                                    class="h-5 w-5 rounded-lg border-gray-300 dark:border-slate-700
                                    text-sky-500 focus:ring-sky-500/20 dark:bg-slate-900 cursor-pointer transition-all"
                                />
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-600 dark:text-zinc-300
                            group-hover:text-sky-500 transition-colors">
                                {{ dep.name }} <span class="ml-1 text-[10px] opacity-50">({{ dep.initials }})</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="font-black text-gray-400 dark:text-zinc-500 ml-1 block text-[10px]
                     tracking-[0.2em] uppercase">Période</label>
                    <div class="gap-3 grid grid-cols-1">
                        <div v-for="(label, key) in { startDate: 'Après le', endDate: 'Avant le' }" :key="key">
                            <div class="group relative">
                                <div
                                    class="inset-y-0 left-0 pl-3 pointer-events-none absolute flex items-center"
                                >
                                    <CalendarIcon class="h-4 w-4 text-gray-400 group-focus-within:text-sky-500
                                     transition-colors" />
                                </div>
                                <input
                                    type="date"
                                    v-model="filters[key]"
                                    class="dark:bg-slate-900/50 border-gray-200 dark:border-slate-800
                                     rounded-xl pl-10 py-2.5 text-sm dark:text-zinc-200 focus:ring-sky-500/10
                                      focus:border-sky-500 block w-full bg-transparent transition-all
                                      focus:ring-4"
                                />
                                <span
                                    class="-top-2 left-3 bg-white dark:bg-slate-900 px-1 font-bold
                                    text-zinc-400 tracking-tighter absolute text-[9px] uppercase"
                                    >{{ label }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="fileType" class="font-black text-gray-400 dark:text-zinc-500 ml-1
                    block text-[10px] tracking-[0.2em] uppercase"
                        >Format</label
                    >
                    <div class="relative">
                        <div class="inset-y-0 left-0 pl-3 text-gray-400 pointer-events-none
                        absolute flex items-center">
                            <DocumentIcon class="h-4 w-4" />
                        </div>
                        <select
                            id="fileType"
                            v-model="filters.fileType"
                            class="dark:bg-slate-900/50 border-gray-200 dark:border-slate-800
                             rounded-xl pl-10 py-2.5 text-sm dark:text-zinc-200 focus:ring-sky-500/10
                              focus:border-sky-500 block w-full cursor-pointer appearance-none bg-transparent
                               transition-all focus:ring-4"
                        >
                            <option
                                v-for="type in fileTypeList"
                                :key="type.id"
                                :value="type.id"
                                class="dark:bg-slate-900"
                            >
                                {{ type.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="font-black text-gray-400 dark:text-zinc-500 mb-3 ml-1 block
                    text-[10px] tracking-[0.2em] uppercase"
                        >Trier par</label
                    >
                    <div class="space-y-1">
                        <label
                            v-for="(label, value) in sortPossibilities"
                            :key="value"
                            :class="[
                                'p-2 rounded-xl flex cursor-pointer items-center border border-transparent ' +
                                 'transition-all',
                                filters.sortBy === value
                                    ? 'bg-sky-50 dark:bg-sky-900/20 border-sky-100 ' +
                                     'dark:border-sky-900/30 text-sky-600 dark:text-sky-400'
                                    : 'hover:bg-gray-50 dark:hover:bg-slate-800 text-gray-600 ' +
                                     'dark:text-zinc-400',
                            ]"
                        >
                            <input
                                :id="value"
                                :value="value"
                                v-model="filters.sortBy"
                                type="radio"
                                class="h-4 w-4 border-gray-300 text-sky-500 focus:ring-sky-500/20
                                dark:bg-slate-900 transition-all"
                            />
                            <span class="ml-3 text-sm font-semibold tracking-tight">{{ label }}</span>
                        </label>
                    </div>
                </div>
            </form>
        </Transition>
    </section>
</template>
