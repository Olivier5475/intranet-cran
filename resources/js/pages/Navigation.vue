<script setup lang="ts">
import RepertoryWidget from '@/Components/RepertoryWidget.vue';
import SearchBarWidget from '@/Components/SearchBarWidget.vue';
import DocumentWidget from '@/Components/DocumentWidget.vue';
import CreationWidget from '@/Components/CreationWidget.vue';
import DisplayModWidget from '@/Components/DisplayModWidget.vue';
import { ListBulletIcon, ViewColumnsIcon } from '@heroicons/vue/20/solid';

import { computed, DeepReadonly, inject, ref, Ref, toRef } from 'vue';
import { useFilteredChildren } from '@/lib/filtres';
import DocumentListWidget from '@/Components/DocumentListWidget.vue';

const props = defineProps<{
    children: Array<{
        id: number;
        name: string;
        type: string;
        color?: string;
        mimetype?: string;
        departements?: Array<{
            id: number;
            name: string;
            initials: string;
        }>;
        created_at: string;
    }>;
    parents: Array<{
        id: number;
        name: string;
    }>;
    currentSearch: string;
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

const view_mod = ref('list');

const change_mod = (value: string) => {
    view_mod.value = value;
}
</script>

<template>
    <div class="flex justify-between">
        <RepertoryWidget :parents="parents" />
        <div class="flex">
            <ListBulletIcon  @click="change_mod('list')" class="w-5 mr-2 hover:cursor-pointer" />
            <ViewColumnsIcon @click="change_mod('icon')" class="w-5 hover:cursor-pointer" />
        </div>
        <CreationWidget :folder_id="folder_id" />
    </div>
    <SearchBarWidget class="mt-2" :currentSearch="currentSearch" />
    <div v-if="view_mod == 'icon'" class="mt-4 flex w-full flex-wrap">
        <DocumentWidget v-for="child in filteredChildren" :key="child.name" :child="child" :folder_id="folder_id" />
    </div>
    <div v-else-if="view_mod == 'list'" class="mt-4 w-full">
        <div class="grid grid-cols-12 pb-1">
            <p class="col-span-4 text-center">Nom</p>
            <p class="col-span-3 text-center">Type</p>
            <p class="col-span-3 text-center">Date de création</p>
            <p class="col-span-2 text-center">Action</p>
        </div>
        <DocumentListWidget v-for="child in filteredChildren" :key="child.name" :child="child" :folder_id="folder_id" />
    </div>
</template>

<style scoped></style>
