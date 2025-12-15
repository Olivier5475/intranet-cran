<script setup lang="ts">
//REGEX
import { isDocFile, isGifFile, isImageFile, isPresentationFile, isTabFile, isVideoFile } from '@/lib/documentsTypeRegex';

//ICONS
import {
    FolderIcon,
    DocumentIcon,
    DocumentTextIcon,
    TableCellsIcon,
    PresentationChartLineIcon,
    GifIcon,
    PhotoIcon,
    FilmIcon,
    PaperClipIcon,
    ChevronDownIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/solid';

import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    child: {
        id: number;
        name: string;
        type: string;
        mimetype?: string,
        color?: string;
    };
    folder_id: number;
}>();

const isActive = ref(false);

const color = computed(() => {
    return props.child.color;
});

let href;
if (props.child.type === 'file') {
    href = `/download/file/${props.child.id}`;
} else if (props.child.type === 'folder') {
    href = `/navigation/${props.child.id}`;
} else {
    href = `/navigation/${props.folder_id}/documents/${props.child.id}`;
}

const updateHref = `/navigation/${props.folder_id}/admin/${props.child.type}s/update/${props.child.id}`;
const deleteHref = `/navigation/${props.folder_id}/admin/${props.child.type}s/delete/${props.child.id}`;

// On initialise à false pour que la modale soit cachée au début
const isActiveValidation = ref(false);
</script>

<template>
    <div :class="`hover:bg-blue-400 hover:bg-opacity-50 w-[11.35%] ml-[1%] transition-all duration-150 relative`">

        <Link v-if="child.type !== 'file'" :href="href">
            <FolderIcon v-if="child.type === 'folder'" class="color mx-auto aspect-square w-10/12" />
            <DocumentIcon v-else-if="child.type === 'document'" class="color mx-auto aspect-square w-10/12" />
            <p class="text-black dark:text-gray-200 overflow-hidden mx-auto w-full">{{ child.name }}</p>
        </Link>
        <a v-else :href=href >
            <PhotoIcon v-if="child.mimetype && isImageFile(child.mimetype)" class="mx-auto aspect-square w-10/12" />
            <FilmIcon v-else-if="child.mimetype && isVideoFile(child.mimetype)" class="mx-auto aspect-square w-10/12" />
            <GifIcon v-else-if="child.mimetype && isGifFile(child.mimetype)" class="mx-auto aspect-square w-10/12" />
            <PresentationChartLineIcon v-else-if="child.mimetype && isPresentationFile(child.mimetype)" class="text-red-500 mx-auto aspect-square w-10/12" />
            <DocumentTextIcon v-else-if="child.mimetype && isDocFile(child.mimetype)" class="text-sky-500 mx-auto aspect-square w-10/12" />
            <DocumentTextIcon v-else-if="child.mimetype && child.mimetype.includes('pdf')" class="text-red-500 mx-auto aspect-square w-10/12" />
            <TableCellsIcon v-else-if="child.mimetype && isTabFile(child.mimetype)" class="text-green-500 mx-auto aspect-square w-10/12" />
            <PaperClipIcon v-else class="mx-auto aspect-square w-10/12" />
            <p class="overflow-hidden mx-auto">{{ child.name }}</p>
        </a>
        <p v-if=isActive @click="isActive = !isActive" class="text-center bg-slate-500 absolute bottom-0 right-0 w-6 rounded-full aspect-square"> <ChevronDownIcon  class="w-4 inline" /> </p>
        <p v-else        @click="isActive = !isActive" class="text-center bg-slate-500 absolute bottom-0 right-0 w-6 rounded-full aspect-square"> <ChevronRightIcon class="w-4 inline" /> </p>

        <div v-if=isActive class="rounded-xl absolute right-0 bottom-negative bg-slate-500 z-10">
            <Link class="rounded-t-xl text-yellow-500 hover:text-white hover:bg-yellow-500 block pb-1 pt-2 px-2" :href=updateHref > Update </Link>
            <p @click="isActiveValidation = true" class="rounded-b-xl text-red-600    hover:text-white hover:bg-red-600    block pt-1 pb-2 px-2"> Delete </p>
        </div>
    </div>

    <div  :class='(isActiveValidation ? "" : "hidden") + " fixed inset-0 bg-opacity-70 bg-gray-900 w-full h-full z-50 flex items-start justify-center pt-[10%]"' @click.self="isActiveValidation = false">
        <div class="w-full max-w-lg bg-slate-900 text-white p-6 rounded-lg shadow-2xl">
            <h1 class="text-2xl font-bold mb-4 text-center">Êtes-vous sûr de vouloir supprimer cet élément ?</h1>
            <hr class="border-b border-gray-700 mb-4" />
            <p class="text-md text-gray-300 text-center mb-6">La suppression que vous êtes sur le point d'effectuer sera définitive. Êtes-vous sûr de vouloir procéder à la suppression ?</p>
            <hr class="border-b border-gray-700 mt-1" />
            <div class="flex justify-evenly mt-4">
                <Link method="delete" :href="deleteHref" class="text-white bg-red-600 w-1/3 py-2 rounded-md hover:bg-red-700 transition duration-150 text-center font-semibold"> SUPPRIMER </Link>
                <button @click="isActiveValidation = false" class="text-white bg-green-600 w-1/3 py-2 rounded-md hover:bg-green-700 transition duration-150 font-semibold"> ANNULER </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.color {
    color: v-bind(color);
}
.bottom-negative {
    bottom: -4.5rem;
}
</style>
