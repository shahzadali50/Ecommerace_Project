<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    HomeOutlined,
    ShopOutlined,
    HeartOutlined,
    ShoppingCartOutlined,
} from '@ant-design/icons-vue';
const page = usePage();
const translations = computed(() => {
    return (page.props.translations as any)?.header || {};
});
const currentPath = computed(() => page.url);


defineProps({
    isAuthenticated: {
        type: Boolean,
        default: false,
    },
     totalCartQty: {
    type: Number,
    default: 0,
  },
});
const wishlist = computed(() => {
    return page.props.wishlist as number[] || [];
});
const wishlistCount = computed(() => wishlist.value.length);



const emit = defineEmits(['toggleCart', 'logout']);

const toggleCart = () => {
    emit('toggleCart');
};
</script>
<template>
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="flex justify-around items-center h-16">
            <Link :href="route('home')" class="flex flex-col items-center"
                :class="currentPath === route('home', {}, false) ? 'text-black font-bold' : 'text-gray-600'">
            <HomeOutlined class="text-xl" />
            <span class="text-xs mt-1">{{ translations.home || 'Home' }}</span>
            </Link>
            <Link :href="route('all.products')" class="flex flex-col items-center"  :class="currentPath === route('all.products', {}, false) ? 'text-black font-bold' : 'text-gray-600'">
            <ShopOutlined class="text-xl" />
            <span class="text-xs mt-1">{{ translations.shop || 'Shop' }}</span>
            </Link>
            <Link :href="route('wishlist')" class="flex flex-col items-center relative "
                :class="currentPath === route('wishlist', {}, false) ? 'text-black font-bold' : 'text-gray-600'">
            <HeartOutlined class="text-xl" />
            <span class="text-xs mt-1">{{ translations.whishlist || 'Whislist' }}</span>
            
              <a-badge :count="wishlistCount" class="absolute right-[-3px] top-[-5px]"></a-badge>
            </Link>

            <a class="flex flex-col items-center relative"
                :class="[currentPath === route('cart.checkout', {}, false) || currentPath === route('cart.payment', {}, false) ? 'text-black font-bold' : 'text-gray-600']"
                @click="toggleCart">
                <ShoppingCartOutlined class="text-xl" />
                <span class="text-xs mt-1">{{ translations.cart || 'Cart' }}</span>
                <a-badge :count="totalCartQty" class="absolute left-[15px] top-[-5px]"></a-badge>

            </a>
        </div>
    </div>
</template>
