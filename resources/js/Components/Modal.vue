<script setup lang="ts">
import { XMarkIcon } from '@heroicons/vue/24/outline';
defineProps<{ show: boolean; title: string }>();
const emit = defineEmits(['close']);
</script>

<template>
    <Transition
        enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 w-full max-w-2xl rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b dark:border-zinc-800">
                    <h2 class="text-xl font-bold dark:text-white">{{ title }}</h2>
                    <button @click="emit('close')" class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors">
                        <XMarkIcon class="w-6 h-6 text-zinc-500" />
                    </button>
                </div>
                <div class="p-6 max-h-[80vh] overflow-y-auto">
                    <slot />
                </div>
            </div>
        </div>
    </Transition>
</template>
