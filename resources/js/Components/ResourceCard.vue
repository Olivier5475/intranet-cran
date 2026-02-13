<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { EllipsisHorizontalIcon } from '@heroicons/vue/20/solid';
import { useResource } from '@/Composables/useResource';
import ResourceIcon from '@/Components/ResourceIcon.vue';
import DeleteModal from '@/Components/DeleteModal.vue';
import folder_route from '@/routes/editor/folder';

const props = defineProps<{
    child: any;
    folder_id: number;
}>();

const { links, itemColor, canEdit } = useResource(props);

const isMenuExpend = ref(false);
const toggleMenu = ref(false);
const isActiveValidation = ref(false);
</script>

<template>
    <div class="group relative bg-white dark:bg-slate-800/30 border border-transparent hover:border-sky-200 dark:hover:border-sky-900  overflow-hidden hover:overflow-visible hover:shadow-md p-4 rounded-2xl transition-all duration-200">

        <component :is="child.type !== 'file' ? Link : 'a'" :href="links.href" class="flex flex-col items-center">
            <div class="w-16 h-16 mb-3 transition-transform duration-200 group-hover:scale-110 ">
                <ResourceIcon :child="child" :color="itemColor" class="w-full h-full" />
            </div>
            <span class="text-xs font-semibold text-gray-700 dark:text-zinc-200 text-center line-clamp-2 min-h-[2rem] max-w-full">
                {{ child.name }}
            </span>
        </component>

        <div
            class="absolute top-0 right-0"
            v-if="canEdit"
            @mouseenter="isMenuExpend = true"
            @mouseleave="isMenuExpend = false"
        >
            <button
                @click="toggleMenu = !toggleMenu"
                class="p-1 rounded-full opacity-0 group-hover:opacity-100
                hover:bg-gray-100 dark:hover:bg-zinc-700 transition-all"
            >
                <EllipsisHorizontalIcon class="w-5 h-5 text-gray-400" />
            </button>

            <div
                v-if="toggleMenu || isMenuExpend"
                class="absolute top-6 -right-7 w-32 z-30
                bg-white dark:bg-zinc-900
                shadow-xl rounded-xl border
                border-gray-100 dark:border-zinc-700"
            >
                <Link :href="folder_route.update.url(child.id)" class="block px-4 py-2 text-xs hover:bg-gray-50 dark:hover:bg-yellow-900/50 text-yellow-600">Modifier</Link>
                <button @click="isActiveValidation = true" class="w-full text-left block px-4 py-2 text-xs hover:bg-red-50 dark:hover:bg-red-900/20 text-red-500">Supprimer</button>
            </div>
        </div>
    </div>
    <DeleteModal :show="isActiveValidation" :delete-href="links.delete" @close="isActiveValidation = false" />
</template>
<style scoped>
</style>
