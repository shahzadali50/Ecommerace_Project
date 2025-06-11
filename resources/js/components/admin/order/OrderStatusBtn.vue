<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const currentPath = computed(() => page.url);
</script>

<template>
    <div>
       <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.order.list', {}, false)) ? 'btn-primary' : ' btn-outline-dark'"
      :href="route('admin.order.list')"
    >
      <i class="fa fa-cart-plus" aria-hidden="true"></i> All Orders
    </Link>
    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.today.order.list', {}, false)) ? 'btn-primary' : 'btn-outline-dark'"
      :href="route('admin.today.order.list')"
    >
      <i class="fa fa-cart-plus" aria-hidden="true"></i> Today Orders
    </Link>
    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.orders.last3days', {}, false)) ? 'btn-primary' : 'btn-outline-dark'"
      :href="route('admin.orders.last3days')"
    >
      <i class="fa fa-cart-plus" aria-hidden="true"></i> Last 3 Days Orders
    </Link>
    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.orders.last7days', {}, false)) ? 'btn-primary' : 'btn-outline-dark'"
      :href="route('admin.orders.last7days')"
    >
      <i class="fa fa-cart-plus" aria-hidden="true"></i> Last 7 Days Orders
    </Link>
    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.orders.last30days', {}, false)) ? 'btn-primary' : 'btn-outline-dark'"
      :href="route('admin.orders.last30days')"
    >
      <i class="fa fa-cart-plus" aria-hidden="true"></i> Last 30 Days Orders
    </Link>
    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.orders.thisMonth', {}, false)) ? 'btn-primary' : 'btn-outline-dark'"
      :href="route('admin.orders.thisMonth')"
    >
      <i class="fa fa-cart-plus" aria-hidden="true"></i> Current Month Orders
    </Link>

    </div>
  <div>

    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.pending.orders', {}, false)) ? 'btn-warning' : 'btn-outline-dark'"
      :href="route('admin.pending.orders')"
    >
      <i class="fa fa-clock-o"></i> Pending Orders
    </Link>

    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.processing.orders', {}, false)) ? 'btn-primary' : 'btn-outline-dark'"
      :href="route('admin.processing.orders')"
    >
      <i class="fa fa-cogs"></i> Processing Orders
    </Link>

    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.dispatched.orders', {}, false)) ? 'btn-primary' : 'btn-outline-dark'"
      :href="route('admin.dispatched.orders')"
    >
      <i class="fa fa-truck"></i> Dispatched Orders
    </Link>

    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.delivered.orders', {}, false)) ? 'btn-success' : 'btn-outline-dark'"
      :href="route('admin.delivered.orders')"
    >
      <i class="fa fa-check-circle"></i> Delivered Orders
    </Link>

    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.cancelled.orders', {}, false)) ? 'btn-danger' : 'btn-outline-dark'"
      :href="route('admin.cancelled.orders')"
    >
      <i class="fa fa-times-circle"></i> Cancelled Orders
    </Link>

    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.returned.orders', {}, false)) ? 'btn-secondary' : 'btn-outline-dark'"
      :href="route('admin.returned.orders')"
    >
      <i class="fa fa-undo"></i> Returned Orders
    </Link>

    <Link
      class="btn m-1"
      :class="currentPath.startsWith(route('admin.refunded.orders', {}, false)) ? 'btn-dark' : 'btn-outline-dark'"
      :href="route('admin.refunded.orders')"
    >
      <i class="fa fa-money"></i> Refunded Orders
    </Link>
  </div>
</template>

<style scoped>
/* Optional: spacing for consistent layout */
.btn {
  min-width: 180px;
}
</style>
