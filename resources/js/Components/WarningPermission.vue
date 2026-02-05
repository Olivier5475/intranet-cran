<script setup lang="ts">
import { ExclamationTriangleIcon } from '@heroicons/vue/24/solid';

defineProps<{
    show: boolean;
    objectType?: string; // ex: 'dossier', 'document', 'fichier'
}>();
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-1"
    >
        <div v-if="show" class="mt-4 rounded-md bg-yellow-50 p-4 border border-yellow-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400" aria-hidden="true" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Attention aux droits d'accès</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            En cochant un autre département, le(s) responsable(s) de ce département aura
                            <strong>les droits d'édition</strong> sur votre {{ objectType ?? 'élément' }}.
                        </p>
                        <div>
                            Ainsi il(s) pourra(ont) :
                            <slot />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
