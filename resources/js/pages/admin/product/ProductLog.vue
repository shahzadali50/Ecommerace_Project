<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import dayjs from "dayjs";
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const formatDate = (date: string) => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};

const page = usePage();
const translations = computed(() => {
    return (page.props.translations as any)?.dashboard_all_pages || {};
});

const columns = [
    { title: translations.value.id || 'ID', dataIndex: 'id', key: 'id' },
    { title: translations.value.note || 'Note', dataIndex: 'note', key: 'note' },
    { title: translations.value.product || 'Product', dataIndex: 'product_name', key: 'product_name' },
    { title: translations.value.user || 'User', dataIndex: 'user_name', key: 'user' },
    { title: translations.value.created_at || 'Created At', dataIndex: 'created_at', key: 'created_at' },
];

defineProps({
    productLog: Object, // ✅ Ensure correct prop name
});
</script>

<template>
    <AdminLayout>
    <Head title="Category Logs" />
        <a-row>
            <a-col :span="24">
                <div class="bg-white rounded-lg p-4 shadow-md responsive-table">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ translations.product_logs || 'Product Logs' }}</h2>
                        <Link :href="route('admin.products')" >
                            <a-button type="default">{{ translations.product_list || 'Product List' }}</a-button>
                        </Link>

                    </div>

                    <a-table v-if="productLog" :columns="columns" :data-source="productLog.data" rowKey="id">
                        <template #bodyCell="{ column, record,index  }">
                            <template v-if="column.dataIndex === 'id'">
                                {{ index + 1 }}
                            </template>
                            <template v-else-if="column.dataIndex === 'note'">
                                {{ record.note }}
                            </template>
                            <template v-else-if="column.dataIndex === 'product_name'">
                                {{ record.product_name ?? 'Deleted Category' }}
                            </template>
                            <template v-else-if="column.dataIndex === 'user_name'">
                                {{ record.user?.name ?? 'N/A' }}
                            </template>
                            <template v-else-if="column.dataIndex === 'created_at'">
                                {{ formatDate(record.created_at) }}
                            </template>
                        </template>
                    </a-table>

                </div>
            </a-col>
        </a-row>
    </AdminLayout>
</template>
