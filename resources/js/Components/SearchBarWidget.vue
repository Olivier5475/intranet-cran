<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/20/solid'

const props = defineProps<{
    currentSearch: string | null,
}>()

const searchQuery = ref(props.currentSearch || '')

function search() {
    router.get(
        window.location.pathname,
        { q: searchQuery.value },
        {
            preserveState: true,
            replace: true,
        }
    )
}

// Optionnel : Effacer la recherche
function clearSearch() {
    searchQuery.value = ''
    search()
}
</script>

<template>
    <div class="relative flex items-center w-full max-w-4xl mx-auto px-4 group">
        <div class="absolute left-7 text-gray-400 group-focus-within:text-sky-500 transition-colors">
            <MagnifyingGlassIcon class="h-5 w-5" />
        </div>

        <input
            type="text"
            v-model="searchQuery"
            @keyup.enter="search"
            placeholder="Rechercher un dossier, un document..."
            class="
                w-full
                pl-11 pr-20 py-2.5
                bg-white dark:bg-zinc-800/50
                border border-gray-200 dark:border-zinc-700
                rounded-xl
                shadow-sm
                text-sm
                text-gray-900 dark:text-gray-100
                placeholder-gray-400 dark:placeholder-zinc-500
                focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500
                focus:bg-white dark:focus:bg-zinc-800
                transition-all duration-200
            "
        >

        <div class="absolute right-6 flex items-center gap-2">
            <button
                v-if="searchQuery"
                @click="clearSearch"
                class="p-1 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors"
            >
                <XMarkIcon class="h-4 w-4" />
            </button>

            <div v-if="searchQuery" class="w-[1px] h-4 bg-gray-200 dark:border-zinc-700"></div>

            <button
                @click="search"
                class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:text-sky-700 px-2 py-1 transition-colors"
            >
                ENTRÉE
            </button>
        </div>
    </div>
</template>
