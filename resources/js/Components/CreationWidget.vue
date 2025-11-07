<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRightIcon, ChevronDownIcon } from '@heroicons/vue/20/solid';
import { ref } from 'vue';

const isActive = ref(false);

const props = defineProps<{folder_id : number}>();

const href = {
  folder : `/navigation/${props.folder_id}/admin/folders/create`,
  document: `/navigation/${props.folder_id}/admin/documents/create`,
  file: `/navigation/${props.folder_id}/admin/files/create`,
};

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
            <Link :href=href.folder class="block hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded-sm"> Dossier </Link>
            <Link :href=href.document class="block hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded-sm"> Document </Link>
            <Link :href=href.file class="block hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded-sm"> Fichier importé </Link>
        </div>
    </nav>
</template>
