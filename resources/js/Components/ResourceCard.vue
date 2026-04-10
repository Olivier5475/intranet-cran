<script setup lang="ts">
// 1. Vue & Core
import { ref } from "vue";
import { Link, usePage } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { EllipsisHorizontalIcon } from "@heroicons/vue/24/solid";

// 3. Composables & Utilitaires
import { decodeEntities } from "@/Composables/useDecodeModule";
import { useResource } from "@/Composables/useResource";

// 4. Composants
import DeleteModal from "@/Components/DeleteModal.vue";
import ResourceIcon from "@/Components/ResourceIcon.vue";

// 5. Types
import { Child } from "@/types/child";
import { Departement } from '@/types/departement';
import { isImageFile } from '@/Composables/useDocumentsTypeRegex';

const props = defineProps<{
    child: Child;
    folder_id: number;
}>();

// Utilisation du composable
const { links, itemColor, canEdit } = useResource(props);

// Menu = Menu dropdown d'action d'un dossier (avec bouton modifier et supprimer)
// pour savoir si le menu est étendu. sert pour quand on passe dessus
const isMenuExpend = ref(false);

// pour garder le menu ouvert même quand la souris n'est pas dessus
const toggleMenu = ref(false);

// savoir si le Modal de validation de suppression est ouvert
const isActiveValidation = ref(false);

//
const page = usePage();
// Récupère les informations d'un département grâce à son id
const getDep = (id: number) => {
    return page.props.departements.find((d: Departement) => d.id === id);
};

const hoveredDepId = ref<number | null>(null);

const showImage = ref(false);
const wasShown = ref(false); // Garde en mémoire si on a déjà survolé

const handleMouseEnter = () => {
    showImage.value = true;
    wasShown.value = true; // On active le rendu définitif
};
</script>

<template>
    <div v-if="wasShown && child.storage_path" v-show="showImage" class="absolute">
        <div class="fixed pointer-events-none z-[100] shadow-2xl border-4 bg-white text-black rounded-lg overflow-hidden top-20 right-20">
            <p class="text-center font-extrabold">Prévisualisation</p>

            <img
                v-if="child.mimetype && isImageFile(child.mimetype)"
                :src="'/storage/' + child.storage_path"
                class="min-w-[12rem] max-w-[18rem] h-auto object-cover"
                alt=""
            />

            <iframe
                v-else-if="child.mimetype && child.mimetype.includes('pdf')"
                :src="'/storage/' + child.storage_path"
                frameborder="0"
                width="350px" height="600px"
            ></iframe>
        </div>
    </div>
    <div
        class="group bg-white dark:bg-slate-800/30 hover:border-sky-200
        dark:hover:border-sky-900 hover:shadow-md p-4 rounded-2xl relative
         overflow-hidden border border-transparent transition-all duration-200
         hover:overflow-visible"

        @mouseenter="handleMouseEnter"
        @mouseleave="showImage = false"
    >
        <component
            :is="child.type !== 'file' ? Link : 'a'"
            :href="links.href"
            class="flex flex-col items-center"
        >

            <div
                class="w-16 h-16 mb-3 transition-transform
                duration-200 group-hover:scale-110"
            >
                <ResourceIcon
                    :child="child"
                    :color="itemColor"
                    class="h-full w-full"
                />
            </div>
            <span
                class="text-xs font-semibold text-gray-700 dark:text-zinc-200
                line-clamp-2 min-h-[2rem] max-w-full text-center"
            >
                {{ decodeEntities(child.name) }}
            </span>
        </component>

        <div class="absolute top-1 left-1 flex flex-wrap gap-1 max-w-[90%] z-[50] pointer-events-none">
            <div
                v-for="depId in child.departements"
                :key="depId"
                class="pointer-events-auto relative h-3 transition-all duration-300"
                :style="{ // Si moins de 2 departements, largueur de base en auto, sinon fixé à 0.75rem
                    width: (child.departements as number[]).length <= 2 ? 'auto' : '0.75rem'
                }"
                @mouseenter="hoveredDepId = depId"
                @mouseleave="hoveredDepId = null"
            >
                <div
                    :style="{
                        backgroundColor: getDep(depId)?.color,
                        zIndex: hoveredDepId === depId ? 10 : 1
                    }"
                :class="[
                    // Si le nombre de departement est supérieur à 2,
                    (child.departements as number[]).length > 2
                        // on fixe les pastilles à un endroit
                        ? 'absolute top-0 left-0'
                        // Sinon, on les laisse se placer en ligne.
                        : 'relative',

                    // S\'il y a 2 departements, ou moins par-dessus le departement
                    ((child.departements as number[]).length <= 2 || hoveredDepId === depId)
                        // On fix la largeur à 'fit' et le padding à 1
                        ? 'w-fit px-1'
                        // Sinon, on fix largeur à 3
                        : 'w-3'
                ]"
                    class="h-3 rounded-full flex items-center justify-center
                    transition-all duration-300 shadow-sm whitespace-nowrap"
                >
                    <span
                        v-if="(child.departements as number[]).length <= 3 || hoveredDepId === depId"
                        class="text-[0.5rem] font-black uppercase text-white p-1"
                        :class="getDep(depId)?.color === '#ffffff' ? '!text-black' : ''"
                    >
                        {{ getDep(depId)?.initials }}
                    </span>
                </div>
            </div>
        </div>

        <div
            class="top-0 right-0 absolute"
            v-if="canEdit"
            @mouseenter="isMenuExpend = true"
            @mouseleave="isMenuExpend = false"
        >
            <button
                @click="toggleMenu = !toggleMenu"
                class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full
                opacity-0 transition-all group-hover:opacity-100"
            >
                <EllipsisHorizontalIcon class="w-5 h-5 text-gray-400" />
            </button>

            <div
                v-if="toggleMenu || isMenuExpend"
                class="top-6 -right-7 w-32 bg-white dark:bg-zinc-900
                shadow-xl rounded-xl border-gray-100 dark:border-zinc-700
                absolute z-30 border"
            >
                <Link
                    v-if="links.history"
                    :href="links.history"
                    class="px-4 py-2 text-xs hover:bg-gray-40
                    dark:hover:bg-sky-900/50 text-sky-500 block"
                >
                    Historique
                </Link>
                <Link
                    :href="links.update"
                    class="px-4 py-2 text-xs hover:bg-gray-50
                    dark:hover:bg-yellow-900/50 text-yellow-600 block"
                >
                    Modifier
                </Link>
                <button
                    v-if="!child.is_archived"
                    @click="isActiveValidation = true"
                    class="px-4 py-2 text-xs hover:bg-red-50 dark:hover:bg-red-900/20
                    text-red-500 block w-full text-left"
                >
                    Archiver
                </button>
                <Link
                    v-if="child.is_archived"
                    :href="links.restore"
                    method="patch"
                    class="px-4 py-2 text-xs hover:bg-emerald-400
                    dark:hover:bg-emerald-900/50 text-emerald-600
                    dark:text-emerald-500 block w-full text-left"
                >
                    Restaurer
                </Link>
            </div>
        </div>
    </div>
    <DeleteModal
        :show="isActiveValidation"
        :delete-href="links.delete as string"
        @close="isActiveValidation = false"
    />
</template>
<style scoped></style>
