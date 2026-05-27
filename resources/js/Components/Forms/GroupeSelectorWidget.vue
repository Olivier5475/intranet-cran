<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { CheckIcon } from "@heroicons/vue/24/solid";
import { Groupe } from "@/types/groupe";

const props = defineProps<{
    allGroupes: Groupe[];
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
    if (user.role == "admin") {
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
</script>

<template>
    <div class="space-y-4">
        <label
            class="font-black text-gray-400 ml-1 block text-center text-[10px] tracking-[0.2em] uppercase"
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
</template>
