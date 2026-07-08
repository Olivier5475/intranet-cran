<script setup lang="ts">
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { CheckIcon, ExclamationTriangleIcon } from "@heroicons/vue/24/solid";
import { Groupe } from "@/types/groupe";
import { User } from '@/types';

const props = defineProps<{
    allGroupes: Groupe[];
    editors?: User[]
}>();

// Le v-model automatique lié au tableau form.groupes du parent
const selectedGrp = defineModel<number[]>({ default: [] });

const page = usePage();
const user = page.props.auth.user;
const userGroupeIds = user.groupes as number[];
const allGroupesIds = props.allGroupes.map((d) => d.id);

// Empêcher de se désélectionner de son propre service s'il n'en reste qu'un
const isCheckboxDisabled = (groupeId: number) => {
    let mySelectedGrp;
    if (user.role == "admin" || user.role == "superadmin") {
        if (!allGroupesIds.includes(groupeId)) return false;
        mySelectedGrp = selectedGrp.value.filter((id) =>
            allGroupesIds.includes(id),
        );
    } else {
        if (!userGroupeIds.includes(groupeId)) return false;
        mySelectedGrp = selectedGrp.value.filter((id) =>
            userGroupeIds.includes(id),
        );
    }
    return (
        selectedGrp.value.includes(groupeId) && mySelectedGrp.length <= 1
    );
};

// 🚀 Filtre dynamique des éditeurs en fonction des groupes cochés
const impactedEditors = computed(() => {
    if(props.editors) {
        return props.editors.filter((editor) =>
            editor.groupes.some((gId) => selectedGrp.value.includes(gId))
        );
    } else {
        return null
    }
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <label
                class="font-black text-gray-400 ml-1 mb-4 block text-center text-[10px] tracking-[0.2em] uppercase"
            >
                Groupes ayant accès
            </label>

            <div class="sm:grid-cols-2 lg:grid-cols-3 gap-3 grid grid-cols-1">
                <label
                    v-for="grp in allGroupes"
                    :key="grp.id"
                    :class="[
                        'p-4 rounded-2xl group relative flex cursor-pointer items-center border-2 transition-all',
                        selectedGrp.includes(grp.id)
                            ? 'border-sky-500 bg-sky-500/5'
                            : 'border-gray-100 dark:border-zinc-800 hover:border-gray-200 dark:hover:border-zinc-700',
                        isCheckboxDisabled(grp.id)
                            ? 'cursor-not-allowed opacity-40'
                            : '',
                    ]"
                >
                    <input
                        type="checkbox"
                        :value="grp.id"
                        v-model="selectedGrp"
                        :disabled="isCheckboxDisabled(grp.id)"
                        class="sr-only"
                    />
                    <span
                        :class="[
                            'w-5 h-5 rounded-lg mr-3 flex items-center justify-center border transition-colors',
                            selectedGrp.includes(grp.id)
                                ? 'bg-sky-500 border-sky-500'
                                : 'border-gray-300 dark:border-zinc-600',
                        ]"
                    >
                        <CheckIcon
                            v-if="selectedGrp.includes(grp.id)"
                            class="w-3.5 h-3.5 text-white stroke-[3]"
                        />
                    </span>
                    <span
                        class="text-sm font-bold tracking-tight"
                        :class="
                            selectedGrp.includes(grp.id)
                                ? 'text-sky-700 dark:text-sky-400'
                                : 'text-gray-500 dark:text-zinc-400'
                        "
                    >
                        {{ grp.name }}
                    </span>
                </label>
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
