<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import UserLayout from '@/layouts/UserLayout.vue';
import { computed } from 'vue';
import { type SharedData } from '@/types';

defineProps<{
    status?: string;
}>();

const page = usePage<SharedData>();
const translations = computed(() => page.props.translations as Record<string, string>);

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <UserLayout>
    <AuthLayout :title="translations.forgot_password" :description="translations.enter_email_reset">
        <Head :title="translations.forgot_password" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="email">{{ translations.email }}</Label>
                    <Input id="email" type="email" name="email" autocomplete="off" v-model="form.email" autofocus :placeholder="translations.email_placeholder" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ translations.email_reset_link }}
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>{{ translations.or_return_to }}</span>
                <TextLink :href="route('login')">{{ translations.login }}</TextLink>
            </div>
        </div>
    </AuthLayout>
    </UserLayout>
</template>
