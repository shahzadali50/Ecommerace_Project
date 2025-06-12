<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import OrderList from '@/components/admin/order/OrderList.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from "vue";
const page = usePage();
const translations = computed(() => {
    return (page.props.translations as any)?.dashboard_all_pages || {};
});

 defineProps({
    orders: Object
});
</script>

<template>
    <AdminLayout>
        <Head :title="translations.order_list || 'Order List'" />
        <OrderList :orders="orders" />
    </AdminLayout>
</template>
