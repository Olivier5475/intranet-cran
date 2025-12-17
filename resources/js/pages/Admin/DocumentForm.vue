<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import CKEditor5Widget from '@/Components/CKEditor5Widget.vue';

const props = defineProps<{
    folder_id: number;
    document?: {
        id: number;
        title: string;
        content: string;
        color: string;
        attachments: Array<{
            id: number;
            name: string;
        }>;
    };
}>();

let form;
if (props.document) {
    form = useForm({
        title: props.document.title,
        content: props.document.content,
        color: props.document.color,
        existing_attachments: props.document.attachments || [],

        new_attachments: [] as File[],
    });
} else {
    form = useForm({
        title: '',
        content: '',
        color: '#894f00',
        existing_attachments: [],

        new_attachments: [] as File[],
    });
}

// La fonction 'route' est utilisée directement ici, car elle est globale.
const submitUrl = computed(() => {
    if (props.document) {
        return `/navigation/${props.folder_id}/admin/documents/store/${props.document.id}`;
    }
    return `/navigation/${props.folder_id}/admin/documents/store`; // Route de création
});

const handleNewFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    form.new_attachments = Array.from(target.files || []) as File[];
};

const removeExistingAttachment = (index: number) => {
    form.existing_attachments.splice(index, 1);
};

const submit = () => {
    if(props.document) {
        form.patch(submitUrl.value, {
            method: "patch",
        });
    } else {
        form.post(submitUrl.value, {
            method: "post",
        });
    }
};

</script>

<template>
    <Head :title="document ? `Modifier le document ${document.title}` : 'Créer un nouveau document'" />
    <h1 class="text-3xl m-4 text-center first-letter:uppercase">{{ document ? "Modification d'un document" : "Creation d'un nouveau document" }}</h1>
    <hr class="mx-auto w-11/12" />
    <form @submit.prevent="submit" class="pt-4 mx-auto w-11/12">
        <div class="flex justify-between">
            <input type="text" name="title" placeholder="Titre du document" v-model="form.title" class="mr-10 rounded-md text-black block grow" />
            <div v-if="form.errors.title" class="text-red-500">{{ form.errors.title }}</div>

            <input type="color" name="color" v-model="form.color" class="rounded-md text-black block h-auto w-1/6" />
            <div v-if="form.errors.color" class="text-red-500">{{ form.errors.color }}</div>
        </div>

        <CKEditor5Widget name="content"  v-model="form.content" class="mt-4 rounded-md text-black mx-auto block max-w-full"></CKEditor5Widget>
<!--        <textarea name="content" placeholder="Contenu du document" v-model="form.content" class="m-2 rounded-md text-black mx-auto block w-full" />-->
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
                    class="p-1 rounded flex-grow border"
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
                <svg class="w-6 h-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                    ></path>
                </svg>

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

        <button type="submit" :disabled="form.processing" class="py-2 bg-indigo-600 text-white rounded w-full">
            {{ document ? 'Mettre à Jour' : 'Créer' }}
        </button>
    </form>

</template>

<style scoped></style>
