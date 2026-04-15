<script setup lang="ts">
import {
    isDocFile,
    isImageFile,
    isPresentationFile,
    isTabFile,
    isVideoFile
} from '@/Composables/useDocumentsTypeRegex';

import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

import download from '@/routes/download';
import { Attachment } from '@/types/attachment';

defineProps<{
    attachments: Attachment[]
}>()
</script>

<template>
    <section class="space-y-12 pb-12 border-slate-100 dark:border-slate-800 pt-10 border-t">
        <div v-for="attachment in attachments" :key="attachment.id" class="px-4">
            <div v-if="attachment.name.endsWith('pdf')" class="group">
                <p class="text-xs font-black tracking-widest text-slate-400 mb-4 gap-2 flex items-center justify-center text-center uppercase">
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                    Aperçu PDF : {{ attachment.name }}
                </p>
                <iframe
                    :id="'pdf-' + attachment.id"
                    :src="download.attachment.url(attachment.id)"
                    height="800px"
                    class="md:w-11/12 bg-white rounded-2xl shadow-2xl border-slate-200 dark:border-slate-700 mx-auto w-full border"
                />
            </div>

            <figure v-else-if="isImageFile(attachment.mimetype)" class="flex flex-col items-center">
                <img
                    :src="download.attachment.url(attachment.id)"
                    :alt="attachment.name"
                    class="md:w-10/12 rounded-2xl shadow-lg border-slate-100 dark:border-slate-800 mx-auto w-full border"
                />
                <figcaption class="mt-4 text-sm text-slate-400 italic">{{ attachment.name }}</figcaption>
            </figure>

            <video
                v-else-if="isVideoFile(attachment.name)"
                :src="download.attachment.url(attachment.id)"
                controls
                class="md:w-9/12 rounded-2xl shadow-2xl mx-auto w-full overflow-hidden"
            />

            <div v-else class="flex justify-center">
                <a
                    class="group gap-6 p-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:shadow-xl hover:border-sky-300 dark:hover:border-sky-500 flex items-center rounded-full border transition-all duration-300"
                    :href="download.attachment.url(attachment.id)"
                >
                        <span class="pl-6 font-bold text-slate-600 dark:text-slate-300 group-hover:text-sky-600 dark:group-hover:text-sky-400">
                            Télécharger le document
                        </span>

                    <span
                        :class="[
                                isPresentationFile(attachment.name)
                                    ? 'bg-red-500 shadow-red-500/20'
                                    : isDocFile(attachment.name)
                                      ? 'bg-blue-500 shadow-blue-500/20'
                                      : isTabFile(attachment.name)
                                        ? 'bg-emerald-500 shadow-emerald-500/20'
                                        : 'bg-violet-500 shadow-violet-500/20',
                                'px-6 py-4 text-white font-black text-sm gap-2 shadow-lg flex items-center rounded-full transition-transform group-hover:scale-105',
                            ]"
                    >
                            <ArrowDownTrayIcon class="w-5 h-5 stroke-[3]" />
                            {{ attachment.name }}
                        </span>
                </a>
            </div>
        </div>
    </section>
</template>

<style scoped>

</style>
