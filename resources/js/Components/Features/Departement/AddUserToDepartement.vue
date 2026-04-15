<script setup lang="ts">
// 1. Vue & Core
import { useForm } from '@inertiajs/vue3';

// 2. Types
import { Departement } from '@/types/departement';
import { User } from '@/types';

// 3. Librairies Tierces (Icons)
import { UserPlusIcon } from '@heroicons/vue/24/outline';

// 4. Routes
import user_route from '@/routes/admin/user'

defineProps<{
    users: User[];
    departement: Departement
}>()
const emit = defineEmits(["success"]);

const addUser = (user : User, departement_id : number) => {

    const form = useForm({
        nom: user.nom,
        prenom: user.prenom,
        role: user.role,
        email: user.email,
        departements: [...user.departements, departement_id] // On ajoute l'ID dynamiquement
    })

    form.patch(user_route.post.update.url(user.id), {
        preserveScroll: true, // Évite que la page remonte au sommet
        onSuccess: () => emit("success")
    })
}
</script>

<template>
    <div class="gap-4 mt-4 grid">
        <div
            v-for="user in users"
            :key="user.id"
            class="group bg-white dark:bg-zinc-900 p-4 rounded-2xl border-zinc-100 dark:border-zinc-800 hover:shadow-md flex items-center justify-between border transition-all"
        >
            <div class="gap-4 flex items-center">
                <div class="w-12 h-12 bg-sky-100 dark:bg-sky-900/30 text-sky-600 font-bold text-xl flex items-center justify-center rounded-full">
                    {{ user.nom[0] }}
                </div>
                <div>
                    <p class="font-bold dark:text-white">{{ user.prenom }} {{ user.nom }}</p>
                    <p class="text-sm text-zinc-500">{{ user.email }}</p>
                </div>
            </div>

            <div class="gap-6 flex items-center">
                    <span
                        class="px-3 py-1 text-xs font-bold tracking-widest mx-auto rounded-full uppercase"
                        :class="
                            user.role === 'admin'
                                ? 'bg-red-100 text-red-600'
                                : user.role === 'editeur'
                                  ? 'bg-yellow-100 text-yellow-600'
                                  : 'bg-emerald-100 text-emerald-600'
                        "
                    >
                        {{ user.role }}
                    </span>
                <div class="gap-2 flex items-center opacity-0 transition-opacity group-hover:opacity-100">
                    <button
                        @click="addUser(user, departement.id)"
                        class="p-2 hover:bg-red-50 dark:hover:bg-emerald-900/20 rounded-lg text-zinc-400 hover:text-emerald-500"
                    >
                        <UserPlusIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
