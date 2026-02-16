<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { HomeIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';
import navigate from '@/routes/navigate';
import { decodeEntities } from '@/lib/utils';

defineProps<{
    parents : Array<{
        id : number,
        name : string
    }>,
}>();
</script>

<template>
    <nav class="flex items-center space-x-1 text-sm font-medium p-1 overflow-x-auto no-scrollbar">
        <Link
            href="/"
            class="flex items-center justify-center p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-white dark:hover:bg-zinc-800 hover:text-sky-600 dark:hover:text-sky-400 transition-all duration-200 shadow-sm border border-transparent hover:border-gray-200 dark:hover:border-zinc-700"
        >
            <HomeIcon class="h-4 w-4 shrink-0" />
        </Link>

        <div v-for="(parent, index) in parents" :key="parent.id" class="flex items-center">
            <ChevronRightIcon class="h-5 w-5 text-gray-400 shrink-0 mx-0.5" />

            <Link
                :href="navigate.folder.url(parent.id)"
                class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-all duration-200"
                :class="[
                    index === parents.length - 1
                        ? 'text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-900/30 font-bold'
                        : 'text-gray-600 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-800 hover:text-sky-600 shadow-sm border border-transparent hover:border-gray-200 dark:hover:border-zinc-700'
                ]"
            >
                {{ decodeEntities(parent.name) }}
            </Link>
        </div>
    </nav>
</template>

<style scoped>
/* Cache la scrollbar si le chemin est trop long sur mobile tout en permettant le défilement */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
