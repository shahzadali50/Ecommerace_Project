<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link , useForm,usePage} from '@inertiajs/vue3';
import {  Modal } from 'ant-design-vue';
import dayjs from "dayjs";
import { ref, computed } from 'vue';
const isLoading = ref(false);
// Format date function
const formatDate = (date: string) => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};
const page = usePage();

const translations = computed(() => {
    return page.props.translations?.dashboard_all_pages || {};
});

// Image preview modal state
const isImagePreviewModalVisible = ref(false);
const previewImageUrl = ref('');

const openImagePreview = (imagePath: string) => {
    previewImageUrl.value = '/storage/' + imagePath;
    isImagePreviewModalVisible.value = true;
};

// Define table columns
const columns = computed(() => [
    { title: translations.value.sr || 'Sr.', dataIndex: 'id', key: 'id' },
    { title: translations.value.image || 'Image', dataIndex: 'image', key: 'image' },
    { title: translations.value.name || 'Name', dataIndex: 'name', key: 'name' },
    { title: translations.value.description || 'Description', dataIndex: 'description', key: 'description' },
    { title: translations.value.category || 'Category', dataIndex: 'category', key: 'category' },
    { title: translations.value.created_at || 'Created At', dataIndex: 'created_at', key: 'created_at' },
    { title: translations.value.action || 'Action', dataIndex: 'action', key: 'action' },
]);

// Receive brands as a prop from Inertia
defineProps({
    brands: Object,
    category: Object,
});
const form = useForm({});
const deleteBrand = (id: number) => {
    Modal.confirm({
        title: translations.value.delete_brand_confirm || 'Are you sure you want to delete this Brand?',
        content: translations.value.delete_brand_warning || 'This action cannot be undone.',
        okText: translations.value.confirm_delete_ok || 'Yes, Delete',
        okType: 'danger',
        cancelText: translations.value.confirm_delete_cancel || 'Cancel',
        onOk() {
            isLoading.value = true;
            form.delete(route('admin.brand.delete', { id: id }), {
                onSuccess: () => {
                },
                onFinish: () => {
           isLoading.value = false;
        }
            });
        },
    });
};
const isEditModalVisible = ref(false);
const editForm = useForm({
    id: null,
    name: '',
    description: '',
    image: null as File | null,
    _method: 'PUT'
});

const editImagePreview = ref('');
const currentImage = ref('');

const handleEditImageChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        editForm.image = target.files[0];
        // Create preview URL
        editImagePreview.value = URL.createObjectURL(target.files[0]);
    }
};

const openEditModal = (brands: any) => {
    editForm.id = brands.id;
    editForm.name = brands.name;
    editForm.description = brands.description;
    editForm.image = null; // Reset image when opening modal
    currentImage.value = brands.image; // Store the current image path
    editImagePreview.value = ''; // Reset preview
    isEditModalVisible.value = true;
};

