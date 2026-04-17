<script setup lang="ts">
import { CloudArrowUpIcon, DocumentIcon, CheckIcon } from "@heroicons/vue/24/solid";
import { useDragAndDrop } from "@/Composables/useDragAndDrop"; // Ajuste le chemin

const props = defineProps<{
    modelValue: File[];
    multiple?: boolean;
    error?: string;
    isEdit?: boolean;
}>();

const emit = defineEmits(["update:modelValue"]);

// Logique commune pour traiter les fichiers (Input ou Drop)
const updateFiles = (fileList: FileList | File[]) => {
    const files = Array.from(fileList);
    if (props.multiple) {
        emit("update:modelValue", files);
    } else {
        emit("update:modelValue", files.slice(0, 1));
    }
};

const handleFileInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) updateFiles(target.files);
};

// Intégration du Drag & Drop
const { isDragging } = useDragAndDrop({
    canDrop: true,
    onDrop: (files) => updateFiles(files)
});
</script>

<template>
    <div class="space-y-2">
        <label class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase">
            <slot name="label">Source du document</slot>
        </label>

        <label
            class="group p-8 rounded-3xl shadow-sm hover:shadow-xl relative flex cursor-pointer flex-col items-center justify-center border-2 border-dashed transition-all"
            :class="[
                modelValue.length
                    ? 'border-emerald-500 bg-emerald-500/5'
                    : 'border-sky-200 dark:border-zinc-800 hover:border-sky-400 dark:hover:border-zinc-700',
                isDragging ? 'border-sky-500 bg-sky-500/10 ring-4 ring-sky-500/20 scale-[1.02]' : ''
            ]"
        >
            <span
                class="p-4 bg-sky-50 dark:bg-zinc-800 rounded-full transition-transform duration-300"
                :class="isDragging ? 'scale-110 rotate-3' : 'group-hover:scale-110'"
            >
                <CloudArrowUpIcon class="w-8 h-8 text-sky-600 dark:text-sky-400" />
            </span>

            <span class="mt-4 text-center">
                <span class="text-sm font-bold text-gray-700 dark:text-gray-200 block">
                    {{ isDragging ? "Lâchez pour importer" : (modelValue.length ? (multiple ? `${modelValue.length} fichiers` : "Fichier sélectionné") : "Cliquez ou glissez ici") }}
                </span>
                <span class="text-xs text-gray-400 mt-1 block">
                    {{ isEdit && !modelValue.length ? "(Laissez vide pour conserver)" : "PDF, Image, Word..." }}
                </span>
            </span>

            <input type="file" :multiple="multiple" @change="handleFileInput" class="sr-only" />

            <span v-if="modelValue.length && !isDragging" class="top-4 right-4 gap-1 bg-emerald-500 text-white px-3 py-1 font-black absolute flex items-center rounded-full text-[10px]">
                <CheckIcon class="w-3 h-3" /> PRÊT
            </span>
        </label>

        <div v-if="modelValue.length" class="mt-3 flex flex-wrap gap-2 justify-center">
            <div v-for="file in modelValue" :key="file.name" class="gap-2 px-3 py-1 bg-emerald-500/10 text-emerald-600 text-xs font-bold border-emerald-500/20 flex items-center rounded-full border">
                <DocumentIcon class="w-3 h-3" /> {{ file.name }}
            </div>
        </div>

        <div v-if="error" class="text-xs text-red-500 font-bold mt-2 text-center">
            {{ error }}
        </div>
    </div>
</template>
