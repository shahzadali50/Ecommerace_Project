<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import dayjs from "dayjs";
import { ref, computed } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import OrderStatusBtn from '@/components/admin/order/OrderStatusBtn.vue';

DataTable.use(DataTablesCore);

const isLoading = ref(false);

const page = usePage();
const translations = computed(() => {
    return (page.props.translations as any)?.dashboard_all_pages || {};
});

const formatDate = (date: string) => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};

defineProps({
    orders: Object,
});

const isViewModal = ref(false);
const isStatusModal = ref(false);
const order = ref<any>(null);
const status = ref('');

const statuses = [
    { value: 'pending', label: 'Pending', icon: 'fa fa-clock-o' },
    { value: 'processing', label: 'Processing', icon: 'fa-cog' },
    { value: 'dispatched', label: 'Dispatched', icon: 'fa-truck' },
    { value: 'delivered', label: 'Delivered', icon: 'fa-check-circle' },
    { value: 'cancelled', label: 'Cancelled', icon: 'fa-times-circle' },
    { value: 'returned', label: 'Returned', icon: 'fa-undo' },
    { value: 'refunded', label: 'Refunded', icon: 'fa fa-retweet' }
];

const openView = (data: any) => {
    order.value = data;
    isViewModal.value = true;
};

// DataTable columns for orders
const dataTableColumns = [
    {
        title: translations.value.sr || 'Sr.',
        data: null,
        render: (data: any, type: any, row: any, meta: any) => meta.row + 1,
    },
    {
        title: translations.value.order_id || 'Order ID',
        data: 'order_id',
        render: (data: string) => `<span class="badge bg-secondary">${data}</span>`,
    },
    {
        title: translations.value.status || 'Status',
        data: 'status',
        render: (data: any, type: any, row: any) => {
            let btnClass = 'btn-secondary';
            switch (data.toLowerCase()) {
                case 'pending':
                    btnClass = 'btn-warning';
                    break;
                case 'processing':
                    btnClass = 'btn-info';
                    break;
                case 'dispatched':
                    btnClass = 'btn-primary';
                    break;
                case 'delivered':
                    btnClass = 'btn-success';
                    break;
                case 'cancelled':
                    btnClass = 'btn-danger';
                    break;
                case 'returned':
                    btnClass = 'btn-secondary';
                    break;
                case 'refunded':
                  btnClass = 'btn-dark';
                    break;
            }
            return `
                <button class="btn-order-status ${btnClass} p-2" data-id="${row.id}">
                    ${data}
                </button>
            `;
        }
    },
    {
    title: translations.value.payment_status || 'Payment Status',
    data: 'payment_status',
    render: (data: string) => `<span class="badge bg-success">${data || 'N/A'}</span>`,
    },
    {
        title: translations.value.payment_method || 'Payment Method',
        data: 'payment_method',
        render: (data: string) => data || 'N/A',
    },
    {
        title: translations.value.payment_intent_id || 'payment_intent_id',
        data: 'payment_intent_id',
        render: (data: string) => data || 'N/A',
    },

    {
        title: translations.value.customer_name || 'Customer Name',
        data: 'name'
    },
    {
        title: translations.value.phone_number || 'Phone No',
        data: 'phone_number'
    },
    {
        title: translations.value.subtotal || 'SubTotal',
        data: 'subtotal_price',
        render: (data: number) => Math.floor(data)
    },
    {
        title: translations.value.total || 'Total',
        data: 'total_price',
        render: (data: number) => Math.floor(data)
    },
    {
        title: translations.value.created_at || 'Created At',
        data: 'created_at',
        render: (data: string) => formatDate(data)
    },
    {
        title: translations.value.action || 'Action',
        data: null,
        orderable: false,
        render: (data: any, type: any, row: any) => `
            <button class="view-btn p-2" data-id="${row.id}" title="${translations.value.view || 'View'}">
                 <i class="fa fa-cart-plus text-2xl" aria-hidden="true"></i>
            </button>
        `
    }
];

// DataTable options
const options = {
    paging: true,
    searching: true,
    ordering: true,
    responsive: true,
    pageLength: 10,
    createdRow: (row: HTMLElement, data: any) => {
        setTimeout(() => {
            row.querySelector('.view-btn')?.addEventListener('click', () => openView(data));
            row.querySelector('.btn-order-status')?.addEventListener('click', () => openStatus(data));
        }, 0);
    },
};

const form = useForm({
    id: null,
    status: '',
    _method: 'PUT',
});

const openStatus = (data: any) => {
    form.id = data.id;
    order.value = data;
    status.value = data.status;
    isStatusModal.value = true;
};

