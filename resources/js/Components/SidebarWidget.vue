<script setup lang="ts">
import { ref } from 'vue';
import TreeViewItem from '@/Components/TreeViewItem.vue';
import { Link } from '@inertiajs/vue3';
import folder_route from '@/routes/editor/folder';
interface Child {
    id: number;
    name: string;
    children: Array<Child> | null;
}

defineProps<{
    title: string;
    children: Array<Child> | null;
}>();

const isActive = ref(true);
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
                class="inline-flex items-center rounded-full font-extrabold text-yellow-600 hover:text-yellow-800 min-w-[40px] overflow-hidden transition-all duration-300"
                :href="folder_route.create.url(0)"
            >
                <span class="flex-shrink flex-1 overflow-hidden whitespace-nowrap text-ellipsis mr-1 min-w-0">
                    Nouveau dossier
                </span>

                <span class="flex-none">+</span>
            </Link>
        </ul>
    </section>
</template>
