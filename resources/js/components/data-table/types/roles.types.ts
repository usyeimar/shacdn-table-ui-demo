import { Permission } from '@/types';
import type { Identifiable, PaginationMeta } from './datatable.types';

export interface ApiRoleItem {
    id: number;
    name: string;
    description?: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
    permissions_count: number;
    users_count: number;
}

export interface ApiRolesResponse {
    data: ApiRoleItem[];
    meta: PaginationMeta;
}

export interface Role extends Identifiable {
    id: number;
    name: string;
    display_name: string;
    description: string | null;
    guard_name: string;
    created_at: string;
    updated_at: string;
    permissions?: Permission[];
    users_count?: number;
    permissions_count?: number;
    permissions_by_group?: Record<string, string[]>;
}
