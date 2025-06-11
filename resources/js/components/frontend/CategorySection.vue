<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Row, Col } from 'ant-design-vue';
import { Link } from "@inertiajs/vue3";


const page = usePage();

const translations = computed(() => {
    return (page.props.translations as any)?.products || {};
});

const categories = computed(() => {
    return (page.props.categories as any[]) || [];
});
</script>

<template>
  <section class="py-14">
    <div class="container mx-auto px-2 sm:px-4">
      <div class="text-center mb-8 sm:mb-12">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3 sm:mb-4">{{ translations.shop_by_category || 'Shop By Category' }}</h2>
      </div>

      <Row :gutter="[16, 16]">
        <Col :xs="12" :sm="8" :md="6" :lg="4" :xl="4" v-for="category in categories" :key="category.id">
          <div class="text-center">
            <div class="mb-4 image-container">
                <Link :href="route('all.products', { category: category.slug })">
                    <img
                      :src="category.image ? '/storage/' + category.image : '/assets/images/default-category.png'"
                      :alt="category.name"
                      class="category-image"
                    />
                </Link>
            </div>
            <h5 class="text-lg font-semibold my-1">{{ category.name }}</h5>
            <p class="text-sm text-gray-500">{{ category.product_count }} {{ translations.products || 'Products' }}</p>
          </div>
        </Col>
      </Row>
    </div>
  </section>
</template>

<style scoped>
.image-container {
  overflow: hidden;
  width: 140px;
  height: 140px;
  margin: 0 auto;
}

.category-image {
  transition: transform 0.3s ease;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-container:hover .category-image {
  transform: scale(1.2);
}
</style>

