<script setup lang="ts">
import { ref, computed } from 'vue';
import { FilterOutlined, ShoppingCartOutlined } from '@ant-design/icons-vue';
import DashboardCard from '@/components/admin/DashboardCard.vue';
import dayjs from 'dayjs';
import isBetween from 'dayjs/plugin/isBetween';
import { getCurrentInstance } from 'vue';

interface Order {
    id: number;
    created_at: string;
}

interface Props {
    orders: Order[];
}

const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

dayjs.extend(isBetween);

const props = defineProps<Props>();

const filters = ref({
    start_date: null,
    end_date: null,
});

// Monthly Orders Count
const monthlyOrders = computed(() => {
    const thisMonth = dayjs().month();
    return props.orders.filter(order => dayjs(order.created_at).month() === thisMonth).length;
});

// Weekly Orders Count
const weeklyOrders = computed(() => {
    const startOfWeek = dayjs().startOf('week');
    return props.orders.filter(order => dayjs(order.created_at).isAfter(startOfWeek)).length;
});

// Today's Orders Count
const todayOrders = computed(() => {
    const today = dayjs().format('YYYY-MM-DD');
    return props.orders.filter(order => dayjs(order.created_at).format('YYYY-MM-DD') === today).length;
});

// Filtered Orders Count (Custom Date Range)
const filteredOrders = computed(() => {
    if (!filters.value.start_date || !filters.value.end_date) {
        return props.orders.length;
    }

    const start = dayjs(filters.value.start_date).startOf('day');
    const end = dayjs(filters.value.end_date).endOf('day');

    return props.orders.filter(order => {
        const orderDate = dayjs(order.created_at);
        return orderDate.isValid() && orderDate.isBetween(start, end, null, '[]');
    }).length;
});
</script>

<template>
    <div class="mt-5" style="background-color: #ececec; padding: 20px">
        <a-row :gutter="[16, 16]">
            <a-col :lg="24" :md="24" :sm="24" :xs="24">
                <h2 class="text-2xl">{{ t('Orders') }}</h2>
            </a-col>
            <a-col :lg="6" :sm="12" :xs="24">
                <DashboardCard
                    :title="t('Monthly Orders')"
                    :value="monthlyOrders"
                    :icon="ShoppingCartOutlined"
                    bgColor="bg-green-700"
                />
            </a-col>
            <a-col :lg="6" :sm="12" :xs="24">
                <DashboardCard
                    :title="t('Weekly Orders')"
                    :value="weeklyOrders"
                    :icon="ShoppingCartOutlined"
                    bgColor="bg-green-700"
                />
            </a-col>
            <a-col :lg="6" :sm="12" :xs="24">
                <DashboardCard
                    :title="t('Today\'s Orders')"
                    :value="todayOrders"
                    :icon="ShoppingCartOutlined"
                    bgColor="bg-green-700"
                />
            </a-col>
            <a-col :lg="24">
                <h5>{{ t('Orders Filter By Date') }}</h5>
                <div class="flex">
                    <a-date-picker
                        class="mx-1"
                        v-model:value="filters.start_date"
                        :placeholder="t('Start Date')"
                    />
                    <a-date-picker
                        class="mx-1"
                        v-model:value="filters.end_date"
                        :placeholder="t('End Date')"
                    />
                </div>
            </a-col>
            <a-col :lg="6" :xs="24">
                <DashboardCard
                    :title="t('Filtered Orders')"
                    :value="filteredOrders"
                    :icon="FilterOutlined"
                    bgColor="bg-slate-600"
                />
            </a-col>
        </a-row>
    </div>
</template>
