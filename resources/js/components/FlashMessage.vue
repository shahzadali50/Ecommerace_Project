<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue'; // Make sure this import exists
import { message } from 'ant-design-vue';

const page = usePage();

// Watch for flash messages dynamically
watch(() => page.props.flash, (flash) => {
    if (flash?.message) {
        message.success(flash.message);
    }
    if (flash?.error) {
        message.error(flash.error);
    }
});
</script>

<template>
    <div style="display: none;"></div>
</template>
