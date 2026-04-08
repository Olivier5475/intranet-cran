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
        class="group py-3 px-4 border-gray-100 dark:border-zinc-800 hover:bg-sky-50/50
        dark:hover:bg-slate-900/10 grid grid-cols-12 items-center border-t transition-colors
         duration-150"
    >
        <component
            :is="child.type !== 'file' ? Link : 'a'"
            :href="links.href"
            class="space-x-3 col-span-5 flex items-center overflow-hidden"
        >
            <div class="w-9 h-9 flex-shrink-0">
                <ResourceIcon
                    :child="child"
                    :color="itemColor"
                    class="h-full w-full transform transition-transform group-hover:scale-110"
                />
            </div>
            <p
                class="text-sm font-medium text-gray-700 dark:text-zinc-200 truncate"
            >
                {{ decodeEntities(child.name) }}
            </p>
        </component>

        <div
            class="text-xs text-gray-500 dark:text-zinc-400 col-span-2 text-center"
        >
            <span class="px-2 py-1 bg-gray-100 dark:bg-slate-800 rounded-full">
                {{
                    child.type === "folder"
                        ? "Dossier"
                        : child.type === "document"
                          ? "Document"
                          : "Fichier"
                }}
            </span>
        </div>

        <p class="text-xs text-gray-400 col-span-3 text-center">
            {{ child.created_at }}
        </p>

        <div
            class="relative col-start-12 flex justify-end"
            v-if="canEdit"
            @mouseenter="isMenuExpend = true"
            @mouseleave="isMenuExpend = false"
        >
            <button
                @click="toggleMenu = !toggleMenu"
                class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-700
                rounded-full transition-all group-hover:opacity-100"
            >
                <EllipsisHorizontalIcon class="w-5 h-5 text-gray-400" />
            </button>

            <div
                v-if="toggleMenu || isMenuExpend"
                class="top-6 -right-7 w-32 bg-white dark:bg-zinc-900 shadow-xl
                rounded-xl border-gray-100 dark:border-zinc-700 absolute z-50 border"
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
                    class="px-4 py-2 text-xs hover:bg-gray-40
                    dark:hover:bg-yellow-900/50 text-yellow-600 block"
                >
                    Modifier
                </Link>

                <button
                    v-if="!child.is_archived"
                    @click="isActiveValidation = true"
                    class="px-4 py-2 text-xs hover:bg-red-400 text-left
                    dark:hover:bg-red-900/50 text-red-600 block w-full"
                >
                    Archiver
                </button>
                <Link
                    v-if="child.is_archived"
                    :href="links.restore"
                    method="patch"
                    class="px-4 py-2 text-xs hover:bg-emerald-400 w-full text-left block
                    dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-500"
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
