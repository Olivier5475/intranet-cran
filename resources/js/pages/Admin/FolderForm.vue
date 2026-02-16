<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import route from '@/routes/editor/folder';
import { Departement } from '@/departement';
import { computed, onMounted } from 'vue';
import WarningPermission from '@/Components/WarningPermission.vue';
import { FolderIcon, CheckIcon } from '@heroicons/vue/24/solid';
import { decodeEntities } from '@/lib/utils';

const props = defineProps<{
    parent_id?: number;
    folder?: {
        id: number;
        name: string;
        color: string;
        departements: number[];
    };
    departements?: Departement[];
}>();

const form = useForm({
    name: props.folder ? decodeEntities(props.folder.name) : '',
    color: props.folder?.color ?? '#d7ac53',
    departements: props.folder?.departements ?? [],
    parent_id: props.folder ? null : (props.parent_id ?? null),
});

const page = usePage();
const userDepartementIds = page.props.auth.user.departements_ids;

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
        form.departements = allAvailableDeps.filter((id) => userDepartementIds.includes(id));
    }
});

const showExternalWarning = computed(() => {
    return form.departements.some((selectedId) => !userDepartementIds.includes(selectedId));
});

const isCheckboxDisabled = (departementId: number) => {
    if (!userDepartementIds.includes(departementId)) return false;
    const mySelectedDeps = form.departements.filter(id => userDepartementIds.includes(id));
    return form.departements.includes(departementId) && mySelectedDeps.length <= 1;
};
</script>

<template>
    <Head :title="folder ? `Modifier le dossier ${decodeEntities(folder.name)}` : 'Créer un nouveau dossier'" />

    <div class="max-w-4xl mx-auto py-6">
        <header class="mb-8 text-center">
            <div class="inline-flex p-3 rounded-2xl bg-sky-500/10 mb-4">
                <FolderIcon class="w-8 h-8 text-sky-500" />
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                {{ folder ? `Modifier ${decodeEntities(folder.name)}` : "Nouveau Dossier" }}
            </h1>
        </header>

        <form @submit.prevent="submit" class="space-y-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-grow space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Nom du dossier</label>
                    <input
                        type="text"
                        v-model="form.name"
                        placeholder="Ex: Factures 2026"
                        class="w-full px-5 py-4 rounded-2xl border-gray-200 dark:border-zinc-800 dark:bg-zinc-900/50 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all text-gray-900 dark:text-white"
                    />
                    <div v-if="form.errors.name" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.name }}</div>
                </div>

                <div v-if="page.props.auth.user.role == 'admin'" class="md:w-1/4 space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Couleur UI</label>
                    <div class="relative flex items-center">
                        <input
                            type="color"
                            v-model="form.color"
                            class="w-full h-[58px] p-1 bg-white dark:bg-zinc-900/50 border border-gray-200 dark:border-zinc-800 rounded-2xl cursor-pointer"
                        />
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Visibilité par département</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <label
                        v-for="departement in departements"
                        :key="departement.id"
                        :class="[
                            'relative flex items-center p-4 rounded-2xl border-2 transition-all cursor-pointer group',
                            form.departements.includes(departement.id)
                                ? 'border-sky-500 bg-sky-500/5'
                                : 'border-gray-100 dark:border-zinc-800 hover:border-gray-200 dark:hover:border-zinc-700',
                            isCheckboxDisabled(departement.id) ? 'opacity-40 cursor-not-allowed' : ''
                        ]"
                    >
                        <input
                            type="checkbox"
                            :value="departement.id"
                            v-model="form.departements"
                            :disabled="isCheckboxDisabled(departement.id)"
                            class="sr-only"
                        />
                        <span :class="[
                            'w-5 h-5 rounded-lg border flex items-center justify-center mr-3 transition-colors',
                            form.departements.includes(departement.id) ? 'bg-sky-500 border-sky-500 shadow-sm shadow-sky-500/40' : 'border-gray-300 dark:border-zinc-600'
                        ]">
                            <CheckIcon v-if="form.departements.includes(departement.id)" class="w-3.5 h-3.5 text-white stroke-[3]" />
                        </span>
                        <span class="text-sm font-bold tracking-tight" :class="form.departements.includes(departement.id) ? 'text-sky-700 dark:text-sky-400' : 'text-gray-500 dark:text-zinc-400'">
                            {{ departement.name }}
                        </span>
                    </label>
                </div>
                <div v-if="form.errors.departements" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.departements }}</div>
            </div>

            <WarningPermission :show="showExternalWarning" object-type="dossier">
                <div class="text-sm space-y-1">
                    <p class="font-bold text-amber-600 dark:text-amber-400">Attention : Ce dossier sera visible par des départements externes.</p>
                    <ul class="list-disc ml-5 opacity-80">
                        <li>Modification du nom et des permissions par les admins</li>
                        <li>Création de contenu collaborative activée</li>
                    </ul>
                </div>
            </WarningPermission>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl shadow-xl hover:scale-[1.01] active:scale-[0.98] transition-all disabled:opacity-50 uppercase tracking-widest text-sm"
            >
                {{ folder ? 'Enregistrer les modifications' : 'Créer le dossier' }}
            </button>
        </form>
    </div>
</template>
