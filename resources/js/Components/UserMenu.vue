<script setup lang="ts">
    import { Link, usePage } from '@inertiajs/vue3';
    import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
    import { ChevronDownIcon, UserCircleIcon, Cog6ToothIcon } from '@heroicons/vue/20/solid';
    import { computed } from 'vue';

    const page = usePage();
    const user = computed(() => page.props.auth)["user"]
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
        <div v-if="user != null">
            <MenuButton class="inline-flex w-full justify-center items-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <UserCircleIcon class="h-5 w-5 text-gray-500" />
                    {{ user.name }}
                <ChevronDownIcon class="-mr-1 h-5 w-5 text-gray-400" />
            </MenuButton>
        </div>

        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <MenuItems class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div v-if="user != null" class="py-1">
                    <MenuItem v-slot="{ active }">
                        <Link href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'group flex items-center px-4 py-2 text-sm']">
                            <Cog6ToothIcon class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" />
                            Préférences
                        </Link>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>
