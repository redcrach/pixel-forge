<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Bell, Menu, Plus } from '@lucide/vue';
import { computed } from 'vue';
import StudioLogo from '@/components/StudioLogo.vue';
import StudioSidebar from '@/components/StudioSidebar.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useBuyCreditsDialog } from '@/composables/useBuyCreditsDialog';
import { useInitials } from '@/composables/useInitials';
import { generate } from '@/routes';

const page = usePage();
const user = computed(() => page.props.auth.user);
const { getInitials } = useInitials();
const { openBuyCredits } = useBuyCreditsDialog();
</script>

<template>
    <header
        class="sticky top-0 z-40 flex h-14 items-center justify-between bg-surface-container-lowest/90 px-4 backdrop-blur-md"
    >
        <div class="flex items-center gap-2">
            <Sheet>
                <SheetTrigger as-child>
                    <button
                        type="button"
                        class="flex size-8 items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface lg:hidden"
                    >
                        <Menu class="size-5" />
                        <span class="sr-only">Open navigation</span>
                    </button>
                </SheetTrigger>
                <SheetContent
                    side="left"
                    class="w-64 border-0 bg-surface-container-lowest p-0 pt-12"
                >
                    <SheetTitle class="sr-only">Navigation</SheetTitle>
                    <StudioSidebar class="h-full" />
                </SheetContent>
            </Sheet>

            <Link :href="generate()">
                <StudioLogo />
            </Link>
        </div>

        <div class="flex items-center gap-3">
            <div
                class="hidden items-center gap-1 rounded-full bg-surface-container-high/60 px-2 py-0.5 md:flex"
            >
                <span class="relative flex size-2">
                    <span
                        class="absolute inline-flex size-full animate-ping rounded-full bg-tertiary opacity-75"
                    />
                    <span
                        class="relative inline-flex size-2 rounded-full bg-tertiary"
                    />
                </span>
                <span class="text-label-dense text-on-surface-variant">
                    All systems normal
                </span>
            </div>

            <div
                class="flex items-center gap-1 rounded-lg bg-surface-container-high px-2 py-0.5"
            >
                <span
                    class="hidden text-label-default text-on-surface-variant sm:inline"
                >
                    Balance:
                </span>
                <span
                    class="font-mono text-mono-metric text-primary tabular-nums"
                >
                    $4.2731
                </span>
            </div>

            <button
                type="button"
                class="flex items-center gap-0.5 rounded-lg bg-surface-container px-2 py-0.5 text-label-default text-on-surface transition-colors hover:bg-surface-container-high"
                @click="openBuyCredits"
            >
                <Plus class="size-4" />
                <span class="hidden sm:inline">Buy credits</span>
            </button>

            <button
                type="button"
                class="relative flex size-8 items-center justify-center rounded-lg text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface"
            >
                <Bell class="size-5" />
                <span
                    class="absolute top-1.5 right-1.5 size-1.5 rounded-full bg-primary"
                />
                <span class="sr-only">Notifications</span>
            </button>

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <button type="button" class="rounded-full">
                        <Avatar
                            class="size-8 ring-1 ring-surface-container-highest"
                        >
                            <AvatarImage
                                v-if="user.avatar"
                                :src="user.avatar"
                                :alt="user.name"
                            />
                            <AvatarFallback
                                class="bg-surface-container-high text-label-default text-on-surface"
                            >
                                {{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56">
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
