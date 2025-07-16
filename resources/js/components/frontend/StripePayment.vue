<script setup lang="ts">
import { loadStripe } from '@stripe/stripe-js';
import { ref, getCurrentInstance } from 'vue';
import { LoaderCircle } from 'lucide-vue-next';
import { CreditCardOutlined } from '@ant-design/icons-vue';

// 👇 get access to global $t
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;

const isLoading = ref(false);
const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_KEY);

const handleStripeCheckout = async () => {
    isLoading.value = true;

    const stripe = await stripePromise;

    const response = await fetch('/create-checkout-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || '',
        },
    });

    const data = await response.json();

    const result = await stripe?.redirectToCheckout({
        sessionId: data.id,
    });

    if (result?.error) {
        alert(result.error.message);
    }

    isLoading.value = false;
};
</script>

<template>
    <button class="w-full mt-4 flex items-center justify-center btn btn-dark " @click="handleStripeCheckout"
        :disabled="isLoading">
        <LoaderCircle v-if="isLoading" class="h-4 w-4 animate-spin mr-2" />
        <CreditCardOutlined v-if="!isLoading" class="mr-2" /> {{ t('Pay with Card') }}
    </button>
    <p class="text-sm text-gray-500 text-center mt-2">
  {{ t('Secure payment powered by') }} <span class="font-medium text-gray-700">{{ t('Stripe') }}</span>. {{ t('We do not store your card details.') }}
</p>

</template>
