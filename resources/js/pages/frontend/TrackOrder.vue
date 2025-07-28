<script setup lang="ts">
import UserLayout from "@/layouts/UserLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Row, Col } from "ant-design-vue";
import { useForm, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { ref, computed } from "vue";
import type { PageProps as InertiaPageProps } from '@inertiajs/core';

interface Order {
    id: number;
    order_id: string;
    status: string;
    name: string;
    email: string;
    phone_number: string;
    address: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
    order_notes: string;
    subtotal_price: string;
    total_price: number;
    payment_status: string;
    payment_method: string;
    created_at: string;
    updated_at: string;
    sale_products: Array<{
        id: number;
        sale_price: string;
        qty: number;
        total_price: string;
        product: {
            id: number;
            name: string;
            thumnail_img: string;
        };
    }>;
}

interface Props {
    order?: Order;
    found?: boolean;
    order_number?: string;
}

const props = defineProps<Props>();
const page = usePage<PageProps>();

interface PageProps extends InertiaPageProps {
    flash?: {
        success?: string;
        error?: string;
    };
}

const form = useForm({
    order_number: props.order_number || '',
});

// Auto-focus input on mount
const orderInputRef = ref<HTMLInputElement>();

// Computed properties for better performance
const hasOrder = computed(() => props.found && props.order);
const orderStatusColor = computed(() =>
    props.order ? getStatusColor(props.order.status) : 'default'
);

// Optimized status color mapping
const STATUS_COLORS = {
    'pending': 'orange',
    'processing': 'blue',
    'dispatched': 'purple',
    'delivered': 'green',
    'cancelled': 'red',
    'returned': 'red',
    'refunded': 'red'
} as const;

const getStatusColor = (status: string): string => {
    return STATUS_COLORS[status?.toLowerCase() as keyof typeof STATUS_COLORS] || 'default';
};

const formatDate = (date: string): string => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};

const trackOrder = () => {

    form.post(route('track.order.post'), {
        onSuccess: () => {
        },
        onFinish: () => {
            // Reset form processing state
        }
    });
};
const formatPrice = (price: number | string) => {
    const numPrice = typeof price === 'string' ? parseFloat(price) || 0 : price;
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 2,
    }).format(numPrice);
};

</script>

