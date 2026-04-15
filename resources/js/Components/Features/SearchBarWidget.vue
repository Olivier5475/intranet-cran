<script setup lang="ts">
// 1. Vue & Core
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

// 2. Librairies tierces (Icônes)
import { MagnifyingGlassIcon, XMarkIcon } from "@heroicons/vue/24/solid";

const props = defineProps<{
    currentSearch: string | null;
    placeholder: string;
}>();

const searchQuery = ref(props.currentSearch || "");
const searchInContent = ref(false); // Toggle ON/OFF
/**
 * Lance une requête sur l'URL courant en ajoutant une query string
 */
function search() {
    router.get(
        window.location.pathname,
        { q: searchQuery.value, in_content: searchInContent.value},
        {
            preserveState: true,
            replace: true,
        },
    );
}

/**
 * Efface la recherche
 */
function clearSearch() {
    searchQuery.value = "";
    search();
}

const showTooltip = ref(false);
const tooltipPos = ref({ x: 0, y: 0 });

const updateTooltip = (e: any) => {
    // On ajoute un petit décalage (15px) pour que le texte ne soit pas sous le curseur
    tooltipPos.value = { x: e.clientX + 15, y: e.clientY + 15 };
};
</script>

<template>
    <div class="max-w-4xl px-4 group relative mx-auto flex w-full items-center">
        <div
            class="left-7 text-gray-400 group-focus-within:text-sky-500 absolute transition-colors"
        >
            <MagnifyingGlassIcon class="h-5 w-5" />
        </div>

        <input
            type="text"
            v-model="searchQuery"
            @keyup.enter="search"
            :placeholder="placeholder"
            class="pl-11 pr-20 py-2.5 rounded shadow-sm text-sm bg-white dark:bg-zinc-800/50 border-gray-200 dark:border-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:ring-sky-500/20 focus:border-sky-500 focus:bg-white dark:focus:bg-zinc-800 w-full border transition-all duration-200 focus:ring-2"
        />

        <div class="right-6 gap-2 absolute flex items-center">
            <button
                v-if="searchQuery"
                @click="clearSearch"
                class="p-1 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors"
            >
                <XMarkIcon class="h-4 w-4" />
            </button>

            <div
                v-if="searchQuery"
                class="h-4 bg-gray-200 dark:border-zinc-700 w-[1px]"
            ></div>

            <div class="relative inline-block mt-1.5">
                <label
                    class="inline-flex items-center cursor-pointer"
                    @mouseenter="showTooltip = true"
                    @mouseleave="showTooltip = false"
                    @mousemove="updateTooltip"
                >
                    <input
                        type="checkbox"
                        class="sr-only peer"
                        v-model="searchInContent"
                    />
                    <div
                        class="relative w-9 h-5 bg-slate-900 peer-checked:bg-sky-600 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"
                    ></div>
                </label>

                <div
                    v-if="showTooltip"
                    class="fixed z-[9999] pointer-events-none px-2 py-1 text-xs font-medium text-white bg-slate-800 rounded shadow-lg border border-slate-600 whitespace-nowrap"
                    :style="{
                        top: tooltipPos.y + 'px',
                        left: tooltipPos.x + 'px',
                    }"
                >
                    Recherche dans le contenu
                </div>
            </div>

            <button
                @click="search"
                class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:text-sky-700 px-2 py-1 transition-colors"
            >
                ENTRÉE
            </button>
        </div>
    </div>
</template>
