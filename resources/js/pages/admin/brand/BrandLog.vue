<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import dayjs from "dayjs";
import { Link } from '@inertiajs/vue3';

const formatDate = (date: string) => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};

const columns = [
    { title: 'ID', dataIndex: 'id', key: 'id' },
    { title: 'Note', dataIndex: 'note', key: 'note' },
    { title: 'Brand', dataIndex: 'brand_name', key: 'category' }, // ✅ Fixed key
    { title: 'User', dataIndex: 'user_name', key: 'user' }, // ✅ Fixed key
    { title: 'Created At', dataIndex: 'created_at', key: 'created_at' },
];

defineProps({
    BrandLog: Object, // ✅ Ensure correct prop name
});
</script>

<template>
    <AdminLayout>
    <Head title="Category Logs" />
        <a-row>
            <a-col :span="24">
                <div class="bg-white rounded-lg p-4 shadow-md responsive-table">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Brand Logs</h2>
                        <Link :href="route('admin.brands')" >
                            <a-button type="default">Brand List</a-button>
                        </Link>

                    </div>

                    <a-table v-if="BrandLog" :columns="columns" :data-source="BrandLog.data" rowKey="id">
                        <template #bodyCell="{ column, record,index  }">
                            <template v-if="column.dataIndex === 'id'">
                                {{ index + 1 }}
                            </template>
                            <template v-else-if="column.dataIndex === 'note'">
                                {{ record.note }}
                            </template>
                            <template v-else-if="column.dataIndex === 'brand_name'">
                                {{ record.brand_name ?? 'Deleted Category' }}
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
