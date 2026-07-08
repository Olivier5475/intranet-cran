<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

import {
    DocumentTextIcon,
    TrashIcon,
    PaperClipIcon,
} from "@heroicons/vue/24/solid";

import { Groupe } from "@/types/groupe";
import { Document } from "@/types/document";
import route from "@/routes/editor/document";
import { decodeEntities, formatForDatetimeLocal } from '@/Composables/useDecodeModule';

import CKEditor5Widget from "@/Components/Features/Document/CKEditor5Widget.vue";
import WarningPermission from "@/Components/UI/WarningPermission.vue";
import GroupeSelectorWidget from "@/Components/Forms/GroupeSelectorWidget.vue";
import FileUploadZone from '@/Components/Forms/FileUploadZone.vue';
import NameInputWidget from '@/Components/Forms/NameInputWidget.vue';
import ColorPickerWidget from '@/Components/Forms/ColorPickerWidget.vue';
import { User } from '@/types';

const props = defineProps<{
    parent_id: number;
    document?: Document;
    groupes: Groupe[];
    editors: User[];
}>();

const page = usePage();
const user = page.props.auth.user;
const userGroupeIds = user.groupes;

const form = useForm({
    name: props.document
        ? decodeEntities(props.document.name)
        : props.parent_id == 0
            ? "Accueil"
            : "",
    type: props.document?.type ?? "document", // Gère le type ('document' ou 'appelprojet')
    deadline: formatForDatetimeLocal(props.document?.deadline) ?? null, // Gère la date limite
    content: props.document?.content ?? "",
    existing_attachments: props.document?.attachments ?? [],
    new_attachments: [] as File[],
    groupes: props.document?.groupes ?? [],
    ...(page.props.auth.user.role === "admin" && {
        color: props.document?.color ?? "#ffffff",
    }),
    parent_id: props.document ? null : (props.parent_id ?? null),
});
console.log(props.document?.deadline);
// État local pour piloter l'animation d'ouverture/fermeture de la deadline
const isAppelProjet = ref(form.type === "appelprojet");

// On surveille le changement de type pour déclencher l'animation et vider la deadline si on repasse en document
const handleTypeChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    if (target.value === "appelprojet") {
        isAppelProjet.value = true;
    } else {
        isAppelProjet.value = false;
        form.deadline = null; // Clean la date si c'est plus un appel à projet
    }
};

onMounted(() => {
    if (!props.document) {
        const allAvailableGrp = props.groupes?.map((d) => d.id) ?? [];
        form.groupes = allAvailableGrp.filter((id) =>
            userGroupeIds.includes(id),
        );
    }
});

const showExternalWarning = computed(() =>
    form.groupes.some(
        (selectedId) => !userGroupeIds.includes(selectedId),
    ),
);

const removeExistingAttachment = (index: number) =>
    form.existing_attachments.splice(index, 1);

const submit = () => {
    form.post(
        props.document
            ? route.post.update.url(props.document.id)
            : route.post.create.url()
    );
};
</script>

