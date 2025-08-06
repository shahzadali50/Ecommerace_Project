<script setup lang="ts">
import { Head, usePage, Link } from '@inertiajs/vue3';
import UserLayout from '@/layouts/UserLayout.vue';
import ProductSection from '@/components/frontend/ProductSection.vue';
import { computed, ref, getCurrentInstance } from 'vue';
import { Row, Col } from 'ant-design-vue';
import { HeartOutlined } from '@ant-design/icons-vue';
import LoginModal from "@/components/frontend/LoginModal.vue";

const page = usePage();

// 👇 get access to global $t
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

const products = computed(() => (page.props.products as { data: any[] })?.data || []);
const isLoginModalVisible = ref(false);
const isAuthenticated = computed(() => (page.props.auth as any)?.user);
</script>

<template>
    <UserLayout>

        <Head :title="t('Wishlist')" />
        <!-- Page Header -->
        <section class="bg-cover bg-center py-16 sm:py-24"
            style="background-image: url('/assets/images/page-header-bg.jpg');">
            <div class="container mx-auto">
                <Row>   
                    <Col :span="24" class="text-center">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-0">{{ t('Wishlist Products') }}</h1>
                    </Col>
                </Row>
            </div>
        </section>

        <!-- Breadcrumb -->
        <section>
            <div class="container mx-auto">
                <Row class="items-center m-5">
                    <Col :span="12">
                    <a-breadcrumb>
                        <a-breadcrumb-item>{{ t('Home') }}</a-breadcrumb-item>
                        <a-breadcrumb-item>{{ t('Wishlist') }}</a-breadcrumb-item>
                    </a-breadcrumb>
                    </Col>
                </Row>
            </div>
        </section>
        <div v-if="isAuthenticated">
            <!-- Products or Empty -->
            <div v-if="products.length > 0">
                <ProductSection :showPagination="true" :showFilter="false" sectionClass="py-4" :title="t('Your Wishlist')"
                    :subtitle="t('Products you saved for later.')" />
            </div>
            <div v-else>
                <a-result :title="t('This wishlist is empty.')"
                    :sub-title="t('You don\'t have any products in the wishlist yet. You will find a lot of interesting products on our Products page.')">
                    <template #icon>
                        <HeartOutlined style="color: #999999;" />
                    </template>
                    <template #extra>
                        <button class="btn btn-primary" size="large" >
                            <Link :href="route('all.products')" class="hover:text-white">{{ t('Return to Products') }}</Link>
                        </button>
                    </template>
                </a-result>
            </div>
        </div>
        <div v-else>

            <a-result :title="t('Please login to view your wishlist.')"
                :sub-title="t('You need to be logged in to access your wishlist.')">
                <template #icon>
                    <HeartOutlined style="color: #999999;" />
                </template>
                <template #extra>
                    <div>
                        <button class="btn btn-primary items-center" size="large"
                            @click="isLoginModalVisible = true">
                            {{ t('Login') }}
                        </button>
                    </div>

                </template>
            </a-result>


        </div>
        <LoginModal v-model:open="isLoginModalVisible" :canResetPassword="false" />




    </UserLayout>
</template>
