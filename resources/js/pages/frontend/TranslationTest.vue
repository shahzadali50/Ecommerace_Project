<script setup lang="ts">
import { getCurrentInstance, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/layouts/UserLayout.vue';

const props = defineProps<{
  locale: string;
  flash?: { success?: string; error?: string };
}>();

const available_languages = [
  { code: 'en', name: 'English' },
  { code: 'es', name: 'Spanish' },
  { code: 'it', name: 'Italian' },
  { code: 'fr', name: 'French' },
  { code: 'de', name: 'German' },
  { code: 'ar', name: 'Arabic' },
  { code: 'hi', name: 'Hindi' },
  { code: 'ur', name: 'Urdu' },
];

const selectedLanguage = ref(props.locale);

// 👇 get access to global $t
const { appContext } = getCurrentInstance()!;
const t = appContext.config.globalProperties.$t as (key: string) => string;


</script>

<template>
  <UserLayout>
    <Head title="Translation Test" />
    <section class="py-14">
      <div class="container mx-auto px-4">
        <div class="flex flex-col">
          <div class="w-full">
           

            <div
              v-if="props.flash?.error"
              class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"
            >
              {{ props.flash.error }}
            </div>

            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
              <strong>{{ t('Current Language') }}:</strong> {{ props.locale }}
            </div>

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
                      name="language"
                      id="language"
                      v-model="selectedLanguage"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                    >
                      <option
                        v-for="lang in available_languages"
                        :key="lang.code"
                        :value="lang.code"
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
