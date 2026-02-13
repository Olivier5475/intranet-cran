<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/20/solid';

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
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">
                    Prénom
                </label>
                <input
                    v-model="form.prenom"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border-zinc-200
                    dark:border-zinc-700 dark:bg-zinc-800
                    focus:ring-sky-500/20 focus:border-sky-500
                    transition-all text-sm"
                />
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">
                    Nom
                </label>
                <input
                    v-model="form.nom"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border-zinc-200
                    dark:border-zinc-700 dark:bg-zinc-800
                    focus:ring-sky-500/20 focus:border-sky-500
                    transition-all text-sm"
                />
            </div>
        </div>

        <div class="w-full">
            <label for="role">Rôle</label>
            <select
                name="role"
                v-model="form.role"
                class="w-full px-4 py-3 rounded-xl border-zinc-200
                    dark:border-zinc-700 dark:bg-zinc-800
                    focus:ring-sky-500/20 focus:border-sky-500
                    transition-all text-sm"
            >
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="editeur">Éditeur</option>
            </select>
            <div v-if="form.errors.role" class="text-red-500">{{ form.errors.role }}</div>
        </div>

        <div class="space-y-1">
            <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">Email professionnel</label>
            <input v-model="form.email" type="email" class="w-full px-4 py-3 rounded-xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500/20 focus:border-sky-500 transition-all text-sm" />
        </div>

        <div class="space-y-3">
            <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">Accès aux départements</label>
            <div class="grid grid-cols-2 gap-2">
                <label v-for="dept in departements" :key="dept.id"
                       :class="[
                        'flex items-center p-3 rounded-xl border-2 cursor-pointer transition-all',
                        form.departements.includes(dept.id) ? 'border-sky-500 bg-sky-50 dark:bg-sky-900/20' : 'border-zinc-100 dark:border-zinc-800'
                    ]"
                >
                    <input type="checkbox" :value="dept.id" v-model="form.departements" class="sr-only" />
                    <div :class="['w-4 h-4 rounded border flex items-center justify-center mr-3 transition-colors', form.departements.includes(dept.id) ? 'bg-sky-500 border-sky-500' : 'border-zinc-300']">
                        <CheckIcon v-if="form.departements.includes(dept.id)" class="w-3 h-3 text-white" />
                    </div>
                    <span class="text-xs font-bold truncate" :class="form.departements.includes(dept.id) ? 'text-sky-700 dark:text-sky-400' : 'text-zinc-500'">{{ dept.name }}</span>
                </label>
            </div>
        </div>

        <button type="submit" :disabled="form.processing" class="w-full py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-bold rounded-xl hover:opacity-90 transition-all disabled:opacity-50 shadow-xl">
            {{ user ? 'Mettre à jour le profil' : 'Créer l\'utilisateur' }}
        </button>
    </form>
</template>