// Update brand
const updateBrand = () => {
    isLoading.value = true;
    editForm.post(route('admin.brand.update', { id: editForm.id }), {
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
     <div v-if="isLoading" class="loading-overlay">
        <a-spin size="large" />
    </div>
    <AdminLayout>
        <Head :title="translations.brand_title || 'Brand List'" />
        <a-row>
            <a-col :span="24">
                <div class="bg-white rounded-lg p-4 shadow-md">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold"> {{ translations.brand_title || 'Brand List' }}  - {{ category.name }}</h2>

                        <div>
                            <Link :href="route('admin.categories')">
                            <a-button class="mx-2" type="default">{{ translations.back || 'Back' }}</a-button>
                        </Link>
                        <Link :href="route('admin.brand-log')">
                            <a-button class="mx-2" type="default">{{ translations.brand_logs || 'Brand Logs' }}</a-button>
                        </Link>
                        <Link :href="route('admin.brands')" >
                            <a-button class="mx-2" type="default">{{ translations.all_brand_list || 'All Brand List' }}</a-button>
                        </Link>
                        </div>
                    </div>

                    <!-- Display table -->
                    <a-table v-if="brands" :columns="columns" :data-source="brands.data" rowKey="id" bordered>
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.dataIndex === 'id'">
                                {{ index + 1 }}
                            </template>
                            <template v-if="column.dataIndex === 'image'">
                                <div>
                                    <img v-if="record.image" :src="'/storage/' + record.image" alt="Category Image"
                                        class="w-12 h-12 object-cover rounded mb-1 cursor-pointer hover:opacity-80 transition-opacity"
                                        @click="openImagePreview(record.image)" />
                                    <span v-else class="text-gray-400 mb-1">No Image</span>
                                </div>
                            </template>
                            <template v-if="column.dataIndex === 'name'">
                                {{ record.name}}
                            </template>
                            <template v-if="column.dataIndex === 'description'">
                                {{ record.description ? record.description : 'N/A'}}
                            </template>
                            <template v-if="column.dataIndex === 'category'">
                                {{ record.slug}}
                            </template>

                            <template v-else-if="column.dataIndex === 'created_at'">
                                {{ formatDate(record.created_at) }}
                            </template>
                            <template v-else-if="column.dataIndex === 'action'">
                            <a-tooltip placement="top">
                                    <template #title>{{ translations.delete || 'Delete' }}</template>
                                    <a-button type="link"  @click="deleteBrand(record.id)"><i class="fa fa-trash text-red-500" aria-hidden="true"></i></a-button>
                                </a-tooltip>
                                <a-tooltip placement="top">
                                    <template #title>{{ translations.edit || 'Edit' }}</template>
                                    <a-button type="link" @click="openEditModal(record)"><i class="fa fa-pencil-square-o text-s text-green-500" aria-hidden="true"></i></a-button>
                                </a-tooltip>
                            </template>

                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
          <!-- Edit Category Modal -->
          <a-modal v-model:open="isEditModalVisible" :title="translations.update || 'Edit Brand'" @cancel="isEditModalVisible = false"
            :footer="null">
            <form @submit.prevent="updateBrand()" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block">{{ translations.name || 'Name' }}</label>
                    <a-input v-model:value="editForm.name" class="mt-2 w-full" :placeholder="translations.name_placeholder || 'Enter Name'" />
                    <div v-if="editForm.errors.name" class="text-red-500">
                        {{ editForm.errors.name }}
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block">{{ translations.description || 'Description' }}</label>
                    <a-textarea v-model:value="editForm.description" class="mt-2 w-full" :placeholder="translations.eneter_description || 'Enter Description'"
                        :auto-size="{ minRows: 2, maxRows: 5 }" />
                    <div v-if="editForm.errors.description" class="text-red-500">
                        {{ editForm.errors.description }}
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block">{{ translations.image || 'Image' }}</label>
                    <div v-if="currentImage" class="mb-2">
                        <p class="text-sm text-gray-500 mb-1">{{ translations.current_image || 'Current Image' }}</p>
                        <img :src="'/storage/' + currentImage" alt="Current Brand Image"
                            class="w-24 h-24 object-cover rounded border" />
                    </div>
                    <input type="file" @change="handleEditImageChange" accept="image/*"
                        class="mt-2 w-full p-2 border rounded" />
                    <div v-if="editForm.errors.image" class="text-red-500">{{ editForm.errors.image }}</div>
                    <div class="mt-2 text-sm text-gray-500">
                        {{ translations.keep_current_image || 'Leave empty to keep the current image' }}
                    </div>
                    <!-- New Image Preview -->
                    <div v-if="editImagePreview" class="mt-2">
                        <p class="text-sm text-gray-600 mb-1">{{ translations.new_image_preview || 'New Image Preview' }}:</p>
                        <img :src="editImagePreview" alt="New Image Preview"
                            class="w-24 h-24 object-cover rounded border" />
                    </div>
                </div>
                <div class="text-right">
                    <a-button type="default" @click="isEditModalVisible = false">{{ translations.cancel || 'Cancel' }}</a-button>
                    <a-button type="primary" html-type="submit" class="ml-2">{{ translations.update || 'Update' }}</a-button>
                </div>
            </form>

        </a-modal>

        <!-- Image Preview Modal -->
        <a-modal v-model:open="isImagePreviewModalVisible" :title="translations.preview || 'Image Preview'"
            @cancel="isImagePreviewModalVisible = false" :footer="null" width="600px">
            <div class="flex justify-center p-4">
                <img :src="previewImageUrl" alt="Full Size Image" class="max-w-full max-h-[500px] object-cover" />
            </div>
        </a-modal>
    </AdminLayout>
</template>
