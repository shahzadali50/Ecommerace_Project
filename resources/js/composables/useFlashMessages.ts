import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { message } from 'ant-design-vue';

export function useFlashMessages() {
    const page = usePage();

    watch(() => page.props.flash, (flash) => {
        if (flash?.success) {
            message.success(flash.success);
        }
        if (flash?.error) {
            message.error(flash.error);
        }
    });
}
