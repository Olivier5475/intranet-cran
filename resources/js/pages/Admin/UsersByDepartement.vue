<script setup lang="ts">

// 1. Vue & Core
import { ref } from 'vue';

// 2. Types
import { User } from '@/types';
import { Departement } from '@/types/departement';

// 3. Librairies Tierces (Icons)
import { UserPlusIcon } from '@heroicons/vue/24/outline';

// 5. Composants
import Modal from '@/Components/UI/Modal.vue';
import UsersList from '@/Components/Features/Users/UsersList.vue';
import AddUserToDepartement from '@/Components/Features/Departement/AddUserToDepartement.vue';
import UserForm from '@/Components/Forms/UserForm.vue';

defineProps<{
    departements: Departement[];
    departement: Departement;
    othersUsers: User[]
}>();

const selectedUser = ref();
const showModalAddUser = ref(false);
const showModalEditUserUser = ref(false);

const openAddUser = () => {
    showModalAddUser.value = true;
};

</script>

<template>
    <div>
        <div
            class="group p-5 rounded-2xl border-zinc-100 dark:border-zinc-800 hover:shadow-xl
            hover:border-sky-200 dark:hover:border-sky-900/50 flex items-center
            justify-between border transition-all duration-200"
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
            <button
                @click="openAddUser"
                class="gap-2 bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl
                shadow-lg shadow-sky-500/20 flex items-center transition-all active:scale-95"
            >
                <UserPlusIcon class="w-5 h-5 stroke-[3]" />
                <span class="font-bold">Ajouter un utilisateur</span>
            </button>
        </div>

        <UsersList
            v-if="departement.users"
            :users="departement.users"
            @selected-user="selectedUser = $event"
            @show-modal="showModalEditUserUser = $event"
            :departement="departement"
        />

        <Modal
            :show="showModalAddUser"
            :title="'Ajouter un utilisateur au departement ' + departement.initials"
            @close="showModalAddUser = false"
        >
            <AddUserToDepartement
                :departement="departement"
                :users="othersUsers"
                @success="showModalAddUser = false"
            />
        </Modal>
        <Modal
            :show="showModalEditUserUser"
            :title="'Ajouter un utilisateur au departement ' + departement.initials"
            @close="showModalEditUserUser = false"
        >
            <UserForm
                :user="selectedUser"
                :departements="departements"
                @success="showModalEditUserUser = false"
            />
        </Modal>
    </div>
</template>
