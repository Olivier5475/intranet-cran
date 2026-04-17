<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { CheckIcon } from "@heroicons/vue/24/solid";
import { Departement } from "@/types/departement";

const props = defineProps<{
    allDepartements: Departement[];
}>();

// Le v-model automatique lié au tableau form.departements du parent
const selectedDeps = defineModel<number[]>({ default: [] });

const page = usePage();
const user = page.props.auth.user;
const userDepartementIds = user.departements as number[];
const allDepartementsIds = props.allDepartements.map((d) => d.id);

// Empêcher de se désélectionner de son propre service s'il n'en reste qu'un
const isCheckboxDisabled = (departementId: number) => {
    let mySelectedDeps;
    if (user.role == "admin") {
        if (!allDepartementsIds.includes(departementId)) return false;
        mySelectedDeps = selectedDeps.value.filter((id) =>
            allDepartementsIds.includes(id),
        );
    } else {
        if (!userDepartementIds.includes(departementId)) return false;
        mySelectedDeps = selectedDeps.value.filter((id) =>
            userDepartementIds.includes(id),
        );
    }
    return (
        selectedDeps.value.includes(departementId) && mySelectedDeps.length <= 1
    );
};
</script>

<template>
    <div class="space-y-4">
        <label
            class="font-black text-gray-400 ml-1 block text-center text-[10px] tracking-[0.2em] uppercase"
        >
            Départements ayant accès
        </label>

        <div class="sm:grid-cols-2 lg:grid-cols-3 gap-3 grid grid-cols-1">
            <label
                v-for="dep in allDepartements"
                :key="dep.id"
                :class="[
                    'p-4 rounded-2xl group relative flex cursor-pointer items-center border-2 transition-all',
                    selectedDeps.includes(dep.id)
                        ? 'border-sky-500 bg-sky-500/5'
                        : 'border-gray-100 dark:border-zinc-800 hover:border-gray-200 dark:hover:border-zinc-700',
                    isCheckboxDisabled(dep.id)
                        ? 'cursor-not-allowed opacity-40'
                        : '',
                ]"
            >
                <input
                    type="checkbox"
                    :value="dep.id"
                    v-model="selectedDeps"
                    :disabled="isCheckboxDisabled(dep.id)"
                    class="sr-only"
                />
                <span
                    :class="[
                        'w-5 h-5 rounded-lg mr-3 flex items-center justify-center border transition-colors',
                        selectedDeps.includes(dep.id)
                            ? 'bg-sky-500 border-sky-500'
                            : 'border-gray-300 dark:border-zinc-600',
                    ]"
                >
                    <CheckIcon
                        v-if="selectedDeps.includes(dep.id)"
                        class="w-3.5 h-3.5 text-white stroke-[3]"
                    />
                </span>
                <span
                    class="text-sm font-bold tracking-tight"
                    :class="
                        selectedDeps.includes(dep.id)
                            ? 'text-sky-700 dark:text-sky-400'
                            : 'text-gray-500 dark:text-zinc-400'
                    "
                >
                    {{ dep.name }}
                </span>
            </label>
        </div>
    </div>
</template>
