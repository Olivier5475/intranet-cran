<script setup lang="ts">
import { isDocFile, isImageFile, isPresentationFile, isTabFile, isVideoFile } from '@/lib/documentsTypeRegex';
import { PencilIcon } from "@heroicons/vue/20/solid"
import { Link } from '@inertiajs/vue3';
import editor_route from '@/routes/editor';
import download from '@/routes/download';

defineProps<{
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

const decodeEntities = (str : string) => {
    const txt = document.createElement("textarea");
    txt.innerHTML = str;
    return txt.value;
};
</script>

<template>
    <!--    AFFICHAGE TITRE   -->
    <h1 class="relative text-3xl p-3 dark:bg-slate-700 text-center first-letter:uppercase">
        {{ decodeEntities(document.title) }}
        <Link :href="editor_route.document.update.url(document.id)"><PencilIcon class="absolute right-2 top-2 w-6" /></Link>
    </h1>
    <hr class="" />
    <!--    AFFICHAGE TEXTE   -->
    <div class="text-xl p-4 text-justify">
        <div v-html="document.content" class="ckeditor-content-render"></div>    </div>

    <!--    AFFICHAGE FICHIER INTEGRER  -->

    <div v-for="attachment in document.attachments" :key="attachment.id" class="">
        <!--        PDF         -->
        <iframe v-if="attachment.name.endsWith('pdf')" :src="download.attachment.url(attachment.id)" height="700px" class="mx-auto w-10/12" />

        <!--        IMAGE       -->
        <img v-else-if="isImageFile(attachment.mimetype)" :src="download.attachment.url(attachment.id)" :alt=attachment.name class="mx-auto w-11/12" />

        <!--        VIDEO       -->
        <video v-else-if="isVideoFile(attachment.name)" :src="download.attachment.url(attachment.id)" class="mx-auto w-8/12" />

        <!--        SINON TELECHARGER LE FICHIER      -->
        <div v-else class="my-5 ml-10">
            <a class="py-3 bg-white text-gray-700 pl-3 rounded-full" :href="download.attachment.url(attachment.id)">
                Télécharger le document
                <span
                    :class="[
                        isPresentationFile(attachment.name) ? 'bg-red-400' :
                        isDocFile(attachment.name) ? 'bg-blue-400' :
                        isTabFile(attachment.name) ? 'bg-green-400' :
                        'bg-violet-400',
                        'p-3 rounded-full'
                    ]">
                    {{ attachment.name.split('/').at(-1) }}
                </span>
            </a>
        </div>
    </div>
</template>

<style scoped></style>
