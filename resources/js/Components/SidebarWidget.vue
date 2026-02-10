<script setup lang="ts">
import { ref } from 'vue';
import TreeViewItem from '@/Components/TreeViewItem.vue';
import { Link } from '@inertiajs/vue3';
import editor from '@/routes/editor/index';
import { home } from '@/routes'
interface Child {
    id: number;
    name: string;
    children: Array<Child> | null;
}

defineProps<{
    title: string;
    children: Array<Child> | null;
    racineDocument: {
        id: number;
        title: string;
    } | null;
}>();

const isActive = ref(true);
</script>

<template>
    <section class="bg-white shadow rounded-lg overflow-hidden">
        <h2
            @click="isActive = !isActive"
            class="font-bold text-lg p-4 space-x-2 bg-slate-400 dark:bg-slate-800 dark:text-gray-300 flex h-[6vh] items-center border-b hover:cursor-pointer"
        >
            {{ title }}
        </h2>

        <ul v-if="isActive" class="overflow-scroll divide-gray-200 dark:bg-zinc-700 dark:text-gray-300 min-h-[69svh] p-2">
            <Link
                v-if="racineDocument"
                :href="home.url()"
                class="font-semibold hover:font-extrabold block text-center
                text-lg hover:text-sky-600 dark:hover:text-sky-500"
            >
                Accueil
            </Link>
            <Link
                class="font-semibold hover:font-extrabold block text-center
                text-lg hover:text-sky-600 dark:hover:text-sky-500"
                v-else :href="editor.document.create.url(0)"
            >
                Créer la page d'accueil
            </Link>
            <li>
                <hr class="mt-2 w-3/5 mx-auto" />
            </li>
            <TreeViewItem
                v-for="child in children"
                :key="child.id"
                :child="child"
                class="ml-1.5"
            />
            <Link
                class="inline-flex items-center rounded-full font-extrabold text-yellow-600 hover:text-yellow-800 min-w-[40px] overflow-hidden transition-all duration-300"
                :href="editor.folder.create.url(0)"
            >
                <span class="flex-shrink flex-1 overflow-hidden whitespace-nowrap text-ellipsis mr-1 min-w-0">
                    Nouveau dossier
                </span>

                <span class="flex-none">+</span>
            </Link>
        </ul>
    </section>
</template>
