<script setup lang="ts">
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { CheckIcon, ExclamationTriangleIcon } from "@heroicons/vue/24/solid";
import { ChevronDownIcon } from "@heroicons/vue/24/outline";
import { Groupe } from "@/types/groupe";
import { User } from '@/types';

const props = defineProps<{
    allGroupes: Groupe[];
    editors?: User[]
}>();

// Le v-model automatique lié au tableau du parent
const selectedGrp = defineModel<number[]>({ default: [] });

const page = usePage();
const user = page.props.auth.user as User; // Typage strict de l'utilisateur
const userGroupeIds = user.groupes as number[];
const allGroupesIds = props.allGroupes.map((d) => d.id);

// --- 1. FONCTIONS UTILITAIRES ---

const racineGroupes = computed(() => {
    return props.allGroupes.filter(grp => grp.parent === null || grp.parent === undefined);
});

const getChild = (id: number): Groupe | undefined => {
    return props.allGroupes.find(g => g.id === id);
};

// Empêcher de se désélectionner de son propre service
const isCheckboxDisabled = (groupeId: number): boolean => {
    let mySelectedGrp: number[] = [];

    if (user.role === "admin" || user.role === "superadmin") {
        if (!allGroupesIds.includes(groupeId)) return false;
        mySelectedGrp = selectedGrp.value.filter((id) => allGroupesIds.includes(id));
    } else {
        if (!userGroupeIds.includes(groupeId)) return false;
        mySelectedGrp = selectedGrp.value.filter((id) => userGroupeIds.includes(id));
    }

    return selectedGrp.value.includes(groupeId) && mySelectedGrp.length <= 1;
};

// --- 2. GESTION DES CLICS ET ACCORDÉON ---

const expandedGrps = ref<number[]>([]);

const toggleExpand = (id: number) => {
    const index = expandedGrps.value.indexOf(id);
    if (index > -1) {
        expandedGrps.value.splice(index, 1);
    } else {
        expandedGrps.value.push(id);
    }
};

const toggleParent = (grp: Groupe) => {
    if (isCheckboxDisabled(grp.id)) return;

    const isSelected = selectedGrp.value.includes(grp.id);
    const childrenIds = grp.children || []; // Sécurise si children est null

    if (isSelected) {
        // ON DÉCOCHE : On retire le parent et ses enfants
        const idsToRemove = [grp.id, ...childrenIds];
        selectedGrp.value = selectedGrp.value.filter(id => !idsToRemove.includes(id));
    } else {
        // ON COCHE : On ajoute le parent et ses enfants
        const idsToAdd = [grp.id, ...childrenIds];
        selectedGrp.value = Array.from(new Set([...selectedGrp.value, ...idsToAdd]));
    }
};

const toggleChild = (childId: number, parentId: number) => {
    if (isCheckboxDisabled(childId)) return;

    const isSelected = selectedGrp.value.includes(childId);

    if (isSelected) {
        // ON DÉCOCHE L'ENFANT : On retire l'enfant ET le parent
        selectedGrp.value = selectedGrp.value.filter(id => id !== childId && id !== parentId);
    } else {
        // ON COCHE L'ENFANT
        selectedGrp.value.push(childId);
    }
};

// --- 3. FILTRE DES ÉDITEURS ---

