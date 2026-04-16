<script setup lang="ts">
// 1. Vue & Core
import { ref } from 'vue';

// 2. Librairies tierces (Icônes)
import {
    UserPlusIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline';

// 3. Types & Routes
import { User } from '@/types';
import { Departement } from '@/types/departement';

// 4. Composants
import Modal from '@/Components/UI/Modal.vue';
import UserForm from '@/Components/Forms/UserForm.vue';
import SearchBarWidget from '@/Components/Features/SearchBarWidget.vue';
import UsersList from '@/Components/Features/Users/UsersList.vue';

defineProps<{
    users: User[];
    departements: Departement[];
    currentSearch: string;
}>();

const showModal = ref(false);
const selectedUser = ref();
selectedUser.value = null;

const openCreate = () => {
    selectedUser.value = null;
    showModal.value = true;
};

</script>

<template>
    <div
        class="p-6 max-w-6xl mx-auto"
    >
        <div
            class="mb-8 flex items-center justify-between"
        >
            <div>
                <h1 class="text-3xl font-black dark:text-white gap-3 flex items-center">
                    <UserGroupIcon class="w-8 h-8 text-sky-500" />
                    Utilisateurs
                </h1>
                <p class="text-zinc-500 text-sm mt-1">Gérez les entités et services de l'intranet</p>
            </div>
            <button
                @click="openCreate"
                class="gap-2 bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl
                shadow-lg shadow-sky-500/20 flex items-center transition-all active:scale-95"
            >
                <UserPlusIcon class="w-5 h-5 stroke-[3]" />
                <span class="font-bold">Créer un utilisateur</span>
            </button>
        </div>

        <SearchBarWidget :current-search="currentSearch" placeholder="Rechercher un utilisateur..." class="my-3"></SearchBarWidget>

        <UsersList
            :users="users"
            @selected-user="selectedUser = $event"
            @show-modal="showModal = $event"
        />

        <Modal :show="showModal" :title="selectedUser ? 'Modifier l\'utilisateur' : 'Créer un utilisateur'" @close="showModal = false">
            <UserForm
                :user="selectedUser"
                :departements="departements"
                @success="showModal = false"
            />
        </Modal>
    </div>
</template>
