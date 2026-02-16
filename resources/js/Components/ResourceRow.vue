<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { EllipsisHorizontalIcon } from '@heroicons/vue/20/solid';
import { useResource } from '@/Composables/useResource'; // Import du composable
import ResourceIcon from '@/Components/ResourceIcon.vue'; // Import de l'icone
import DeleteModal from '@/Components/DeleteModal.vue';
import { decodeEntities } from '@/lib/utils';

// REGEX imports... (tu peux aussi les déplacer dans un helper si tu veux afficher le texte "Image", "Video" etc)

const props = defineProps<{
    child: any; // Tu peux remettre ton typage complet ici
    folder_id: number;
}>();

// Utilisation du composable
const { links, itemColor, canEdit } = useResource(props);

const isMenuExpend = ref(false);
const toggleMenu = ref(false);
const isActiveValidation = ref(false);

</script>

<template>
    <div class="group grid grid-cols-12 items-center py-3 px-4 border-t border-gray-100 dark:border-zinc-800 hover:bg-sky-50/50 dark:hover:bg-slate-900/10 transition-colors duration-150">
        <component :is="child.type !== 'file' ? Link : 'a'" :href="links.href" class="col-span-5 flex items-center space-x-3 overflow-hidden">
            <div class="w-9 h-9 flex-shrink-0">
                <ResourceIcon :child="child" :color="itemColor" class="w-full h-full transform group-hover:scale-110 transition-transform" />
            </div>
            <p class="text-sm font-medium text-gray-700 dark:text-zinc-200 truncate">
                {{ decodeEntities(child.name) }}
            </p>
        </component>

        <div class="col-span-2 text-center text-xs text-gray-500 dark:text-zinc-400">
            <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-slate-800">
                {{ child.type === 'folder' ? 'Dossier' : child.type === 'document' ? 'Document' : 'Fichier' }}
            </span>
        </div>

        <p class="col-span-3 text-center text-xs text-gray-400">
            {{ child.created_at }}
        </p>

        <div
            class="relative  col-start-12 flex justify-end"
            v-if="canEdit"
            @mouseenter="isMenuExpend = true"
            @mouseleave="isMenuExpend = false"
        >
            <button
                @click="toggleMenu = !toggleMenu"
                class="p-1 rounded-full group-hover:opacity-100
                hover:bg-gray-100 dark:hover:bg-zinc-700 transition-all"
            >
                <EllipsisHorizontalIcon class="w-5 h-5 text-gray-400" />
            </button>

            <div
                v-if="toggleMenu || isMenuExpend"
                class="absolute top-6 -right-7 w-32 z-50
                bg-white dark:bg-zinc-900
                shadow-xl rounded-xl border
                border-gray-100 dark:border-zinc-700"
            >
                <Link :href="links.update" class="block px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-yellow-900/50 text-yellow-600">Modifier</Link>
                <button @click="isActiveValidation = true" class="w-full text-left block px-4 py-2 text-xs hover:bg-red-50 dark:hover:bg-red-900/20 text-red-500">Supprimer</button>
            </div>
        </div>
    </div>
    <DeleteModal :show="isActiveValidation" :delete-href="links.delete" @close="isActiveValidation = false" />
</template>

<style scoped>
</style>
