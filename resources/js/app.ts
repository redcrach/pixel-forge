import { createInertiaApp } from '@inertiajs/vue3';
import { echo } from '@inertiajs/vue3/echo';
import { configureEcho } from '@laravel/echo-vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

// Reverb pushes generation updates to the browser (see App\Events\GenerationUpdated).
configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#6ee7b7',
    },
    // Live props reload themselves when their broadcast event arrives, and after a dropped connection returns.
    live: echo(),
});

// This will listen for flash toast data from the server...
initializeFlashToast();
