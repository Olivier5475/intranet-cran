<script setup lang="ts">
import { ref } from 'vue';
import { ChevronRightIcon, ChevronDownIcon } from '@heroicons/vue/20/solid';

// Le composant doit s'auto-référencer pour la récursivité.
// On utilise une importation dynamique pour éviter les problèmes de dépendance circulaire.
// Le nom sera utilisé dans le template.
import TreeViewItem from './TreeViewItem.vue';
import { Link } from '@inertiajs/vue3';

interface Child {
    id: number;
    name: string;
    children: Array<Child> | null;
}

const props = defineProps<{
    child: Child; // On passe un seul enfant (le nœud actuel)
}>();

// État pour savoir si ce nœud est ouvert ou non
const isExpanded = ref(false);

// Vérifie si le nœud a des enfants à afficher
const hasChildren = props.child.children && props.child.children.length > 0;

const href = (id : number) => {
    return "/navigation/" + id;
}
</script>

<template>
    <li :id="child.id.toString()">

        <div
            class="flex items-center space-x-1 p-1 rounded "
        >
            <template v-if="hasChildren">
                <ChevronRightIcon @click="isExpanded = !isExpanded" v-if="!isExpanded" class="w-5 h-5 flex-shrink-0 rounded-full hover:bg-slate-200 dark:hover:bg-zinc-600 hover:cursor-pointer" />
                <ChevronDownIcon @click="isExpanded = !isExpanded" v-else class="w-5 h-5 flex-shrink-0 rounded-full hover:bg-slate-200 dark:hover:bg-zinc-600 hover:cursor-pointer" />
            </template>
            <div v-else class="w-5 h-5"></div>

            <Link :href="href(child.id)" class="truncate hover:text-sky-300">{{ child.name }}</Link>
        </div>

        <ul v-if="hasChildren && isExpanded" class="pl-6 border-l border-gray-300 dark:border-zinc-500 ml-2">
            <TreeViewItem
                v-for="subChild in child.children"
                :key="subChild.id"
                :child="subChild"
            />
            <Link class="rounded-full text-lg font-extrabold px-5 bg-gray-400 dark:bg-slate-600 dark:text-slate-400" :href="`/navigation/`+ child.id + `/admin/folders/create`">Nouveau dossier +</Link>

        </ul>
    </li>
</template>
