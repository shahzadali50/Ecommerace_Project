<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import SaleManagement from '@/components/admin/SaleManagement.vue';
import {  DollarCircleOutlined, ShoppingCartOutlined, BranchesOutlined, CiOutlined } from '@ant-design/icons-vue';
import DashboardCard from '@/components/admin/DashboardCard.vue';
import dayjs from 'dayjs';
import isBetween from 'dayjs/plugin/isBetween';
import OrderManagement from '@/components/admin/OrderManagement.vue';
import { getCurrentInstance, computed } from 'vue';

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

const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

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
                    <h2 class="text-2xl">{{ t('Welcome to Dashboard') }}</h2>
                </a-col>
                <a-col :lg="6" :sm="12" :xs="24">
                    <DashboardCard
                        :title="t('Total Sale')"
                        :value="totalSales"
                        :icon="DollarCircleOutlined"
                        bgColor="bg-yellow-800"
                    />
                </a-col>
                <a-col :lg="6" :sm="12" :xs="24">
                    <DashboardCard
                        :title="t('Total Orders')"
                        :value="totalOrders"
                        :icon="ShoppingCartOutlined"
                        bgColor="bg-green-700"
                    />
                </a-col>
                <a-col :lg="6" :sm="12" :xs="24">
                    <DashboardCard
                        :title="t('Brands')"
                        :value="props.brands"
                        :icon="BranchesOutlined"
                        bgColor="bg-sky-900"
                    />
                </a-col>
                <a-col :lg="6" :sm="12" :xs="24">
                    <DashboardCard
                        :title="t('Products')"
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
