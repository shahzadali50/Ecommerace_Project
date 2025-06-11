<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';

DataTable.use(DataTablesCore);
defineProps<{
  CategoryLog: {
    data: Array<any>;
  };
}>();

const formatDate = (date: string) => {
  return date ? dayjs(date).format('DD-MM-YYYY hh:mm A') : 'N/A';
};

// Columns can be dynamic, just update this array as needed
const dataTableColumns = [
  { title: 'ID', data: 'id' },
  { title: 'Note', data: 'note' },
  { title: 'Category', data: 'category_name', render: (data: string | undefined) => data ?? 'Deleted Category' },
  { title: 'User', data: 'user', render: (data: { name: string } | undefined) => data?.name ?? 'N/A' },
  { title: 'Created At', data: 'created_at', render: (data: string) => formatDate(data) },
];

const options = {
  paging: true,
  searching: true,
  ordering: true,
  responsive: true,
};
</script>

<template>
  <AdminLayout>
    <Head title="Category Logs" />
    <a-row>
      <a-col :span="24">
        <div class="bg-white rounded-lg responsive-table p-4 shadow-md">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Category Logs</h2>
            <Link :href="route('admin.categories')">
              <a-button type="default">Back</a-button>
            </Link>
          </div>
          <DataTable
            v-if="CategoryLog"
            :data="CategoryLog.data"
            :columns="dataTableColumns"
            :options="options"
            class="display"
          >
          </DataTable>
        </div>
      </a-col>
    </a-row>
  </AdminLayout>
</template>

<style>
.display {
  width: 100%;
}
</style>
