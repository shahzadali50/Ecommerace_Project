<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/layouts/UserLayout.vue';
import { ref, watch } from 'vue';
import { useTranslator } from '@/composables/useTranslator';

const props = defineProps<{
  locale: string;
  available_languages: { code: string; name: string }[];
  flash?: { success?: string; error?: string };
}>();

const selectedLanguage = ref(props.locale);

const { t } = useTranslator(selectedLanguage.value);

watch(selectedLanguage, newLang => {
  // Optionally: make an API call to persist language change
});
</script>

<template>
  <UserLayout>
    <Head title="Translation Test" />

    <section class="py-14">
      <div class="container mx-auto px-4">
        <div class="flex flex-col">
          <div class="w-full">
            <!-- Success Message -->
            <div
              v-if="props.flash?.success"
              class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 flex justify-between items-center"
            >
              {{ props.flash.success }}
              <button
                type="button"
                class="text-green-700 hover:text-green-900"
                aria-label="Close"
                @click="props.flash.success = ''"
              >
                <span>×</span>
              </button>
            </div>

            <!-- Error Message -->
            <div
              v-if="props.flash?.error"
              class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"
            >
              {{ props.flash.error }}
            </div>

            <!-- Current Language Display -->
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
              <strong>{{ t('Current Language') }}:</strong> {{ props.locale }}
            </div>

            <!-- Language Selection Form -->
            <div class="bg-white shadow-md rounded-lg">
              <div class="bg-gray-100 px-4 py-3 rounded-t-lg">
                <h5 class="text-lg font-semibold">{{ t('Select Language') }}</h5>
              </div>
              <div class="p-4">
                <form :action="route('language.switch')" method="POST">
                  <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                  <div class="mb-4">
                    <label
                      for="language"
                      class="block text-sm font-medium text-gray-700"
                    >
                      {{ t('Choose your preferred language') }}:
                    </label>
                    <select
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      id="language"
                      name="language"
                      v-model="selectedLanguage"
                      required
                    >
                      <option value="">{{ t('Choose your preferred language') }}</option>
                      <option
                        v-for="lang in props.available_languages"
                        :key="lang.code"
                        :value="lang.code"
                        :selected="lang.code === props.locale"
                      >
                        {{ lang.name }}
                      </option>
                    </select>
                  </div>
                  <button
                    type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md"
                  >
                    {{ t('Set Language') }}
                  </button>
                </form>
              </div>
            </div>

            <!-- Example Content Section -->
            <div class="mt-6">
              <div class="bg-white shadow-md rounded-lg">
                <div class="bg-gray-100 px-4 py-3 rounded-t-lg">
                  <h4 class="text-xl font-semibold">{{ t('Example Content') }}</h4>
                </div>
                <div class="p-4">
                  <h1 class="text-3xl font-bold mb-4">{{ t('What is your name?') }}</h1>
                  <p class="text-gray-700 mb-2">
                    {{ t('This is an example of how you can translate any text in your templates.') }}
                  </p>
                  <p class="text-gray-700 mb-4">
                    {{ t('You can use this system on any page and any section of your website.') }}
                  </p>

                  <h3 class="text-lg font-semibold mb-2">{{ t('Features') }}</h3>
                  <ul class="list-disc pl-5 mb-4">
                    <li>{{ t('Easy to use in any template') }}</li>
                    <li>{{ t('Automatic translation based on selected language') }}</li>
                    <li>{{ t('Works with any content type') }}</li>
                    <li>{{ t('No need to modify controllers') }}</li>
                  </ul>

                  <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                    <strong>{{ t('Tip') }}:</strong>
                    {{ t('You can use translation functions for dynamic content.') }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </UserLayout>
</template>

<style scoped>
/* Tailwind CSS is used via utility classes */
</style>
