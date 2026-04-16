<script setup lang="ts">
import { ListBulletIcon, ViewColumnsIcon } from "@heroicons/vue/24/solid";
import RepertoryWidget from "@/Components/Features/Navigation/RepertoryWidget.vue";
import CreationWidget from "@/Components/Features/Navigation/Creation/CreationWidget.vue";
import { Folder } from "@/types/folder";

defineProps<{
    parents: Folder[];
    viewMode: string;
    canEdit: boolean;
    folderId: number;
    fastFolderCreation: boolean;
}>();

const emit = defineEmits<{
    // On utilise la syntaxe "e: string, value: any" pour éviter les conflits d'overload
    (e: 'update:viewMode', value: string): void;
    (e: 'update:fastFolderCreation', value: boolean): void;
}>();

// Petite fonction helper pour forcer le type boolean et éviter l'erreur "undefined"
const handleFastCreationUpdate = (value: boolean | undefined) => {
    emit('update:fastFolderCreation', !!value); // Le "!!" force la conversion en boolean pur
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 p-2 rounded-xl shadow-sm border-gray-100 dark:border-zinc-800 flex items-center justify-between border">

        <RepertoryWidget :parents="parents" />

        <div class="space-x-4 flex items-center">
            <div class="bg-gray-100 dark:bg-slate-800 p-1 rounded-lg flex">
                <button
                    @click="emit('update:viewMode', 'list')"
                    class="p-1.5 rounded-md transition-all"
                    :class="viewMode === 'list'
                        ? 'bg-white dark:bg-slate-700 shadow-sm text-sky-500'
                        : 'text-gray-400 hover:text-gray-600'"
                >
                    <ListBulletIcon class="w-5 h-5" />
                </button>
                <button
                    @click="emit('update:viewMode', 'icon')"
                    class="p-1.5 rounded-md transition-all"
                    :class="viewMode === 'icon'
                        ? 'bg-white dark:bg-slate-700 shadow-sm text-sky-500'
                        : 'text-gray-400 hover:text-gray-600'"
                >
                    <ViewColumnsIcon class="w-5 h-5" />
                </button>
            </div>

            <CreationWidget
                v-if="canEdit"
                :model-value="fastFolderCreation"
                @update:model-value="handleFastCreationUpdate"
                :folder_id="folderId"
                :is-active-fast-creation="fastFolderCreation"
            />
        </div>
    </div>
</template>
