<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import {
    UserOutlined,
    LogoutOutlined,
    SearchOutlined,
    OrderedListOutlined
} from "@ant-design/icons-vue";
</script>

<template>
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Account Menu</h3>
        <div class="space-y-1 sm:space-y-2">
            <Link :href="route('home')"
                class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700 hover:text-primary text-sm sm:text-base">
            <UserOutlined class="text-base sm:text-lg" />
            <span>Profile</span>
            </Link>

            <Link :href="route('home')"
                class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700 hover:text-primary text-sm sm:text-base">
            <SearchOutlined class="text-base sm:text-lg" />
            <span>Order Track</span>
            </Link>

            <Link :href="route('order.list')"
                class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700 hover:text-primary text-sm sm:text-base">
            <OrderedListOutlined class="text-base sm:text-lg" />
            <span>Order List</span>
            </Link>

            <Link :href="route('home')" method="post" as="button"
                class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 rounded-lg hover:bg-red-50 transition-colors text-gray-700 hover:text-red-600 w-full text-left text-sm sm:text-base">
            <LogoutOutlined class="text-base sm:text-lg" />
            <span>Logout</span>
            </Link>
        </div>
    </div>
</template>
