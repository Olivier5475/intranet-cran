<script setup lang="ts">
import { ref } from 'vue';
import {
    isDocFile,
    isImageFile,
    isPresentationFile,
    isTabFile,
    isVideoFile,
    isGifFile // 🚀 Ajout de l'import pour le GIF
} from '@/Composables/useDocumentsTypeRegex';

import { ArrowDownTrayIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';

import download from '@/routes/download';
import type { Attachment } from '@/types/attachment';

defineProps<{
    attachments: Attachment[]
}>();

const openPreviews = ref<number[]>([]);

const togglePreview = (id: number) => {
    if (openPreviews.value.includes(id)) {
        openPreviews.value = openPreviews.value.filter(previewId => previewId !== id);
    } else {
        openPreviews.value.push(id);
    }
};

// 🚀 On utilise le mimetype pour tester si on peut faire un aperçu
const isPreviewable = (attachment: Attachment) => {
    const mime = attachment.mimetype;
    return attachment.name.toLowerCase().endsWith('.pdf') ||
        isImageFile(mime) ||
        isGifFile(mime) ||
        isVideoFile(mime);
};
</script>

<template>
    <section class="space-y-8 pb-12 border-slate-100 dark:border-slate-800 pt-10 border-t">
        <div v-for="attachment in attachments" :key="attachment.id" class="px-4 flex flex-col items-center">

            <div class="flex items-center justify-center gap-4 w-full max-w-3xl mx-auto">

                <a
                    class="flex-1 group p-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:shadow-xl hover:border-sky-300 dark:hover:border-sky-500 flex items-center justify-between rounded-full border transition-all duration-300 min-w-0"
                    :href="download.attachment.url(attachment.id)"
                >
                    <span class="pl-4 sm:pl-6 font-bold text-slate-600 dark:text-slate-300 group-hover:text-sky-600 dark:group-hover:text-sky-400 whitespace-nowrap">
                        Télécharger
                    </span>

                    <span
                        :class="[
                            isPresentationFile(attachment.mimetype)
                                ? 'bg-red-500 shadow-red-500/20'
                                : isDocFile(attachment.mimetype)
                                  ? 'bg-blue-500 shadow-blue-500/20'
                                  : isTabFile(attachment.mimetype)
                                    ? 'bg-emerald-500 shadow-emerald-500/20'
                                    : 'bg-violet-500 shadow-violet-500/20',
                            'px-4 sm:px-6 py-4 text-white font-black text-sm gap-2 shadow-lg flex items-center rounded-full transition-transform group-hover:scale-105 overflow-hidden max-w-[60%]',
                        ]"
                    >
                        <ArrowDownTrayIcon class="w-5 h-5 stroke-[3] shrink-0" />
                        <span class="truncate">{{ attachment.name }}</span>
                    </span>
                </a>

                <button
                    v-if="isPreviewable(attachment)"
                    @click="togglePreview(attachment.id)"
                    class="w-[130px] shrink-0 flex justify-center items-center gap-2 px-5 py-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-sm rounded-full border border-slate-200 dark:border-slate-700 transition-colors"
                >
                    <span>Aperçu</span>
                    <ChevronDownIcon
                        class="w-4 h-4 transition-transform duration-300"
                        :class="openPreviews.includes(attachment.id) ? 'rotate-180 text-sky-500' : ''"
                    />
                </button>

                <div v-else class="w-[130px] shrink-0 hidden sm:block pointer-events-none"></div>

            </div>

            <Transition
                enter-active-class="transition-all duration-500 ease-out overflow-hidden"
                enter-from-class="opacity-0 max-h-0 -translate-y-4"
                enter-to-class="opacity-100 max-h-[1500px] translate-y-0"
                leave-active-class="transition-all duration-300 ease-in overflow-hidden"
                leave-from-class="opacity-100 max-h-[1500px] translate-y-0"
                leave-to-class="opacity-0 max-h-0 -translate-y-4"
            >
                <div v-if="openPreviews.includes(attachment.id)" class="w-full mt-8">

                    <div v-if="attachment.name.toLowerCase().endsWith('.pdf')" class="group">
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

                    <figure v-else-if="isImageFile(attachment.mimetype) || isGifFile(attachment.mimetype)" class="flex flex-col items-center">
                        <img
                            :src="download.attachment.url(attachment.id)"
                            :alt="attachment.name"
                            class="md:w-10/12 rounded-2xl shadow-lg border-slate-100 dark:border-slate-800 mx-auto w-full border object-contain"
                        />
                        <figcaption class="mt-4 text-sm text-slate-400 italic">{{ attachment.name }}</figcaption>
                    </figure>

                    <video
                        v-else-if="isVideoFile(attachment.mimetype)"
                        :src="download.attachment.url(attachment.id)"
                        controls
                        class="md:w-9/12 rounded-2xl shadow-2xl mx-auto w-full overflow-hidden"
                    />

                </div>
            </Transition>

        </div>
    </section>
</template>
