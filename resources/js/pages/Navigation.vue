<script setup lang="ts">
import RepertoryWidget from '@/Components/RepertoryWidget.vue';
import SearchBarWidget from '@/Components/SearchBarWidget.vue';
import DocumentWidget from '@/Components/DocumentWidget.vue';
defineProps<{
    children : Array<{
        id : number,
        name : string,
        type : string
    }>,
    parents : Array<{
        id : number,
        name : string,
    }>
}>();

</script>

<template>
    <RepertoryWidget :parents=parents></RepertoryWidget>
    <SearchBarWidget/>
    <div class="flex flex-wrap w-full">
        <DocumentWidget v-for="child in children" :key=child.name :child=child />
    </div>
</template>

<style scoped></style>
