<script setup lang="ts">
import UserLayout from '@/layouts/UserLayout.vue';
import ProductSection from '@/components/frontend/ProductSection.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Row, Col } from 'ant-design-vue';

const page = usePage();
const translations = computed(() => {
  return (page.props.translations as any)?.products || {};
});

// Compute the product count text dynamically
const productCountText = computed(() => {
  const products = page.props.products as any;
  if (!products || !products.data) return 'Showing 0 of 0 Products';
  const total = products.total || 0; // Total products from database
  const from = products.from || 1; // Starting product number
  const to = products.to || products.data.length; // Ending product number
  return `Showing ${from}-${to} of ${total} Products`;
});
</script>

<template>
  <UserLayout>
    <Head :title="translations.shop || 'Shop'" />
    <section
      class="bg-cover bg-center py-16 sm:py-24"
      style="background-image: url('/assets/images/page-header-bg.jpg');"
    >
      <div class="container mx-auto">
        <Row>
          <Col :span="24" class="text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-0">
              Shop By Products
            </h1>
          </Col>
        </Row>
      </div>
    </section>
    <section>
      <div class="container mx-auto">
        <Row class="items-center m-5">
          <Col :span="12">
            <a-breadcrumb class="">
              <a-breadcrumb-item>Home</a-breadcrumb-item>
              <a-breadcrumb-item>Shop</a-breadcrumb-item>
            </a-breadcrumb>
          </Col>
          <Col :span="12" class=" text-right">
            <p class="mb-0">{{ productCountText }}</p>
          </Col>
          <Col :span="24" class="my-2">
            <hr />
          </Col>
        </Row>
      </div>
    </section>

  <ProductSection
  :showPagination="true"
  :showFilter="true"
  :categories="page.props.categories"
  :selectedCategory="page.props.selectedCategory"
  :brands="page.props.brands"
  :selectedBrand="page.props.selectedBrand"
  sectionClass="py-4"
/>

  </UserLayout>
</template>
