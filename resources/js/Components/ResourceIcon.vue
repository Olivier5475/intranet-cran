<script setup lang="ts">
// 1. Vue & Core
import { computed } from "vue";

// 2. Librairie Interne (REGEX)
import {
    isDocFile,
    isGifFile,
    isImageFile,
    isPresentationFile,
    isTabFile,
    isVideoFile,
} from "@/Composables/useDocumentsTypeRegex";

// 3. Librairies tierces (Icônes)
import * as SolidIcons from "@heroicons/vue/24/solid";

const props = defineProps<{
    child: { id: number; type: string; mimetype?: string; name?: string };
    color?: string;
}>();

const iconConfig = computed(() => {
    const mime = props.child.mimetype || "";
    if (props.child.type === "folder")
        return {
            icon: SolidIcons.FolderIcon,
            colorClass: "text-amber-400 dark:text-amber-500",
        };
    if (props.child.type === "document")
        return {
            icon: SolidIcons.DocumentIcon,
            colorClass: "text-sky-500 dark:text-sky-400",
        };

    if (isImageFile(mime))
        return { icon: SolidIcons.PhotoIcon, colorClass: "text-pink-500" };
    if (isVideoFile(mime))
        return { icon: SolidIcons.FilmIcon, colorClass: "text-purple-500" };
    if (isGifFile(mime))
        return { icon: SolidIcons.GifIcon, colorClass: "text-indigo-500" };
    if (isPresentationFile(mime))
        return {
            icon: SolidIcons.PresentationChartLineIcon,
            colorClass: "text-orange-500",
        };
    if (isDocFile(mime))
        return {
            icon: SolidIcons.DocumentTextIcon,
            colorClass: "text-blue-600",
        };
    if (mime.includes("pdf"))
        return {
            icon: SolidIcons.DocumentTextIcon,
            colorClass: "text-red-600",
        };
    if (isTabFile(mime))
        return {
            icon: SolidIcons.TableCellsIcon,
            colorClass: "text-emerald-600",
        };

    return { icon: SolidIcons.PaperClipIcon, colorClass: "text-slate-400" };
});

// Fonction pour détecter si une couleur est "neutre" (Noir ou Blanc)
const isNeutral = (c: string) => {
    const clean = c.toLowerCase().trim();
    return (
        clean === "#ffffff" ||
        clean === "#fff" ||
        clean === "white" ||
        clean === "#000000" ||
        clean === "#000" ||
        clean === "black"
    );
};

const styleObject = computed(() => {
    if (!props.color) return {};

    // SI la couleur est Noir ou Blanc, on ne l'applique pas en "hardcode"
    // On laisse le CSS gérer via 'currentColor'
    if (isNeutral(props.color)) {
        return { color: "currentColor" };
    }

    return { color: props.color };
});
</script>

<template>
    <div class="relative flex h-full w-full items-center justify-center">
        <component
            :is="iconConfig.icon"
            :key="child.id + child.type"
            class="drop-shadow-sm h-full w-full"
            :class="iconConfig.colorClass"
            :style="styleObject"
        />
    </div>
</template>
