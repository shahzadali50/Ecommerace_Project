<script setup lang="ts">
import InputError from "@/components/InputError.vue";
import TextLink from "@/components/TextLink.vue";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import AuthBase from "@/layouts/AuthLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { LoaderCircle } from "lucide-vue-next";
import UserLayout from "@/layouts/UserLayout.vue";
import { getCurrentInstance } from 'vue';

defineProps<{
  status?: string;
  canResetPassword: boolean;
}>();

// 👇 get access to global $t
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

const form = useForm({
  email: "",
  password: "",
  remember: false,
});
const submit = () => {

  form.post(route("login"), {
    onFinish: () => form.reset("password"),
  });
};
</script>

<template>
  <UserLayout>
    <AuthBase
      :title="t('Login to your account')"
      :description="t('Enter your credentials to access your account')"
    >
      <Head :title="t('Login')" />

      <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
        {{ status }}
      </div>

      <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-6">
          <div class="grid gap-2">
            <Label for="email">{{ t('Email') }}</Label>
            <Input
              id="email"
              type="email"
              required
              autofocus
              :tabindex="1"
              autocomplete="email"
              v-model="form.email"
              :placeholder="t('Enter your email address')"
            />
            <InputError :message="form.errors.email" />
          </div>

          <div class="grid gap-2">
            <div class="flex items-center justify-between">
              <Label for="password">{{ t('Password') }}</Label>
              <TextLink
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-sm"
                :tabindex="5"
              >
                {{ t('Forgot your password?') }}
              </TextLink>
            </div>
            <Input
              id="password"
              type="password"
              required
              :tabindex="2"
              autocomplete="current-password"
              v-model="form.password"
              :placeholder="t('Enter your password')"
            />
            <InputError :message="form.errors.password" />
          </div>

          <div class="flex items-center justify-between" :tabindex="3">
            <Label for="remember" class="flex items-center space-x-3">
              <Checkbox id="remember" v-model:checked="form.remember" :tabindex="4" />
              <span>{{ t('Remember me') }}</span>
            </Label>
          </div>

          <Button
            type="submit"
            class="mt-4 w-full"
            :tabindex="4"
            :disabled="form.processing"
          >
            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
            {{ t('Login') }}
          </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
          {{ t('Don\'t have an account?') }}
          <TextLink :href="route('register')" :tabindex="5">{{
            t('Sign up')
          }}</TextLink>
        </div>
      </form>
    </AuthBase>
  </UserLayout>
</template>
