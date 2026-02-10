<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import route from '@/routes/editor/folder/index.js';
import { Departement } from '@/departement';
import { computed, onMounted } from 'vue';
import WarningPermission from '@/Components/WarningPermission.vue';
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
    name: props.folder?.name ?? '',
    color: props.folder?.color ?? '#d7ac53',
    departements: props.folder?.departements ?? [],
    parent_id: props.folder ? null : (props.parent_id ?? null),
});

const page = usePage();
const userDepartementIds = page.props.auth.user.departements_ids;
const submit = () => {
    if (props.folder) {
        form.patch(route.post.update.url(props.folder.id), {
            method: 'patch',
        });
    } else {
        form.post(route.post.create.url());
    }
};
onMounted(() => {
    const allAvailableDeps = props.departements?.map((d) => d.id) ?? [];
    form.departements = allAvailableDeps.filter((id) => userDepartementIds.includes(id));
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
    // Alors, on le désactive pour empêcher de le décocher.
    return form.departements.includes(departementId) && mySelectedDeps.length <= 1;
};
</script>

<template>
    <Head :title="folder ? `Modifier le dossier ${folder.name}` : 'Créer un nouveau dossier'" />
    <h1 class="text-3xl m-4 text-center first-letter:uppercase">
        {{ folder ? `Modifier le dossier ${folder.name}` : "Creation d'un nouveau dossier" }}
    </h1>

    <hr class="mx-auto w-11/12" />

    <form @submit.prevent="submit" class="pt-2 mt-2 mx-auto w-11/12">
        <div class="flex justify-between">
            <input
                type="text"
                name="title"
                placeholder="Titre du document"
                v-model="form.name"
                class="mr-10 rounded-md
                text-black block grow"
            />
            <input
                v-if="page.props.auth.user.role == 'admin'"
                type="color"
                name="color"
                v-model="form.color"
                class="rounded-md
                text-black block h-auto w-1/6" />
        </div>

        <div class="flex justify-between">
            <div v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</div>
            <div v-if="form.errors.color" class="text-red-500">{{ form.errors.color }}</div>
        </div>

        <div class="md:w-3/4 lg:w-2/3 w-5/6">
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
        </div>
        <WarningPermission :show="showExternalWarning" object-type="dossier">
            <ul class="ml-4">
                <li><strong>- Modifier le nom et les départements du dossier</strong></li>
                <li><strong>- Créer des Dossiers / Documents / Fichiers à l'intérieur</strong></li>
            </ul>
        </WarningPermission>

        <button type="submit" :disabled="form.processing" class="py-2 mt-5 bg-indigo-600 text-white rounded w-full">
            {{ folder ? 'Mettre à Jour' : 'Créer' }}
        </button>
    </form>
</template>
