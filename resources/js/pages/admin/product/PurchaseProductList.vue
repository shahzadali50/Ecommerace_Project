<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link , useForm} from '@inertiajs/vue3';
import { Modal } from 'ant-design-vue';
import dayjs from "dayjs";
import { ref, computed } from "vue";
const isLoading = ref(false);

// Format date function
const formatDate = (date: string) => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};

// Define table columns
const columns = [
    { title: 'ID', dataIndex: 'id', key: 'id' },
    { title: 'Name', dataIndex: 'name', key: 'name' },
    { title: 'Purchase Price', dataIndex: 'purchase_price', key: 'purchase_price' },
    { title: 'Sale Price', dataIndex: 'sale_Price', key: 'sale_Price' },
    { title: 'Stock', dataIndex: 'stock', key: 'stock' },
    { title: 'Created At', dataIndex: 'created_at', key: 'created_at' },
    { title: 'Action', dataIndex: 'action', key: 'action' },
];

// Receive brands as a prop from Inertia
const props = defineProps({
    purchaseProducts: Object,
    products: Array, // Receive product list from Laravel
});
const productOptions = computed(() => {
    return props.products?.map((product: any) => ({
        value: product.id,
        label: product.name
    })) || [];
});

// Search filter function
const filterOption = (input: string, option: any) => {
    return option.label.toLowerCase().includes(input.toLowerCase());
};
const form = useForm({});
const deletePurchaseProduct = (id: number) => {
    Modal.confirm({
        title: 'Are you sure you want to delete this purchase?',
        content: 'This action cannot be undone.',
        okText: 'Yes, Delete',
        okType: 'danger',
        cancelText: 'Cancel',
        onOk() {
            isLoading.value = true;
            form.delete(route('admin.purchase.product.delete', id), {
                onSuccess: () => {

                },
                onFinish: () => {
                isLoading.value = false;
                }
            });
        },
    });
};
const isPurchaseProductModalVisible = ref(false);
const isEditModalVisible = ref(false);
const purchaseProductForm = useForm({
    product_id: null, // Will hold selected product
    purchase_price: "",
    sale_price: "",
    stock: "",
    description: "",
});
const purchaseProductEditForm  = useForm({
    id: null,
    purchase_price: '',
    sale_price: '',
    stock: '',
    description: '',
});
const openPurchaseDetailModal  =()=>{

    isPurchaseProductModalVisible.value = true;
}
const savePurchaseProduct = () => {
    isLoading.value = true;
    purchaseProductForm.post(route("admin.purchase.product.detail.store"), {
        onSuccess: () => {
            isPurchaseProductModalVisible.value = false;
            purchaseProductForm.reset();
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
const showEditPurchaseModal  =(purchaseProduct:any)=>{
    purchaseProductEditForm.id = purchaseProduct.id;
    purchaseProductEditForm.purchase_price = purchaseProduct.purchase_price;
    purchaseProductEditForm.sale_price = purchaseProduct.sale_price;
    purchaseProductEditForm.description = purchaseProduct.description;
    purchaseProductEditForm.stock = purchaseProduct.stock;
    isEditModalVisible.value = true;
}
// Update brand
const updatePurchaseDetails = () => {
    isLoading.value = true;
    purchaseProductEditForm .put(route('admin.purchase.product.update', purchaseProductEditForm .id), {
        onSuccess: () => {
            isEditModalVisible.value = false;
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

</script>
<template>
      <div v-if="isLoading" class="loading-overlay" >
        <a-spin size="large" />
    </div>
    <AdminLayout>
        <Head title="Purchase Product List" />
        <a-row>
            <a-col :span="24">
                <div class="bg-white rounded-lg p-4 shadow-md responsive-table">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Purchase Product List</h2>

                        <div>
                            <a-button class="mx-2" type="default" @click="openPurchaseDetailModal()">Add Purchase Detail</a-button>
                            <Link :href="route('admin.products')">
                            <a-button class="mx-2" type="default">Back</a-button>
                        </Link>
                        <Link :href="route('admin.purchase.product.log')" >
                            <a-button class="mx-2" type="default">purchase products logs</a-button>
                        </Link>
                        </div>
                    </div>
                    <!-- Display table -->
                    <a-table v-if="purchaseProducts" :columns="columns" :data-source="purchaseProducts.data" rowKey="id" bordered>
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.dataIndex === 'id'">
                                {{ index + 1 }}
                            </template>
                            <template v-if="column.dataIndex === 'name'">
                                {{ record.product.name}}
                            </template>
                            <template v-if="column.dataIndex === 'purchase_price'">
                                {{ record.purchase_price}}
                            </template>
                            <template v-if="column.dataIndex === 'sale_Price'">
                                {{ record.sale_price}}
                            </template>
                            <template v-if="column.dataIndex === 'stock'">
                                {{ record.remaining_stock}}
                            </template>

                            <template v-else-if="column.dataIndex === 'created_at'">
                                {{ formatDate(record.created_at) }}
                            </template>
                            <template v-else-if="column.dataIndex === 'action'">
                            <a-tooltip placement="top">
                                    <template #title>Delete</template>
                                    <a-button type="link"  @click="deletePurchaseProduct(record.id)"><i class="fa fa-trash text-red-500" aria-hidden="true"></i></a-button>
                                </a-tooltip>
                                <a-tooltip placement="top">
                                    <template #title>Edit</template>
                                    <a-button type="link" @click="showEditPurchaseModal (record)"><i class="fa fa-pencil-square-o text-s text-green-500" aria-hidden="true"></i></a-button>
                                </a-tooltip>
                            </template>

                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
          <!-- Purchase Product Modal -->
          <a-modal
    v-model:open="isPurchaseProductModalVisible"
    title="Add Purchase Detail"
    @cancel="isPurchaseProductModalVisible = false"
    :footer="null"
>
    <form @submit.prevent="savePurchaseProduct">
        <div class="mb-4">
            <label class="block">Product</label>
            <a-select
                v-model:value="purchaseProductForm.product_id"
                show-search
                placeholder="Select a product"
                style="width: 100%"
                :options="productOptions"
                :filter-option="filterOption"
            />
            <div v-if="purchaseProductForm.errors.product_id" class="text-red-500">
                {{ purchaseProductForm.errors.product_id }}
            </div>
        </div>

        <div class="mb-4">
            <label class="block">Purchase Price</label>
            <a-input type="number" v-model:value="purchaseProductForm.purchase_price" class="mt-2 w-full" />
            <div v-if="purchaseProductForm.errors.purchase_price" class="text-red-500">
                {{ purchaseProductForm.errors.purchase_price }}
            </div>
        </div>

        <div class="mb-4">
            <label class="block">Sale Price</label>
            <a-input type="number" v-model:value="purchaseProductForm.sale_price" class="mt-2 w-full" />
            <div v-if="purchaseProductForm.errors.sale_price" class="text-red-500">
                {{ purchaseProductForm.errors.sale_price }}
            </div>
        </div>

        <div class="mb-4">
            <label class="block">Stock</label>
            <a-input type="number" v-model:value="purchaseProductForm.stock" class="mt-2 w-full" />
            <div v-if="purchaseProductForm.errors.stock" class="text-red-500">
                {{ purchaseProductForm.errors.stock }}
            </div>
        </div>

        <div class="mb-4">
            <label class="block">Description</label>
            <a-textarea v-model:value="purchaseProductForm.description" class="mt-2 w-full" placeholder="Description"
                :auto-size="{ minRows: 2, maxRows: 5 }" />
            <div v-if="purchaseProductForm.errors.description" class="text-red-500">
                {{ purchaseProductForm.errors.description }}
            </div>
        </div>

        <div class="text-right">
            <a-button type="default" @click="isPurchaseProductModalVisible = false">Cancel</a-button>
            <a-button type="primary" html-type="submit" class="ml-2">Save</a-button>
        </div>
    </form>
</a-modal>


          <!-- Edit Product Modal -->
          <a-modal v-model:open="isEditModalVisible" title="Edit Purchase Details" @cancel="isEditModalVisible = false"
            :footer="null">
            <form @submit.prevent="updatePurchaseDetails()">
                <div class="mb-4">
                    <label class="block">Purchase price</label>
                    <a-input type="number" v-model:value="purchaseProductEditForm .purchase_price" class="mt-2 w-full"/>
                    <div v-if="purchaseProductEditForm .errors.purchase_price" class="text-red-500">{{ purchaseProductEditForm .errors.purchase_price }}</div>
                </div>
                <div class="mb-4">
                    <label class="block">Sale price</label>
                    <a-input type="number" v-model:value="purchaseProductEditForm .sale_price" class="mt-2 w-full" />
                    <div v-if="purchaseProductEditForm .errors.sale_price" class="text-red-500">{{ purchaseProductEditForm .errors.sale_price }}</div>
                </div>
                <div class="mb-4">
                    <label class="block">Sale price</label>
                    <a-input type="number" v-model:value="purchaseProductEditForm .stock" class="mt-2 w-full" />
                    <div v-if="purchaseProductEditForm .errors.stock" class="text-red-500">{{ purchaseProductEditForm .errors.stock }}</div>
                </div>

                <div class="mb-4">
                    <label class="block">Description</label>
                    <a-textarea v-model:value="purchaseProductEditForm .description" class="mt-2 w-full" placeholder="Description"
                        :auto-size="{ minRows: 2, maxRows: 5 }" />
                    <div v-if="purchaseProductEditForm .errors.description" class="text-red-500">{{ purchaseProductEditForm .errors.description }}</div>
                </div>
                <div class="text-right">
                    <a-button type="default" @click="isEditModalVisible = false">Cancel</a-button>
                    <a-button type="primary" html-type="submit" class="ml-2">Update</a-button>
                </div>
            </form>
        </a-modal>
    </AdminLayout>
</template>
