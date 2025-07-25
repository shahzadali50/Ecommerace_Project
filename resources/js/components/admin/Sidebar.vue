<script setup lang="ts">
import { ref, watch, computed, defineProps } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { SharedData } from '@/types';

import {
    SettingOutlined,
    DatabaseOutlined,
    AppstoreOutlined,
    TrademarkCircleOutlined,
    ShoppingOutlined,
    OrderedListOutlined,
    PoweroffOutlined,
    UsergroupAddOutlined
} from '@ant-design/icons-vue';

// Get user data from page props
const page = usePage<SharedData>();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');
const isUser = computed(() => user.value?.role === 'user');

defineProps<{ collapsed: boolean }>();

const selectedKeys = ref<string[]>([]);
const currentPath = computed(() => page.url);

watch(currentPath, (newUrl) => {
    selectedKeys.value = [newUrl];
}, { immediate: true });

// logout function
const handleLogout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <a-layout-sider :collapsed="collapsed" :trigger="null" collapsible>
        <div class="logo">
            <h3 class="mb-0" v-if="!collapsed">LaCuna-Marketplace</h3>
        </div>
        <div class="scrollbar" id="style-2">
            <div v-if="isAdmin">
                <a-menu v-model:selectedKeys="selectedKeys" class="force-overflow">
                    <!-- Dashboard -->
                    <a-menu-item key="1"
                        :class="{ 'active': currentPath.startsWith(route('admin.dashboard', {}, false)) }">
                        <Link :href="route('admin.dashboard')">
                        <DatabaseOutlined />
                        <span>Dashboard</span>
                        </Link>
                    </a-menu-item>
                    <a-menu-item key="2"
                        :class="{ 'active': currentPath.startsWith(route('admin.login.user', {}, false)) }">
                        <Link :href="route('admin.login.user')">
                            <UsergroupAddOutlined />
                        <span>Users</span>
                        </Link>
                    </a-menu-item>

                    <!-- Categories -->
                    <a-menu-item key="3"
                        :class="{ 'active': currentPath.startsWith(route('admin.categories', {}, false)) }">
                        <Link :href="route('admin.categories')">
                        <AppstoreOutlined />
                        <span>Categories</span>
                        </Link>
                    </a-menu-item>

                    <!-- Brands -->
                    <a-menu-item key="4"
                        :class="{ 'active': currentPath.startsWith(route('admin.brands', {}, false)) }">
                        <Link :href="route('admin.brands')">
                        <TrademarkCircleOutlined />
                        <span>Brands</span>
                        </Link>
                    </a-menu-item>

                    <!-- Products -->
                    <a-menu-item key="5"
                        :class="{ 'active': currentPath.startsWith(route('admin.products', {}, false)) }">
                        <Link :href="route('admin.products')">
                        <ShoppingOutlined />
                        <span>Products</span>
                        </Link>
                    </a-menu-item>
                    <!-- Order List -->
                    <a-menu-item key="6"
                        :class="{ 'active': currentPath.startsWith(route('admin.order.list', {}, false)) }">
                        <Link :href="route('admin.order.list')">
                        <OrderedListOutlined />
                        <span>Order List</span>
                        </Link>
                    </a-menu-item>
                    <!-- Profile -->
                    <a-menu-item key="7"
                        :class="{ 'active': currentPath.startsWith(route('profile.edit', {}, false)) }">
                        <Link :href="route('profile.edit')">
                        <SettingOutlined />
                        <span>Profile</span>
                        </Link>
                    </a-menu-item>
                        <!-- Website -->
                    <a-menu-item key="8">
                        <Link :href="route('home')">
                            <AppstoreOutlined />
                            <span>Website</span>
                        </Link>
                    </a-menu-item>
                    <a-menu-item key="9">
                        <a href="#" @click.prevent="handleLogout">
                            <PoweroffOutlined />
                            Logout
                        </a>
                    </a-menu-item>
                </a-menu>
            </div>
            <div v-if="isUser">
                <a-menu v-model:selectedKeys="selectedKeys" class="force-overflow">

                    <!-- Website -->
                    <a-menu-item key="1">
                        <Link :href="route('home')">
                            <AppstoreOutlined />
                            <span>Website</span>
                        </Link>
                    </a-menu-item>
                    <a-menu-item key="2"
                        :class="{ 'active': currentPath.startsWith(route('user.order.list', {}, false)) }">
                        <Link :href="route('user.order.list')">
                        <OrderedListOutlined />
                        <span>Order List</span>
                        </Link>
                    </a-menu-item>
                    <a-menu-item key="2"
                        :class="{ 'active': currentPath.startsWith(route('profile.edit', {}, false)) }">
                        <Link :href="route('profile.edit')">
                        <SettingOutlined />
                        <span>Profile</span>
                        </Link>
                    </a-menu-item>
                    <a-menu-item key="3">
                        <a href="#" @click.prevent="handleLogout">
                            <PoweroffOutlined />
                            Logout
                        </a>
                    </a-menu-item>
                </a-menu>
            </div>

        </div>
    </a-layout-sider>
</template>

<style scoped>
.logo {
    margin-top: 10px;
    height: 42px;
    color: rgb(255, 255, 255) !important;
    background-color: #232330 !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
