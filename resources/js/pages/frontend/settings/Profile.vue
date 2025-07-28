<script setup lang="ts">
import UserLayout from "@/layouts/UserLayout.vue";
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Row, Col } from "ant-design-vue";
import UserAccountSidebar from "@/components/frontend/UserAccountSidebar.vue";
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type SharedData, type User } from '@/types';


interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();


const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

</script>

<template>
    <UserLayout>

        <Head title="Profile settings" />
        <section class="bg-cover bg-center py-16 sm:py-24"
            style="background-image: url('/assets/images/page-header-bg.jpg')">
            <div class="container mx-auto">
                <Row>
                    <Col :span="24" class="text-center">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-0">Profile settings</h1>
                    </Col>
                </Row>
            </div>
        </section>
        <section>
            <div class="container mx-auto">
                <Row :gutter="[16, 16]">
                    <!-- Sidebar -->
                    <Col :xs="24" :sm="24" :md="8" :lg="6" :xl="6">
                    <UserAccountSidebar />

                    </Col>

                    <!-- Main Content -->
                    <Col :xs="24" :sm="24" :md="16" :lg="18" :xl="18">
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <SettingsLayout>
                            <div class="flex flex-col space-y-6">
                                <HeadingSmall title="Profile information"
                                    description="Update your name and email address" />

                                <form @submit.prevent="submit" class="space-y-6">
                                    <div class="grid gap-2">
                                        <Label for="name"> Name </Label>
                                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required
                                            autocomplete="name" placeholder="Full name" />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="email">Email address</Label>
                                        <Input id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                                            required autocomplete="username" placeholder="Email address" />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                                        <p class="-mt-4 text-sm text-muted-foreground">
                                            Your email address is unverified.
                                            <Link :href="route('verification.send')" method="post" as="button"
                                                class="hover:!decoration-current text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out dark:decoration-neutral-500">
                                            Click here to resend the verification email.
                                            </Link>
                                        </p>

                                        <div v-if="status === 'verification-link-sent'"
                                            class="mt-2 text-sm font-medium text-green-600">
                                            A new verification link has been sent to your email address.
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <Button :disabled="form.processing">Save</Button>

                                        <TransitionRoot :show="form.recentlySuccessful" enter="transition ease-in-out"
                                            enter-from="opacity-0" leave="transition ease-in-out" leave-to="opacity-0">
                                            <p class="text-sm text-neutral-600">Saved.</p>
                                        </TransitionRoot>
                                    </div>
                                </form>
                            </div>

                            <DeleteUser />
                        </SettingsLayout>
                    </div>
                    </Col>
                </Row>
            </div>
        </section>

    </UserLayout>
</template>
