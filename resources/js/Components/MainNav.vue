<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { UserGroupIcon, BuildingOfficeIcon } from '@heroicons/vue/20/solid';
import UserMenu from '@/Components/UserMenu.vue'; // Une icône pour le dropdown

defineProps<{
    racineChildren : Array<{
        id : number,
        name : string,
    }> | null;
}>();

</script>

<template>
    <nav class="bg-gray-800 flex h-16">
        <ul class="container ml-10 flex flex-wrap items-center space-x-1 px-2">

            <li v-for="child in racineChildren" :key=child.id>

                <Link
                    :href="'/navigation/'+child.id"
                    class="
                        block px-3 rounded-md font-medium text-sm
                        text-white hover:text-white active:text-white
                    "
                > {{ child.name }} </Link>

            </li>

            <li>
                <Link class="rounded-full text-lg font-extrabold px-3 py-1 bg-gray-400 dark:bg-slate-600 dark:text-slate-400" :href="`/navigation/0/admin/folders/create`">+</Link>
            </li>
        </ul>
        <div class="items-end flex my-auto mr-10">

            <Link :href="`/admin/departements`" class="text-white flex gap-2 mr-8"> <span class="text-2xl">Departements</span> <BuildingOfficeIcon class="w-8"></BuildingOfficeIcon> </Link>
            <Link :href="`/admin/users`" class="text-white flex gap-2 mr-8"> <span class="text-2xl">Admin</span> <UserGroupIcon class="w-8"></UserGroupIcon> </Link>
            <UserMenu/>
        </div>


    </nav>
</template>
