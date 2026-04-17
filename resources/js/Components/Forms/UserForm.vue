<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import DepartementSelectorWidget from "@/Components/Forms/DepartementSelectorWidget.vue";
import { Departement } from "@/types/departement";
import { User } from "@/types";
import NameInputWidget from '@/Components/Forms/NameInputWidget.vue';

const props = defineProps<{
    user?: User;
    departements: Departement[]
}>();
const emit = defineEmits(["success"]);

const form = useForm({
    nom: props.user?.nom ?? "",
    prenom: props.user?.prenom ?? "",
    email: props.user?.email ?? "",
    role: props.user?.role ?? "user",
    departements: props.user?.departements ?? [],
});
const submit = () => {
    const action = props.user ? "patch" : "post";
    const url = props.user ? `/admin/users/${props.user.id}` : "/admin/users";

    form[action](url, {
        onSuccess: () => emit("success"),
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="gap-4 grid grid-cols-2">
            <NameInputWidget
                v-model="form.prenom"
                label="Prénom"
                variant="compact"
                :error="form.errors.prenom"
            />

            <NameInputWidget
                v-model="form.nom"
                label="Nom"
                variant="compact"
                :error="form.errors.nom"
            />
        </div>

        <div class="w-full">
            <label for="role">Rôle</label>
            <select
                name="role"
                v-model="form.role"
                class="px-4 py-3 rounded-xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500/20 focus:border-sky-500 text-sm w-full transition-all"
            >
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="editeur">Éditeur</option>
            </select>
        </div>

        <div class="space-y-1">
            <label class="font-black text-zinc-400 ml-1 text-[10px] uppercase"
                >Email professionnel</label
            >
            <input
                v-model="form.email"
                type="email"
                class="px-4 py-3 rounded-xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500/20 focus:border-sky-500 text-sm w-full transition-all"
            />
        </div>

        <div class="pt-6 dark:border-zinc-800 border-t">
            <DepartementSelectorWidget
                v-model="form.departements"
                :all-departements="departements"
            />
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-bold rounded-xl shadow-xl w-full transition-all hover:opacity-90 disabled:opacity-50"
        >
            {{ user ? "Mettre à jour le profil" : "Créer l'utilisateur" }}
        </button>
    </form>
</template>
