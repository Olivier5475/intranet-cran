<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { UserPlusIcon, UserMinusIcon } from '@heroicons/vue/20/solid';
import { ref, watch } from 'vue';
interface User {
    nom: string;
    prenom: string;
    email: string;
    role: string;
    id: number;
}

const props = defineProps<{
    users: User[];
}>();

// Fonction utilitaire pour colorer les rôles
const getRoleColor = (role: string) => {
    switch (role.toLowerCase()) {
        case 'admin':
            return 'text-red-500';
        case 'editeur':
            return 'text-yellow-500';
        case 'user':
        default:
            return 'text-green-500';
    }
};

const getHref = (id: number) => {
    return `/admin/users/${id}`;
};

const lastIndex = ref(props.users.length);
watch(() => props.users, () => {
    lastIndex.value = props.users.length;
});
</script>

<template>
    <div class="p-4 max-w-4xl mx-auto min-h-screen">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-6 text-center">Gestion des Utilisateurs</h1>

        <div class="shadow-2xl rounded-xl bg-white dark:bg-gray-800 overflow-hidden">
            <!-- Header de la grille -->
            <div class="bg-indigo-700 text-white font-bold text-sm tracking-wider p-4 shadow-md grid grid-cols-12 uppercase">
                <p class="sm:col-span-4 col-span-4">Nom Prénom</p>
                <p class="sm:col-span-4 col-span-4 truncate">Email</p>
                <p class="sm:col-span-2 col-span-2 text-right">Rôle</p>
                <p class="sm:col-span-2 col-span-2 text-right">Actions</p>
            </div>

            <!-- Lignes de données -->
            <div v-if="users.length === 0" class="p-6 text-gray-500 dark:text-gray-400 text-center">Aucun utilisateur trouvé.</div>

            <div v-else>
                <Link
                    v-for="(user, index) in users"
                    :key="user.email"
                    :href="getHref(user.id)"
                    class="p-4 text-sm ease-in-out dark:border-gray-700 grid cursor-pointer grid-cols-12 border-t transition duration-150"
                    :class="{
                        'bg-gray-50 dark:bg-gray-700 hover:bg-indigo-100 dark:hover:bg-indigo-900': index % 2 === 0,
                        'bg-white dark:bg-gray-900 hover:bg-indigo-100 dark:hover:bg-indigo-900': index % 2 !== 0,
                    }"
                >
                    <p class="sm:col-span-4 font-medium text-gray-800 dark:text-gray-200 col-span-4 truncate">{{ user.nom }} {{ user.prenom }}</p>

                    <p class="sm:col-span-4 text-gray-600 dark:text-gray-400 col-span-4 truncate">
                        {{ user.email }}
                    </p>

                    <p class="sm:col-span-2 font-semibold col-span-2 text-right" :class="getRoleColor(user.role)">
                        {{ user.role }}
                    </p>

                    <Link
                        :href="getHref(user.id)"
                        method="delete"
                        class="sm:col-span-2 end font-semibold col-span-2 text-right"
                    >
                        <UserMinusIcon class="w-8 text-red-600 ml-auto"></UserMinusIcon>
                    </Link>
                </Link>
                <Link
                    :href="`/admin/users/create`"
                    class="p-4 text-sm ease-in-out dark:border-gray-700 flex cursor-pointer grid-cols-12 border-t transition duration-150"
                    :class="
                        lastIndex % 2 !== 0
                            ? 'bg-white dark:bg-gray-900 hover:bg-indigo-100 dark:hover:bg-indigo-900'
                            : 'bg-gray-50 dark:bg-gray-700 hover:bg-indigo-100 dark:hover:bg-indigo-900'
                    "
                >
                    <UserPlusIcon class="w-8 mx-auto" />
                </Link>
            </div>
        </div>
    </div>
</template>