const impactedEditors = computed(() => {
    if (!props.editors) return [];

    return props.editors.filter((editor) =>
        editor.groupes.some((gId) => selectedGrp.value.includes(gId))
    );
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <label class="font-black text-gray-400 ml-1 mb-4 block text-center text-[10px] tracking-[0.2em] uppercase">
                Groupes ayant accès
            </label>

            <div class="sm:grid-cols-2 lg:grid-cols-3 gap-4 grid grid-cols-1 items-start">

                <div v-for="grp in racineGroupes" :key="grp.id" class="flex flex-col gap-2">

                    <!-- 🚀 L'ajout de .stop.prevent bloque l'ouverture accidentelle de ton dossier -->
                    <div
                        @click.stop.prevent="toggleParent(grp)"
                        :class="[
                            'p-4 rounded-2xl group relative flex cursor-pointer items-center border-2 transition-all',
                            selectedGrp.includes(grp.id)
                                ? 'border-sky-500 bg-sky-500/5'
                                : 'border-gray-100 dark:border-zinc-800 hover:border-gray-200 dark:hover:border-zinc-700',
                            isCheckboxDisabled(grp.id) ? 'cursor-not-allowed opacity-40' : '',
                        ]"
                    >
                        <span
                            :class="[
                                'w-5 h-5 rounded-lg mr-3 flex items-center justify-center border transition-colors shrink-0',
                                selectedGrp.includes(grp.id)
                                    ? 'bg-sky-500 border-sky-500'
                                    : 'border-gray-300 dark:border-zinc-600',
                            ]"
                        >
                            <CheckIcon v-if="selectedGrp.includes(grp.id)" class="w-3.5 h-3.5 text-white stroke-[3]" />
                        </span>

                        <span
                            class="text-sm font-bold tracking-tight truncate pr-2"
                            :class="selectedGrp.includes(grp.id) ? 'text-sky-700 dark:text-sky-400' : 'text-gray-500 dark:text-zinc-400'"
                        >
                            {{ grp.name }}
                        </span>

                        <button
                            v-if="grp.children && grp.children.length > 0"
                            @click.stop.prevent="toggleExpand(grp.id)"
                            class="ml-auto p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                        >
                            <ChevronDownIcon
                                class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                :class="expandedGrps.includes(grp.id) ? 'rotate-180' : ''"
                            />
                        </button>
                    </div>

                    <div v-if="expandedGrps.includes(grp.id)" class="pl-4 flex flex-col gap-2 border-l-2 border-gray-100 dark:border-zinc-800 ml-6">
                        <!-- 🚀 Pareil ici, .stop.prevent sur les enfants -->
                        <div
                            v-for="childId in grp.children"
                            :key="childId"
                            @click.stop.prevent="toggleChild(childId, grp.id)"
                            :class="[
                                'p-3 rounded-xl group relative flex cursor-pointer items-center border transition-all',
                                selectedGrp.includes(childId)
                                    ? 'border-sky-300 bg-sky-50 dark:bg-sky-900/20 dark:border-sky-800'
                                    : 'border-transparent hover:bg-gray-50 dark:hover:bg-zinc-800/50',
                                isCheckboxDisabled(childId) ? 'cursor-not-allowed opacity-40' : '',
                            ]"
                        >
                            <span
                                :class="[
                                    'w-4 h-4 rounded-md mr-3 flex items-center justify-center border transition-colors shrink-0',
                                    selectedGrp.includes(childId)
                                        ? 'bg-sky-500 border-sky-500'
                                        : 'border-gray-300 dark:border-zinc-600',
                                ]"
                            >
                                <CheckIcon v-if="selectedGrp.includes(childId)" class="w-3 h-3 text-white stroke-[3]" />
                            </span>

                            <span
                                class="text-xs font-bold tracking-tight truncate"
                                :class="selectedGrp.includes(childId) ? 'text-sky-700 dark:text-sky-400' : 'text-gray-500 dark:text-zinc-400'"
                            >
                                {{ getChild(childId)?.name }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="impactedEditors && impactedEditors.length > 0"
                class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4 flex gap-3 items-start shadow-sm"
            >
                <ExclamationTriangleIcon class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
                <div class="text-sm text-amber-800 dark:text-amber-400">
                    <p class="font-bold mb-1">Personnes autorisées à modifier :</p>
                    <ul class="leading-relaxed opacity-90 space-y-1">
                        <li v-for="editor in impactedEditors" :key="editor.id">
                            - {{ editor.prenom }} {{ editor.nom }}
                        </li>
                    </ul>
                </div>
            </div>
        </Transition>
    </div>
</template>
