<script setup lang="ts">
import { ref, onMounted } from 'vue'; // 1. Ajout des imports
import { FolderIcon, ArrowTurnDownRightIcon } from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/vue3';
import route from '@/routes/editor/folder';

const props = defineProps<{
    parent?: {
        id: number;
        name: string;
        groupes: number[];
    };
}>();

const form = useForm({
    name: '',
    color: '#d7ac53',
    groupes: props.parent?.groupes ?? [],
    parent_id: props.parent?.id ?? null,
});
const model = defineModel<boolean>();

// 2. Création d'une référence pour l'input
const inputRef = ref<HTMLInputElement | null>(null);

// 3. Focus et scroll fluide dès l'apparition de la carte
onMounted(() => {
    if (inputRef.value) {
        inputRef.value.focus();
        inputRef.value.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

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
                ref="inputRef"
                v-model="form.name"
                placeholder="Nom du dossier..."
                class="text-xs font-semibold border-purple-500 focus:border-purple-400
                dark:text-zinc-200 w-full border-b bg-transparent text-center focus:ring-0 rounded"
            />
            <button
                type="submit"
                class="w-full p-1.5 bg-purple-600 hover:bg-purple-700 text-white shadow-lg
                rounded transition-transform active:scale-90"
            >
                <ArrowTurnDownRightIcon class="w-4 h-4 mx-auto" />
            </button>
        </form>
    </div>
</template>
