<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import { LoaderCircle } from "lucide-vue-next";

const props = defineProps<{
  isVisible: boolean;
  categories: Array<{ id: number; name: string; children?: Array<any> }>;
  brands: Array<{ id: number; name: string; category_id: number }>;
  translations: Record<string, string>;
}>();

const emit = defineEmits(["update:isVisible", "success"]);

const isLoading = ref(false);
const thumnailPreview = ref<string | null>(null);
const gallaryPreviews = ref<string[]>([]);

const addProductForm = useForm({
  name: "",
  description: "",
  brand_id: null,
  category_id: null,
  thumnail_img: null as File | null,
  gallary_img: [] as File[],
  stock: 0,
  status: "active",
  purchase_price: null as number | null,
  sale_price: null as number | null,
  feature: false,
  barcode: "",
  discount: 0,
  final_price: 0,
});

const brandOptions = computed(() => {
  return (
    props.brands?.map((brand) => ({
      value: brand.id,
      label: brand.name,
    })) || []
  );
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

  return props.categories?.map(transformCategory) || [];
});

const filterOption = (input: string, option: any) => {
  return option.label.toLowerCase().includes(input.toLowerCase());
};

const handleThumnailChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    addProductForm.thumnail_img = target.files[0];
    thumnailPreview.value = URL.createObjectURL(target.files[0]);
  }
};

const handleGallaryChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files) {
    // Append new files to existing ones
    const newFiles = Array.from(target.files);
    addProductForm.gallary_img = [...addProductForm.gallary_img, ...newFiles];

    // Create previews for new files and append to existing previews
    const newPreviews = newFiles.map((file) => URL.createObjectURL(file));
    gallaryPreviews.value = [...gallaryPreviews.value, ...newPreviews];
  }
};

const removeGalleryImage = (index: number, event: Event) => {
  event.preventDefault();
  event.stopPropagation();
  // Remove the file from the form data
  addProductForm.gallary_img.splice(index, 1);
  // Remove the preview
  gallaryPreviews.value.splice(index, 1);
};

const finalPrice = computed(() => {
  if (addProductForm.sale_price && addProductForm.discount) {
    const discountAmount = (addProductForm.sale_price * addProductForm.discount) / 100;
    return addProductForm.sale_price - discountAmount;
  }
  return addProductForm.sale_price;
});

watch([() => addProductForm.sale_price, () => addProductForm.discount], () => {
  addProductForm.final_price = finalPrice.value;
});

const saveProduct = () => {
  addProductForm.post(route("admin.product.store"), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      addProductForm.reset();
      emit("update:isVisible", false);
      emit("success");
      thumnailPreview.value = null;
      gallaryPreviews.value = [];
    },
    onFinish: () => {},
  });
};
</script>

