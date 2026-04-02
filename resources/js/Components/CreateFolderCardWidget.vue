<script setup lang="ts">
import { FolderIcon, ArrowTurnDownRightIcon } from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/vue3';
import route from '@/routes/editor/folder';

const props = defineProps<{
    parent?: {
        id: number;
        name: string;
        departements: number[];
    };
}>();

const form = useForm({
    name: '',
    color: '#d7ac53',
    departements: props.parent?.departements ?? [],
    parent_id: props.parent?.id ?? null,
});
const model = defineModel<boolean>();

const submit = () => {
    form.post(route.post.create.url());
    model.value = !model.value;
    form.name = '';
};
</script>

<template>
    <div
        class="group bg-white dark:bg-slate-800/30 border-purple-400/50 p-4 rounded-2xl
        flex min-h-[160px] flex-col items-center justify-center border-2 border-dashed"
    >
        <div class="w-16 h-16 mb-2">
            <FolderIcon class="animate-pulse h-full w-full text-[#d7ac53]" />
        </div>

        <form @submit.prevent="submit" class="gap-2 flex w-full flex-col items-center">
            <input
                v-model="form.name"
                placeholder="Nom du dossier..."
                autofocus
                class="text-xs font-semibold border-purple-500 focus:border-purple-400
                dark:text-zinc-200 w-full border-b bg-transparent text-center focus:ring-0"
            />
            <button
                type="submit"
                class="mt-2 p-1.5 bg-purple-600 hover:bg-purple-700 text-white shadow-lg
                rounded-full transition-transform active:scale-90"
            >
                <ArrowTurnDownRightIcon class="w-4 h-4" />
            </button>
        </form>
    </div>
</template>
