<script setup lang="ts">
import {
    Button
} from 'ant-design-vue';
import { getCurrentInstance } from 'vue';

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
                        {{ t('Welcome to LaCuna Marketplace') }}
                    </h1>
                    <p class="text-lg sm:text-xl text-blue-800/80">
                         {{ t('A global marketplace connecting cultures, services, and innovation.') }}
                    </p>
                    <a href="#" class="inline-block">
                        <Button size="large"
                            class="btn-primary">
                            {{ t('Start Exploring') }}
                        </Button>
                    </a>
                </a-col>
            </a-row>


        </div>
    </section>
</template>
