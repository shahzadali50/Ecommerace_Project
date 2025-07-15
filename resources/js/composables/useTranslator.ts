import { ref, watch } from 'vue';
import axios from 'axios';

const globalCache = new Map<string, Record<string, string>>();

export function useTranslator(locale: string) {
  const translations = ref<Record<string, string>>({});
  const pendingTexts = new Set<string>();
  const loading = ref(false);

  const loadFromCacheOrFetch = () => {
    if (globalCache.has(locale)) {
      translations.value = globalCache.get(locale)!;
      return;
    }
  };

  const fetchTranslations = async () => {
    if (pendingTexts.size === 0) return;

    const texts = Array.from(pendingTexts);
    pendingTexts.clear();

    loading.value = true;

    try {
      const { data } = await axios.post(route('translate'), {
        texts,
        target: locale,
      });

      translations.value = { ...translations.value, ...data.translations };
      globalCache.set(locale, translations.value);
    } catch (err) {
      console.error('Translation error', err);
      texts.forEach(t => translations.value[t] = t);
    } finally {
      loading.value = false;
    }
  };

  const t = (text: string) => {
    if (!translations.value[text]) {
      pendingTexts.add(text);
      debounceFetch();
      return text;
    }
    return translations.value[text];
  };

  let debounceTimer: number | undefined;
  const debounceFetch = () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(() => fetchTranslations(), 50);
  };

  watch(
    () => locale,
    (newLocale) => {
      if (globalCache.has(newLocale)) {
        translations.value = globalCache.get(newLocale)!;
      } else {
        translations.value = {};
      }
      pendingTexts.clear();
    }
  );

  loadFromCacheOrFetch();

  return { t, translations, loading };
}
