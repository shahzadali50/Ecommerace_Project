<script setup lang="ts">
import Sidebar from "@/components/admin/Sidebar.vue";
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useFlashMessages } from '@/composables/useFlashMessages';
import { router } from '@inertiajs/vue3'
import {
    FilterOutlined
} from '@ant-design/icons-vue';
import LanguageSwitcher from "@/components/LanguageSwitcher.vue";
useFlashMessages();
// State for sidebar
const isMobile = ref(window.innerWidth <= 992);
const collapsed = ref(isMobile.value);

// Handle window resize
const handleResize = () => {
    isMobile.value = window.innerWidth <= 992;
    collapsed.value = isMobile.value;
};
// Close sidebar when clicking outside (on mobile)
const closeSidebar = (event: MouseEvent) => {
    if (isMobile.value && !event.target.closest('.sidebar, .trigger')) {
        collapsed.value = true;
    }
};
// Add event listeners
onMounted(() => {
    window.addEventListener("resize", handleResize);
    document.addEventListener("click", closeSidebar);
});

// Cleanup event listeners
onBeforeUnmount(() => {
    window.removeEventListener("resize", handleResize);
    document.removeEventListener("click", closeSidebar);
});
const clearCache = () => {
  router.visit(route('admin.cache.clear'), {
    method: 'get',
    preserveScroll: true,
    preserveState: true,
  });
};

</script>

<template>

    <Head title="Dashboard" />
    <a-layout>
        <!-- Sidebar -->
        <div class="sidebar-wrapper" :class="{ 'overlay': isMobile && !collapsed }">
            <div class="sidebar" :class="{ collapsed: collapsed }">
                <Sidebar :collapsed="collapsed" />
            </div>
        </div>

        <!-- Main Layout -->
        <a-layout :style="{ height: '100vh', marginLeft: isMobile ? '0' : collapsed ? '0' : '200px' }" style="z-index: 200;">
            <!-- Header -->
            <a-layout-header class="header">
                <div class="trigger-button mr-2">
                    <i class="fa trigger" :class="collapsed ? 'fa fa-align-right' : 'fa fa-align-left'"
                        @click.stop="collapsed = !collapsed"></i>
                </div>
                <a-button  @click="clearCache"  type="dashed" danger> <FilterOutlined />Clear Cache</a-button>
                <LanguageSwitcher />

            </a-layout-header>

            <!-- Content -->
            <a-layout-content class="content">
                <slot />
            </a-layout-content>
        </a-layout>
    </a-layout>
</template>

<style scoped>
/* Header */
.header {
    padding: 0 10px;
}
.trigger-button {
    z-index: 1200;
    cursor: pointer;
    transition: background 0.3s ease-in-out;
}

/* Trigger Button */
.fa {
    font-size: 18px;
    cursor: pointer;
    transition: transform 0.3s ease, color 0.3s ease;
    color: rgb(0, 0, 0);
}

/* Sidebar Wrapper */
.sidebar-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    transform: translateX(0);
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
    opacity: 1;
}

.sidebar.collapsed {
    transform: translateX(-100%);
    opacity: 0;
}

/* Overlay Effect */
.overlay {
    width: 100vw;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    transition: opacity 0.3s ease-in-out;
}

/* Sidebar */
.sidebar {
    width: 200px;
    height: 100vh;
    background: #001529;
    z-index: 1100;
    transform: translateX(0);
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
    opacity: 1;
}

.sidebar.collapsed {
    transform: translateX(-100%);
    opacity: 0;
}
</style>
