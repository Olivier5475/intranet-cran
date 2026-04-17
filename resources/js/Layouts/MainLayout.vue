<script setup lang="ts">
// 1. Vue & Core
import { provide, readonly, ref } from 'vue';

// 2. Librairies & Utilitaires tiers
import timeout from '@/Composables/useAutoLogout';

// 3. Types
import { Departement } from '@/types/departement';
import { Folder } from '@/types/folder';

// 4. Composants
import FilterWidget from '@/Components/Layout/FilterWidget.vue';
import FlashMessage from '@/Components/UI/FlashMessage.vue';
import LogoWidget from '@/Components/Layout/LogoWidget.vue';
import MainNav from '@/Components/Layout/MainNav.vue';
import SidebarWidget from '@/Components/Layout/SidebarWidget.vue';

timeout.setup();
const activeFilters = ref({});

function handleFilterChange(newFilters: any) {
    activeFilters.value = newFilters;
    console.log(newFilters);
}

provide('activeFilters', readonly(activeFilters));

defineProps<{
    racineChildren?: Folder[];
    departements: Departement[];
    racineDocument: {
        id: number;
        name: string;
    } | null;
}>();
</script>

<template>
    <FlashMessage />
    <div class="w-full flex-grow">
        <header>
            <MainNav :racineChildren="racineChildren" />
        </header>

        <div class="bg-gray-100 dark:bg-slate-600">
            <div class="lg:grid-cols-5 gap-6 p-4 mx-auto grid w-11/12 grid-cols-1 items-stretch">

                <aside class="lg:col-span-1 h-full">
                    <SidebarWidget
                        class="sticky top-4 overflow-y-auto"
                        name="Navigation"
                        :children="racineChildren"
                        :racine-document="racineDocument"
                    />
                </aside>

                <main class="lg:col-span-3 bg-white dark:bg-slate-800 dark:text-white shadow-lg rounded-lg pb-12 pt-2 px-2 min-h-[75vh] overflow-hidden">
                    <slot />
                </main>

                <aside class="lg:col-span-1">
                    <FilterWidget :departements="departements" @filters-updated="handleFilterChange" />
                </aside>
            </div>
        </div>
    </div>

    <footer class="py-10 bg-white dark:bg-slate-900 shadow-sm flex items-center justify-between">
        <LogoWidget />
    </footer>
</template>
