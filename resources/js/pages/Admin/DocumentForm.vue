<script setup lang="ts">
// 1. Vue & Core
import { computed, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { CloudArrowUpIcon, DocumentTextIcon, CheckIcon, TrashIcon, PaperClipIcon } from '@heroicons/vue/24/solid';

// 3. Types, Routes & Utilitaires
import { Departement } from '@/types/departement';
import { Document } from '@/types/document';
import route from '@/routes/editor/document';
import { decodeEntities } from '@/Composables/useDecodeModule';

// 4. Composants
import CKEditor5Widget from '@/Components/CKEditor5Widget.vue';
import WarningPermission from '@/Components/WarningPermission.vue';

const props = defineProps<{
    parent_id: number;
    document?: Document;
    departements: Departement[];
}>();

const page = usePage();
// récupération de l'utilisateur courant
const user = page.props.auth.user;
// récupération de ses departements
const userDepartementIds = user.departements_ids;

const form = useForm({
    title: props.document ? decodeEntities(props.document.title) : props.parent_id == 0 ? 'Accueil' : '',
    content: props.document?.content ?? '',
    existing_attachments: props.document?.attachments ?? [],
    new_attachments: [] as File[],
    departements: props.document?.departements ?? [],
    ...(page.props.auth.user.role === 'admin' && { color: props.document?.color ?? '#ffffff' }),
    parent_id: props.document ? null : (props.parent_id ?? null),
});

onMounted(() => {
    if (!props.document) {
        const allAvailableDeps = props.departements?.map((d) => d.id) ?? [];
        form.departements = allAvailableDeps.filter((id) => userDepartementIds.includes(id));
    }
});

const showExternalWarning = computed(() => form.departements.some((selectedId) => !userDepartementIds.includes(selectedId)));
const handleNewFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.new_attachments = Array.from(target.files || []) as File[];
};
const removeExistingAttachment = (index: number) => form.existing_attachments.splice(index, 1);

const submit = () => {
    if (props.document) form.post(route.post.update.url(props.document.id), { method: 'patch' });
    else form.post(route.post.create.url());
};

const isCheckboxDisabled = (departementId: number) => {
    if (!userDepartementIds.includes(departementId)) return false;
    const mySelectedDeps = form.departements.filter((id) => userDepartementIds.includes(id));
    return form.departements.includes(departementId) && mySelectedDeps.length <= 1;
};
</script>

<template>
    <Head :title="document ? `Modifier ${decodeEntities(document.title)}` : 'Nouveau document'" />

    <div class="max-w-5xl py-6 mx-auto">
        <header class="mb-10 text-center">
            <div class="p-3 rounded-2xl bg-sky-500/10 mb-4 text-sky-500 inline-flex">
                <DocumentTextIcon class="w-8 h-8" />
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase">
                {{ document ? 'Édition du document' : 'Nouveau document' }}
            </h1>
        </header>

        <form @submit.prevent="submit" class="space-y-10">
            <div class="md:flex-row gap-6 flex flex-col">
                <div class="space-y-2 flex-grow">
                    <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Titre du document</label>
                    <input
                        type="text"
                        v-model="form.title"
                        placeholder="Ex: Procédure de sécurité"
                        :disabled="parent_id == 0"
                        class="px-5 py-4 rounded-2xl border-gray-200 dark:border-zinc-800 dark:bg-zinc-900/50 focus:ring-sky-500/10 focus:border-sky-500 text-xl font-bold dark:text-white w-full transition-all focus:ring-4 disabled:opacity-50"
                    />
                    <div v-if="form.errors.title" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.title }}</div>
                </div>

                <div v-if="page.props.auth.user.role == 'admin'" class="md:w-1/4 space-y-2">
                    <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Couleur d'accent</label>
                    <input
                        type="color"
                        v-model="form.color"
                        class="p-1 bg-white dark:bg-zinc-900/50 border-gray-200 dark:border-zinc-800 rounded-2xl h-[62px] w-full cursor-pointer border"
                    />
                </div>
            </div>

            <div class="space-y-2">
                <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Contenu du document</label>
                <div class="rounded-2xl border-gray-200 dark:border-zinc-800 shadow-sm overflow-hidden border">
                    <CKEditor5Widget name="content" v-model="form.content" class="text-black min-h-[100px]" />
                </div>
                <div v-if="form.errors.content" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.content }}</div>
            </div>

            <div class="lg:grid-cols-2 gap-8">
                <div class="space-y-4 w-full">
                    <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Ajouter des fichiers</label>
                    <label
                        for="new_attachments_input"
                        class="group p-8 rounded-3xl border-sky-200 dark:border-zinc-800 hover:border-sky-400 hover:bg-sky-500/5 flex w-full cursor-pointer flex-col items-center justify-center border-2 border-dashed transition-all"
                    >
                        <CloudArrowUpIcon class="w-10 h-10 text-sky-500 mb-3 transition-transform group-hover:scale-110" />
                        <span class="text-sm font-bold text-gray-600 dark:text-zinc-300">Glissez ou cliquez pour uploader</span>
                        <span class="text-gray-400 mt-1 text-[10px] uppercase">Multiples fichiers autorisés</span>
                        <input id="new_attachments_input" type="file" multiple @change="handleNewFileUpload" class="sr-only" />
                    </label>

                    <div v-if="form.new_attachments.length" class="gap-2 flex flex-wrap">
                        <div
                            v-for="file in form.new_attachments"
                            :key="file.name"
                            class="gap-2 px-3 py-1 bg-emerald-500/10 text-emerald-600 text-xs font-bold border-emerald-500/20 flex items-center rounded-full border"
                        >
                            <CheckIcon class="w-3 h-3" /> {{ file.name }}
                        </div>
                    </div>
                </div>

                <div class="space-y-4" v-if="form.existing_attachments.length > 0">
                    <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Pièces jointes actuelles</label>
                    <div class="space-y-2 pr-2 custom-scrollbar max-h-[180px] overflow-y-auto">
                        <div
                            v-for="(attachment, index) in form.existing_attachments"
                            :key="attachment.id"
                            class="gap-3 p-3 bg-white dark:bg-zinc-800/50 dark:border-zinc-700 rounded-xl group flex items-center border"
                        >
                            <PaperClipIcon class="w-4 h-4 text-zinc-400" />
                            <input
                                type="text"
                                v-model="attachment.name"
                                class="text-sm font-medium p-0 dark:text-white flex-grow border-none bg-transparent focus:ring-0"
                            />
                            <button
                                type="button"
                                @click="removeExistingAttachment(index)"
                                class="text-red-400 hover:text-red-600 opacity-0 transition-colors group-hover:opacity-100"
                            >
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 pt-6 dark:border-zinc-800 border-t">
                <label class="font-black text-gray-400 ml-1 block text-center text-[10px] tracking-[0.2em] uppercase">Départements ayant accès</label>
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
                                form.departements.includes(departement.id) ? 'bg-sky-500 border-sky-500' : 'border-gray-300 dark:border-zinc-600',
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
            </div>

            <WarningPermission :show="showExternalWarning" object-type="document" />

            <button
                type="submit"
                :disabled="form.processing"
                class="py-5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl shadow-2xl tracking-widest w-full uppercase transition-all hover:scale-[1.01] active:scale-[0.98] disabled:opacity-50"
            >
                {{ document ? 'Enregistrer les modifications' : 'Publier le document' }}
            </button>
        </form>
    </div>
</template>
