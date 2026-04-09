<script setup lang="ts">
// 1. Vue & Core (Vue, Inertia, etc.)
import { computed, onMounted } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

// 2. Librairies tierces (Icônes)
import { FolderIcon } from "@heroicons/vue/24/solid";

// 3. Types, Routes & Utilitaires
import { Departement } from "@/types/departement";
import route from "@/routes/editor/folder";
import { decodeEntities } from "@/Composables/useDecodeModule";

// 4. Composants
import WarningPermission from "@/Components/WarningPermission.vue";
import DepartementSelector from "@/Components/Forms/DepartementSelector.vue";

const props = defineProps<{
    parent_id?: number;
    folder?: {
        id: number;
        name: string;
        color: string;
        departements: number[];
    };
    departements: Departement[];
}>();

const form = useForm({
    name: props.folder ? decodeEntities(props.folder.name) : "",
    color: props.folder?.color ?? "#d7ac53",
    departements: props.folder?.departements ?? [],
    parent_id: props.folder ? null : (props.parent_id ?? null),
});

const page = usePage();
const userDepartementsIds = page.props.auth.user.departements;

const submit = () => {
    if (props.folder) {
        form.patch(route.post.update.url(props.folder.id));
    } else {
        form.post(route.post.create.url());
    }
};

onMounted(() => {
    if (!props.folder) {
        const allAvailableDeps = props.departements?.map((d) => d.id) ?? [];
        form.departements = allAvailableDeps.filter((id) =>
            userDepartementsIds.includes(id),
        );
    }
});

const showExternalWarning = computed(() => {
    return form.departements.some(
        (selectedId) => !userDepartementsIds.includes(selectedId),
    );
});

const isCheckboxDisabled = (departementId: number) => {
    if (!userDepartementsIds.includes(departementId)) return false;
    const mySelectedDeps = form.departements.filter((id) => userDepartementsIds.includes(id));
    return form.departements.includes(departementId) && mySelectedDeps.length <= 1;
};
</script>

<template>
    <Head
        :title="
            folder
                ? `Modifier le dossier ${decodeEntities(folder.name)}`
                : 'Créer un nouveau dossier'
        "
    />

    <div class="max-w-4xl py-6 mx-auto">
        <header class="mb-8 text-center">
            <div class="p-3 rounded-2xl bg-sky-500/10 mb-4 inline-flex">
                <FolderIcon class="w-8 h-8 text-sky-500" />
            </div>
            <h1
                class="text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase"
            >
                {{
                    folder
                        ? `Modifier ${decodeEntities(folder.name)}`
                        : "Nouveau Dossier"
                }}
            </h1>
        </header>

        <form @submit.prevent="submit" class="space-y-8">
            <div class="md:flex-row gap-4 flex flex-col">
                <div class="space-y-2 flex-grow">
                    <label
                        class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase"
                        >Nom du dossier</label
                    >
                    <input
                        type="text"
                        v-model="form.name"
                        placeholder="Ex: Factures 2026"
                        class="px-5 py-4 rounded-2xl border-gray-200 dark:border-zinc-800 dark:bg-zinc-900/50 focus:ring-sky-500/10 focus:border-sky-500 text-gray-900 dark:text-white w-full transition-all focus:ring-4"
                    />
                    <div
                        v-if="form.errors.name"
                        class="text-xs text-red-500 font-bold ml-1"
                    >
                        {{ form.errors.name }}
                    </div>
                </div>

                <div
                    v-if="page.props.auth.user.role == 'admin'"
                    class="md:w-1/4 space-y-2"
                >
                    <label
                        class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase"
                        >Couleur UI</label
                    >
                    <div class="relative flex items-center">
                        <input
                            type="color"
                            v-model="form.color"
                            class="p-1 bg-white dark:bg-zinc-900/50 border-gray-200 dark:border-zinc-800 rounded-2xl h-[58px] w-full cursor-pointer border"
                        />
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Visibilité par département</label>
                <div class="sm:grid-cols-2 lg:grid-cols-3 gap-3 grid grid-cols-1">
                    <label
                        v-for="departement in departements"
                        :key="departement.id"
                        :class="[
                            'p-4 rounded-2xl group relative flex cursor-pointer items-center border-2 transition-all',
                            form.departements.includes(departement.id)
                                ? 'border-sky-500 bg-sky-500/5'
                                : 'border-gray-100 dark:border-zinc-800 hover:border-gray-200 dark:hover:border-zinc-700',
                            isCheckboxDisabled(departement.id) ? 'cursor-not-allowed opacity-40' : '',
                        ]"
                    >
                        <input
                            type="checkbox"
                            :value="departement.id"
                            v-model="form.departements"
                            :disabled="isCheckboxDisabled(departement.id)"
                            class="sr-only"
                        />
                        <span
                            :class="[
                                'w-5 h-5 rounded-lg mr-3 flex items-center justify-center border transition-colors',
                                form.departements.includes(departement.id)
                                    ? 'bg-sky-500 border-sky-500 shadow-sm shadow-sky-500/40'
                                    : 'border-gray-300 dark:border-zinc-600',
                            ]"
                        >
                            <CheckIcon v-if="form.departements.includes(departement.id)" class="w-3.5 h-3.5 text-white stroke-[3]" />
                        </span>
                        <span
                            class="text-sm font-bold tracking-tight"
                            :class="
                                form.departements.includes(departement.id) ? 'text-sky-700 dark:text-sky-400' : 'text-gray-500 dark:text-zinc-400'
                            "
                        >
                            {{ departement.name }}
                        </span>
                    </label>
                </div>
                <div v-if="form.errors.departements" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.departements }}</div>
            </div>

            <WarningPermission
                :show="showExternalWarning"
                object-type="dossier"
            >
                <div class="text-sm space-y-1">
                    <p class="font-bold text-amber-600 dark:text-amber-400">
                        Attention : Ce dossier sera visible par des départements
                        externes.
                    </p>
                    <ul class="ml-5 list-disc opacity-80">
                        <li>
                            Modification du nom et des permissions par les
                            admins
                        </li>
                        <li>Création de contenu collaborative activée</li>
                    </ul>
                </div>
            </WarningPermission>

            <button
                type="submit"
                :disabled="form.processing"
                class="py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl shadow-xl tracking-widest text-sm w-full uppercase transition-all hover:scale-[1.01] active:scale-[0.98] disabled:opacity-50"
            >
                {{
                    folder
                        ? "Enregistrer les modifications"
                        : "Créer le dossier"
                }}
            </button>
        </form>
    </div>
</template>
