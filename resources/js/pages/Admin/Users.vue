<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { UserPlusIcon, UserMinusIcon } from '@heroicons/vue/20/solid';
import { ref } from 'vue';
interface User {
    nom: string;
    prenom: string;
    email: string;
    role: string;
    id: number;
}

// Correction du typage : 'users' doit être un tableau d'objets User
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
    return `/admin/users/${id}`
}

const lastIndex = ref(props.users.length);
const updateLastIndex = function () {
    lastIndex.value -= 1;
}
</script>

<template>
    <div class="p-4 mx-auto max-w-4xl min-h-screen">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-6 text-center">Gestion des Utilisateurs</h1>

        <div class="shadow-2xl rounded-xl overflow-hidden bg-white dark:bg-gray-800">
            <!-- Header de la grille -->
            <div class="grid grid-cols-12 bg-indigo-700 text-white font-bold text-sm uppercase tracking-wider p-4 shadow-md">
                <p class="col-span-4 sm:col-span-4">Nom Prénom</p>
                <p class="col-span-4 sm:col-span-4 truncate">Email</p>
                <p class="col-span-2 sm:col-span-2 text-right">Rôle</p>
                <p class="col-span-2 sm:col-span-2 text-right">Actions</p>
            </div>

            <!-- Lignes de données -->
            <div v-if="users.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                Aucun utilisateur trouvé.
            </div>

            <div v-else>
                <Link v-for="(user, index) in users" :key="user.email"
                     :href="getHref(user.id)"
                     class="grid grid-cols-12 p-4 text-sm border-t transition duration-150 ease-in-out cursor-pointer dark:border-gray-700"
                     :class="{
                        'bg-gray-50 dark:bg-gray-700 hover:bg-indigo-100 dark:hover:bg-indigo-900': index % 2 === 0,
                        'bg-white dark:bg-gray-900 hover:bg-indigo-100 dark:hover:bg-indigo-900': index % 2 !== 0,
                     }">

                    <p class="col-span-4 sm:col-span-4 truncate font-medium text-gray-800 dark:text-gray-200">
                        {{ user.nom }} {{ user.prenom }}
                    </p>

                    <p class="col-span-4 sm:col-span-4 truncate text-gray-600 dark:text-gray-400">
                        {{ user.email }}
                    </p>

                    <p class="col-span-2 sm:col-span-2 text-right font-semibold" :class="getRoleColor(user.role)">
                        {{ user.role }}
                    </p>

                    <Link :href="getHref(user.id)" method="delete" @click="updateLastIndex()" class="col-span-2 sm:col-span-2 text-right end font-semibold">
                        <UserMinusIcon class="w-8 ml-auto text-red-600"></UserMinusIcon>
                    </Link>
                </Link>
                <Link href="/admin/users/create"
                      class="flex grid-cols-12 p-4 text-sm border-t transition duration-150 ease-in-out cursor-pointer dark:border-gray-700"
                      :class="lastIndex % 2 !== 0 ?
                    'bg-white dark:bg-gray-900 hover:bg-indigo-100 dark:hover:bg-indigo-900' :
                    'bg-gray-50 dark:bg-gray-700 hover:bg-indigo-100 dark:hover:bg-indigo-900'
                    "> <UserPlusIcon class="w-8 mx-auto" /> </Link>
            </div>
        </div>
    </div>
</template>
