<script setup lang="ts">
import { isDocFile, isImageFile, isVideoFile } from '@/lib/documentsTypeRegex';
import { Link } from '@inertiajs/vue3';

const props  = defineProps<{
    child : {
        id : number,
        name : string,
        type : string
    },
}>()

const typeFromName = (name : string) => {
    // if folder
    // return "folder"
    if(isImageFile(name)) {
        return "image";
    }
    else if(isVideoFile(name)) {
        return "video";
    }
    else if(isDocFile(name)) {
        return "doc";
    }
    else if(name.endsWith("pdf")) {
        return "pdf";
    }

    else {
        return "other";
    }
}

let src  ;
let alt  ;
let href ;
if(props.child.type === "file") {
    const type = typeFromName(props.child.name);
    src = "/images/document/" + type + ".png";
    alt = type + " icone";
    href = ""
} else if(props.child.type === "folder") {
    src = "/images/document/" + props.child.type.toLowerCase() + ".png";
    alt = props.child.type.toLowerCase() + " icone";
    href = "/navigation/" + props.child.id;
} else {
    src = "/images/document/" + props.child.type.toLowerCase() + ".png";
    alt = props.child.type.toLowerCase() + " icone";
    href = "/documents/" + props.child.id;
}
</script>

<template>
    <div class="w-1/6 transition-all duration-150 hover:bg-blue-400 hover:bg-opacity-50">
        <Link
            v-if="child.type !== 'file'"
            :href=href
        >
            <img :src=src :alt=alt class="w-10/12 aspect-square mx-auto">
            <p class="mx-auto overflow-hidden">{{ child.name }}</p>
        </Link>
        <a
            v-else
            href=""
        >
            <img :src=src :alt=alt class="w-10/12 aspect-square mx-auto">
            <p class="mx-auto overflow-hidden">{{ child.name }}</p>
        </a>
    </div>
</template>

<style scoped>
p {
    max-width: 75%;
}
</style>
