<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    UserOutlined,
    LogoutOutlined,
    SearchOutlined,
    OrderedListOutlined
} from "@ant-design/icons-vue";

const page = usePage();
const currentPath = computed(() => page.url);
</script>

<template>
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Account Menu</h3>
        <div class="space-y-1 sm:space-y-2">
            <Link :href="route('profile.edit')" :class="[
                'flex items-center gap-2 sm:gap-3 p-2 sm:p-3 rounded-lg transition-colors text-sm sm:text-base',
                currentPath.startsWith(route('profile.edit', {}, false)) || currentPath.startsWith(route('password.edit', {}, false))
                    ? 'bg-gray-300 text-black hover:text-black'
                    : 'hover:bg-gray-300 text-black hover:text-primary'
            ]">
            <UserOutlined class="text-base sm:text-lg" />
            <span>Profile</span>
            </Link>
            <Link :href="route('user.order.list')" :class="[
                'flex items-center gap-2 sm:gap-3 p-2 sm:p-3 rounded-lg transition-colors text-sm sm:text-base',
                currentPath.startsWith(route('user.order.list', {}, false))
                    ? 'bg-gray-300 text-black hover:text-black'
                    : 'hover:bg-gray-300 text-black hover:text-primary'
            ]">
            <OrderedListOutlined class="text-base sm:text-lg" />
            <span>Order List</span>
            </Link>

            <Link :href="route('track.order')" :class="[
                'flex items-center gap-2 sm:gap-3 p-2 sm:p-3 rounded-lg transition-colors text-sm sm:text-base',
                currentPath.startsWith(route('track.order', {}, false))
                    ? 'bg-gray-300 text-black hover:text-black'
                    : 'hover:bg-gray-300 text-black hover:text-primary'
            ]">
            <SearchOutlined class="text-base sm:text-lg" />
            <span>Order Track</span>
            </Link>



            <Link :href="route('logout')" method="post" as="button"
                class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 rounded-lg hover:bg-red-50 transition-colors text-gray-700 hover:text-red-600 w-full text-left text-sm sm:text-base">
            <LogoutOutlined class="text-base sm:text-lg" />
            <span>Logout</span>
            </Link>
        </div>
    </div>
</template>
