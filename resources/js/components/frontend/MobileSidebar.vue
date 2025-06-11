<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';



const page = usePage();
const translations = computed(() => {
    return (page.props.translations as any)?.header || {};
});


defineProps<{
  visible: boolean;
}>();

const emit = defineEmits<{
  'update:visible': [value: boolean]
}>();

const closeDrawer = () => {
  emit('update:visible', false);
};
</script>

<template>
  <a-drawer
    :open="visible"
    placement="left"
    :closable="true"
    @close="closeDrawer"
    width="280"
  >
    <div class="flex flex-col h-full">
      <!-- Navigation Links -->
      <nav class="flex-1">
        <a-menu mode="vertical">
          <a-menu-item key="home">
            <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">
              {{ translations.home || 'Home' }}
            </Link>
          </a-menu-item>
          <a-menu-item key="products">
            <Link :href="route('all.products')" class="text-gray-600 hover:text-gray-900">
              {{ translations.shop || 'Shop' }}
            </Link>
          </a-menu-item>
          <a-menu-item key="categories">
            <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">
              {{ translations.categories || 'Categories' }}
            </Link>
          </a-menu-item>
          <a-menu-item key="brands">
            <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">
              {{ translations.brands || 'Brands' }}
            </Link>
          </a-menu-item>
          <a-menu-item key="about">
            <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">
              {{ translations.about_us || 'About Us' }}
            </Link>
          </a-menu-item>
        </a-menu>
      </nav>
    </div>
  </a-drawer>
</template>

<style scoped>
.ant-drawer-body {
  padding: 0;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.ant-menu-item {
  margin: 0 !important;
}

.ant-menu-item a {
  color: #666;
}

.ant-menu-item-selected a {
  color: #1890ff;
}
</style>
