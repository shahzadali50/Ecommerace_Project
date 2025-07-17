<script setup lang="ts">
import { ref, computed, getCurrentInstance } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import UserLayout from '@/layouts/UserLayout.vue';
import { Row, Col } from 'ant-design-vue';
import { Link } from '@inertiajs/vue3';
import {
    ShoppingCartOutlined,
    HeartOutlined,
    CheckCircleOutlined,
    CarOutlined,
    SafetyCertificateOutlined,
    ArrowLeftOutlined,
    ArrowRightOutlined,
    MinusOutlined,
    PlusOutlined
} from '@ant-design/icons-vue';
import LoginModal from "@/components/frontend/LoginModal.vue";
import RelatedProducts from "@/components/frontend/RelatedProducts.vue";
const page = usePage();
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

const user = computed(() => (page.props.auth as any)?.user);
const isLoginModalVisible = ref(false);
const isAddingToWishlist = ref(false);
const wishlist = computed<number[]>(() => (page.props.wishlist as number[]) || []);

const isInWishlist = (productId: number) => {
    return wishlist.value.includes(productId);
};

const addToWhishlist = (productId: number) => {
    if (user.value) {
        isAddingToWishlist.value = true;
        router.post(route('wishlist.add'),
            { product_id: productId },
            {
                preserveScroll: true,
                onSuccess: () => {
                    isAddingToWishlist.value = false;
                },
                onError: () => {
                    isAddingToWishlist.value = false;
                }
            }
        );
    } else {
        isLoginModalVisible.value = true;
    }
};

interface Product {
    id: number;
    name: string;
    description: string;
    slug: string;
    final_price: number;
    sale_price: number | null;
    discount: number | null;
    stock: number;
    category_name: string;
    thumbnail_image: string | null;
    gallery_images: string[];
}


const props = defineProps<{
    product: Product;
    relatedProducts: any[];
}>();

const quantity = ref(1);
const currentImageIndex = ref(0);

// Combine thumbnail with gallery for slider
const fullGallery = computed(() => {
    const images = [...(props.product.gallery_images || [])];
    if (props.product.thumbnail_image) {
        images.unshift(`/storage/${props.product.thumbnail_image}`);
    }
    return images;
});

const nextImage = () => {
    if (fullGallery.value.length > 0) {
        currentImageIndex.value = (currentImageIndex.value + 1) % fullGallery.value.length;
    }
};

const prevImage = () => {
    if (fullGallery.value.length > 0) {
        currentImageIndex.value = currentImageIndex.value === 0
            ? fullGallery.value.length - 1
            : currentImageIndex.value - 1;
    }
};

const increaseQuantity = () => {
    if (quantity.value < props.product.stock) {
        quantity.value++;
    }
};

const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};
const isAddingToCart = ref(false);
const addToCart = () => {
    isAddingToCart.value = true;
    router.post(route('cart.add'), {
        id: props.product.id,
        qty: quantity.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isAddingToCart.value = false;
            console.log('Added to cart');
        },
        onError: (errors) => {
            isAddingToCart.value = false;
            console.error('Failed to add to cart', errors);
        }
    });
};
</script>

