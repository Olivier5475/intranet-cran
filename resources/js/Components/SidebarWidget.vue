<script setup lang="ts">
import { ref } from 'vue';
import TreeViewItem from '@/Components/TreeViewItem.vue';
import { Link } from '@inertiajs/vue3'; // 🎯 Importez le nouveau composant récursif

interface Child {
    id: number;
    name: string;
    children: Array<Child> | null;
}

defineProps<{
    title: string;
    children: Array<Child> | null;
}>();

const isActive = ref(true); // État de la section principale
</script>

<template>
    <section class="bg-white shadow rounded-lg overflow-hidden">
        <h2
            @click="isActive = !isActive"
            class="font-bold text-lg p-4 space-x-2 bg-slate-300 dark:bg-slate-800 dark:text-gray-300 flex h-[6vh] items-center border-b hover:cursor-pointer"
        >
            {{ title }}
        </h2>

        <ul v-if="isActive" class="overflow-scroll divide-gray-200 dark:bg-zinc-700 dark:text-gray-300 min-h-[69svh] p-2">
            <TreeViewItem
                v-for="child in children"
                :key="child.id"
                :child="child"
            />
            <Link
                class="inline-flex items-center rounded-full text-lg font-extrabold bg-gray-400 dark:bg-slate-600 dark:text-slate-400 min-w-[40px] h-10 px-3 overflow-hidden transition-all duration-300"
                :href="`/navigation/0/admin/folders/create`"
            >
                <span class="flex-shrink flex-1 overflow-hidden whitespace-nowrap text-ellipsis mr-1 min-w-0">
                    Nouveau dossier
                </span>

                <span class="flex-none">+</span>
            </Link>        </ul>
    </section>
</template>
