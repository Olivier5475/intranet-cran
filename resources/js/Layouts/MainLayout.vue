<script setup lang="ts">
import { ref, onUnmounted, provide } from 'vue';

// --- IMPORTS DES COMPOSANTS (Vérifie bien tes chemins d'accès ici) ---
import MainNav from '@/Components/Layout/MainNav.vue';
import SidebarWidget from '@/Components/Layout/SidebarWidget.vue';
import FilterWidget from '@/Components/Layout/FilterWidget.vue';

// --- PROPS ---
defineProps<{
    racineChildren: any[];
    groupes: any[];
}>();

// --- ÉTATS DES BARRES LATÉRALES ---
const sidebarActive = ref(true);
const filterActive = ref(true);
const activeFilters = ref({}); // Stockage global de l'état des filtres

// 🚀 ENVOI DES ÉTATS AUX ENFANTS (Home, Navigation, etc.)
provide('sidebarActive', sidebarActive);
provide('filterActive', filterActive);
provide('activeFilters', activeFilters);

const handleFilterChange = (filters: any) => {
    activeFilters.value = filters;
    console.log('Filtres mis à jour', filters);
};

// --- LOGIQUE DE REDIMENSIONNEMENT (DRAG & RESIZE) ---
const sidebarWidth = ref(280);
const filterWidth = ref(280);

const MIN_WIDTH = 200;
const MAX_WIDTH = 600;

let isDraggingSidebar = false;
let isDraggingFilter = false;

const startDragSidebar = () => {
    isDraggingSidebar = true;
    document.addEventListener('mousemove', onDragSidebar);
    document.addEventListener('mouseup', stopDrag);
    document.body.classList.add('select-none', 'cursor-col-resize');
};

const startDragFilter = () => {
    isDraggingFilter = true;
    document.addEventListener('mousemove', onDragFilter);
    document.addEventListener('mouseup', stopDrag);
    document.body.classList.add('select-none', 'cursor-col-resize');
};

const onDragSidebar = (e: MouseEvent) => {
    if (!isDraggingSidebar) return;
    sidebarWidth.value += e.movementX;
    if (sidebarWidth.value < MIN_WIDTH) sidebarWidth.value = MIN_WIDTH;
    if (sidebarWidth.value > MAX_WIDTH) sidebarWidth.value = MAX_WIDTH;
};

const onDragFilter = (e: MouseEvent) => {
    if (!isDraggingFilter) return;
    filterWidth.value -= e.movementX;
    if (filterWidth.value < MIN_WIDTH) filterWidth.value = MIN_WIDTH;
    if (filterWidth.value > MAX_WIDTH) filterWidth.value = MAX_WIDTH;
};

const stopDrag = () => {
    isDraggingSidebar = false;
    isDraggingFilter = false;
    document.removeEventListener('mousemove', onDragSidebar);
    document.removeEventListener('mousemove', onDragFilter);
    document.removeEventListener('mouseup', stopDrag);
    document.body.classList.remove('select-none', 'cursor-col-resize');
};

onUnmounted(() => {
    stopDrag();
});
</script>

<template>
    <div class="w-full flex-grow">
        <header>
            <MainNav :racineChildren="racineChildren" />
        </header>

        <div class="bg-gray-100 dark:bg-slate-600 min-h-screen">
            <div
                class="flex flex-col lg:flex-row flex-wrap items-start gap-6 p-4 mx-auto w-11/12 transition-all"
                :style="{
                    '--sidebar-w': sidebarWidth + 'px',
                    '--filter-w': filterWidth + 'px'
                }"
            >
                <aside
                    :class="[
                        sidebarActive
                            ? 'w-full lg:w-[var(--sidebar-w)] flex-shrink-0 relative  lg:top-4'
                            : 'w-full order-first'
                    ]"
                >
                    <SidebarWidget
                        name="Navigation"
                        :children="racineChildren"
                        v-model:isActive="sidebarActive"
                    />

                    <div
                        v-if="sidebarActive"
                        @mousedown.prevent="startDragSidebar"
                        class="hidden lg:block absolute -right-3 top-0 bottom-0 w-2 cursor-col-resize hover:bg-sky-500 rounded transition-colors z-10"
                    ></div>
                </aside>

                <main
                    class="bg-white dark:bg-slate-800 dark:text-white shadow-lg
                           rounded-lg pb-12 pt-2 px-2 min-h-[75vh] overflow-hidden flex-1 w-full min-w-0"
                >
                    <slot />
                </main>

                <aside
                    :class="[
                        filterActive
                            ? 'w-full lg:w-[var(--filter-w)] flex-shrink-0 relative lg:top-4'
                            : 'w-full order-first'
                    ]"
                >
                    <div
                        v-if="filterActive"
                        @mousedown.prevent="startDragFilter"
                        class="hidden lg:block absolute -left-3 top-0 bottom-0 w-2 cursor-col-resize hover:bg-sky-500 rounded transition-colors z-10"
                    ></div>

                    <FilterWidget
                        :groupes="groupes"
                        @filters-updated="handleFilterChange"
                        v-model:isActive="filterActive"
                    />
                </aside>

            </div>
        </div>
    </div>
</template>
