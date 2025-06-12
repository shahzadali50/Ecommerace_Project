<script setup lang="ts">
import UserLayout from "@/layouts/UserLayout.vue";
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import { Row, Col } from "ant-design-vue";
import { Button } from "@/components/ui/button";
import { ShoppingCartOutlined, ShoppingOutlined, ArrowLeftOutlined } from "@ant-design/icons-vue";
import { Link } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { LoaderCircle } from "lucide-vue-next";
import type { PageProps as InertiaPageProps } from "@inertiajs/core";
import StripePayment from "@/components/frontend/StripePayment.vue";


// Add interface for page props
interface PageProps extends InertiaPageProps {
    flash?: {
        success?: string;
        error?: string;
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
const form = useForm({

    payment_method: "",
});

const orderGenerate = () => {
    form.post(route("order.generate"), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
<template>
    <UserLayout>

        <Head title="Payment" />
        <section class="py-14">
            <div class="container mx-auto px-2 sm:px-4">
                <Link :href="route('cart.checkout')" class="flex items-center mb-2">
                <ArrowLeftOutlined />Back</Link>
                <a-alert class="my-1" v-if="page.props.flash?.success" :message="page.props.flash.success"
                    type="success" show-icon />
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
                        <Col :xs="24" :md="10" class="mb-5 order-2 md:order-1">
                        <div>
                            <h1 class="text-5xl font-bold text-green-800 mb-6 flex items-center gap-2">
                                2.
                            </h1>
                            <div v-if="form.errors.payment_method" class="text-red-500">
                                {{ form.errors.payment_method }}
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">
                                {{ translations.payment_method || "Payment Method" }}
                            </h1>
                            <div class="">
                                <div class="flex items-center">
                                    <a-radio-group v-model:value="form.payment_method" class="ml-2">
                                        <a-radio value="Cash On Delivery">{{
                                            translations.cash_on_delivery || "Cash On Delivery"
                                            }}</a-radio>
                                    </a-radio-group>
                                </div>
                                <p class="text-gray-500 text-sm ml-6">
                                    {{
                                        translations.pay_cash_on_delivery || "Pay with cash upon delivery."
                                    }}
                                </p>
                            </div>
                            <div>
                                <a-button html-type="submit"
                                    class="w-full btn-primary mt-5 flex items-center justify-center py-4"
                                    :disabled="form.processing">
                                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                                    Place Order
                                    <template #icon v-if="!form.processing">
                                        <ShoppingOutlined />
                                    </template>

                                </a-button>
                                  <!-- OR Separator -->
                            <div class="flex items-center my-6">
                                <div class="flex-grow h-px bg-gray-300"></div>
                                <span class="mx-4 text-gray-600 font-medium">OR</span>
                                <div class="flex-grow h-px bg-gray-300"></div>
                            </div>
                                <StripePayment />
                            </div>
                        </div>
                        </Col>
                        <Col :xs="24" :md="10" class="mb-5 order-1 md:order-2  p-4"
                            style="box-shadow: rgba(0, 0, 0, 0.05) 0px 6px 24px 0px, rgba(0, 0, 0, 0.08) 0px 0px 0px 1px;">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                                Order Summary
                            </h1>
                        </div>
                        <!-- <CartItems /> -->
                        <div class="flex justify-between mb-4 border-b py-4">
                            <span class="">{{ translations.subtotal || "Subtotal" }}</span>
                            <span class=" text-slate-500">{{ formatPrice(total) }}</span>
                        </div>
                        <div class="flex justify-between mb-4 border-b py-4">
                            <span class="">{{
                                translations.shipping_charge || "Shipping"
                                }}</span>
                            <span class=" text-slate-500">{{
                                translations.free_delivery || "Free Delivery"
                                }}</span>
                        </div>
                        <div class="flex justify-between mb-4">
                            <span class="">{{ translations.total || "Total" }}:</span>
                            <span class=" text-slate-500">{{ formatPrice(total) }}</span>
                        </div>
                        </Col>

                    </Row>
                </form>
            </div>
        </section>
    </UserLayout>
</template>
