<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    groupeIds: number[];
    mode: 'row' | 'card';
}>();

const page = usePage();
const hoveredGrpId = ref<number | null>(null);

const getGrp = (id: number) => page.props.groupes.find((d: any) => d.id === id);
</script>

<template>
    <div v-if="mode == 'row'" class="flex flex-wrap justify-center gap-1">
        <span v-for="id in groupeIds" :key="id"
              :style="{ backgroundColor: getGrp(id)?.color }"
              class="px-2 rounded-xl text-white text-[8px] font-semibold">
            {{ getGrp(id)?.initials }}
        </span>
    </div>

    <div
        v-else
        class="absolute top-1 left-1 flex max-w-[90%] z-10"
        :class="groupeIds.length > 2 ? 'gap-x-3.5' : 'gap-x-1'"
    >
        <div
            v-for="grpId in groupeIds"
            :key="grpId"
            class="pointer-events-auto relative h-3 transition-all duration-300"
            :style="{
                    // Si moins de 2 groupes, largueur de base en auto,
                    // sinon fixé à 0.75rem
                    width: groupeIds.length <= 2
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
                    // Si le nombre de groupe est supérieur à 2,
                    groupeIds.length > 2
                        // on fixe les pastilles à un endroit
                        ? 'absolute top-0 left-0'
                        // Sinon, on les laisse se placer en ligne.
                        : 'relative',

                    // S\'il y a 2 groupes, ou moins par-dessus le groupe
                    (groupeIds.length <= 2 || hoveredGrpId === grpId)
                        // On fix la largeur à 'fit' et le padding à 1
                        ? 'w-fit px-1'
                        // Sinon, on fix largeur à 3
                        : 'w-3'
                ]"
                class="h-3 rounded-full flex items-center justify-center
                    transition-all duration-300 shadow-sm whitespace-nowrap"
            >
                    <span
                        v-if="groupeIds.length <= 2
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
