<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const searchQuery = ref('');
const searchResults = ref([]);
const loading = ref(false);
const showDropdown = ref(false);

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
      searchResults.value = data.products;
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
const selectProduct = (product: any) => {
  router.visit(route('product.detail', product.slug));
  searchQuery.value = '';
  searchResults.value = [];
  showDropdown.value = false;
};
</script>

<template>
  <div class="relative w-64">
    <a-input-search
      v-model:value="searchQuery"
      placeholder="Search products..."
      :loading="loading"
      @input="searchProducts"
    />
    <div
      v-if="showDropdown && searchResults.length"
      class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto mt-1"
    >
      <ul>
        <li
          v-for="product in searchResults"
          :key="product.id"
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
          @click="selectProduct(product)"
        >
          {{ product.name }}
        </li>
      </ul>
    </div>
    <div
      v-if="showDropdown && !searchResults.length && !loading && searchQuery"
      class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg p-4 mt-1"
    >
      No products found.
    </div>
  </div>
</template>

<style scoped>
/* Add any additional styles if needed */
</style>
