<script setup lang="ts">
import { getCurrentInstance } from 'vue';

const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-600 dark:text-red-500">
            {{ message ? t(message) : '' }}
        </p>
    </div>
</template>
