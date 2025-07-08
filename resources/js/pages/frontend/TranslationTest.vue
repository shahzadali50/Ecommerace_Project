<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import UserLayout from '@/layouts/UserLayout.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
  locale: string;
  available_languages: { code: string; name: string }[];
  flash?: { success?: string; error?: string };
}>();

// Reactive state for translations
const translations = ref<Record<string, string>>({});
const selectedLanguage = ref(props.locale);

// Common translate function
async function translate(text: string): Promise<string> {
  if (props.locale === 'en') {
    return text; // No translation needed for English
  }

  // Check if translation is already cached
  if (translations.value[text]) {
    return translations.value[text];
  }

  try {
    const response = await axios.post(route('translate'), {
      text,
      target: props.locale,
    });
    translations.value[text] = response.data.translated;
    return response.data.translated;
  } catch (error) {
    console.error('Translation error:', error);
    return text; // Fallback to original text
  }
}

// Preload translations for initial render
const textsToTranslate = [
  'Language Selection',
  'Current Language',
  'Select Language',
  'Choose your preferred language',
  'Set Language',
  'Example Content',
  'What is your name?',
  'This is an example of how you can translate any text in your templates.',
  'You can use this system on any page and any section of your website.',
  'Features',
  'Easy to use in any template',
  'Automatic translation based on selected language',
  'Works with any content type',
  'No need to modify controllers',
  'Tip',
  'You can use translation functions for dynamic content.',
];

Promise.all(textsToTranslate.map(async (text) => {
  translations.value[text] = await translate(text);
}));
</script>

<template>
  <UserLayout>
    <Head title="Translation Test" />

    <section class="py-14">
      <div class="container mx-auto px-4">
        <div class="flex flex-col">
          <div class="w-full">
            <!-- Success Message -->
            <div v-if="props.flash?.success" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 flex justify-between items-center">
              {{ props.flash.success }}
              <button type="button" class="text-green-700 hover:text-green-900" aria-label="Close" @click="props.flash.success = ''">
                <span>×</span>
              </button>
            </div>

            <!-- Error Message -->
            <div v-if="props.flash?.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
              {{ props.flash.error }}
            </div>

            <!-- Current Language Display -->
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
              <strong>{{ translations['Current Language'] || 'Current Language' }}:</strong> {{ props.locale }}
            </div>

            <!-- Language Selection Form -->
            <div class="bg-white shadow-md rounded-lg">
              <div class="bg-gray-100 px-4 py-3 rounded-t-lg">
                <h5 class="text-lg font-semibold">{{ translations['Select Language'] || 'Select Language' }}</h5>
              </div>
              <div class="p-4">
                <form :action="route('language.switch')" method="POST">
                  <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                  <div class="mb-4">
                    <label for="language" class="block text-sm font-medium text-gray-700">
                      {{ translations['Choose your preferred language'] || 'Choose your preferred language' }}:
                    </label>
                    <select
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      id="language"
                      name="language"
                      v-model="selectedLanguage"
                      required
                    >
                      <option value="">{{ translations['Choose your preferred language'] || 'Choose your preferred language' }}</option>
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
                  <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md">
                    {{ translations['Set Language'] || 'Set Language' }}
                  </button>
                </form>
              </div>
            </div>

            <!-- Example Content Section -->
            <div class="mt-6">
              <div class="bg-white shadow-md rounded-lg">
                <div class="bg-gray-100 px-4 py-3 rounded-t-lg">
                  <h4 class="text-xl font-semibold">{{ translations['Example Content'] || 'Example Content' }}</h4>
                </div>
                <div class="p-4">
                  <h1 class="text-3xl font-bold mb-4">{{ translations['What is your name?'] || 'What is your name?' }}</h1>
                  <p class="text-gray-700 mb-2">{{ translations['This is an example of how you can translate any text in your templates.'] || 'This is an example of how you can translate any text in your templates.' }}</p>
                  <p class="text-gray-700 mb-4">{{ translations['You can use this system on any page and any section of your website.'] || 'You can use this system on any page and any section of your website.' }}</p>

                  <h3 class="text-lg font-semibold mb-2">{{ translations['Features'] || 'Features' }}</h3>
                  <ul class="list-disc pl-5 mb-4">
                    <li>{{ translations['Easy to use in any template'] || 'Easy to use in any template' }}</li>
                    <li>{{ translations['Automatic translation based on selected language'] || 'Automatic translation based on selected language' }}</li>
                    <li>{{ translations['Works with any content type'] || 'Works with any content type' }}</li>
                    <li>{{ translations['No need to modify controllers'] || 'No need to modify controllers' }}</li>
                  </ul>

                  <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                    <strong>{{ translations['Tip'] || 'Tip' }}:</strong>
                    {{ translations['You can use translation functions for dynamic content.'] || 'You can use translation functions for dynamic content.' }}
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
/* Tailwind CSS is used via utility classes, so no additional styles are needed */
</style>
