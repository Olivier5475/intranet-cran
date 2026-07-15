<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    groupeIds: number[];
    mode: 'row' | 'card';
}>();

const page = usePage();
const hoveredGrpId = ref<number | null>(null);

// Fonction pour récupérer l'objet complet d'un groupe depuis son ID
const getGrp = (id: number) => page.props.groupes.find((d: any) => d.id === id);

// 🚀 La logique magique : on remonte au parent et on supprime les doublons
const processedGroupeIds = computed(() => {
    // Un "Set" est une liste qui refuse automatiquement les valeurs en double
    const uniqueIds = new Set<number>();

    props.groupeIds.forEach(id => {
        const grp = getGrp(id);
        if (grp) {
            // Si le groupe a un parent, on cible l'ID du parent. Sinon, on garde son ID.
            const finalId = grp.parent ? grp.parent : grp.id;
            uniqueIds.add(finalId);
        }
    });

    // On transforme le Set en tableau classique pour que le v-for fonctionne bien
    return Array.from(uniqueIds);
});
</script>

<template>
    <div v-if="mode == 'row'" class="flex flex-wrap justify-center gap-1">
        <!-- 🔄 On boucle sur processedGroupeIds au lieu de groupeIds -->
        <span v-for="id in processedGroupeIds" :key="id"
              :style="{ backgroundColor: getGrp(id)?.color }"
              class="px-2 rounded-xl text-white text-[8px] font-semibold">
            {{ getGrp(id)?.initials }}
        </span>
    </div>

    <div
        v-else
        class="absolute top-1 left-1 flex max-w-[90%] z-10"
        :class="processedGroupeIds.length > 2 ? 'gap-x-3.5' : 'gap-x-1'"
    >
        <!-- 🔄 On remplace tout par processedGroupeIds -->
        <div
            v-for="grpId in processedGroupeIds"
            :key="grpId"
            class="pointer-events-auto relative h-3 transition-all duration-300"
            :style="{
                    // Si moins de 2 groupes, largeur de base en auto,
                    // sinon fixé à 0.75rem
                    width: processedGroupeIds.length <= 2
                                                ? 'auto'
                                                : '0.75rem'
                }"
            @mouseenter="hoveredGrpId = grpId"
            @mouseleave="hoveredGrpId = null"
        >
            <div
                :style="{
                        backgroundColor: getGrp(grpId)?.color,
                        zIndex: hoveredGrpId === grpId ? 10 : 1
                    }"
                :class="[
                    // Si le nombre de groupes est supérieur à 2,
                    processedGroupeIds.length > 2
                        // on fixe les pastilles à un endroit
                        ? 'absolute top-0 left-0'
                        // Sinon, on les laisse se placer en ligne.
                        : 'relative',

                    // S'il y a 2 groupes, ou moins par-dessus le groupe
                    (processedGroupeIds.length <= 2 || hoveredGrpId === grpId)
                        // On fixe la largeur à 'fit' et le padding à 1
                        ? 'w-fit px-1'
                        // Sinon, on fixe largeur à 3
                        : 'w-3'
                ]"
                class="h-3 rounded-full flex items-center justify-center
                    transition-all duration-300 shadow-sm whitespace-nowrap"
            >
                    <span
                        v-if="processedGroupeIds.length <= 2
                                || hoveredGrpId === grpId"
                        class="text-[0.5rem] font-black uppercase text-white p-1"
                        :class="getGrp(grpId)?.color === '#ffffff' ? '!text-black' : ''"
                    >
                        {{ getGrp(grpId)?.initials }}
                    </span>
            </div>
        </div>
    </div>
</template>
