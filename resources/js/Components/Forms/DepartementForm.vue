<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    departement?: any;
}>();

const emit = defineEmits(['success']);

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
            .toUpperCase()
            .substring(0, 3); // Limite à 3 caractères pour le design
    }
};

const submit = () => {
    const action = props.departement ? 'patch' : 'post';
    const url = props.departement
        ? `/admin/departements/${props.departement.id}`
        : '/admin/departements';

    form[action](url, {
        onSuccess: () => emit('success'),
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="md:col-span-3 space-y-2">
                <label class="text-[11px] font-black uppercase tracking-widest text-zinc-400 ml-1">
                    Nom du département
                </label>
                <input
                    type="text"
                    v-model="form.name"
                    @input="updateInitials"
                    placeholder="Ex: Direction Technique"
                    class="w-full px-5 py-4 rounded-2xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all font-medium"
                />
                <div v-if="form.errors.name" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.name }}</div>
            </div>

            <div class="space-y-2">
                <label class="text-[11px] font-black uppercase tracking-widest text-zinc-400 ml-1">
                    Code
                </label>
                <input
                    type="text"
                    v-model="form.initials"
                    @input="userInteractedWith = true"
                    placeholder="DT"
                    maxlength="4"
                    class="w-full px-4 py-4 rounded-2xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all font-black text-center text-sky-600 dark:text-sky-400"
                />
                <div v-if="form.errors.initials" class="text-xs text-red-500 font-bold ml-1">{{ form.errors.initials }}</div>
            </div>
        </div>

        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-zinc-100 dark:border-zinc-700">
            <p class="text-xs text-zinc-500 leading-relaxed italic text-center">
                Les initiales seront utilisées comme icône par défaut dans l'arborescence et les listes de navigation.
            </p>
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 shadow-xl shadow-zinc-500/10"
        >
            {{ departement ? 'ENREGISTRER LES MODIFICATIONS' : 'CRÉER LE DÉPARTEMENT' }}
        </button>
    </form>
</template>
