<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { History, Settings, Sparkles } from '@lucide/vue';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { history, home } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { StudioNavItem } from '@/types';

const page = usePage();
const isAuthenticated = computed(() => page.props.auth.user !== null);

const navItems = computed<StudioNavItem[]>(() => [
    { title: 'Generate', href: home(), icon: Sparkles },
    {
        title: 'History',
        href: history(),
        icon: History,
        badge: page.props.generationCount?.toString(),
    },
]);

const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const navItemClass =
    'flex items-center justify-between rounded-lg px-2 py-1 text-body-dense transition-colors';
const activeClass = 'bg-surface-container-high font-medium text-primary';
const inactiveClass =
    'text-on-surface-variant hover:bg-surface-container hover:text-on-surface';
</script>

<template>
    <aside
        class="flex w-64 flex-col justify-between bg-surface-container-lowest p-2"
    >
        <nav class="flex flex-col gap-0.5">
            <Link
                v-for="item in navItems"
                :key="item.title"
                :href="item.href"
                :class="[
                    navItemClass,
                    isCurrentUrl(item.href) ? activeClass : inactiveClass,
                ]"
            >
                <span class="flex items-center gap-2">
                    <component :is="item.icon" class="size-4.5" />
                    {{ item.title }}
                </span>
                <span
                    v-if="item.badge"
                    class="rounded bg-surface-container-high px-0.5 py-0.5 font-mono text-mono-badge text-on-surface-variant"
                >
                    {{ item.badge }}
                </span>
            </Link>
        </nav>

        <Link
            v-if="isAuthenticated"
            :href="editProfile()"
            :class="[
                navItemClass,
                'justify-start gap-2',
                isCurrentOrParentUrl('/settings') ? activeClass : inactiveClass,
            ]"
        >
            <Settings class="size-4.5" />
            Settings
        </Link>
    </aside>
</template>
