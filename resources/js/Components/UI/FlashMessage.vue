<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/solid';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref<'success' | 'error' | 'warn'>('success');

const config = {
    success: {
        color: 'text-emerald-500',
        bg: 'bg-emerald-50/90 dark:bg-emerald-900/20',
        border: 'border-emerald-200 dark:border-emerald-800',
        icon: CheckCircleIcon,
    },
    warn: {
        color: 'text-amber-500',
        bg: 'bg-amber-50/90 dark:bg-amber-900/20',
        border: 'border-amber-200 dark:border-amber-800',
        icon: ExclamationTriangleIcon,
    },
    error: { color: 'text-rose-500', bg: 'bg-rose-50/90 dark:bg-rose-900/20', border: 'border-rose-200 dark:border-rose-800', icon: XCircleIcon },
};

interface FlashProps {
    success?: string;
    error?: string;
    warning?: string; // Vérifie si c'est 'warn' ou 'warning' dans ton Backend
}

watch(
    // On cast l'objet flash directement dans la source du watch
    () => page.props.flash as FlashProps,
    (flash) => {
        if (!flash) return;

        const msg = flash.success || flash.error || flash.warning;

        if (msg) {
            message.value = msg;
            type.value = flash.success ? 'success' : flash.warning ? 'warn' : 'error';
            show.value = true;

            // On reset le timer si un nouveau message arrive
            setTimeout(() => (show.value = false), 4000);
        }
    },
    { immediate: true, deep: true }, // deep: true est important ici car on surveille un objet
);
</script>

<template>
    <div class="top-6 right-6 max-w-sm pointer-events-none fixed z-[100] w-full">
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="show"
                :class="['rounded-2xl backdrop-blur-md shadow-2xl pointer-events-auto overflow-hidden border', config[type].bg, config[type].border]"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <component :is="config[type].icon" :class="['h-6 w-6', config[type].color]" />
                        </div>
                        <div class="ml-3 w-0 pt-0.5 flex-1">
                            <p class="text-sm font-bold text-gray-900 dark:text-white tracking-wider uppercase">
                                {{ type === 'success' ? 'Succès' : type === 'warn' ? 'Attention' : 'Erreur' }}
                            </p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-zinc-300 leading-relaxed">
                                {{ message }}
                            </p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button @click="show = false" class="rounded-md text-gray-400 hover:text-gray-500 inline-flex focus:outline-none">
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="h-1 bg-gray-200 dark:bg-zinc-700/50 w-full">
                    <div
                        class="h-full transition-all duration-[4000ms] ease-linear"
                        :class="[type === 'success' ? 'bg-emerald-500' : type === 'warn' ? 'bg-amber-500' : 'bg-rose-500']"
                        :style="{ width: show ? '0%' : '100%', transitionDuration: show ? '4000ms' : '0ms' }"
                    ></div>
                </div>
            </div>
        </Transition>
    </div>
</template>
