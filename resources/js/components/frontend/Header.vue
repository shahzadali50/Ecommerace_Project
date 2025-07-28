<script setup lang="ts">
import { ref, computed, getCurrentInstance } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import CartSidebar from '@/components/frontend/CartSidebar.vue';
import MobileSidebar from '@/components/frontend/MobileSidebar.vue';
import { Link } from "@inertiajs/vue3";
import MobileBottomNav from './MobileBottomNav.vue';
import type { SharedData } from '@/types';

import {
    MenuOutlined,
    CloseOutlined,
    HeartOutlined,
    ShoppingCartOutlined,
    UserOutlined,
    SearchOutlined,
} from '@ant-design/icons-vue';
import SearchProducts from './SearchProducts.vue';

const page = usePage<SharedData>();
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

const cart = computed(() => {
    const data = page.props.cart as any[] || [];
    return data.map(item => ({
        ...item,
        quantity: item.qty || 0,
    }));
});
const totalCartQty = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.quantity || 0), 0);
});
// wishlistCount
const wishlist = computed(() => {
    return page.props.wishlist as number[] || [];
});
const wishlistCount = computed(() => wishlist.value.length);

const categories = computed(() => {
    return page.props.global_categories as any[] || [];
});

const brands = computed(() => {
    return page.props.global_brands as any[] || [];
});

