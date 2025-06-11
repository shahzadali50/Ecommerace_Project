<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import {  GlobalOutlined } from '@ant-design/icons-vue';
import { MenuProps } from 'ant-design-vue';

const { props } = usePage();

const availableLocales = [
  { code: 'en', name: 'English' },
//   { code: 'es', name: 'Español' },
//   { code: 'fr', name: 'Français' },
//   { code: 'de', name: 'Deutsch' },
  { code: 'pt', name: 'Português' },
  { code: 'ja', name: '日本語' },
];

const switchLanguage = (locale: string) => {
  router.visit(route("language.switch", { locale }), {
    method: "get",
    preserveScroll: true,
  });
};

// Handle dropdown click
const handleMenuClick: MenuProps['onClick'] = (info) => {
  switchLanguage(info.key);
};
</script>

<template>
<a-dropdown trigger="click">
  <template #overlay>
    <a-menu @click="handleMenuClick">
      <a-menu-item
        v-for="locale in availableLocales"
        :key="locale.code"
        :class="{ 'bg-gray-100': locale.code === props.currentLocale }"
      >
        {{ locale.name }}
      </a-menu-item>
    </a-menu>
  </template>

  <a-button
    type="text"
    class="flex items-center gap-1 focus:outline-none hover:bg-transparent mt-1"
    aria-label="Select language"
  >
    <GlobalOutlined />
    <!-- <span class="uppercase">{{ props.currentLocale }}</span> -->
    <!-- <DownOutlined /> -->
  </a-button>
</a-dropdown>

</template>
