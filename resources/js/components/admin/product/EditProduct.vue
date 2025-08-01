<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Button } from "@/components/ui/button";
import { LoaderCircle } from "lucide-vue-next";

// Define props
const props = defineProps<{
  isVisible: boolean;
  product: {
    id: number;
    name: string;
    description: string;
    brand_id: number;
    category_id: number;
    thumnail_img: string;
    gallary_img: string;
    stock: number;
    status: string;
    purchase_price: number;
    sale_price: number;
    feature: boolean;
    barcode: string;
    discount: number;
    final_price: number;
  } | null;
  categories: { data: Array<{ id: number; name: string; children?: Array<any> }> };
  brands: { data: Array<{ id: number; name: string; category_id: number }> };
  translations: Record<string, string>;
}>();

// Define emits
const emit = defineEmits(['update:isVisible', 'success']);

// State
const isLoading = ref(false);
const thumnailPreview = ref<string | null>(null);
const gallaryPreviews = ref<string[]>([]);
const existingGalleryImages = ref<string[]>([]);

// Initialize form with product data
const editForm = useForm({
  id: props.product?.id || 0,
  name: props.product?.name || '',
  description: props.product?.description || '',
  brand_id: props.product?.brand_id || null,
  category_id: props.product?.category_id || null,
  thumnail_img: null as File | null,
  gallary_img: [] as File[],
  existing_gallary_img: [] as string[],
  stock: props.product?.stock || 0,
  status: props.product?.status || 'active',
  purchase_price: props.product?.purchase_price || 0,
  sale_price: props.product?.sale_price || 0,
  feature: props.product?.feature || false,
  barcode: props.product?.barcode || '',
  discount: props.product?.discount || 0,
  final_price: props.product?.final_price || props.product?.sale_price || 0,
});

// Initialize existing gallery images
if (props.product?.gallary_img) {
  try {
    const parsedImages = JSON.parse(props.product.gallary_img);
    existingGalleryImages.value = parsedImages;
    editForm.existing_gallary_img = parsedImages;
  } catch (e) {
    console.error('Error parsing gallery images:', e);
    existingGalleryImages.value = [];
    editForm.existing_gallary_img = [];
  }
}

// Function to handle modal visibility changes
const handleModalVisibility = (visible: boolean) => {
  if (visible && props.product) {
    // Reset form with product data
    editForm.reset();
    editForm.id = props.product.id;
    editForm.name = props.product.name;
    editForm.description = props.product.description;
    editForm.brand_id = props.product.brand_id;
    editForm.category_id = props.product.category_id;
    editForm.stock = props.product.stock;
    editForm.status = props.product.status;
    editForm.purchase_price = props.product.purchase_price;
    editForm.sale_price = props.product.sale_price;
    editForm.feature = props.product.feature;
    editForm.barcode = props.product.barcode;
    editForm.discount = props.product.discount;
    editForm.final_price = props.product.final_price || props.product.sale_price;

    // Reset image previews
    thumnailPreview.value = null;
    gallaryPreviews.value = [];

    // Initialize gallery images
    if (props.product.gallary_img) {
      try {
        const parsedImages = JSON.parse(props.product.gallary_img);
        existingGalleryImages.value = parsedImages;
        editForm.existing_gallary_img = parsedImages;
      } catch (e) {
        console.error('Error parsing gallery images:', e);
        existingGalleryImages.value = [];
        editForm.existing_gallary_img = [];
      }
    }
  }
};

// Watch for modal visibility changes
watch(() => props.isVisible, handleModalVisibility, { immediate: true });



// Computed properties for select options
const brandOptions = computed(() => {
  return props.brands.data?.map((brand) => ({
    value: brand.id,
    label: brand.name,
  })) || [];
});

const categoryTreeData = computed(() => {
  const transformCategory = (category: any): any => {
    const node: any = {
      label: category.name,
      value: category.id,
    };

    if (category.children && category.children.length > 0) {
      node.children = category.children.map(transformCategory);
    }

    return node;
  };

  return props.categories.data?.map(transformCategory) || [];
});

const filterOption = (input: string, option: any) => {
  return option.label.toLowerCase().includes(input.toLowerCase());
};

// Handle thumbnail image change
const handleThumnailChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    editForm.thumnail_img = target.files[0];
    thumnailPreview.value = URL.createObjectURL(target.files[0]);
  }
};

// Handle gallery image change
const handleGallaryChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files) {
    const newFiles = Array.from(target.files);
    editForm.gallary_img = [...editForm.gallary_img, ...newFiles];
    const newPreviews = newFiles.map((file) => URL.createObjectURL(file));
    gallaryPreviews.value = [...gallaryPreviews.value, ...newPreviews];
  }
};

