<script setup lang="ts">
// 1. Vue & Core
import { useForm } from "@inertiajs/vue3";
import { computed, ref } from 'vue';

// 2. Routes
import grps_route from "@/routes/admin/groupes";
import { Groupe } from "@/types/groupe";
import NameInputWidget from '@/Components/Forms/NameInputWidget.vue';
import ColorPickerWidget from '@/Components/Forms/ColorPickerWidget.vue';

const props = defineProps<{
    groupes: Groupe[]
    groupe: Groupe | null;
}>();

const emit = defineEmits(["success"]);

const form = useForm({
    name: props.groupe?.name ?? "",
    initials: props.groupe?.initials ?? "",
    color: props.groupe?.color ?? "#ffffff",
    parent: props.groupe?.parent ?? null,
});

const availableGroupes = computed(() => {
    if (!props.groupe) return props.groupes; // Si création, tout est disponible
    return props.groupes.filter(grp => grp.id !== props.groupe?.id);
});
const userInteractedWith = ref(!!props.groupe);

const updateInitials = () => {
    if (!userInteractedWith.value) {
        form.initials = form.name
            .split(" ")
            .map((word: any) => word[0])
            .join("")
            .toUpperCase()
            .substring(0, 3); // Limite à 3 caractères pour le design
    }
};

const submit = () => {
    const action = props.groupe ? "patch" : "post";
    const url = props.groupe
        ? grps_route.post.update.url(props.groupe.id)
        : grps_route.post.create.url();

    form[action](url, {
        onSuccess: () => emit("success"),
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <NameInputWidget
                v-model="form.name"
                label="Nom du groupe"
                placeholder="Ex: Direction Technique"
                :error="form.errors.name"
                @input="updateInitials"
                class="md:col-span-3"
            />

            <div class="space-y-2">
                <label
                    class="text-[11px] font-black uppercase tracking-widest text-zinc-400 ml-1"
                >
                    Code
                </label>
                <input
                    type="text"
                    v-model="form.initials"
                    @input="userInteractedWith = true"
                    placeholder="DT"
                    maxlength="4"
                    class="w-full px-4 py-4 rounded-2xl border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 transition-all font-black text-center text-sky-600 dark:text-sky-400"
                />
                <div
                    v-if="form.errors.initials"
                    class="text-xs text-red-500 font-bold ml-1"
                >
                    {{ form.errors.initials }}
                </div>
            </div>

            <ColorPickerWidget v-model="form.color" />
        </div>

        <select
            class="w-full bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-zinc-100 dark:border-zinc-700"
            v-model="form.parent"
        >
            <option
                v-for="grp in availableGroupes"
                :key="grp.id"
                :value="grp.id"
            >
                {{ grp.name }}
            </option>
        </select>
        <div
            class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl border border-zinc-100 dark:border-zinc-700"
        >
            <p class="text-xs text-zinc-500 leading-relaxed italic text-center">
                Les initiales seront utilisées comme icône par défaut dans
                l'arborescence et les listes de navigation.
            </p>
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-black rounded-2xl hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 shadow-xl shadow-zinc-500/10"
        >
            {{
                groupe
                    ? "ENREGISTRER LES MODIFICATIONS"
                    : "CRÉER LE GROUPE"
            }}
        </button>
    </form>
</template>
