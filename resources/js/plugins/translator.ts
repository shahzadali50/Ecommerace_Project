import { App } from 'vue';
import { useTranslator } from '@/composables/useTranslator';

export default {
  install(app: App, locale: string) {
    const translator = useTranslator(locale);

    // make it globally available
    app.config.globalProperties.$t = translator.t;
    app.config.globalProperties.$translations = translator.translations;
    app.config.globalProperties.$loadingTranslations = translator.loading;
  }
}
