<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { getCurrentInstance, ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

const page = usePage();


defineProps<{
  visible: boolean;
}>();

const emit = defineEmits<{
  'update:visible': [value: boolean]
}>();

const activeKey = ref<string[]>([]);

// Get categories from global data
const categories = computed(() => {
    return page.props.global_categories as any[] || [];
});
const brands = computed(() => {
    return page.props.global_brands as any[] || [];
});

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
              {{ t('Home') }}
            </Link>
          </a-menu-item>
          <a-menu-item key="products">
            <Link :href="route('all.products')" class="text-gray-600 hover:text-gray-900">
              {{ t('Shop') }}
            </Link>
          </a-menu-item>
        </a-menu>

        <!-- Collapse Section -->
        <a-collapse v-model:activeKey="activeKey" accordion>
          <a-collapse-panel key="categories" :header="t('Categories')">
            <div class="category-list">
              <Link
                v-for="category in categories"
                :key="`category-${category.id}`"
                :href="route('all.products', { category: category.slug })"
                class="category-item"
              >
                {{ category.name }}
              </Link>
            </div>
          </a-collapse-panel>
        </a-collapse>
               <!-- Collapse Section -->
               <a-collapse v-model:activeKey="activeKey" accordion>
          <a-collapse-panel key="brands" :header="t('Brands')">
            <div class="category-list">
              <Link
                v-for="brand in brands"
                :key="`brand-${brand.id}`"
                :href="route('all.products', { brand: brand.slug })"
                class="category-item"
              >
                {{ brand.name }}
              </Link>
            </div>
          </a-collapse-panel>
        </a-collapse>

        <a-menu mode="vertical">
          <a-menu-item key="about">
            <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">
              {{ t('About Us') }}
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
.ant-collapse{
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    color: rgba(0, 0, 0, 0.88);
    font-size: 14px;
    line-height: 1.5714285714285714;
    list-style: none;
    background: none;
    border: none;
    border-radius: 0;
}
.ant-collapse>.ant-collapse-item:last-child>.ant-collapse-header {
   border-radius: 0 !important;
}
.ant-collapse-item {
   border-bottom: none !important;
}

.ant-collapse-header {
   border-bottom: none !important;
}

.ant-collapse-content {
   border-bottom: none !important;
}
.ant-collapse .ant-collapse-content {
    color: rgba(0, 0, 0, 0.88);
    background-color: #ffffff;
    border-top: none !important;
}

.category-list {
    display: flex;
    flex-direction: column;
}

.category-item {
    display: block;
    padding: 8px 16px;
    color: #666;
    text-decoration: none;
    font-size: 14px;
    border-bottom: 1px solid #f5f5f5;
    transition: all 0.3s ease;
}

.category-item:hover {
    color: #1890ff;
    background-color: #f8f9fa;
}

.category-item:last-child {
    border-bottom: none;
}
</style>
