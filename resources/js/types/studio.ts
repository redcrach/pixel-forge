import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

export type GenerationStatus =
    | 'processing'
    | 'waiting'
    | 'queued'
    | 'succeeded'
    | 'failed';

export type StudioNavItem = {
    title: string;
    href?: NonNullable<InertiaLinkProps['href']>;
    icon: LucideIcon;
    badge?: string;
    badgeTone?: 'primary' | 'muted';
};

export type AspectRatioOption = {
    ratio: string;
    dimensions: string;
    description: string;
    previewClass: string;
    isDefault?: boolean;
    isSelected?: boolean;
};

export type QualityOption = {
    label: string;
    description: string;
    isSelected?: boolean;
};

export type ReferenceImage = {
    url: string;
    thumbnailUrl: string;
    weight: number;
    role: string;
};

export type CostLineItem = {
    label: string;
    detail: string;
    amount: string;
    isEstimate?: boolean;
};

export type TopUpPreset = {
    amount: string;
    images: string;
    caption: string;
    badge?: string;
    tone?: 'primary' | 'tertiary';
    isSelected?: boolean;
};

export type GenerationTag = {
    label: string;
    tone?: 'default' | 'model' | 'primary' | 'success';
};

export type GenerationSummary = {
    id: string;
    prompt: string;
    status: GenerationStatus;
    statusLabel: string;
    imageUrl?: string;
    isExpired?: boolean;
    failureReason?: string;
    failureCode?: string;
    tags: GenerationTag[];
    costLabel: string;
    costValue: string;
    isCostPending?: boolean;
    createdAgo: string;
};

export type CostBreakdownRow = {
    phase: string;
    estimated: string;
    actual: string;
    isSaving?: boolean;
};

export type TimelineEvent = {
    title: string;
    description: string;
    timestamp: string;
    tone: 'muted' | 'processing' | 'succeeded' | 'primary';
};

export type GenerationDetails = {
    id: string;
    status: GenerationStatus;
    statusLabel: string;
    createdAt: string;
    imageUrl: string;
    imageMeta: string;
    prompt: string;
    seed: string;
    sampler: string;
    steps: number;
    savedLabel: string;
    costRows: CostBreakdownRow[];
    totalEstimated: string;
    totalActual: string;
    timeline: TimelineEvent[];
};

export type HistoryFilterTab = {
    label: string;
    count: number;
    tone: 'primary' | 'processing' | 'succeeded' | 'failed';
    isActive?: boolean;
};
