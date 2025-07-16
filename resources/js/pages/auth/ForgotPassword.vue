<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <UserLayout>
    <AuthLayout :title="t('Forgot Password')" :description="t('Enter your email address to receive a password reset link')">
        <Head :title="t('Forgot Password')" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ t(status) }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="email">{{ t('Email') }}</Label>
                    <Input id="email" type="email" name="email" autocomplete="off" v-model="form.email" autofocus :placeholder="t('Enter your email address')" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ t('Send Password Reset Link') }}
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>{{ t('Or return to') }}</span>
                <TextLink :href="route('login')">{{ t('Login') }}</TextLink>
            </div>
        </div>
    </AuthLayout>
    </UserLayout>
</template>
