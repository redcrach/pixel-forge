<script setup lang="ts">
const developer = {
    name: 'Taibouni Abdel Hafid',
    email: 'hafid@taibouni.dev',
    githubHandle: 'Redcrach',
    portfolioHost: 'Taibouni.dev',
};

const contactLinks = [
    {
        label: `LinkedIn: ${developer.name}`,
        href: 'https://www.linkedin.com/in/taibouni-abdel-hafid/',
    },
    {
        label: `GitHub: @${developer.githubHandle}`,
        href: `https://github.com/${developer.githubHandle}`,
    },
    {
        label: `Portfolio: ${developer.portfolioHost}`,
        href: `https://${developer.portfolioHost}`,
    },
    {
        label: `Mail: ${developer.email}`,
        href: `mailto:${developer.email}`,
    },
];

const buildYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="flex flex-col gap-2 border-t-2 border-surface-container-highest bg-surface-container-lowest px-4 py-3 font-mono text-label-dense tracking-wider uppercase lg:flex-row lg:items-center lg:justify-between"
    >
        <p class="text-on-surface-variant">
            <span class="text-primary">Dev:</span>
            Crafted by {{ developer.name }} // Sys.build {{ buildYear }}
        </p>

        <nav
            aria-label="Developer contact"
            class="flex flex-wrap items-center gap-1.5"
        >
            <a
                v-for="link in contactLinks"
                :key="link.href"
                :href="link.href"
                target="_blank"
                rel="noopener"
                class="flex min-h-11 items-center border border-surface-container-highest px-1.5 py-0.5 text-wheat normal-case transition-colors hover:bg-surface-container-high hover:text-on-surface sm:min-h-0"
            >
                [ {{ link.label }} ]
            </a>
        </nav>
    </footer>
</template>
