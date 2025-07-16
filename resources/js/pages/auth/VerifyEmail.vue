<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import UserLayout from '@/layouts/UserLayout.vue';
import { getCurrentInstance } from 'vue';

defineProps<{
    status?: string;
}>();

// 👇 get access to global $t
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
        <UserLayout>
    <AuthLayout :title="t('Verify Email')" :description="t('Before proceeding, please check your email for a verification link')">
        <Head :title="t('Email Verification')" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ t('A new verification link has been sent to the email address you provided during registration') }}
        </div>

        <form @submit.prevent="submit" class="space-y-6 text-center">
            <Button :disabled="form.processing" variant="secondary">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                {{ t('Resend Verification Email') }}
            </Button>

            <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm"> {{ t('Logout') }} </TextLink>
        </form>
    </AuthLayout>
    </UserLayout>
</template>
