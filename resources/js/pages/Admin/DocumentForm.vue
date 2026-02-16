<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import CKEditor5Widget from '@/Components/CKEditor5Widget.vue';
import { Departement } from '@/departement';
import route from '@/routes/editor/document';
import { computed, onMounted } from 'vue';
import { CloudArrowUpIcon, DocumentTextIcon, CheckIcon, TrashIcon, PaperClipIcon } from '@heroicons/vue/24/solid';
import WarningPermission from '@/Components/WarningPermission.vue';
import { decodeEntities } from '@/lib/utils';

interface Attachment { id: number; name: string; }
interface Document { id: number; title: string; content: string; color: string; attachments: Attachment[]; departements: number[]; }

const props = defineProps<{ parent_id: number; document?: Document; departements: Departement[]; }>();
const page = usePage();

const form = useForm({
    title: props.document ? decodeEntities(props.document.title) : ((props.parent_id == 0) ? 'Accueil' : ''),
    content: props.document?.content ?? '',
    existing_attachments: props.document?.attachments ?? [],
    new_attachments: [] as File[],
    departements: props.document?.departements ?? [],
    ...(page.props.auth.user.role === 'admin' && { color: props.document?.color ?? '#ffffff' }),
    parent_id: props.document ? null : (props.parent_id ?? null),
});

const userDepartementIds = page.props.auth.user.departements_ids;

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
    const mySelectedDeps = form.departements.filter(id => userDepartementIds.includes(id));
    return form.departements.includes(departementId) && mySelectedDeps.length <= 1;
};
</script>

<template>
    <Head :title="document ? `Modifier ${decodeEntities(document.title)}` : 'Nouveau document'" />

    <div class="max-w-5xl mx-auto py-6">
        <header class="mb-10 text-center">
            <div class="inline-flex p-3 rounded-2xl bg-sky-500/10 mb-4 text-sky-500">
                <DocumentTextIcon class="w-8 h-8" />
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                {{ document ? "Édition du document" : "Nouveau document" }}
            </h1>
        </header>

        <form @submit.prevent="submit" class="space-y-10">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-grow space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Titre du document</label>
                    <input
                        type="text"
                        v-model="form.title"
                        placeholder="Ex: Procédure de sécurité"
                        :disabled="parent_id == 0"
                        class="w-full px-5 py-4 rounded-2xl border-gray-200 dark:border-zinc-800 dark:bg-zinc-900/50 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all text-xl font-bold dark:text-white disabled:opacity-50"
                    />
                    <div v-if="form.errors.title" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.title }}</div>
                </div>

                <div v-if="page.props.auth.user.role == 'admin'" class="md:w-1/4 space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Couleur d'accent</label>
                    <input type="color" v-model="form.color" class="w-full h-[62px] p-1 bg-white dark:bg-zinc-900/50 border border-gray-200 dark:border-zinc-800 rounded-2xl cursor-pointer" />
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Contenu du document</label>
                <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-zinc-800 shadow-sm">
                    <CKEditor5Widget name="content" v-model="form.content" class="text-black min-h-[100px]" />
                </div>
                <div v-if="form.errors.content" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.content }}</div>
            </div>

            <div class="lg:grid-cols-2 gap-8">
                <div class="space-y-4 w-full">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Ajouter des fichiers</label>
                    <label
                        for="new_attachments_input"
                        class="w-full group flex flex-col items-center justify-center p-8 border-2 border-dashed rounded-3xl transition-all cursor-pointer border-sky-200 dark:border-zinc-800 hover:border-sky-400 hover:bg-sky-500/5"
                    >
                        <CloudArrowUpIcon class="w-10 h-10 text-sky-500 mb-3 group-hover:scale-110 transition-transform" />
                        <span class="text-sm font-bold text-gray-600 dark:text-zinc-300">Glissez ou cliquez pour uploader</span>
                        <span class="text-[10px] text-gray-400 mt-1 uppercase">Multiples fichiers autorisés</span>
                        <input id="new_attachments_input" type="file" multiple @change="handleNewFileUpload" class="sr-only" />
                    </label>

                    <div v-if="form.new_attachments.length" class="flex flex-wrap gap-2">
                        <div v-for="file in form.new_attachments" :key="file.name" class="flex items-center gap-2 px-3 py-1 bg-emerald-500/10 text-emerald-600 rounded-full text-xs font-bold border border-emerald-500/20">
                            <CheckIcon class="w-3 h-3" /> {{ file.name }}
                        </div>
                    </div>
                </div>

                <div class="space-y-4" v-if="form.existing_attachments.length > 0">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">Pièces jointes actuelles</label>
                    <div class="space-y-2 max-h-[180px] overflow-y-auto pr-2 custom-scrollbar">
                        <div v-for="(attachment, index) in form.existing_attachments" :key="attachment.id"
                             class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-800/50 border dark:border-zinc-700 rounded-xl group"
                        >
                            <PaperClipIcon class="w-4 h-4 text-zinc-400" />
                            <input type="text" v-model="attachment.name" class="flex-grow bg-transparent border-none text-sm font-medium focus:ring-0 p-0 dark:text-white" />
                            <button type="button" @click="removeExistingAttachment(index)" class="text-red-400 hover:text-red-600 transition-colors opacity-0 group-hover:opacity-100">
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 pt-6 border-t dark:border-zinc-800">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1 text-center block">Départements ayant accès</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <label v-for="departement in departements" :key="departement.id"
                           :class="['relative flex items-center p-4 rounded-2xl border-2 transition-all cursor-pointer group',
                                form.departements.includes(departement.id) ? 'border-sky-500 bg-sky-500/5' : 'border-gray-100 dark:border-zinc-800 hover:border-gray-200 dark:hover:border-zinc-700',
                                isCheckboxDisabled(departement.id) ? 'opacity-40 cursor-not-allowed' : '']"
                    >
                        <input type="checkbox" :value="departement.id" v-model="form.departements" :disabled="isCheckboxDisabled(departement.id)" class="sr-only" />
                        <span :class="['w-5 h-5 rounded-lg border flex items-center justify-center mr-3 transition-colors',
                                     form.departements.includes(departement.id) ? 'bg-sky-500 border-sky-500' : 'border-gray-300 dark:border-zinc-600']">
                            <CheckIcon v-if="form.departements.includes(departement.id)" class="w-3.5 h-3.5 text-white stroke-[3]" />
                        </span>
                        <span class="text-sm font-bold tracking-tight" :class="form.departements.includes(departement.id) ? 'text-sky-700 dark:text-sky-400' : 'text-gray-500 dark:text-zinc-400'">
                            {{ departement.name }}
                        </span>
                    </label>
                </div>
            </div>

            <WarningPermission :show="showExternalWarning" object-type="document" />

            <button type="submit" :disabled="form.processing"
                    class="w-full py-5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl shadow-2xl hover:scale-[1.01] active:scale-[0.98] transition-all disabled:opacity-50 uppercase tracking-widest"
            >
                {{ document ? 'Enregistrer les modifications' : 'Publier le document' }}
            </button>
        </form>
    </div>
</template>
