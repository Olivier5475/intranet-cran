<script setup lang="ts">
// 1. Vue & Core
import { provide, readonly, ref } from 'vue';

// 2. Librairies & Utilitaires tiers
import timeout from '@/Composables/useAutoLogout';

// 3. Types
import { Groupe } from '@/types/groupe';
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
    groupes: Groupe[];
    racineDocument: {
        id: number;
        name: string;
    } | null;
}>();

const sidebarActive = ref(true);
const filterActive = ref(true);

// Partage de l'état des Sidebars pour l'adaptation dynamique des enfants (comme Navigation.vue)
provide('sidebarActive', readonly(sidebarActive));
provide('filterActive', readonly(filterActive));
</script>

<template>
    <FlashMessage />
    <div class="w-full flex-grow">
        <header>
            <MainNav :racineChildren="racineChildren" />
        </header>

        <div class="bg-gray-100 dark:bg-slate-600 min-h-screen">
            <div class="lg:grid-cols-5 gap-6 p-4 mx-auto grid w-11/12 grid-cols-1 items-start">

                <aside
                    class="w-full"
                    :class="[
                        sidebarActive ? 'lg:col-span-1 lg:sticky lg:top-4' : 'lg:col-span-5 lg:order-first',
                    ]"
                >
                    <SidebarWidget
                        name="Navigation"
                        :children="racineChildren"
                        :racine-document="racineDocument"
                        v-model:isActive="sidebarActive"
                    />
                </aside>

                <main
                    class="bg-white dark:bg-slate-800 dark:text-white shadow-lg rounded-lg pb-12 pt-2 px-2 min-h-[75vh] overflow-hidden"
                    :class="[
                        sidebarActive && filterActive ? 'lg:col-span-3' : '',
                        !sidebarActive && filterActive ? 'lg:col-span-4' : '',
                        sidebarActive && !filterActive ? 'lg:col-span-4' : '',
                        !sidebarActive && !filterActive ? 'lg:col-span-5' : '',
                    ]"
                >
                    <slot />
                </main>

                <aside
                    class="w-full"
                    :class="[
                        filterActive ? 'lg:col-span-1' : 'lg:col-span-5 lg:order-first',
                    ]"
                >
                    <FilterWidget
                        :groupes="groupes"
                        @filters-updated="handleFilterChange"
                        v-model:isActive="filterActive"
                    />
                </aside>
            </div>
        </div>
    </div>

    <footer class="py-10 bg-white dark:bg-slate-900 shadow-sm flex items-center justify-between">
        <LogoWidget />
    </footer>
</template>
