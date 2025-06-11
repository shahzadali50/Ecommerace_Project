<script setup lang="ts">
import UserLayout from '@/layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
import BannerSection from '@/components/frontend/BannerSection.vue';
import { defineAsyncComponent } from 'vue';

// ✅ Lazy-loaded components
const ProductSection = defineAsyncComponent(() => import('@/components/frontend/ProductSection.vue'));
const ShopByCategory = defineAsyncComponent(() => import('@/components/frontend/CategorySection.vue'));
</script>

<template>
  <UserLayout>
    <Head title="Home" />
    <BannerSection />
    <ProductSection
      :showAllProductsButton="true"
      :showTitle="true"
      :showSubTitle="true"
      sectionClass="py-14"
    />
    <ShopByCategory />
  </UserLayout>
</template>

<style scoped></style>
