<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import route from '@/routes/editor/folder'
const props = defineProps<{
    parent_id?: number,
    folder?: {
        id: number;
        name: string;
        color: string;
    };
}>();

const form = useForm({
    name: props.folder?.name ?? '',
    color: props.folder?.color ?? '#d7ac53',
    parent_id: props.folder ? null : (props.parent_id ?? null),
});

console.log(props.parent_id)
const submit = () => {
    if (props.folder) {
        form.patch(route.post.update.url(props.folder.id), {
            method: 'patch',
        });
    } else {
        form.post(route.post.create.url())
    }
};
</script>

<template>
    <Head :title='folder ? `Modifier le dossier ${folder.name}` : "Créer un nouveau dossier" ' />
    <h1 class="text-3xl m-4 text-center first-letter:uppercase">
        {{ folder ? `Modifier le dossier ${folder.name}` : "Creation d'un nouveau dossier" }}
    </h1>

    <hr class="mx-auto w-11/12" />

    <form @submit.prevent="submit" class="pt-2 mt-2 mx-auto w-11/12">
        <div class="flex justify-between">
            <input type="text" name="title" placeholder="Titre du document" v-model="form.name" class="mr-10 rounded-md text-black block grow" />
            <div v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</div>

            <input type="color" name="color" v-model="form.color" class="rounded-md text-black block h-auto w-1/6" />
            <div v-if="form.errors.color" class="text-red-500">{{ form.errors.color }}</div>
        </div>
        <button
            type="submit"
            :disabled="form.processing"
            class="py-2 mt-5 bg-indigo-600 text-white rounded w-full"
        >
            {{ folder ? 'Mettre à Jour' : 'Créer' }}
        </button>
    </form>
</template>
