<script setup lang="ts">
import { FolderIcon, ArrowTurnDownRightIcon} from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/vue3';
import route from '@/routes/editor/folder';

const props = defineProps<{
    parent?: {
        id: number;
        name: string;
        departements: number[];
    },
}>()

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
}
</script>

<template>
    <div class="group grid grid-cols-12 items-center py-3 px-4 border-t border-gray-100 dark:border-zinc-800 hover:bg-sky-50/50 dark:hover:bg-slate-900/10 transition-colors duration-150">
        <div class="col-span-5 flex items-center space-x-3 overflow-hidden">
            <div class="w-9 h-9 flex-shrink-0">
                <FolderIcon class="w-full h-full transform group-hover:scale-110 transition-transform text-[#d7ac53]" />
            </div>
            <form @submit.prevent="submit" class="flex gap-2">
                <input
                    v-model="form.name"
                    class="text-sm font-medium bg-slate-700 w-full rounded-lg focus:ring-0
        hover:border-violet-300 hover:border-1 focus:border-violet-500 focus:border-2
        text-gray-700 dark:text-zinc-200 truncate"
                >
                <button
                    @click="submit"
                    class="group flex items-center justify-center p-2.5
           bg-purple-600 hover:bg-purple-700 active:scale-95
           text-white rounded-full shadow-sm hover:shadow-md
           transition-all duration-200 ease-in-out
           focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                    aria-label="Envoyer"
                >
                    <ArrowTurnDownRightIcon
                        class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                    />
                </button>
            </form>
        </div>

        <div class="col-span-2 text-center text-xs text-gray-500 dark:text-zinc-400">
            <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-slate-800">
                Dossier
            </span>
        </div>

        <p class="col-span-3 text-center text-xs text-gray-400">
            {{ new Date().toLocaleString() }}
        </p>
    </div>
</template>

<style scoped>
</style>
