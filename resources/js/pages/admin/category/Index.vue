<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Modal } from 'ant-design-vue';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import dayjs from "dayjs";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import { router } from '@inertiajs/vue3';
import { getCurrentInstance } from 'vue';

DataTable.use(DataTablesCore);

const isLoading = ref(false);
const formatDate = (date: string) => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};

const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

defineProps<{
    categories: {
        data: Array<any>;
    };
}>();

const form = useForm({
    name: '',
    description: '',
    id: null,
    image: null as File | null
})
const editForm = useForm({
    id: null,
    name: '',
    description: '',
    image: null as File | null,
    _method: 'PUT'
});
const brandForm = useForm({
    id: null,
    name: '',
    description: '',
    category_id: null,
    image: null as File | null,
});

const isAddCategoryModalVisible = ref(false);
const selectedCategoryName = ref('');
const currentImage = ref('');
const imagePreview = ref('');
const editImagePreview = ref('');
const isImagePreviewModalVisible = ref(false);
const previewImageUrl = ref('');

const isEditModalVisible = ref(false);
const isbrandModalVisible = ref(false);
const isImportModalVisible = ref(false);
const isImporting = ref(false);

// Import form
const importForm = useForm({
    excel_file: null as File | null,
});

// DataTable columns for categories
const dataTableColumns = [
    { title: t('Sr.'), data: null, render: (data: any, type: any, row: any, meta: any) => meta.row + 1 },
    {
        title: t('Image'),
        data: 'image',
        orderable: false,
        render: (data: string) =>
            data
                ? `<img src="/storage/${data}" class="w-12 h-12 object-cover rounded mb-1 cursor-pointer" style="display:inline-block;" />`
                : '<span class="text-gray-400 mb-1">No Image</span>'
    },
    { title: t('Name'), data: 'name' },
    { title: t('Description'), data: 'description', render: (data: string) => data ?? 'N/A' },
    { title: t('Created At'), data: 'created_at', render: (data: string) => formatDate(data) },
    {
        title: t('Action'),
        data: null,
        orderable: false,
        render: (data, type, row) => `
        <button class="edit-btn p-2" data-id="${row.id}" title="${t('Edit')}">
            <i class="fa fa-pencil-square-o text-green-500"></i>
        </button>
        <button class="delete-btn p-2" data-id="${row.id}" title="${t('Delete')}">
            <i class="fa fa-trash text-red-500"></i>
        </button>
        <button class="brand-btn p-2" data-id="${row.id}" title="${t('Add Brand')}">
            <i class="fa fa-creative-commons"></i>
        </button>
        <button class="related-brand-btn p-2" data-id="${row.slug}" title="${t('Brand List')}">
            <i class="fa fa-list text-slate-800"></i>
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
            row.querySelector('.edit-btn')?.addEventListener('click', () => openEditModal(data));
            row.querySelector('.delete-btn')?.addEventListener('click', () => deleteCategory(data.id));
            row.querySelector('.brand-btn')?.addEventListener('click', () => openBrandModal(data));
            row.querySelector('.related-brand-btn ')?.addEventListener('click', () => moveRelatedBrand(data));
            row.querySelector('img')?.addEventListener('click', () => {
                if (data.image) openImagePreview(data.image);
            });
        }, 0);
    }
};

const openAddCategoryModal = () => {
    form.reset();
    imagePreview.value = '';
    isAddCategoryModalVisible.value = true;
};

const handleImageChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.image = target.files[0];
        imagePreview.value = URL.createObjectURL(target.files[0]);
    }
};

const handleBrandImageChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        brandForm.image = target.files[0];
        imagePreview.value = URL.createObjectURL(target.files[0]);
    }
};

const handleEditImageChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        editForm.image = target.files[0];
        editImagePreview.value = URL.createObjectURL(target.files[0]);
    }
};

const saveCategory = () => {
    isLoading.value = true;
    form.post(route('admin.category.store'), {
        onSuccess: () => {
            form.reset();
            isAddCategoryModalVisible.value = false;
        },
        onFinish: () => {
            isLoading.value = false;
        }
    })
}
const deleteCategory = (id: number) => {
    Modal.confirm({
        title: t('Are you sure you want to delete'),
        content: t('Deleting this category will also remove all associated brands. This action is irreversible. Are you sure you want to proceed?'),
        okText: t('Yes, Delete'),
        okType: 'danger',
        cancelText: t('Cancel'),
        onOk() {
            isLoading.value = true;
            form.delete(route('admin.category.delete', { id: id }), {
                onFinish: () => {
                    isLoading.value = false;
                }
            });
        },
    });
};

const importCategory = () => {
    isImportModalVisible.value = true;
}

const openEditModal = (category: any) => {
    editForm.id = category.id;
    editForm.name = category.name;
    editForm.description = category.description;
    editForm.image = null;
    currentImage.value = category.image;
    editImagePreview.value = '';
    isEditModalVisible.value = true;
};
const openBrandModal = (record: any) => {
    selectedCategoryName.value = record.name;
    brandForm.category_id = record.id;
    isbrandModalVisible.value = true;
};
const moveRelatedBrand = (record: any) => {
    router.get(route('admin.related-brand-list', record.slug))

};
const saveBrand = () => {
    isLoading.value = true;
    brandForm.post(route('admin.brand.store'), {
        onSuccess: () => {
            brandForm.reset();
            isbrandModalVisible.value = false;
            if (imagePreview.value) {
                URL.revokeObjectURL(imagePreview.value);
                imagePreview.value = '';
            }
        },
        onFinish: () => {
            isLoading.value = false;
        }
    })
}

const updateCategory = () => {
    isLoading.value = true;
    editForm.post(route('admin.category.update', { id: editForm.id }), {
        onSuccess: () => {
            isEditModalVisible.value = false;
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

const getOriginalFilename = (path: string) => {
    if (!path) return '';
    const parts = path.split('/');
    const filename = parts[parts.length - 1];
    return filename;
};

const openImagePreview = (imagePath: string) => {
    previewImageUrl.value = '/storage/' + imagePath;
    isImagePreviewModalVisible.value = true;
};

const handleImportData = () => {

    isImporting.value = true;

    // Create FormData manually
    const formData = new FormData();
    formData.append('file', importForm.excel_file as unknown as File);

    // Use router.post with FormData
    router.post(route('admin.import.data'), formData, {
        onSuccess: () => {
            isImportModalVisible.value = false;
            importForm.reset();
        },
        onError: (errors) => {
            console.error('Import errors:', errors);
        },
        onFinish: () => {
            isImporting.value = false;
        }
    });
};

const handleExcelFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        importForm.excel_file = target.files[0];
    }
};
</script>

<template>
    <div v-if="isLoading" class="loading-overlay">
        <a-spin size="large" />
    </div>
    <AdminLayout>

        <Head :title="t('Category List')" />

        <a-row>
            <a-col :xs="24">
                <div class="bg-white rounded-lg responsive-table p-4 shadow-md">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold mb-4">{{ t('Category List') }}</h2>
                        <div>
                            <a-button @click="openAddCategoryModal()" type="default">
                                {{ t('Add Category') }}
                            </a-button>
                            <a-button @click="importCategory()" type="default">
                                Import Category
                            </a-button>
                            <Link :href="route('admin.category.log')">
                            <a-button type="default">
                                {{ t('Category Logs') }}
                            </a-button>
                            </Link>
                        </div>
                    </div>
                    <DataTable v-if="categories?.data" :data="categories.data" :columns="dataTableColumns"
                        :options="options" class="display">
                        <thead>
                            <tr>
                                <th>{{ t('Sr.') }}</th>
                                <th>{{ t('Image') }}</th>
                                <th>{{ t('Name') }}</th>
                                <th>{{ t('Description') }}</th>
                                <th>{{ t('Created At') }}</th>
                                <th>{{ t('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this automatically -->
                        </tbody>
                    </DataTable>
                </div>
            </a-col>
        </a-row>



        <!-- Add Category Modal -->
        <a-modal v-model:open="isAddCategoryModalVisible" :title="t('Add Category')"
            @cancel="isAddCategoryModalVisible = false" :footer="null">
            <form @submit.prevent="saveCategory()" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block">{{ t('Name') }}</label>
                    <a-input v-model:value="form.name" class="mt-2 w-full"
                        :placeholder="t('Enter Name')" />
                    <div v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</div>
                </div>
                <div class="mb-4">
                    <label class="block">{{ t('Description') }}</label>
                    <a-textarea v-model:value="form.description" class="mt-2 w-full"
                        :placeholder="t('Enter Description')"
                        :auto-size="{ minRows: 2, maxRows: 5 }" />
                    <div v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</div>
                </div>
                <div class="mb-4">
                    <label class="block">{{ t('Image') }}</label>
                    <input type="file" @change="handleImageChange" accept="image/*"
                        class="mt-2 w-full p-2 border rounded" />
                    <div v-if="form.errors.image" class="text-red-500">{{ form.errors.image }}</div>
                    <div v-if="imagePreview" class="mt-2">
                        <p class="text-sm text-gray-600 mb-1">{{ t('Preview') }}:</p>
                        <img :src="imagePreview" alt="Image Preview" class="w-24 h-24 object-cover rounded border" />
                    </div>
                </div>
                <div class="text-right">
                    <a-button type="default" @click="isAddCategoryModalVisible = false">{{ t('Cancel') }}</a-button>
                    <a-button type="primary" html-type="submit" class="ml-2">{{ t('Add') }}</a-button>
                </div>
            </form>
        </a-modal>

        <!-- Edit Category Modal -->
        <a-modal v-model:open="isEditModalVisible" :title="t('Edit Category')"
            @cancel="isEditModalVisible = false" :footer="null">
            <form @submit.prevent="updateCategory()" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block">{{ t('Name') }}</label>
                    <a-input v-model:value="editForm.name" class="mt-2 w-full"
                        :placeholder="t('Enter Name')" />
                    <div v-if="editForm.errors.name" class="text-red-500">{{ editForm.errors.name }}</div>
                </div>
                <div class="mb-4">
                    <label class="block">{{ t('Description') }}</label>
                    <a-textarea v-model:value="editForm.description" class="mt-2 w-full"
                        :placeholder="t('Enter Description')"
                        :auto-size="{ minRows: 2, maxRows: 5 }" />
                    <div v-if="editForm.errors.description" class="text-red-500">{{ editForm.errors.description }}</div>
                </div>
                <div class="mb-4">
                    <label class="block">{{ t('Image') }}</label>
                    <div v-if="currentImage" class="mb-2">
                        <p class="text-sm text-gray-500 mb-1">{{ t('Current Image') }}</p>
                        <img :src="'/storage/' + currentImage" alt="Current Category Image"
                            class="w-24 h-24 object-cover rounded border" />
                        <p class="text-xs text-gray-500 mt-1">{{ getOriginalFilename(currentImage) }}</p>
                    </div>
                    <input type="file" @change="handleEditImageChange" accept="image/*"
                        class="mt-2 w-full p-2 border rounded" />
                    <div v-if="editForm.errors.image" class="text-red-500">{{ editForm.errors.image }}</div>
                    <div class="mt-2 text-sm text-gray-500">
                        {{ t('Leave empty to keep the current image') }}
                    </div>
                    <div v-if="editImagePreview" class="mt-2">
                        <p class="text-sm text-gray-600 mb-1">{{ t('New Image Preview') }}:</p>
                        <img :src="editImagePreview" alt="New Image Preview"
                            class="w-24 h-24 object-cover rounded border" />
                    </div>
                </div>
                <div class="text-right">
                    <a-button type="default" @click="isEditModalVisible = false">{{ t('Cancel') }}</a-button>
                    <a-button type="primary" html-type="submit" class="ml-2">{{ t('Update') }}</a-button>
                </div>
            </form>
        </a-modal>

        <!-- Brand Modal -->
        <a-modal v-model:open="isbrandModalVisible" :title="t('Add Brand')"
            @cancel="isbrandModalVisible = false" :footer="null">
            <h4 class="text-md">{{ t('Category') }} ({{ selectedCategoryName }})</h4>
            <form @submit.prevent="saveBrand()" enctype="multipart/form-data">
                <a-input hidden v-model:value="brandForm.category_id" class="mt-2 w-full"
                    :placeholder="t('Enter Name')" />
                <div class="mb-4">
                    <label class="block">{{ t('Name') }}</label>
                    <a-input v-model:value="brandForm.name" class="mt-2 w-full"
                        :placeholder="t('Enter Name')" />
                    <div v-if="brandForm.errors.name" class="text-red-500">{{ brandForm.errors.name }}</div>
                </div>
                <div class="mb-4">
                    <label class="block">{{ t('Description') }}</label>
                    <a-textarea v-model:value="brandForm.description" class="mt-2 w-full"
                        :placeholder="t('Enter Description')"
                        :auto-size="{ minRows: 2, maxRows: 5 }" />
                    <div v-if="brandForm.errors.description" class="text-red-500">{{ brandForm.errors.description }}
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block">{{ t('Image') }}</label>
                    <input type="file" @change="handleBrandImageChange" accept="image/*"
                        class="mt-2 w-full p-2 border rounded" />
                    <div v-if="brandForm.errors.image" class="text-red-500">{{ brandForm.errors.image }}</div>
                    <div v-if="imagePreview" class="mt-2">
                        <p class="text-sm text-gray-600 mb-1">{{ t('Preview') }}:</p>
                        <img :src="imagePreview" alt="Image Preview" class="w-24 h-24 object-cover rounded border" />
                    </div>
                </div>
                <div class="text-right">
                    <a-button type="default" @click="isbrandModalVisible = false">{{ t('Cancel') }}</a-button>
                    <a-button type="primary" html-type="submit" class="ml-2">{{ t('Save') }}</a-button>
                </div>
            </form>
        </a-modal>

        <!-- Image Preview Modal -->
        <a-modal v-model:open="isImagePreviewModalVisible" :title="t('Image Preview')"
            @cancel="isImagePreviewModalVisible = false" :footer="null" width="600px">
            <div class="flex justify-center p-4">
                <img :src="previewImageUrl" alt="Full Size Image" class="max-w-full max-h-[500px] object-cover" />
            </div>
        </a-modal>

        <!-- Import Category Modal -->
        <a-modal v-model:open="isImportModalVisible" :title="t('Import Category')"
            @cancel="isImportModalVisible = false" :footer="null">
            <form @submit.prevent="handleImportData()" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block font-medium mb-2">{{ t('Excel File') }}</label>
                    <input type="file"
                        @change="handleExcelFileChange"
                        accept=".xlsx,.xls,.csv"
                        class="mt-2 w-full p-2 border rounded"
                        required />
                    <div class="text-sm text-gray-500 mt-1">
                        {{ t('Supported formats: XLSX, XLS, CSV') }}
                    </div>
                    <div v-if="importForm.errors.excel_file" class="text-red-500 mt-1">{{ importForm.errors.excel_file }}</div>
                </div>

                <div class="text-right">
                    <a-button type="default" @click="isImportModalVisible = false">
                        {{ t('Cancel') }}
                    </a-button>
                    <a-button type="primary" html-type="submit" class="ml-2" :loading="isImporting">
                        {{ t('Import') }}
                    </a-button>
                </div>
            </form>
        </a-modal>
    </AdminLayout>
</template>
<style scoped>
.display {
    width: 100%;
}
</style>
