<script setup lang="ts">
import BuyCreditsDialog from '@/components/credits/BuyCreditsDialog.vue';
import StudioHeader from '@/components/StudioHeader.vue';
import StudioSidebar from '@/components/StudioSidebar.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="min-h-svh bg-surface text-on-surface">
        <StudioHeader />

        <div class="flex">
            <StudioSidebar
                class="sticky top-14 hidden h-[calc(100svh-3.5rem)] shrink-0 lg:flex"
            />

            <main class="min-w-0 flex-1 px-4 pt-2 pb-6">
                <slot />
            </main>
        </div>

        <BuyCreditsDialog />
        <Toaster />
    </div>
</template>
