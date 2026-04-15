<script setup lang="ts">
import { ref } from 'vue';
import { injectStyles } from '@/Composables/useInjectStyle';
import DOMPurify from 'dompurify';

defineProps<{
    content: string
}>()
const iframeRef = ref<HTMLIFrameElement | null>(null);

const adjustHeight = () => {
    const iframe = iframeRef.value;
    if (iframe && iframe.contentWindow) {
        const doc = iframe.contentWindow.document;
        // On récupère la hauteur réelle du body ou de l'élément racine
        const height = Math.max(
            doc.body.scrollHeight,
            doc.documentElement.scrollHeight
        );
        iframe.style.height = `${height}px`;
    }
};

// On observe les changements de taille (chargement d'images, etc.)
const onIframeLoad = (event: Event) => {
    adjustHeight();
    const iframe = event.target as HTMLIFrameElement;
    if (iframe.contentWindow) {
        const observer = new ResizeObserver(() => adjustHeight());
        observer.observe(iframe.contentWindow.document.body);
    }
};
</script>

<template>
    <article class="bg-white p-8 md:p-12 text-xl leading-relaxed text-justify dark:bg-transparent">
        <iframe
            ref="iframeRef"
            :srcdoc="injectStyles(DOMPurify.sanitize(content))"
            @load="onIframeLoad"
            sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin"
            class="w-full border-none"
            scrolling="no"
        ></iframe>
    </article>
</template>

<style scoped>

</style>
