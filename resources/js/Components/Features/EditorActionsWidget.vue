<script setup lang="ts">
// 1. Vue & Core
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { PencilIcon } from '@heroicons/vue/24/solid';

// 3. Composants
import DeleteModal from '@/Components/UI/DeleteModal.vue';

defineProps<{
    links: any,
    is_archived: boolean
}>();

const activeRename = ref(false);

const emit = defineEmits(['activeRename']);

// Menu = Menu dropdown d'action d'un dossier (avec bouton modifier et supprimer)
// pour savoir si le menu est étendu. sert pour quand on passe dessus
const isMenuExpend = ref(false);

// pour garder le menu ouvert même quand la souris n'est pas dessus
const toggleMenu = ref(false);

// savoir si le Modal de validation de suppression est ouvert
const isActiveValidation = ref(false);

const toggleRename = () => {
    activeRename.value = !activeRename.value
    emit('activeRename', activeRename.value)
}
</script>

<template>
    <!--            ACTION EDITEUR            -->
    <div
        class="relative flex justify-end"
        @mouseenter="isMenuExpend = true"
        @mouseleave="isMenuExpend = false"
    >
        <button
            @click="toggleMenu = !toggleMenu"
            class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-700
                rounded-full transition-all group-hover:opacity-100"
        >
            <PencilIcon class="w-5 h-5 text-gray-400" />
        </button>

        <div
            v-if="toggleMenu || isMenuExpend"
            class="top-6 -right-7 w-32 bg-white dark:bg-zinc-900 shadow-xl rounded-xl border-gray-100 dark:border-zinc-700 absolute z-50 border"
        >
            <Link
                v-if="links.history"
                :href="links.history"
                class="px-4 py-2 text-xs hover:bg-gray-40 dark:hover:bg-sky-900/50 text-sky-500 block"
            >
                Historique
            </Link>
            <Link
                :href="links.update"
                class="px-4 py-2 text-xs hover:bg-gray-40 dark:hover:bg-yellow-900/50 text-yellow-600 block"
            >
                Modifier
            </Link>

            <button
                v-if="!is_archived"
                @click="isActiveValidation = true"
                class="px-4 py-2 text-xs hover:bg-red-400 text-left dark:hover:bg-red-900/50 text-red-600 block w-full"
            >
                Archiver
            </button>
            <Link
                v-if="is_archived"
                :href="links.restore"
                method="patch"
                class="px-4 py-2 text-xs hover:bg-emerald-400 w-full text-left block dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-500"
            >
                Restaurer
            </Link>
            <button
                class="px-4 py-2 text-xs hover:bg-purple-400 text-left dark:hover:bg-purple-900/50 text-purple-600 block w-full"
                @click="toggleRename()"
            >
                Renommer
            </button>
        </div>
    </div>


    <DeleteModal
        :show="isActiveValidation"
        :delete-href="links.delete as string"
        @close="isActiveValidation = false"
    />
</template>
