<script setup lang="ts">
import {
    Button
} from 'ant-design-vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { getCurrentInstance, } from 'vue';

const page = usePage();
const translations = computed(() => {
    return (page.props.translations as any)?.banner || {};
});
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;
</script>

<template>
    <section class=" bg-gradient-to-br from-blue-100 via-blue-200 to-blue-300  py-14 px-4">

        <div class="container mx-auto">
            <a-row :gutter="[16, 16]" class="flex items-center">
                <!-- Image Column -->
                <a-col :xs="24" :md="12" class="order-1 md:order-2">
                    <img src="\assets\images\banner.webp" alt="LaCuna Logo" class="max-w-[500px] h-auto" loading="lazy" />
                </a-col>

                <!-- Text Column -->
                <a-col :xs="24" :md="12" class="order-2 md:order-1">

                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold  text-primary">
                        {{ translations.title || 'Welcome to LaCuna Marketplace' }}
                        {{ t('Choose your preferred language') }}:

                    </h1>
                    <p class="text-lg sm:text-xl text-blue-800/80">
                         {{ translations.subtitle || 'A global marketplace connecting cultures, services, and innovation.' }}
                    </p>
                    <a :href="translations.cta_link || '#'" class="inline-block">
                        <Button size="large"
                            class="btn-primary">
                            {{ translations.cta_text || 'Start Exploring' }}
                        </Button>
                    </a>
                </a-col>
            </a-row>


        </div>
    </section>
</template>
