<script setup lang="ts">
// 1. Vue & Core
import { computed } from "vue";

// 2. Librairie Interne (REGEX)
import {
    isDocFile,
    isGifFile,
    isImageFile,
    isPresentationFile,
    isTabFile, isTextFile,
    isVideoFile
} from '@/Composables/useDocumentsTypeRegex';

// 3. Librairies tierces (Icônes)
import * as SolidIcons from "@heroicons/vue/24/solid";
import * as OutlineIcons from "@heroicons/vue/24/outline";

const props = defineProps<{
    child: { id: number; type: string; mimetype?: string; name?: string };
    color?: string;
    solid?: boolean
}>();
const iconConfig = computed(() => {
    const mime = props.child.mimetype || "";
    if (props.child.type === "folder")
        return {
            icon: props.solid ? SolidIcons.FolderIcon : OutlineIcons.FolderIcon,
            colorClass: "text-amber-400 dark:text-amber-500",
        };
    if (props.child.type === "document")
        return {
            icon: props.solid ? SolidIcons.DocumentIcon : OutlineIcons.DocumentIcon,
            colorClass: "text-sky-500 dark:text-sky-400",
        };

    if (props.child.type === "appelprojet")
        return {
            icon: props.solid ? SolidIcons.MegaphoneIcon : OutlineIcons.MegaphoneIcon,
        };

    if (isImageFile(mime))
        return {
            icon: props.solid ? SolidIcons.PhotoIcon : OutlineIcons.PhotoIcon,
            colorClass: "text-pink-500"
    };
    if (isVideoFile(mime))
        return {
            icon: props.solid ? SolidIcons.FilmIcon : OutlineIcons.FilmIcon,
            colorClass: "text-purple-500"
    };
    if (isGifFile(mime))
        return {
            icon: props.solid ? SolidIcons.GifIcon : OutlineIcons.GifIcon,
            colorClass: "text-indigo-500"
    };
    if (isPresentationFile(mime))
        return {
            icon: props.solid ? SolidIcons.PresentationChartLineIcon : OutlineIcons.PresentationChartLineIcon,
            colorClass: "text-orange-500",
        };
    if (isDocFile(mime))
        return {
            icon: props.solid ? SolidIcons.DocumentTextIcon : OutlineIcons.DocumentTextIcon,
            colorClass: "text-blue-600",
        };
    if (mime.includes("pdf"))
        return {
            icon: props.solid ? SolidIcons.DocumentTextIcon : OutlineIcons.DocumentTextIcon,
            colorClass: "text-red-600",
        };
    if (isTabFile(mime))
        return {
            icon: props.solid ? SolidIcons.TableCellsIcon : OutlineIcons.TableCellsIcon,
            colorClass: "text-emerald-600",
        };
    if (isTextFile(mime))
        return {
            icon: props.solid ? SolidIcons.DocumentTextIcon : OutlineIcons.DocumentTextIcon,
            colorClass: "text-black dark:text-white",
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
