<script setup lang="ts">
// 1. Vue & Core
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// 2. Librairie Tierce (Icon)
import {
    PlusIcon,
    FolderPlusIcon,
    DocumentPlusIcon,
    ArrowUpTrayIcon
} from '@heroicons/vue/24/solid';

// 3. Routes
import editor from '@/routes/editor';

const isActive = ref(false);
defineProps<{
    folder_id: number;
}>();
const model = defineModel<boolean>();

const toggleMode = () => {
    model.value = !model.value;
};
</script>

<template>
    <div class="relative">
        <button
            @click="isActive = !isActive"
            class="gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg
            shadow-md flex items-center transition-all duration-200 active:scale-95"
        >
            <PlusIcon class="w-5 h-5 transition-transform duration-300" :class="{ 'rotate-45': isActive }" />
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
                class="right-0 mt-2 w-48 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-700
                rounded-xl shadow-xl py-1 absolute z-50 overflow-hidden border"
            >
                <Link
                    :href="editor.folder.create.url(folder_id)"
                    @click="isActive = false"
                    class="gap-3 px-4 py-2 text-sm text-gray-700 dark:text-zinc-200 hover:bg-sky-50
                    dark:hover:bg-sky-900/20 flex items-center transition-colors"
                >
                    <FolderPlusIcon class="w-5 h-5 text-sky-500" />
                    Dossier
                </Link>

                <button
                    class="gap-3 px-4 py-2 text-sm text-gray-700 dark:text-zinc-200 hover:bg-sky-50
                    dark:hover:bg-sky-900/20 flex w-full items-center transition-colors"
                    @click="toggleMode"
                >
                    <FolderPlusIcon class="w-5 h-5 text-violet-500" />
                    Dossier Rapide
                </button>

                <Link
                    :href="editor.document.create.url(folder_id)"
                    @click="isActive = false"
                    class="gap-3 px-4 py-2 text-sm text-gray-700 dark:text-zinc-200 hover:bg-sky-50
                    dark:hover:bg-sky-900/20 flex items-center transition-colors"
                >
                    <DocumentPlusIcon class="w-5 h-5 text-emerald-500" />
                    Document
                </Link>

                <div class="border-gray-100 dark:border-zinc-800 my-1 border-t"></div>

                <Link
                    :href="editor.file.create.url(folder_id)"
                    @click="isActive = false"
                    class="gap-3 px-4 py-2 text-sm text-gray-700 dark:text-zinc-200 hover:bg-sky-50
                    dark:hover:bg-sky-900/20 flex items-center transition-colors"
                >
                    <ArrowUpTrayIcon class="w-5 h-5 text-amber-500" />
                    Fichier importé
                </Link>
            </div>
        </Transition>
    </div>
</template>
