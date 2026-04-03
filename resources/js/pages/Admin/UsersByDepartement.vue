<script setup lang="ts">
import { User } from '@/types';
import { Departement } from '@/types/departement';
import { TrashIcon } from '@heroicons/vue/24/outline';
import dept_routes from '@/routes/admin/departements';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    departement: Departement;
    users: User[];
}>();

const removeUser = (user_id: number) => {
    if (confirm('Voulez-vous vraiment retirer cet utilisateur de ce departement ?')) {
        router.delete(dept_routes.users.remove.url([props.departement.id, user_id]));
    }
};
</script>

<template>
    <div>
        <div
            class="group p-5 rounded-2xl border-zinc-100 dark:border-zinc-800 hover:shadow-xl hover:border-sky-200 dark:hover:border-sky-900/50 flex items-center justify-between border transition-all duration-200"
        >
            <div class="gap-5 flex items-center">
                <div
                    class="w-14 h-14 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-2xl font-black text-lg tracking-tighter border-zinc-200 dark:border-zinc-700 group-hover:bg-sky-500 group-hover:text-white group-hover:border-sky-400 flex items-center justify-center border transition-colors"
                >
                    {{ departement.initials }}
                </div>

                <div>
                    <h3 class="text-lg dark:text-white">
                        Utilisateur du département
                        <span class="font-bold dark:group-hover:text-sky-400 group-hover:text-sky-600 transition-colors">{{ departement.name }}</span>
                    </h3>
                    <p class="text-xs text-zinc-400 tracking-widest font-semibold mt-0.5 uppercase">Entité active</p>
                </div>
            </div>
        </div>

        <div class="gap-4 mt-4 grid">
            <div
                v-for="user in users"
                :key="user.id"
                class="group bg-white dark:bg-zinc-900 p-4 rounded-2xl border-zinc-100 dark:border-zinc-800 hover:shadow-md flex items-center justify-between border transition-all"
            >
                <div class="gap-4 flex items-center">
                    <div class="w-12 h-12 bg-sky-100 dark:bg-sky-900/30 text-sky-600 font-bold text-xl flex items-center justify-center rounded-full">
                        {{ user.nom[0] }}
                    </div>
                    <div>
                        <p class="font-bold dark:text-white">{{ user.prenom }} {{ user.nom }}</p>
                        <p class="text-sm text-zinc-500">{{ user.email }}</p>
                    </div>
                </div>

                <div class="gap-6 flex items-center">
                    <span
                        class="px-3 py-1 text-xs font-bold tracking-widest mx-auto rounded-full uppercase"
                        :class="
                            user.role === 'admin'
                                ? 'bg-red-100 text-red-600'
                                : user.role === 'editeur'
                                  ? 'bg-yellow-100 text-yellow-600'
                                  : 'bg-emerald-100 text-emerald-600'
                        "
                    >
                        {{ user.role }}
                    </span>
                    <div class="gap-2 flex items-center opacity-0 transition-opacity group-hover:opacity-100">
                        <button
                            @click="removeUser(user.id)"
                            class="p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg text-zinc-400 hover:text-red-500"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
