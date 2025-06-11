<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
  FacebookOutlined,
  TwitterOutlined,
  InstagramOutlined,
  LinkedinOutlined,
  CarOutlined,
  SafetyCertificateOutlined,
  CustomerServiceOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

// Get page props and create a computed property for translations
const page = usePage();
const translations = computed(() => {
  return page.props.translations?.footer || {};
});

const email = ref('');

const subscribe = () => {
  if (!email.value) {
    message.error(translations.value.enter_email || 'Please enter your email address');
    return;
  }

  // Here you would typically send the email to your backend
  message.success(translations.value.subscribe_success || 'Thank you for subscribing!');
  email.value = '';
};
</script>

<template>
  <!-- Top Footer Section -->
  <div class="bg-primary text-white py-8">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Free Shipping -->
        <div class="flex items-center">
          <div class="mr-4">
            <CarOutlined class="text-3xl" />
          </div>
          <div>
            <h3 class="text-lg font-semibold">{{ translations.free_shipping || 'Free Shipping' }}</h3>
            <p class="text-sm text-blue-100">{{ translations.free_shipping_desc || 'On orders over $50' }}</p>
          </div>
        </div>

        <!-- Secure Payment -->
        <div class="flex items-center">
          <div class="mr-4">
            <SafetyCertificateOutlined class="text-3xl" />
          </div>
          <div>
            <h3 class="text-lg font-semibold">{{ translations.secure_payment || 'Secure Payment' }}</h3>
            <p class="text-sm text-blue-100">{{ translations.secure_payment_desc || '100% secure payment' }}</p>
          </div>
        </div>

        <!-- 24/7 Support -->
        <div class="flex items-center">
          <div class="mr-4">
            <CustomerServiceOutlined class="text-3xl" />
          </div>
          <div>
            <h3 class="text-lg font-semibold">{{ translations.support || '24/7 Support' }}</h3>
            <p class="text-sm text-blue-100">{{ translations.support_desc || 'Dedicated support' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Footer -->
  <footer class="bg-gray-100 pt-12 pb-8">
    <div class="container mx-auto px-4">
      <!-- Main Footer Content -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
        <!-- Company Info -->
        <div class="flex flex-col">
          <div class="mb-4">
            <Link :href="route('home')" class="flex items-center">
              <img src="\assets\images\Logo-LaCuna-JP-azul.fw.webp" alt="Logo" class="h-8 w-auto" />
            </Link>
          </div>
          <p class="text-gray-600 mb-4">
            {{ translations.company_description || 'Your trusted marketplace for quality products and services.' }}
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-gray-500 hover:text-gray-700">
              <FacebookOutlined class="text-xl" />
            </a>
            <a href="#" class="text-gray-500 hover:text-gray-700">
              <TwitterOutlined class="text-xl" />
            </a>
            <a href="#" class="text-gray-500 hover:text-gray-700">
              <InstagramOutlined class="text-xl" />
            </a>
            <a href="#" class="text-gray-500 hover:text-gray-700">
              <LinkedinOutlined class="text-xl" />
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div>
          <h3 class="text-lg font-semibold mb-4">{{ translations.quick_links || 'Quick Links' }}</h3>
          <ul class="space-y-2">
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.home || 'Home' }}</Link>
            </li>
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.products || 'Products' }}</Link>
            </li>
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.categories || 'Categories' }}</Link>
            </li>
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.brands || 'Brands' }}</Link>
            </li>
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.about_us || 'About Us' }}</Link>
            </li>
          </ul>
        </div>

        <!-- Customer Service -->
        <div>
          <h3 class="text-lg font-semibold mb-4">{{ translations.customer_service || 'Customer Service' }}</h3>
          <ul class="space-y-2">
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.contact_us || 'Contact Us' }}</Link>
            </li>
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.faq || 'FAQ' }}</Link>
            </li>
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.shipping_info || 'Shipping Info' }}</Link>
            </li>
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.returns || 'Returns' }}</Link>
            </li>
            <li>
              <Link :href="route('home')" class="text-gray-600 hover:text-gray-900">{{ translations.privacy_policy || 'Privacy Policy' }}</Link>
            </li>
          </ul>
        </div>

        <!-- Newsletter -->
        <div>
          <h3 class="text-lg font-semibold mb-4">{{ translations.newsletter || 'Newsletter' }}</h3>
          <p class="text-gray-600 mb-4">
            {{ translations.newsletter_desc || 'Subscribe to our newsletter for updates and exclusive offers.' }}
          </p>
          <div class="flex">
            <a-input
              v-model:value="email"
              :placeholder="translations.email_placeholder || 'Your email'"
              class="rounded-r-none"
            />
            <a-button  class="btn-primary" @click="subscribe">
              {{ translations.subscribe || 'Subscribe' }}
            </a-button>
          </div>
        </div>
      </div>

      <!-- Bottom Footer -->
      <div class="border-t border-gray-200 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="mb-4 md:mb-0">
            <p class="text-gray-600 text-sm">
              {{ (translations.copyright || '© :year Lacuna Marketplace. All rights reserved.').replace(':year', new Date().getFullYear().toString()) }}
            </p>
          </div>
          <div class="flex space-x-4">
            <Link :href="route('home')" class="text-gray-600 hover:text-gray-900 text-sm">{{ translations.terms || 'Terms' }}</Link>
            <Link :href="route('home')" class="text-gray-600 hover:text-gray-900 text-sm">{{ translations.privacy || 'Privacy' }}</Link>
            <Link :href="route('home')" class="text-gray-600 hover:text-gray-900 text-sm">{{ translations.cookies || 'Cookies' }}</Link>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.ant-input {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.ant-btn {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}
</style>