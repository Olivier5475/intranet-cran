<script setup lang="ts">
import { isDocFile, isImageFile, isPresentationFile, isTabFile, isVideoFile } from '@/lib/documentsTypeRegex';
import { PencilIcon, ArrowDownTrayIcon } from "@heroicons/vue/24/outline"; // Utilisation de v24 pour plus de finesse
import { Link } from '@inertiajs/vue3';
import editor_route from '@/routes/editor';
import download from '@/routes/download';
import { decodeEntities } from '@/lib/utils';

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
</script>

<template>
    <div class="max-w-5xl mx-auto mb-10 overflow-hidden">
        <header class="relative group bg-slate-50 dark:bg-slate-800/50 p-6 rounded-t-2xl border-b border-slate-200 dark:border-slate-700">
            <h1 class="text-4xl text-center first-letter:uppercase font-black text-slate-800 dark:text-white tracking-tight">
                {{ decodeEntities(document.title) }}
            </h1>

            <Link
                :href="editor_route.document.update.url(document.id)"
                class="absolute right-4 top-1/2 -translate-y-1/2 p-3 bg-white dark:bg-slate-700 shadow-sm hover:shadow-md hover:scale-110 rounded-xl transition-all text-sky-600 dark:text-sky-400 border border-slate-100 dark:border-slate-600"
                title="Modifier le document"
            >
                <PencilIcon class="w-6 h-6" />
            </Link>
        </header>

        <article class="bg-white dark:bg-transparent p-8 md:p-12 text-xl leading-relaxed text-justify text-slate-700 dark:text-slate-200">
            <div v-html="document.content" class="ckeditor-content-render prose prose-slate dark:prose-invert max-w-none"></div>
        </article>

        <section class="space-y-12 pb-12 border-t border-slate-100 dark:border-slate-800 pt-10">
            <div v-for="attachment in document.attachments" :key="attachment.id" class="px-4">

                <div v-if="attachment.name.endsWith('pdf')" class="group">
                    <p class="text-center text-xs font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center justify-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        Aperçu PDF : {{ attachment.name }}
                    </p>
                    <iframe
                        :id="'pdf-' + attachment.id"
                        :src="download.attachment.url(attachment.id)"
                        height="800px"
                        class="mx-auto w-full md:w-11/12 bg-white rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700"
                    />
                </div>

                <figure v-else-if="isImageFile(attachment.mimetype)" class="flex flex-col items-center">
                    <img
                        :src="download.attachment.url(attachment.id)"
                        :alt="attachment.name"
                        class="mx-auto w-full md:w-10/12 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-800"
                    />
                    <figcaption class="mt-4 text-sm text-slate-400 italic">{{ attachment.name }}</figcaption>
                </figure>

                <video
                    v-else-if="isVideoFile(attachment.name)"
                    :src="download.attachment.url(attachment.id)"
                    controls
                    class="mx-auto w-full md:w-9/12 rounded-2xl shadow-2xl overflow-hidden"
                />

                <div v-else class="flex justify-center">
                    <a
                        class="group flex items-center gap-6 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full hover:shadow-xl hover:border-sky-300 dark:hover:border-sky-500 transition-all duration-300"
                        :href="download.attachment.url(attachment.id)"
                    >
                        <span class="pl-6 font-bold text-slate-600 dark:text-slate-300 group-hover:text-sky-600 dark:group-hover:text-sky-400">
                            Télécharger le document
                        </span>

                        <span
                            :class="[
                                isPresentationFile(attachment.name) ? 'bg-red-500 shadow-red-500/20' :
                                isDocFile(attachment.name) ? 'bg-blue-500 shadow-blue-500/20' :
                                isTabFile(attachment.name) ? 'bg-emerald-500 shadow-emerald-500/20' :
                                'bg-violet-500 shadow-violet-500/20',
                                'px-6 py-4 rounded-full text-white font-black text-sm flex items-center gap-2 shadow-lg group-hover:scale-105 transition-transform'
                            ]"
                        >
                            <ArrowDownTrayIcon class="w-5 h-5 stroke-[3]" />
                            {{ attachment.name }}
                        </span>
                    </a>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* Amélioration du rendu CKEditor par défaut */
.ckeditor-content-render :deep(h2) {
    @apply text-2xl font-black text-slate-800 dark:text-white mb-4 mt-8 pb-2 border-b dark:border-slate-800;
}
.ckeditor-content-render :deep(p) {
    @apply mb-6;
}
.ckeditor-content-render :deep(ul) {
    @apply list-disc list-inside mb-6 space-y-2;
}
</style>
