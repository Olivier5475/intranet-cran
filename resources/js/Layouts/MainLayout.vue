<script setup lang="ts">
import SidebarWidget from '@/Components/SidebarWidget.vue';
import MainNav from '@/Components/MainNav.vue';
import FilterWidget from '@/Components/FilterWidget.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import LogoWidget from '@/Components/LogoWidget.vue';
import { provide, readonly, ref } from 'vue';

const activeFilters = ref({});

function handleFilterChange(newFilters : any) {
    activeFilters.value = newFilters;
    console.log(newFilters);
}

provide('activeFilters', readonly(activeFilters));

interface Child {
    id: number;
    name: string;
    children: Array<Child> | null;
}

interface Departement {
    id: number;
    name: string;
    initials: string;
}

defineProps<{
    racineChildren: Array<Child> | null;
    departements: Array<Departement> | null;
}>();
</script>

<template>
    <FlashMessage />
    <div class="flex-grow w-full">
        <header>
            <MainNav :racineChildren="racineChildren" />
        </header>

        <div class="bg-gray-100 dark:bg-slate-600">
            <div class="lg:grid-cols-5 gap-6 p-4 mx-auto grid w-11/12 grid-cols-1">
                <aside class="lg:col-span-1 space-y-6">
                    <SidebarWidget title="Navigation" :children="racineChildren" />
                </aside>

                <main class="lg:col-span-3 bg-white dark:bg-slate-800 dark:text-white shadow-lg rounded-lg overflow-hidden pb-12 pt-2 px-2 min-h-[75vh]">
                    <slot />
                </main>

                <aside class="lg:col-span-1 space-y-6">
                    <FilterWidget :departements=departements @filters-updated=handleFilterChange />
                </aside>
            </div>
        </div>
    </div>

    <footer class="py-10 bg-white dark:bg-slate-900 shadow-sm flex items-center justify-between">
        <LogoWidget />
    </footer>
</template>
