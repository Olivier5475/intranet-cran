<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Departement } from '@/departement';
import route from '@/routes/editor/file';
import { computed, onMounted } from 'vue';
import { CloudArrowUpIcon } from '@heroicons/vue/24/solid';
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

// Vérifie si la checkbox doit être désactivée
const isCheckboxDisabled = (departementId: number) => {
    // 1. Si ce département ne fait pas partie des miens, on ne bloque jamais
    if (!userDepartementIds.includes(departementId)) {
        return false;
    }

    // 2. On compte combien de MES départements sont actuellement cochés dans le formulaire
    const mySelectedDeps = form.departements.filter(id => userDepartementIds.includes(id));

    // 3. Si ce département est coché ET que c'est le dernier des miens restant
    // Alors on le désactive pour empêcher de le décocher
    return form.departements.includes(departementId) && mySelectedDeps.length <= 1;
};
</script>

<template>
    <Head :title="file ? `Modifier le fichier ${file.name}` : 'Créer un nouveau fichier'" />
    <h1 class="text-3xl m-4 text-center first-letter:uppercase">
        {{ file ? "Modification d'un fichier" : "Création d'un nouveau fichier" }}
    </h1>
    <hr class="mx-auto w-11/12" />
    <form @submit.prevent="submit" class="pt-4 mx-auto w-11/12">
        <input
            type="text"
            name="name"
            placeholder="Nom du fichier"
            v-model="form.name"
            class="mr-10 rounded-md text-black block w-full grow"
        />
        <div v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</div>

        <div class="my-6 mx-auto w-full">
            <label for="file_input" class="font-semibold mb-2 block">
                {{ file ? 'Remplacer le fichier (Optionnel) 📁' : 'Ajouter un fichier 📁' }}
            </label>

            <label
                for="files_input"
                class="p-4 border-indigo-300 rounded-lg bg-indigo-50 hover:bg-indigo-100 ease-in-out shadow-sm hover:shadow-md flex cursor-pointer items-center justify-center border-2 border-dashed transition duration-300"
            >
                <CloudArrowUpIcon class="w-6 text-indigo-700 mr-1" />
                <span class="text-indigo-700 font-semibold text-base">
                     Cliquez ou glissez-déposez ici pour uploader
                </span>

                <input id="files_input" type="file" name="files[]" @change="handleNewFileUpload" class="sr-only" />
            </label>

            <div v-if="form.errors.files" class="text-red-500 text-sm mt-2">
                {{ form.errors.files }}
            </div>

            <div v-if="form.files.length" class="text-sm text-green-600 mt-2">
                {{ form.files.length }} fichier(s) sélectionné(s).
            </div>
        </div>

        <div class="w-full">
            <p class="mb-1">Départements</p>
            <div v-for="departement in departements" :key="departement.id" class="flex gap-2">
                <input
                    type="checkbox"
                    :id="'dept-' + departement.id"
                    :value="departement.id"
                    v-model="form.departements"
                    :disabled="isCheckboxDisabled(departement.id)"
                    class="my-auto rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
                />
                <label
                    :for="'dept-' + departement.id"
                    class="text-sm text-gray-700 dark:text-gray-100"
                    :class="{ 'opacity-50': isCheckboxDisabled(departement.id) }"
                >
                    {{ departement.name }}
                </label>
            </div>
            <div v-if="form.errors.departements" class="text-red-500">{{ form.errors.departements }}</div>

            <WarningPermission :show="showExternalWarning" object-type="fichier">
                <ul class="ml-4">
                    <li><strong>- Renommer ou supprimer le fichier</strong></li>
                    <li><strong>- Télécharger une nouvelle version (écraser)</strong></li>
                </ul>
            </WarningPermission>
        </div>

        <button type="submit" :disabled="form.processing" class="py-2 mt-4 bg-indigo-600 text-white rounded w-full">
            {{ file ? 'Mettre à Jour' : 'Créer' }}
        </button>
    </form>
</template>

<style scoped></style>
