<script setup lang="ts">
defineProps<{
    modelValue: string;
    label: string;
    placeholder?: string;
    error?: string;
    disabled?: boolean;
    // Ajout d'une prop pour choisir la taille
    variant?: 'compact' | 'large';
}>();

defineEmits(["update:modelValue", "input"]);
</script>

<template>
    <div class="space-y-1"> <label
        class="font-black text-zinc-400 ml-1 text-[10px] tracking-[0.2em] uppercase"
    >
        {{ label }}
    </label>

        <input
            type="text"
            :value="modelValue"
            @input="
                $emit('update:modelValue', ($event.target as HTMLInputElement).value);
                $emit('input', $event);
            "
            :placeholder="placeholder"
            :disabled="disabled"
            class="border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500/10 focus:border-sky-500 dark:text-white w-full transition-all focus:ring-4 disabled:opacity-50"
            :class="[
                variant === 'compact'
                    ? 'px-4 py-3 rounded-xl text-sm'
                    : 'px-5 py-4 rounded-2xl text-base font-medium'
            ]"
        />

        <div v-if="error" class="text-xs text-red-500 font-bold ml-1">
            {{ error }}
        </div>
    </div>
</template>