// Remove gallery image
const removeGalleryImage = (index: number, event: Event) => {
  event.preventDefault();
  event.stopPropagation();

  if (index < existingGalleryImages.value.length) {
    existingGalleryImages.value.splice(index, 1);
    editForm.existing_gallary_img = [...existingGalleryImages.value];
  } else {
    const adjustedIndex = index - existingGalleryImages.value.length;
    editForm.gallary_img.splice(adjustedIndex, 1);
    gallaryPreviews.value.splice(adjustedIndex, 1);
  }
};

// Compute final price
const finalPrice = computed(() => {
  if (editForm.sale_price && editForm.discount) {
    const discountAmount = (editForm.sale_price * editForm.discount) / 100;
    return editForm.sale_price - discountAmount;
  }
  return editForm.sale_price || 0;
});

// Update final_price when sale_price or discount changes
watch([() => editForm.sale_price, () => editForm.discount], () => {
  editForm.final_price = finalPrice.value;
});

// Validate and update product
const updateProduct = () => {
  isLoading.value = true;
  editForm.post(route('admin.product.update', { id: editForm.id }), {
    onSuccess: () => {
      emit('update:isVisible', false);
      emit('success');
      thumnailPreview.value = null;
      gallaryPreviews.value = [];
    },
    onFinish: () => {
      isLoading.value = false;
    },
  });
};
</script>