const updateStatus = () => {
    isLoading.value = true;
    form.status = status.value;
    form.post(route('admin.order.status.update', { id: form.id }), {
        preserveScroll: true,
        onSuccess: () => {
            isStatusModal.value = false;
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <AdminLayout>

        <Head :title="translations.order_list || 'Order List'" />
        <a-row>

            <a-col :span="24" class="mb-2">
                <OrderStatusBtn />
            </a-col>

        </a-row>
        <a-row>
            <a-col :xs="24">

                <div class="bg-white rounded-lg responsive-table p-4 shadow-md">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold mb-4">{{ translations.order_list || 'Order List' }}</h2>
                    </div>
                    <DataTable v-if="orders?.data" :data="orders.data" :columns="dataTableColumns" :options="options"
                        class="display">
                        <thead>
                            <!-- <tr>
                                <th>{{ translations.sr || 'Sr.' }}</th>
                                <th>{{ translations.order_id || 'Order ID' }}</th>
                                <th>{{ translations.status || 'Status' }}</th>
                                <th>{{ translations.customer_name || 'Customer Name' }}</th>
                                <th>{{ translations.phone || 'Phone No' }}</th>
                                <th>{{ translations.subtotal || 'SubTotal' }}</th>
                                <th>{{ translations.total || 'Total' }}</th>
                                <th>{{ translations.created_at || 'Created At' }}</th>
                                <th>{{ translations.action || 'Action' }}</th>
                            </tr> -->
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this automatically -->
                        </tbody>
                    </DataTable>
                </div>
            </a-col>
        </a-row>

        <!-- Order View Modal -->
        <a-modal width="700px" v-model:open="isStatusModal" title="Change Order Status" @cancel="isStatusModal = false"
            :footer="null">
            <form @submit.prevent="updateStatus">
                <a-row>
                    <a-col :xs="24">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold mb-3">Order #{{ order?.order_id }}</h3>
                            <div class="mb-4">
                                <label class="block">{{ translations.status || 'Status' }}</label>
                                <a-select v-model:value="status" style="width: 200px">
                                    <a-select-option v-for="status in statuses" :key="status.value"
                                        :value="status.value">
                                        <i :class="['fa', status.icon, 'mr-2']"></i>
                                        {{ status.label }}
                                    </a-select-option>
                                </a-select>
                                <div v-if="form.errors.status" class="text-red-500">
                                    {{ form.errors.status }}
                                </div>
                            </div>
                            <div class="text-right">

                                <a-button type="primary" html-type="submit" :loading="isLoading" class="ml-2">
                                    {{ translations.update_status || 'Update Status' }}
                                </a-button>
                            </div>
                        </div>
                    </a-col>
                </a-row>
            </form>
        </a-modal>
        <a-modal width="700px" v-model:open="isViewModal" :title="translations.order_preview || 'Order Preview'"
            @cancel="isViewModal = false" :footer="null">
            <a-row>
                <a-col :xs="24">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold mb-3">{{ translations.shiping_details || 'Shipping Details' }}
                        </h3>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="12">
                                <p class="mb-2"><span class="font-semibold">{{ translations.name || 'Name' }}:</span> {{
                                    order?.name }}</p>
                                <p class="mb-2"><span class="font-semibold">{{ translations.phone_number || 'Phone'
                                }}:</span> {{ order?.phone_number }}</p>
                                <p class="mb-2"><span class="font-semibold">{{ translations.email || 'Email' }}:</span>
                                    {{ order?.email }}</p>
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <p class="mb-2"><span class="font-semibold">{{ translations.address || 'Address'
                                }}:</span> {{ order?.address }}</p>
                                <p class="mb-2"><span class="font-semibold">{{ translations.city || 'City' }}:</span> {{
                                    order?.city }}</p>
                                <p class="mb-2"><span class="font-semibold">{{ translations.state || 'State' }}:</span>
                                    {{ order?.state }}</p>
                                <p class="mb-2"><span class="font-semibold">{{ translations.postal_code || 'Postal Code'
                                }}:</span> {{ order?.postal_code }}</p>
                                <p class="mb-2"><span class="font-semibold">{{ translations.country || 'Country'
                                }}:</span> {{ order?.country }}</p>
                            </a-col>
                        </a-row>
                        <div v-if="order?.order_notes" class="mt-2">
                            <p class="mb-2"><span class="font-semibold">{{ translations.order_notes || 'Order Notes'
                            }}:</span> {{ order.order_notes }}</p>
                        </div>
                    </div>
                </a-col>
                <a-col :xs="24">
                    <div class="mb-2 overflow-x-auto">
                        <div class="border-gray-500 border my-4"></div>
                        <table class="table-auto w-full min-w-[600px]">
                            <thead>
                                <tr class="text-left">
                                    <th>{{ translations.sr || 'Sr' }}</th>
                                    <th>{{ translations.image || 'Image' }}</th>
                                    <th>{{ translations.product || 'Product' }}</th>
                                    <th>{{ translations.price || 'Price' }}</th>
                                    <th>{{ translations.quantity || 'QTY' }}</th>
                                    <th>{{ translations.total || 'Total' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(sale, index) in order?.sale_products" :key="sale.id" class="border-b py-3">
                                    <td class="py-2">{{ index + 1 }}</td>
                                    <td class="py-2">
                                        <img :src="'/storage/' + sale.product.thumnail_img" :alt="sale.product.name"
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
                        <h4 class="mb-2">{{ translations.subtotal || 'Subtotal' }}: <span
                                class="font-bold text-primary">{{ order?.subtotal_price }}</span></h4>
                        <h4 class="mb-2">{{ translations.shipping_charge || 'Shipping Charges' }}: <span
                                class="font-bold text-primary">{{ translations.free_delivery || 'Free Delivery'
                                }}</span></h4>
                        <h4 class="mb-2">{{ translations.total_price || 'Total Price' }}: <span
                                class="font-bold text-primary">{{ order?.total_price }}</span></h4>
                    </div>
                </a-col>
            </a-row>
        </a-modal>
    </AdminLayout>
</template>

<style scoped></style>
