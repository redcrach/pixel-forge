<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, Plus } from '@lucide/vue';
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
import { home, login, register } from '@/routes';

const page = usePage();
const user = computed(() => page.props.auth.user);
const { getInitials } = useInitials();
const { openBuyCredits } = useBuyCreditsDialog();
</script>

<template>
    <header
        class="sticky top-0 z-40 flex h-14 items-center justify-between gap-2 border-b border-border-subtle bg-surface-container-lowest/90 px-3 backdrop-blur-md sm:px-4"
    >
        <div class="flex items-center gap-2">
            <Sheet>
                <SheetTrigger as-child>
                    <button
                        type="button"
                        class="flex size-11 items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface sm:size-8 md:hidden"
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
                    <div
                        v-if="user"
                        class="mx-2 mb-2 flex flex-col gap-2 rounded-lg border border-border-subtle bg-surface-raised p-3"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="font-mono text-label-default text-outline"
                            >
                                Balance
                            </span>
                            <span
                                class="font-mono text-mono-metric font-semibold text-primary tabular-nums"
                            >
                                $4.2731
                            </span>
                        </div>
                        <SheetClose as-child>
                            <button
                                type="button"
                                class="flex min-h-11 items-center justify-center gap-1 rounded bg-primary px-3 text-label-default font-semibold text-primary-foreground transition-colors hover:bg-primary-container"
                                @click="openBuyCredits"
                            >
                                <Plus class="size-4" />
                                Buy credits
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
                class="flex items-center gap-1 rounded-full border border-border-subtle bg-surface-raised px-2 py-0.5"
            >
                <span
                    class="hidden font-mono text-label-default text-outline sm:inline"
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
                aria-label="Buy credits"
                class="flex min-h-11 min-w-11 items-center justify-center gap-0.5 rounded bg-primary px-2 py-1 text-label-default font-semibold text-primary-foreground shadow-[0_0_12px_rgba(154,230,0,0.25)] transition-colors hover:bg-primary-container sm:min-h-0 sm:min-w-0"
                @click="openBuyCredits"
            >
                <Plus class="size-4" />
                <span class="hidden sm:inline">Buy credits</span>
            </button>

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <button
                        type="button"
                        aria-label="Account menu"
                        class="flex size-11 items-center justify-center rounded-full sm:size-auto"
                    >
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

        <div v-else class="flex items-center gap-1">
            <Link
                :href="login()"
                class="rounded px-2 py-1 text-label-default text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface"
            >
                Log in
            </Link>
            <Link
                :href="register()"
                class="rounded bg-primary px-2 py-1 text-label-default font-semibold text-primary-foreground transition-colors hover:bg-primary-container"
            >
                Register
            </Link>
        </div>
    </header>
</template>
