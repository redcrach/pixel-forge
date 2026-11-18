<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Box,
    History,
    Moon,
    Settings,
    Sparkles,
    SquareTerminal,
} from '@lucide/vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { generate, history } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { StudioNavItem } from '@/types';

const navItems: StudioNavItem[] = [
    {
        title: 'Generate',
        href: generate(),
        icon: Sparkles,
        badge: 'New v2.4',
        badgeTone: 'primary',
    },
    {
        title: 'History',
        href: history(),
        icon: History,
        badge: '18',
        badgeTone: 'muted',
    },
    {
        title: 'Models & Pricing',
        icon: Box,
    },
    {
        title: 'API & Docs',
        icon: SquareTerminal,
    },
];

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
        <div class="flex flex-col gap-3">
            <span
                class="px-1 pt-0.5 text-label-dense tracking-wider text-outline uppercase"
            >
                Workspace
            </span>

            <nav class="flex flex-col gap-0.5">
                <component
                    :is="item.href ? Link : 'span'"
                    v-for="item in navItems"
                    :key="item.title"
                    :href="item.href"
                    :class="[
                        navItemClass,
                        item.href && isCurrentUrl(item.href)
                            ? activeClass
                            : inactiveClass,
                        !item.href && 'cursor-default',
                    ]"
                >
                    <span class="flex items-center gap-2">
                        <component :is="item.icon" class="size-4.5" />
                        {{ item.title }}
                    </span>
                    <span
                        v-if="item.badge"
                        class="rounded px-0.5 py-0.5 font-mono text-mono-badge"
                        :class="
                            item.badgeTone === 'primary'
                                ? 'bg-primary-container/20 text-primary'
                                : 'bg-surface-container-high text-on-surface-variant'
                        "
                    >
                        {{ item.badge }}
                    </span>
                </component>
            </nav>
        </div>

        <div class="flex flex-col gap-2 pt-2">
            <div
                class="flex flex-col gap-1 rounded-lg bg-surface-container-low p-2"
            >
                <div class="flex items-center justify-between">
                    <span class="text-label-dense text-on-surface-variant">
                        Daily Limit
                    </span>
                    <span class="font-mono text-mono-badge text-on-surface">
                        42/100 images
                    </span>
                </div>
                <div
                    class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container-highest"
                >
                    <div class="h-1.5 w-[42%] rounded-full bg-primary" />
                </div>
            </div>

            <div class="flex items-center justify-between px-1">
                <span
                    class="flex items-center gap-0.5 text-label-default text-on-surface-variant"
                >
                    <Moon class="size-4" />
                    Dark theme
                </span>
                <button
                    type="button"
                    role="switch"
                    aria-checked="true"
                    aria-label="Dark theme"
                    class="flex h-4 w-7 items-center justify-end rounded-full bg-primary/20 p-0.5"
                >
                    <span class="size-3 rounded-full bg-primary" />
                </button>
            </div>

            <Link
                :href="editProfile()"
                :class="[
                    navItemClass,
                    'justify-start gap-2',
                    isCurrentOrParentUrl('/settings')
                        ? activeClass
                        : inactiveClass,
                ]"
            >
                <Settings class="size-4.5" />
                Settings
            </Link>
        </div>
    </aside>
</template>
