<script setup lang="ts">
import UserLayout from "@/layouts/UserLayout.vue";
import { computed,ref} from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import { Row, Col } from "ant-design-vue";
import CartItems from "@/components/frontend/CartItems.vue";
import { Button } from "@/components/ui/button";
import { ShoppingCartOutlined, ArrowRightOutlined } from "@ant-design/icons-vue";
import { Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { LoaderCircle } from "lucide-vue-next";
import type { PageProps as InertiaPageProps } from "@inertiajs/core";
import LoginModal from "@/components/frontend/LoginModal.vue";

// Add interface for page props
interface PageProps extends InertiaPageProps {
    flash?: {
        success?: string;
        error?: string;
    };
    billingDetail?: {
        name?: string;
        email?: string;
        phone_number?: string;
        address?: string;
        country?: string;
        state?: string;
        city?: string;
        postal_code?: string;
        order_notes?: string;
    };
}

const page = usePage<PageProps>();
const translations = computed(() => {
    return (page.props.translations as any)?.cart_checkout || {};
});

const cart = computed(() => {
    const data = (usePage().props.cart as any[]) || [];
    return data;
});
const total = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.total_price, 0);
});
const formatPrice = (price: number) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "PKR",
        minimumFractionDigits: 2,
    }).format(price);
};

// Initialize form with billingDetail values if they exist
const billingDetail = computed(() => page.props.billingDetail || {});
const form = useForm({
    name: billingDetail.value.name || "",
    email: billingDetail.value.email || "",
    phone_number: billingDetail.value.phone_number || "",
    address: billingDetail.value.address || "",
    country: billingDetail.value.country || "",
    state: billingDetail.value.state || "",
    city: billingDetail.value.city || "",
    postal_code: billingDetail.value.postal_code || "",
    order_notes: billingDetail.value.order_notes || "",
});
const isLoginModalVisible = ref(false);
const user = computed(() => (page.props.auth as any)?.user);

