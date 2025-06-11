<script setup lang="ts">
import { defineAsyncComponent } from 'vue';
import { useFlashMessages } from '@/composables/useFlashMessages';
import Header from '@/components/frontend/Header.vue';
const Footer = defineAsyncComponent(() => import('@/components/frontend/Footer.vue'));
useFlashMessages();
</script>
<template>
   <Header />
        <main>
            <slot />
        </main>
        <Footer />
</template>
<style scoped></style>
