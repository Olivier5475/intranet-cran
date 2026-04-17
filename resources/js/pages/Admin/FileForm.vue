<script setup lang="ts">
// 1. Vue & Core
import { computed, onMounted } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

// 2. Librairies tierces (Icônes)
import { DocumentIcon, } from "@heroicons/vue/24/solid";

// 3. Types, Routes & Utilitaires
import { Departement } from "@/types/departement";
import { FileEntry } from "@/types/fileEntry";
import route from "@/routes/editor/file";
import { decodeEntities } from "@/Composables/useDecodeModule";

// 4. Composants
import WarningPermission from "@/Components/UI/WarningPermission.vue";
import DepartementSelector from "@/Components/Forms/DepartementSelector.vue";
import FileUploadZone from '@/Components/Forms/FileUploadZone.vue';


const props = defineProps<{
    parent_id?: number;
    file?: FileEntry;
    departements: Departement[];
}>();

const form = useForm({
    name: props.file ? decodeEntities(props.file.name) : "",
    files: [] as File[],
    departements: props.file?.departements ?? [],
    parent_id: props.file ? null : (props.parent_id ?? null),
});

const page = usePage();
const userDepartementIds = page.props.auth.user.departements;

const submit = () => {
    form.post(
        props.file
            ? route.post.update.url(props.file.id)
            : route.post.create.url(),
        {
            forceFormData: true, // Force l'envoi en FormData (indispensable pour les fichiers)
            onSuccess: () => {
                // Optionnel
            },
        }
    );
};

onMounted(() => {
    if (!props.file) {
        const allAvailableDeps = props.departements?.map((d) => d.id) ?? [];
        form.departements = allAvailableDeps.filter((id) =>
            userDepartementIds.includes(id),
        );
    }
});

const showExternalWarning = computed(() => {
    return form.departements.some(
        (selectedId) => !userDepartementIds.includes(selectedId),
    );
});
</script>

<template>
    <Head
        :title="
            file
                ? `Modifier le fichier ${decodeEntities(file.name)}`
                : 'Créer un nouveau fichier'
        "
    />

    <div class="max-w-4xl py-6 mx-auto">
        <header class="mb-8 text-center">
            <div class="p-3 rounded-2xl bg-amber-500/10 mb-4 inline-flex">
                <DocumentIcon class="w-8 h-8 text-amber-500" />
            </div>
            <h1
                class="text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase"
            >
                {{ file ? "Modification du fichier" : "Importer un fichier" }}
            </h1>
        </header>

        <form @submit.prevent="submit" class="space-y-8">
            <div class="space-y-2">
                <label
                    class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase"
                    >Désignation du fichier</label
                >
                <input
                    type="text"
                    v-model="form.name"
                    placeholder="Ex: Rapport_Annuel_2025.pdf"
                    class="px-5 py-4 rounded-2xl border-gray-200 dark:border-zinc-800 dark:bg-zinc-900/50 focus:ring-sky-500/10 focus:border-sky-500 text-gray-900 dark:text-white font-medium w-full transition-all focus:ring-4"
                />
                <div
                    v-if="form.errors.name"
                    class="text-xs text-red-500 font-bold ml-1"
                >
                    {{ form.errors.name }}
                </div>
            </div>

            <FileUploadZone
                v-model="form.files"
                :error="form.errors.files"
                :is-edit="!!file"
            />

            <div class="pt-6 dark:border-zinc-800 border-t">
                <DepartementSelector
                    v-model="form.departements"
                    :all-departements="departements"
                />
            </div>

            <WarningPermission
                :show="showExternalWarning"
                object-type="fichier"
            >
                <div class="text-sm space-y-1">
                    <p
                        class="font-bold text-amber-600 dark:text-amber-400 text-center italic"
                    >
                        Ce fichier sera partagé hors de votre département.
                    </p>
                </div>
            </WarningPermission>

            <button
                type="submit"
                :disabled="form.processing"
                class="py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl shadow-xl text-sm w-full tracking-[0.1em] uppercase transition-all hover:scale-[1.01] active:scale-[0.98] disabled:opacity-50"
            >
                {{ file ? "Mettre à jour le fichier" : "Lancer l'importation" }}
            </button>
        </form>
    </div>
</template>
