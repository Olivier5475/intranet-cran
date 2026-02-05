<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronDownIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';
import { useResource } from '@/Composables/useResource'; // Import du composable
import ResourceIcon from '@/Components/ResourceIcon.vue'; // Import de l'icone
import DeleteModal from '@/Components/DeleteModal.vue'; // Import de la modale
// REGEX imports... (tu peux aussi les déplacer dans un helper si tu veux afficher le texte "Image", "Video" etc)

const props = defineProps<{
    child: any; // Tu peux remettre ton typage complet ici
    folder_id: number;
}>();

// Utilisation du composable
const { links, itemColor, canEdit } = useResource(props);

const isActive = ref(false);
const isActiveValidation = ref(false);
</script>

<template>
    <div class="hover:bg-blue-400 hover:bg-opacity-50 py-1 border-gray-200 relative ml-[1%] grid w-[95%] grid-cols-12 border-t-2 transition-all duration-150">

        <component
            :is="child.type !== 'file' ? Link : 'a'"
            :href="links.href"
            class="gap-2 col-span-4 col-start-1 flex"
        >
            <div class="w-8 aspect-square">
                <ResourceIcon :child="child" :color="itemColor" class="w-full h-full" />
            </div>
            <p class="text-black dark:text-gray-200 my-auto w-full overflow-hidden">
                {{ child.name }}
            </p>
        </component>

        <div class="mx-2 col-span-3 col-start-5 m-auto text-center verflow-hidden">
            <span v-if="child.type == 'folder'">Dossier</span>
            <span v-else-if="child.type == 'document'">Document</span>
            <span v-else>Fichier</span>
        </div>

        <p class="h-7 col-span-3 col-start-8 m-auto text-center overflow-hidden">
            {{ child.created_at }}
        </p>

        <div
            v-if="canEdit"
            class="col-start-12 mx-auto aspect-square bg-slate-500 w-6 rounded-full text-center cursor-pointer"
            @click="isActive = !isActive"
        >
            <ChevronDownIcon v-if="isActive" class="w-4 inline" />
            <ChevronRightIcon v-else class="w-4 inline" />
        </div>

        <div v-if="isActive && canEdit" class="rounded-xl right-0 bottom-negative bg-slate-500 absolute z-10">
            <Link class="rounded-t-xl text-yellow-500 hover:text-white hover:bg-yellow-500 pb-1 pt-2 px-2 block" :href="links.update">
                Update
            </Link>
            <p v-if="links.delete" @click="isActiveValidation = true" class="rounded-b-xl text-red-600 hover:text-white hover:bg-red-600 pt-1 pb-2 px-2 block cursor-pointer">
                Delete
            </p>
        </div>
    </div>

    <DeleteModal
        :show="isActiveValidation"
        :delete-href="links.delete"
        @close="isActiveValidation = false"
    />
</template>

<style scoped>
.bottom-negative { bottom: -4.5rem; }
</style>
