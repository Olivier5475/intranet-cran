<script setup lang="ts">
import { ref } from 'vue'; // On importe 'ref' pour gérer l'état (menu ouvert/fermé)
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';
import UserMenu from '@/Components/UserMenu.vue'; // Une icône pour le dropdown

const page = usePage();

// --- Préparation pour le dropdown ---
// On crée une variable qui stockera le nom du menu ouvert (null si aucun)
const activeDropdown = ref(null);

const toggleDropdown = (name) => {
    activeDropdown.value = activeDropdown.value === name ? null : name;
};

// ------------------------------------

defineProps<{
    racineChildren : Array<{
        id : number,
        name : string,
    }>
}>();

// const navLinks = [
//     { href: '/', text: 'Accueil' },
//     {
//         text: 'Fonctionnement CRAN',
//         children: [
//             { href: '/navigation?path=Fonctionnement CRAN/Projet A', text: 'Projet A' },
//             { href: '#', text: 'Projet B' },
//             { href: '#', text: 'Projet C' },
//         ],
//     },
//     {
//         text: 'Activités scientifiques CRAN',
//         children: [
//             { href: '#', text: 'Projet A' },
//             { href: '#', text: 'Projet B' },
//             { href: '#', text: 'Projet C' },
//         ],
//     },
//
//     {
//         text: 'Appels d’offres - emplois',
//         children: [
//             { href: '#', text: 'Projet A' },
//             { href: '#', text: 'Projet B' },
//             { href: '#', text: 'Projet C' },
//         ],
//     },
//     {
//         text: 'Espaces collaboratifs', // C'est maintenant un "parent"
//         children: [
//             { href: '#', text: 'Projet A' },
//             { href: '#', text: 'Projet B' },
//             { href: '#', text: 'Projet C' },
//         ],
//     },
// ];
</script>

<template>
    <nav class="bg-gray-800 flex">
        <ul class="container mx-auto flex flex-wrap items-center space-x-1 p-2">

            <li v-for="child in racineChildren" :key=child.id>

                <Link
                    :href="'/navigation/'+child.id"
                    class="
                        block px-3 py-2 rounded-md font-medium text-sm
                        text-white hover:text-white active:text-white
                    "
                >
                    {{ child.name }}
                </Link>

<!--                <div v-else class="relative">-->
<!--                    <p-->
<!--                        @click="toggleDropdown(link.text)"-->
<!--                        :class="[-->
<!--                            'flex items-center space-x-1 px-3 py-2 rounded-md font-medium text-sm transition-colors',-->
<!--                            activeDropdown === link.text // Style si le menu est ouvert-->
<!--                                ? 'bg-gray-900 text-white'-->
<!--                                : 'text-white hover:bg-gray-700 hover:text-white'-->
<!--                        ]"-->
<!--                    >-->
<!--                        <span>{{ link.text }}</span>-->
<!--                        <ChevronDownIcon class="h-4 w-4" />-->
<!--                    </p>-->

<!--                    <div-->
<!--                        v-if="activeDropdown === link.text"-->
<!--                        class="absolute left-0 mt-2 w-48 origin-top-left rounded-md bg-white shadow-lg z-10"-->
<!--                    >-->
<!--                        <ul class="py-1">-->
<!--                            <li v-for="child in link.children" :key="child.href">-->
<!--                                <Link-->
<!--                                    :href="child.href"-->
<!--                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"-->
<!--                                >-->
<!--                                    {{ child.text }}-->
<!--                                </Link>-->
<!--                            </li>-->
<!--                        </ul>-->
<!--                    </div>-->
<!--                </div>-->

            </li>
        </ul>

        <div class="items-end">
            <UserMenu/>
        </div>
    </nav>
</template>