<template>
    <UserLayout>

        <Head title="Track Order" />
        <section class="bg-cover bg-center py-16 sm:py-24"
            style="background-image: url('/assets/images/page-header-bg.jpg')">
            <div class="container mx-auto">
                <Row>
                    <Col :span="24" class="text-center">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-0">Track Order</h1>
                    </Col>
                </Row>
            </div>
        </section>
        <section>
            <div class="container mx-auto">
                <Row class="items-center m-5">
                    <Col :span="12">
                    <a-breadcrumb class="">
                        <a-breadcrumb-item>Home</a-breadcrumb-item>
                        <a-breadcrumb-item>Track Order</a-breadcrumb-item>
                    </a-breadcrumb>
                    </Col>
                </Row>
            </div>
        </section>
        <section>
            <div class="container mx-auto">
                <Row class="flex justify-center">
                    <Col :lg="16" :sm="22" :xs="22">


                    <!-- Search Form -->
                    <div class="bg-white rounded-lg p-4 shadow-md mb-6">
                        <h2 class="text-lg font-semibold">Track Your Order</h2>

                        <form @submit.prevent="trackOrder()">
                            <div class="flex items-center gap-2 mb-4">
                                <a-input ref="orderInputRef" type="text"
                                    placeholder="Enter your order no (e.g., ORD-123456)"
                                    v-model:value="form.order_number" class="flex-1" />
                                <a-button html-type="submit" type="primary">
                                    {{ form.processing ? 'Searching...' : 'Track Order' }}
                                </a-button>
                            </div>
                            <div v-if="form.errors.order_number" class="text-red-500 mb-4 text-sm">
                                {{ form.errors.order_number }}
                            </div>
                            <div class="text-xs text-gray-500">
                                💡 Tip Your order number is in the format ORD-XXXXXX
                            </div>
                        </form>
                    </div>
                    <!-- Flash Messages -->
                    <a-row v-if="page.props.flash?.success || page.props.flash?.error">
                        <a-col :span="24">
                            <a-result v-if="page.props.flash?.success" status="success"
                                :title="page.props.flash.success"></a-result>
                            <a-result v-if="page.props.flash?.error" status="error"
                                :title="page.props.flash.error"></a-result>
                        </a-col>
                    </a-row>

                    <!-- Order Results -->
                    <div v-if="hasOrder" class="bg-white rounded-lg p-4 shadow-md my-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Order Details</h2>
                            <a-tag :color="orderStatusColor">
                                {{ props.order?.status.toUpperCase() }}
                            </a-tag>
                        </div>

                        <!-- Order Information -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold mb-3">Order Information</h3>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="12">
                                    <p class="mb-2"><span class="font-semibold">Order ID:</span> {{
                                        props.order?.order_id }}</p>
                                    <p class="mb-2"><span class="font-semibold">Order Date:</span> {{
                                        formatDate(props.order?.created_at || '') }}</p>
                                    <p class="mb-2"><span class="font-semibold">Payment Method:</span> {{
                                        props.order?.payment_method || 'N/A' }}</p>
                                    <p class="mb-2"><span class="font-semibold">Payment Status:</span> {{
                                        props.order?.payment_status || 'N/A' }}</p>
                                </a-col>
                            </a-row>
                        </div>

                        <!-- Shipping Details -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold mb-3">Shipping Details</h3>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="12">
                                    <p class="mb-2"><span class="font-semibold">Name:</span> {{ props.order?.name }}</p>
                                    <p class="mb-2"><span class="font-semibold">Phone:</span> {{
                                        props.order?.phone_number }}</p>
                                    <p class="mb-2"><span class="font-semibold">Email:</span> {{ props.order?.email }}
                                    </p>
                                </a-col>
                                <a-col :xs="24" :sm="12">
                                    <p class="mb-2"><span class="font-semibold">Address:</span> {{ props.order?.address
                                        }}</p>
                                    <p class="mb-2"><span class="font-semibold">City:</span> {{ props.order?.city }}</p>
                                    <p class="mb-2"><span class="font-semibold">State:</span> {{ props.order?.state }}
                                    </p>
                                    <p class="mb-2"><span class="font-semibold">Postal Code:</span> {{
                                        props.order?.postal_code }}</p>
                                    <p class="mb-2"><span class="font-semibold">Country:</span> {{ props.order?.country
                                        }}</p>
                                </a-col>
                            </a-row>
                            <div v-if="props.order?.order_notes" class="mt-2">
                                <p class="mb-2"><span class="font-semibold">Order Notes:</span> {{
                                    props.order.order_notes }}</p>
                            </div>
                        </div>

                        <!-- Order Items -->
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
                                    <tr v-for="(sale, index) in props.order?.sale_products" :key="sale.id"
                                        class="border-b py-3">
                                        <td class="py-2">{{ index + 1 }}</td>
                                        <td class="py-2">
                                            <img :src="'/storage/' + sale.product.thumnail_img" :alt="sale.product.name"
                                                class="w-16 h-16 object-cover rounded" />
                                        </td>
                                        <td class="py-2">{{ sale.product.name }}</td>
                                        <td class="py-2">{{ sale.sale_price }}</td>
                                        <td class="py-2">{{ sale.qty }}</td>
                                        <td class="py-2">{{ formatPrice(sale.total_price) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Order Summary -->
                        <div class="border-gray-500 border my-4"></div>
                        <div class="my-3">
                            <h4 class="mb-2">Subtotal: <span class="font-bold text-primary">{{ formatPrice(props.order?.subtotal_price || '0') }}</span></h4>
                            <h4 class="mb-2">Shipping Charges: <span class="font-bold text-primary">Free Delivery</span>
                            </h4>
                            <h4 class="mb-2">Total Price: <span class="font-bold text-primary">{{ formatPrice(props.order?.total_price || 0) }}</span></h4>
                        </div>
                    </div>
                    </Col>
                </Row>
            </div>
        </section>
    </UserLayout>
</template>
