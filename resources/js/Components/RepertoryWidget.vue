<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { HomeIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';
import navigate from '@/routes/navigate/index.js';

defineProps<{
    parents : Array<{
        id : number,
        name : string
    }>,
}>();
</script>

<template>
    <nav class="text-base font-medium text-gray-500 flex items-center dark:text-white p-2">
        <Link href="/" class="hover:text-gray-700 hover:dark:text-gray-400">
            <HomeIcon class="h-5 w-5 shrink-0" />
        </Link>
        <div v-for="parent in parents" :key=parent.id class="flex">
            <ChevronRightIcon class="h-5 w-5 shrink-0 my-auto" />
            <Link
                :href='navigate.folder.url(parent.id)'
                class=" hover:dark:text-gray-400"
            >
                {{ parent.name }}
            </Link>
        </div>
    </nav>
</template>