<template>
    <UserLayout>

        <Head :title="product.name" />
        <!-- Breadcrumb -->

        <section class="pb-7">
            <div class="container mx-auto px-4">
                <div class="flex flex-wrap items-center text-sm text-gray-500">
                    <Link :href="route('home')" class="hover:text-primary">{{ t('Home') }}</Link>
                    <span class="mx-2">/</span>
                    <span>{{ t('Products') }}</span>
                    <span class="mx-2">/</span>
                    <span>{{ t(product.category_name) }}</span>
                    <span class="mx-2">/</span>
                    <span class="text-gray-700">{{ t(product.name) }}</span>
                </div>
            </div>
        </section>




        <!-- Main Content Section -->
        <section>
            <div class="container mx-auto px-4">
                <Row :gutter="[32, 32]">
                    <!-- Product Images -->
                    <Col :xs="24" :lg="12">
                    <div class="relative">
                        <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                            <img :src="fullGallery[currentImageIndex]" :alt="product.name"
                                class="w-full h-full object-cover" />
                        </div>

                        <!-- Navigation Buttons -->
                        <button @click="prevImage"
                            class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-md hover:bg-gray-100 transition-colors duration-200">
                            <ArrowLeftOutlined />
                        </button>
                        <button @click="nextImage"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-md hover:bg-gray-100 transition-colors duration-200">
                            <ArrowRightOutlined />
                        </button>

                        <!-- Thumbnails -->
                        <div class="flex mt-4 space-x-2 overflow-x-auto">
                            <div v-for="(image, index) in fullGallery" :key="index" @click="currentImageIndex = index"
                                class="w-16 h-16 rounded-md overflow-hidden cursor-pointer border-2 transition-all duration-200 hover:opacity-80"
                                :class="currentImageIndex === index ? 'border-primary' : 'border-transparent'">
                                <img :src="image" :alt="`${product.name} - ${index + 1}`"
                                    class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>
                    </Col>

                    <!-- Product Info -->
                    <Col :xs="24" :lg="12">
                    <div class="h-full">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ t(product.name) }}</h1>

                        <!-- Price -->
                        <div class="mb-6">
                            <div class="flex items-center">
                                <span class="text-3xl font-bold text-gray-900">${{ product.final_price }}</span>
                                <span v-if="product.sale_price" class="ml-3 text-lg text-gray-500 line-through">${{
                                    product.sale_price }}</span>
                                <span v-if="product.discount"
                                    class="ml-3 bg-red-100 text-red-600 px-2 py-1 rounded text-sm font-medium">
                                    -{{ product.discount }}%
                                </span>
                            </div>
                        </div>

                        <!-- Stock -->
                        <div class="mb-6">
                            <div class="flex items-center">
                                <CheckCircleOutlined
                                    :class="product.stock > 0 ? 'text-green-500 mr-2' : 'text-red-500 mr-2'" />
                                <span class="text-gray-700">
                                    {{ product.stock > 0 ? t('In Stock') : t('Out of Stock') }}
                                    <span v-if="product.stock > 0" class="text-gray-500">({{ product.stock }}
                                        {{ t('Available') }})</span>
                                </span>
                            </div>
                        </div>

                        <!-- Shipping Info -->
                        <Row :gutter="[16, 16]" class="mb-6">
                            <Col :xs="24" :sm="8">
                            <div class="flex items-center p-3 bg-gray-100 rounded-lg">
                                <CarOutlined class="text-primary text-xl mr-2" />
                                <span class="text-sm">{{ t('Free Shipping') }}</span>
                            </div>
                            </Col>
                            <Col :xs="24" :sm="8">
                            <div class="flex items-center p-3 bg-gray-100 rounded-lg">
                                <SafetyCertificateOutlined class="text-primary text-xl mr-2" />
                                <span class="text-sm">{{ t('Secure Payment') }}</span>
                            </div>
                            </Col>
                            <Col :xs="24" :sm="8">
                            <div class="flex items-center p-3 bg-gray-100 rounded-lg">
                                <CheckCircleOutlined class="text-primary text-xl mr-2" />
                                <span class="text-sm">{{ t('Money Back') }}</span>
                            </div>
                            </Col>
                        </Row>

                        <!-- Quantity -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('Quantity') }}</label>
                            <div class="flex items-center">
                                <button @click="decreaseQuantity"
                                    class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-l-md hover:bg-gray-100">
                                    <MinusOutlined />
                                </button>
                                <input type="number" v-model="quantity" min="1" :max="product.stock"
                                    class="w-16 h-10 text-center border-t border-b border-gray-300 focus:outline-none" />
                                <button @click="increaseQuantity"
                                    class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-r-md hover:bg-gray-100">
                                    <PlusOutlined />
                                </button>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <Row :gutter="[16, 16]" class="mb-6">
                            <Col :xs="24" :sm="12">
                            <button @click="addToCart" :disabled="product.stock === 0 || isAddingToCart"
                                class="w-full bg-primary text-white py-3 px-6 rounded-md font-medium hover:bg-primary-dark transition-all duration-200 flex items-center justify-center transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:hover:bg-primary">
                                <div v-if="isAddingToCart"
                                    class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2">
                                </div>
                                <ShoppingCartOutlined v-else class="mr-2" />
                                {{ product.stock === 0 ? t('Out of Stock') : t('Add to Cart') }}
                            </button>
                            </Col>
                            <Col :xs="24" :sm="12">
                            <button @click="addToWhishlist(product.id)" :disabled="isAddingToWishlist" :class="[
                                'w-full border py-3 px-6 rounded-md font-medium transition-all duration-200 flex items-center justify-center transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed',
                                isInWishlist(product.id) ? 'border-red-500 bg-red-500 text-white hover:bg-red-600' : 'border-gray-300 hover:bg-gray-50']">
                                <div v-if="isAddingToWishlist"
                                    class="w-4 h-4 border-2 border-red-500 border-t-transparent rounded-full animate-spin mr-2">
                                </div>
                                <HeartOutlined v-else :class="isInWishlist(product.id) ? 'text-white' : 'text-gray-600'"
                                    class="mr-2" />
                                {{ isInWishlist(product.id)
                                    ? t('Remove from Wishlist')
                                    : t('Add to Wishlist')
                                }}
                            </button>
                            </Col>
                        </Row>
                    </div>
                    </Col>
                </Row>

                <!-- Description -->
                <div class="mt-12">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button class="py-4 px-6 border-b-2 border-primary font-medium text-primary">
                                {{ t('Description') }}
                            </button>
                        </nav>
                    </div>

                    <div class="py-6 prose max-w-none">
                        <p>{{ t(product.description) }}</p>

                    </div>
                </div>
            </div>
        </section>
        <section class="my-14">
            <div class="container mx-auto px-4">
                <Row >
                    <Col :xs="24" :lg="24">
                        <RelatedProducts :related-products="relatedProducts" />
                    </Col>
                </Row>
            </div>
        </section>
        <LoginModal v-model:open="isLoginModalVisible" :canResetPassword="false" />
    </UserLayout>
</template>

<style scoped></style>
