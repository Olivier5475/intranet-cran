<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import CKEditor5Widget from '@/Components/CKEditor5Widget.vue';
import { Departement } from '@/departement';
import route from '@/routes/editor/document';
import { computed, onMounted } from 'vue';
import { CloudArrowUpIcon } from '@heroicons/vue/24/solid';
import WarningPermission from '@/Components/WarningPermission.vue';

interface Attachment {
    id: number;
    name: string;
}

interface Document {
    id: number;
    title: string;
    content: string;
    color: string;
    attachments: Attachment[];
    departements: number[];
}

const props = defineProps<{
    parent_id: number;
    document?: Document;
    departements: Departement[];
}>();

const page = usePage();

const form = useForm({
    title: props.document?.title ?? '',
    content: props.document?.content ?? '',
    existing_attachments: props.document?.attachments ?? [],
    new_attachments: [] as File[],
    departements: props.document?.departements ?? [],
    ...(page.props.auth.user.role === 'admin' && { color: props.document?.color ?? '#ffffff' }),
    parent_id: props.document ? null : (props.parent_id ?? null),
});

// --- Logique Gestion Utilisateur & Départements ---
const userDepartementIds = page.props.auth.user.departements_ids;

onMounted(() => {
    if (!props.document) {
        const allAvailableDeps = props.departements?.map((d) => d.id) ?? [];
        form.departements = allAvailableDeps.filter((id) => userDepartementIds.includes(id));
    }
});

const showExternalWarning = computed(() => {
    return form.departements.some((selectedId) => !userDepartementIds.includes(selectedId));
});

const handleNewFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.new_attachments = Array.from(target.files || []) as File[];
};

const removeExistingAttachment = (index: number) => {
    form.existing_attachments.splice(index, 1);
};

const submit = () => {
    if (props.document) {
        form.post(route.post.update.url(props.document.id), {
            method: 'patch',
        });
    } else {
        form.post(route.post.create.url());
    }
};

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
    <Head :title="document ? `Modifier le document ${document.title}` : 'Créer un nouveau document'" />
    <h1 class="text-3xl m-4 text-center first-letter:uppercase">
        {{ document ? "Modification d'un document" : "Création d'un nouveau document" }}
    </h1>
    <hr class="mx-auto w-11/12" />
    <form @submit.prevent="submit" class="pt-4 mx-auto w-11/12">
        <div class="flex justify-between">
            <label
                for="title"
                class="rounded-md text-black dark:text-white block grow"
            >
                Titre
            </label>
            <label
                v-if="page.props.auth.user.role == 'admin'"
                type="color" for="color"
                class="rounded-md text-black dark:text-white block w-1/6"
            >
                Couleur
            </label>
        </div>
        <div class="flex justify-between">
            <input
                type="text"
                name="title"
                placeholder="Titre du document"
                v-model="form.title"
                class="rounded-md text-black block grow"
            />
            <input
                v-if="page.props.auth.user.role == 'admin'"
                type="color"
                name="color"
                v-model="form.color"
                class="rounded-md text-black block h-auto w-1/6"
            />
        </div>
        <div class="flex justify-between">
            <div v-if="form.errors.title" class="text-red-500">{{ form.errors.title }}</div>
            <div v-if="form.errors.color" class="text-red-500">{{ form.errors.color }}</div>
        </div>

        <CKEditor5Widget name="content" v-model="form.content" class="mt-4 rounded-md text-black mx-auto block max-w-full"></CKEditor5Widget>
        <div v-if="form.errors.content" class="text-red-500">{{ form.errors.content }}</div>

        <div v-if="form.existing_attachments.length > 0">
            <h3 class="font-semibold my-4 mx-auto w-full">Pièces Jointes Existantes</h3>

            <div v-for="(attachment, index) in form.existing_attachments" :key="attachment.id" class="space-x-3 mb-2 flex items-center">
                <input type="hidden" :name="`existing_attachments.${index}.id`" :value="attachment.id" />

                <input
                    type="text"
                    :name="`existing_attachments.${index}.name`"
                    v-model="attachment.name"
                    placeholder="Nom du fichier"
                    class="p-1 rounded flex-grow border text-black"
                />

                <button type="button" @click="removeExistingAttachment(index)" class="text-red-500 text-sm">Retirer</button>
            </div>
        </div>

        <div class="my-6 mx-auto w-full">
            <label for="new_attachments_input" class="font-semibold mb-2 block"> Ajouter des documents 📁 </label>

            <label
                for="new_attachments_input"
                class="p-4 border-indigo-300 rounded-lg bg-indigo-50 hover:bg-indigo-100 ease-in-out shadow-sm hover:shadow-md flex cursor-pointer items-center justify-center border-2 border-dashed transition duration-300"
            >
                <CloudArrowUpIcon class="w-6 text-indigo-700 mr-1" />
                <span class="text-indigo-700 font-semibold text-base"> Cliquez ou glissez-déposez ici pour uploader (Multiples fichiers) </span>

                <input id="new_attachments_input" type="file" name="new_attachments[]" multiple @change="handleNewFileUpload" class="sr-only" />
            </label>

            <div v-if="form.errors['new_attachments']" class="text-red-500 text-sm mt-2">
                {{ form.errors['new_attachments'] }}
            </div>
            <div v-if="form.errors['new_attachments.0']" class="text-red-500 text-sm mt-2">
                Veuillez vérifier la taille ou le format de vos fichiers.
            </div>

            <div v-if="form.new_attachments.length" class="text-sm text-green-600 mt-2">
                {{ form.new_attachments.length }} fichier(s) sélectionné(s).
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

            <WarningPermission :show="showExternalWarning" object-type="document">
                <ul class="ml-4">
                    <li><strong>- Modifier le contenu et les départements</strong></li>
                    <li><strong>- Ajouter / Supprimer des pièces jointes</strong></li>
                </ul>
            </WarningPermission>
        </div>

        <button type="submit" :disabled="form.processing" class="py-2 mt-4 bg-indigo-600 text-white rounded w-full">
            {{ document ? 'Mettre à Jour' : 'Créer' }}
        </button>
    </form>
</template>

<style scoped></style>
