<script setup lang="ts">
import { ref, computed, defineProps } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { ShoppingCartOutlined, HeartOutlined } from '@ant-design/icons-vue';
import { Row, Col, Card, Button, Skeleton } from 'ant-design-vue';
import { router, Link } from '@inertiajs/vue3';
import PaginationComponent from '@/components/frontend/PaginationComponent.vue';
import FilterProduct from './FilterProduct.vue';
import LoginModal from "@/components/frontend/LoginModal.vue";

interface Product {
    id: number;
    name: string;
    slug: string;
    final_price: number;
    sale_price: number;
    thumnail_img: string;
    category_name: string;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    product_count: number;
}

// Props
const props = defineProps<{
    title?: string;
    subtitle?: string;
    showAllProductsButton?: boolean;
    showFilter?: boolean;
    showPagination?: boolean;
    categories?: Category[];
    selectedCategory?: string | null;
    showTitle?: boolean;
    showSubTitle?: boolean;
    sectionClass?: string;
}>();

const page = usePage();

const translations = computed(() => {
    return (page.props.translations as any)?.products || {};
});

// Products
const products = computed<Product[]>(() => (page.props.products as any)?.data || []);

// Format price
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 2,
    }).format(price);
};

// Product detail navigation
const goToProductDetail = (slug: string) => {
    router.visit(route('product.detail', { slug }));
};

// Add to cart
const addingToCart = ref(new Set<number>());
const addToCart = (product: Product) => {
    addingToCart.value.add(product.id);
    router.post(
        route('cart.add'),
        {
            id: product.id,
            qty: 1,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                addingToCart.value.delete(product.id);
            },
            onError: (errors) => {
                addingToCart.value.delete(product.id);
                console.error('Failed to add to cart', errors);
            },
        }
    );
};
const isLoginModalVisible = ref(false);
const user = computed(() => (page.props.auth as any)?.user);


const loadingWishlist = ref(new Set<number>());

const addToWhishlist = (productId: number) => {
    if (user.value) {
        loadingWishlist.value.add(productId);
        router.post(route('wishlist.add'),
            { product_id: productId },
            {
                preserveScroll: true,
                onSuccess: () => {
                    loadingWishlist.value.delete(productId);
                },
                onError: () => {
                    loadingWishlist.value.delete(productId);
                },
            }
        );
    } else {
        isLoginModalVisible.value = true;
    }
};
const wishlist = computed<number[]>(() => (page.props.wishlist as number[]) || []);

const isInWishlist = (productId: number) => {
    return wishlist.value.includes(productId);
};
</script>

<template>
    <section>
        <div class="container  mx-auto">
            <FilterProduct v-if="props.showFilter === true" :categories="props.categories"
                :selectedCategory="props.selectedCategory" :translations="translations" />
        </div>
    </section>
    <section :class="['', props.sectionClass]">
        <div class="container mx-auto px-2 sm:px-4">
            <div class="text-center mb-8 sm:mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3 sm:mb-4" v-if="props.showTitle !== false">
                    {{ props.title || translations.title || 'Product List' }}
                </h2>
                <p class="text-base sm:text-lg text-gray-600 max-w-3xl mx-auto" v-if="props.showSubTitle !== false">
                    {{ props.subtitle || translations.subtitle || 'Explore our wide range of high-quality products tailored to your needs.' }}
                </p>
            </div>

            <div v-if="products.length > 0">
                <!-- Product Cards -->
                <Row :gutter="[16, 16]">
                    <Col :xs="12" :sm="12" :md="8" :lg="8" :xl="6" v-for="product in products" :key="product.id"
                        class="mb-6">
                    <Card hoverable class="h-full product-card cursor-pointer" @click="goToProductDetail(product.slug)">
                        <template #cover>
                            <div class="relative h-28 sm:h-32 md:h-40 lg:h-48 overflow-hidden">
                                <template v-if="product.thumnail_img">
                                    <img :src="'/storage/' + product.thumnail_img" :alt="product.name"
                                        class="w-full h-full object-cover" />
                                </template>
                                <template v-else>
                                    <Skeleton.Image class="w-full h-full" />
                                </template>
                                <div
                                    class="absolute top-1 right-1 bg-white rounded-full px-1.5 py-0.5 text-[10px] sm:text-xs font-medium text-gray-800">
                                    {{ product.category_name }}
                                </div>
                              <Button
                                @click.stop="addToWhishlist(product.id)"
                                shape="circle"
                                size="small"
                                :loading="loadingWishlist.has(product.id)"
                                :class="[
                                    'flex items-center justify-center !w-7 !h-7 absolute top-7 right-1',
                                    isInWishlist(product.id) ? '!bg-red-500 !text-white !border-red-500 hover:!bg-red-600' : '!bg-white !text-black !border-gray-300 hover:!bg-red-500 hover:!text-white hover:!border-red-500'
                                ]"
                                aria-label="Toggle favorite"
                            >
                                <template #icon>
                                    <HeartOutlined />
                                </template>
                            </Button>

                            </div>
                        </template>
                        <div>
                            <h3 class="text-[15px] sm:text-xl font-semibold text-gray-900 mb-1">
                                {{ product.name }}
                            </h3>
                            <div class="flex justify-between items-center">
                                <div class="flex flex-wrap">
                                    <span class="text-xs sm:text-sm md:text-base font-bold text-primary pr-2">
                                        {{ formatPrice(product.final_price) }}
                                    </span>
                                    <span class="text-xs sm:text-sm md:text-base text-secondary line-through">
                                        {{ formatPrice(product.sale_price) }}
                                    </span>
                                </div>
                            </div>
                            <Button type="primary" class="btn-primary flex items-center justify-center mt-2"
                                :loading="addingToCart.has(product.id)" @click.stop="addToCart(product)"
                                aria-label="Add to cart">
                                <template #icon>
                                    <ShoppingCartOutlined />
                                </template>
                                {{ translations.add_to_cart || 'Add to Cart' }}
                            </Button>
                        </div>
                    </Card>
                    </Col>
                </Row>

                <!-- Pagination -->
                <PaginationComponent v-if="props.showPagination !== false" :pagination-data="page.props.products"
                    route-name="all.products" />

                <!-- All Products Button -->
                <div v-if="props.showAllProductsButton === true" class="text-center mt-8 sm:mt-12">
                    <Button size="large" class="btn-primary" aria-label="View all products">
                        <Link :href="route('all.products')">
                        {{ translations.all_products || 'All Products' }}<i class="fa fa-long-arrow-right ml-2"
                            aria-hidden="true"></i>
                        </Link>
                    </Button>
                </div>
            </div>

            <div v-else>
                <a-result status="404" title="404" sub-title="We couldn’t find any products matching your search.">
                    <template #extra>
                        <Button class="btn-primary" size="large" type="primary">
                            <Link :href="route('all.products')">
                            Continue Shopping
                            </Link>
                        </Button>
                    </template>
                </a-result>
            </div>
        </div>
    </section>
      <LoginModal v-model:open="isLoginModalVisible" :canResetPassword="false" />
</template>

<style scoped>
</style>
