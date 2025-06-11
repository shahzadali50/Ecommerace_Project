<script setup lang="ts">
import { defineProps } from 'vue';

defineProps<{
    title: string;
    value: number | string;
    icon: any;
    bgColor?: string;
}>();
</script>

<template>
    <a-card class="dashboard-card">
        <div class="flex justify-between">
            <div>
                <h3 class="text-sm text-gray-400">{{ title }} </h3>
                <h4 class="text-xl font-bold mb-0">{{ value }}</h4>
            </div>
            <div class="flex justify-end">
                <div class="badge-icon flex justify-center items-center text-white" :class="bgColor">
                    <component :is="icon" />
                </div>
            </div>
        </div>
    </a-card>
</template>

<style scoped>
.dashboard-card  .badge-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

@media only screen and (max-width: 576px) {
  .dashboard-card  .badge-icon {
    width: 30px;
    height: 30px;

}

}
</style>
