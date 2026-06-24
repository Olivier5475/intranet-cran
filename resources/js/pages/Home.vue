<script setup lang="ts">
import SearchBarWidget from '@/Components/Features/SearchBarWidget.vue';
import { Child } from '@/types/child';
import { Folder } from '@/types/folder';
// import CreateFolderCardWidget from '@/Components/Features/Navigation/Creation/CreateFolderCardWidget.vue';
import { computed, DeepReadonly, inject, ref, Ref, toRef } from 'vue';
import { useFilteredChildren } from '@/Composables/useFiltres';
import { FilterState } from '@/types/filtres';
import HomeCard from '@/Components/Features/Home/HomeCard.vue';
// import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    // children?: Array<Child>;
    racineChildren: Array<Child>;
    favorites: Array<Child>;
    parents: Folder[];
    currentSearch?: string;
    isArchived: boolean;
}>();

const combinedChildren = computed(() => {
    const merged = [...props.favorites, ...props.racineChildren];

    const uniqueItems = new Map();
    merged.forEach(item => {
        // On utilise le type et l'id comme clé unique pour filtrer les doublons
        uniqueItems.set(`${item.type}-${item.id}`, item);
    });

    return Array.from(uniqueItems.values());
});

const filters = inject<DeepReadonly<Ref<FilterState>>>("activeFilters");

// 2. On passe notre computed directement au composable
const filteredChildren = useFilteredChildren(
    combinedChildren,
    filters as Ref<FilterState | null>,
    toRef("create")
)

// SUPPRESSION DE LA LOGIQUE JAVASCRIPT DES COLONNES
// (sidebarActive, filterActive et gridColsClass ont été retirés car le CSS gère tout)

// const user = usePage().props.auth.user;
// const canEdit = user.role == "admin";

</script>

<template>
    <div class="flex">
        <SearchBarWidget
            class="mt-4"
            :currentSearch="currentSearch"
            placeholder="Rechercher un fichier, un document..."
        />
    </div>

    <div
        class="mt-6 gap-4 grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] transition-all duration-300"
    >
        <HomeCard
            v-for="child in filteredChildren"
            :key="child.name"
            :child="child"
            :folder_id="0"
            :favorites="favorites"
        />
    </div>
</template>

<style scoped>

</style>