<template>
  <div v-if="isLoading" class="loading-overlay">
    <a-spin size="large" />
  </div>
    <a-modal
        width="1000px"
        :open="isVisible"
        :title="translations.add_product || 'Add Product'"
        @update:open="$emit('update:isVisible', $event)"
        @cancel="$emit('update:isVisible', false)"
        :footer="null"
    >
        <form @submit.prevent="saveProduct">
            <a-row :gutter="16">
                <a-col :span="12">
                    <div class="mb-4">
                        <label class="block">{{ translations.select_category || 'Select Category' }}</label>
                        <a-tree-select
                            v-model:value="addProductForm.category_id"
                            show-search
                            style="width: 100%"
                            :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
                            :placeholder="translations.select_category || 'Select Category'"
                            allow-clear
                            tree-default-expand-all
                            :tree-data="categoryTreeData"
                            tree-node-filter-prop="label"
                        />
                        <div v-if="addProductForm.errors.category_id" class="text-red-500">
                            {{ addProductForm.errors.category_id }}
                        </div>
                    </div>
                </a-col>
                <a-col :span="12">
                    <div class="mb-4">
                        <label class="block">{{ translations.select_brand || 'Select Brand' }}</label>
                        <a-select
                            v-model:value="addProductForm.brand_id"
                            show-search
                            :placeholder="translations.select_brand || 'Select Brand'"
                            class="mt-2 w-full"
                            :options="brandOptions"
                            :filter-option="filterOption"
                        ></a-select>
                        <div v-if="addProductForm.errors.brand_id" class="text-red-500">
                            {{ addProductForm.errors.brand_id }}
                        </div>
                    </div>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :span="12">
                    <div class="mb-4">
                        <label class="block">{{ translations.enter_product_name || 'Enter Product Name' }}</label>
                        <a-input
                            v-model:value="addProductForm.name"
                            class="mt-2 w-full"
                            :placeholder="translations.enter_product_name || 'Enter Product Name'"
                        />
                        <div v-if="addProductForm.errors.name" class="text-red-500">
                            {{ addProductForm.errors.name }}
                        </div>
                    </div>
                </a-col>
                <a-col :span="12">
                    <div class="mb-4">
                        <label class="block">{{ translations.enter_barcode || 'Enter Barcode' }}</label>
                        <a-input
                            v-model:value="addProductForm.barcode"
                            class="mt-2 w-full"
                            :placeholder="translations.enter_barcode || 'Enter Barcode'"
                        />
                        <div v-if="addProductForm.errors.barcode" class="text-red-500">
                            {{ addProductForm.errors.barcode }}
                        </div>
                    </div>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :span="12">
                    <div class="mb-4">
                        <label class="block">{{ translations.enter_purchase_price || 'Enter Purchase Price' }}</label>
                        <a-input-number
                            v-model:value="addProductForm.purchase_price"
                            class="mt-2 w-full"
                            :min="0"
                            type="number"
                            :placeholder="translations.enter_purchase_price || 'Enter Purchase Price'"
                        />
                        <div v-if="addProductForm.errors.purchase_price" class="text-red-500">
                            {{ addProductForm.errors.purchase_price }}
                        </div>
                    </div>
                </a-col>
                <a-col :span="12">
                    <div class="mb-4">
                        <label class="block">{{ translations.enter_sale_price || 'Sale Price' }}</label>
                        <a-input-number
                            v-model:value="addProductForm.sale_price"
                            class="mt-2 w-full"
                            :min="0"
                            type="number"
                            :placeholder="translations.enter_sale_price || 'Enter Sale Price'"
                        />
                        <div v-if="addProductForm.errors.sale_price" class="text-red-500">
                            {{ addProductForm.errors.sale_price }}
                        </div>
                    </div>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :span="12">
                    <div class="mb-4">
                        <label class="block">{{ translations.stock || 'Stock' }}</label>
                        <a-input-number
                            v-model:value="addProductForm.stock"
                            class="mt-2 w-full"
                            :min="0"
                            :placeholder="translations.enter_stock || 'Enter Stock'"
                        />
                        <div v-if="addProductForm.errors.stock" class="text-red-500">
                            {{ addProductForm.errors.stock }}
                        </div>
                    </div>
                </a-col>
                <a-col :span="12">
                    <div class="mb-4">
                        <label class="block">{{ translations.select_status || 'Select Status' }}</label>
                        <a-select
                            v-model:value="addProductForm.status"
                            class="mt-2 w-full"
                            :placeholder="translations.select_status || 'Select Status'"
                        >
                            <a-select-option value="active">{{ translations.active || 'Active' }}</a-select-option>
                            <a-select-option value="inactive">{{ translations.inactive || 'Inactive' }}</a-select-option>
                        </a-select>
                        <div v-if="addProductForm.errors.status" class="text-red-500">
                            {{ addProductForm.errors.status }}
                        </div>
                    </div>
                </a-col>
            </a-row>
            <div class="mb-4">
                <label class="block">{{ translations.enter_description || 'Enter Description' }}</label>
                <a-textarea
                    v-model:value="addProductForm.description"
                    class="mt-2 w-full"
                    :placeholder="translations.enter_description || 'Enter Description'"
                    :auto-size="{ minRows: 3, maxRows: 6 }"
                />
                <div v-if="addProductForm.errors.description" class="text-red-500">
                    {{ addProductForm.errors.description }}
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
                        <div v-if="addProductForm.errors.thumnail_img" class="text-red-500">
                            {{ addProductForm.errors.thumnail_img }}
                        </div>
                        <div v-if="thumnailPreview" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">{{ translations.preview || 'Preview' }}</p>
                            <img
                                :src="thumnailPreview"
                                alt="Thumbnail Preview"
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
                        <div v-if="addProductForm.errors.gallary_img" class="text-red-500">
                            {{ addProductForm.errors.gallary_img }}
                        </div>
                        <div v-if="gallaryPreviews.length" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">{{ translations.preview || 'Preview' }}</p>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="(preview, index) in gallaryPreviews" :key="index" class="relative">
                                    <img
                                        :src="preview"
                                        alt="Gallery Preview"
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
                    </div>
                </a-col>
            </a-row>
            <div class="mb-4">
                <label class="flex items-center">
                    <a-checkbox v-model:checked="addProductForm.feature">
                        {{ translations.featured || 'Featured' }}
                    </a-checkbox>
                </label>
                <div v-if="addProductForm.errors.feature" class="text-red-500">
                    {{ addProductForm.errors.feature }}
                </div>
            </div>
            <a-row :gutter="16">
                <a-col :xs="24" :md="12">
                    <div class="mb-4">
                        <label class="block">{{ translations.enter_discount || 'Enter Discount' }}</label>
                        <a-input-number
                            v-model:value="addProductForm.discount"
                            class="mt-2 w-full"
                            :min="0"
                            :max="100"
                            :step="1"
                            type="number"
                        />
                        <div v-if="addProductForm.errors.discount" class="text-red-500">
                            {{ addProductForm.errors.discount }}
                        </div>
                    </div>
                </a-col>
                <a-col :xs="24" :md="12">
                    <div class="mb-4">
                        <label class="block font-bold " >{{ translations.final_price || 'Final Price' }}</label>
                        <a-input-number
                            v-model:value="addProductForm.final_price"
                            class="mt-2 w-full text-2xl font-bold p-2 text-red-600"
                            :min="0"
                            disabled
                        />
                    </div>
                </a-col>
            </a-row>
            <div class="text-right">
                  <button type="submit"  class=" btn  btn-primary ml-2 w-full h-16 flex items-center justify-center text-lg" :disabled="addProductForm.processing">
                    <LoaderCircle v-if="addProductForm.processing" class="h-4 w-4 animate-spin" />
                     {{ translations.save || 'Save' }}
                </button>
            </div>
        </form>
    </a-modal>
</template>
