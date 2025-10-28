<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircleIcon } from '@heroicons/vue/20/solid';

const page = usePage();
const show = ref(false);
const message = ref('');

// On surveille la prop flash
watch(() => page.props.flash.success, (newMessage: string | null) => {
    if (newMessage) {
        message.value = newMessage;
        show.value = true;
        setTimeout(() => {
            show.value = false;
        }, 3000);
    }
}, {
    immediate: true
});
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
        <div
            v-if="show"
            class="fixed top-5 left-5 z-50 max-w-sm"
        >
            <div
                class="flex items-center gap-x-3 rounded-md bg-green-100 p-4 shadow-lg border border-green-700"
            >
                <CheckCircleIcon class="h-5 w-5 text-green-600" />
                <p class="text-sm font-medium text-green-900">
                    {{ message }}
                </p>
            </div>
        </div>
    </Transition>
</template>
