<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PlusIcon, FolderPlusIcon, DocumentPlusIcon, ArrowUpTrayIcon } from '@heroicons/vue/20/solid';
import { ref } from 'vue';
import editor from '@/routes/editor';
const isActive = ref(false);
defineProps<{
    folder_id: number
}>();
const model = defineModel<boolean>();

const toggleMode = () => {
    model.value = !model.value;
}
</script>


<template>
    <div class="relative">
        <button
            @click="isActive = !isActive"
            class="flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg shadow-md transition-all duration-200 active:scale-95"
        >
            <PlusIcon class="w-5 h-5 transition-transform duration-300" :class="{'rotate-45': isActive}" />
            <span class="font-semibold text-sm">Nouveau</span>
        </button>

        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="isActive"
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-xl z-50 py-1 overflow-hidden"
            >
                <Link
                    :href="editor.folder.create.url(folder_id)"
                    @click="isActive = false"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-zinc-200 hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-colors"
                >
                    <FolderPlusIcon class="w-5 h-5 text-sky-500" />
                    Dossier
                </Link>

                <button
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-zinc-200 hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-colors"
                    @click="toggleMode"
                >
                    <FolderPlusIcon class="w-5 h-5 text-violet-500" />
                    Dossier Rapide
                </button>

                <Link
                    :href="editor.document.create.url(folder_id)"
                    @click="isActive = false"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-zinc-200 hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-colors"
                >
                    <DocumentPlusIcon class="w-5 h-5 text-emerald-500" />
                    Document
                </Link>

                <div class="border-t border-gray-100 dark:border-zinc-800 my-1"></div>

                <Link
                    :href="editor.file.create.url(folder_id)"
                    @click="isActive = false"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-zinc-200 hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-colors"
                >
                    <ArrowUpTrayIcon class="w-5 h-5 text-amber-500" />
                    Fichier importé
                </Link>
            </div>
        </Transition>
    </div>
</template>
