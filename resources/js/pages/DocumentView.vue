<script setup lang="ts">
import { isDocFile, isImageFile, isPresentationFile, isTabFile, isVideoFile } from '@/lib/documentsTypeRegex';

defineProps<{
    document : {
        id : number,
        title : string,
        content : string,
        attachments : Array<{
            id           : number,
            name         : string,
            storage_path : string,
            mime_type    : string,
            size         : number
        }>
    }
}>();
</script>

<template>
    <!--    AFFICHAGE TITRE   -->
    <h1 class="text-3xl p-3 dark:bg-slate-700 text-center first-letter:uppercase">
        {{ document.title }}
    </h1>
    <hr class="" />
    <!--    AFFICHAGE TEXTE   -->
    <p class="text-xl p-4 text-justify">
        {{ document.content }}
    </p>

    <!--    AFFICHAGE FICHIER INTEGRER  -->

    <div v-for="attachment in document.attachments" :key="attachment.id" class="">
        <!--        PDF         -->
        <iframe v-if="attachment.storage_path.endsWith('pdf')" :src=attachment.storage_path height="700px" class="mx-auto w-10/12" />

        <!--        IMAGE       -->
        <img v-else-if="isImageFile(attachment.storage_path)" :src=attachment.storage_path :alt=attachment.name class="mx-auto w-8/12" />

        <video v-else-if="isVideoFile(attachment.storage_path)" :src=attachment.storage_path class="mx-auto w-8/12" />

        <!--        SINON TELECHARGER LE FICHIER      -->
        <div v-else class="my-5 ml-10">
            <a class="py-3 bg-white text-gray-700 pl-3 rounded-full" :href=attachment.storage_path download>
                Télécharger le document

                <span v-if="isPresentationFile(attachment.storage_path)" class="bg-red-400 p-3 rounded-full">
                    {{ attachment.name.split('/').at(-1) }}
                </span>

                <span v-else-if="isDocFile(attachment.storage_path)" class="bg-blue-400 p-3 rounded-full">
                    {{ attachment.name.split('/').at(-1) }}
                </span>

                <span v-else-if="isTabFile(attachment.storage_path)" class="bg-green-400 p-3 rounded-full">
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
