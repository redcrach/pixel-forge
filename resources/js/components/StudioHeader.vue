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

            <Link :href="home()">
                <StudioLogo />
            </Link>
        </div>

        <div v-if="user" class="flex items-center gap-3">
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

        <div v-else class="flex items-center gap-1">
            <Link
                :href="login()"
                class="rounded-lg px-2 py-1 text-label-default text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface"
            >
                Log in
            </Link>
            <Link
                :href="register()"
                class="rounded-lg bg-primary px-2 py-1 text-label-default text-primary-foreground transition-colors hover:bg-primary-container"
            >
                Register
            </Link>
        </div>
    </header>
</template>
