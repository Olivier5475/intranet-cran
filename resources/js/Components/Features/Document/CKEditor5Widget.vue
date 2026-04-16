<template>
    <div class="main-container">
        <div
            class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-block-toolbar editor-container_include-fullscreen"
            ref="editorContainerElement"
        >
            <div class="editor-container__editor bg-slate-800">
                <div ref="editorElement" style="">
                    <ckeditor
                        v-if="editor && config"
                        :modelValue="purify(modelValue)"
                        :editor="editor"
                        :config="config as any"
                        @input="onInput" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
// 1. Vue & Core
import { computed, ref, onMounted } from 'vue';

// 2. Librairies tierces (CKEditor & Plugins)
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import 'ckeditor5/ckeditor5.css';
import { ClassicEditor } from 'ckeditor5';

// 3. Composables & Utilitaires locaux
import CKEditorConfig from '@/Composables/useCKEditor5Config';
import { purify } from '@/Composables/usePurifyHTML';

// Définition des props pour v-model
defineProps<{
    modelValue: string; // Le contenu actuel, lié via v-model
    name: string; // Le nom du champ pour le formulaire
}>();

// Définition des événements pour v-model
const emit = defineEmits(['update:modelValue']);

const onInput = (newHtmlValue: string) => {
    emit('update:modelValue', purify(newHtmlValue));};

const isLayoutReady = ref(false);
const editor = ClassicEditor;

const config = computed(() => {
    if (!isLayoutReady.value) {
        return null;
    }

    return CKEditorConfig
});

onMounted(() => {
    isLayoutReady.value = true;
});
</script>
