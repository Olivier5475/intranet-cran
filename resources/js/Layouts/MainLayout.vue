<script setup lang="ts">
import SidebarWidget from '@/Components/SidebarWidget.vue';
import MainNav from '@/Components/MainNav.vue';
import FilterWidget from '@/Components/FilterWidget.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import LogoWidget from '@/Components/LogoWidget.vue';
import { provide, readonly, ref } from 'vue';

const activeFilters = ref({});

function handleFilterChange(newFilters) {
    activeFilters.value = newFilters;
    console.log('MainLayout a reçu les filtres :', newFilters);
}

provide('activeFilters', readonly(activeFilters));

// --- Données pour les Widgets ---
const modificationsRecentes = [
    { href: '#', text: "présentation projet d'intégration Andrea Witz", date: '20/10/2025' },
    { href: '#', text: 'présentation projet communication MS Nourdin', date: '20/10/2025' },
];

const actualites = [
    { href: '#', text: "Titre de l'actu", date: '03/04/2023', icon: 'News' },
    { href: '#', text: 'Cloud CRAN', date: '22/09/2021', icon: 'News' },
];

defineProps<{
    racineChildren: Array<{
        id: number;
        name: string;
    }>;
    departements: Array<{
        id: number;
        name: string;
        initials: string;
    }>;
}>();
</script>

<template>
    <FlashMessage />

    <header>
        <MainNav :racineChildren="racineChildren" />
    </header>

    <div class="bg-gray-100 dark:bg-slate-600 min-h-screen">
        <div class="lg:grid-cols-5 gap-6 p-4 mx-auto grid w-11/12 grid-cols-1">
            <aside class="lg:col-span-1 space-y-6">
                <SidebarWidget title="Modifications récentes" />
            </aside>

            <main class="lg:col-span-3 bg-white dark:bg-slate-800 dark:text-white shadow-lg rounded-lg overflow-hidden">
                <slot />
            </main>

            <aside class="lg:col-span-1 space-y-6">
                <FilterWidget :departements=departements @filters-updated=handleFilterChange />
            </aside>
        </div>
        <footer class="py-10 bg-white dark:bg-slate-900 shadow-sm flex items-center justify-between">
            <LogoWidget />
        </footer>
    </div>
</template>
