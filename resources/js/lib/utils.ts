import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { extendTailwindMerge } from 'tailwind-merge';

/**
 * Register the design system's custom font sizes (see `@theme` in app.css),
 * so classes like `text-label-dense` aren't mistaken for text colors.
 */
const twMerge = extendTailwindMerge({
    extend: {
        theme: {
            text: [
                'headline-xl',
                'headline-lg',
                'headline-sm',
                'body-default',
                'body-dense',
                'label-default',
                'label-dense',
                'mono-metric',
                'mono-badge',
            ],
        },
    },
});

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
