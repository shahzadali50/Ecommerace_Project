<script setup lang="ts">
import { ref, watch, getCurrentInstance } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

// Define the Product interface based on the backend response
interface Product {
    id: number;
    name: string;
    slug: string;
    thumnail_img: string | null;
    sale_price: number;
    final_price: number;
}

const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

const searchQuery = ref('');
const searchResults = ref<Product[]>([]);
const loading = ref(false);
const showDropdown = ref(false);

// Format price with currency and two decimal places
const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(price);
};

// Debounced search function to avoid excessive API calls
const searchProducts = debounce(async (query: string) => {
    if (!query) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }

    loading.value = true;
    try {
        const response = await fetch(`/products/search?query=${encodeURIComponent(query)}`);
        const data = await response.json();
        if (response.ok) {
            searchResults.value = data.products as Product[];
            showDropdown.value = true;
        } else {
            console.error(data.error);
        }
    } catch (error) {
        console.error('Error fetching search results:', error);
    } finally {
        loading.value = false;
    }
}, 300);

// Watch for changes in search query
watch(searchQuery, (newQuery) => {
    searchProducts(newQuery);
});

// Handle product click to navigate to product detail page
const selectProduct = (product: Product) => {
    router.visit(route('product.detail', { slug: product.slug }));
    searchQuery.value = '';
    searchResults.value = [];
    showDropdown.value = false;
};
</script>

<template>
    <div class="w-full md:w-64 relative">
        <a-input-search v-model:value="searchQuery" :placeholder="t('Search products...')" :loading="loading"
            @input="searchProducts" class="focus:ring-2 focus:ring-blue-500" />
        <div v-if="showDropdown && searchResults.length"
            class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto mt-1">
            <ul>
                <li v-for="product in searchResults" :key="product.id"
                    class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer"
                    @click="selectProduct(product)">
                    <img :src="product.thumnail_img ? '/storage/' + product.thumnail_img : '/images/placeholder.jpg'"
                        :alt="product.name || 'Product thumbnail'" class="w-10 h-10 object-cover rounded mr-3"
                        loading="lazy" />
                    <div class="flex-1">
                        <span class="text-gray-900 font-medium">{{ product.name }}</span>
                        <div class="flex items-center space-x-2">
                            <span v-if="product.sale_price !== product.final_price"
                                class="text-gray-500 line-through text-sm">
                                <span></span>
                                {{ formatPrice(product.sale_price) }}
                            </span>
                            <span class="text-green-600 font-semibold">
                                <span class="text-xs sm:text-sm md:text-base text-secondary line-through"> </span>
                                {{ formatPrice(product.final_price) }}
                            </span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div v-if="showDropdown && !searchResults.length && !loading && searchQuery"
            class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg p-4 mt-1 text-gray-600">
            {{ t('We\'re sorry, no products match your search.') }}
        </div>
    </div>
</template>

<style scoped>
img {
    max-width: 100%;
    height: auto;
}

img:not([src]) {
    background-color: #f0f0f0;
}
</style>
