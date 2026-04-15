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
                        :modelValue="cleanHtml(modelValue)"
                        :editor="editor"
                        :config="config as any"
                        @input="onEditorChange" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
/**
 * This configuration was generated using the CKEditor 5 Builder. You can modify it anytime using this link:
 * https://ckeditor.com/ckeditor-5/builder/#installation/NoFgNARATAdAnDADBSBWKUDsBGTiDMUIAHAGzH6Wr7bXZz7HWqJGLbHE6KYVQoQApgDsUiMMGxgpM6dIC6kREwAmAY2L95QA
 */

import { computed, ref, onMounted } from 'vue';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import DOMPurify from 'dompurify';

// Définition des props pour v-model
defineProps<{
    modelValue: string; // Le contenu actuel, lié via v-model
    name: string; // Le nom du champ pour le formulaire (optionnel ici, mais bonne pratique)
}>();

const cleanHtml = (html: string) => {
    let clean = DOMPurify.sanitize(html, {
        USE_PROFILES: { html: true, svg: true },
        // ON NE BANNI PLUS TOUT LE STYLE
        // On laisse DOMPurify nettoyer le contenu du style par défaut
        FORBID_TAGS: ['script', 'iframe', 'object', 'embed'],
        FORBID_ATTR: ['onerror', 'onclick', 'onload', 'onmouseover'],
        ALLOW_UNKNOWN_PROTOCOLS: false,
    });

    clean = clean.replace(/position\s*:\s*(fixed|absolute)/gi, 'position:static');
    clean = clean.replace(/z-index\s*:\s*\d+/gi, 'z-index:1');
    return clean;
}
// Définition des événements pour v-model
const emit = defineEmits(['update:modelValue']);

const onEditorChange = (newHtmlValue: string) => {
    emit('update:modelValue', newHtmlValue);
};

