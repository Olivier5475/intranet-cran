<script setup lang="ts">
// 1. Vue & Core
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";

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
</script>

<template>
    <div
        class="group bg-white dark:bg-slate-800/30 hover:border-sky-200
        dark:hover:border-sky-900 hover:shadow-md p-4 rounded-2xl relative
         overflow-hidden border border-transparent transition-all duration-200
         hover:overflow-visible"
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
