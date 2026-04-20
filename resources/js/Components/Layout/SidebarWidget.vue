<script setup lang="ts">
// 1. Vue & Core
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import {
    ChevronDownIcon,
    ChevronRightIcon,
    HomeIcon,
    PlusIcon
} from '@heroicons/vue/24/solid';

// 3. Routes
import editor from '@/routes/editor';
import folder_route from '@/routes/editor/folder';
import { home } from '@/routes';

// 4. Composants
import TreeViewItem from '@/Components/Layout/TreeViewItem.vue';

// 5. Types
import { Folder } from '@/types/folder';

const page = usePage();
const user = page.props.auth.user;

defineProps<{
    name: string;
    children?: Array<Folder>;
    racineDocument: {
        id: number;
        name: string;
    } | null;
}>();

const isActive = ref(true);
</script>

<template>
    <section class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-slate-800 dark:bg-slate-900 transition-all duration-300">
        <h2
            @click="isActive = !isActive"
            class="h-14 cursor-pointer font-bold text-gray-700 dark:text-zinc-300 p-4 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors border-b border-gray-100 dark:border-slate-800"
        >
            <span class="flex items-center space-x-3">
                <component
                    :is="isActive ? ChevronDownIcon : ChevronRightIcon"
                    class="w-5 h-5 text-sky-500 transition-transform duration-300"
                />
                <span class="text-sm uppercase tracking-wider">{{ name }}</span>
            </span>
        </h2>

        <Transition
            enter-active-class="transition-all duration-300 ease-in-out"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200 ease-in-out"
            leave-from-class="opacity-100"
            leave-to-class="max-h-0 opacity-0"
        >
            <div v-if="isActive" class="dark:bg-sky-900/5 p-3">
                <ul class="lg:min-h-[65svh] max-h-[calc(100vh-2rem)] overflow-y-auto no-scrollbar space-y-1">

                    <Link
                        v-if="racineDocument"
                        :href="home.url()"
                        class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group border border-transparent"
                        :class="page.url === '/'
                                ? 'bg-sky-50 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 font-bold border-sky-100 dark:border-sky-900/30 shadow-sm'
                                : 'text-gray-600 dark:text-zinc-400 hover:bg-white dark:hover:bg-slate-800 hover:text-sky-600 dark:hover:text-sky-300 hover:shadow-sm hover:border-gray-100 dark:hover:border-slate-700'"
                    >
                        <HomeIcon
                            class="h-5 w-5 mr-3 transition-transform duration-200 group-hover:scale-110"
                            :class="page.url === '/' ? 'text-sky-500' : 'text-gray-400 dark:text-zinc-500'"
                        />
                        <span class="text-sm font-semibold uppercase tracking-tight"> Accueil </span>
                    </Link>

                    <Link
                        v-else
                        :href="editor.document.create.url(0)"
                        class="block p-4 text-center border-2 border-dashed border-gray-200 dark:border-slate-800 rounded-xl text-sm text-gray-400 hover:border-sky-500 hover:text-sky-500 transition-all"
                    >
                        + Créer la page d'accueil
                    </Link>

                    <li class="py-2">
                        <div class="h-px bg-gradient-to-r from-transparent via-gray-200 dark:via-slate-800 to-transparent w-4/5 mx-auto"></div>
                    </li>

                    <TreeViewItem v-for="child in children" :key="child.id" :child="child" />

                    <li class="mt-4 pt-2 border-t border-gray-50 dark:border-slate-800/50">
                        <Link
                            v-if="user.role == 'admin'"
                            class="group flex items-center px-3 py-2 text-xs font-bold text-gray-400 hover:text-amber-500 dark:text-zinc-500 dark:hover:text-amber-400 transition-colors uppercase tracking-widest"
                            :href="folder_route.create.url(0)"
                        >
                            <PlusIcon class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-300" />
                            Nouveau dossier
                        </Link>
                    </li>
                </ul>
            </div>
        </Transition>
    </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
