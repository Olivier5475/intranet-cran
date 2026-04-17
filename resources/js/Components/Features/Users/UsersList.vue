<script setup lang="ts">
import { router } from '@inertiajs/vue3';

import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

import { User } from '@/types';
import { Departement } from '@/types/departement';

import user_route from '@/routes/admin/user'
import dept_routes from '@/routes/admin/departements';
import { ref } from 'vue';

const props =  defineProps<{
    users: User[]
    departement?: Departement
}>();

const emit = defineEmits(["selectedUser", "showModal"]);
const openEdit = (user: User) => {
    emit("selectedUser", user)
    emit("showModal", true)
};


const deleteUser = (user_id: number) => {
    if(props.departement) {
        if (confirm('Voulez-vous vraiment retirer cet utilisateur de ce departement ?')) {
            router.delete(dept_routes.users.remove.url([props.departement.id, user_id]));
        }
    } else {
        if (confirm('Supprimer ?')) router.delete(user_route.delete.url(user_id));
    }
};
</script>

<template>
    <div class="gap-4 grid">
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
                        @click="openEdit(user)"
                        class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg text-zinc-400 hover:text-sky-500"
                    >
                        <PencilSquareIcon class="w-5 h-5" />
                    </button>
                    <button
                        @click="deleteUser(user.id)"
                        class="p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg text-zinc-400 hover:text-red-500"
                    >
                        <TrashIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
