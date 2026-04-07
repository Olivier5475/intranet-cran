<script setup lang="ts">
// 1. Vue & Core
import { computed, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { CloudArrowUpIcon, DocumentIcon, CheckIcon } from '@heroicons/vue/24/solid';

// 3. Types, Routes & Utilitaires
import { Departement } from '@/types/departement';
import route from '@/routes/editor/file';
import { decodeEntities } from '@/Composables/useDecodeModule';

// 4. Composants
import WarningPermission from '@/Components/WarningPermission.vue';

const props = defineProps<{
    parent_id: number;
    file?: {
        id: number;
        name: string;
        departements: number[];
        is_private: boolean
    };
    departements?: Departement[];
}>();

const form = useForm({
    name: props.file ? decodeEntities(props.file.name) : '',
    files: [] as File[],
    departements: props.file?.departements ?? [],
    parent_id: props.file ? null : (props.parent_id ?? null),
    is_private : props.file?.is_private ?? false,
});

const page = usePage();
const userDepartementIds = page.props.auth.user.departements_ids;

const handleNewFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.files = Array.from(target.files || []) as File[];
};

const submit = () => {
    if (props.file) {
        form.post(route.post.update.url(props.file.id), {
            method: 'patch',
        });
    } else {
        form.post(route.post.create.url());
    }
};

onMounted(() => {
    if (!props.file) {
        const allAvailableDeps = props.departements?.map((d) => d.id) ?? [];
        form.departements = allAvailableDeps.filter((id) => userDepartementIds.includes(id));
    }
});

const showExternalWarning = computed(() => {
    return form.departements.some((selectedId) => !userDepartementIds.includes(selectedId));
});

const isCheckboxDisabled = (departementId: number) => {
    if (!userDepartementIds.includes(departementId)) return false;
    const mySelectedDeps = form.departements.filter((id) => userDepartementIds.includes(id));
    return form.departements.includes(departementId) && mySelectedDeps.length <= 1;
};
</script>

<template>
    <Head :title="file ? `Modifier le fichier ${decodeEntities(file.name)}` : 'Créer un nouveau fichier'" />

    <div class="max-w-4xl py-6 mx-auto">
        <header class="mb-8 text-center">
            <div class="p-3 rounded-2xl bg-amber-500/10 mb-4 inline-flex">
                <DocumentIcon class="w-8 h-8 text-amber-500" />
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase">
                {{ file ? 'Modification du fichier' : 'Importer un fichier' }}
            </h1>
        </header>

        <form @submit.prevent="submit" class="space-y-8">
            <div class="space-y-2">
                <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Désignation du fichier</label>
                <input
                    type="text"
                    v-model="form.name"
                    placeholder="Ex: Rapport_Annuel_2025.pdf"
                    class="px-5 py-4 rounded-2xl border-gray-200 dark:border-zinc-800 dark:bg-zinc-900/50 focus:ring-sky-500/10 focus:border-sky-500 text-gray-900 dark:text-white font-medium w-full transition-all focus:ring-4"
                />
                <div v-if="form.errors.name" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.name }}</div>
            </div>

            <div class="space-y-2">
                <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Source du document</label>

                <label
                    for="files_input"
                    class="group p-10 rounded-3xl shadow-sm hover:shadow-xl group relative flex cursor-pointer flex-col items-center justify-center border-2 border-dashed transition-all"
                    :class="
                        form.files.length
                            ? 'border-emerald-500 bg-emerald-500/5'
                            : 'border-sky-200 dark:border-zinc-800 hover:border-sky-400 dark:hover:border-zinc-700'
                    "
                >
                    <span class="p-4 bg-sky-50 dark:bg-zinc-800 rounded-full transition-transform duration-300 group-hover:scale-110">
                        <CloudArrowUpIcon class="w-8 h-8 text-sky-600 dark:text-sky-400" />
                    </span>

                    <span class="mt-4 text-center">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200 block">
                            {{ form.files.length ? 'Fichier sélectionné' : 'Cliquez ou glissez un fichier ici' }}
                        </span>
                        <span class="text-xs text-gray-400 mt-1 block">
                            {{ file ? '(Laissez vide pour conserver le fichier actuel)' : 'PDF, Image, Word, Excel...' }}
                        </span>
                    </span>

                    <input id="files_input" type="file" @change="handleNewFileUpload" class="sr-only" />

                    <span
                        v-if="form.files.length"
                        class="top-4 right-4 gap-1 bg-emerald-500 text-white px-3 py-1 font-black absolute flex items-center rounded-full text-[10px]"
                    >
                        <CheckIcon class="w-3 h-3" /> PRÊT
                    </span>
                </label>

                <div
                    v-if="form.files.length"
                    class="mt-3 gap-2 text-emerald-600 dark:text-emerald-400 font-bold text-sm flex items-center justify-center"
                >
                    <DocumentIcon class="w-4 h-4" />
                    {{ form.files[0].name }}
                </div>

                <div v-if="form.errors.files" class="text-xs text-red-500 font-bold mt-2 text-center">{{ form.errors.files }}</div>
            </div>


            <div class="space-y-4">
                <label class="font-black text-gray-400 ml-1 block text-center text-[10px] tracking-[0.2em] uppercase">Accès au fichier</label>
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
                            {{ decodeEntities(departement.name) }}
                        </span>
                    </label>
                </div>
            </div>

            <WarningPermission :show="showExternalWarning" object-type="fichier">
                <div class="text-sm space-y-1">
                    <p class="font-bold text-amber-600 dark:text-amber-400 text-center italic">Ce fichier sera partagé hors de votre département.</p>
                </div>
            </WarningPermission>

            <button
                type="submit"
                :disabled="form.processing"
                class="py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl shadow-xl text-sm w-full tracking-[0.1em] uppercase transition-all hover:scale-[1.01] active:scale-[0.98] disabled:opacity-50"
            >
                {{ file ? 'Mettre à jour le fichier' : "Lancer l'importation" }}
            </button>
        </form>
    </div>
</template>