const orderGenerate = () => {
     if (user.value) {
         form.post(route("billing.details"), {
        onSuccess: () => {
            form.reset();
        },
    });
    } else {
        isLoginModalVisible.value = true;
    }

};
</script>
<template>
    <UserLayout>

        <Head :title="translations.checkout" />
        <section class="py-14">
            <div class="container mx-auto px-2 sm:px-4">
                <a-alert v-if="page.props.flash?.success" :message="translations.success || 'Success'"
                    :description="page.props.flash.success" type="success" show-icon class="mb-4" />
                <Row v-if="!cart || cart.length === 0" class="py-10">
                    <Col span="24">
                    <div class="text-center">
                        <ShoppingCartOutlined class="text-7xl text-gray-400 mb-4" />
                        <h4 class="text-gray-500 text-2xl">
                            {{ translations.cart_empty || "Your cart is empty" }}
                        </h4>
                        <Link :href="route('home')" class="inline-block mt-4">
                        <Button>{{
                            translations.continue_shopping || "Continue Shopping"
                            }}</Button>
                        </Link>
                    </div>
                    </Col>
                </Row>

                <form class="w-full" v-else @submit.prevent="orderGenerate()">
                    <div v-if="Object.keys(form.errors).length > 0"
                        class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="list-disc list-inside text-red-600">
                            <li v-for="error in Object.values(form.errors)" :key="error" class="mb-1">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                    <Row class="justify-between px-6">
                        <Col :xs="24" :md="10" class="mb-5">
                        <div>
                            <h1 class="text-5xl font-bold text-green-800 mb-6 flex items-center gap-2">
                                1.
                            </h1>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                {{ translations.billing_detail || "Billing Details" }}
                            </h1>
                        </div>
                        <div class="mb-4">
                            <label class="block">{{ translations.name || "Full Name" }}
                                <span class="text-red-500">*</span></label>
                            <a-input v-model:value="form.name" class="mt-2 w-full"
                                :placeholder="translations.name_placeholder" />
                            <div v-if="form.errors.name" class="text-red-500">
                                {{ form.errors.name }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block">{{ translations.email || "Email" }}
                                <span class="text-red-500">*</span></label>
                            <a-input type="email" v-model:value="form.email" class="mt-2 w-full"
                                :placeholder="translations.email_placeholder" />
                            <div v-if="form.errors.email" class="text-red-500">
                                {{ form.errors.email }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block">{{ translations.phone || "Phone Number" }}
                                <span class="text-red-500">*</span></label>
                            <a-input type="number" v-model:value="form.phone_number" class="mt-2 w-full"
                                :placeholder="translations.phone_placeholder" />
                            <div v-if="form.errors.phone_number" class="text-red-500">
                                {{ form.errors.phone_number }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block">{{ translations.address || "Address" }}
                                <span class="text-red-500">*</span></label>
                            <a-textarea v-model:value="form.address" class="mt-2 w-full"
                                :placeholder="translations.address_placeholder" :rows="4" />
                            <div v-if="form.errors.address" class="text-red-500">
                                {{ form.errors.address }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block">{{ translations.country || "Country" }}
                                <span class="text-red-500">*</span></label>
                            <a-input v-model:value="form.country" class="mt-2 w-full"
                                :placeholder="translations.country_placeholder" />
                            <div v-if="form.errors.country" class="text-red-500">
                                {{ form.errors.country }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block">{{ translations.state || "State" }}
                                <span class="text-red-500">*</span></label>
                            <a-input v-model:value="form.state" class="mt-2 w-full"
                                :placeholder="translations.state_placeholder" />
                            <div v-if="form.errors.state" class="text-red-500">
                                {{ form.errors.state }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block">{{ translations.city || "City" }}
                                <span class="text-red-500">*</span></label>
                            <a-input v-model:value="form.city" class="mt-2 w-full"
                                :placeholder="translations.city_placeholder" />
                            <div v-if="form.errors.city" class="text-red-500">
                                {{ form.errors.city }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block">{{ translations.pcode || "Postal Code" }}
                                <span class="text-red-500">*</span></label>
                            <a-input type="number" v-model:value="form.postal_code" class="mt-2 w-full"
                                :placeholder="translations.pcode_placeholder" />
                            <div v-if="form.errors.postal_code" class="text-red-500">
                                {{ form.errors.postal_code }}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block">{{
                                translations.order_notes || "Order notes (optional)"
                                }}</label>
                            <a-textarea v-model:value="form.order_notes" class="mt-2 w-full"
                                :placeholder="translations.order_notes_placeholder" :rows="4" />
                            <div v-if="form.errors.order_notes" class="text-red-500">
                                {{ form.errors.order_notes }}
                            </div>
                        </div>
                        </Col>
                        <Col :xs="24" :md="10" class="mb-5">
                        <div>
                            <h1 class="text-5xl font-bold text-transparent mb-6 flex items-center gap-2">
                                2.
                            </h1>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">
                                {{ translations.check_your_order || "Check Your Order" }}
                            </h1>
                        </div>
                        <CartItems />
                        <div class="flex justify-between mb-4 border-b py-4">
                            <span class="font-medium">{{ translations.subtotal || "Subtotal" }}</span>
                            <span class="font-bold text-primary">{{ formatPrice(total) }}</span>
                        </div>
                        <div class="flex justify-between mb-4 border-b py-4">
                            <span class="font-medium">{{
                                translations.shipping_charge || "Shipping"
                                }}</span>
                            <span class="font-bold text-primary">{{
                                translations.free_delivery || "Free Delivery"
                                }}</span>
                        </div>
                        <div class="flex justify-between mb-4">
                            <span class="font-medium">{{ translations.total || "Total" }}:</span>
                            <span class="font-bold text-primary">{{ formatPrice(total) }}</span>
                        </div>
                        </Col>
                        <Col :xs="24" :md="10" class="mb-5">
                        <div>
                            <div>
                                <a-button html-type="submit"
                                    class="w-full btn-primary mt-5 flex items-center justify-center py-4"
                                    :disabled="form.processing">
                                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                                    Next
                                    <template #icon v-if="!form.processing">
                                        <ArrowRightOutlined />
                                    </template>

                                </a-button>
                            </div>
                        </div>
                        </Col>
                    </Row>
                </form>
                 <LoginModal v-model:open="isLoginModalVisible" :canResetPassword="false" />
            </div>
        </section>
    </UserLayout>
</template>
