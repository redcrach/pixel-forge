<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { history, home } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { StudioNavItem } from '@/types';

const page = usePage();
const isAuthenticated = computed(() => page.props.auth.user !== null);

const navItems = computed<StudioNavItem[]>(() => [
    { title: 'Generate', href: home() },
    {
        title: 'History',
        href: history(),
        badge: page.props.generationCount?.toString(),
    },
]);

const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const navItemClass =
    'flex min-h-11 items-center justify-between border-2 px-2 py-1 font-mono text-label-default uppercase transition-colors md:min-h-0';
const activeClass =
    'border-primary-container bg-primary font-bold text-primary-foreground shadow-pixel-sm';
const inactiveClass =
    'border-surface-container-highest bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface';
</script>

<template>
    <aside
        class="flex w-64 flex-col justify-between border-r-2 border-surface-container-highest bg-surface-container-lowest p-2"
    >
        <nav class="flex flex-col gap-1">
            <div
                class="mb-1 flex items-center justify-between border-b-2 border-surface-container-highest bg-surface-container-low px-2 py-1 font-mono text-label-dense tracking-widest text-on-surface-variant uppercase"
            >
                <span>[ Workspace ]</span>
                <span class="text-primary">CH.01</span>
            </div>
            <Link
                v-for="item in navItems"
                :key="item.title"
                :href="item.href"
                :class="[
                    navItemClass,
                    isCurrentUrl(item.href) ? activeClass : inactiveClass,
                ]"
            >
                <span>
                    {{ isCurrentUrl(item.href) ? '[>' : '[' }}
                    {{ item.title }}]
                </span>
                <span v-if="item.badge" class="text-label-dense">
                    [{{ item.badge }}]
                </span>
            </Link>
        </nav>

        <Link
            v-if="isAuthenticated"
            :href="editProfile()"
            :class="[
                navItemClass,
                'justify-center tracking-wider',
                isCurrentOrParentUrl('/settings') ? activeClass : inactiveClass,
            ]"
        >
            [ Settings ]
        </Link>
    </aside>
</template>
