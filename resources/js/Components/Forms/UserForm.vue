<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{ user?: any; departements: any[] }>();
const emit = defineEmits(['success']);

const form = useForm({
    nom: props.user?.nom ?? '',
    prenom: props.user?.prenom ?? '',
    email: props.user?.email ?? '',
    role: props.user?.role ?? 'user',
    departements: props.user?.departements ?? [],
});
const submit = () => {
    const action = props.user ? 'patch' : 'post';
    const url = props.user ? `/admin/users/${props.user.id}` : '/admin/users';

    form[action](url, {
        onSuccess: () => emit('success'),
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="gap-4 grid grid-cols-2">
            <div class="space-y-1">
                <label class="font-black text-zinc-400 ml-1 text-[10px] uppercase"> Prénom </label>
                <input
                    v-model="form.prenom"
                    type="text"
                    class="px-4 py-3 rounded-xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500/20 focus:border-sky-500 text-sm w-full transition-all"
                />
            </div>
            <div class="space-y-1">
                <label class="font-black text-zinc-400 ml-1 text-[10px] uppercase"> Nom </label>
                <input
                    v-model="form.nom"
                    type="text"
                    class="px-4 py-3 rounded-xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500/20 focus:border-sky-500 text-sm w-full transition-all"
                />
            </div>
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
            <label class="font-black text-zinc-400 ml-1 text-[10px] uppercase">Email professionnel</label>
            <input
                v-model="form.email"
                type="email"
                class="px-4 py-3 rounded-xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500/20 focus:border-sky-500 text-sm w-full transition-all"
            />
        </div>

        <div class="space-y-3">
            <label class="font-black text-zinc-400 ml-1 text-[10px] uppercase">Accès aux départements</label>
            <div class="gap-2 grid grid-cols-2">
                <label
                    v-for="dept in departements"
                    :key="dept.id"
                    :class="[
                        'p-3 rounded-xl flex cursor-pointer items-center border-2 transition-all',
                        form.departements.includes(dept.id) ? 'border-sky-500 bg-sky-50 dark:bg-sky-900/20' : 'border-zinc-100 dark:border-zinc-800',
                    ]"
                >
                    <input type="checkbox" :value="dept.id" v-model="form.departements" class="sr-only" />
                    <div
                        :class="[
                            'w-4 h-4 rounded mr-3 flex items-center justify-center border transition-colors',
                            form.departements.includes(dept.id) ? 'bg-sky-500 border-sky-500' : 'border-zinc-300',
                        ]"
                    >
                        <CheckIcon v-if="form.departements.includes(dept.id)" class="w-3 h-3 text-white" />
                    </div>
                    <span
                        class="text-xs font-bold truncate"
                        :class="form.departements.includes(dept.id) ? 'text-sky-700 dark:text-sky-400' : 'text-zinc-500'"
                        >{{ dept.name }}</span
                    >
                </label>
            </div>
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-bold rounded-xl shadow-xl w-full transition-all hover:opacity-90 disabled:opacity-50"
        >
            {{ user ? 'Mettre à jour le profil' : "Créer l'utilisateur" }}
        </button>
    </form>
</template>
