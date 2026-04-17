<script setup lang="ts">
import { computed, onMounted } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

import {
    DocumentTextIcon,
    TrashIcon,
    PaperClipIcon,
} from "@heroicons/vue/24/solid";

import { Departement } from "@/types/departement";
import { Document } from "@/types/document";
import route from "@/routes/editor/document";
import { decodeEntities } from "@/Composables/useDecodeModule";

import CKEditor5Widget from "@/Components/Features/Document/CKEditor5Widget.vue";
import WarningPermission from "@/Components/UI/WarningPermission.vue";
import DepartementSelectorWidget from "@/Components/Forms/DepartementSelectorWidget.vue";
import FileUploadZone from '@/Components/Forms/FileUploadZone.vue';
import NameInputWidget from '@/Components/Forms/NameInputWidget.vue';
import ColorPickerWidget from '@/Components/Forms/ColorPickerWidget.vue';

const props = defineProps<{
    parent_id: number;
    document?: Document;
    departements: Departement[];
}>();

const page = usePage();
// récupération de l'utilisateur courant
const user = page.props.auth.user;
// récupération de ses departements
const userDepartementIds = user.departements;

const form = useForm({
    name: props.document
        ? decodeEntities(props.document.name)
        : props.parent_id == 0
          ? "Accueil"
          : "",
    content: props.document?.content ?? "",
    existing_attachments: props.document?.attachments ?? [],
    new_attachments: [] as File[],
    departements: props.document?.departements ?? [],
    ...(page.props.auth.user.role === "admin" && {
        color: props.document?.color ?? "#ffffff",
    }),
    parent_id: props.document ? null : (props.parent_id ?? null),
});

onMounted(() => {
    if (!props.document) {
        const allAvailableDeps = props.departements?.map((d) => d.id) ?? [];
        form.departements = allAvailableDeps.filter((id) =>
            userDepartementIds.includes(id),
        );
    }
});

const showExternalWarning = computed(() =>
    form.departements.some(
        (selectedId) => !userDepartementIds.includes(selectedId),
    ),
);

const removeExistingAttachment = (index: number) =>
    form.existing_attachments.splice(index, 1);

const submit = () => {
        form.post(
            props.document // Si on a un document
                ? route.post.update.url(props.document.id) // on l'update
                : route.post.create.url() // sinon, on en crée un
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
            <div
                class="p-3 rounded-2xl bg-sky-500/10 mb-4 text-sky-500 inline-flex"
            >
                <DocumentTextIcon class="w-8 h-8" />
            </div>
            <h1
                class="text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase"
            >
                {{ document ? "Édition du document" : "Nouveau document" }}
            </h1>
        </header>

        <form @submit.prevent="submit" class="space-y-10">
            <div class="md:flex-row gap-6 flex flex-col ">
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
                    v-if="page.props.auth.user.role === 'admin'"
                    v-model="form.color as string"
                    label="Couleur d'accent"
                    class="md:w-1/4"
                />
            </div>

            <div class="space-y-2">
                <label
                    class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase"
                    >Contenu du document</label
                >
                <div
                    class="rounded-2xl border-gray-200 dark:border-zinc-800 shadow-sm overflow-hidden border"
                >
                    <CKEditor5Widget
                        name="content"
                        v-model="form.content"
                        class="text-black min-h-[100px]"
                    />
                </div>
                <div
                    v-if="form.errors.content"
                    class="text-xs text-red-500 font-bold ml-1"
                >
                    {{ form.errors.content }}
                </div>
            </div>

            <div class="lg:grid-cols-2 gap-8">
                <FileUploadZone
                    v-model="form.new_attachments"
                    :error="form.errors.new_attachments"
                    multiple
                >
                    <template #label>Ajouter des pièces jointes</template>
                </FileUploadZone>

                <div
                    class="space-y-4"
                    v-if="form.existing_attachments.length > 0"
                >
                    <label
                        class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase"
                        >Pièces jointes actuelles</label
                    >
                    <div
                        class="space-y-2 pr-2 custom-scrollbar max-h-[180px] overflow-y-auto"
                    >
                        <div
                            v-for="(
                                attachment, index
                            ) in form.existing_attachments"
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

            <div class="pt-6 dark:border-zinc-800 border-t">
                <DepartementSelectorWidget
                    v-model="form.departements"
                    :all-departements="departements"
                />
            </div>

            <WarningPermission
                :show="showExternalWarning"
                object-type="document"
            />

            <button
                type="submit"
                :disabled="form.processing"
                class="py-5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl shadow-2xl tracking-widest w-full uppercase transition-all hover:scale-[1.01] active:scale-[0.98] disabled:opacity-50"
            >
                {{
                    document
                        ? "Enregistrer les modifications"
                        : "Publier le document"
                }}
            </button>
        </form>
    </div>
</template>
