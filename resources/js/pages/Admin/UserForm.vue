<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import route from '@/routes/admin/user/index';

interface User {
    id: number;
    nom: string;
    prenom: string;
    email: string;
    role: string;
    departements: number[];
}

interface Departement {
    id: number;
    name: string;
}
const props = defineProps<{
    user?: User;
    departements: Departement[];
}>();

const form = useForm({
    nom: props.user?.nom ?? '',
    prenom: props.user?.prenom ?? '',
    email: props.user?.email ?? '',
    role: props.user?.role ?? 'user',
    departements: props.user?.departements ?? [],
});


const submit = () => {
    if (props.user) {
        form.patch(route.post.update.url(props.user.id), {
            method: 'patch',
        });
    } else {
        form.post(route.post.create.url());
    }
};
</script>

<template>
    <Head :title="user ? `Modifier l'utilisateur ${user.nom} ${user.prenom}` : 'Créer un nouvel utilisateur'" />
    <h1 class="text-3xl m-4 text-center first-letter:uppercase">
        {{ user ? `Modifier le utilisateur ${user.nom} ${user.prenom}` : "Creation d'un nouvel utilisateur" }}
    </h1>

    <hr class="mx-auto w-11/12" />

    <form @submit.prevent="submit" class="pt-2 mt-2 mx-auto w-11/12 ">
        <div class="">
            <div class="flex justify-between w-full">
                <div class="w-[49%]">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" placeholder="Nom" v-model="form.nom" class="rounded-md text-black block w-full" />
                    <div v-if="form.errors.nom" class="text-red-500">{{ form.errors.nom }}</div>
                </div>
                <div class="w-[49%]">
                    <label for="prenom">Prenom</label>
                    <input type="text" name="prenom" placeholder="Prenom" v-model="form.prenom" class="rounded-md text-black block w-full" />
                    <div v-if="form.errors.prenom" class="text-red-500">{{ form.errors.prenom }}</div>
                </div>
            </div>


            <div class="mt-4">
                <label for="email">Adresse Email</label>
                <input type="text" name="email" v-model="form.email" class="rounded-md text-black block h-auto w-full" />
                <div v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</div>
            </div>

            <div class="flex w-full mt-4">
                <div class="w-5/6 md:w-3/4 lg:w-2/3">
                    <p class="mb-1">Départements</p>
                    <div v-for="departement in departements" :key="departement.id" class="flex gap-2">
                        <input
                            type="checkbox"
                            :id="'dept-' + departement.id"
                            :value="departement.id"
                            v-model="form.departements"
                            class="my-auto"
                        />
                        <label :for="'dept-' + departement.id">{{ departement.name }}</label>
                    </div>
                    <div v-if="form.errors.departements" class="text-red-500">{{ form.errors.departements }}</div>
                </div>

                <div class="w-1/6 md:w-1/4 lg:w-1/3">
                    <label for="role">Rôle</label>
                    <select name="role" v-model="form.role" class="rounded-md text-black block h-auto w-full">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <option value="editeur">Éditeur</option>
                    </select>
                    <div v-if="form.errors.role" class="text-red-500">{{ form.errors.role }}</div>
                </div>
            </div>
        </div>

        <button type="submit" :disabled="form.processing" class="py-2 mt-7 bg-indigo-600 text-white rounded w-full">
            {{ user ? 'Mettre à Jour' : 'Créer' }}
        </button>
    </form>
</template>

<style scoped></style>
