<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ArrowTurnDownRightIcon } from "@heroicons/vue/24/solid";

const props = defineProps<{
    modelValue: string;
    routeUrl: string;
    isRow?: boolean; // Pour adapter le style entre Card et Row
}>();

const emit = defineEmits(['update:modelValue', 'success']);

const form = useForm({ name: props.modelValue });

const submit = () => {
    form.post(props.routeUrl, {
        onSuccess: () => emit('success')
    });
};
</script>

<template>
    <form @submit.prevent="submit" :class="isRow ? 'flex gap-2' : 'flex flex-col gap-1'">
        <input
            v-model="form.name"
            :class="[
                'text-sm font-medium dark:bg-slate-700 rounded-lg focus:ring-0 hover:border-violet-300 focus:border-violet-500 border-2 text-gray-700 dark:text-zinc-200 truncate',
                isRow ? 'w-full' : 'w-[130%] mx-auto -translate-x-[11%]'
            ]"
        >
        <button type="submit" :class="isRow ? 'p-2.5 rounded-full' : 'w-full py-1 rounded-md'" class="bg-purple-600 text-white">
            <ArrowTurnDownRightIcon v-if="isRow" class="w-5 h-5" />
            <span v-else class="text-xs">Send</span>
        </button>
    </form>
</template>