<template>
    <Head
        :title="
            document
                ? `Modifier ${decodeEntities(document.name)}`
                : 'Nouveau document'
        "
    />

    <div class="max-w-5xl py-6 mx-auto">
        <header class="mb-10 text-center">
            <div class="p-3 rounded-2xl bg-sky-500/10 mb-4 text-sky-500 inline-flex">
                <DocumentTextIcon class="w-8 h-8" />
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase">
                {{ document ? "Édition du document" : "Nouveau document" }}
            </h1>
        </header>

        <form @submit.prevent="submit" class="space-y-10">
            <div class="md:flex-row gap-6 flex flex-col">
                <NameInputWidget
                    v-model="form.name"
                    label="Titre du document"
                    placeholder="Ex: Procédure de sécurité"
                    :error="form.errors.name"
                    :disabled="parent_id == 0"
                    input-class="text-xl font-bold"
                    class="grow"
                />

                <ColorPickerWidget
                    v-if="page.props.auth.user.role === 'admin' && !isAppelProjet"
                    v-model="form.color as string"
                    label="Couleur d'accent"
                    class="md:w-1/4"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div class="space-y-2">
                    <label for="type" class="font-black text-gray-400 dark:text-zinc-500 text-[10px] tracking-[0.2em] uppercase block ml-1">
                        Type de document
                    </label>
                    <select
                        id="type"
                        v-model="form.type"
                        @change="handleTypeChange"
                        class="dark:bg-slate-900/50 border-gray-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm dark:text-zinc-200 focus:ring-sky-500/10 focus:border-sky-500 block w-full cursor-pointer bg-white dark:bg-slate-900 transition-all focus:ring-4"
                    >
                        <option value="document">Document classique</option>
                        <option value="appelprojet">Appel à projet</option>
                    </select>
                </div>

                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0 transform -translate-y-2"
                    enter-to-class="max-h-24 opacity-100 transform translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-24 opacity-100 transform translate-y-0"
                    leave-to-class="max-h-0 opacity-0 transform -translate-y-2"
                >
                    <div v-if="isAppelProjet" class="space-y-2 overflow-hidden">
                        <label for="deadline" class="font-black text-gray-400 dark:text-zinc-500 text-[10px] tracking-[0.2em] uppercase block ml-1">
                            Date limite (Deadline)
                        </label>
                        <div class="relative">
                            <input
                                id="deadline"
                                type="datetime-local"
                                v-model="form.deadline"
                                :required="isAppelProjet"
                                class="dark:bg-slate-900/50 border-gray-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm dark:text-zinc-200 focus:ring-sky-500/10 focus:border-sky-500 block w-full bg-white dark:bg-slate-900 transition-all focus:ring-4"
                            />
                        </div>
                        <div v-if="form.errors.deadline" class="text-xs text-red-500 font-bold ml-1">
                            {{ form.errors.deadline }}
                        </div>
                    </div>
                </Transition>
            </div>

            <div class="space-y-2">
                <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Contenu du document</label>
                <div class="rounded-2xl border-gray-200 dark:border-zinc-800 shadow-sm overflow-hidden border">
                    <CKEditor5Widget name="content" v-model="form.content" class="text-black min-h-[100px]" />
                </div>
                <div v-if="form.errors.content" class="text-xs text-red-500 font-bold ml-1">
                    {{ form.errors.content }}
                </div>
            </div>

            <div :class="form.existing_attachments.length > 0 ? 'lg:grid-cols-2 gap-8 grid' : ''">
                <FileUploadZone v-model="form.new_attachments" :error="form.errors.new_attachments" multiple>
                    <template #label>Ajouter des pièces jointes</template>
                </FileUploadZone>

                <div class="space-y-4" v-if="form.existing_attachments.length > 0">
                    <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">Pièces jointes actuelles</label>
                    <div class="space-y-2 pr-2 custom-scrollbar max-h-[180px] overflow-y-auto">
                        <div v-for="(attachment, index) in form.existing_attachments" :key="attachment.id" class="gap-3 p-3 bg-white dark:bg-zinc-800/50 dark:border-zinc-700 rounded-xl group flex items-center border">
                            <PaperClipIcon class="w-4 h-4 text-zinc-400" />
                            <input type="text" v-model="attachment.name" class="text-sm font-medium p-0 dark:text-white flex-grow border-none bg-transparent focus:ring-0" />
                            <button type="button" @click="removeExistingAttachment(index)" class="text-red-400 hover:text-red-600 opacity-0 transition-colors group-hover:opacity-100">
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 dark:border-zinc-800 border-t">
                <GroupeSelectorWidget
                    v-model="form.groupes"
                    :editors="editors"
                    :all-groupes="groupes"
                />
            </div>

            <WarningPermission :show="showExternalWarning" object-type="document" />

            <button
                type="submit"
                :disabled="form.processing"
                class="py-5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl shadow-2xl tracking-widest w-full uppercase transition-all hover:scale-[1.01] active:scale-[0.98] disabled:opacity-50"
            >
                {{ document ? "Enregistrer les modifications" : "Publier le document" }}
            </button>
        </form>
    </div>
</template>
