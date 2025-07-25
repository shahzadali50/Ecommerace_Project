import { ref, watch } from 'vue';
import axios from 'axios';

const globalCache = new Map<string, Record<string, string>>();

export function useTranslator(locale: string) {
  const translations = ref<Record<string, string>>({});
  const pendingTexts = new Set<string>();
  const loading = ref(false);
  const isFetching = ref(false);
  console.log(' ===Current Language====', locale);
  // Load from cache if available
  const loadFromCacheOrFetch = () => {
    if (globalCache.has(locale)) {
      translations.value = globalCache.get(locale)!;
    }
  };
  // Fetch translations for pending texts
  const fetchTranslations = async () => {
    console.log();
    if (pendingTexts.size === 0 || isFetching.value) return;

    const texts = Array.from(pendingTexts);
    pendingTexts.clear();

    isFetching.value = true;

    try {
      const { data } = await axios.post(route('translate'), {
        texts,
        target: locale,
      });

      const newTranslations = { ...translations.value, ...data.translations };
      translations.value = newTranslations;
      globalCache.set(locale, newTranslations);
    } catch (err) {
      console.error('Translation error', err);
      texts.forEach(t => translations.value[t] = t); // fallback
    } finally {
      isFetching.value = false;
    }
  };

  // Translate function
  const t = (text: string) => {
    if (translations.value[text]) {
      return translations.value[text];
    }

    if (!pendingTexts.has(text)) {
      pendingTexts.add(text);
      fetchTranslations(); // Call immediately on new text
    }

    return text;
  };

  // Clear pendingTexts and reset translations on locale change
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
