<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ChevronRightIcon, ChevronDownIcon } from '@heroicons/vue/20/solid';
import TreeViewItem from './TreeViewItem.vue';
import { Link, usePage } from '@inertiajs/vue3';
import navigate from '@/routes/navigate';
import folder_route from '@/routes/editor/folder';
import { EllipsisHorizontalIcon } from '@heroicons/vue/24/solid';
import DeleteModal from '@/Components/DeleteModal.vue';
interface Child {
    id: number;
    name: string;
    children: Array<Child> | null;
}

const props = defineProps<{
    child: Child;
}>();

const page = usePage();

// 1. Extraire le folder_id de l'URL (/navigation/f/{id})
const currentFolderId = computed(() => {
    const parts = page.url.split('/');
    const navIndex = parts.indexOf('f');
    return navIndex !== -1 ? parseInt(parts[navIndex + 1]) : null;
});

// 2. Fonction récursive pour vérifier si ce nœud ou un descendant est actif
const checkShouldExpand = (item: Child, targetId: number | null): boolean => {
    if (!targetId) return false;
    if (item.id === targetId) return true;
    if (item.children) {
        return item.children.some((sub) => checkShouldExpand(sub, targetId));
    }
    return false;
};

// 3. État initial : true si le dossier actuel (ou un enfant) est détecté
const isExpanded = ref(checkShouldExpand(props.child, currentFolderId.value));

watch(currentFolderId, (newId) => {
    isExpanded.value = checkShouldExpand(props.child, newId);
});

const isMenuExpend = ref(false);
const toggleMenu = ref(false);
const isActiveValidation = ref(false);

</script>

<template>
    <li :id="child.id.toString()" class="select-none">
        <div
            class="group flex items-center p-1 rounded-md transition-colors duration-150 hover:bg-slate-100 dark:hover:bg-sky-900/50"
            :class="{ 'bg-sky-50 dark:bg-sky-900/20': child.id === currentFolderId }"
        >
            <div @click="isExpanded = !isExpanded" class="p-1 cursor-pointer">
                <component
                    :is="isExpanded ? ChevronDownIcon : ChevronRightIcon"
                    class="w-4 h-4 text-gray-500"
                />
            </div>

            <div class="flex flex-1 items-center justify-between ml-1">
                <Link
                    :href="navigate.folder.url(child.id)"
                    class="text-sm truncate"
                    :class="child.id === currentFolderId ? 'text-sky-600 dark:text-sky-400 font-bold' : 'text-gray-700 dark:text-gray-300'"
                >
                    {{ child.name }}
                </Link>
                <div
                    class="relative flex items-center group-hover:opacity-100 transition-opacity"
                    @mouseover="isMenuExpend = true"
                    @mouseleave="isMenuExpend = false"
                >
                    <button @click="toggleMenu = !toggleMenu" class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-zinc-700">
                        <EllipsisHorizontalIcon class="h-5 w-5 text-gray-500" />
                    </button>

                    <div v-if="isMenuExpend || toggleMenu" class="absolute right-0 top-5 z-20 w-32 rounded-lg opacity-25 group-hover:opacity-100 shadow-xl bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 py-1">
                        <Link
                            :href="folder_route.update.url(child.id)"
                            class="block px-4 py-2 text-xs hover:bg-gray-100 dark:hover:bg-yellow-900/50 text-yellow-600"
                        >
                            Modifier
                        </Link>
                        <button
                            @click="isActiveValidation = true"
                            class="w-full text-left block px-4 py-2 text-xs hover:bg-red-50 dark:hover:bg-red-900/50 text-red-500"
                        >
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Transition
            enter-active-class="transition-all duration-300 ease-in-out"
            enter-from-class="max-h-0 opacity-0 overflow-hidden"
            enter-to-class="max-h-[1000px] opacity-100"
            leave-active-class="transition-all duration-200 ease-in-out"
            leave-from-class="max-h-[1000px] opacity-100"
            leave-to-class="max-h-0 opacity-0 overflow-hidden"
        >
            <ul v-if="isExpanded" class="pl-4 ml-3 border-l border-gray-200 dark:border-zinc-700 mt-1 space-y-1">
                <TreeViewItem v-for="subChild in child.children" :key="subChild.id" :child="subChild" />

                <li>
                    <Link
                        class="text-xs flex items-center text-gray-400 hover:text-yellow-600 py-1 transition-colors"
                        :href="folder_route.create.url(child.id)"
                    >
                        <span class="mr-2 text-lg">+</span>
                        Nouveau dossier
                    </Link>
                </li>
            </ul>
        </Transition>
    </li>

    <DeleteModal
        :show="isActiveValidation"
        :delete-href="folder_route.delete.url(child.id)"
        @close="isActiveValidation = false"
    />
</template>
