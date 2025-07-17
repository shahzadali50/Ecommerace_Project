<script setup lang="ts">
import { ref, computed, getCurrentInstance } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {  Card, Button, Skeleton, Carousel } from 'ant-design-vue';
import {
    ShoppingCartOutlined,
    HeartOutlined,
    LeftOutlined,
    RightOutlined,
} from '@ant-design/icons-vue';
import LoginModal from "@/components/frontend/LoginModal.vue";

const page = usePage();
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

const user = computed(() => (page.props.auth as any)?.user);
const isLoginModalVisible = ref(false);
const wishlist = computed<number[]>(() => (page.props.wishlist as number[]) || []);
const loadingWishlist = ref(new Set<number>());
const addingToCart = ref(new Set<number>());
const carouselRef = ref();

defineProps<{
    relatedProducts: any[];
}>();

const isInWishlist = (productId: number) => {
    return wishlist.value.includes(productId);
};

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
                }
            }
        );
    } else {
        isLoginModalVisible.value = true;
    }
};

const goToProductDetail = (slug: string) => {
    router.visit(route('product.detail', { slug }));
};

const addRelatedProductToCart = (relatedProduct: any) => {
    addingToCart.value.add(relatedProduct.id);
    router.post(route('cart.add'), {
        id: relatedProduct.id,
        qty: 1,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            addingToCart.value.delete(relatedProduct.id);
            console.log('Added related product to cart');
        },
        onError: (errors) => {
            addingToCart.value.delete(relatedProduct.id);
            console.error('Failed to add related product to cart', errors);
        }
    });
};
</script>

<template>
    <div v-if="relatedProducts && relatedProducts.length > 0">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3 sm:mb-4">{{ t('Related Products') }}</h2>
        </div>

        <!-- Related Products Slider -->
        <div class="relative">
            <Carousel
                ref="carouselRef"
                :slidesToShow="4"
                :slidesToScroll="1"
                :dots="false"
                :infinite="relatedProducts.length > 4"
                :autoplay="false"
                :responsive="[
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 2,
                        }
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1,
                        }
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2,
                        }
                    }
                ]"
                class="related-products-carousel">
                <div v-for="relatedProduct in relatedProducts" :key="relatedProduct.id">
                    <Card hoverable class="h-full product-card cursor-pointer" @click="goToProductDetail(relatedProduct.slug)">
                        <template #cover>
                            <div class="relative h-28 sm:h-32 md:h-40 lg:h-48 overflow-hidden">
                                <template v-if="relatedProduct.thumbnail_image">
                                    <img :src="relatedProduct.thumbnail_image" :alt="relatedProduct.name"
                                        class="w-full h-full object-cover" />
                                </template>
                                <template v-else>
                                    <Skeleton.Image class="w-full h-full" />
                                </template>
                                <div
                                    class="absolute top-1 right-1 bg-white rounded-full px-1.5 py-0.5 text-[10px] sm:text-xs font-medium text-gray-800">
                                    {{ t(relatedProduct.category_name) }}
                                </div>
                                <Button
                                    @click.stop="addToWhishlist(relatedProduct.id)"
                                    shape="circle"
                                    size="small"
                                    :loading="loadingWishlist.has(relatedProduct.id)"
                                    :class="[
                                        'flex items-center justify-center !w-7 !h-7 absolute top-7 right-1',
                                        isInWishlist(relatedProduct.id) ? '!bg-red-500 !text-white !border-red-500 hover:!bg-red-600' : '!bg-white !text-black !border-gray-300 hover:!bg-red-500 hover:!text-white hover:!border-red-500'
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
                                {{ t(relatedProduct.name) }}
                            </h3>
                            <div class="flex justify-between items-center">
                                <div class="flex flex-wrap">
                                    <span class="text-xs sm:text-sm md:text-base font-bold text-primary pr-2">
                                        ${{ relatedProduct.final_price }}
                                    </span>
                                    <span v-if="relatedProduct.sale_price" class="text-xs sm:text-sm md:text-base text-secondary line-through">
                                        ${{ relatedProduct.sale_price }}
                                    </span>
                                </div>
                            </div>
                            <Button type="primary"
                                :class="[
                                    'flex items-center justify-center mt-2',
                                    (relatedProduct.stock || 0) === 0 ? '!bg-gray-400 !text-white !border-gray-400 cursor-not-allowed hover:!bg-gray-400': 'btn-primary']"
                                :loading="addingToCart.has(relatedProduct.id)"
                                :disabled="(relatedProduct.stock || 0) === 0"
                                @click.stop="addRelatedProductToCart(relatedProduct)"
                                aria-label="Add to cart">
                                <template #icon>
                                    <ShoppingCartOutlined />
                                </template>
                                {{ (relatedProduct.stock || 0) === 0 ? t('Out of Stock') : t('Add to Cart') }}
                            </Button>
                        </div>
                    </Card>
                </div>
            </Carousel>
            <!-- Custom Navigation Arrows -->
            <button
                v-if="relatedProducts.length > 4"
                class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-50 transition-colors duration-200 -ml-4"
                @click="carouselRef?.prev()">
                <LeftOutlined class="text-gray-600" />
            </button>
            <button
                v-if="relatedProducts.length > 4"
                class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-50 transition-colors duration-200 -mr-4"
                @click="carouselRef?.next()">
                <RightOutlined class="text-gray-600" />
            </button>
        </div>
    </div>

    <!-- No Related Products Message -->
    <div v-else class="text-center py-8">
        <p class="text-gray-500">{{ t('No related products found.') }}</p>
    </div>

    <LoginModal v-model:open="isLoginModalVisible" :canResetPassword="false" />
</template>

<style scoped>
.related-products-carousel {
    padding: 0 0px;
}

.related-products-carousel :deep(.slick-slide) {
    padding: 0 8px;
}

.related-products-carousel :deep(.slick-track) {
    display: flex;
    gap: 16px;
}

.related-products-carousel :deep(.slick-slide > div) {
    height: 100%;
}

.related-products-carousel :deep(.slick-slide > div > div) {
    height: 100%;
}

/* Hide default arrows */
.related-products-carousel :deep(.slick-arrow) {
    display: none !important;
}

/* Custom navigation buttons */
.custom-nav-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    background: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.custom-nav-button:hover {
    background: #f5f5f5;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.custom-nav-button.prev {
    left: -20px;
}
.custom-nav-button.next {
    right: -20px;
}

@media (max-width: 768px) {
    .custom-nav-button {
        width: 36px;
        height: 36px;
    }

    .custom-nav-button.prev {
        left: -18px;
    }

    .custom-nav-button.next {
        right: -18px;
    }
}
@media (max-width: 576px) {
    .related-products-carousel :deep(.slick-slide) {
        width: 100% !important;
        padding: 0;
    }

    .related-products-carousel :deep(.slick-track) {
        gap: 10px;
    }
}
</style>
