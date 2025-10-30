<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3' // 1. Importe le routeur Inertia

const props = defineProps<{
    currentSearch: string | null,
}>()

// 2. Un 'ref' pour binder l'input
const searchQuery = ref(props.currentSearch || '')

// 3. La fonction qui lance la recherche
function search() {
    router.get(
        window.location.pathname, // L'URL actuelle
        { q: searchQuery.value }, // Les paramètres
        {
            preserveState: true, // Ne reset pas l'état local
            replace: true,       // Ne pollue pas l'historique du navigateur
        }
    )
}
</script>

<template>
    <div class="flex mx-auto w-11/12">
        <input
            type="text"
            v-model=searchQuery
            @keyup.enter=search
            placeholder="Rechercher..."
            class="
            w-10/12
            block
            mx-auto
            px-4 py-2 my-3
            border border-gray-300
            rounded-md
            shadow-sm
            text-gray-900
            placeholder-gray-400
            focus:outline-none
            focus:ring-2
            focus:ring-blue-500
            focus:border-transparent
            dark:bg-gray-700
            dark:border-gray-600
            dark:text-gray-100
            dark:placeholder-gray-500
        "
        >

        <button
            class="
            px-4 py-3 my-auto bg-slate-400 rounded-lg
            shadow-gray-500 shadow-md hover:bg-slate-400

            dark:bg-slate-600

            active:shadow-gray-500 active:shadow-lg active:translate-y-px
            transition-all duration-150 ease-in-out"

            @click=search
        >
            Rechercher
        </button>
    </div>
</template>
