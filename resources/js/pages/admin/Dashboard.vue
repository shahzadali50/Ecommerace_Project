<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import SaleManagement from '@/components/admin/SaleManagement.vue';
import {  DollarCircleOutlined, ShoppingCartOutlined, BranchesOutlined, CiOutlined } from '@ant-design/icons-vue';
import DashboardCard from '@/components/admin/DashboardCard.vue';
import dayjs from 'dayjs';
import isBetween from 'dayjs/plugin/isBetween';
import OrderManagement from '@/components/admin/OrderManagement.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

dayjs.extend(isBetween);

interface Order {
    id: number;
    total_price: string | number;
    created_at: string;
}
interface Props {
    brands: number;
    totalProduct: number;
    category: number;
    orders: Order[];
}

const props = withDefaults(defineProps<Props>(), {
    brands: 0,
    totalProduct: 0,
    category: 0,
    orders: () => [],
});

interface Translations {
    dashboardMainPage?: {
        welcome_to_dashboard?: string;
        latest_products?: string;
        total_sale?: string;
        total_orders?: string;
        brands?: string;
        products?: string;
        stock?: string;
    };
}

const page = usePage();
const translations = computed(() => {
    return (page.props.translations as Translations)?.dashboardMainPage || {};
});

const totalSales = computed(() => {
    return props.orders.reduce((sum: number, order: Order) => sum + Number(order.total_price), 0);
});

const totalOrders = computed(() => props.orders.length);
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard" />
        <div class="mb-5" style="background-color: #ececec; padding: 20px">
            <a-row :gutter="[16, 16]">
                <a-col :lg="24" :md="24" :sm="24" :xs="24">
                    <h2 class="text-2xl">{{ translations.welcome_to_dashboard || 'Welcome to Dashboard' }}</h2>
                </a-col>
                <a-col :lg="6" :sm="12" :xs="24">
                    <DashboardCard
                        :title="translations.total_sale || 'Total Sale'"
                        :value="totalSales"
                        :icon="DollarCircleOutlined"
                        bgColor="bg-yellow-800"
                    />
                </a-col>
                <a-col :lg="6" :sm="12" :xs="24">
                    <DashboardCard
                        :title="translations.total_orders || 'Total Orders'"
                        :value="totalOrders"
                        :icon="ShoppingCartOutlined"
                        bgColor="bg-green-700"
                    />
                </a-col>
                <a-col :lg="6" :sm="12" :xs="24">
                    <DashboardCard
                        :title="translations.brands || 'Brands'"
                        :value="props.brands"
                        :icon="BranchesOutlined"
                        bgColor="bg-sky-900"
                    />
                </a-col>
                <a-col :lg="6" :sm="12" :xs="24">
                    <DashboardCard
                        :title="translations.products || 'Products'"
                        :value="props.totalProduct"
                        :icon="CiOutlined"
                        bgColor="bg-red-700"
                    />
                </a-col>
            </a-row>
        </div>

        <SaleManagement :orders="props.orders" />
        <OrderManagement :orders="props.orders" />

    </AdminLayout>
</template>
