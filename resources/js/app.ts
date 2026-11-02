import { createInertiaApp } from '@inertiajs/vue3';
import { i18nVue } from 'laravel-vue-i18n';
import { initializeTheme } from '@/composables/useAppearance';
import AccountLayout from '@/layouts/AccountLayout.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Eager so translations are available on first render (and under SSR).
const translations = import.meta.glob<Record<string, string>>(
    '../../lang/*.json',
    { eager: true, import: 'default' },
);

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name.startsWith('site/'):
                return SiteLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('admin/'):
                return AdminLayout;
            case name.startsWith('account/settings/'):
                return [AccountLayout, SettingsLayout];
            case name.startsWith('account/'):
                return AccountLayout;
            default:
                return null;
        }
    },
    withApp: (app, { page }) => {
        app.use(i18nVue, {
            lang: page.props.locale,
            resolve: (lang: string) => translations[`../../lang/${lang}.json`],
        });

        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
