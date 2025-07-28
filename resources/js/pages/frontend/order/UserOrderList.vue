<script setup lang="ts">
import UserLayout from "@/layouts/UserLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Row, Col } from "ant-design-vue";
import UserAccountSidebar from "@/components/frontend/UserAccountSidebar.vue";
import dayjs from "dayjs";
import { ref } from "vue";
import { EyeOutlined } from '@ant-design/icons-vue';
import { usePage } from "@inertiajs/vue3";

import type { PageProps as InertiaPageProps } from '@inertiajs/core';

const formatDate = (date: string) => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};
const page = usePage<PageProps>();
interface PageProps extends InertiaPageProps {
    flash?: {
        success?: string;
        error?: string;
    };
}

const columns = [
    { title: 'ID', dataIndex: 'id', key: 'id' },
    { title: 'Order ID', dataIndex: 'order_id', key: 'order_id' },
    { title: 'Status', dataIndex: 'status', key: 'status' },
    { title: 'Total Price', dataIndex: 'total_price', key: 'total_price' },
    { title: 'Created At', dataIndex: 'created_at', key: 'created_at' },
    { title: 'Action', dataIndex: 'action', key: 'action' },
];

defineProps({
    orders: Object,
});
const isOrderViewModalVisible = ref(false);
const selectedOrder = ref<any>(null);

const openOrderView = (order: any) => {
    selectedOrder.value = order;
    isOrderViewModalVisible.value = true;
};

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'pending':
            return 'orange';
        case 'processing':
            return 'blue';
        case 'completed':
            return 'green';
        case 'cancelled':
            return 'red';
        default:
            return 'default';
    }
};

</script>

