import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    product_count: number;
}

export interface Brand {
    id: number;
    name: string;
    slug: string;
    product_count: number;
}

export interface Product {
    id: number;
    name: string;
    slug: string;
    final_price: number;
    sale_price: number;
    thumnail_img: string;
    category_name: string;
    stock: number;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    categories?: Category[];
    brands?: Brand[];
    products?: PaginatedData<Product>;
    selectedCategory?: string | null;
    selectedBrand?: string | null;
    wishlist?: number[];
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    role?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