const mobileMenuOpen = ref(false);
const cartDrawerVisible = ref(false);
const isAuthenticated = computed(() => page.props.auth?.user);

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


                    <Link :href="route('home')" class="text-gray-600 hover:text-gray-900 text-[18px]">
                    {{ t('Home') }}
                    </Link>
                    <Link :href="route('all.products')" class="text-gray-600 hover:text-gray-900 text-[18px]">
                    {{ t('Shop') }}
                    </Link>
                    <a-dropdown trigger="hover" placement="bottom">
                        <a class="text-gray-600 hover:text-gray-900 text-[18px] cursor-pointer">
                            {{ t('Categories') }}
                        </a>
                        <template #overlay>
                            <div class="mega-menu bg-white shadow-lg border border-gray-200 rounded-lg p-6 min-w-[300px]">
                                <div class="grid grid-cols-1 gap-6">
                                    <!-- Categories -->
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                                            {{ t('Popular Categories') }}
                                        </h3>
                                        <ul class="space-y-2 max-h-64 overflow-y-auto pr-2">
                                            <template v-if="categories.length > 0">
                                                <li v-for="category in categories" :key="category.id">
                                                    <Link :href="route('all.products', { category: category.slug })" class="text-gray-600 hover:text-primary transition-colors duration-200 flex items-center justify-between py-1">
                                                        <div class="flex items-center">
                                                            <span class="w-2 h-2 bg-primary rounded-full mr-3"></span>
                                                            {{ t(category.name) }}
                                                        </div>
                                                    </Link>
                                                </li>
                                            </template>
                                            <li v-else class="text-gray-500 text-sm">
                                                {{ t('No categories available') }}
                                            </li>
                                        </ul>
                                    </div>


                                </div>
                            </div>
                        </template>
                    </a-dropdown>
                    <a-dropdown trigger="hover" placement="bottom">
                        <a class="text-gray-600 hover:text-gray-900 text-[18px] cursor-pointer">
                            {{ t(' Brands') }}
                        </a>
                        <template #overlay>
                            <div class="mega-menu bg-white shadow-lg border border-gray-200 rounded-lg p-6 min-w-[300px]">
                                <div class="grid grid-cols-1 gap-6">
                                    <!-- Brands -->
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                                            {{ t('Popular Brands') }}
                                        </h3>
                                        <ul class="space-y-2 max-h-64 overflow-y-auto pr-2">
                                            <template v-if="brands.length > 0">
                                                <li v-for="brand in brands" :key="brand.id">
                                                    <Link :href="route('all.products', { brand: brand.slug })" class="text-gray-600 hover:text-primary transition-colors duration-200 flex items-center justify-between py-1">
                                                        <div class="flex items-center">
                                                            <span class="w-2 h-2 bg-green-500 rounded-full mr-3"></span>
                                                            {{ t(brand.name) }}
                                                        </div>
                                                    </Link>
                                                </li>
                                            </template>
                                            <li v-else class="text-gray-500 text-sm">
                                                {{ t('No brands available') }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </a-dropdown>
                    <Link :href="route('track.order')" class="text-gray-600 hover:text-gray-900 text-[18px]">
                    {{ t('Trck Order') }}
                    </Link>
                </nav>


                <!-- Right Side Actions -->
                <div class="flex items-center space-x-0">
                    <!-- Search -->
                    <div class="hidden md:block">
                        <SearchProducts />
                    </div>

                    <!-- Language Switcher - Visible on both mobile and desktop -->
                    <div>
                        <LanguageSwitcher />
                    </div>
                    <div class="block lg:hidden">
                        <a-dropdown v-if="isAuthenticated" trigger="click">
                            <a-button class="text-[18px] p-0" type="text" shape="circle">

                                <template #icon>
                                    <UserOutlined />
                                </template>
                            </a-button>
                            <template #overlay>
                                <a-menu>
                                    <a-menu-item key="profile">
                                        <Link :href="route('dashboard')" class="text-gray-600 hover:text-gray-900">
                                        {{ t('Dashboard') }}
                                        </Link>

                                    </a-menu-item>
                                    <a-menu-divider />
                                    <a-menu-item key="logout">
                                     <a href="#" @click.prevent="handleLogout">{{ t('Logout') }}</a>
                                    </a-menu-item>
                                </a-menu>
                            </template>
                        </a-dropdown>
                        <div v-else class="flex space-x-2">
                            <a-dropdown trigger="click">
                                <a-button class="text-[18px] p-0" type="text" shape="circle">
                                    <template #icon>
                                        <UserOutlined />
                                    </template>
                                </a-button>
                                <template #overlay>
                                    <a-menu>
                                        <a-menu-item key="login">
                                            <Link :href="route('login')" class="text-gray-600 hover:text-gray-900">
                                            {{ t('Login') }}
                                            </Link>
                                        </a-menu-item>
                                        <a-menu-item key="register">
                                            <Link :href="route('register')" class="text-gray-600 hover:text-gray-900">
                                            {{ t('Register') }}
                                            </Link>
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>
                        </div>


                    </div>

                    <!-- User Actions - Only visible on desktop -->
                    <div class="hidden lg:flex items-center space-x-2">
                        <a-button class="text-[18px] p-0" type="text" shape="circle" @click="goToWishlist">
                            <template #icon>
                                <HeartOutlined />
                                <a-badge  :count="wishlistCount" class="absolute left-[14px] top-0"></a-badge>

                            </template>
                        </a-button>
                        <a-button class="text-[18px] p-0" type="text" shape="circle" @click="toggleCart">
                            <template #icon>
                                <ShoppingCartOutlined />
                                <a-badge  :count="totalCartQty" class="absolute left-[14px] top-0"></a-badge>
                            </template>
                        </a-button>
                        <a-dropdown v-if="isAuthenticated">
                            <a-button class="text-[18px] p-0" type="text" shape="circle">

                                <template #icon>
                                    <!-- <UserOutlined /> -->
                                    <img src="\assets\images\login-user.svg" alt="Logo" class="h-8 w-auto" />
                                </template>
                            </a-button>
                            <template #overlay>
                                <a-menu>
                                    <a-menu-item key="profile">
                                        <Link :href="route('dashboard')" class="text-gray-600 hover:text-gray-900">
                                        {{ t('Dashboard') }}
                                        </Link>

                                    </a-menu-item>
                                    <a-menu-divider />
                                    <a-menu-item key="logout">

                                        <a href="#" @click.prevent="handleLogout">{{ t('Logout') }}</a>
                                    </a-menu-item>
                                </a-menu>
                            </template>
                        </a-dropdown>
                        <div v-else class="flex space-x-2">
                            <a-dropdown>
                                <a-button class="text-[18px] p-0" type="text" shape="circle">
                                    <template #icon>
                                        <UserOutlined />

                                    </template>
                                </a-button>
                                <template #overlay>
                                    <a-menu>
                                        <a-menu-item key="login">
                                            <Link :href="route('login')" class="text-gray-600 hover:text-gray-900">
                                            {{ t('Login') }}
                                            </Link>
                                        </a-menu-item>
                                        <a-menu-item key="register">
                                            <Link :href="route('register')" class="text-gray-600 hover:text-gray-900">
                                            {{ t('Register') }}
                                            </Link>
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>
                        </div>
                    </div>
                    <div class="md:hidden ">
                        <a-button class="text-[18px] p-0" type="text" shape="circle" @click="toggleSearch">
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
                    <SearchProducts />
                </div>
            </Transition>


        </div>
    </header>

    <!-- Mobile Sidebar -->
    <MobileSidebar v-model:visible="mobileMenuOpen" />

    <!-- Mobile Bottom Navigation -->
    <MobileBottomNav :total-cart-qty="totalCartQty"  @toggle-cart="toggleCart" />


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

/* Mega Menu Styles */
.mega-menu {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Ensure mega menu appears above other elements */
:deep(.ant-dropdown) {
    z-index: 1000;
}

:deep(.ant-dropdown-menu) {
    padding: 0;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Hover effects for mega menu items */
.mega-menu a:hover {
    transform: translateX(5px);
}

.mega-menu a {
    transition: all 0.2s ease;
}

/* Custom scrollbar for mega menu */
.mega-menu ul::-webkit-scrollbar {
    width: 6px;
}

.mega-menu ul::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.mega-menu ul::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.mega-menu ul::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
