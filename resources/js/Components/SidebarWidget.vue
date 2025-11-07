<script setup lang="ts">

import { ref } from 'vue';

defineProps<{
    title: string,
}>();

const isActive = ref(!window.matchMedia('(orientation: portrait)').matches);
const toggle = function() {
    isActive.value = !isActive.value;
};

</script>

<template>
    <section class="bg-white shadow rounded-lg overflow-hidden">
        <h2 @click=toggle class="font-bold text-lg p-4 border-b flex items-center space-x-2 bg-slate-300 dark:bg-slate-800 dark:text-gray-300 hover:cursor-pointer">
            {{ title }}
        </h2>

        <ul v-if="isActive" class="divide-y divide-gray-200 dark:bg-zinc-700 dark:text-gray-300 h-screen">

        </ul>
    </section>
</template>
