<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    show: boolean;
    deleteHref: string | null;
    isArchived: boolean
}>();

const emit = defineEmits(['close']);
</script>

<template>
    <div
        v-if="show && deleteHref"
        class="fixed inset-0 bg-opacity-70 bg-gray-900 w-full h-full z-50
        flex items-start justify-center pt-[10%]"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-lg bg-slate-900 text-white p-6 rounded-lg shadow-2xl">
            <h1 class="text-2xl font-bold mb-4 text-center">Êtes-vous sûr de vouloir supprimer cet élément ?</h1>
            <hr class="border-b border-gray-700 mb-4" />
            <p class="text-md text-gray-300 text-center mb-6">
                {{ isArchived
                        ? 'Cet élément sera irrécupérable'
                        : 'Cet élément sera archivé et ne sera plus visible.'
                }}
            </p>
            <hr class="border-b border-gray-700 mt-1" />
            <div class="flex justify-evenly mt-4">
                <Link
                    method="delete"
                    :href="deleteHref"
                    as="button"
                    class="text-white bg-red-600 w-1/3 py-2 rounded-md hover:bg-red-700
                    transition duration-150 text-center font-semibold"
                >
                    {{
                        isArchived
                            ? 'Supprimer'
                            : 'ARCHIVER'
                    }}
                </Link>
                <button
                    @click="emit('close')"
                    class="text-white bg-green-600 w-1/3 py-2 rounded-md hover:bg-green-700
                     transition duration-150 font-semibold"
                >
                    ANNULER
                </button>
            </div>
        </div>
    </div>
</template>
