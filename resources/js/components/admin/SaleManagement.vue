<script setup lang="ts">
import { ref, computed } from 'vue';
import { FilterOutlined, DollarCircleOutlined } from '@ant-design/icons-vue';
import DashboardCard from '@/components/admin/DashboardCard.vue';
import dayjs from 'dayjs';
import isBetween from 'dayjs/plugin/isBetween';
import { usePage } from '@inertiajs/vue3';

interface Order {
    id: number;
    total_price: string | number;
    created_at: string;
}

interface Props {
    orders: Order[];
}

interface Translations {
    dashboardMainPage?: {
        sales?: string;
        todays_sale?: string;
        weekly_sale?: string;
        monthly_sale?: string;
        sale_filter_by_date?: string;
        start_date?: string;
        end_date?: string;
        filtered_revenue?: string;
    };
}

const page = usePage();
const translations = computed(() => {
    return (page.props.translations as Translations)?.dashboardMainPage || {};
});

dayjs.extend(isBetween);

const props = defineProps<Props>();

const filters = ref({
    start_date: null,
    end_date: null,
});

// **Total Sales**
const totalSales = computed(() =>
    props.orders.reduce((sum, order) => sum + Number(order.total_price), 0)
);

// **Monthly Sales**
const monthlySales = computed(() => {
    const thisMonth = dayjs().month();
    return props.orders.reduce((sum, order) => {
        const orderDate = dayjs(order.created_at);
        return orderDate.isValid() && orderDate.month() === thisMonth
            ? sum + Number(order.total_price)
            : sum;
    }, 0);
});

// **Weekly Sales**
const weeklySales = computed(() => {
    const startOfWeek = dayjs().startOf('week');
    return props.orders.reduce((sum, order) => {
        const orderDate = dayjs(order.created_at);
        return orderDate.isValid() && orderDate.isAfter(startOfWeek)
            ? sum + Number(order.total_price)
            : sum;
    }, 0);
});

// **Today's Sales**
const todaySales = computed(() => {
    const today = dayjs().format('YYYY-MM-DD');
    return props.orders.reduce((sum, order) => {
        return dayjs(order.created_at).format('YYYY-MM-DD') === today
            ? sum + Number(order.total_price)
            : sum;
    }, 0);
});

// **Filtered Revenue (Custom Date Range)**
const filteredRevenue = computed(() => {
    if (!filters.value.start_date || !filters.value.end_date) {
        return totalSales.value;
    }

    const start = dayjs(filters.value.start_date).startOf('day');
    const end = dayjs(filters.value.end_date).endOf('day');

    return props.orders.reduce((sum, order) => {
        const orderDate = dayjs(order.created_at);
        return orderDate.isValid() && orderDate.isBetween(start, end, null, '[]')
            ? sum + Number(order.total_price)
            : sum;
    }, 0);
});
</script>

<template>
    <div style="background-color: #ececec; padding: 20px">
        <a-row :gutter="[16, 16]">
            <a-col :xs="24">
                <h2 class="text-2xl">{{ translations.sales || 'Sales' }}</h2>
            </a-col>
            <a-col :lg="6" :sm="12" :xs="24">
                <DashboardCard
                    :title="translations.todays_sale || 'Today\'s Sale'"
                    :value="todaySales"
                    :icon="DollarCircleOutlined"
                    bgColor="bg-cyan-600"
                />
            </a-col>
            <a-col :lg="6" :sm="12" :xs="24">
                <DashboardCard
                    :title="translations.weekly_sale || 'Weekly Sale'"
                    :value="weeklySales"
                    :icon="DollarCircleOutlined"
                    bgColor="bg-cyan-600"
                />
            </a-col>
            <a-col :lg="6" :sm="12" :xs="24">
                <DashboardCard
                    :title="translations.monthly_sale || 'Monthly Sale'"
                    :value="monthlySales"
                    :icon="DollarCircleOutlined"
                    bgColor="bg-cyan-600"
                />
            </a-col>

            <a-col :xs="24">
                <h5>{{ translations.sale_filter_by_date || 'Sale Filter By Date' }}</h5>
                <div class="flex">
                    <a-date-picker
                        class="mx-1"
                        v-model:value="filters.start_date"
                        :placeholder="translations.start_date || 'Start Date'"
                    />
                    <a-date-picker
                        class="mx-1"
                        v-model:value="filters.end_date"
                        :placeholder="translations.end_date || 'End Date'"
                    />
                </div>
            </a-col>
            <a-col :lg="6" :xs="24">
                <DashboardCard
                    :title="translations.filtered_revenue || 'Filtered Revenue'"
                    :value="filteredRevenue"
                    :icon="FilterOutlined"
                    bgColor="bg-slate-600"
                />
            </a-col>
        </a-row>
    </div>
</template>
