<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { UserIcon,UserGroupIcon, BuildingOfficeIcon, ArrowLeftEndOnRectangleIcon } from '@heroicons/vue/20/solid';
import admin_routes from '@/routes/admin'
import {logout} from '@/routes';

defineProps<{
    racineChildren: Array<{
        id: number;
        name: string;
    }> | null;
}>();
const page = usePage();
const isActive = (url: string) => page.url.startsWith(url);
</script>

<template>
    <nav class="bg-slate-800/90 dark:bg-slate-900/95 border-b border-slate-600 dark:border-zinc-800 h-16 flex items-center justify-between px-8 shadow-lg">

        <div class="flex items-center space-x-2">
            <template v-if="page.props.auth.user.role === 'admin'">
                <Link
                    :href="admin_routes.departements.url()"
                    class="group flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-200"
                    :class="isActive(admin_routes.departements.url()) ? 'bg-red-500/10 text-red-400' : 'text-slate-300 hover:bg-slate-700'"
                >
                    <BuildingOfficeIcon class="w-6 h-6 transition-transform group-hover:-rotate-12" />
                    <span class="text-lg font-medium">Départements</span>
                </Link>

                <Link
                    :href="admin_routes.user.url()"
                    class="group flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-200"
                    :class="isActive(admin_routes.user.url()) ? 'bg-red-500/10 text-red-400' : 'text-slate-300 hover:bg-slate-700'"
                >
                    <UserGroupIcon class="w-6 h-6 transition-transform group-hover:scale-110" />
                    <span class="text-lg font-medium">Admin</span>
                </Link>

                <div class="h-8 w-[1px] bg-slate-600 mx-4"></div>
            </template>
        </div>

        <div class="flex items-center space-x-4">
            <div class="flex items-center gap-3 px-4 py-2 text-slate-200 bg-slate-700/50 dark:bg-zinc-800/50 rounded-full border border-slate-600/50">
                <span class="text-lg font-semibold tracking-tight">
                    {{ page.props.auth.user.prenom }} {{ page.props.auth.user.nom.charAt(0) }}.
                </span>
                <div class="p-1 bg-sky-500 rounded-full">
                    <UserIcon class="w-5 h-5 text-white" />
                </div>
            </div>

            <a
                :href="logout.url()"
                class="group flex items-center gap-2 px-4 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-red-600/20 transition-all duration-200"
            >
                <span class="text-lg font-medium">Déconnexion</span>
                <ArrowLeftEndOnRectangleIcon class="w-6 h-6 transition-transform group-hover:translate-x-1" />
            </a>
        </div>
    </nav>
</template>
