<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed } from 'vue';
import BuyCreditsDialog from '@/components/credits/BuyCreditsDialog.vue';
import GenerationNotifications from '@/components/GenerationNotifications.vue';
import StudioHeader from '@/components/StudioHeader.vue';
import StudioSidebar from '@/components/StudioSidebar.vue';
import { Toaster } from '@/components/ui/sonner';

const page = usePage();
const user = computed(() => page.props.auth.user);

/**
 * Where the Generate page has its bottom action bar, toasts move to the top, just below the header.
 * Sonner's own phone CSS (≤ 600px) sets the toaster to 100% width plus a side offset, so it sticks out
 * of the screen; the width overrides below keep toasts inside with 16px side margins.
 */
const isWideScreen = useMediaQuery('(min-width: 1024px)');
const belowHeaderOffset = {
    top: '4.5rem',
    right: '16px',
    bottom: '16px',
    left: '16px',
};
</script>

<template>
    <div class="min-h-svh bg-surface text-on-surface">
        <StudioHeader />

        <div class="flex">
            <StudioSidebar
                class="sticky top-14 hidden h-[calc(100svh-3.5rem)] shrink-0 md:flex"
            />

            <main class="min-w-0 flex-1 px-4 pt-3 pb-6 sm:pt-2">
                <slot />
            </main>
        </div>

        <BuyCreditsDialog />
        <Toaster
            :position="isWideScreen ? 'bottom-right' : 'top-center'"
            :offset="isWideScreen ? undefined : belowHeaderOffset"
            :mobile-offset="belowHeaderOffset"
            class="max-[600px]:w-auto!"
            :toast-options="{ class: 'max-[600px]:w-full!' }"
        />
        <GenerationNotifications
            v-if="user"
            :key="user.id"
            :user-id="user.id"
        />
    </div>
</template>
