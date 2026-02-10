<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ChevronRightIcon, ChevronDownIcon } from '@heroicons/vue/20/solid';
import TreeViewItem from './TreeViewItem.vue';
import { Link, usePage } from '@inertiajs/vue3';
import navigate from '@/routes/navigate/index.js';
import folder_route from '@/routes/editor/folder/index.js';
import { EllipsisHorizontalIcon } from '@heroicons/vue/24/solid';
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
</script>

<template>
    <li :id="child.id.toString()">
        <div class="space-x-1 p-1 rounded flex items-center">
            <ChevronRightIcon
                @click="isExpanded = !isExpanded"
                v-if="!isExpanded"
                class="w-5 h-5 hover:bg-slate-200 dark:hover:bg-zinc-600 flex-shrink-0 rounded-full hover:cursor-pointer"
            />
            <ChevronDownIcon
                @click="isExpanded = !isExpanded"
                v-else
                class="w-5 h-5 hover:bg-slate-200 dark:hover:bg-zinc-600 flex-shrink-0 rounded-full hover:cursor-pointer"
            />

            <div class="flex w-full justify-between">
                <Link
                    :href="navigate.folder.url(child.id)"
                    class="hover:text-sky-600 dark:hover:text-sky-300"
                    :class="child.id === currentFolderId ? `text-sky-500 font-bold` : ``">
                    {{ child.name }}
                </Link>

                <div class="relative" @mouseenter="isMenuExpend = true" @mouseleave="isMenuExpend = false">
                    <EllipsisHorizontalIcon class="h-7 w-7 text-gray-700 dark:text-gray-400 rounded-full bg-slate-400 dark:bg-slate-500" />
                    <Link
                        v-if="isMenuExpend"
                        :href="folder_route.update.url(child.id)"
                        class="absolute right-0 top-5 min-w-[5rem] z-10 border-2 font-semibold text-center rounded-md
                        bg-slate-400 border-slate-400 dark:bg-slate-800 dark:border-slate-800 text-yellow-400
                        hover:text-yellow-800 hover:bg-yellow-400 hover:border-yellow-800"
                    >
                        Modifier
                    </Link>
                </div>

            </div>
        </div>

        <ul v-if="isExpanded" class="pl-3 border-gray-300 dark:border-zinc-500 ml-2 border-l">
            <TreeViewItem v-for="subChild in child.children" :key="subChild.id" :child="subChild" />
            <Link
                class="font-extrabold text-yellow-600 h-10 px-3 inline-flex min-w-[40px] items-center overflow-hidden rounded-full transition-all duration-300"
                :href="folder_route.create.url(child.id)"
            >
                <span class="mr-1 min-w-0 flex-1 flex-shrink text-ellipsis whitespace-nowrap"> Nouveau dossier </span>

                <span class="flex-none">+</span>
            </Link>
        </ul>
    </li>
</template>
