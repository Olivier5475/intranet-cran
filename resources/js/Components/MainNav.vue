<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { UserGroupIcon, BuildingOfficeIcon } from '@heroicons/vue/20/solid';
import editor from '@/routes/editor/index.js';
import admin_routes from '@/routes/admin/index.js'
import navigate from '@/routes/navigate/index.js'

defineProps<{
    racineChildren: Array<{
        id: number;
        name: string;
    }> | null;
}>();
const page = usePage();
</script>

<template>
    <nav class="bg-slate-700 dark:bg-slate-800 h-16 flex">
        <ul class="ml-10 space-x-1 px-2 container flex flex-wrap items-center">
            <li v-for="child in racineChildren" :key="child.id">
                <Link
                    :href="navigate.folder.url(child.id)"
                    class="px-3 rounded-md font-medium text-sm text-white hover:text-white active:text-white block"
                >
                    {{ child.name }}
                </Link>
            </li>

            <li>
                <Link
                    class="text-lg font-extrabold px-3 py-1 bg-gray-400 dark:bg-slate-600 dark:text-slate-400 rounded-full"
                    :href="editor.folder.create.url(0)"
                >
                    +
                </Link>
            </li>
        </ul>
        <div class="mr-10 my-auto flex items-end">
            <Link v-if="page.props.auth.user.role === 'admin'" :href="admin_routes.departements.url()" class="text-white gap-2 mr-8 flex">
                <span class="text-2xl">Departements</span>
                <BuildingOfficeIcon class="w-8"></BuildingOfficeIcon>
            </Link>
            <Link
                v-if="page.props.auth.user.role === 'admin'"
                :href="admin_routes.user.url()"
                class="text-white gap-2 mr-8 flex">
                <span class="text-2xl">Admin</span>
                <UserGroupIcon class="w-8"></UserGroupIcon>
            </Link>
        </div>
    </nav>
</template>
