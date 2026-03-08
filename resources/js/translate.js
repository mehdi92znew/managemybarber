import { usePage } from '@inertiajs/vue3';
import { translations } from './lang';

export function __ (key) {
    let locale = 'en';
    try {
        const page = usePage();
        if (page?.props?.locale) {
            locale = page.props.locale;
        } else {
            locale = document.documentElement.lang || 'en';
        }
    } catch (e) {
        locale = document.documentElement.lang || 'en';
    }

    if (locale.includes('-')) {
        locale = locale.split('-')[0];
    }

    return translations[locale]?.[key] || key;
}
