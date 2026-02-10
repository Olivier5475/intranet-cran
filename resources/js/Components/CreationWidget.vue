<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRightIcon, ChevronDownIcon } from '@heroicons/vue/20/solid';
import { ref } from 'vue';
import editor from '@/routes/editor/index.js';

const isActive = ref(false);

defineProps<{folder_id : number}>();
</script>

<template>
    <nav class="p-2 text-base font-medium text-gray-500 dark:text-white flex items-center relative w-1/6">
        <a @click="isActive = !isActive" class="cursor-pointer w-full block text-right">
            Nouveau
            <span v-if="isActive">  <ChevronDownIcon class="w-7 inline" />     </span>
            <span v-else>           <ChevronRightIcon class="w-7 inline" />    </span>
        </a>

        <div id="menu"
             :class="isActive ? '' : 'hidden'"
             class="absolute top-full bg-white dark:bg-gray-800 shadow-lg p-2 rounded-md min-w-[10em]"
        >
            <Link :href="editor.folder.create.url(folder_id)" class="block hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded-sm"> Dossier </Link>
            <Link :href="editor.document.create.url(folder_id)" class="block hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded-sm"> Document </Link>
            <Link :href="editor.file.create.url(folder_id)" class="block hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded-sm"> Fichier importé </Link>
        </div>
    </nav>
</template>
