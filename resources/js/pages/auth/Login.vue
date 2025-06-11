<script setup lang="ts">
import InputError from "@/components/InputError.vue";
import TextLink from "@/components/TextLink.vue";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import AuthBase from "@/layouts/AuthLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { LoaderCircle } from "lucide-vue-next";
import UserLayout from "@/layouts/UserLayout.vue";
import {computed } from 'vue';
import { type SharedData } from '@/types';


defineProps<{
  status?: string;
  canResetPassword: boolean;
}>();

const page = usePage<SharedData>();
const translations = computed(() => page.props.translations as Record<string, string>);


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
      :title="translations.login_to_account"
      :description="translations.enter_credentials"
    >
      <Head :title="translations.login" />

      <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
        {{ status }}
      </div>

      <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-6">
          <div class="grid gap-2">
            <Label for="email">{{ translations.email }}</Label>
            <Input
              id="email"
              type="email"
              required
              autofocus
              :tabindex="1"
              autocomplete="email"
              v-model="form.email"
              :placeholder="translations.email_placeholder"
            />
            <InputError :message="form.errors.email" />
          </div>

          <div class="grid gap-2">
            <div class="flex items-center justify-between">
              <Label for="password">{{ translations.password }}</Label>
              <TextLink
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-sm"
                :tabindex="5"
              >
                {{ translations.forgot_password }}
              </TextLink>
            </div>
            <Input
              id="password"
              type="password"
              required
              :tabindex="2"
              autocomplete="current-password"
              v-model="form.password"
              :placeholder="translations.password_placeholder"
            />
            <InputError :message="form.errors.password" />
          </div>

          <div class="flex items-center justify-between" :tabindex="3">
            <Label for="remember" class="flex items-center space-x-3">
              <Checkbox id="remember" v-model:checked="form.remember" :tabindex="4" />
              <span>{{ translations.remember_me }}</span>
            </Label>
          </div>

          <Button
            type="submit"
            class="mt-4 w-full"
            :tabindex="4"
            :disabled="form.processing"
          >
            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
            {{ translations.login }}
          </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
          {{ translations.no_account }}
          <TextLink :href="route('register')" :tabindex="5">{{
            translations.sign_up
          }}</TextLink>
        </div>
      </form>
    </AuthBase>
  </UserLayout>
</template>
