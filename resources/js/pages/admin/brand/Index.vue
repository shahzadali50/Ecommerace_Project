<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Modal } from 'ant-design-vue';
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';

DataTable.use(DataTablesCore);

const isLoading = ref(false);

const page = usePage();
const translations = computed(() => {
  return (page.props.translations as any)?.dashboard_all_pages || {};
});

const props = defineProps<{
  brands: { data: Array<any> };
}>();

// Format date
const formatDate = (date: string) => {
  return date ? dayjs(date).format('DD-MM-YYYY hh:mm A') : 'N/A';
};

// DataTable columns for brands
const dataTableColumns = [
  {
    title: translations.value.sr || 'Sr.',
    data: null,
    render: (data: any, type: any, row: any, meta: any) => meta.row + 1,
  },
  {
    title: translations.value.image || 'Image',
    data: 'image',
    orderable: false,
    render: (data: string) =>
      data
        ? `<img src="/storage/${data}" class="w-12 h-12 object-cover rounded mb-1 cursor-pointer" style="display:inline-block;" />`
        : '<span class="text-gray-400 mb-1">No Image</span>',
  },
  { title: translations.value.name || 'Name', data: 'name' },
  {
    title: translations.value.description || 'Description',
    data: 'description',
    render: (data: string) => data ?? 'N/A',
  },
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
      row.querySelector('.delete-btn')?.addEventListener('click', () => deleteBrand(data.id));
      row.querySelector('img')?.addEventListener('click', () => {
        if (data.image) openImagePreview(data.image);
      });
    }, 0);
  },
};

// Forms
const form = useForm({
  name: '',
  description: '',
  image: null as File | null,
});

const editForm = useForm({
  id: null,
  name: '',
  description: '',
  image: null as File | null,
  _method: 'PUT',
});

// Modal states
const isAddBrandModalVisible = ref(false);
const isEditModalVisible = ref(false);
const isImagePreviewModalVisible = ref(false);
const previewImageUrl = ref('');
const imagePreview = ref<string | null>(null);
const editImagePreview = ref('');
const currentImage = ref('');

// Add brand modal
const openAddBrandModal = () => {
  form.reset();
  imagePreview.value = null;
  isAddBrandModalVisible.value = true;
};

// Edit brand modal
const openEditModal = (brand: any) => {
  editForm.id = brand.id;
  editForm.name = brand.name;
  editForm.description = brand.description;
  editForm.image = null;
  currentImage.value = brand.image;
  editImagePreview.value = '';
  isEditModalVisible.value = true;
};

