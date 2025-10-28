<script setup lang="ts">
import { PrinterIcon, EnvelopeIcon, ArrowsPointingOutIcon } from '@heroicons/vue/24/outline';

import organigramme from '/public/images/organigramme.png';
import RepertoryWidget from '@/Components/RepertoryWidget.vue';

</script>

<template>
    <RepertoryWidget :parents=[]></RepertoryWidget>
    <div>
        <div class="p-6 border-b flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Bienvenue sur le site intranet du CRAN
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Par adminsysinfo - <span>Dernière modification 06/10/2025</span>
                </p>
            </div>

            <div class="flex space-x-2">
                <button class="p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-full">
                    <EnvelopeIcon class="h-5 w-5" />
                </button>
                <button class="p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-full">
                    <PrinterIcon class="h-5 w-5" />
                </button>
                <button class="p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-full">
                    <ArrowsPointingOutIcon class="h-5 w-5" />
                </button>
            </div>
        </div>

        <article class="p-6 prose lg:prose-xl max-w-none">

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-md my-6 text-black">
                <p>
                    <strong>
                        <a href="#">Serveur 12+ registre H&S, budget-commandes</a>
                    </strong>
                </p>
                <p class="text-red-600 font-bold">
                    IMPORTANT : pour un poste situé hors des réseaux de l'UL, un client VPN est nécessaire.
                </p>
                <p>Consultez le guide VPN : <a href="#">https://wikidocs.univ-lorraine.fr/...</a></p>
                <p class="text-red-600">En cas de difficulté : contactez votre informaticien.</p>
            </div>

            <div class="text-center">
                <p><strong>L'organigramme du laboratoire et vos contacts</strong></p>
                <img :src="organigramme" alt="Organigramme_CRAN_2025" class="w-full h-auto border">
                <map name="organigramme">
                    <area shape="rect" coords="240,420,468,649" href="#" alt="Service informatique">
                </map>
            </div>

        </article>
    </div>
</template>
