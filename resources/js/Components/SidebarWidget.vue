<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { DocumentIcon, NewspaperIcon, AdjustmentsHorizontalIcon } from '@heroicons/vue/24/outline';

// Fait correspondre un 'nom' d'icône à un composant
const icons = {
    News: NewspaperIcon,
    Adjustments: AdjustmentsHorizontalIcon,
    default: DocumentIcon, // 'Modifications récentes' n'a pas d'icône, on met celle-ci
};

// Manière propre de faire un defineProps avec TypeScript
// 1. On définit le type pour UN objet dans le tableau
interface Item {
    href: string;
    text: string;
    date?: string; // <-- On ajoute '?' pour dire qu'il est optionnel
    icon?: string; // <-- 'icon' est aussi optionnel
}

// 2. On définit le type pour TOUTES les props du composant
interface Props {
    title: string;
    items: Item[]; // <-- On dit que 'items' est un TABLEAU de 'Item'
    footerText?: string; // <-- Optionnel
    footerLink?: string; // <-- Optionnel
}

// 3. On passe notre interface 'Props' à defineProps
defineProps<Props>();


</script>

<template>
    <section class="bg-white shadow rounded-lg overflow-hidden">
        <h2 class="font-bold text-lg p-4 border-b flex items-center space-x-2 bg-slate-300 dark:bg-slate-800 dark:text-gray-300">
            {{ title }}
        </h2>

        <ul class="divide-y divide-gray-200 dark:bg-zinc-700 dark:text-gray-300">
            <li v-for="item in items" :key="item.text">
                <Link :href="item.href" class="group p-4 block hover:bg-gray-50 hover:dark:bg-slate-800 hover:dark:text-blue-200">
                    <div class="flex items-center space-x-3">
                        <component
                            :is="icons[item.icon] || icons.default"
                            class="h-6 w-6 shrink-0 text-gray-400 dark:text-white group-hover:text-blue-500 group-hover:dark:text-blue-200"
                        />
                        <div>

                            <p class="text-sm text-gray-800 dark:text-gray-300 group-hover:text-blue-600 group-hover:dark:text-blue-200">
                                {{ item.text }}
                            </p>
                            <span v-if="item.date" class="text-xs text-gray-500 dark:text-gray-300">
                {{ item.date }}
              </span>
                        </div>
                    </div>
                </Link>
            </li>
        </ul>

        <footer v-if="footerLink" class="p-4 bg-gray-50 border-t text-blue-600 dark:bg-slate-700 dark:text-blue-200 ">
            <Link :href="footerLink" class="text-sm font-medium  hover:underline">
                {{ footerText }}
            </Link>
        </footer>
    </section>
</template>
