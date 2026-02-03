<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Departement } from '@/departement';
import route from '@/routes/editor/file';

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
</script>

<template>
    <Head :title="file ? `Modifier le fichier ${file.name}` : 'Créer un nouveau fichier'" />
    <h1 class="text-3xl m-4 text-center first-letter:uppercase">
        {{ file ? "Modification d'un fichier" : "Creation d'un nouveau fichier" }}
    </h1>
    <hr class="mx-auto w-11/12" />
    <form @submit.prevent="submit" class="pt-4 mx-auto w-11/12">
        <input type="text" name="name" placeholder="Nom du fichier" v-model="form.name" class="mr-10 rounded-md text-black block w-full grow" />
        <div v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</div>

        <div class="my-6 mx-auto w-full">
            <label for="file_input" class="font-semibold mb-2 block"> Ajouter un fichier 📁 </label>

            <label
                for="files_input"
                class="p-4 border-indigo-300 rounded-lg bg-indigo-50 hover:bg-indigo-100 ease-in-out shadow-sm hover:shadow-md flex cursor-pointer items-center justify-center border-2 border-dashed transition duration-300"
            >
                <svg class="w-6 h-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                    ></path>
                </svg>

                <span class="text-indigo-700 font-semibold text-base"> Cliquez ou glissez-déposez ici pour uploader </span>

                <input id="files_input" type="file" name="files[]" @change="handleNewFileUpload" class="sr-only" />
            </label>

            <div v-if="form.errors.files" class="text-red-500 text-sm mt-2">
                {{ form.errors.files }}
            </div>

            <div v-if="form.files.length" class="text-sm text-green-600 mt-2">{{ form.files.length }} fichier(s) sélectionné(s).</div>
        </div>

        <div class="md:w-3/4 lg:w-2/3 w-5/6">
            <p class="mb-1">Départements</p>
            <div v-for="departement in departements" :key="departement.id" class="gap-2 flex">
                <input type="checkbox" :id="'dept-' + departement.id" :value="departement.id" v-model="form.departements" class="my-auto" />
                <label :for="'dept-' + departement.id">{{ departement.name }}</label>
            </div>
            <div v-if="form.errors.departements" class="text-red-500">{{ form.errors.departements }}</div>
        </div>

        <button type="submit" :disabled="form.processing" class="py-2 mt-4 bg-indigo-600 text-white rounded w-full">
            {{ file ? 'Mettre à Jour' : 'Créer' }}
        </button>
    </form>
</template>

<style scoped></style>