import {
    ClassicEditor,
    Autosave,
    Essentials,
    Paragraph,
    Alignment,
    AutoImage,
    Autoformat,
    AutoLink,
    ImageBlock,
    BlockQuote,
    Bold,
    CloudServices,
    Code,
    CodeBlock,
    Emoji,
    FontBackgroundColor,
    FontColor,
    FontFamily,
    FontSize,
    Fullscreen,
    GeneralHtmlSupport,
    Heading,
    Highlight,
    HorizontalLine,
    HtmlComment,
    ImageCaption,
    ImageInsertViaUrl,
    ImageStyle,
    ImageTextAlternative,
    ImageToolbar,
    ImageUpload,
    ImageInline,
    Indent,
    IndentBlock,
    Italic,
    Link,
    LinkImage,
    List,
    MediaEmbed,
    Mention,
    PlainTableOutput,
    ShowBlocks,
    SourceEditing,
    Strikethrough,
    Style,
    Subscript,
    Superscript,
    Table,
    TableCaption,
    TableToolbar,
    TextPartLanguage,
    TextTransformation,
    TodoList,
    Underline,
    BalloonToolbar,
    BlockToolbar,
    SimpleUploadAdapter,
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';
import route from '@/routes/document';

const LICENSE_KEY = 'GPL';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const isLayoutReady = ref(false);

const editor = ClassicEditor;

const config = computed(() => {
    if (!isLayoutReady.value) {
        return null;
    }

    return {
        toolbar: {
            items: [
                'fullscreen',
                'undo',
                'redo',
                '|',
                'sourceEditing',
                'showBlocks',
                '|',
                'fontSize',
                'fontFamily',
                'fontColor',
                'fontBackgroundColor',
                '|',
                'bold',
                'italic',
                'underline',
                'strikethrough',
                'subscript',
                'superscript',
                'code',
                '|',
                'emoji',
                'horizontalLine',
                'link',
                'insertImageViaUrl',
                'mediaEmbed',
                'insertTable',
                'highlight',
                'blockQuote',
                'codeBlock',
                '|',
                'alignment',
                '|',
                'bulletedList',
                'numberedList',
                'todoList',
                'outdent',
                'indent',
            ],
            shouldNotGroupWhenFull: false,
        },
        plugins: [
            SimpleUploadAdapter,
            Alignment,
            Autoformat,
            AutoImage,
            AutoLink,
            Autosave,
            BalloonToolbar,
            BlockQuote,
            BlockToolbar,
            Bold,
            CloudServices,
            Code,
            CodeBlock,
            Emoji,
            Essentials,
            FontBackgroundColor,
            FontColor,
            FontFamily,
            FontSize,
            Fullscreen,
            GeneralHtmlSupport,
            Heading,
            Highlight,
            HorizontalLine,
            HtmlComment,
            ImageBlock,
            ImageCaption,
            ImageInline,
            ImageInsertViaUrl,
            ImageStyle,
            ImageTextAlternative,
            ImageToolbar,
            ImageUpload,
            Indent,
            IndentBlock,
            Italic,
            Link,
            LinkImage,
            List,
            MediaEmbed,
            Mention,
            Paragraph,
            PlainTableOutput,
            ShowBlocks,
            SourceEditing,
            Strikethrough,
            Style,
            Subscript,
            Superscript,
            Table,
            TableCaption,
            TableToolbar,
            TextPartLanguage,
            TextTransformation,
            TodoList,
            Underline,
        ],
        simpleUpload: {
            uploadUrl: route.uploadImage.url(),
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
        },
        balloonToolbar: ['bold', 'italic', '|', 'link', '|', 'bulletedList', 'numberedList'],
        blockToolbar: [
            'fontSize',
            'fontColor',
            'fontBackgroundColor',
            '|',
            'bold',
            'italic',
            '|',
            'link',
            'insertTable',
            '|',
            'bulletedList',
            'numberedList',
            'outdent',
            'indent',
        ],
        fontFamily: {
            supportAllValues: true,
        },
        fontSize: {
            options: [10, 12, 14, 'default', 18, 20, 22],
            supportAllValues: true,
        },
        fullscreen: {
            onEnterCallback: (container: {
                classList: { add: (arg0: string, arg1: string, arg2: string, arg3: string, arg4: string, arg5: string) => any };
            }) =>
                container.classList.add(
                    'editor-container',
                    'editor-container_classic-editor',
                    'editor-container_include-style',
                    'editor-container_include-block-toolbar',
                    'editor-container_include-fullscreen',
                    'main-container',
                ),
        },
        heading: {
            options: [
                {
                    model: 'paragraph',
                    title: 'Paragraph',
                    class: 'ck-heading_paragraph',
                },
                {
                    model: 'heading1',
                    view: 'h1',
                    title: 'Heading 1',
                    class: 'ck-heading_heading1',
                },
                {
                    model: 'heading2',
                    view: 'h2',
                    title: 'Heading 2',
                    class: 'ck-heading_heading2',
                },
                {
                    model: 'heading3',
                    view: 'h3',
                    title: 'Heading 3',
                    class: 'ck-heading_heading3',
                },
                {
                    model: 'heading4',
                    view: 'h4',
                    title: 'Heading 4',
                    class: 'ck-heading_heading4',
                },
                {
                    model: 'heading5',
                    view: 'h5',
                    title: 'Heading 5',
                    class: 'ck-heading_heading5',
                },
                {
                    model: 'heading6',
                    view: 'h6',
                    title: 'Heading 6',
                    class: 'ck-heading_heading6',
                },
            ],
        },
        htmlSupport: {
            allow: [
                {
                    name: /^.*$/,
                    styles: true,
                    attributes: true,
                    classes: true,
                },
            ],
        },
        image: {
            toolbar: ['toggleImageCaption', 'imageTextAlternative', '|', 'imageStyle:inline', 'imageStyle:wrapText', 'imageStyle:breakText'],
        },
        initialData:
            '<h2>Congratulations on setting up CKEditor 5! 🎉</h2>\n<p>\n\tYou\'ve successfully created a CKEditor 5 project. This powerful text editor\n\twill enhance your application, enabling rich text editing capabilities that\n\tare customizable and easy to use.\n</p>\n<h3>What\'s next?</h3>\n<ol>\n\t<li>\n\t\t<strong>Integrate into your app</strong>: time to bring the editing into\n\t\tyour application. Take the code you created and add to your application.\n\t</li>\n\t<li>\n\t\t<strong>Explore features:</strong> Experiment with different plugins and\n\t\ttoolbar options to discover what works best for your needs.\n\t</li>\n\t<li>\n\t\t<strong>Customize your editor:</strong> Tailor the editor\'s\n\t\tconfiguration to match your application\'s style and requirements. Or\n\t\teven write your plugin!\n\t</li>\n</ol>\n<p>\n\tKeep experimenting, and don\'t hesitate to push the boundaries of what you\n\tcan achieve with CKEditor 5. Your feedback is invaluable to us as we strive\n\tto improve and evolve. Happy editing!\n</p>\n<h3>Helpful resources</h3>\n<ul>\n\t<li>📝 <a href="https://portal.ckeditor.com/checkout?plan=free">Trial sign up</a>,</li>\n\t<li>📕 <a href="https://ckeditor.com/docs/ckeditor5/latest/installation/index.html">Documentation</a>,</li>\n\t<li>⭐️ <a href="https://github.com/ckeditor/ckeditor5">GitHub</a> (star us if you can!),</li>\n\t<li>🏠 <a href="https://ckeditor.com">CKEditor Homepage</a>,</li>\n\t<li>🧑‍💻 <a href="https://ckeditor.com/ckeditor-5/demo/">CKEditor 5 Demos</a>,</li>\n</ul>\n<h3>Need help?</h3>\n<p>\n\tSee this text, but the editor is not starting up? Check the browser\'s\n\tconsole for clues and guidance. It may be related to an incorrect license\n\tkey if you use premium features or another feature-related requirement. If\n\tyou cannot make it work, file a GitHub issue, and we will help as soon as\n\tpossible!\n</p>\n',
        licenseKey: LICENSE_KEY,
        link: {
            addTargetToExternalLinks: true,
            defaultProtocol: 'https://',
            decorators: {
                toggleDownloadable: {
                    mode: 'manual',
                    label: 'Downloadable',
                    attributes: {
                        download: 'file',
                    },
                },
            },
        },
        mention: {
            feeds: [
                {
                    marker: '@',
                    feed: [
                        /* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
                    ],
                },
            ],
        },
        menuBar: {
            isVisible: true,
        },
        placeholder: 'Type or paste your content here!',
        style: {
            definitions: [
                {
                    name: 'Article category',
                    element: 'h3',
                    classes: ['category'],
                },
                {
                    name: 'Title',
                    element: 'h2',
                    classes: ['document-title'],
                },
                {
                    name: 'Subtitle',
                    element: 'h3',
                    classes: ['document-subtitle'],
                },
                {
                    name: 'Info box',
                    element: 'p',
                    classes: ['info-box'],
                },
                {
                    name: 'CTA Link Primary',
                    element: 'a',
                    classes: ['button', 'button--green'],
                },
                {
                    name: 'CTA Link Secondary',
                    element: 'a',
                    classes: ['button', 'button--black'],
                },
                {
                    name: 'Marker',
                    element: 'span',
                    classes: ['marker'],
                },
                {
                    name: 'Spoiler',
                    element: 'span',
                    classes: ['spoiler'],
                },
            ],
        },
        table: {
            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
        },
    };
});

onMounted(() => {
    isLayoutReady.value = true;
});
</script>
