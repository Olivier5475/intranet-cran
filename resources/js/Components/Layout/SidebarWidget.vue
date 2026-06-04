<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDownIcon, ChevronRightIcon, HomeIcon, PlusIcon } from '@heroicons/vue/24/solid';
import editor from '@/routes/editor';
import folder_route from '@/routes/editor/folder';
import { home } from '@/routes';
import TreeViewItem from '@/Components/Layout/TreeViewItem.vue';
import { Folder } from '@/types/folder';

const page = usePage();
const user = page.props.auth.user;

defineProps<{
    name: string;
    children?: Array<Folder>;
}>();

// Liaison bi-directionnelle de l'état actif avec le MainLayout
const isActive = defineModel<boolean>('isActive', { default: true });
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
            enter-from-class="max-h-0 opacity-0 transform -translate-y-2"
            enter-to-class="max-h-[1000px] opacity-100 transform translate-y-0"
            leave-active-class="transition-all duration-200 ease-in-out"
            leave-from-class="max-h-[1000px] opacity-100 transform translate-y-0"
            leave-to-class="max-h-0 opacity-0 transform -translate-y-2"
        >
            <div v-if="isActive" class="dark:bg-sky-900/5 p-3 overflow-hidden">
                <ul class="lg:min-h-[65svh] max-h-[calc(100vh-2rem)] overflow-y-auto no-scrollbar space-y-1">
                    <Link
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
