<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/20/solid';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref<'success' | 'error' | 'warn'>('success');

watch(() => [page.props.flash.success, page.props.flash.error, page.props.flash.warn], ([success, error, warn]) => {
    const msg = success || error || warn;
    if (msg) {
        message.value = msg as string;

        // Détermination du type
        if (success) type.value = 'success';
        else if (warn) type.value = 'warn';
        else type.value = 'error';

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
                type === 'success' ? 'bg-green-100 border-green-700' :
                type === 'warn' ? 'bg-yellow-100 border-yellow-700' : 'bg-red-100 border-red-700'
            ]">
                <CheckCircleIcon v-if="type === 'success'" class="h-5 w-5 text-green-600" />
                <ExclamationTriangleIcon v-else-if="type === 'warn'" class="h-5 w-5 text-yellow-600" />
                <XCircleIcon v-else class="h-5 w-5 text-red-600" />

                <p :class="[
                    'text-sm font-medium',
                    type === 'success' ? 'text-green-900' :
                    type === 'warn' ? 'text-yellow-900' : 'text-red-900'
                ]">
                    {{ message }}
                </p>
            </div>
        </div>
    </Transition>
</template>
