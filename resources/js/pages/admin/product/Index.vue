<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Modal } from 'ant-design-vue';
import dayjs from "dayjs";
import { ref, computed } from 'vue';
import AddProduct from '@/components/admin/product/AddProduct.vue';
import EditProduct from '@/components/admin/product/EditProduct.vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';

DataTable.use(DataTablesCore);

// Props Definition
defineProps<{
    products: { data: Array<any> };
    brands: { data: Array<any> };
    categories: { data: Array<any> };
}>();

// State Management
const isLoading = ref(false);
const isAddProductModalVisible = ref(false);
const isEditModalVisible = ref(false);
const isImagePreviewModalVisible = ref(false);
const previewImageUrl = ref('');
const selectedProduct = ref<any>(null);

// Page and Translations
const page = usePage();
const translations = computed(() => {
    return (page.props.translations as any)?.dashboard_all_pages || {};
});

// Format date
const formatDate = (date: string) => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};

// DataTable columns for products
const dataTableColumns = [
    {
        title: translations.value.sr || 'Sr.',
        data: null,
        render: (data: any, type: any, row: any, meta: any) => meta.row + 1,
    },
    {
        title: translations.value.image || 'Image',
        data: 'thumnail_img',
        orderable: false,
        render: (data: string) =>
            data
                ? `<img src="/storage/${data}" class="w-12 h-12 object-cover rounded mb-1 cursor-pointer" style="display:inline-block;" />`
                : '<span class="text-gray-400 mb-1">No Image</span>',
    },
    { title: translations.value.name || 'Name', data: 'name' },
    { title: translations.value.stock || 'Stock', data: 'stock' },
    { title: translations.value.purchase_price || 'Purchase Price', data: 'purchase_price' },
    { title: translations.value.sale_price || 'Sale Price', data: 'sale_price' },
    {
        title: translations.value.discount || 'Discount',
        data: 'discount',
        render: (data: number) => data ? `${data}%` : '0%'
    },
    { title: translations.value.final_price || 'Final Price', data: 'final_price' },
    { title: translations.value.category || 'Category', data: 'category_name' },
    { title: translations.value.brand || 'Brand', data: 'brand_name' },
    {
        title: translations.value.created_at || 'Created At',
        data: 'created_at',
        render: (data: string) => formatDate(data),
    },
    {
        title: translations.value.action || 'Action',
        data: null,
        orderable: false,
        render: (data: any, type: any, row: any) => `
            <button class="edit-btn p-2" data-id="${row.id}" title="${translations.value.edit || 'Edit'}">
                <i class="fa fa-pencil-square-o text-green-500"></i>
            </button>
            <button class="delete-btn p-2" data-id="${row.id}" title="${translations.value.delete || 'Delete'}">
                <i class="fa fa-trash text-red-500"></i>
            </button>
        `,
    },
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
            row.querySelector('.edit-btn')?.addEventListener('click', () => openEditModal(data));
            row.querySelector('.delete-btn')?.addEventListener('click', () => deleteProduct(data.id));
            row.querySelector('img')?.addEventListener('click', () => {
                if (data.thumnail_img) openImagePreview(data.thumnail_img);
            });
        }, 0);
    },
};

// Event Handlers
const deleteProduct = (id: number) => {
    Modal.confirm({
        title: translations.value.confirm_delete_title || 'Are you sure you want to delete',
        content: translations.value.delete_product_warning || 'This action cannot be undone.',
        okText: translations.value.confirm_delete_ok || 'Yes, Delete',
        okType: 'danger',
        cancelText: translations.value.confirm_delete_cancel || 'Cancel',
        onOk() {
            isLoading.value = true;
            useForm({}).delete(route('admin.product.delete', { id }), {
                onSuccess: () => {
                    // Optional: Add success notification
                },
                onFinish: () => {
                    isLoading.value = false;
                },
            });
        },
    });
};

const openEditModal = (product: any) => {
    selectedProduct.value = product;
    isEditModalVisible.value = true;
};

const openImagePreview = (imagePath: string) => {
    previewImageUrl.value = '/storage/' + imagePath;
    isImagePreviewModalVisible.value = true;
};
</script>

<template>
    <div v-if="isLoading" class="loading-overlay">
        <a-spin size="large" />
    </div>
    <AdminLayout>
        <Head :title="translations.product_title || 'Product List'" />
        <a-row>
            <a-col :span="24">
                <div class="bg-white rounded-lg p-4 shadow-md responsive-table">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ translations.product_title || 'Product List' }}</h2>
                        <div>
                            <a-button @click="isAddProductModalVisible = true" type="default">
                                {{ translations.add_product || 'Add Product' }}
                            </a-button>
                            <Link :href="route('admin.product-log')">
                                <a-button type="default">
                                    {{ translations.product_logs || 'Product Logs' }}
                                </a-button>
                            </Link>
                        </div>
                    </div>
                    <DataTable
                        v-if="products?.data"
                        :data="products.data"
                        :columns="dataTableColumns"
                        :options="options"
                        class="display"
                    >
                        <thead>
                            <tr>
                                <th>{{ translations.sr || 'Sr.' }}</th>
                                <th>{{ translations.image || 'Image' }}</th>
                                <th>{{ translations.name || 'Name' }}</th>
                                <th>{{ translations.stock || 'Stock' }}</th>
                                <th>{{ translations.purchase_price || 'Purchase Price' }}</th>
                                <th>{{ translations.sale_price || 'Sale Price' }}</th>
                                <th>{{ translations.discount || 'Discount' }}</th>
                                <th>{{ translations.final_price || 'Final Price' }}</th>
                                <th>{{ translations.category || 'Category' }}</th>
                                <th>{{ translations.brand || 'Brand' }}</th>
                                <th>{{ translations.created_at || 'Created At' }}</th>
                                <th>{{ translations.action || 'Action' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this automatically -->
                        </tbody>
                    </DataTable>
                </div>
            </a-col>
        </a-row>

        <AddProduct
            :is-visible="isAddProductModalVisible"
            :categories="categories.data"
            :brands="brands.data"
            :translations="translations"
            @update:is-visible="isAddProductModalVisible = $event"
            @success="isAddProductModalVisible = false"
        />

        <EditProduct
            :is-visible="isEditModalVisible"
            :product="selectedProduct"
            :categories="categories"
            :brands="brands"
            :translations="translations"
            @update:is-visible="isEditModalVisible = $event"
            @success="isEditModalVisible = false"
        />

        <!-- Image Preview Modal -->
        <a-modal
            v-model:open="isImagePreviewModalVisible"
            :title="translations.preview || 'Image Preview'"
            @cancel="isImagePreviewModalVisible = false"
            :footer="null"
            width="600px"
        >
            <div class="flex justify-center p-4">
                <img :src="previewImageUrl" alt="Full Size Image" class="max-w-full max-h-[500px] object-cover" />
            </div>
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.display {
    width: 100%;
}
</style>