// Delete brand
const deleteBrand = (id: number) => {
  Modal.confirm({
    title: translations.value.confirm_delete_title || 'Are you sure you want to delete',
    content: translations.value.delete_brand_warning || 'This action cannot be undone.',
    okText: translations.value.confirm_delete_ok || 'Yes, Delete',
    okType: 'danger',
    cancelText: translations.value.confirm_delete_cancel || 'Cancel',
    onOk() {
      isLoading.value = true;
      form.delete(route('admin.brand.delete', { id }), {
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

// Save brand
const saveBrand = () => {
  isLoading.value = true;
  form.post(route('admin.brand.store'), {
    onSuccess: () => {
      form.reset();
      isAddBrandModalVisible.value = false;
      if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
      }
    },
    onFinish: () => {
      isLoading.value = false;
    },
  });
};

// Update brand
const updateBrand = () => {
  isLoading.value = true;
  editForm.post(route('admin.brand.update', { id: editForm.id }), {
    onSuccess: () => {
      isEditModalVisible.value = false;
      if (editImagePreview.value) {
        URL.revokeObjectURL(editImagePreview.value);
        editImagePreview.value = '';
      }
    },
    onFinish: () => {
      isLoading.value = false;
    },
  });
};

// Image handling
const handleBrandImageChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.image = target.files[0];
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

const openImagePreview = (imagePath: string) => {
  previewImageUrl.value = '/storage/' + imagePath;
  isImagePreviewModalVisible.value = true;
};

const getOriginalFilename = (path: string) => {
  if (!path) return '';
  const parts = path.split('/');
  return parts[parts.length - 1];
};
</script>

<template>
  <div v-if="isLoading" class="loading-overlay">
    <a-spin size="large" />
  </div>
  <AdminLayout>
    <Head :title="translations.brand_title || 'Brand List'" />
    <a-row>
      <a-col :xs="24">
        <div class="bg-white rounded-lg responsive-table p-4 shadow-md">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold mb-4">{{ translations.brand_title || 'Brand List' }}</h2>
            <div>
              <a-button @click="openAddBrandModal" type="default">
                {{ translations.add_brand || 'Add Brand' }}
              </a-button>
              <Link :href="route('admin.brand-log')">
                <a-button type="default">
                  {{ translations.brand_logs || 'Brand Logs' }}
                </a-button>
              </Link>
            </div>
          </div>
          <DataTable
            v-if="brands?.data"
            :data="brands.data"
            :columns="dataTableColumns"
            :options="options"
            class="display"
          >
            <thead>
              <tr>
                <th>{{ translations.sr || 'Sr.' }}</th>
                <th>{{ translations.image || 'Image' }}</th>
                <th>{{ translations.name || 'Name' }}</th>
                <th>{{ translations.description || 'Description' }}</th>
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

    <!-- Add Brand Modal -->
    <a-modal
      v-model:open="isAddBrandModalVisible"
      :title="translations.add_brand || 'Add Brand'"
      @cancel="isAddBrandModalVisible = false"
      :footer="null"
    >
      <form @submit.prevent="saveBrand()" enctype="multipart/form-data">
        <div class="mb-4">
          <label class="block">{{ translations.name || 'Name' }}</label>
          <a-input
            v-model:value="form.name"
            class="mt-2 w-full"
            :placeholder="translations.name_placeholder || 'Enter Name'"
          />
          <div v-if="form.errors.name" class="text-red-500">
            {{ form.errors.name }}
          </div>
        </div>
        <div class="mb-4">
          <label class="block">{{ translations.description || 'Description' }}</label>
          <a-textarea
            v-model:value="form.description"
            class="mt-2 w-full"
            :placeholder="translations.eneter_description || 'Enter Description'"
            :auto-size="{ minRows: 2, maxRows: 5 }"
          />
          <div v-if="form.errors.description" class="text-red-500">
            {{ form.errors.description }}
          </div>
        </div>
        <div class="mb-4">
          <label class="block">{{ translations.image || 'Image' }}</label>
          <input
            type="file"
            @change="handleBrandImageChange"
            accept="image/*"
            class="mt-2 w-full p-2 border rounded"
          />
          <div v-if="form.errors.image" class="text-red-500">{{ form.errors.image }}</div>
          <div v-if="imagePreview" class="mt-2">
            <p class="text-sm text-gray-600 mb-1">{{ translations.preview || 'Preview' }}</p>
            <img :src="imagePreview" alt="Image Preview" class="w-24 h-24 object-cover rounded border" />
          </div>
        </div>
        <div class="text-right">
          <a-button type="default" @click="isAddBrandModalVisible = false">
            {{ translations.cancel || 'Cancel' }}
          </a-button>
          <a-button type="primary" html-type="submit" class="ml-2">
            {{ translations.save || 'Save' }}
          </a-button>
        </div>
      </form>
    </a-modal>

    <!-- Edit Brand Modal -->
    <a-modal
      v-model:open="isEditModalVisible"
      :title="translations.update || 'Edit Brand'"
      @cancel="isEditModalVisible = false"
      :footer="null"
    >
      <form @submit.prevent="updateBrand()" enctype="multipart/form-data">
        <div class="mb-4">
          <label class="block">{{ translations.name || 'Name' }}</label>
          <a-input
            v-model:value="editForm.name"
            class="mt-2 w-full"
            :placeholder="translations.name_placeholder || 'Enter Name'"
          />
          <div v-if="editForm.errors.name" class="text-red-500">
            {{ editForm.errors.name }}
          </div>
        </div>
        <div class="mb-4">
          <label class="block">{{ translations.description || 'Description' }}</label>
          <a-textarea
            v-model:value="editForm.description"
            class="mt-2 w-full"
            :placeholder="translations.eneter_description || 'Enter Description'"
            :auto-size="{ minRows: 2, maxRows: 5 }"
          />
          <div v-if="editForm.errors.description" class="text-red-500">
            {{ editForm.errors.description }}
          </div>
        </div>
        <div class="mb-4">
          <label class="block">{{ translations.image || 'Image' }}</label>
          <div v-if="currentImage" class="mb-2">
            <p class="text-sm text-gray-500 mb-1">{{ translations.current_image || 'Current Image' }}</p>
            <img
              :src="'/storage/' + currentImage"
              alt="Current Brand Image"
              class="w-24 h-24 object-cover rounded border"
            />
            <p class="text-xs text-gray-500 mt-1">{{ getOriginalFilename(currentImage) }}</p>
          </div>
          <input
            type="file"
            @change="handleEditImageChange"
            accept="image/*"
            class="mt-2 w-full p-2 border rounded"
          />
          <div v-if="editForm.errors.image" class="text-red-500">{{ editForm.errors.image }}</div>
          <div class="mt-2 text-sm text-gray-500">
            {{ translations.keep_current_image || 'Leave empty to keep the current image' }}
          </div>
          <div v-if="editImagePreview" class="mt-2">
            <p class="text-sm text-gray-600 mb-1">{{ translations.new_image_preview || 'New Image Preview' }}</p>
            <img :src="editImagePreview" alt="New Image Preview" class="w-24 h-24 object-cover rounded border" />
          </div>
        </div>
        <div class="text-right">
          <a-button type="default" @click="isEditModalVisible = false">
            {{ translations.cancel || 'Cancel' }}
          </a-button>
          <a-button type="primary" html-type="submit" class="ml-2">
            {{ translations.update || 'Update' }}
          </a-button>
        </div>
      </form>
    </a-modal>

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
