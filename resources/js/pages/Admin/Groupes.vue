<script setup lang="ts">
// 1. Vue & Core
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { PlusIcon, TrashIcon, PencilSquareIcon, BuildingOfficeIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';

// 3. Types & Routes
import { Groupe } from '@/types/groupe';
import grp_routes from '@/routes/admin/groupes';

// 4. Composants
import GroupeForm from '@/Components/Forms/GroupeForm.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps<{
    groupes: Groupe[];
}>();

const showModal = ref(false);
const selectedGrp = ref<Groupe | null>(null);

// Gestion de l'ouverture des sous-groupes
const expandedGrps = ref<number[]>([]);

const toggleExpand = (id: number) => {
    const index = expandedGrps.value.indexOf(id);
    if (index > -1) {
        expandedGrps.value.splice(index, 1);
    } else {
        expandedGrps.value.push(id);
    }
};

// Fonction pour récupérer les objets Groupes à partir de leurs IDs
const getChildren = (childrenIds: number[] | undefined) => {
    if(childrenIds == undefined) return;
    return props.groupes.filter(g => childrenIds.includes(g.id));
};

const openCreate = () => {
    selectedGrp.value = null;
    showModal.value = true;
};
const openEdit = (grp: Groupe) => {
    selectedGrp.value = grp;
    showModal.value = true;
};
const deleteGrp = (id: number) => {
    if (confirm('Voulez-vous vraiment supprimer ce groupe ?')) {
        router.delete(grp_routes.delete.url(id));
    }
};

const racineGroupes = computed(() => {
    if (!props.groupes) return props.groupes;
    return props.groupes.filter(grp => grp.parent == null);
})
</script>

<template>
    <div class="p-6 max-w-5xl mx-auto">
        <div class="sm:flex-row mb-10 gap-4 flex flex-col items-center justify-between">
            <div>
                <h1 class="text-3xl font-black dark:text-white gap-3 flex items-center">
                    <BuildingOfficeIcon class="w-8 h-8 text-sky-500" />
                    Groupes
                </h1>
                <p class="text-zinc-500 text-sm mt-1">Gérez les entités et services de l'intranet</p>
            </div>

            <button
                @click="openCreate"
                class="gap-2 bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-sky-500/20 flex items-center transition-all active:scale-95"
            >
                <PlusIcon class="w-5 h-5 stroke-[3]" />
                <span class="font-bold">Nouveau groupe</span>
            </button>
        </div>

        <div
            v-if="groupes.length === 0"
            class="py-20 bg-zinc-50 dark:bg-zinc-900/50 rounded-3xl border-zinc-200 dark:border-zinc-800 border-2 border-dashed text-center"
        >
            <BuildingOfficeIcon class="w-12 h-12 text-zinc-300 mb-4 mx-auto" />
            <p class="text-zinc-500">Aucun groupe n'a encore été créé.</p>
        </div>

        <div v-else class="gap-4 grid">
            <!-- Boucle principale sur les groupes racines -->
            <div v-for="grp in racineGroupes" :key="grp.id" class="flex flex-col gap-3">

                <!-- Carte du groupe Parent -->
                <div
                    class="group bg-white dark:bg-zinc-900 p-5 rounded-2xl border-zinc-100 dark:border-zinc-800 hover:shadow-xl hover:border-sky-200 dark:hover:border-sky-900/50 flex items-center justify-between border transition-all duration-200"
                >
                    <Link class="gap-5 flex items-center" :href="grp_routes.users(grp.id)">
                        <div
                            class="w-14 h-14 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-2xl font-black text-lg tracking-tighter border-zinc-200 dark:border-zinc-700 group-hover:bg-sky-500 group-hover:text-white group-hover:border-sky-400 flex items-center justify-center border transition-colors"
                        >
                            {{ grp.initials }}
                        </div>

                        <div>
                            <h3 class="font-bold text-lg dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                                {{ grp.name }}
                            </h3>
                            <p class="text-xs text-zinc-400 tracking-widest font-semibold mt-0.5 uppercase">Entité active</p>
                        </div>
                    </Link>

                    <div class="gap-2 flex items-center">
                        <!-- Bouton pour ouvrir les sous-groupes (s'il y en a) -->
                        <button
                            v-if="grp.children && grp.children.length > 0"
                            @click="toggleExpand(grp.id)"
                            class="px-3 py-2 bg-sky-50 dark:bg-sky-900/20 text-sky-600 dark:text-sky-400 rounded-xl font-bold text-sm flex items-center gap-2 hover:bg-sky-100 transition-colors"
                        >
                            {{ grp.children.length }} sous-groupe(s)
                            <ChevronDownIcon
                                class="w-4 h-4 transition-transform duration-200"
                                :class="expandedGrps.includes(grp.id) ? 'rotate-180' : ''"
                            />
                        </button>

                        <button
                            @click="openEdit(grp)"
                            class="p-2.5 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-xl text-zinc-400 hover:text-sky-600 transition-all"
                            title="Modifier"
                        >
                            <PencilSquareIcon class="w-6 h-6" />
                        </button>
                        <button
                            @click="deleteGrp(grp.id)"
                            class="p-2.5 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl text-zinc-400 hover:text-red-500 transition-all"
                            title="Supprimer"
                        >
                            <TrashIcon class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Menu déroulant des sous-groupes (Enfants) -->
                <div v-if="expandedGrps.includes(grp.id)" class="pl-12 flex flex-col gap-3">
                    <div
                        v-for="child in getChildren(grp.children)"
                        :key="child.id"
                        class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-100 dark:border-zinc-700/50 flex items-center justify-between"
                    >
                        <Link class="gap-4 flex items-center" :href="grp_routes.users(child.id)">
                            <div class="w-10 h-10 bg-white dark:bg-zinc-700 text-zinc-500 rounded-lg font-bold text-sm border-zinc-200 flex items-center justify-center border">
                                {{ child.initials }}
                            </div>
                            <h4 class="font-bold text-zinc-700 dark:text-zinc-300 hover:text-sky-600 transition-colors">
                                {{ child.name }}
                            </h4>
                        </Link>

                        <div class="gap-2 flex items-center">
                            <button @click="openEdit(child)" class="p-2 hover:bg-white dark:hover:bg-zinc-700 rounded-lg text-zinc-400 hover:text-sky-600">
                                <PencilSquareIcon class="w-5 h-5" />
                            </button>
                            <button @click="deleteGrp(child.id)" class="p-2 hover:bg-white dark:hover:bg-zinc-700 rounded-lg text-zinc-400 hover:text-red-500">
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Modal :show="showModal" :title="selectedGrp ? 'Modifier le groupe' : 'Créer un groupe'" @close="showModal = false">
            <GroupeForm
                :groupes="groupes"
                :groupe="selectedGrp"
                @success="showModal = false"
            />
        </Modal>
    </div>
</template>
