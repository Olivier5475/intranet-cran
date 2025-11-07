<script setup lang="ts">
import RepertoryWidget from '@/Components/RepertoryWidget.vue';
import SearchBarWidget from '@/Components/SearchBarWidget.vue';
import DocumentWidget from '@/Components/DocumentWidget.vue';
import CreationWidget from '@/Components/CreationWidget.vue';
import { computed, DeepReadonly, inject, Ref, toRef } from 'vue';
import { useFilteredChildren } from '@/lib/filtres';

const props = defineProps<{
    children: Array<{
        id: number,
        name: string,
        type: string,
        color?: string,
        mimetype?: string,
        departements?: Array<{
            id: number,
            name: string,
            initials: string,
        }>;
        created_at: string,
    }>;
    parents: Array<{
        id: number,
        name: string,
    }>;
    currentSearch: string,
}>();

const folder_id = computed(() => {
    const lastParent = props.parents.at(-1);

    return lastParent ? lastParent.id : 0;
});
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
    <div class="flex justify-between">
        <RepertoryWidget :parents="parents" />
        <CreationWidget :folder_id="folder_id" />
    </div>
    <SearchBarWidget :currentSearch="currentSearch" />
    <div class="flex w-full flex-wrap">
        <DocumentWidget v-for="child in filteredChildren" :key="child.name" :child=child :folder_id=folder_id />
    </div>
</template>

<style scoped></style>
