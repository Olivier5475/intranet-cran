<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon } from '@heroicons/vue/20/solid';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref<'success' | 'error'>('success');

watch(() => [page.props.flash.success, page.props.flash.error], ([success, error]) => {
    const msg = success || error;
    if (msg) {
        message.value = msg as string;
        type.value = success ? 'success' : 'error';
        show.value = true;
        setTimeout(() => show.value = false, 3000);
    }
}, { immediate: true });
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform opacity-0 -translate-x-full"
        enter-to-class="transform opacity-100 translate-x-0"
        leave-active-class="transition ease-in duration-300"
        leave-from-class="transform opacity-100 translate-x-0"
        leave-to-class="transform opacity-0 -translate-x-full"
    >
        <div v-if="show" class="fixed top-5 left-5 z-50 max-w-sm">
            <div :class="[
                'flex items-center gap-x-3 rounded-md p-4 shadow-lg border',
                type === 'success' ? 'bg-green-100 border-green-700' : 'bg-red-100 border-red-700'
            ]">
                <CheckCircleIcon v-if="type === 'success'" class="h-5 w-5 text-green-600" />
                <XCircleIcon v-else class="h-5 w-5 text-red-600" />

                <p :class="['text-sm font-medium', type === 'success' ? 'text-green-900' : 'text-red-900']">
                    {{ message }}
                </p>
            </div>
        </div>
    </Transition>
</template>