<template>
    <UserLayout>

        <Head title="User Account" />
        <section class="bg-cover bg-center py-16 sm:py-24"
            style="background-image: url('/assets/images/page-header-bg.jpg')">
            <div class="container mx-auto">
                <Row>
                    <Col :span="24" class="text-center">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-0">User Account</h1>
                    </Col>
                </Row>
            </div>
        </section>
        <section>
            <div class="container mx-auto">
                <Row :gutter="[16, 16]">
                    <!-- Sidebar -->
                    <Col :xs="24" :sm="24" :md="8" :lg="6" :xl="6">
                    <UserAccountSidebar />

                    </Col>

                    <!-- Main Content -->
                    <Col :xs="24" :sm="24" :md="16" :lg="18" :xl="18">
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 overflow-x-auto">
                        <a-row>
                            <a-col :span="24">
                                <a-result v-if="page.props.flash?.success" status="success"
                                    :title="page.props.flash.success"></a-result>

                            </a-col>
                            <a-col :span="24">
                                <div class="responsive-table">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h2 class="text-lg font-semibold">Order List</h2>
                                    </div>

                                    <a-table v-if="orders" :columns="columns" :data-source="orders.data" rowKey="id">
                                        <template #bodyCell="{ column, record, index }">
                                            <template v-if="column.dataIndex === 'id'">
                                                {{ index + 1 }}
                                            </template>
                                            <template v-else-if="column.dataIndex === 'order_id'">
                                                <a-badge :count="record.order_id"
                                                    :number-style="{ backgroundColor: '#1890ff' }" />
                                            </template>

                                            <template v-else-if="column.dataIndex === 'status'">
                                                <a-tag :color="getStatusColor(record.status)">
                                                    {{ record.status }}
                                                </a-tag>
                                            </template>
                                            <template v-else-if="column.dataIndex === 'total_price'">
                                                {{ Math.floor(record.total_price) }}
                                            </template>
                                            <template v-else-if="column.dataIndex === 'created_at'">
                                                {{ formatDate(record.created_at) }}
                                            </template>
                                            <template v-else-if="column.dataIndex === 'action'">
                                                <a-tooltip placement="top">
                                                    <template #title>Order View</template>
                                                    <a-button type="link" @click="openOrderView(record)">
                                                        <EyeOutlined style="font-size: 20px;" />
                                                    </a-button>
                                                </a-tooltip>
                                            </template>
                                        </template>
                                    </a-table>

                                </div>
                            </a-col>
                        </a-row>
                        <!-- Edit Product Modal -->
                        <a-modal width="700px" v-model:open="isOrderViewModalVisible" title="Order Preview"
                            @cancel="isOrderViewModalVisible = false" :footer="null">
                            <a-row>
                                <a-col :xs="24">
                                    <div class="mb-4">
                                        <h3 class="text-lg font-semibold mb-3">Shipping Details</h3>
                                        <a-row :gutter="16">
                                            <a-col :xs="24" :sm="12">
                                                <p class="mb-2"><span class="font-semibold">Name:</span> {{
                                                    selectedOrder.name }}</p>
                                                <p class="mb-2"><span class="font-semibold">Phone:</span> {{
                                                    selectedOrder.phone_number
                                                    }}</p>
                                                <p class="mb-2"><span class="font-semibold">Email:</span> {{
                                                    selectedOrder.email }}</p>
                                            </a-col>
                                            <a-col :xs="24" :sm="12">
                                                <p class="mb-2"><span class="font-semibold">Address:</span> {{
                                                    selectedOrder.address }}
                                                </p>
                                                <p class="mb-2"><span class="font-semibold">City:</span> {{
                                                    selectedOrder.city }}</p>
                                                <p class="mb-2"><span class="font-semibold">State:</span> {{
                                                    selectedOrder.state }}</p>
                                                <p class="mb-2"><span class="font-semibold">Postal Code:</span> {{
                                                    selectedOrder.postal_code }}
                                                </p>
                                                <p class="mb-2"><span class="font-semibold">Country:</span> {{
                                                    selectedOrder.country }}
                                                </p>
                                            </a-col>
                                        </a-row>
                                        <div v-if="selectedOrder.order_notes" class="mt-2">
                                            <p class="mb-2"><span class="font-semibold">Order Notes:</span> {{
                                                selectedOrder.order_notes
                                                }}</p>
                                        </div>
                                    </div>

                                </a-col>
                                <a-col :xs="24">
                                    <div class="mb-2 overflow-x-auto">
                                        <div class="border-gray-500 border my-4"></div>
                                        <table class="table-auto w-full min-w-[600px]">
                                            <thead>
                                                <tr class="text-left">
                                                    <th>Sr</th>
                                                    <th>Image</th>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>QTY</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(sale, index) in selectedOrder?.sale_products" :key="sale.id"
                                                    class="border-b py-3">
                                                    <td class="py-2">{{ index + 1 }}</td>
                                                    <td class="py-2">
                                                        <img :src="'/storage/' + sale.product.thumnail_img"
                                                            :alt="sale.product.name"
                                                            class="w-16 h-16 object-cover rounded" />
                                                    </td>
                                                    <td class="py-2">{{ sale.product.name }}</td>
                                                    <td class="py-2">{{ sale.sale_price }}</td>
                                                    <td class="py-2">{{ sale.qty }}</td>
                                                    <td class="py-2">{{ sale.total_price }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </a-col>
                                <a-col :xs="24">
                                    <div class="border-gray-500 border my-4"></div>
                                    <div class="my-3">
                                        <h4 class="mb-2">Subtotal: <span class="font-bold text-primary">{{
                                            selectedOrder?.subtotal_price
                                                }}</span></h4>
                                        <h4 class="mb-2">Shipping Charges: <span class="font-bold text-primary">Free
                                                Delivery</span>
                                        </h4>
                                        <h4 class="mb-2">Total Price: <span class="font-bold text-primary">{{
                                                selectedOrder?.total_price}}</span></h4>
                                    </div>
                                </a-col>
                            </a-row>
                        </a-modal>
                    </div>
                    </Col>
                </Row>
            </div>
        </section>

    </UserLayout>
</template>
