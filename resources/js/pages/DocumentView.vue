<script setup lang="ts">
import { isDocFile, isImageFile, isPresentationFile, isTabFile, isVideoFile } from '@/lib/documentsTypeRegex';
import { PencilIcon } from "@heroicons/vue/20/solid"
import { Link } from '@inertiajs/vue3';
defineProps<{
    folder_id: number;
    document : {
        id : number,
        title : string,
        content : string,
        attachments? : Array<{
            id           : number,
            name         : string,
            storage_path : string,
            mimetype     : string,
            size         : number
        }>
    }
}>();
</script>

<template>
    <!--    AFFICHAGE TITRE   -->
    <h1 class="relative text-3xl p-3 dark:bg-slate-700 text-center first-letter:uppercase">
        {{ document.title }}
        <Link :href="`/navigation/`+ folder_id +`/admin/documents/update/` + document.id"><PencilIcon class="absolute right-2 top-2 w-6" /></Link>
    </h1>
    <hr class="" />
    <!--    AFFICHAGE TEXTE   -->
    <div class="text-xl p-4 text-justify">
        <div v-html="document.content" class="ckeditor-content-render"></div>    </div>

    <!--    AFFICHAGE FICHIER INTEGRER  -->

    <div v-for="attachment in document.attachments" :key="attachment.id" class="">
        <!--        PDF         -->
        <iframe v-if="attachment.name.endsWith('pdf')" :src='"/download/attachment/"+attachment.id' height="700px" class="mx-auto w-10/12" />

        <!--        IMAGE       -->
        <img v-else-if="isImageFile(attachment.mimetype)" :src='"/download/attachment/"+attachment.id' :alt=attachment.name class="mx-auto w-11/12" />

        <!--        VIDEO       -->
        <video v-else-if="isVideoFile(attachment.name)" :src='"/download/attachment/"+attachment.id' class="mx-auto w-8/12" />

        <!--        SINON TELECHARGER LE FICHIER      -->
        <div v-else class="my-5 ml-10">
            <a class="py-3 bg-white text-gray-700 pl-3 rounded-full" :href='"/download/attachment/"+attachment.id'>
                Télécharger le document

                <span v-if="isPresentationFile(attachment.name)" class="bg-red-400 p-3 rounded-full">
                    {{ attachment.name.split('/').at(-1) }}
                </span>

                <span v-else-if="isDocFile(attachment.name)" class="bg-blue-400 p-3 rounded-full">
                    {{ attachment.name.split('/').at(-1) }}
                </span>

                <span v-else-if="isTabFile(attachment.name)" class="bg-green-400 p-3 rounded-full">
                    {{ attachment.name.split('/').at(-1) }}
                </span>

                <span v-else class="bg-violet-400 p-3 rounded-full">
                    {{ attachment.name.split('/').at(-1) }}
                </span>
            </a>
        </div>
    </div>
</template>

<style scoped></style>
