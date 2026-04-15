<script setup lang="ts">
// 1. Vue & Core
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { PlusIcon, TrashIcon, PencilSquareIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';

// 3. Types & Routes
import { Departement } from '@/types/departement';
import dept_routes from '@/routes/admin/departements';

// 4. Composants
import DepartementForm from '@/Components/Forms/DepartementForm.vue';
import Modal from '@/Components/UI/Modal.vue';

defineProps<{
    departements: Departement[];
}>();

const showModal = ref(false);
const selectedDept = ref<Departement | null>(null);

const openCreate = () => {
    selectedDept.value = null;
    showModal.value = true;
};
const openEdit = (dept: Departement) => {
    selectedDept.value = dept;
    showModal.value = true;
};
const deleteDept = (id: number) => {
    if (confirm('Voulez-vous vraiment supprimer ce département ?')) {
        router.delete(dept_routes.delete.url(id));
    }
};
</script>

<template>
    <div class="p-6 max-w-5xl mx-auto">
        <div class="sm:flex-row mb-10 gap-4 flex flex-col items-center justify-between">
            <div>
                <h1 class="text-3xl font-black dark:text-white gap-3 flex items-center">
                    <BuildingOfficeIcon class="w-8 h-8 text-sky-500" />
                    Départements
                </h1>
                <p class="text-zinc-500 text-sm mt-1">Gérez les entités et services de l'intranet</p>
            </div>

            <button
                @click="openCreate"
                class="gap-2 bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-sky-500/20 flex items-center transition-all active:scale-95"
            >
                <PlusIcon class="w-5 h-5 stroke-[3]" />
                <span class="font-bold">Nouveau département</span>
            </button>
        </div>

        <div
            v-if="departements.length === 0"
            class="py-20 bg-zinc-50 dark:bg-zinc-900/50 rounded-3xl border-zinc-200 dark:border-zinc-800 border-2 border-dashed text-center"
        >
            <BuildingOfficeIcon class="w-12 h-12 text-zinc-300 mb-4 mx-auto" />
            <p class="text-zinc-500">Aucun département n'a encore été créé.</p>
        </div>

        <div v-else class="gap-4 grid">
            <div
                v-for="dept in departements"
                :key="dept.id"
                class="group bg-white dark:bg-zinc-900 p-5 rounded-2xl border-zinc-100 dark:border-zinc-800 hover:shadow-xl hover:border-sky-200 dark:hover:border-sky-900/50 flex items-center justify-between border transition-all duration-200"
            >
                <Link class="gap-5 flex items-center" :href="dept_routes.users(dept.id)">
                    <div
                        class="w-14 h-14 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-2xl font-black text-lg tracking-tighter border-zinc-200 dark:border-zinc-700 group-hover:bg-sky-500 group-hover:text-white group-hover:border-sky-400 flex items-center justify-center border transition-colors"
                    >
                        {{ dept.initials }}
                    </div>

                    <div>
                        <h3 class="font-bold text-lg dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                            {{ dept.name }}
                        </h3>
                        <p class="text-xs text-zinc-400 tracking-widest font-semibold mt-0.5 uppercase">Entité active</p>
                    </div>
                </Link>

                <div class="gap-2 flex items-center">
                    <button
                        @click="openEdit(dept)"
                        class="p-2.5 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-xl text-zinc-400 hover:text-sky-600 transition-all"
                        title="Modifier"
                    >
                        <PencilSquareIcon class="w-6 h-6" />
                    </button>
                    <button
                        @click="deleteDept(dept.id)"
                        class="p-2.5 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl text-zinc-400 hover:text-red-500 transition-all"
                        title="Supprimer"
                    >
                        <TrashIcon class="w-6 h-6" />
                    </button>
                </div>
            </div>
        </div>

        <Modal :show="showModal" :title="selectedDept ? 'Modifier le département' : 'Créer un département'" @close="showModal = false">
            <DepartementForm :departement="selectedDept" @success="showModal = false" />
        </Modal>
    </div>
</template>
