<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Departement } from '@/departement';
import route from '@/routes/admin/departements';

const props = defineProps<{
    departement: Departement;
}>();

const form = useForm({
    name: props.departement?.name ?? '',
    initials: props.departement?.initials ?? '',
});

const userInteractedWith = ref(!!props.departement);
const updateInitials = () => {
    if (!userInteractedWith.value) {
        form.initials = form.name
            .split(' ')
            .map((word) => word[0])
            .join('')
            .toUpperCase();
    }
};
const submit = () => {
    if (props.departement) {
        form.patch(route.post.update.url({ id: props.departement.id }));
    } else {
        form.post(route.post.create.url());
    }
};
</script>

<template>
    <Head :title="departement ? `Modifier le departement ${departement.name}` : 'Créer un nouveau departement'" />
    <h1 class="text-3xl m-4 text-center first-letter:uppercase">
        {{ departement ? `Modifier le utilisateur ${departement.name} ` : "Creation d'un nouveau departement" }}
    </h1>

    <hr class="mx-auto w-11/12" />

    <form @submit.prevent="submit" class="pt-2 mt-2 mx-auto w-11/12">
        <div class="">
            <div class="flex w-full justify-between">
                <div class="w-[49%]">
                    <label for="nom">Nom</label>
                    <input
                        type="text"
                        name="nom"
                        placeholder="Nom"
                        v-model="form.name"
                        v-on:input="updateInitials()"
                        class="rounded-md text-black block w-full"
                    />
                    <div v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</div>
                </div>
                <div class="w-[49%]">
                    <label for="prenom">Initials</label>
                    <input
                        type="text"
                        name="initials"
                        placeholder="Initials"
                        v-model="form.initials"
                        v-on:input="userInteractedWith = true"
                        class="rounded-md text-black block w-full"
                    />
                    <div v-if="form.errors.initials" class="text-red-500">{{ form.errors.initials }}</div>
                </div>
            </div>
        </div>
        <button type="submit" :disabled="form.processing" class="py-2 mt-7 bg-indigo-600 text-white rounded w-full">
            {{ departement ? 'Mettre à Jour' : 'Créer' }}
        </button>
    </form>
</template>

<style scoped></style>
