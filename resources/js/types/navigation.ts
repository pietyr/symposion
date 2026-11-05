import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    /** Highlight only on this exact URL, not on nested pages (area home pages). */
    exact?: boolean;
};

export type NavGroup = {
    label: string;
    items: NavItem[];
};

export type SidebarNavigation = {
    title: string;
    homeHref: NonNullable<InertiaLinkProps['href']>;
    groups: NavGroup[];
    footerItems: NavItem[];
};
