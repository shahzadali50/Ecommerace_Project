<script setup lang="ts">
import { defineProps } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Button, Collapse, CollapsePanel, Checkbox } from 'ant-design-vue';
import { ClearOutlined, FilterOutlined } from '@ant-design/icons-vue';
import { ref, onMounted, onUnmounted } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    product_count: number;
}

interface Brand {
    id: number;
    name: string;
    slug: string;
    product_count: number;
}

const props = defineProps<{
    categories?: Category[];
    selectedCategory?: string | null;
    brands?: Brand[];
    selectedBrand?: string | null;
}>();

const isFilterDrawerVisible = ref(false);
const openFilterDrawer = () => { isFilterDrawerVisible.value = true; };
const closeFilterDrawer = () => { isFilterDrawerVisible.value = false; };

const activeKey = ref(['1', '2']);


// Bind min/max price
const minPrice = ref<number | null>(null);
const maxPrice = ref<number | null>(null);

const filterByCategory = (categorySlug: string | null) => {
    router.get(
        route('all.products'),
        {
            category: categorySlug,
            min_price: minPrice.value,
            max_price: maxPrice.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                closeFilterDrawer();
            },
        }
    );
};

const filterByBrand = (brandSlug: string | null) => {
    router.get(
        route('all.products'),
        {
            category: props.selectedCategory,
            brand: brandSlug,
            min_price: minPrice.value,
            max_price: maxPrice.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                closeFilterDrawer();
            },
        }
    );
};

const filterByPrice = () => {
    router.get(
        route('all.products'),
        {
            category: props.selectedCategory,
            min_price: minPrice.value,
            max_price: maxPrice.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                closeFilterDrawer();
            },
        }
    );
};

const drawerWidth = ref(500);

const updateDrawerWidth = () => {
    drawerWidth.value = window.innerWidth <= 576 ? 280 : 380;
};

onMounted(() => {
    updateDrawerWidth();
    window.addEventListener('resize', updateDrawerWidth);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateDrawerWidth);
});
</script>

<template>
    <div>
        <div class="flex item-center">

            <Button type="text" class="flex items-center" size="large" @click="openFilterDrawer">
                <FilterOutlined />Filter
            </Button>
            <Button size="large" type="text">
                <Link :href="route('all.products')" class="flex items-center">
                <ClearOutlined />Clear Filters
                </Link>
            </Button>
        </div>
        <a-drawer title="Filter Products" placement="left" :open="isFilterDrawerVisible" @close="closeFilterDrawer"
            :width="drawerWidth">
            <div>

                <div>
                    <div class="flex items-end gap-2 my-4">
                        <div class="">
                            <label class="block text-xs mb-1">From</label>
                            <a-input type="number" placeholder="00000" min="0" v-model:value="minPrice" />
                        </div>
                        <div class="">
                            <label class="block text-xs mb-1">To</label>
                            <a-input type="number" placeholder="2000" min="0" v-model:value="maxPrice" />
                        </div>
                        <div class="">

                            <Button type="dashed" @click="filterByPrice">
                                <FilterOutlined /> Filter
                            </Button>
                        </div>
                    </div>
                </div>
                <Collapse v-model:activeKey="activeKey" :bordered="false">
                    <CollapsePanel key="1" header="Filter by Category">
                        <div v-for="category in props.categories" :key="category.id"
                            class="flex justify-between items-center mb-2 cursor-pointer"
                            @click="filterByCategory(category.slug)">
                            <Checkbox :checked="props.selectedCategory === category.slug">
                                {{ category.name }}
                            </Checkbox>
                             <a-badge :count="category.product_count" :number-style="{ backgroundColor: '#757d87' }" />
                        </div>
                    </CollapsePanel>
                    <CollapsePanel key="2" header="Filter by Brand">
                        <div v-for="brand in props.brands" :key="brand.id"
                            class="flex justify-between items-center mb-2 cursor-pointer"
                            @click="filterByBrand(brand.slug === props.selectedBrand ? null : brand.slug)">
                            <Checkbox :checked="props.selectedBrand === brand.slug">
                                {{ brand.name }}
                            </Checkbox>
                              <a-badge :count=" brand.product_count" :number-style="{ backgroundColor: '#757d87' }" />

                        </div>
                    </CollapsePanel>

                </Collapse>
            </div>
        </a-drawer>
    </div>
</template>

<style scoped></style>
