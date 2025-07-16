<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import OrderList from '@/components/admin/order/OrderList.vue';
import { Head } from '@inertiajs/vue3';
import { getCurrentInstance } from 'vue';

const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

defineProps({
    orders: Object
});
</script>

<template>
    <AdminLayout>
        <Head :title="t('Order List')" />
        <OrderList :orders="orders" />
    </AdminLayout>
</template>
