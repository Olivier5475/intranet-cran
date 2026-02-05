<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronDownIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';
import { useResource } from '@/Composables/useResource';
import ResourceIcon from '@/Components/ResourceIcon.vue';
import DeleteModal from '@/Components/DeleteModal.vue';

const props = defineProps<{
    child: any;
    folder_id: number;
}>();

const { links, itemColor, canEdit } = useResource(props);

const isActive = ref(false);
const isActiveValidation = ref(false);
</script>

<template>
    <div class="hover:bg-blue-400 hover:bg-opacity-50 w-[11.35%] ml-[1%] transition-all duration-150 relative group">

        <component
            :is="child.type !== 'file' ? Link : 'a'"
            :href="links.href"
            class="block"
        >
            <div class="mx-auto aspect-square w-10/12">
                <ResourceIcon :child="child" :color="itemColor" class="w-full h-full" />
            </div>
            <p class="text-black dark:text-gray-200 overflow-hidden mx-auto w-full text-center">
                {{ child.name }}
            </p>
        </component>

        <div
            v-if="canEdit"
            @click="isActive = !isActive"
            class="text-center bg-slate-500 absolute bottom-0 right-0 w-6 rounded-full aspect-square cursor-pointer"
        >
            <ChevronDownIcon v-if="isActive" class="w-4 inline"/>
            <ChevronRightIcon v-else class="w-4 inline"/>
        </div>

        <div v-if="isActive && canEdit" class="rounded-xl absolute right-0 bottom-negative bg-slate-500 z-10">
            <Link class="rounded-t-xl text-yellow-500 hover:text-white hover:bg-yellow-500 block pb-1 pt-2 px-2" :href="links.update">
                Update
            </Link>
            <p v-if="links.delete" @click="isActiveValidation = true" class="rounded-b-xl text-red-600 hover:text-white hover:bg-red-600 block pt-1 pb-2 px-2 cursor-pointer">
                Delete
            </p>
        </div>
    </div>

    <DeleteModal
        :show="isActiveValidation"
        :delete-href="links.delete"
        @close="isActiveValidation = false"
    />
</template>

<style scoped>
.bottom-negative { bottom: -4.5rem; }
</style>
