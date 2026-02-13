<script setup lang="ts">
import { ref } from 'vue';
import { UserPlusIcon, TrashIcon, PencilSquareIcon, UserGroupIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import UserForm from '@/Components/Forms/UserForm.vue'; // Ton formulaire refait
import { router } from '@inertiajs/vue3';
import user from '@/routes/admin/user';
import { User } from '@/types';
defineProps<{ users: any[]; departements: any[] }>();

const showModal = ref(false);
const selectedUser = ref();
selectedUser.value = null;

const openCreate = () => {
    selectedUser.value = null;
    showModal.value = true;
};
const openEdit = (user: User) => {
    selectedUser.value = user;
    showModal.value = true;
};
const deleteUser = (id: number) => {
    if (confirm('Supprimer ?')) router.delete(user.delete.url(id));
};
</script>

<template>
    <div class="p-6 max-w-6xl mx-auto">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black dark:text-white flex items-center gap-3">
                    <UserGroupIcon class="w-8 h-8 text-sky-500" />
                    Utilisateurs
                </h1>
                <p class="text-zinc-500 text-sm mt-1">Gérez les entités et services de l'intranet</p>
            </div>
            <button
                @click="openCreate"
                class="gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-xl shadow-lg shadow-sky-500/20 flex items-center transition-all"
            >
                <UserPlusIcon class="w-5 h-5" />
                Nouveau
            </button>
        </div>

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

        <Modal :show="showModal" :title="selectedUser ? 'Modifier l\'utilisateur' : 'Créer un utilisateur'" @close="showModal = false">
            <UserForm :user="selectedUser" :departements="departements" @success="showModal = false" />
        </Modal>
    </div>
</template>
