import { ref, watch } from 'vue';
import axios from 'axios';

export function useTranslator(locale: string) {
  const translations = ref<Record<string, string>>({});
  const pendingTexts = new Set<string>();
  const loading = ref(false);

  const fetchTranslations = async () => {
    if (pendingTexts.size === 0) return;

    const texts = Array.from(pendingTexts);
    pendingTexts.clear();

    loading.value = true;

    try {
      const response = await axios.post(route('translate'), {
        texts,
        target: locale,
      });

      translations.value = {
        ...translations.value,
        ...response.data.translations,
      };
    } catch (err) {
      console.error('Translation error', err);
    } finally {
      loading.value = false;
    }
  };

  const t = (text: string): string => {
    if (!translations.value[text]) {
      pendingTexts.add(text);
      debounceFetch();
      return text; // fallback until translated
    }
    return translations.value[text];
  };

  let debounceTimer: number | undefined;

  const debounceFetch = () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(() => fetchTranslations(), 100);
  };

  return { t, translations, loading };
}
