<script setup lang="ts">
import { computed, ref, getCurrentInstance } from 'vue';
import { DeleteOutlined, ShoppingCartOutlined } from '@ant-design/icons-vue';
import { usePage, router } from '@inertiajs/vue3';
import { Link } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";

const page = usePage();
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;
const deletingItems = ref(new Set<number>());

const cart = computed(() => {
    const data = page.props.cart as any[] || [];
    return data.map(item => ({
        ...item,
        quantity: item.qty || 0,
    }));
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 2,
    }).format(price);
};

const updateQuantity = (productId: number, action: string) => {
    router.post(route('cart.add'), { id: productId, qty: 1, action }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            // Cart will be automatically updated through page props
        },
        onError: (errors) => {
            console.error('Cart update failed', errors);
        }
    });
};

const removeItem = (productId: number) => {
    deletingItems.value.add(productId);
    router.post(route('cart.remove'), { id: productId }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            deletingItems.value.delete(productId);
        },
        onError: (errors) => {
            deletingItems.value.delete(productId);
            console.error('Remove failed', errors);
        }
    });
};
</script>

<template>
    <div class="flex-1 overflow-y-auto">
        <div v-if="!cart || cart.length === 0" class="text-center py-8">
            <ShoppingCartOutlined class="text-5xl text-gray-400" />
            <h4 class="text-gray-500 text-2xl my-4">{{ t('Your cart is empty') }}</h4>
                <Button class="btn-primary text-white">
                    <Link :href="route('home')" class="text-white">
                        {{ t('Continue Shopping') }}
                </Link>
        </Button>

        </div>
        <div v-else class="space-y-4">
            <div v-for="item in cart" :key="item.id" class="flex items-start gap-4 border-b">
                <img :src="'/storage/' + item.thumnail_img" :alt="item.name" class="w-20 h-20 object-cover rounded">
                <div class="flex-1">
                    <div>
                        <h3 class="font-medium">{{ t('item.name') }}</h3>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <a-button size="small" @click="updateQuantity(item.id, 'decrease')"  :disabled="item.quantity <= 1">-</a-button>
                        <span class="text-gray-600">{{ item.quantity }}</span>
                        <a-button size="small" @click="updateQuantity(item.id, 'add')">+</a-button>
                        <a-button type="text" shape="circle" class="text-red-500" :loading="deletingItems.has(item.id)"
                            @click="removeItem(item.id)">
                            <template #icon>
                                <DeleteOutlined />
                            </template>
                        </a-button>
                    </div>
                    <div class="flex justify-between items-start mt-2">
                        <p>{{ item.quantity }} X {{ formatPrice(item.final_price) }}</p>
                    </div>
                    <p class="text-primary font-medium mt-2">
                        {{ t('Total') }}: {{ formatPrice(item.total_price) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
