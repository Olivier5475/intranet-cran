<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Departement } from '@/departement';
import route from '@/routes/editor/file';
import { computed, onMounted } from 'vue';
import { CloudArrowUpIcon, DocumentIcon, CheckIcon } from '@heroicons/vue/24/solid';
import WarningPermission from '@/Components/WarningPermission.vue';

const props = defineProps<{
    parent_id: number;
    file?: {
        id: number;
        name: string;
        departements: number[];
    };
    departements?: Departement[];
}>();

const form = useForm({
    name: props.file?.name ?? '',
    files: [] as File[],
    departements: props.file?.departements ?? [],
    parent_id: props.file ? null : (props.parent_id ?? null),
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
    const mySelectedDeps = form.departements.filter(id => userDepartementIds.includes(id));
    return form.departements.includes(departementId) && mySelectedDeps.length <= 1;
};
</script>

<template>
    <Head :title="file ? `Modifier le fichier ${file.name}` : 'Créer un nouveau fichier'" />

    <div class="max-w-4xl mx-auto py-6">
        <header class="mb-8 text-center">
            <div class="inline-flex p-3 rounded-2xl bg-amber-500/10 mb-4">
                <DocumentIcon class="w-8 h-8 text-amber-500" />
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                {{ file ? "Modification du fichier" : "Importer un fichier" }}
            </h1>
        </header>

        <form @submit.prevent="submit" class="space-y-8">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Désignation du fichier</label>
                <input
                    type="text"
                    v-model="form.name"
                    placeholder="Ex: Rapport_Annuel_2025.pdf"
                    class="w-full px-5 py-4 rounded-2xl border-gray-200 dark:border-zinc-800 dark:bg-zinc-900/50 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all text-gray-900 dark:text-white font-medium"
                />
                <div v-if="form.errors.name" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.name }}</div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Source du document</label>

                <label
                    for="files_input"
                    class="relative group flex flex-col items-center justify-center p-10 border-2 border-dashed rounded-3xl transition-all cursor-pointer shadow-sm hover:shadow-xl group"
                    :class="form.files.length ? 'border-emerald-500 bg-emerald-500/5' : 'border-sky-200 dark:border-zinc-800 hover:border-sky-400 dark:hover:border-zinc-700'"
                >
                    <div class="p-4 rounded-full bg-sky-50 dark:bg-zinc-800 group-hover:scale-110 transition-transform duration-300">
                        <CloudArrowUpIcon class="w-8 h-8 text-sky-600 dark:text-sky-400" />
                    </div>

                    <div class="mt-4 text-center">
                        <span class="block text-sm font-bold text-gray-700 dark:text-gray-200">
                            {{ form.files.length ? 'Fichier sélectionné' : 'Cliquez ou glissez un fichier ici' }}
                        </span>
                        <span class="text-xs text-gray-400 mt-1 block">
                            {{ file ? '(Laissez vide pour conserver le fichier actuel)' : 'PDF, Image, Word, Excel...' }}
                        </span>
                    </div>

                    <input id="files_input" type="file" @change="handleNewFileUpload" class="sr-only" />

                    <div v-if="form.files.length" class="absolute top-4 right-4 flex items-center gap-1 bg-emerald-500 text-white px-3 py-1 rounded-full text-[10px] font-black">
                        <CheckIcon class="w-3 h-3" /> PRÊT
                    </div>
                </label>

                <div v-if="form.files.length" class="mt-3 flex items-center justify-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold text-sm">
                    <DocumentIcon class="w-4 h-4" />
                    {{ form.files[0].name }}
                </div>

                <div v-if="form.errors.files" class="text-xs text-red-500 font-bold text-center mt-2">{{ form.errors.files }}</div>
            </div>

            <div class="space-y-4">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1 text-center block">Accès au fichier</label>
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
            </div>

            <WarningPermission :show="showExternalWarning" object-type="fichier">
                <div class="text-sm space-y-1">
                    <p class="font-bold text-amber-600 dark:text-amber-400 italic text-center">Ce fichier sera partagé hors de votre département.</p>
                </div>
            </WarningPermission>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl shadow-xl hover:scale-[1.01] active:scale-[0.98] transition-all disabled:opacity-50 uppercase tracking-[0.1em] text-sm"
            >
                {{ file ? 'Mettre à jour le fichier' : 'Lancer l\'importation' }}
            </button>
        </form>
    </div>
</template>
