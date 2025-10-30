<script setup lang="ts">
import RepertoryWidget from '@/Components/RepertoryWidget.vue';
import SearchBarWidget from '@/Components/SearchBarWidget.vue';
import DocumentWidget from '@/Components/DocumentWidget.vue';
import { DeepReadonly, inject, Ref, toRef } from 'vue';
import { useFilteredChildren } from '@/lib/filtres';

const props = defineProps<{
    children: Array<{
        id: number,
        name: string,
        type: string,
        departements?: Array<{
            id: number,
            name: string,
            initials: string,
        }>,
        created_at: string,
    }>,
    parents: Array<{
        id: number,
        name: string,
    }>,
    currentSearch: string,
}>();

interface FilterState {
    startDate?: string | null;
    endDate?: string | null;
    fileType?: string;
    sortBy?: string;
    selectedDepartments?: number[];
}

const filters = inject<DeepReadonly<Ref<FilterState>>>('activeFilters');

const filteredChildren = useFilteredChildren(
    toRef(props, 'children'), // Convertit la prop en Ref réactive
    filters as Ref<FilterState | null>, // On force le type pour le composable
);
</script>

<template>
    <RepertoryWidget :parents=parents></RepertoryWidget>
    <SearchBarWidget :currentSearch=currentSearch />
    <div class="flex w-full flex-wrap">
        <DocumentWidget v-for="child in filteredChildren" :key=child.name :child=child />
    </div>
</template>

<style scoped></style>
