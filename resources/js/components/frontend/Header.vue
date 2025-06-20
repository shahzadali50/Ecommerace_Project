<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import CartSidebar from '@/components/frontend/CartSidebar.vue';
import MobileSidebar from '@/components/frontend/MobileSidebar.vue';
import { Link } from "@inertiajs/vue3";
import MobileBottomNav from './MobileBottomNav.vue';

import {
    MenuOutlined,
    CloseOutlined,
    HeartOutlined,
    ShoppingCartOutlined,
    UserOutlined,
    SearchOutlined,
} from '@ant-design/icons-vue';
import SearchProducts from './SearchProducts.vue';

const page = usePage();
const translations = computed(() => {
    return (page.props.translations as any)?.header || {};
});

const { props } = usePage();
const mobileMenuOpen = ref(false);
const cartDrawerVisible = ref(false);
const isAuthenticated = computed(() => props.auth?.user);

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const toggleCart = () => {
    cartDrawerVisible.value = !cartDrawerVisible.value;
};

const handleLogout = () => {
    router.post(route('logout'));
};
const goToWishlist = () => {
    router.visit(route('wishlist'));
};
const mobileSearchVisible = ref(false);

const toggleSearch = () => {
    mobileSearchVisible.value = !mobileSearchVisible.value;
};
</script>
<template>
    <header class="bg-white shadow fixed top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <Link :href="route('home')" class="flex items-center">

                    <img src="\assets\images\Logo-LaCuna-JP-azul.fw.webp" alt="Logo" class="h-8 w-auto" />
                    </Link>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-8">

                    <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">
                    {{ translations.home || 'Home' }}
                    </Link>
                    <Link :href="route('all.products')" class="text-gray-600 hover:text-gray-900">
                    {{ translations.shop || 'Shop' }}
                    </Link>
                    <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">
                    {{ translations.categories || 'Categories' }}
                    </Link>
                    <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">
                    {{ translations.brands || 'Brands' }}
                    </Link>
                </nav>


                <!-- Right Side Actions -->
                <div class="flex items-center space-x-0">
                    <!-- Search -->
                    <div class="hidden md:block">
                    <SearchProducts/>
                    </div>

                    <!-- Language Switcher - Visible on both mobile and desktop -->
                    <div>
                        <LanguageSwitcher />
                    </div>
                    <div class="block lg:hidden">
                        <a-dropdown v-if="isAuthenticated" trigger="click">
                            <a-button type="text" shape="circle">

                                <template #icon>
                                    <UserOutlined />
                                </template>
                            </a-button>
                            <template #overlay>
                                <a-menu>
                                    <a-menu-item key="profile">
                                        <Link :href="route('dashboard')" class="text-gray-600 hover:text-gray-900">
                                        {{ translations.dashboard || 'Dashboard' }}
                                        </Link>

                                    </a-menu-item>
                                    <a-menu-divider />
                                    <a-menu-item key="logout">

                                        <a href="#" @click.prevent="handleLogout">{{ translations.logout || 'Logout'
                                        }}</a>
                                    </a-menu-item>
                                </a-menu>
                            </template>
                        </a-dropdown>
                        <div v-else class="flex space-x-2">
                            <a-dropdown trigger="click">
                                <a-button type="text" shape="circle">
                                    <template #icon>
                                        <UserOutlined />
                                    </template>
                                </a-button>
                                <template #overlay>
                                    <a-menu>
                                        <a-menu-item key="login">
                                            <Link :href="route('login')" class="text-gray-600 hover:text-gray-900">
                                            {{ translations.login || 'Login' }}
                                            </Link>
                                        </a-menu-item>
                                        <a-menu-item key="register">
                                            <Link :href="route('register')" class="text-gray-600 hover:text-gray-900">
                                            {{ translations.register || 'Register' }}
                                            </Link>
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>
                        </div>


                    </div>

                    <!-- User Actions - Only visible on desktop -->
                    <div class="hidden lg:flex items-center space-x-2">
                        <a-button type="text" shape="circle" @click="goToWishlist">
                            <template #icon>
                                <HeartOutlined />
                            </template>
                        </a-button>
                        <a-button type="text" shape="circle" @click="toggleCart">
                            <template #icon>
                                <ShoppingCartOutlined />
                            </template>
                        </a-button>
                        <a-dropdown v-if="isAuthenticated">
                            <a-button type="text" shape="circle">

                                <template #icon>
                                    <UserOutlined />
                                </template>
                            </a-button>
                            <template #overlay>
                                <a-menu>
                                    <a-menu-item key="profile">
                                        <Link :href="route('dashboard')" class="text-gray-600 hover:text-gray-900">
                                        {{ translations.dashboard || 'Dashboard' }}
                                        </Link>

                                    </a-menu-item>
                                    <a-menu-divider />
                                    <a-menu-item key="logout">

                                        <a href="#" @click.prevent="handleLogout">{{ translations.logout || 'Logout'
                                        }}</a>
                                    </a-menu-item>
                                </a-menu>
                            </template>
                        </a-dropdown>
                        <div v-else class="flex space-x-2">
                            <a-dropdown>
                                <a-button type="text" shape="circle">
                                    <template #icon>
                                        <UserOutlined />
                                    </template>
                                </a-button>
                                <template #overlay>
                                    <a-menu>
                                        <a-menu-item key="login">
                                            <Link :href="route('login')" class="text-gray-600 hover:text-gray-900">
                                            {{ translations.login || 'Login' }}
                                            </Link>
                                        </a-menu-item>
                                        <a-menu-item key="register">
                                            <Link :href="route('register')" class="text-gray-600 hover:text-gray-900">
                                            {{ translations.register || 'Register' }}
                                            </Link>
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>
                        </div>
                    </div>
                    <div class="md:hidden ">
                        <a-button type="text" shape="circle" @click="toggleSearch">
                            <template #icon>
                                <SearchOutlined />
                            </template>
                        </a-button>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="lg:hidden">
                        <a-button type="text" shape="circle" @click="toggleMobileMenu">
                            <template #icon>
                                <MenuOutlined v-if="!mobileMenuOpen" />
                                <CloseOutlined v-else />
                            </template>
                        </a-button>
                    </div>

                </div>
            </div>
            <Transition name="fade-slide">
                <div v-if="mobileSearchVisible" class="md:hidden mt-4">
                    <!-- <a-input-search placeholder="Search products..." class="w-full" /> -->
                      <SearchProducts/>
                </div>
            </Transition>


        </div>
    </header>

    <!-- Mobile Sidebar -->
    <MobileSidebar v-model:visible="mobileMenuOpen" />

    <!-- Mobile Bottom Navigation -->
    <MobileBottomNav @toggle-cart="toggleCart" />

    <!-- Cart Sidebar -->
    <CartSidebar v-model:visible="cartDrawerVisible" />
    <div class="mt-[120px] md:mt-[70px]">
    </div>
</template>



<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.fade-slide-enter-to {
    opacity: 1;
    transform: translateY(0);
}

.fade-slide-leave-from {
    opacity: 1;
    transform: translateY(0);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
