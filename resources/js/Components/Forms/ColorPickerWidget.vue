<script setup lang="ts">
defineProps<{
    modelValue: string;
    label?: string;
}>();

defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="space-y-2">
        <label
            class="font-black text-gray-400 ml-1 text-[10px] tracking-[0.2em] uppercase"
        >
            {{ label ?? 'Couleur' }}
        </label>
        <div class="relative flex items-center">
            <input
                type="color"
                :value="modelValue"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                class="p-1 bg-white dark:bg-zinc-900/50 border-gray-200 dark:border-zinc-800 rounded-2xl h-[60px] w-full cursor-pointer border transition-all focus:ring-4 focus:ring-sky-500/10"
            />
        </div>
    </div>
</template>