<template>
  <div v-if="isLoading" class="loading-overlay">
    <a-spin size="large" />
  </div>
  <a-modal
    width="1000px"
    style="top: 20px"
    :open="isVisible"
    :title="translations.edit_product || 'Edit Product'"
    @update:open="$emit('update:isVisible', $event)"
    @cancel="$emit('update:isVisible', false)"
    :footer="null"
  >
    <form @submit.prevent="updateProduct" enctype="multipart/form-data">
      <a-row :gutter="16">
        <a-col :span="12">
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
        </a-col>
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.barcode || 'Barcode' }}</label>
            <a-input
              v-model:value="editForm.barcode"
              class="mt-2 w-full"
              :placeholder="translations.enter_barcode || 'Enter Barcode'"
            />
            <div v-if="editForm.errors.barcode" class="text-red-500">
              {{ editForm.errors.barcode }}
            </div>
          </div>
        </a-col>
      </a-row>
      <a-row :gutter="16">
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.category || 'Category' }}</label>
            <a-tree-select
              v-model:value="editForm.category_id"
              show-search
              style="width: 100%"
              :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
              :placeholder="translations.select_category || 'Select Category'"
              allow-clear
              tree-default-expand-all
              :tree-data="categoryTreeData"
              tree-node-filter-prop="label"
            />
            <div v-if="editForm.errors.category_id" class="text-red-500">
              {{ editForm.errors.category_id }}
            </div>
          </div>
        </a-col>
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.brand || 'Brand' }}</label>
            <a-select
              v-model:value="editForm.brand_id"
              show-search
              :placeholder="translations.select_brand || 'Select Brand'"
              class="mt-2 w-full"
              :options="brandOptions"
              :filter-option="filterOption"
            ></a-select>
            <div v-if="editForm.errors.brand_id" class="text-red-500">
              {{ editForm.errors.brand_id }}
            </div>
          </div>
        </a-col>
      </a-row>
      <a-row :gutter="16">
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.purchase_price || 'Purchase Price' }}</label>
            <a-input-number
              v-model:value="editForm.purchase_price"
              class="mt-2 w-full"
              :min="0"
              :step="0.01"
              :placeholder="translations.enter_purchase_price || 'Enter Purchase Price'"
            />
            <div v-if="editForm.errors.purchase_price" class="text-red-500">
              {{ editForm.errors.purchase_price }}
            </div>

          </div>
        </a-col>
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.sale_price || 'Sale Price' }}</label>
            <a-input-number
              v-model:value="editForm.sale_price"
              class="mt-2 w-full"
              :min="0"
              :step="0.01"
              :placeholder="translations.enter_sale_price || 'Enter Sale Price'"
            />
            <div v-if="editForm.errors.sale_price" class="text-red-500">
              {{ editForm.errors.sale_price }}
            </div>
          </div>
        </a-col>
      </a-row>
      <a-row :gutter="16">
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.discount || 'Discount (%)' }}</label>
            <a-input-number
              v-model:value="editForm.discount"
              class="mt-2 w-full"
              :min="0"
              :max="100"
              :step="1"
              :placeholder="translations.enter_discount || 'Enter Discount'"
            />
            <div v-if="editForm.errors.discount" class="text-red-500">
              {{ editForm.errors.discount }}
            </div>
          </div>
        </a-col>
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.final_price || 'Final Price' }}</label>
            <a-input-number
              v-model:value="editForm.final_price"
              class="mt-2 w-full"
              :min="0"
              :step="0.01"
              disabled
            />
            <div v-if="editForm.errors.final_price" class="text-red-500">
              {{ editForm.errors.final_price }}
            </div>
          </div>
        </a-col>
      </a-row>
      <a-row :gutter="16">
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.stock || 'Stock' }}</label>
            <a-input-number
              v-model:value="editForm.stock"
              class="mt-2 w-full"
              :min="0"
              :placeholder="translations.enter_stock || 'Enter Stock'"
            />
            <div v-if="editForm.errors.stock" class="text-red-500">
              {{ editForm.errors.stock }}
            </div>
          </div>
        </a-col>
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.status || 'Status' }}</label>
            <a-select
              v-model:value="editForm.status"
              class="mt-2 w-full"
              :placeholder="translations.select_status || 'Select Status'"
            >
              <a-select-option value="active">{{ translations.active || 'Active' }}</a-select-option>
              <a-select-option value="inactive">{{ translations.inactive || 'Inactive' }}</a-select-option>
            </a-select>
            <div v-if="editForm.errors.status" class="text-red-500">
              {{ editForm.errors.status }}
            </div>
          </div>
        </a-col>
      </a-row>
      <div class="mb-4">
        <label class="block">{{ translations.description || 'Description' }}</label>
        <a-textarea
          v-model:value="editForm.description"
          class="mt-2 w-full"
          :placeholder="translations.enter_description || 'Description'"
          :auto-size="{ minRows: 3, maxRows: 6 }"
        />
        <div v-if="editForm.errors.description" class="text-red-500">
          {{ editForm.errors.description }}
        </div>
      </div>
      <a-row :gutter="16">
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.thumbnail_image || 'Thumbnail Image' }}</label>
            <input
              type="file"
              @change="handleThumnailChange"
              accept="image/*"
              class="mt-2 w-full p-2 border rounded"
            />
            <div v-if="editForm.errors.thumnail_img" class="text-red-500">
              {{ editForm.errors.thumnail_img }}
            </div>
            <div v-if="thumnailPreview" class="mt-2">
              <p class="text-sm text-gray-600 mb-1">{{ translations.preview || 'Preview' }}</p>
              <img
                :src="thumnailPreview"
                alt="Thumbnail Preview"
                class="w-24 h-24 object-cover rounded border"
              />
            </div>
            <div v-else-if="product?.thumnail_img" class="mt-2">
              <p class="text-sm text-gray-600 mb-1">{{ translations.current_image || 'Current Image' }}</p>
              <img
                :src="'/storage/' + product.thumnail_img"
                alt="Current Thumbnail"
                class="w-24 h-24 object-cover rounded border"
              />
            </div>
          </div>
        </a-col>
        <a-col :span="12">
          <div class="mb-4">
            <label class="block">{{ translations.gallery_images || 'Gallery Images' }}</label>
            <input
              type="file"
              multiple
              @change="handleGallaryChange"
              accept="image/*"
              class="mt-2 w-full p-2 border rounded"
            />
            <div v-if="editForm.errors.gallary_img" class="text-red-500">
              {{ editForm.errors.gallary_img }}
            </div>
            <div v-if="existingGalleryImages.length" class="mt-2">
              <p class="text-sm text-gray-600 mb-1">{{ translations.current_images || 'Current Images' }}</p>
              <div class="flex flex-wrap gap-2">
                <div v-for="(image, index) in existingGalleryImages" :key="'existing-' + index" class="relative">
                  <img
                    :src="'/storage/' + image"
                    alt="Current Gallery Image"
                    class="w-24 h-24 object-cover rounded border"
                  />
                  <button
                    @click="removeGalleryImage(index, $event)"
                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600"
                    type="button"
                  >
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              </div>
            </div>
            <div v-if="gallaryPreviews.length" class="mt-2">
              <p class="text-sm text-gray-600 mb-1">{{ translations.preview || 'Preview' }}</p>
              <div class="flex flex-wrap gap-2">
                <div v-for="(preview, index) in gallaryPreviews" :key="'new-' + index" class="relative">
                  <img
                    :src="preview"
                    alt="Gallery Preview"
                    class="w-24 h-24 object-cover rounded border"
                  />
                  <button
                    @click="removeGalleryImage(existingGalleryImages.length + index, $event)"
                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600"
                    type="button"
                  >
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </a-col>
      </a-row>
      <div class="mb-4">
        <label class="flex items-center">
          <a-checkbox v-model:checked="editForm.feature">
            {{ translations.featured || 'Featured' }}
          </a-checkbox>
        </label>
        <div v-if="editForm.errors.feature" class="text-red-500">
          {{ editForm.errors.feature }}
        </div>
      </div>
      <div class="text-right">
        <a-button type="default" @click="$emit('update:isVisible', false)">
          {{ translations.cancel || 'Cancel' }}
        </a-button>
        <Button type="submit" class="btn-primary ml-2" :disabled="editForm.processing">
                    <LoaderCircle v-if="editForm.processing" class="h-4 w-4 animate-spin" />
                    {{ translations.update || 'Update' }}
                </Button>
      </div>
    </form>
  </a-modal>
</template>

<style scoped>

</style>
