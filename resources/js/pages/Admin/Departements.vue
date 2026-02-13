<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { PlusIcon, TrashIcon, PencilSquareIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import DepartementForm from '@/Components/Forms/DepartementForm.vue';

interface Departement {
    id: number;
    name: string;
    initials: string;
}

const props = defineProps<{
    departements: Departement[];
}>();

const showModal = ref(false);
const selectedDept = ref<Departement | null>(null);

const openCreate = () => { selectedDept.value = null; showModal.value = true; };
const openEdit = (dept: Departement) => { selectedDept.value = dept; showModal.value = true; };
const deleteDept = (id: number) => {
    if(confirm('Voulez-vous vraiment supprimer ce département ?')) {
        router.delete(`/admin/departements/${id}`);
    }
};
</script>

<template>
    <div class="p-6 max-w-5xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-black dark:text-white flex items-center gap-3">
                    <BuildingOfficeIcon class="w-8 h-8 text-sky-500" />
                    Départements
                </h1>
                <p class="text-zinc-500 text-sm mt-1">Gérez les entités et services de l'intranet</p>
            </div>

            <button
                @click="openCreate"
                class="flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-sky-500/20 active:scale-95"
            >
                <PlusIcon class="w-5 h-5 stroke-[3]" />
                <span class="font-bold">Nouveau département</span>
            </button>
        </div>

        <div v-if="departements.length === 0" class="text-center py-20 bg-zinc-50 dark:bg-zinc-900/50 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
            <BuildingOfficeIcon class="w-12 h-12 mx-auto text-zinc-300 mb-4" />
            <p class="text-zinc-500">Aucun département n'a encore été créé.</p>
        </div>

        <div v-else class="grid gap-4">
            <div
                v-for="dept in departements"
                :key="dept.id"
                class="group bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-zinc-100 dark:border-zinc-800 flex items-center justify-between hover:shadow-xl hover:border-sky-200 dark:hover:border-sky-900/50 transition-all duration-200"
            >
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-2xl flex items-center justify-center font-black text-lg tracking-tighter border border-zinc-200 dark:border-zinc-700 transition-colors group-hover:bg-sky-500 group-hover:text-white group-hover:border-sky-400">
                        {{ dept.initials }}
                    </div>

                    <div>
                        <h3 class="font-bold text-lg dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                            {{ dept.name }}
                        </h3>
                        <p class="text-xs text-zinc-400 uppercase tracking-widest font-semibold mt-0.5">Entité active</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
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

        <Modal
            :show="showModal"
            :title="selectedDept ? 'Modifier le département' : 'Créer un département'"
            @close="showModal = false"
        >
            <DepartementForm
                :departement="selectedDept"
                @success="showModal = false"
            />
        </Modal>
    </div>
</template>
