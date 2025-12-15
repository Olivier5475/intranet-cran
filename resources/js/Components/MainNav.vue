<script setup lang="ts">
import { ref } from 'vue'; // On importe 'ref' pour gérer l'état (menu ouvert/fermé)
import { Link, usePage } from '@inertiajs/vue3';
import { UserGroupIcon } from '@heroicons/vue/20/solid';
import UserMenu from '@/Components/UserMenu.vue'; // Une icône pour le dropdown

const page = usePage();

// --- Préparation pour le dropdown ---
// On crée une variable qui stockera le nom du menu ouvert (null si aucun)
const activeDropdown = ref(null);

const toggleDropdown = (name) => {
    activeDropdown.value = activeDropdown.value === name ? null : name;
};

// ------------------------------------

defineProps<{
    racineChildren : Array<{
        id : number,
        name : string,
    }>
}>();

</script>

<template>
    <nav class="bg-gray-800 flex">
        <ul class="container mx-auto flex flex-wrap items-center space-x-1 p-2">

            <li v-for="child in racineChildren" :key=child.id>

                <Link
                    :href="'/navigation/'+child.id"
                    class="
                        block px-3 py-2 rounded-md font-medium text-sm
                        text-white hover:text-white active:text-white
                    "
                > {{ child.name }} </Link>

            </li>

            <li>
                <a class="rounded-full text-lg font-extrabold px-3 py-1 bg-gray-400 dark:bg-slate-600 dark:text-slate-400" href="/navigation/0/admin/folders/create">+</a>
            </li>
        </ul>
        <div class="items-end">
            <Link href="/admin/users" class="text-white flex gap-2 mt-4 mr-8"> <span class="text-2xl">Admin</span> <UserGroupIcon class="w-8"></UserGroupIcon> </Link>
            <UserMenu/>
        </div>


    </nav>
</template>
