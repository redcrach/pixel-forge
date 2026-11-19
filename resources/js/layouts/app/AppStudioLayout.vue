<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BuyCreditsDialog from '@/components/credits/BuyCreditsDialog.vue';
import GenerationNotifications from '@/components/GenerationNotifications.vue';
import StudioHeader from '@/components/StudioHeader.vue';
import StudioSidebar from '@/components/StudioSidebar.vue';
import { Toaster } from '@/components/ui/sonner';

const page = usePage();
const user = computed(() => page.props.auth.user);
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
        <GenerationNotifications
            v-if="user"
            :key="user.id"
            :user-id="user.id"
        />
    </div>
</template>
