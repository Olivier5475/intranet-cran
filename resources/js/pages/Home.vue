<script setup lang="ts">
import SearchBarWidget from '@/Components/Features/SearchBarWidget.vue';
import { Child } from '@/types/child';
import { Folder } from '@/types/folder';
// import CreateFolderCardWidget from '@/Components/Features/Navigation/Creation/CreateFolderCardWidget.vue';
import { computed, DeepReadonly, inject, ref, Ref } from 'vue';
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
)

// Récupération réactive de l'état des barres latérales du Layout principal
const sidebarActive = inject<Ref<boolean>>('sidebarActive', ref(true));
const filterActive = inject<Ref<boolean>>('filterActive', ref(true));
// const fastFolderCreation = ref(false);

const gridColsClass = computed(() => {
    if (sidebarActive.value && filterActive.value) {
        return 'lg:grid-cols-6'; // 6 colonnes si tout est déplié
    }
    if (!sidebarActive.value && !filterActive.value) {
        return 'lg:grid-cols-8'; // 8 colonnes si tout est fermé
    }
    return 'lg:grid-cols-7'; // 7 colonnes si un seul des deux est fermé
});

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
        class="mt-6 gap-4 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 transition-all duration-300"
        :class="gridColsClass"
    >
        <HomeCard
            v-for="child in filteredChildren"
            :key="child.name"
            :child="child"
            :folder_id="0"
            :favorites="favorites"
        />
<!--        <CreateFolderCardWidget-->
<!--            v-if="fastFolderCreation && canEdit"-->
<!--            :parent="parents.at(-1)"-->
<!--            v-model="fastFolderCreation"-->
<!--            :folder_id="0"-->
<!--        />-->
    </div>
</template>

<style scoped>

</style>
