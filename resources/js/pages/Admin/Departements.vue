<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PlusCircleIcon, MinusCircleIcon } from '@heroicons/vue/20/solid';
//BuildingOfficeIcon
import { ref, watch } from 'vue';
interface Departement {
    id: number;
    name: string;
    initials: string;
}

const props = defineProps<{
    departements: Departement[];
}>();

const getHref = (id: number) => {
    return `/admin/departements/${id}`;
};

const lastIndex = ref(props.departements.length);
watch(() => props.departements, () => {
    lastIndex.value = props.departements.length;
});
</script>

<template>
    <div class="p-4 max-w-4xl mx-auto min-h-screen">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-6 text-center">Gestion des Utilisateurs</h1>

        <div class="shadow-2xl rounded-xl bg-white dark:bg-gray-800 overflow-hidden">
            <!-- Header de la grille -->
            <div class="bg-indigo-700 text-white font-bold text-sm tracking-wider p-4 shadow-md grid grid-cols-12 uppercase">
                <p class="sm:col-span-4 col-span-4">Initials</p>
                <p class="sm:col-span-4 col-span-4 truncate">Nom</p>
                <p class="sm:col-span-2 col-span-2 text-right">Actions</p>
            </div>

            <!-- Lignes de données -->
            <div v-if="departements.length === 0" class="p-6 text-gray-500 dark:text-gray-400 text-center">Aucun utilisateur trouvé.</div>

            <div v-else>
                <Link
                    v-for="(departement, index) in departements"
                    :key="departement.id"
                    :href="getHref(departement.id)"
                    class="p-4 text-sm ease-in-out dark:border-gray-700 grid cursor-pointer grid-cols-12 border-t transition duration-150"
                    :class="{
                        'bg-gray-50 dark:bg-gray-700 hover:bg-indigo-100 dark:hover:bg-indigo-900': index % 2 === 0,
                        'bg-white dark:bg-gray-900 hover:bg-indigo-100 dark:hover:bg-indigo-900': index % 2 !== 0,
                    }"
                >
                    <p class="sm:col-span-4 font-medium text-gray-800 dark:text-gray-200 col-span-4 truncate">{{ departement.initials }}</p>

                    <p class="sm:col-span-4 text-gray-600 dark:text-gray-400 col-span-4 truncate">
                        {{ departement.name }}
                    </p>

                    <Link
                        :href="getHref(departement.id)"
                        method="delete"
                        class="sm:col-span-2 end font-semibold col-span-2 text-right"
                    >
                        <MinusCircleIcon class="w-8 text-red-600 ml-auto"></MinusCircleIcon>
                    </Link>
                </Link>
                <Link
                    :href="`/admin/departements/create`"
                    class="p-4 text-sm ease-in-out dark:border-gray-700 flex cursor-pointer grid-cols-12 border-t transition duration-150"
                    :class="
                        lastIndex % 2 !== 0
                            ? 'bg-white dark:bg-gray-900 hover:bg-indigo-100 dark:hover:bg-indigo-900'
                            : 'bg-gray-50 dark:bg-gray-700 hover:bg-indigo-100 dark:hover:bg-indigo-900'
                    "
                >
                    <PlusCircleIcon class="w-8 mx-auto" />
                </Link>
            </div>
        </div>
    </div>
</template>
