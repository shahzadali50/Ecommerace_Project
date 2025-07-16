<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import dayjs from "dayjs";
import { getCurrentInstance } from 'vue';

DataTable.use(DataTablesCore);

// Props Definition
defineProps<{
    users: { data: Array<any> };
}>();

// State Management
const isLoading = ref(false);

// Global translation function
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

// Format date
const formatDate = (date: string) => {
    return date ? dayjs(date).format("DD-MM-YYYY hh:mm A") : "N/A";
};

// DataTable columns for users
const dataTableColumns = [
    {
        title: t('Sr.'),
        data: null,
        render: (data: any, type: any, row: any, meta: any) => meta.row + 1,
    },
    { title: t('Name'), data: 'name' },
    { title: t('Email'), data: 'email' },
    {
        title: t('Created At'),
        data: 'created_at',
        render: (data: string) => formatDate(data),
    },
];

// DataTable options
const options = {
    paging: true,
    searching: true,
    ordering: true,
    responsive: true,
    pageLength: 10,
};
</script>

<template>
    <div v-if="isLoading" class="loading-overlay">
        <a-spin size="large" />
    </div>
    <AdminLayout>
        <Head :title="t('User List')" />
        <a-row>
            <a-col :span="24">
                <div class="bg-white rounded-lg p-4 shadow-md responsive-table">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ t('User List') }}</h2>
                    </div>
                    <DataTable
                        v-if="users?.data"
                        :data="users.data"
                        :columns="dataTableColumns"
                        :options="options"
                        class="display"
                    >
                        <thead>
                            <tr>
                                <th>{{ t('Sr.') }}</th>
                                <th>{{ t('Name') }}</th>
                                <th>{{ t('Email') }}</th>
                                <th>{{ t('Created At') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this automatically -->
                        </tbody>
                    </DataTable>
                </div>
            </a-col>
        </a-row>
    </AdminLayout>
</template>

<style scoped>
.display {
    width: 100%;
}
</style>
