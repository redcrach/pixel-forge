<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Coins, Menu } from '@lucide/vue';
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
    SheetClose,
    SheetContent,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useBuyCreditsDialog } from '@/composables/useBuyCreditsDialog';
import { useInitials } from '@/composables/useInitials';
import { formatMoneyCompact } from '@/lib/money';
import { home, login, register } from '@/routes';

const page = usePage();
const user = computed(() => page.props.auth.user);
const { getInitials } = useInitials();
const { openBuyCredits } = useBuyCreditsDialog();
</script>

<template>
    <header
        class="sticky top-0 z-40 flex h-14 items-center justify-between gap-2 border-b-2 border-surface-container-highest bg-surface-container-lowest px-3 shadow-[0_3px_0_#080b0a] sm:px-4"
    >
        <div class="flex items-center gap-2">
            <Sheet>
                <SheetTrigger as-child>
                    <button
                        type="button"
                        class="flex size-11 items-center justify-center border-2 border-surface-container-highest bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-primary sm:size-9 md:hidden"
                    >
                        <Menu class="size-5" />
                        <span class="sr-only">Open navigation</span>
                    </button>
                </SheetTrigger>
                <SheetContent
                    side="left"
                    class="w-64 border-0 border-r-2 border-surface-container-highest bg-surface-container-lowest p-0 pt-12"
                >
                    <SheetTitle class="sr-only">Navigation</SheetTitle>
                    <div
                        v-if="user"
                        class="mx-2 mb-2 flex flex-col gap-2 border-2 border-border-subtle bg-surface-container-low p-2"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="font-mono text-label-default text-on-surface-variant uppercase"
                            >
                                Credits:
                            </span>
                            <span
                                class="font-mono text-mono-metric font-bold tracking-wider text-wheat tabular-nums"
                            >
                                {{
                                    formatMoneyCompact(
                                        page.props.balanceNano ?? 0,
                                    )
                                }}
                            </span>
                        </div>
                        <SheetClose as-child>
                            <button
                                type="button"
                                class="pixel-press flex min-h-11 items-center justify-center gap-1 bg-primary px-3 text-label-default font-bold text-primary-foreground uppercase transition-colors hover:bg-primary-container"
                                @click="openBuyCredits"
                            >
                                <Coins class="size-4" />
                                [+ Insert coin]
                            </button>
                        </SheetClose>
                    </div>
                    <StudioSidebar class="h-full" />
                </SheetContent>
            </Sheet>

            <Link
                :href="home()"
                aria-label="Pixelforge Studio home"
                class="flex min-h-11 items-center sm:min-h-0"
            >
                <StudioLogo />
            </Link>
        </div>

        <div v-if="user" class="flex items-center gap-2 sm:gap-3">
            <div
                class="flex items-center gap-1 border-2 border-surface-container-highest bg-surface-container-low px-2 py-1"
            >
                <span
                    class="hidden font-mono text-label-default text-on-surface-variant uppercase sm:inline"
                >
                    Credits:
                </span>
                <span
                    class="font-mono text-mono-metric font-bold tracking-wider text-wheat tabular-nums"
                >
                    {{ formatMoneyCompact(page.props.balanceNano ?? 0) }}
                </span>
            </div>

            <button
                type="button"
                aria-label="Buy credits"
                class="pixel-press flex min-h-11 min-w-11 items-center justify-center gap-1 border-2 border-primary-container bg-primary px-2 py-1 text-label-default font-bold text-primary-foreground uppercase transition-colors hover:bg-primary-container sm:min-h-0 sm:min-w-0"
                @click="openBuyCredits"
            >
                <Coins class="size-4" />
                <span class="hidden sm:inline">[+ Insert coin]</span>
            </button>

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <button
                        type="button"
                        aria-label="Account menu"
                        class="flex size-11 items-center justify-center sm:size-auto"
                    >
                        <Avatar
                            class="size-8 rounded-none border-2 border-surface-container-highest"
                        >
                            <AvatarImage
                                v-if="user.avatar"
                                :src="user.avatar"
                                :alt="user.name"
                            />
                            <AvatarFallback
                                class="rounded-none bg-canvas-light text-label-default font-bold text-primary-foreground"
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

        <div v-else class="flex items-center gap-1">
            <Link
                :href="login()"
                class="px-2 py-1 text-label-default text-on-surface-variant uppercase transition-colors hover:bg-surface-container-high hover:text-on-surface"
            >
                [ Log in ]
            </Link>
            <Link
                :href="register()"
                class="pixel-press bg-primary px-2 py-1 text-label-default font-bold text-primary-foreground uppercase transition-colors hover:bg-primary-container"
            >
                [ Register ]
            </Link>
        </div>
    </header>
</template>
