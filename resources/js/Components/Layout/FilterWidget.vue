<script setup lang="ts">
import { reactive, watch, ref, computed } from 'vue';
import { AdjustmentsHorizontalIcon, CalendarIcon, DocumentIcon, ChevronDownIcon } from "@heroicons/vue/24/outline";
import { Groupe } from "@/types/groupe";

const emit = defineEmits(["filters-updated"]);
const props = defineProps<{ groupes: Groupe[]; }>();

// 🚀 Suppression de sortBy
const filters = reactive({
    startDate: null,
    endDate: null,
    fileType: "all",
    selectedGroupes: [] as number[],
});

const fileTypeList = [
    { id: "all", name: "Tous les types" },
    { id: "word", name: "Word" },
    { id: "tableur", name: "Tableur" },
    { id: "presentation", name: "Presentation" },
    { id: "pdf", name: "PDF" },
    { id: "image", name: "Image" },
    { id: "video", name: "Vidéo" },
    { id: "gif", name: "GIF" },
    { id: "audio", name: "Audio" },
    { id: "archive", name: "Archive" },
    { id: "document", name: "Document" },
    { id: "folder", name: "Dossier" },
];

watch(filters, (newFilterValues) => { emit("filters-updated", newFilterValues); }, { deep: true });

const isActive = defineModel<boolean>('isActive', { default: true });
const isExpanded = ref(isActive.value);

const toggle = function () {
    if (isExpanded.value) {
        isExpanded.value = false;
    } else {
        isActive.value = true;
        setTimeout(() => {
            isExpanded.value = true;
        }, 20);
    }
};

const onAfterLeave = () => {
    isActive.value = false;
};

const racineGroupes = computed(() => {
    if (!props.groupes) return props.groupes;
    return props.groupes.filter(grp => grp.parent == null);
})
</script>

<template>
    <section class="bg-white shadow-xl rounded-2xl border-gray-100 dark:border-slate-800 dark:bg-slate-900 overflow-hidden border transition-all duration-600">
        <div
            @click="toggle"
            class="h-14 cursor-pointer font-bold text-gray-700 dark:text-zinc-300 p-4 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors border-b border-gray-100 dark:border-slate-800"
        >
            <div class="space-x-3 flex items-center">
                <AdjustmentsHorizontalIcon class="h-5 w-5 text-sky-500" />
                <span class="text-sm tracking-wider uppercase">Filtres</span>
            </div>

            <ChevronDownIcon
                :class="['w-5 h-5 text-gray-400 dark:text-zinc-500 transition-transform duration-300', isExpanded ? '-rotate-180' : '']"
            />
        </div>

        <Transition
            enter-active-class="transition-all duration-600 ease-out"
            enter-from-class="max-h-0 opacity-0 transform -translate-y-2"
            enter-to-class="max-h-[1500px] opacity-100 transform translate-y-0"
            leave-active-class="transition-all duration-600 ease-in"
            leave-from-class="max-h-[1500px] opacity-100 transform translate-y-0"
            leave-to-class="max-h-0 opacity-0 transform -translate-y-2"
            @after-leave="onAfterLeave"
        >
            <form v-if="isExpanded" class="p-5 dark:bg-sky-900/5 overflow-hidden flex flex-col gap-8 transition-all duration-600">
                <div class="space-y-4">
                    <label class="font-black text-gray-400 dark:text-zinc-500 ml-1 block text-[10px] tracking-[0.2em] uppercase">Période</label>
                    <div class="gap-3 grid grid-cols-1">
                        <div v-for="(label, key) in { startDate: 'Après le', endDate: 'Avant le' }" :key="key" class="group relative">
                            <div class="inset-y-0 left-0 pl-3 pointer-events-none absolute flex items-center"><CalendarIcon class="h-4 w-4 text-gray-400 group-focus-within:text-sky-500 transition-colors" /></div>
                            <input type="date" v-model="filters[key]" class="dark:bg-slate-900/50 border-gray-200 dark:border-slate-800 rounded-xl pl-10 py-2.5 text-sm dark:text-zinc-200 focus:ring-sky-500/10 focus:border-sky-500 block w-full bg-transparent transition-all focus:ring-4" />
                            <span class="-top-2 left-3 bg-white dark:bg-slate-900 px-1 font-bold text-zinc-400 absolute text-[9px] uppercase tracking-tighter">{{ label }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="fileType" class="font-black text-gray-400 dark:text-zinc-500 ml-1 block text-[10px] tracking-[0.2em] uppercase">Format</label>
                    <div class="relative">
                        <div class="inset-y-0 left-0 pl-3 text-gray-400 pointer-events-none absolute flex items-center"><DocumentIcon class="h-4 w-4" /></div>
                        <select id="fileType" v-model="filters.fileType" class="dark:bg-slate-900/50 border-gray-200 dark:border-slate-800 rounded-xl pl-10 py-2.5 text-sm dark:text-zinc-200 focus:ring-sky-500/10 focus:border-sky-500 block w-full cursor-pointer bg-transparent focus:ring-4 transition-all">
                            <option v-for="type in fileTypeList" :key="type.id" :value="type.id" class="dark:bg-slate-900">{{ type.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="font-black text-gray-400 dark:text-zinc-500 mb-3 ml-1 block text-[10px] tracking-[0.2em] uppercase">Groupes</label>
                    <div class="gap-2 grid grid-cols-1">
                        <label v-for="grp in racineGroupes" :key="grp.id" class="group p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40 flex cursor-pointer items-center transition-all">
                            <div class="relative flex items-center">
                                <input :id="grp.id.toString()" type="checkbox" v-model="filters.selectedGroupes" :value="grp.id" class="h-5 w-5 rounded-lg border-gray-300 dark:border-slate-700 text-sky-500 focus:ring-sky-500/20 dark:bg-slate-900 cursor-pointer" />
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-600 dark:text-zinc-300 group-hover:text-sky-500 transition-colors break-words">
                                {{ grp.name }} <span class="text-[10px] opacity-50">({{ grp.initials }})</span>
                                <div class="rounded-full w-2 h-2 ml-2 inline-block" :style="{ backgroundColor: grp.color }" />
                            </span>
                        </label>
                    </div>
                </div>
            </form>
        </Transition>
    </section>
</template>
